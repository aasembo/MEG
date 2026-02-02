<?php
declare(strict_types=1);

namespace App\Controller\Doctor;

use App\Controller\AppController;
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
                    
                    // Create first slide with cover page information
                    $this->createCoverSlide($report);
                    
                    $this->Flash->success('MEG Report created successfully with cover page. You can now add more slides.');
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
        
        $this->set(compact('slide', 'report', 'examProceduresList', 'slideConfig', 'slideType', 'slideTypes'));
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
        
        $slideIds = $this->request->getData('slide_ids');
        
        if (empty($slideIds) || !is_array($slideIds)) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Invalid slide order data.'
            ]));
        }
        
        $ReportSlides = $this->fetchTable('ReportSlides');
        
        // Get the first slide to check if it's being reordered
        $firstSlideCheck = $ReportSlides->get($slideIds[0]);
        if ($firstSlideCheck->slide_order === 1) {
            return $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'Cannot reorder the cover page (first slide).'
            ]));
        }
        
        // Verify access to first slide's report
        $firstSlide = $ReportSlides->get($slideIds[0]);
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
        
        // Update slide orders
        foreach ($slideIds as $index => $slideId) {
            $slide = $ReportSlides->get($slideId);
            $slide->slide_order = $index + 1;
            $ReportSlides->save($slide);
        }
        
        return $this->response->withStringBody(json_encode([
            'success' => true,
            'message' => 'Slide order updated.'
        ]));
    }

    /**
     * Resize image to fit within max dimensions while maintaining aspect ratio
     *
     * @param string $sourcePath Source image path
     * @param string $destPath Destination image path
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @param string $ext File extension
     * @return bool Success
     */
    /**
     * Download image from URL to temporary file
     *
     * @param string $imageUrl The URL of the image
     * @return string|null Path to temp file or null on failure
     */
    private function downloadTempImage(string $imageUrl): ?string
    {
        if (empty($imageUrl)) {
            return null;
        }
        
        try {
            if (strpos($imageUrl, 'http') === 0) {
                // S3 or remote URL - download to temp file
                $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $tempImage = TMP . 'ppt_img_' . uniqid() . '.' . $ext;
                $imageContent = @file_get_contents($imageUrl);
                if ($imageContent !== false) {
                    file_put_contents($tempImage, $imageContent);
                    return $tempImage;
                }
            } else {
                // Local path
                $localPath = WWW_ROOT . ltrim($imageUrl, '/');
                if (file_exists($localPath)) {
                    return $localPath;
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail
            \Cake\Log\Log::error('Failed to download image for PPT: ' . $e->getMessage());
        }
        
        return null;
    }

    private function resizeImage(string $sourcePath, string $destPath, int $maxWidth, int $maxHeight, string $ext): bool
    {
        // Get original dimensions
        list($originalWidth, $originalHeight) = getimagesize($sourcePath);
        
        // If image is smaller, don't resize
        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            return copy($sourcePath, $destPath);
        }
        
        // Calculate aspect ratio
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = (int)($originalWidth * $ratio);
        $newHeight = (int)($originalHeight * $ratio);
        
        // Create image from source
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case 'png':
                $source = imagecreatefrompng($sourcePath);
                break;
            case 'gif':
                $source = imagecreatefromgif($sourcePath);
                break;
            default:
                return false;
        }
        
        if (!$source) {
            return false;
        }
        
        // Create resized image
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($ext === 'png' || $ext === 'gif') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        // Apply sharpening
        $sharpenMatrix = [[-1, -1, -1], [-1, 16, -1], [-1, -1, -1]];
        $divisor = array_sum(array_map('array_sum', $sharpenMatrix));
        imageconvolution($resized, $sharpenMatrix, $divisor, 0);
        
        // Save resized image
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $result = imagejpeg($resized, $destPath, 100);
                break;
            case 'png':
                $result = imagepng($resized, $destPath, 0);
                break;
            case 'gif':
                $result = imagegif($resized, $destPath);
                break;
            default:
                $result = false;
        }
        
        imagedestroy($source);
        imagedestroy($resized);
        
        return $result;
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
        
        // Get all slides for this report
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
        }
        
        // Create PowerPoint presentation
        $presentation = new \PhpOffice\PhpPresentation\PhpPresentation();
        $presentation->removeSlideByIndex(0); // Remove default slide
        
        // Load PPT styles from configuration
        $pptStyles = unserialize(PPT_STYLES);
        
        // Get slide dimensions from config
        $slideWidth = $pptStyles['slide']['width'] ?? 960;
        $slideHeight = $pptStyles['slide']['height'] ?? 540;
        $margin = 20; // Side margins
        $topMargin = 15;
        
        // Track temp files for cleanup after PowerPoint is generated
        $tempFiles = [];
        
        foreach ($slides as $index => $slide) {
            $pptSlide = $presentation->createSlide();
            
            // Set slide background to white
            $background = $pptSlide->getBackground();
            if ($background) {
                $background->setColor(new \PhpOffice\PhpPresentation\Style\Color('FFFFFFFF'));
            }
            
            // Get slide title - use title field, fallback to description
            $slideTitle = $slide->title ?: $slide->description ?: '';
            $layoutColumns = $slide->layout_columns ?? 1;
            
            // First slide (cover) gets special formatting
            if ($index === 0) {
                // Cover slide
                $lines = explode("\n", $slide->description ?: $slideTitle);
                $heading = array_shift($lines);
                $content = implode("\n", array_slice($lines, 2));
                
                // Add heading - centered
                $headingShape = $pptSlide->createRichTextShape();
                $headingShape->setHeight(60);
                $headingShape->setWidth($slideWidth - ($margin * 2));
                $headingShape->setOffsetX($margin);
                $headingShape->setOffsetY(80);
                $headingShape->getActiveParagraph()->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_CENTER);
                
                $headingRun = $headingShape->createTextRun($heading);
                $headingRun->getFont()
                    ->setSize(28)
                    ->setBold(true)
                    ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                
                // Add content below heading
                if (!empty($content)) {
                    $textShape = $pptSlide->createRichTextShape();
                    $textShape->setHeight($slideHeight - 180);
                    $textShape->setWidth($slideWidth - ($margin * 2));
                    $textShape->setOffsetX($margin);
                    $textShape->setOffsetY(160);
                    $textShape->getActiveParagraph()->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_CENTER);
                    
                    $textRun = $textShape->createTextRun($content);
                    $textRun->getFont()
                        ->setSize(14)
                        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                }
            } else {
                // Regular slides - track vertical position
                $currentY = $topMargin;
                
                // Add title at top
                if (!empty($slideTitle)) {
                    $titleHeight = $pptStyles['title']['height'] ?? 38;
                    $titleMarginBottom = $pptStyles['title']['margin_bottom'] ?? 4;
                    $titleFontFamily = $pptStyles['title']['font_family'] ?? 'Calibri';
                    
                    $titleShape = $pptSlide->createRichTextShape();
                    $titleShape->setHeight($titleHeight);
                    $titleShape->setWidth($slideWidth - ($margin * 2));
                    $titleShape->setOffsetX($margin);
                    $titleShape->setOffsetY($currentY);
                    
                    $titleRun = $titleShape->createTextRun($slideTitle);
                    $titleRun->getFont()
                        ->setName($titleFontFamily)
                        ->setSize($pptStyles['title']['font_size'] ?? 29)
                        ->setBold($pptStyles['title']['font_bold'] ?? true)
                        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF' . ($pptStyles['title']['font_color'] ?? '000000')));
                    
                    $currentY += $titleHeight + $titleMarginBottom;
                }
                
                // Add subtitle if present (as bullet point)
                if (!empty($slide->subtitle)) {
                    $subtitleHeight = $pptStyles['subtitle']['height'] ?? 28;
                    $subtitleMarginBottom = $pptStyles['subtitle']['margin_bottom'] ?? 10;
                    $subtitleFontFamily = $pptStyles['subtitle']['font_family'] ?? 'Calibri';
                    
                    $subtitleShape = $pptSlide->createRichTextShape();
                    $subtitleShape->setHeight($subtitleHeight);
                    $subtitleShape->setWidth($slideWidth - ($margin * 2));
                    $subtitleShape->setOffsetX($margin);
                    $subtitleShape->setOffsetY($currentY);
                    
                    // Check if slide config has bullet formatting
                    $bulletPrefix = '• ';
                    $subtitleRun = $subtitleShape->createTextRun($bulletPrefix . $slide->subtitle);
                    $subtitleRun->getFont()
                        ->setName($subtitleFontFamily)
                        ->setSize($pptStyles['subtitle']['font_size'] ?? 21)
                        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF' . ($pptStyles['subtitle']['font_color'] ?? '000000')));
                    
                    $currentY += $subtitleHeight + $subtitleMarginBottom;
                }
                
                // Calculate available content area
                $contentStartY = (int)($currentY + 8);
                $contentEndY = (int)($slideHeight - 25); // Leave space for footer/legend
                $availableHeight = (int)($contentEndY - $contentStartY);
                
                // Handle two-column layout
                if ($layoutColumns === 2) {
                    $contentWidth = $slideWidth - ($margin * 2);
                    $columnGap = 20;
                    
                    // Get slide config to check for custom column widths
                    $slideTypes = unserialize(PPT_REPORT_PAGES);
                    $slideType = $slide->slide_type ?? 'custom';
                    $slideConfig = $slideTypes[$slideType] ?? null;
                    $layout = $slideConfig['layout'] ?? 'two_column_images';
                    
                    // Get layout configuration for column widths
                    $pptLayouts = unserialize(PPT_LAYOUTS);
                    $layoutConfig = $pptLayouts[$layout] ?? [];
                    
                    // Use custom column widths if defined (e.g., text_and_image layout)
                    $col1WidthPercent = $layoutConfig['col1_width_percent'] ?? 50;
                    $col2WidthPercent = $layoutConfig['col2_width_percent'] ?? 50;
                    
                    $col1Width = (int)(($contentWidth - $columnGap) * $col1WidthPercent / 100);
                    $col2Width = (int)(($contentWidth - $columnGap) * $col2WidthPercent / 100);
                    
                    $col1X = $margin;
                    $col2X = $margin + $col1Width + $columnGap;
                    
                    // Calculate header height based on text length
                    $col1HeaderText = strip_tags($slide->col1_header ?? '');
                    $col2HeaderText = strip_tags($slide->col2_header ?? '');
                    $columnHeaderMarginBottom = $pptStyles['column_header']['margin_bottom'] ?? 8;
                    
                    // Estimate lines needed (roughly 45 chars per line at font size 15)
                    $col1Lines = (int)max(1, ceil(strlen($col1HeaderText) / 40));
                    $col2Lines = (int)max(1, ceil(strlen($col2HeaderText) / 40));
                    $maxHeaderLines = (int)max($col1Lines, $col2Lines);
                    $headerHeight = (int)min(70, $maxHeaderLines * 18 + 5); // Cap at 70px, larger line height for 15pt
                    
                    // Column 1 Header
                    if (!empty($col1HeaderText)) {
                        $col1HeaderFontFamily = $pptStyles['column_header']['font_family'] ?? 'Calibri';
                        $col1HeaderShape = $pptSlide->createRichTextShape();
                        $col1HeaderShape->setHeight($headerHeight);
                        $col1HeaderShape->setWidth($col1Width);
                        $col1HeaderShape->setOffsetX($col1X);
                        $col1HeaderShape->setOffsetY($contentStartY);
                        $col1HeaderShape->getActiveParagraph()->getAlignment()
                            ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_LEFT);
                        
                        // Parse for bold text (simple approach)
                        $col1HeaderRun = $col1HeaderShape->createTextRun($col1HeaderText);
                        $col1HeaderRun->getFont()
                            ->setName($col1HeaderFontFamily)
                            ->setSize($pptStyles['column_header']['font_size'] ?? 15)
                            ->setBold(strpos($slide->col1_header ?? '', '<b>') !== false)
                            ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF' . ($pptStyles['column_header']['font_color'] ?? '000000')));
                    }
                    
                    // Column 2 Header
                    if (!empty($col2HeaderText)) {
                        $col2HeaderFontFamily = $pptStyles['column_header']['font_family'] ?? 'Calibri';
                        $col2HeaderShape = $pptSlide->createRichTextShape();
                        $col2HeaderShape->setHeight($headerHeight);
                        $col2HeaderShape->setWidth($col2Width);
                        $col2HeaderShape->setOffsetX($col2X);
                        $col2HeaderShape->setOffsetY($contentStartY);
                        $col2HeaderShape->getActiveParagraph()->getAlignment()
                            ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_LEFT);
                        
                        $col2HeaderRun = $col2HeaderShape->createTextRun($col2HeaderText);
                        $col2HeaderRun->getFont()
                            ->setName($col2HeaderFontFamily)
                            ->setSize($pptStyles['column_header']['font_size'] ?? 15)
                            ->setBold(strpos($slide->col2_header ?? '', '<b>') !== false)
                            ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF' . ($pptStyles['column_header']['font_color'] ?? '000000')));
                    }
                    
                    // Image area starts after headers with proper margin
                    $imageStartY = (int)($contentStartY + $headerHeight + $columnHeaderMarginBottom);
                    $imageMaxHeight = (int)($availableHeight - $headerHeight - $columnHeaderMarginBottom - 5);
                    
                    // Column 1 Image or Text
                    if (!empty($slide->col1_image_url)) {
                        $tempImage = $this->downloadTempImage($slide->col1_image_url);
                        if ($tempImage && file_exists($tempImage)) {
                            $tempFiles[] = $tempImage;
                            list($imgW, $imgH) = getimagesize($tempImage);
                            
                            // Scale image to fit column while maintaining aspect ratio
                            $scale = min($col1Width / $imgW, $imageMaxHeight / $imgH, 1);
                            $scaledW = (int)($imgW * $scale);
                            $scaledH = (int)($imgH * $scale);
                            
                            // Center image in column
                            $imgX = $col1X + (int)(($col1Width - $scaledW) / 2);
                            
                            $shape = $pptSlide->createDrawingShape();
                            $shape->setPath($tempImage);
                            $shape->setWidth($scaledW);
                            $shape->setHeight($scaledH);
                            $shape->setOffsetX($imgX);
                            $shape->setOffsetY($imageStartY);
                        }
                    } elseif (!empty($slide->col1_content)) {
                        // Use text_and_image_content style for text_and_image layout, otherwise default content style
                        $contentFontSize = ($layout === 'text_and_image') 
                            ? ($pptStyles['text_and_image_content']['font_size'] ?? 17)
                            : ($pptStyles['content']['font_size'] ?? 14);
                        $contentFontColor = ($layout === 'text_and_image')
                            ? ($pptStyles['text_and_image_content']['font_color'] ?? '000000')
                            : ($pptStyles['content']['font_color'] ?? '333333');
                        $contentFontFamily = ($layout === 'text_and_image')
                            ? ($pptStyles['text_and_image_content']['font_family'] ?? 'Calibri')
                            : ($pptStyles['content']['font_family'] ?? 'Calibri');
                        
                        $textShape = $pptSlide->createRichTextShape();
                        $textShape->setHeight($imageMaxHeight);
                        $textShape->setWidth($col1Width);
                        $textShape->setOffsetX($col1X);
                        $textShape->setOffsetY($imageStartY);
                        
                        $textRun = $textShape->createTextRun($slide->col1_content);
                        $textRun->getFont()
                            ->setName($contentFontFamily)
                            ->setSize($contentFontSize)
                            ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF' . $contentFontColor));
                    }
                    
                    // Column 2 Image or Text
                    if (!empty($slide->col2_image_url)) {
                        $tempImage = $this->downloadTempImage($slide->col2_image_url);
                        if ($tempImage && file_exists($tempImage)) {
                            $tempFiles[] = $tempImage;
                            list($imgW, $imgH) = getimagesize($tempImage);
                            
                            // Scale image to fit column while maintaining aspect ratio
                            $scale = min($col2Width / $imgW, $imageMaxHeight / $imgH, 1);
                            $scaledW = (int)($imgW * $scale);
                            $scaledH = (int)($imgH * $scale);
                            
                            // Center image in column
                            $imgX = $col2X + (int)(($col2Width - $scaledW) / 2);
                            
                            $shape = $pptSlide->createDrawingShape();
                            $shape->setPath($tempImage);
                            $shape->setWidth($scaledW);
                            $shape->setHeight($scaledH);
                            $shape->setOffsetX($imgX);
                            $shape->setOffsetY($imageStartY);
                        }
                    } elseif (!empty($slide->col2_content)) {
                        // Use text_and_image_content style for text_and_image layout, otherwise default content style
                        $contentFontSize = ($layout === 'text_and_image') 
                            ? ($pptStyles['text_and_image_content']['font_size'] ?? 17)
                            : ($pptStyles['content']['font_size'] ?? 14);
                        $contentFontColor = ($layout === 'text_and_image')
                            ? ($pptStyles['text_and_image_content']['font_color'] ?? '000000')
                            : ($pptStyles['content']['font_color'] ?? '333333');
                        $contentFontFamily = ($layout === 'text_and_image')
                            ? ($pptStyles['text_and_image_content']['font_family'] ?? 'Calibri')
                            : ($pptStyles['content']['font_family'] ?? 'Calibri');
                        
                        $textShape = $pptSlide->createRichTextShape();
                        $textShape->setHeight($imageMaxHeight);
                        $textShape->setWidth($col2Width);
                        $textShape->setOffsetX($col2X);
                        $textShape->setOffsetY($imageStartY);
                        
                        $textRun = $textShape->createTextRun($slide->col2_content);
                        $textRun->getFont()
                            ->setName($contentFontFamily)
                            ->setSize($contentFontSize)
                            ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF' . $contentFontColor));
                    }
                } else {
                    // Single column layout - maximize image space
                    $contentWidth = $slideWidth - ($margin * 2);
                    
                    // Add image if present (use col1_image_url or legacy image_url)
                    $imageUrl = $slide->col1_image_url ?? $slide->image_url ?? null;
                    if (!empty($imageUrl)) {
                        $tempImage = $this->downloadTempImage($imageUrl);
                        if ($tempImage && file_exists($tempImage)) {
                            $tempFiles[] = $tempImage;
                            list($imgW, $imgH) = getimagesize($tempImage);
                            
                            // Maximize image size while maintaining aspect ratio
                            $maxImgHeight = (int)($availableHeight - 10);
                            $scale = min($contentWidth / $imgW, $maxImgHeight / $imgH, 1);
                            $scaledW = (int)($imgW * $scale);
                            $scaledH = (int)($imgH * $scale);
                            
                            // Center image horizontally
                            $imgX = $margin + (int)(($contentWidth - $scaledW) / 2);
                            
                            $shape = $pptSlide->createDrawingShape();
                            $shape->setPath($tempImage);
                            $shape->setWidth($scaledW);
                            $shape->setHeight($scaledH);
                            $shape->setOffsetX($imgX);
                            $shape->setOffsetY($contentStartY);
                        }
                    }
                    
                    // Add text content if present (below or instead of image)
                    if (!empty($slide->col1_content)) {
                        $textY = (int)(!empty($imageUrl) ? $contentStartY + $availableHeight - 80 : $contentStartY);
                        $textHeight = (int)(!empty($imageUrl) ? 75 : $availableHeight);
                        
                        $textShape = $pptSlide->createRichTextShape();
                        $textShape->setHeight($textHeight);
                        $textShape->setWidth($contentWidth);
                        $textShape->setOffsetX($margin);
                        $textShape->setOffsetY($textY);
                        
                        $textRun = $textShape->createTextRun($slide->col1_content);
                        $textRun->getFont()
                            ->setSize(12)
                            ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                    }
                }
                
                // Add legend if present (at bottom)
                $legendItems = $slide->getLegendItems();
                if (!empty($legendItems)) {
                    $legendY = $slideHeight - 22;
                    $legendX = $margin;
                    $legendItemWidth = 120;
                    
                    foreach ($legendItems as $item) {
                        if (!empty($item['label'])) {
                            $legendShape = $pptSlide->createRichTextShape();
                            $legendShape->setHeight(18);
                            $legendShape->setWidth($legendItemWidth);
                            $legendShape->setOffsetX($legendX);
                            $legendShape->setOffsetY($legendY);
                            
                            $legendRun = $legendShape->createTextRun('■ ' . $item['label']);
                            $legendRun->getFont()
                                ->setSize(9)
                                ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF' . ltrim($item['color'] ?? '000000', '#')));
                            
                            $legendX += $legendItemWidth;
                        }
                    }
                }
            }
        }
        
        // Generate filename
        $caseId = str_pad((string)$report->case_id, 6, 'X', STR_PAD_LEFT);
        $filename = 'MEG_Report_CASE_' . $caseId . '.pptx';
        
        // Generate PowerPoint file
        $writer = new \PhpOffice\PhpPresentation\Writer\PowerPoint2007($presentation);
        
        // Save to temporary file
        $tmpFile = TMP . 'ppt_' . uniqid() . '.pptx';
        $writer->save($tmpFile);
        
        // Clean up temp image files after PowerPoint is generated
        foreach ($tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
        
        // Send file to browser
        $response = $this->response->withFile($tmpFile, [
            'download' => true,
            'name' => $filename
        ]);
        
        // Clean up temp PowerPoint file after sending
        register_shutdown_function(function() use ($tmpFile) {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        });
        
        return $response;
    }

    /**
     * Create cover slide with patient and study information
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
}
