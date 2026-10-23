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
        
        // Signed image URLs for custom layout slides
        $s3Service = new S3DocumentService();
        foreach ($slides as $slide) {
            if ($slide->isCustomLayout()) {
                $slide->grid_layout = SlideGridLayoutService::withImageUrls($slide->getLayoutData(), $s3Service);
            }
        }
        
        // This doctor's whole-report templates (save / start-from-template on the slides page)
        $reportTemplates = $this->fetchTable('ReportTemplates')->find()
            ->where(['user_id' => $userId])
            ->orderBy(['name' => 'ASC'])
            ->all();
        
        $this->set(compact('slides', 'report', 'reportId', 'reportTemplates'));
    }

    /**
     * Add method - choose a layout (or a saved layout), then fill it in
     *
     * Slides are only created as custom grid layouts; the old fixed slide types can no longer be added.
     *
     * @param int|null $reportId Report ID
     * @return \Cake\Http\Response|null|void Renders the layout picker or the custom layout form
     */
    public function add($reportId = null)
    {
        $userId = $this->request->getAttribute('identity')->getIdentifier();
        $reportId = $reportId ?? $this->request->getQuery('report_id');
        
        if (!$reportId) {
            $this->Flash->error('Please select a report.');
            return $this->redirect(['controller' => 'Reports', 'action' => 'index']);
        }
        
        // Verify access to this report
        $report = $this->fetchTable('Reports')->find()
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
        
        if ($this->request->getQuery('slide_type') === SlideGridLayoutService::SLIDE_TYPE) {
            return $this->customLayoutForm($report);
        }
        
        // Layout matrix + this doctor's saved layouts
        $gridPresets = SlideGridLayoutService::presets();
        $layoutTemplates = $this->fetchTable('SlideLayoutTemplates')->find()
            ->where(['user_id' => $userId])
            ->orderBy(['name' => 'ASC'])
            ->all();
        
        $this->set(compact('report', 'reportId', 'gridPresets', 'layoutTemplates'));
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
     * Edit method - Edit an existing slide in the custom layout editor
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
        
        // The patient cover page is generated from the case: refresh it instead of editing
        if ($slide->slide_type === 'cover_page') {
            $this->Flash->info('The cover page is filled in from the case. Use "Refresh from case" on the cover to update it.');
            return $this->redirect(['action' => 'index', $slide->report_id]);
        }
        
        if (!$slide->isCustomLayout()) {
            $this->Flash->error('This slide cannot be edited.');
            return $this->redirect(['action' => 'index', $slide->report_id]);
        }
        
        return $this->customLayoutForm($report, $slide);
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
        
        $s3Service = new S3DocumentService();
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
     * Refresh the patient cover page from the current case data
     *
     * @param int|null $id Cover slide ID
     * @return \Cake\Http\Response|null Redirects
     */
    public function resetSlide($id = null)
    {
        $this->request->allowMethod(['post']);
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
            $this->Flash->error('You do not have access to this slide.');
            return $this->redirect(['controller' => 'Reports', 'action' => 'index']);
        }
        
        if ($slide->slide_type !== 'cover_page') {
            $this->Flash->error('Only the cover page can be refreshed from the case.');
            return $this->redirect(['action' => 'index', $slide->report_id]);
        }
        
        $coverData = $this->buildCoverSlideData($report, $slide->slide_order);
        $slide->title = $coverData['title'];
        $slide->description = $coverData['description'];
        $slide->html_content = $coverData['html_content'];
        
        if ($ReportSlides->save($slide)) {
            $this->Flash->success('Cover page refreshed from the case.');
        } else {
            $this->Flash->error('Failed to refresh the cover page.');
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
        $ReportSlides = $this->fetchTable('ReportSlides');
        $ReportSlides->save($ReportSlides->newEntity($this->buildCoverSlideData($report, 1)));
    }
    
    /**
     * Build cover slide data with patient information
     *
     * @param \App\Model\Entity\Report $report Report entity
     * @param array $slideConfig Slide configuration
     * @param int $slideOrder Slide order number
     * @return array Slide data
     */
    private function buildCoverSlideData($report, $slideOrder)
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
        $coverHeading = PPT_COVER_TITLE;
        
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
            'description' => $fullDescription,
            'html_content' => $htmlContent,
        ];
    }
    
}
