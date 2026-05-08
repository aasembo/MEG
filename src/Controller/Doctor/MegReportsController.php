<?php
declare(strict_types=1);

namespace App\Controller\Doctor;

use App\Controller\AppController;
use App\Controller\Trait\PptDownloadTrait;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use App\Lib\S3DocumentService;

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
                    $report = $Reports->get($reportId, [
                        'contain' => ['Cases' => ['PatientUsers' => ['Patient']], 'Users']
                    ]);
                    
                    // Create all default slides from configuration
                    $this->createDefaultSlides($report);
                    
                    $this->Flash->success('MEG Report created with all default slides. You can now edit each slide to add images and customize content.');
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
            ->order(['slide_order' => 'ASC'])
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
        }
        
        // Get available slide types from configuration
        $slideTypes = unserialize(PPT_REPORT_PAGES);
        $slideCategories = unserialize(PPT_SLIDE_CATEGORIES);
        
        $this->set(compact('slides', 'report', 'reportId', 'slideTypes', 'slideCategories'));
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
        
        $this->set(compact('slide', 'report', 'reportId', 'examProceduresList', 'slideType', 'slideConfig', 'slideTypes', 'slideCategories'));
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
        $slide = $ReportSlides->get($id, ['contain' => ['Reports']]);
        
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

        // If no slides exist at all, create all default slides first
        if (empty($slides)) {
            // Reload report with associations needed for createDefaultSlides
            $report = $Reports->get($reportId, contain: ['Cases' => ['PatientUsers' => ['Patient']], 'Users']);
            $this->createDefaultSlides($report);

            // Re-fetch slides after creation
            $slides = $ReportSlides->find()
                ->where(['report_id' => $reportId])
                ->all()
                ->indexBy('slide_type')
                ->toArray();
        }

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
        if ($slide->file_path) {
            $s3Service = new S3DocumentService();
            $s3Service->deleteDocument($slide->file_path);
        }
        
        if ($ReportSlides->delete($slide)) {
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
        $slide = $ReportSlides->get($id, ['contain' => ['Reports']]);
        
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
        $report = $Reports->get($reportId, ['contain' => ['Cases']]);
        
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
     * Create cover slide with patient and study information
     *
     * @param \App\Model\Entity\Report $report Report entity
     * @return void
     */
    private function createDefaultSlides($report)
    {
        $ReportSlides = $this->fetchTable('ReportSlides');
        
        // Get all slide types from configuration
        $slideTypes = unserialize(PPT_REPORT_PAGES);
        
        // Sort by order
        uasort($slideTypes, function($a, $b) {
            return ($a['order'] ?? 999) - ($b['order'] ?? 999);
        });
        
        // Get patient and case information for cover page
        $patientUser = $report->case->patient_user ?? null;
        $case = $report->case ?? null;
        $patient = $patientUser ? ($patientUser->patient ?? null) : null;
        
        $slideOrder = 1;
        
        foreach ($slideTypes as $slideTypeKey => $slideConfig) {
            $slideData = [
                'report_id' => $report->id,
                'user_id' => $report->user_id,
                'slide_order' => $slideOrder,
                'slide_type' => $slideTypeKey,
                'layout_columns' => $slideConfig['columns'] ?? 1,
                'title' => $slideConfig['title'] ?? '',
                'subtitle' => $slideConfig['subtitle'] ?? null,
                'col1_type' => $slideConfig['col1']['type'] ?? 'text',
                'col1_content' => null,
                'col1_image_path' => null,
                'col1_header' => $slideConfig['col1']['header'] ?? null,
                'col2_type' => $slideConfig['col2']['type'] ?? 'text',
                'col2_content' => null,
                'col2_image_path' => null,
                'col2_header' => $slideConfig['col2']['header'] ?? null,
                'footer_text' => $slideConfig['footer_text'] ?? null,
                'legend_data' => isset($slideConfig['legend']['items']) ? json_encode($slideConfig['legend']['items']) : null,
                'description' => null,
                'html_content' => null,
            ];
            
            // Handle special slide types
            if ($slideTypeKey === 'cover_page') {
                // Cover page with patient data
                $slideData = $this->buildCoverSlideData($report, $slideConfig, $slideOrder);
            } elseif (isset($slideConfig['default_sections'])) {
                // Slides with structured bullets (summary, original_eeg_signals_text, etc.)
                $slideData['col1_content'] = json_encode($slideConfig['default_sections']);
            } elseif (isset($slideConfig['col1']['default_content'])) {
                // Text slide with default content
                $slideData['col1_content'] = $slideConfig['col1']['default_content'];
            } elseif (isset($slideConfig['header_text']['content'])) {
                // Slides with header text content
                $headerContent = $slideConfig['header_text']['content'];
                if (is_array($headerContent)) {
                    $slideData['col1_content'] = implode("\n", $headerContent);
                } else {
                    $slideData['col1_content'] = $headerContent;
                }
            }
            
            // Build HTML content for preview
            $slideData['html_content'] = $this->buildSlideHtml($slideData, $slideConfig);
            
            $slide = $ReportSlides->newEntity($slideData);
            $ReportSlides->save($slide);
            
            $slideOrder++;
        }
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

        if (!$slideType || !$imageData) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Slide type and image data are required.',
                ]));
        }

        $slideTypes = unserialize(PPT_REPORT_PAGES);
        if (empty($slideTypes[$slideType])) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Invalid slide type selected.',
                ]));
        }

        // Find or create the slide for this report and type
        $ReportSlides = $this->fetchTable('ReportSlides');
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

        // Delete old image if exists
        $imagePathField = $column . '_image_path';
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
