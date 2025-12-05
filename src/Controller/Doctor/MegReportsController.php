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
        }
        
        $this->set(compact('slides', 'report', 'reportId'));
    }

    /**
     * Add method - Add a new slide
     *
     * @param int|null $reportId Report ID
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add($reportId = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        $reportId = $reportId ?? $this->request->getQuery('report_id');
        
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
        
        $ReportSlides = $this->fetchTable('ReportSlides');
        $slide = $ReportSlides->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Get next slide order
            $maxOrderQuery = $ReportSlides->find()
                ->where(['report_id' => $reportId])
                ->select(['max_order' => $ReportSlides->find()->func()->max('slide_order')])
                ->first();
            
            $maxOrderValue = $maxOrderQuery ? $maxOrderQuery->max_order : null;
            $nextOrder = ($maxOrderValue !== null) ? (int)$maxOrderValue + 1 : 1;
            
            // Get exam procedure ID (cast to int or null)
            $examProcedureId = $this->request->getData('cases_exams_procedure_id');
            $examProcedureId = $examProcedureId ? (int)$examProcedureId : null;
            
            // Handle image upload
            $imageFile = $this->request->getData('image_file');
            $imagePath = '';
            $s3Key = '';
            $documentId = null;
            
            if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
                $filename = $imageFile->getClientFilename();
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $tmpPath = $imageFile->getStream()->getMetadata('uri');
                    $s3Service = new S3DocumentService();
                    
                    // 1. Upload ORIGINAL image to Documents table ONLY if exam procedure is linked
                    if ($examProcedureId) {
                        $originalUploadResult = $s3Service->uploadDocument(
                            $imageFile,
                            $report->case_id,
                            $report->case->patient_id ?? 0,
                            'meg-slide-image',
                            $examProcedureId
                        );
                        
                        // Save to Documents table
                        if ($originalUploadResult['success']) {
                            $Documents = $this->fetchTable('Documents');
                            $document = $Documents->newEntity([
                                'case_id' => $report->case_id,
                                'user_id' => $userId,
                                'cases_exams_procedure_id' => $examProcedureId,
                                'document_type' => 'meg-slide-image',
                                'file_path' => $originalUploadResult['file_path'],
                                'file_type' => $originalUploadResult['mime_type'],
                                'file_size' => $originalUploadResult['file_size'],
                                'original_filename' => $originalUploadResult['original_name'],
                                'description' => 'MEG Report Slide Image - ' . ($data['title'] ?? 'Untitled'),
                                'uploaded_at' => new \DateTime()
                            ]);
                            
                            if ($Documents->save($document)) {
                                $documentId = $document->id;
                            }
                        }
                    }
                    
                    // 2. Create resized version for slide display (750x450 max)
                    $resizedPath = TMP . 'slide_resized_' . uniqid() . '.' . $ext;
                    if ($this->resizeImage($tmpPath, $resizedPath, 750, 450, $ext)) {
                        // Create a temporary UploadedFile-like object for the resized image
                        $newFilename = 'resized_' . uniqid() . '.' . $ext;
                        
                        // Upload resized version (we'll use array format for this)
                        $mimeTypes = [
                            'jpg' => 'image/jpeg',
                            'jpeg' => 'image/jpeg',
                            'png' => 'image/png',
                            'gif' => 'image/gif'
                        ];
                        
                        $resizedFileArray = [
                            'tmp_name' => $resizedPath,
                            'name' => $newFilename,
                            'size' => filesize($resizedPath),
                            'type' => $mimeTypes[$ext] ?? 'application/octet-stream'
                        ];
                        
                        $resizedUploadResult = $s3Service->uploadDocument(
                            $resizedFileArray,
                            $report->case_id,
                            $report->case->patient_id ?? 0,
                            'report-images',
                            null
                        );
                        
                        if ($resizedUploadResult['success']) {
                            // Store S3 key in file_path (resized version for slide display)
                            $s3Key = $resizedUploadResult['file_path'];
                            $imagePath = $s3Key;
                        }
                        
                        // Clean up temp file
                        if (file_exists($resizedPath)) {
                            unlink($resizedPath);
                        }
                    }
                }
            }
            
            // Build HTML content
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';
            $htmlContent = '<div class="slide-content">';
            if (!empty($title)) {
                $htmlContent .= '<h3>' . h($title) . '</h3>';
            }
            if (!empty($content)) {
                $htmlContent .= '<p>' . nl2br(h($content)) . '</p>';
            }
            if (!empty($imagePath)) {
                $htmlContent .= '<img src="' . h($imagePath) . '" alt="Slide Image" class="slide-image" />';
            }
            $htmlContent .= '</div>';
            
            $slideData = [
                'report_id' => $reportId,
                'user_id' => $userId,
                'slide_order' => $nextOrder,
                'title' => $title,
                'description' => $content,
                'file_path' => $imagePath,
                's3_key' => $s3Key,
                'cases_exams_procedure_id' => $this->request->getData('cases_exams_procedure_id'),
                'document_id' => $documentId,
                'original_filename' => $imageFile ? $imageFile->getClientFilename() : null,
                'mime_type' => $imageFile ? $imageFile->getClientMediaType() : null,
                'file_size' => $imageFile ? $imageFile->getSize() : null,
                'html_content' => $htmlContent,
            ];
            
            $slide = $ReportSlides->patchEntity($slide, $slideData);
            
            if ($ReportSlides->save($slide)) {
                // Update exam procedure status to completed if linked
                if ($examProcedureId) {
                    $CasesExamsProcedures = $this->fetchTable('CasesExamsProcedures');
                    $caseExamProcedure = $CasesExamsProcedures->get($examProcedureId);
                    $caseExamProcedure->status = 'completed';
                    $CasesExamsProcedures->save($caseExamProcedure);
                }
                
                $this->Flash->success('Slide has been added.');
                return $this->redirect(['action' => 'index', $reportId]);
            }
            
            // Show specific validation errors
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
        
        $this->set(compact('slide', 'report', 'reportId', 'examProceduresList'));
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
            ->contain(['Cases'])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $slide->report_id])
            ->first();
            
        if (!$report) {
            $this->Flash->error('You do not have access to edit this slide.');
            return $this->redirect(['controller' => 'Reports', 'action' => 'index']);
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
            
            // Handle image upload
            $imageFile = $this->request->getData('image_file');
            $documentId = $slide->document_id; // Keep track of existing document
            
            if ($imageFile && $imageFile->getError() === UPLOAD_ERR_OK) {
                $filename = $imageFile->getClientFilename();
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $tmpPath = $imageFile->getStream()->getMetadata('uri');
                    
                    // Get exam procedure ID (cast to int or null)
                    $examProcedureId = $this->request->getData('cases_exams_procedure_id');
                    $examProcedureId = $examProcedureId ? (int)$examProcedureId : null;
                    $examProcedureId = $examProcedureId ? (int)$examProcedureId : null;
                    
                    $s3Service = new S3DocumentService();
                    
                    // 1. Upload ORIGINAL image to Documents table ONLY if exam procedure is linked
                    if ($examProcedureId) {
                        $originalUploadResult = $s3Service->uploadDocument(
                            $imageFile,  // Pass UploadedFile object directly
                            $report->case_id,
                            $report->case->patient_id ?? 0,
                            'meg-slide-image',
                            $examProcedureId
                        );
                        
                        if ($originalUploadResult['success']) {
                            // Save to Documents table
                            $Documents = $this->fetchTable('Documents');
                            $document = $Documents->newEntity([
                                'case_id' => $report->case_id,
                                'user_id' => $userId,
                                'cases_exams_procedure_id' => $examProcedureId,
                                'document_type' => 'meg-slide-image',
                                'file_path' => $originalUploadResult['file_path'],
                                'file_type' => $originalUploadResult['mime_type'],
                                'file_size' => $originalUploadResult['file_size'],
                                'original_filename' => $originalUploadResult['original_name'],
                                'description' => 'MEG Report Slide Image - ' . ($data['title'] ?? 'Untitled'),
                                'uploaded_at' => new \DateTime()
                            ]);
                            
                            if ($Documents->save($document)) {
                                $documentId = $document->id;
                                
                                // Delete old document if exists
                                if ($slide->document_id && $slide->document_id != $documentId) {
                                    $oldDocument = $Documents->get($slide->document_id);
                                    if ($oldDocument->file_path) {
                                        $s3Service->deleteDocument($oldDocument->file_path);
                                    }
                                    $Documents->delete($oldDocument);
                                }
                            }
                        }
                    }
                    
                    // 2. Create resized version for slide display (750x450 max)
                    $resizedPath = TMP . 'slide_resized_' . uniqid() . '.' . $ext;
                    if ($this->resizeImage($tmpPath, $resizedPath, 750, 450, $ext)) {
                        // Delete old resized image from S3
                        if ($slide->file_path) {
                            $s3Service->deleteDocument($slide->file_path);
                        }
                        
                        // Upload resized version
                        $mimeTypes = [
                            'jpg' => 'image/jpeg',
                            'jpeg' => 'image/jpeg',
                            'png' => 'image/png',
                            'gif' => 'image/gif'
                        ];
                        
                        $newFilename = 'resized_' . uniqid() . '.' . $ext;
                        $resizedFileArray = [
                            'tmp_name' => $resizedPath,
                            'name' => $newFilename,
                            'size' => filesize($resizedPath),
                            'type' => $mimeTypes[$ext] ?? 'application/octet-stream'
                        ];
                        
                        $resizedUploadResult = $s3Service->uploadDocument(
                            $resizedFileArray,
                            $report->case_id,
                            $report->case->patient_id ?? 0,
                            'report-images',
                            null
                        );
                        
                        if ($resizedUploadResult['success']) {
                            // Store S3 key in file_path (resized version for display)
                            $data['file_path'] = $resizedUploadResult['file_path'];
                            $data['s3_key'] = $resizedUploadResult['file_path'];
                        }
                        
                        // Clean up temp file
                        if (file_exists($resizedPath)) {
                            unlink($resizedPath);
                        }
                    }
                    
                    $data['document_id'] = $documentId;
                    $data['original_filename'] = $imageFile->getClientFilename();
                    $data['mime_type'] = $imageFile->getClientMediaType();
                    $data['file_size'] = $imageFile->getSize();
                }
            }
            
            // Build HTML content
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';
            $imagePath = $data['file_path'] ?? $slide->file_path;
            
            $htmlContent = '<div class="slide-content">';
            if (!empty($title)) {
                $htmlContent .= '<h3>' . h($title) . '</h3>';
            }
            if (!empty($content)) {
                $htmlContent .= '<p>' . nl2br(h($content)) . '</p>';
            }
            if (!empty($imagePath)) {
                $htmlContent .= '<img src="' . h($imagePath) . '" alt="Slide Image" class="slide-image" />';
            }
            $htmlContent .= '</div>';
            
            // Store in separate fields
            $data['title'] = $title;
            $data['description'] = $content;
            $data['html_content'] = $htmlContent;
            
            // Handle exam procedure link
            if (isset($data['cases_exams_procedure_id'])) {
                $examProcedureId = $data['cases_exams_procedure_id'];
            }
            
            $slide = $ReportSlides->patchEntity($slide, $data);
            
            if ($ReportSlides->save($slide)) {
                // Update exam procedure status to completed if linked
                if (isset($examProcedureId) && $examProcedureId) {
                    $CasesExamsProcedures = $this->fetchTable('CasesExamsProcedures');
                    $caseExamProcedure = $CasesExamsProcedures->get($examProcedureId);
                    $caseExamProcedure->status = 'completed';
                    $CasesExamsProcedures->save($caseExamProcedure);
                }
                
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
        
        // Get existing image URL if exists
        if ($slide->file_path) {
            $s3Service = new S3DocumentService();
            $slide->image_url = $s3Service->getDownloadUrl($slide->file_path);
        }
        
        $this->set(compact('slide', 'report', 'examProceduresList'));
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
        }
        
        // Create PowerPoint presentation
        $presentation = new \PhpOffice\PhpPresentation\PhpPresentation();
        $presentation->removeSlideByIndex(0); // Remove default slide
        
        // Track temp files for cleanup after PowerPoint is generated
        $tempFiles = [];
        
        foreach ($slides as $index => $slide) {
            $pptSlide = $presentation->createSlide();
            
            // Set slide background to white
            $background = $pptSlide->getBackground();
            if ($background) {
                $background->setColor(new \PhpOffice\PhpPresentation\Style\Color('FFFFFFFF'));
            }
            
            // Add description/content if present
            if (!empty($slide->description)) {
                // First slide (cover) gets special formatting with heading
                if ($index === 0) {
                    // Split description into heading and content
                    $lines = explode("\n", $slide->description);
                    $heading = array_shift($lines); // First line is heading
                    $content = implode("\n", array_slice($lines, 2)); // Skip empty lines after heading
                    
                    // Add heading
                    $headingShape = $pptSlide->createRichTextShape();
                    $headingShape->setHeight(80);
                    $headingShape->setWidth(900);
                    $headingShape->setOffsetX(30);
                    $headingShape->setOffsetY(50);
                    $headingShape->getActiveParagraph()->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_CENTER);
                    
                    $headingRun = $headingShape->createTextRun($heading);
                    $headingRun->getFont()
                        ->setSize(24)
                        ->setBold(true)
                        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                    
                    // Add content below heading
                    $textShape = $pptSlide->createRichTextShape();
                    $textShape->setHeight(420);
                    $textShape->setWidth(900);
                    $textShape->setOffsetX(30);
                    $textShape->setOffsetY(150);
                    $textShape->getActiveParagraph()->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_CENTER);
                    
                    $textRun = $textShape->createTextRun($content);
                    $textRun->getFont()
                        ->setSize(16)
                        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                } else {
                    // Regular slides - text at top
                    $textShape = $pptSlide->createRichTextShape();
                    $textShape->setHeight(100);  // Reduced height for text
                    $textShape->setWidth(900);
                    $textShape->setOffsetX(30);
                    $textShape->setOffsetY(30);
                    
                    $textRun = $textShape->createTextRun($slide->description);
                    $textRun->getFont()
                        ->setSize(24)
                        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                }
            }
            
            // Add image if present
            if (!empty($slide->image_url)) {
                // Use generated URL (presigned for S3 or local path)
                if (strpos($slide->image_url, 'http') === 0) {
                    // S3 URL - download to temp file
                    $tempImage = TMP . 'ppt_img_' . uniqid() . '.jpg';
                    $imageContent = file_get_contents($slide->image_url);
                    file_put_contents($tempImage, $imageContent);
                    $imagePath = $tempImage;
                    $tempFiles[] = $tempImage; // Track for cleanup
                } else {
                    // Local path
                    $imagePath = WWW_ROOT . ltrim($slide->image_url, '/');
                }
                
                if (file_exists($imagePath)) {
                    $shape = $pptSlide->createDrawingShape();
                    $shape->setPath($imagePath);
                    
                    // Get original image dimensions
                    list($imageWidth, $imageHeight) = getimagesize($imagePath);
                    
                    // Use original image size (no scaling)
                    $shape->setWidth($imageWidth);
                    $shape->setHeight($imageHeight);
                    
                    // Center image horizontally
                    $slideWidth = 960;
                    $centerX = (int)(($slideWidth - $imageWidth) / 2);
                    
                    // Position image below text
                    $offsetY = ($index === 0) ? 150 : 140;  // Below text
                    $shape->setOffsetY($offsetY);
                    $shape->setOffsetX($centerX);
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
