<?php
declare(strict_types=1);

namespace App\Controller\Doctor;

use App\Controller\AppController;
use App\Controller\Trait\PptDownloadTrait;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use App\Lib\S3DocumentService;
use App\Service\SlideGridLayoutService;
use Psr\Http\Message\UploadedFileInterface;

/**
 * MegReports Controller (Doctor)
 * Handles MEG PowerPoint report slide management
 *
 * @property \App\Model\Table\ReportSlidesTable $ReportSlides
 * @property \App\Model\Table\ReportsTable $Reports
 */
class MegReportsController extends AppController
{
    use PptDownloadTrait;
    
    /**
     * Index method - List all slides for a report
     *
     * @param int|null $reportId Report ID
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index($reportId = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        $reportId = $reportId ?? $this->request->getQuery('report_id');
        $caseId = $this->request->getQuery('case_id');
        
        // If no report_id but case_id provided, try to find or create PPT report
        if (!$reportId && $caseId) {
            $Reports = $this->fetchTable('Reports');
            
            // Check if PPT report already exists for this case
            $report = $Reports->find()
                ->contain(['Cases'])
                ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                    return $q->where(['CaseAssignments.assigned_to' => $userId]);
                })
                ->where(['Reports.case_id' => $caseId, 'Reports.type' => 'PPT'])
                ->first();
                
            if (!$report) {
                // Verify user has access to this case
                $case = $this->fetchTable('Cases')->find()
                    ->matching('CaseAssignments', function ($q) use ($userId) {
                        return $q->where(['CaseAssignments.assigned_to' => $userId]);
                    })
                    ->where(['Cases.id' => $caseId])
                    ->first();
                    
                if (!$case) {
                    $this->Flash->error('Case not found or you do not have access.');
                    return $this->redirect(['controller' => 'Cases', 'action' => 'index']);
                }
                
                // Create new PPT report
                $report = $Reports->newEntity([
                    'case_id' => $caseId,
                    'user_id' => $userId,
                    'hospital_id' => $case->hospital_id,
                    'type' => 'PPT'
                ]);
               
                if ($Reports->save($report)) {
                    $reportId = $report->id;
                    
                    // Reload report with associations after creation
                    $report = $Reports->get($reportId, contain: ['Cases' => ['PatientUsers' => ['Patient']], 'Users']);
                    
                    // Start with only the patient cover page; doctors add their own slides (or apply a report template)
                    $this->createCoverPage($report);
                    
                    $this->Flash->success('MEG Report created with the patient cover page. Add slides, or start from one of your report templates.');
                } else {
                    $this->Flash->error('Unable to create MEG report. Please try again.');
                    return $this->redirect(['controller' => 'Cases', 'action' => 'view', $caseId]);
                }
            } else {
                $reportId = $report->id;
            }
        }
        
        if (!$reportId) {
            $this->Flash->error('Please select a report.');
            return $this->redirect(['controller' => 'Reports', 'action' => 'index']);
        }
        
        // If report not already loaded, verify access to this report
        if (!isset($report) || !$report) {
            $Reports = $this->fetchTable('Reports');
            $report = $Reports->find()
                ->contain(['Cases' => ['PatientUsers']])
                ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                    return $q->where(['CaseAssignments.assigned_to' => $userId]);
                })
                ->where(['Reports.id' => $reportId, 'Reports.type' => 'PPT'])
                ->first();
                
            if (!$report) {
                $this->Flash->error('Report not found or you do not have access.');
                return $this->redirect(['controller' => 'Reports', 'action' => 'index']);
            }
        }
        
        $ReportSlides = $this->fetchTable('ReportSlides');
        $slides = $ReportSlides->find()
            ->where(['report_id' => $reportId])
            ->orderBy(['slide_order' => 'ASC'])
            ->all();
        
        // Generate URLs for slide images
        $s3Service = new S3DocumentService();
        foreach ($slides as $slide) {
            if ($slide->file_path) {
                $slide->image_url = $s3Service->getDownloadUrl($slide->file_path);
            }
            if ($slide->col1_image_path) {
                $slide->col1_image_url = $s3Service->getDownloadUrl($slide->col1_image_path);
            }
            if ($slide->col2_image_path) {
                $slide->col2_image_url = $s3Service->getDownloadUrl($slide->col2_image_path);
            }
            // Handle col3-col5 for multi-image slides (index method)
            foreach ([3, 4, 5] as $colNum) {
                $pathField = "col{$colNum}_image_path";
                if ($slide->{$pathField}) {
                    $slide->{"col{$colNum}_image_url"} = $s3Service->getDownloadUrl($slide->{$pathField});
                }
            }
            if ($slide->isCustomLayout()) {
                $slide->grid_layout = SlideGridLayoutService::withImageUrls($slide->getLayoutData(), $s3Service);
            }
        }
        
        // Get available slide types from configuration
        $slideTypes = unserialize(PPT_REPORT_PAGES);
        $slideCategories = unserialize(PPT_SLIDE_CATEGORIES);
        
        // This doctor's whole-report templates (save / start-from-template on the slides page)
        $reportTemplates = $this->fetchTable('ReportTemplates')->find()
            ->where(['user_id' => $userId])
            ->orderBy(['name' => 'ASC'])
            ->all();
        
        $this->set(compact('slides', 'report', 'reportId', 'slideTypes', 'slideCategories', 'reportTemplates'));
    }

    /**
     * Add method - Add a new slide with slide type selection
     *
     * @param int|null $reportId Report ID
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add($reportId = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        $reportId = $reportId ?? $this->request->getQuery('report_id');
        $slideType = $this->request->getQuery('slide_type');
        
        if (!$reportId) {
            $this->Flash->error('Please select a report.');
            return $this->redirect(['controller' => 'Reports', 'action' => 'index']);
        }
        
        // Verify access to this report
        $Reports = $this->fetchTable('Reports');
        $report = $Reports->find()
            ->contain(['Cases'])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $reportId, 'Reports.type' => 'PPT'])
            ->first();
            
        if (!$report) {
            $this->Flash->error('Report not found or you do not have access.');
            return $this->redirect(['controller' => 'Reports', 'action' => 'index']);
        }
        
        if ($slideType === SlideGridLayoutService::SLIDE_TYPE) {
            return $this->customLayoutForm($report);
        }
        
        // Get slide type configuration
        $slideTypes = unserialize(PPT_REPORT_PAGES);
        $slideConfig = null;
        
        if ($slideType && isset($slideTypes[$slideType])) {
            $slideConfig = $slideTypes[$slideType];
        }
        
        // Get exam procedures for this case
        $CasesExamsProcedures = $this->fetchTable('CasesExamsProcedures');
        $examProcedures = $CasesExamsProcedures->find()
            ->contain(['ExamsProcedures' => ['Exams' => ['Modalities'], 'Procedures']])
            ->where(['CasesExamsProcedures.case_id' => $report->case_id])
            ->all();
        
        // Build dropdown list with complete information
        $examProceduresList = [];
        foreach ($examProcedures as $cep) {
            if (!empty($cep->exams_procedure)) {
                $ep = $cep->exams_procedure;
                $label = '';
                
                if (!empty($ep->exam)) {
                    $label = $ep->exam->name;
                    if (!empty($ep->exam->modality)) {
                        $label .= ' (' . $ep->exam->modality->name . ')';
                    }
                }
                
                if (!empty($ep->procedure)) {
                    $label = $label ? $label . ' - ' . $ep->procedure->name : $ep->procedure->name;
                }
                
                if ($label) {
                    $examProceduresList[$cep->id] = $label;
                }
            }
        }
        
        $ReportSlides = $this->fetchTable('ReportSlides');
        $slide = $ReportSlides->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $slideType = $data['slide_type'] ?? $slideType;
            $slideConfig = $slideTypes[$slideType] ?? null;
            
            // Get next slide order
            $maxOrderQuery = $ReportSlides->find()
                ->where(['report_id' => $reportId])
                ->select(['max_order' => $ReportSlides->find()->func()->max('slide_order')])
                ->first();
            
            $maxOrderValue = $maxOrderQuery ? $maxOrderQuery->max_order : null;
            $nextOrder = ($maxOrderValue !== null) ? (int)$maxOrderValue + 1 : 1;
            
            $s3Service = new S3DocumentService();
            
            // Handle Column 1 Image Upload
            $col1ImagePath = null;
            $col1ImageFile = $this->request->getData('col1_image');
            if ($col1ImageFile && $col1ImageFile->getError() === UPLOAD_ERR_OK) {
                $col1ImagePath = $this->uploadSlideImage($col1ImageFile, $report, $s3Service);
            }
            
            // Handle Column 2 Image Upload
            $col2ImagePath = null;
            $col2ImageFile = $this->request->getData('col2_image');
            if ($col2ImageFile && $col2ImageFile->getError() === UPLOAD_ERR_OK) {
                $col2ImagePath = $this->uploadSlideImage($col2ImageFile, $report, $s3Service);
            }
            
            // Handle Column 3-5 Image Uploads (for stacked/multi-image layouts)
            $colImagePaths = [];
            foreach ([3, 4, 5] as $colNum) {
                $colImageFile = $this->request->getData("col{$colNum}_image");
                if ($colImageFile && $colImageFile->getError() === UPLOAD_ERR_OK) {
                    $colImagePaths[$colNum] = $this->uploadSlideImage($colImageFile, $report, $s3Service);
                }
            }
            
            // Handle legacy single image upload (for backward compatibility)
            $imagePath = null;
            $imageFile = $this->request->getData('image_file');
            if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
                $imagePath = $this->uploadSlideImage($imageFile, $report, $s3Service);
            }
            
            // Build slide data
            $slideData = [
                'report_id' => $reportId,
                'user_id' => $userId,
                'slide_order' => $nextOrder,
                'slide_type' => $slideType,
                'layout_columns' => $slideConfig['columns'] ?? 1,
                'title' => $data['title'] ?? ($slideConfig['title'] ?? ''),
                'subtitle' => $data['subtitle'] ?? ($slideConfig['subtitle'] ?? null),
                'col1_type' => $data['col1_type'] ?? ($slideConfig['col1']['type'] ?? 'text'),
                'col1_content' => $data['col1_content'] ?? null,
                'col1_image_path' => $col1ImagePath ?? $imagePath,
                'col1_header' => $data['col1_header'] ?? ($slideConfig['col1']['header'] ?? null),
                'col2_type' => $data['col2_type'] ?? ($slideConfig['col2']['type'] ?? 'text'),
                'col2_content' => $data['col2_content'] ?? null,
                'col2_image_path' => $col2ImagePath,
                'col2_header' => $data['col2_header'] ?? ($slideConfig['col2']['header'] ?? null),
                'col3_image_path' => $colImagePaths[3] ?? null,
                'col4_image_path' => $colImagePaths[4] ?? null,
                'col5_image_path' => $colImagePaths[5] ?? null,
                'footer_text' => $data['footer_text'] ?? ($slideConfig['footer_text'] ?? null),
                'legend_data' => isset($data['legend_items']) ? json_encode($data['legend_items']) : null,
                'description' => $data['description'] ?? $data['col1_content'] ?? null,
                'file_path' => $col1ImagePath ?? $imagePath,
                's3_key' => $col1ImagePath ?? $imagePath,
            ];
            
            // Build HTML content for preview
            $slideData['html_content'] = $this->buildSlideHtml($slideData, $slideConfig);
            
            $slide = $ReportSlides->patchEntity($slide, $slideData);
            
            if ($ReportSlides->save($slide)) {
                $this->Flash->success('Slide has been added.');
                return $this->redirect(['action' => 'index', $reportId]);
            }
            
            $errors = $slide->getErrors();
            if (!empty($errors)) {
                foreach ($errors as $field => $error) {
                    $errorMessage = is_array($error) ? implode(', ', $error) : $error;
                    $this->Flash->error("Error in {$field}: {$errorMessage}");
                }
            } else {
                $this->Flash->error('Unable to add the slide. Please try again.');
            }
        }
        
        // Set default values from slide config
        if ($slideConfig) {
            $slide->title = $slideConfig['title'] ?? '';
            $slide->subtitle = $slideConfig['subtitle'] ?? null;
            $slide->layout_columns = $slideConfig['columns'] ?? 1;
            $slide->col1_type = $slideConfig['col1']['type'] ?? 'text';
            $slide->col2_type = $slideConfig['col2']['type'] ?? 'text';
            $slide->col1_header = $slideConfig['col1']['header'] ?? null;
            $slide->col2_header = $slideConfig['col2']['header'] ?? null;
            $slide->footer_text = $slideConfig['footer_text'] ?? null;
            
            // Set default content for text_and_image layout
            if (isset($slideConfig['col1']['default_content'])) {
                $slide->col1_content = $slideConfig['col1']['default_content'];
            }
        }
        
        $slideCategories = unserialize(PPT_SLIDE_CATEGORIES);
        
        // Custom layout matrix + this doctor's saved layouts for the slide type picker
        $gridPresets = SlideGridLayoutService::presets();
        $layoutTemplates = $this->fetchTable('SlideLayoutTemplates')->find()
            ->where(['user_id' => $userId])
            ->orderBy(['name' => 'ASC'])
            ->all();
        
        $this->set(compact('slide', 'report', 'reportId', 'examProceduresList', 'slideType', 'slideConfig', 'slideTypes', 'slideCategories', 'gridPresets', 'layoutTemplates'));
    }

    /**
     * Add/edit form for doctor-defined grid layout slides
     *
     * New slides start from ?preset=<key> or ?template_id=<id> (a saved layout).
     * Box and column content is matched by flat index, so switching presets keeps content.
     *
     * @param \App\Model\Entity\Report $report Report (with Cases)
     * @param \App\Model\Entity\ReportSlide|null $slide Existing slide, null when adding
     * @return \Cake\Http\Response|null
     */
    private function customLayoutForm($report, $slide = null): ?Response
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();
        $ReportSlides = $this->fetchTable('ReportSlides');
        $LayoutTemplates = $this->fetchTable('SlideLayoutTemplates');
        $gridPresets = SlideGridLayoutService::presets();
        $isNew = $slide === null;
        $oldLayout = $isNew ? ['preset' => null, 'rows' => []] : $slide->getLayoutData();
        
        if ($isNew) {
            $slide = $ReportSlides->newEmptyEntity();
            $layout = null;
            
            $templateId = $this->request->getQuery('template_id');
            if ($templateId) {
                $template = $LayoutTemplates->find()
                    ->where(['id' => $templateId, 'user_id' => $userId])
                    ->first();
                if ($template) {
                    $layout = $template->getLayoutData();
                    $slide->title = $template->title;
                    $slide->subtitle = $template->subtitle;
                }
            }
            
            if (!$layout || !isset($gridPresets[$layout['preset'] ?? ''])) {
                $presetKey = (string)$this->request->getQuery('preset', '');
                $layout = SlideGridLayoutService::fromPreset(isset($gridPresets[$presetKey]) ? $presetKey : '2col');
            }
        } else {
            $layout = $oldLayout;
            if (!isset($gridPresets[$layout['preset'] ?? ''])) {
                $layout = SlideGridLayoutService::fromPreset('2col');
            }
        }
        
        if ($this->request->is(['post', 'put', 'patch'])) {
            $data = $this->request->getData();
            $presetKey = (string)($data['preset'] ?? '');
            
            if (!isset($gridPresets[$presetKey])) {
                $this->Flash->error('Please choose a layout.');
                return $this->redirect($this->referer());
            }
            
            $s3Service = new S3DocumentService();
            $oldCells = SlideGridLayoutService::cells($oldLayout);
            $postedCells = (array)($data['cells'] ?? []);
            
            // Only boxes that exist in the chosen preset are kept (and uploaded)
            $counts = SlideGridLayoutService::counts($presetKey);
            $cellInput = [];
            $uploadedPaths = [];
            for ($i = 0; $i < $counts['boxes']; $i++) {
                $posted = (array)($postedCells[$i] ?? []);
                $imagePath = empty($posted['remove_image']) ? ($oldCells[$i]['image_path'] ?? null) : null;
                
                $imageFile = $posted['image_file'] ?? null;
                if ($imageFile instanceof UploadedFileInterface && $imageFile->getError() === UPLOAD_ERR_OK) {
                    $uploaded = $this->uploadSlideImage($imageFile, $report, $s3Service);
                    if ($uploaded) {
                        $imagePath = $uploaded;
                        $uploadedPaths[] = $uploaded;
                    } else {
                        $this->Flash->warning('Box ' . ($i + 1) . ': image could not be uploaded (use JPG, PNG or GIF).');
                    }
                }
                
                $cellInput[$i] = [
                    'header' => $posted['header'] ?? '',
                    'type' => $posted['type'] ?? null,
                    'text' => $posted['text'] ?? '',
                    'image_path' => $imagePath,
                ];
            }
            
            $columnInput = array_slice((array)($data['columns'] ?? []), 0, $counts['columns'], true);
            $layout = SlideGridLayoutService::build($presetKey, $cellInput, $columnInput);
            $title = trim((string)($data['title'] ?? ''));
            $subtitle = trim((string)($data['subtitle'] ?? ''));
            
            $slideData = [
                'slide_type' => SlideGridLayoutService::SLIDE_TYPE,
                'title' => $title,
                'subtitle' => $subtitle !== '' ? $subtitle : null,
                'description' => $title,
                'layout_columns' => SlideGridLayoutService::maxColumns($layout),
                'layout_data' => json_encode($layout),
                'html_content' => null,
            ];
            
            if ($isNew) {
                $maxOrder = $ReportSlides->find()
                    ->where(['report_id' => $report->id])
                    ->select(['max_order' => $ReportSlides->find()->func()->max('slide_order')])
                    ->first();
                $slideData['report_id'] = $report->id;
                $slideData['user_id'] = $userId;
                $slideData['slide_order'] = (int)($maxOrder->max_order ?? 0) + 1;
            }
            
            $slide = $ReportSlides->patchEntity($slide, $slideData);
            
            if ($ReportSlides->save($slide)) {
                // Remove images that were replaced, removed, or dropped by a smaller layout
                $keptPaths = SlideGridLayoutService::imagePaths($layout);
                foreach (array_diff(SlideGridLayoutService::imagePaths($oldLayout), $keptPaths) as $orphanPath) {
                    $s3Service->deleteDocument($orphanPath);
                }
                
                if (!empty($data['save_as_template'])) {
                    $templateName = trim((string)($data['template_name'] ?? '')) ?: ($title ?: $gridPresets[$presetKey]['name']);
                    $template = $LayoutTemplates->newEntity([
                        'user_id' => $userId,
                        'name' => mb_substr($templateName, 0, 150),
                        'preset' => $presetKey,
                        'title' => $title,
                        'subtitle' => $slideData['subtitle'],
                        'layout_data' => json_encode(SlideGridLayoutService::withoutImages($layout)),
                    ]);
                    if ($LayoutTemplates->save($template)) {
                        $this->Flash->success('Layout saved to "My Layouts" as "' . $template->name . '".');
                    } else {
                        $this->Flash->warning('Slide saved, but the layout template could not be saved.');
                    }
                }
                
                $this->Flash->success($isNew ? 'Slide has been added.' : 'Slide has been updated.');
                return $this->redirect(['action' => 'index', $report->id]);
            }
            
            foreach ($uploadedPaths as $uploadedPath) {
                $s3Service->deleteDocument($uploadedPath);
            }
            $this->Flash->error('Unable to save the slide. Please try again.');
        }
        
        $layout = SlideGridLayoutService::withImageUrls($layout, new S3DocumentService());
        $cellTypes = SlideGridLayoutService::CELL_TYPES;
        $reportId = $report->id;
        
        $this->set(compact('slide', 'report', 'reportId', 'layout', 'gridPresets', 'cellTypes', 'isNew'));
        
        return $this->render('custom_layout');
    }

    /**
     * Find a PPT report the current doctor is assigned to
     *
     * @param int|string|null $reportId Report ID
     * @return \App\Model\Entity\Report|null
     */
    private function findAssignedReport($reportId)
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();

        return $this->fetchTable('Reports')->find()
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $reportId, 'Reports.type' => 'PPT'])
            ->first();
    }

    /**
     * Save this report's custom layout slides (layouts, titles, headers, text; no images)
     * as a whole-report template. Saving under an existing name replaces that template.
     *
     * @param int|null $reportId Report ID
     * @return \Cake\Http\Response|null Redirects back to the slides page
     */
    public function saveReportTemplate($reportId = null)
    {
        $this->request->allowMethod(['post']);
        $userId = $this->request->getAttribute('identity')->getIdentifier();
        
        if (!$this->findAssignedReport($reportId)) {
            $this->Flash->error('Report not found or you do not have access.');
            return $this->redirect(['controller' => 'Reports', 'action' => 'index']);
        }
        
        $customSlides = $this->fetchTable('ReportSlides')->find()
            ->where(['report_id' => $reportId, 'slide_type' => SlideGridLayoutService::SLIDE_TYPE])
            ->orderBy(['slide_order' => 'ASC'])
            ->all();
        
        $templateSlides = [];
        foreach ($customSlides as $slide) {
            $templateSlides[] = [
                'title' => $slide->title,
                'subtitle' => $slide->subtitle,
                'layout' => SlideGridLayoutService::withoutImages($slide->getLayoutData()),
            ];
        }
        
        if (empty($templateSlides)) {
            $this->Flash->error('This report has no custom layout slides to save yet.');
            return $this->redirect(['action' => 'index', $reportId]);
        }
        
        $name = mb_substr(trim((string)$this->request->getData('name')), 0, 150);
        if ($name === '') {
            $this->Flash->error('Please enter a template name.');
            return $this->redirect(['action' => 'index', $reportId]);
        }
        
        $ReportTemplates = $this->fetchTable('ReportTemplates');
        $template = $ReportTemplates->find()->where(['user_id' => $userId, 'name' => $name])->first()
            ?? $ReportTemplates->newEntity(['user_id' => $userId, 'name' => $name]);
        $isUpdate = !$template->isNew();
        $template->slides_data = json_encode($templateSlides);
        
        if ($ReportTemplates->save($template)) {
            $this->Flash->success(sprintf(
                'Report template "%s" %s with %d slide(s).',
                $template->name,
                $isUpdate ? 'updated' : 'saved',
                count($templateSlides)
            ));
        } else {
            $this->Flash->error('Unable to save the report template. Please try again.');
        }
        
        return $this->redirect(['action' => 'index', $reportId]);
    }

    /**
     * Add all slides from one of the doctor's report templates to this report (after existing slides)
     *
     * @param int|null $reportId Report ID
     * @return \Cake\Http\Response|null Redirects back to the slides page
     */
    public function applyReportTemplate($reportId = null)
    {
        $this->request->allowMethod(['post']);
        $userId = $this->request->getAttribute('identity')->getIdentifier();
        
        if (!$this->findAssignedReport($reportId)) {
            $this->Flash->error('Report not found or you do not have access.');
            return $this->redirect(['controller' => 'Reports', 'action' => 'index']);
        }
        
        $template = $this->fetchTable('ReportTemplates')->find()
            ->where(['id' => $this->request->getData('template_id'), 'user_id' => $userId])
            ->first();
        if (!$template) {
            $this->Flash->error('Report template not found.');
            return $this->redirect(['action' => 'index', $reportId]);
        }
        
        $ReportSlides = $this->fetchTable('ReportSlides');
        $maxOrder = $ReportSlides->find()
            ->where(['report_id' => $reportId])
            ->select(['max_order' => $ReportSlides->find()->func()->max('slide_order')])
            ->first();
        $nextOrder = (int)($maxOrder->max_order ?? 0) + 1;
        
        $newSlides = [];
        foreach ($template->getSlides() as $templateSlide) {
            $layout = SlideGridLayoutService::withoutImages($templateSlide['layout'] ?? ['rows' => []]);
            if (empty($layout['rows'])) {
                continue;
            }
            $newSlides[] = $ReportSlides->newEntity([
                'report_id' => $reportId,
                'user_id' => $userId,
                'slide_order' => $nextOrder++,
                'slide_type' => SlideGridLayoutService::SLIDE_TYPE,
                'title' => (string)($templateSlide['title'] ?? ''),
                'subtitle' => $templateSlide['subtitle'] ?? null,
                'description' => (string)($templateSlide['title'] ?? ''),
                'layout_columns' => SlideGridLayoutService::maxColumns($layout),
                'layout_data' => json_encode($layout),
            ]);
        }
        
        if ($newSlides && $ReportSlides->saveMany($newSlides)) {
            $this->Flash->success(sprintf('Added %d slide(s) from "%s". Now add the images to each slide.', count($newSlides), $template->name));
        } else {
            $this->Flash->error('Unable to apply the report template.');
        }
        
        return $this->redirect(['action' => 'index', $reportId]);
    }

    /**
     * Delete one of the current doctor's report templates
     *
     * @param int|null $id Template ID
     * @return \Cake\Http\Response|null Redirects back to the slides page
     */
    public function deleteReportTemplate($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $userId = $this->request->getAttribute('identity')->getIdentifier();
        
        $ReportTemplates = $this->fetchTable('ReportTemplates');
        $template = $ReportTemplates->find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->first();
        
        if ($template && $ReportTemplates->delete($template)) {
            $this->Flash->success('Report template deleted.');
        } else {
            $this->Flash->error('Report template not found.');
        }
        
        return $this->redirect(['action' => 'index', $this->request->getQuery('report_id')]);
    }

    /**
     * Delete one of the current doctor's saved layout templates
     *
     * @param int|null $id Template ID
     * @return \Cake\Http\Response|null Redirects back to the slide type picker
     */
    public function deleteLayoutTemplate($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $userId = $this->request->getAttribute('identity')->getIdentifier();
        
        $LayoutTemplates = $this->fetchTable('SlideLayoutTemplates');
        $template = $LayoutTemplates->find()
            ->where(['id' => $id, 'user_id' => $userId])
            ->first();
        
        if ($template && $LayoutTemplates->delete($template)) {
            $this->Flash->success('Saved layout deleted.');
        } else {
            $this->Flash->error('Saved layout not found.');
        }
        
        return $this->redirect(['action' => 'add', '?' => ['report_id' => $this->request->getQuery('report_id')]]);
    }

    /**
     * Upload slide image to S3
     *
     * @param \Psr\Http\Message\UploadedFileInterface $imageFile Uploaded file
     * @param \App\Model\Entity\Report $report Report entity
     * @param \App\Lib\S3DocumentService $s3Service S3 service
     * @return string|null S3 path or null on failure
     */
    private function uploadSlideImage($imageFile, $report, $s3Service): ?string
    {
        $filename = $imageFile->getClientFilename();
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            return null;
        }
        
        $tmpPath = $imageFile->getStream()->getMetadata('uri');
        
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ];
        
        // Upload original image without resizing
        $fileArray = [
            'tmp_name' => $tmpPath,
            'name' => 'slide_' . uniqid() . '.' . $ext,
            'size' => $imageFile->getSize(),
            'type' => $mimeTypes[$ext] ?? 'application/octet-stream'
        ];
        
        $uploadResult = $s3Service->uploadDocument(
            $fileArray,
            $report->case_id,
            $report->case->patient_id ?? 0,
            'report-images',
            null
        );
        
        if ($uploadResult['success']) {
            return $uploadResult['file_path'];
        }
        
        return null;
    }

    /**
     * Build HTML content for slide preview
     *
     * @param array $slideData Slide data
     * @param array|null $slideConfig Slide configuration
     * @return string HTML content
     */
    private function buildSlideHtml(array $slideData, ?array $slideConfig): string
    {
        $layout = $slideConfig['layout'] ?? 'single_image';
        $columns = $slideData['layout_columns'] ?? 1;
        
        $html = '<div class="slide-content" data-layout="' . h($layout) . '">';
        
        // Title
        if (!empty($slideData['title'])) {
            $html .= '<h2 class="slide-title">' . h($slideData['title']) . '</h2>';
        }
        
        // Subtitle
        if (!empty($slideData['subtitle'])) {
            $html .= '<p class="slide-subtitle">• ' . h($slideData['subtitle']) . '</p>';
        }
        
        if ($columns === 2) {
            $html .= '<div class="slide-columns">';
            
            // Column 1
            $html .= '<div class="slide-column">';
            if (!empty($slideData['col1_header'])) {
                $html .= '<p class="column-header">' . $slideData['col1_header'] . '</p>';
            }
            if ($slideData['col1_type'] === 'image' && !empty($slideData['col1_image_path'])) {
                $html .= '<img src="' . h($slideData['col1_image_path']) . '" class="slide-image" />';
            } elseif (!empty($slideData['col1_content'])) {
                $html .= '<div class="column-text">' . nl2br(h($slideData['col1_content'])) . '</div>';
            }
            $html .= '</div>';
            
            // Column 2
            $html .= '<div class="slide-column">';
            if (!empty($slideData['col2_header'])) {
                $html .= '<p class="column-header">' . $slideData['col2_header'] . '</p>';
            }
            if ($slideData['col2_type'] === 'image' && !empty($slideData['col2_image_path'])) {
                $html .= '<img src="' . h($slideData['col2_image_path']) . '" class="slide-image" />';
            } elseif (!empty($slideData['col2_content'])) {
                $html .= '<div class="column-text">' . nl2br(h($slideData['col2_content'])) . '</div>';
            }
            $html .= '</div>';
            
            $html .= '</div>';
        } else {
            // Single column
            if ($slideData['col1_type'] === 'image' && !empty($slideData['col1_image_path'])) {
                $html .= '<img src="' . h($slideData['col1_image_path']) . '" class="slide-image full-width" />';
            } elseif (!empty($slideData['col1_content'])) {
                $html .= '<div class="slide-text">' . nl2br(h($slideData['col1_content'])) . '</div>';
            }
        }
        
        // Footer
        if (!empty($slideData['footer_text'])) {
            $html .= '<p class="slide-footer">' . h($slideData['footer_text']) . '</p>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Edit method - Edit an existing slide
     *
     * @param int|null $id Slide ID
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        $ReportSlides = $this->fetchTable('ReportSlides');
        $slide = $ReportSlides->get($id, contain: ['Reports']);
        
        // Verify access through case assignment
        $Reports = $this->fetchTable('Reports');
        $report = $Reports->find()
            ->contain(['Cases' => ['PatientUsers']])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $slide->report_id])
            ->first();
            
        if (!$report) {
            $this->Flash->error('You do not have access to edit this slide.');
            return $this->redirect(['controller' => 'Reports', 'action' => 'index']);
        }
        
        if ($slide->isCustomLayout()) {
            return $this->customLayoutForm($report, $slide);
        }
        
        // Get slide type configuration
        $slideTypes = unserialize(PPT_REPORT_PAGES);
        $slideType = $slide->slide_type ?? 'custom';
        $slideConfig = $slideTypes[$slideType] ?? null;
        
        // Get exam procedures for this case
        $CasesExamsProcedures = $this->fetchTable('CasesExamsProcedures');
        $examProcedures = $CasesExamsProcedures->find()
            ->contain(['ExamsProcedures' => ['Exams' => ['Modalities'], 'Procedures']])
            ->where(['CasesExamsProcedures.case_id' => $report->case_id])
            ->all();
        
        // Build dropdown list with complete information
        $examProceduresList = [];
        foreach ($examProcedures as $cep) {
            if (!empty($cep->exams_procedure)) {
                $ep = $cep->exams_procedure;
                $label = '';
                
                // Exam name
                if (!empty($ep->exam)) {
                    $label = $ep->exam->name;
                    
                    // Add modality
                    if (!empty($ep->exam->modality)) {
                        $label .= ' (' . $ep->exam->modality->name . ')';
                    }
                }
                
                // Add procedure name
                if (!empty($ep->procedure)) {
                    if ($label) {
                        $label .= ' - ' . $ep->procedure->name;
                    } else {
                        $label = $ep->procedure->name;
                    }
                }
                
                if ($label) {
                    $examProceduresList[$cep->id] = $label;
                }
            }
        }
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $s3Service = new S3DocumentService();
            // Use slide's stored layout_columns, fallback to config, then default to 1
            $layoutColumns = $slide->layout_columns ?? $slideConfig['columns'] ?? 1;
            
            // Handle Column 1 Image Upload
            $col1ImageFile = $this->request->getData('col1_image_file');
            if ($col1ImageFile && $col1ImageFile->getError() === UPLOAD_ERR_OK) {
                $col1Path = $this->uploadSlideImage($col1ImageFile, $report, $s3Service);
                if ($col1Path) {
                    // Delete old image if exists
                    if ($slide->col1_image_path) {
                        $s3Service->deleteDocument($slide->col1_image_path);
                    }
                    $data['col1_image_path'] = $col1Path;
                }
            }
            
            // Handle Column 2 Image Upload (for two-column layouts)
            $col2ImageFile = $this->request->getData('col2_image_file');
            if ($col2ImageFile && $col2ImageFile->getError() === UPLOAD_ERR_OK) {
                $col2Path = $this->uploadSlideImage($col2ImageFile, $report, $s3Service);
                if ($col2Path) {
                    // Delete old image if exists
                    if ($slide->col2_image_path) {
                        $s3Service->deleteDocument($slide->col2_image_path);
                    }
                    $data['col2_image_path'] = $col2Path;
                }
            }
            
            // Handle Column 3-5 Image Uploads (for multi-image layouts like eeg_meg_discharge)
            foreach ([3, 4, 5] as $colNum) {
                $colImageFile = $this->request->getData("col{$colNum}_image_file");
                if ($colImageFile && $colImageFile->getError() === UPLOAD_ERR_OK) {
                    $colPath = $this->uploadSlideImage($colImageFile, $report, $s3Service);
                    if ($colPath) {
                        // Delete old image if exists
                        $oldPath = $slide->{"col{$colNum}_image_path"};
                        if ($oldPath) {
                            $s3Service->deleteDocument($oldPath);
                        }
                        $data["col{$colNum}_image_path"] = $colPath;
                    }
                }
            }
            
            // Handle legacy single image upload (for backwards compatibility)
            $imageFile = $this->request->getData('image_file');
            if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
                $imagePath = $this->uploadSlideImage($imageFile, $report, $s3Service);
                if ($imagePath) {
                    if ($slide->file_path) {
                        $s3Service->deleteDocument($slide->file_path);
                    }
                    $data['file_path'] = $imagePath;
                    $data['col1_image_path'] = $imagePath; // Also set for new structure
                }
            }
            
            // Set layout columns
            $data['layout_columns'] = $layoutColumns;
            $data['col1_type'] = $slideConfig['col1']['type'] ?? 'image';
            if ($layoutColumns === 2) {
                $data['col2_type'] = $slideConfig['col2']['type'] ?? 'image';
            }
            
            // Handle legend data if present
            if (isset($data['legend_items']) && is_array($data['legend_items'])) {
                $data['legend_data'] = json_encode($data['legend_items']);
            }
            
            // Build HTML content
            $data['html_content'] = $this->buildSlideHtml($data, $slideConfig);
            
            // Store title/description
            $data['title'] = $data['title'] ?? $slideConfig['title'] ?? '';
            $data['description'] = $data['description'] ?? $data['title'];
            
            $slide = $ReportSlides->patchEntity($slide, $data);
            
            if ($ReportSlides->save($slide)) {
                $this->Flash->success('Slide has been updated.');
                return $this->redirect(['action' => 'index', $slide->report_id]);
            }
            
            // Show specific validation errors
            $errors = $slide->getErrors();
            if (!empty($errors)) {
                foreach ($errors as $field => $error) {
                    $errorMessage = is_array($error) ? implode(', ', $error) : $error;
                    $this->Flash->error("Error in {$field}: {$errorMessage}");
                }
            } else {
                $this->Flash->error('Unable to update the slide. Please try again.');
            }
        }
        
        // Get existing image URLs if exist
        $s3Service = new S3DocumentService();
        if ($slide->file_path) {
            $slide->image_url = $s3Service->getDownloadUrl($slide->file_path);
        }
        if ($slide->col1_image_path) {
            $slide->col1_image_url = $s3Service->getDownloadUrl($slide->col1_image_path);
        }
        if ($slide->col2_image_path) {
            $slide->col2_image_url = $s3Service->getDownloadUrl($slide->col2_image_path);
        }
        // Handle col3-col5 for multi-image slides
        foreach ([3, 4, 5] as $colNum) {
            $pathField = "col{$colNum}_image_path";
            if ($slide->{$pathField}) {
                $slide->{"col{$colNum}_image_url"} = $s3Service->getDownloadUrl($slide->{$pathField});
            }
        }
        
        $this->set(compact('slide', 'report', 'examProceduresList', 'slideConfig', 'slideType', 'slideTypes'));
    }

    /**
     * Bulk Upload Images - Upload multiple images at once and auto-match to slides
     * 
     * Files are matched using the PPT_BULK_UPLOAD_NAMES configuration in site_constants.php.
     * Filenames (without extension) are matched case-insensitively to the mapping keys.
     *
     * @param int|null $reportId Report ID
     * @return \Cake\Http\Response JSON response
     */
    public function bulkUploadImages($reportId = null)
    {
        ini_set('upload_max_filesize', '50M');
        ini_set('post_max_size', '50M');
        ini_set('max_file_uploads', '50');
        $this->request->allowMethod(['post']);
        $this->autoRender = false;
        set_time_limit(300); // Allow up to 5 minutes for bulk upload

        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();

        if (!$reportId) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Report ID is required.',
                ]));
        }

        // Verify access
        $Reports = $this->fetchTable('Reports');
        $report = $Reports->find()
            ->contain(['Cases'])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $reportId, 'Reports.type' => 'PPT'])
            ->first();

        if (!$report) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Report not found or access denied.',
                ]));
        }

        // Load filename mapping and slide config
        $nameMap = unserialize(PPT_BULK_UPLOAD_NAMES);
        $slideConfigs = unserialize(PPT_REPORT_PAGES);

        // Get all slides for this report
        $ReportSlides = $this->fetchTable('ReportSlides');
        $slides = $ReportSlides->find()
            ->where(['report_id' => $reportId])
            ->all()
            ->indexBy('slide_type')
            ->toArray();

        $s3Service = new S3DocumentService();
        $results = ['matched' => [], 'skipped' => [], 'errors' => []];
        $uploadedFiles = $this->request->getUploadedFiles();

        // Files come as 'images' array from the form
        $files = $uploadedFiles['images'] ?? [];
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if ($file->getError() !== UPLOAD_ERR_OK) {
                continue;
            }

            $originalName = $file->getClientFilename();
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            // Validate image extension
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $results['skipped'][] = [
                    'file' => $originalName,
                    'reason' => 'Invalid file type. Only JPG, PNG, GIF allowed.',
                ];
                continue;
            }

            // Extract key: remove extension, lowercase, trim
            $nameKey = strtolower(pathinfo($originalName, PATHINFO_FILENAME));
            $nameKey = trim($nameKey);

            // Look up in mapping
            if (!isset($nameMap[$nameKey])) {
                $results['skipped'][] = [
                    'file' => $originalName,
                    'reason' => 'Filename not recognized. Check naming guide.',
                ];
                continue;
            }

            $mapping = $nameMap[$nameKey];
            $slideType = $mapping['slide_type'];
            $column = $mapping['column']; // col1, col2, col3, col4, col5

            // Auto-create slide if it doesn't exist yet
            if (!isset($slides[$slideType])) {
                if (!isset($slideConfigs[$slideType])) {
                    $results['skipped'][] = [
                        'file' => $originalName,
                        'reason' => "Unknown slide type '{$slideType}'.",
                    ];
                    continue;
                }

                $config = $slideConfigs[$slideType];
                $maxOrder = $ReportSlides->find()
                    ->where(['report_id' => $reportId])
                    ->select(['max_order' => $ReportSlides->find()->func()->max('slide_order')])
                    ->first();
                $nextOrder = ($maxOrder->max_order ?? 0) + 1;

                $newSlide = $ReportSlides->newEntity([
                    'report_id' => $reportId,
                    'user_id' => $report->user_id,
                    'slide_order' => $config['order'] ?? $nextOrder,
                    'slide_type' => $slideType,
                    'layout_columns' => $config['columns'] ?? 1,
                    'title' => $config['title'] ?? '',
                    'subtitle' => $config['subtitle'] ?? null,
                    'col1_type' => $config['col1']['type'] ?? 'text',
                    'col1_header' => $config['col1']['header'] ?? null,
                    'col2_type' => $config['col2']['type'] ?? 'text',
                    'col2_header' => $config['col2']['header'] ?? null,
                    'footer_text' => $config['footer_text'] ?? null,
                    'legend_data' => isset($config['legend']['items']) ? json_encode($config['legend']['items']) : null,
                ]);

                if ($ReportSlides->save($newSlide)) {
                    $slides[$slideType] = $newSlide;
                } else {
                    $results['errors'][] = [
                        'file' => $originalName,
                        'reason' => "Failed to create slide for '{$slideType}'.",
                    ];
                    continue;
                }
            }

            $slide = $slides[$slideType];
            $imagePathField = $column . '_image_path';

            // Upload to S3
            $uploadedPath = $this->uploadSlideImage($file, $report, $s3Service);
            if (!$uploadedPath) {
                $results['errors'][] = [
                    'file' => $originalName,
                    'reason' => 'S3 upload failed.',
                ];
                continue;
            }

            // Delete old image if exists
            $oldPath = $slide->{$imagePathField} ?? null;
            if ($oldPath) {
                $s3Service->deleteDocument($oldPath);
            }

            // Update slide
            $slide->{$imagePathField} = $uploadedPath;

            // Also update legacy file_path/s3_key for col1
            if ($column === 'col1') {
                $slide->file_path = $uploadedPath;
                $slide->s3_key = $uploadedPath;
            }

            if ($ReportSlides->save($slide)) {
                $results['matched'][] = [
                    'file' => $originalName,
                    'slide' => str_replace('_', ' ', ucwords($slideType, '_')),
                    'column' => $column,
                ];
            } else {
                $results['errors'][] = [
                    'file' => $originalName,
                    'reason' => 'Failed to save slide update.',
                ];
            }
        }

        $totalFiles = count($results['matched']) + count($results['skipped']) + count($results['errors']);
        $matchedCount = count($results['matched']);

        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'message' => "{$matchedCount} of {$totalFiles} images uploaded successfully.",
                'results' => $results,
            ]));
    }

    /**
     * Delete method
     *
     * @param int|null $id Slide ID
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        $ReportSlides = $this->fetchTable('ReportSlides');
        $slide = $ReportSlides->get($id);
        
        // Prevent deletion of first slide (cover page)
        if ($slide->slide_order === 1) {
            $this->Flash->error('Cannot delete the cover page (first slide).');
            return $this->redirect(['action' => 'index', $slide->report_id]);
        }
        
        // Verify access
        $Reports = $this->fetchTable('Reports');
        $report = $Reports->find()
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $slide->report_id])
            ->first();
            
        if (!$report) {
            $this->Flash->error('You do not have access to delete this slide.');
            return $this->redirect(['controller' => 'Reports', 'action' => 'index']);
        }
        
        $reportId = $slide->report_id;
        
        // Delete image (S3 or local)
        $s3Service = new S3DocumentService();
        if ($slide->file_path) {
            $s3Service->deleteDocument($slide->file_path);
        }
        
        $gridImagePaths = $slide->isCustomLayout() ? SlideGridLayoutService::imagePaths($slide->getLayoutData()) : [];
        
        if ($ReportSlides->delete($slide)) {
            foreach ($gridImagePaths as $gridImagePath) {
                $s3Service->deleteDocument($gridImagePath);
            }
            $this->Flash->success('Slide has been deleted.');
        } else {
            $this->Flash->error('Unable to delete slide. Please try again.');
        }
        
        return $this->redirect(['action' => 'index', $reportId]);
    }

    /**
     * Reorder method - Update slide order via AJAX
     *
     * @return \Cake\Http\Response JSON response
     */
    public function reorder()
    {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setClassName('Json');
        
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $caseId = $input['case_id'] ?? null;
        $slides = $input['slides'] ?? null;
        
        // Fallback to form data for backward compatibility
        if (empty($slides)) {
            $slideIds = $this->request->getData('slide_ids');
            if (!empty($slideIds) && is_array($slideIds)) {
                $slides = array_map(function($id, $index) {
                    return ['id' => $id, 'order' => $index + 1];
                }, $slideIds, array_keys($slideIds));
            }
        }
        
        if (empty($slides) || !is_array($slides)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid slide order data.'
            ]));
        }
        
        $ReportSlides = $this->fetchTable('ReportSlides');
        
        // Get the first slide to verify access
        $firstSlideId = $slides[0]['id'] ?? null;
        if (!$firstSlideId) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'No slides provided.'
            ]));
        }
        
        // Get first slide and verify report access
        $firstSlide = $ReportSlides->get($firstSlideId);
        $Reports = $this->fetchTable('Reports');
        $report = $Reports->find()
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $firstSlide->report_id])
            ->first();
            
        if (!$report) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Access denied.'
            ]));
        }
        
        // Update slide orders - but don't allow moving the cover page (order 1)
        foreach ($slides as $slideData) {
            $slideId = $slideData['id'];
            $newOrder = (int)$slideData['order'];
            
            $slide = $ReportSlides->get($slideId);
            
            // Skip if trying to change cover page order or assign order 1 to non-cover
            if ($slide->slide_order === 1 && $newOrder !== 1) {
                continue; // Don't move cover page
            }
            
            $slide->slide_order = $newOrder;
            $ReportSlides->save($slide);
        }
        
        return $this->response->withStringBody(json_encode([
            'success' => true,
            'message' => 'Slide order updated.'
        ]));
    }

    /**
     * Reset slide to default template values
     * Useful for fixing slides that were created before template updates
     *
     * @param int|null $id Slide ID
     * @return \Cake\Http\Response|null Redirects
     */
    public function resetSlide($id = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        $ReportSlides = $this->fetchTable('ReportSlides');
        $slide = $ReportSlides->get($id, contain: ['Reports']);
        
        // Verify access through case assignment
        $Reports = $this->fetchTable('Reports');
        $report = $Reports->find()
            ->contain(['Cases' => ['PatientUsers' => ['Patient']], 'Users'])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $slide->report_id])
            ->first();
            
        if (!$report) {
            $this->Flash->error('You do not have access to reset this slide.');
            return $this->redirect(['controller' => 'Reports', 'action' => 'index']);
        }
        
        // Get slide type configuration
        $slideTypes = unserialize(PPT_REPORT_PAGES);
        $slideType = $slide->slide_type;
        $slideConfig = $slideTypes[$slideType] ?? null;
        
        if (!$slideConfig) {
            $this->Flash->error('Cannot reset: Unknown slide type.');
            return $this->redirect(['action' => 'index', $slide->report_id]);
        }
        
        // Reset slide data from template
        $slide->title = $slideConfig['title'] ?? '';
        $slide->subtitle = $slideConfig['subtitle'] ?? null;
        $slide->layout_columns = $slideConfig['columns'] ?? 1;
        $slide->col1_header = $slideConfig['col1']['header'] ?? null;
        $slide->col2_header = $slideConfig['col2']['header'] ?? null;
        $slide->footer_text = $slideConfig['footer_text'] ?? null;
        $slide->legend_data = isset($slideConfig['legend']['items']) ? json_encode($slideConfig['legend']['items']) : null;
        
        // Handle special content types
        if ($slideType === 'cover_page') {
            // Rebuild cover page
            $coverData = $this->buildCoverSlideData($report, $slideConfig, $slide->slide_order);
            $slide->description = $coverData['description'];
            $slide->html_content = $coverData['html_content'];
        } elseif (isset($slideConfig['default_sections'])) {
            // Structured bullets
            $slide->col1_content = json_encode($slideConfig['default_sections']);
        } elseif (isset($slideConfig['col1']['default_content'])) {
            // Default text content
            $slide->col1_content = $slideConfig['col1']['default_content'];
        } elseif (isset($slideConfig['header_text']['content'])) {
            // Header text content
            $headerContent = $slideConfig['header_text']['content'];
            if (is_array($headerContent)) {
                $slide->col1_content = implode("\n", $headerContent);
            } else {
                $slide->col1_content = $headerContent;
            }
        }
        
        // Rebuild HTML content
        $slideData = $slide->toArray();
        $slide->html_content = $this->buildSlideHtml($slideData, $slideConfig);
        
        if ($ReportSlides->save($slide)) {
            $this->Flash->success('Slide reset to default template values.');
        } else {
            $this->Flash->error('Failed to reset slide.');
        }
        
        return $this->redirect(['action' => 'index', $slide->report_id]);
    }

    /**
     * Download PowerPoint presentation
     *
     * @param int|null $reportId Report ID
     * @return \Cake\Http\Response
     * @throws \Cake\Http\Exception\NotFoundException When record not found.
     */
    public function downloadPpt($reportId = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        $Reports = $this->fetchTable('Reports');
        $report = $Reports->get($reportId, contain: ['Cases']);
        
        // Verify access through case assignment
        $hasAccess = $this->fetchTable('CaseAssignments')->exists([
            'case_id' => $report->case_id,
            'assigned_to' => $userId
        ]);
        
        if (!$hasAccess) {
            throw new NotFoundException(__('Report not found or you do not have access to it.'));
        }
        
        // Use shared trait method for PPT generation
        return $this->downloadPptReadOnly($reportId);
    }

    /**
     * Create the patient cover page - the only slide a new report starts with
     *
     * @param \App\Model\Entity\Report $report Report entity (with Cases.PatientUsers.Patient)
     * @return void
     */
    private function createCoverPage($report): void
    {
        $slideConfig = unserialize(PPT_REPORT_PAGES)['cover_page'];
        $ReportSlides = $this->fetchTable('ReportSlides');
        $ReportSlides->save($ReportSlides->newEntity($this->buildCoverSlideData($report, $slideConfig, 1)));
    }
    
    /**
     * Build cover slide data with patient information
     *
     * @param \App\Model\Entity\Report $report Report entity
     * @param array $slideConfig Slide configuration
     * @param int $slideOrder Slide order number
     * @return array Slide data
     */
    private function buildCoverSlideData($report, $slideConfig, $slideOrder)
    {
        $patientUser = $report->case->patient_user ?? null;
        $case = $report->case ?? null;
        $patient = $patientUser ? ($patientUser->patient ?? null) : null;
        
        // Format patient name
        $firstName = $patientUser->first_name ?? '';
        $lastName = $patientUser->last_name ?? '';
        $patientName = trim($lastName . ', ' . $firstName);
        if (empty($patientName) || $patientName === ',') {
            $patientName = 'Last, First';
        }
        
        // Format date of birth
        $dob = 'xx/xx/xxxx';
        if ($patient && $patient->dob) {
            $dob = $patient->dob->format('m/d/Y');
        }
        
        // Get MRN and FIN
        $mrn = ($patient && $patient->medical_record_number) ? $patient->medical_record_number : 'xxx';
        $fin = ($patient && $patient->financial_record_number) ? $patient->financial_record_number : 'xxx';
        
        // Format study date
        $studyDate = $case && $case->date ? $case->date->format('m/d/Y') : ($case && $case->created ? $case->created->format('m/d/Y') : 'xx/xx/xxxx');
        
        // Get referring physician
        $doctorUser = $report->user ?? null;
        $referringPhysician = 'Not specified';
        if ($doctorUser) {
            $doctorFirstName = $doctorUser->first_name ?? '';
            $doctorLastName = $doctorUser->last_name ?? '';
            $referringPhysician = trim($doctorFirstName . ' ' . $doctorLastName);
            if (empty($referringPhysician)) {
                $referringPhysician = 'Not specified';
            }
        }
        
        // MEG ID
        $megId = 'CASE_' . str_pad((string)($case ? $case->id : 0), 6, 'X', STR_PAD_LEFT);
        
        // Get age and gender
        $age = '';
        $gender = '';
        if ($patient) {
            if ($patient->dob) {
                $now = new \DateTime();
                $dobDateTime = new \DateTime($patient->dob->format('Y-m-d'));
                $diff = $now->diff($dobDateTime);
                $age = $diff->y . ' years old';
            }
            $gender = match($patient->gender ?? '') {
                'M' => 'Male',
                'F' => 'Female',
                'O' => 'Other',
                default => ''
            };
        }
        
        // Get ASMs
        $asms = $case ? ($case->asms ?? 'None listed') : 'None listed';
        
        // Build cover page content
        $coverHeading = $slideConfig['title'] ?? "Magnetoencephalography Report (MEG)";
        
        $coverContent = "Name: {$patientName}\n";
        $coverContent .= "Date of Birth: {$dob}\n";
        $coverContent .= "MRN: {$mrn}; FIN: {$fin}\n";
        $coverContent .= "Date of Study: {$studyDate}\n";
        $coverContent .= "Referring Physician: {$referringPhysician}\n";
        $coverContent .= "MEG ID: {$megId}\n\n\n\n\n";
        $coverContent .= "MEG performed without sedation\n";
        $coverContent .= "{$age} {$gender}\n";
        $coverContent .= "ASMs: {$asms}";
        
        // Build HTML content
        $htmlContent = '<div class="slide-content" style="text-align: center;">';
        $htmlContent .= '<h2 style="font-size: 24px;">' . h($coverHeading) . '</h2>';
        $htmlContent .= '<p style="font-size: 16px;">' . nl2br(h($coverContent)) . '</p>';
        $htmlContent .= '</div>';
        
        $fullDescription = $coverHeading . "\n\n\n" . $coverContent;
        
        return [
            'report_id' => $report->id,
            'user_id' => $report->user_id,
            'slide_order' => $slideOrder,
            'slide_type' => 'cover_page',
            'layout_columns' => 1,
            'title' => 'Cover Page',
            'subtitle' => null,
            'col1_type' => 'text',
            'col1_content' => null,
            'col1_image_path' => null,
            'col1_header' => null,
            'col2_type' => 'text',
            'col2_content' => null,
            'col2_image_path' => null,
            'col2_header' => null,
            'footer_text' => null,
            'legend_data' => null,
            'description' => $fullDescription,
            'html_content' => $htmlContent,
        ];
    }
    
    /**
     * Create cover slide with patient information (Legacy - kept for backward compatibility)
     *
     * @param \App\Model\Entity\Report $report Report entity
     * @return void
     */
    private function createCoverSlide($report)
    {
        $ReportSlides = $this->fetchTable('ReportSlides');
        
        // Get patient and case information
        $patientUser = $report->case->patient_user ?? null;
        $case = $report->case ?? null;
        
        if (!$patientUser || !$case) {
            return;
        }
        
        // Get the patient record (has medical details)
        $patient = $patientUser->patient ?? null;
        
        // Format patient name from User table
        $firstName = $patientUser->first_name ?? '';
        $lastName = $patientUser->last_name ?? '';
        $patientName = trim($lastName . ', ' . $firstName);
        if (empty($patientName) || $patientName === ',') {
            $patientName = 'Last, First';
        }
        
        // Format date of birth from Patient table
        $dob = 'xx/xx/xxxx';
        if ($patient && $patient->dob) {
            $dob = $patient->dob->format('m/d/Y');
        }
        
        // Get MRN and FIN from Patient table
        $mrn = ($patient && $patient->medical_record_number) ? $patient->medical_record_number : 'xxx';
        $fin = ($patient && $patient->financial_record_number) ? $patient->financial_record_number : 'xxx';
        
        // Format study date from Case
        $studyDate = $case->date ? $case->date->format('m/d/Y') : ($case->created ? $case->created->format('m/d/Y') : 'xx/xx/xxxx');
        
        // Get referring physician - use doctor's name from report user
        $doctorUser = $report->user ?? null;
        $referringPhysician = 'Not specified';
        if ($doctorUser) {
            $doctorFirstName = $doctorUser->first_name ?? '';
            $doctorLastName = $doctorUser->last_name ?? '';
            $referringPhysician = trim($doctorFirstName . ' ' . $doctorLastName);
            if (empty($referringPhysician)) {
                $referringPhysician = 'Not specified';
            }
        }
        
        // MEG ID (case ID)
        $megId = 'CASE_' . str_pad((string)$case->id, 6, 'X', STR_PAD_LEFT);
        
        // Get age and gender from Patient table
        $age = '';
        $gender = '';
        if ($patient) {
            if ($patient->dob) {
                $now = new \DateTime();
                $dobDateTime = new \DateTime($patient->dob->format('Y-m-d'));
                $diff = $now->diff($dobDateTime);
                $age = $diff->y . ' years old';
            }
            $gender = match($patient->gender ?? '') {
                'M' => 'Male',
                'F' => 'Female',
                'O' => 'Other',
                default => ''
            };
        }
        
        // Get ASMs from Case (if you have this field, otherwise leave empty)
        $asms = $case->asms ?? 'None listed';
        
        // Build cover page content - separate heading from body
        $coverHeading = "Magnetoencephalography Report (MEG)";
        
        $coverContent = "Name: {$patientName}\n";
        $coverContent .= "Date of Birth: {$dob}\n";
        $coverContent .= "MRN: {$mrn}; FIN: {$fin}\n";
        $coverContent .= "Date of Study: {$studyDate}\n";
        $coverContent .= "Referring Physician: {$referringPhysician}\n";
        $coverContent .= "MEG ID: {$megId}\n\n\n\n\n";
        $coverContent .= "MEG performed without sedation\n";
        $coverContent .= "{$age} {$gender}\n";
        $coverContent .= "ASMs: {$asms}";
        
        // Build HTML content with center alignment
        $htmlContent = '<div class="slide-content" style="text-align: center;">';
        $htmlContent .= '<h2 style="font-size: 24px;">' . h($coverHeading) . '</h2>';
        $htmlContent .= '<p style="font-size: 16px;">' . nl2br(h($coverContent)) . '</p>';
        $htmlContent .= '</div>';
        
        // Store full text for description (for PowerPoint generation)
        $fullDescription = $coverHeading . "\n\n\n" . $coverContent;
        
        // Create the cover slide
        $slide = $ReportSlides->newEntity([
            'report_id' => $report->id,
            'user_id' => $report->user_id,
            'slide_order' => 1,
            'title' => 'Cover Page',
            'description' => $fullDescription,
            'html_content' => $htmlContent,
            'file_path' => null,
            's3_key' => null,
            'original_filename' => null,
            'mime_type' => null,
            'file_size' => null
        ]);
        
        $ReportSlides->save($slide);
    }

    /**
     * Paste Image - Handle pasted images from clipboard and upload to specified slide/column
     *
     * @param int|null $reportId Report ID
     * @return \Cake\Http\Response JSON response
     */
    public function pasteImage($reportId = null)
    {
        $this->request->allowMethod(['post']);
        $this->autoRender = false;

        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();

        if (!$reportId) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Report ID is required.',
                ]));
        }

        // Verify access
        $Reports = $this->fetchTable('Reports');
        $report = $Reports->find()
            ->contain(['Cases'])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $reportId, 'Reports.type' => 'PPT'])
            ->first();

        if (!$report) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Report not found or access denied.',
                ]));
        }

        $data = $this->request->getData();
        $slideType = $data['slide_type'] ?? null;
        $column = $data['column'] ?? 'col1'; // col1, col2, col3, col4, col5
        $imageData = $data['image_data'] ?? null; // base64 encoded image

        // Custom layout slides are addressed by slide id + flat cell index instead of slide type
        $customSlideId = $data['slide_id'] ?? null;
        $cellIndex = (int)($data['cell'] ?? 0);

        if ((!$slideType && !$customSlideId) || !$imageData) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Slide type and image data are required.',
                ]));
        }

        $ReportSlides = $this->fetchTable('ReportSlides');

        if ($customSlideId) {
            $slide = $ReportSlides->find()
                ->where([
                    'id' => $customSlideId,
                    'report_id' => $reportId,
                    'slide_type' => SlideGridLayoutService::SLIDE_TYPE,
                ])
                ->first();

            if (!$slide || !isset(SlideGridLayoutService::cells($slide->getLayoutData())[$cellIndex])) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Selected slide or box was not found.',
                    ]));
            }
        } else {
            // Find or create the slide for this report and type
            $slideTypes = unserialize(PPT_REPORT_PAGES);
            if (empty($slideTypes[$slideType])) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Invalid slide type selected.',
                    ]));
            }

            $slide = $ReportSlides->find()
                ->where(['report_id' => $reportId, 'slide_type' => $slideType])
                ->first();

            if (!$slide) {
                $slideConfig = $slideTypes[$slideType];
                $maxOrder = $ReportSlides->find()
                    ->where(['report_id' => $reportId])
                    ->select(['max_order' => $ReportSlides->find()->func()->max('slide_order')])
                    ->first();
                $nextOrder = ($maxOrder->max_order ?? 0) + 1;

                $slide = $ReportSlides->newEntity([
                    'report_id' => $reportId,
                    'user_id' => $report->user_id,
                    'slide_order' => $slideConfig['order'] ?? $nextOrder,
                    'slide_type' => $slideType,
                    'layout_columns' => $slideConfig['columns'] ?? 1,
                    'title' => $slideConfig['title'] ?? '',
                    'subtitle' => $slideConfig['subtitle'] ?? null,
                    'col1_type' => $slideConfig['col1']['type'] ?? 'text',
                    'col1_header' => $slideConfig['col1']['header'] ?? null,
                    'col2_type' => $slideConfig['col2']['type'] ?? 'text',
                    'col2_header' => $slideConfig['col2']['header'] ?? null,
                    'footer_text' => $slideConfig['footer_text'] ?? null,
                    'legend_data' => isset($slideConfig['legend']['items']) ? json_encode($slideConfig['legend']['items']) : null,
                    'file_path' => null,
                    's3_key' => null,
                ]);

                if (!$ReportSlides->save($slide)) {
                    return $this->response
                        ->withType('application/json')
                        ->withStringBody(json_encode([
                            'success' => false,
                            'message' => 'Unable to create slide for selected type.',
                        ]));
                }
            }
        }

        // Decode base64 image
        $imageData = str_replace('data:image/png;base64,', '', $imageData);
        $imageData = str_replace('data:image/jpeg;base64,', '', $imageData);
        $imageData = str_replace('data:image/gif;base64,', '', $imageData);
        $imageData = str_replace('data:image/jpg;base64,', '', $imageData);
        $imageData = str_replace(' ', '+', $imageData);

        $imageBinary = base64_decode($imageData);
        if (!$imageBinary) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Invalid image data.',
                ]));
        }

        // Create temporary file
        $tempFile = tmpfile();
        $tempPath = stream_get_meta_data($tempFile)['uri'];
        fwrite($tempFile, $imageBinary);

        // Create file array for S3 upload
        $fileArray = [
            'tmp_name' => $tempPath,
            'name' => 'pasted_image_' . uniqid() . '.png',
            'size' => strlen($imageBinary),
            'type' => 'image/png'
        ];

        // Upload to S3 directly
        $s3Service = new S3DocumentService();
        $uploadResult = $s3Service->uploadDocument(
            $fileArray,
            $report->case_id,
            $report->case->patient_id ?? 0,
            'report-images',
            null
        );

        // Close temp file
        fclose($tempFile);

        if (!$uploadResult['success']) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to upload image: ' . ($uploadResult['error'] ?? 'Unknown error'),
                ]));
        }

        $uploadedPath = $uploadResult['file_path'];

        if (!$uploadedPath) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Upload succeeded but no file path returned.',
                ]));
        }

        if ($customSlideId) {
            $layout = $slide->getLayoutData();
            $oldPath = SlideGridLayoutService::setCellImage($layout, $cellIndex, $uploadedPath);
            $slide->layout_data = json_encode($layout);
        } else {
            $imagePathField = $column . '_image_path';
            $oldPath = $slide->{$imagePathField} ?? null;

            // Update slide
            $slide->{$imagePathField} = $uploadedPath;

            // Also update legacy file_path/s3_key for col1
            if ($column === 'col1') {
                $slide->file_path = $uploadedPath;
                $slide->s3_key = $uploadedPath;
            }
        }

        // Delete old image if exists
        if ($oldPath) {
            $s3Service->deleteDocument($oldPath);
        }

        if ($ReportSlides->save($slide)) {
            $imageUrl = $uploadedPath ? $s3Service->getDownloadUrl($uploadedPath) : '';
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'message' => 'Image uploaded successfully.',
                    'image_url' => $imageUrl,
                    'slide_id' => $slide->id,
                    'column' => $column,
                ]));
        } else {
            // Clean up uploaded file if save failed
            $s3Service->deleteDocument($uploadedPath);
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Failed to save slide changes.',
                ]));
        }
    }
}
