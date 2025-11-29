<?php
declare(strict_types=1);

namespace App\Controller\Doctor;

use App\Controller\AppController;
use Cake\Http\Exception\NotFoundException;

/**
 * Reports Controller (Doctor)
 *
 * @property \App\Model\Table\ReportsTable $Reports
 */
class ReportsController extends AppController
{
    /**
     * Index method - List reports for cases assigned to the doctor
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Only show reports for cases assigned to this doctor
        $reports = $this->Reports->find()
            ->contain([
                'Users' => ['Roles'], // Load Users with their Roles
                'Cases' => [
                    'PatientUsers',
                    'Hospitals',
                    'Departments',
                    'Sedations',
                    'CasesExamsProcedures' => [
                        'ExamsProcedures' => [
                            'Exams' => ['Modalities'],
                            'Procedures'
                        ]
                    ],
                    'Documents'
                ],
                'Hospitals'
            ])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->order(['Reports.case_id' => 'ASC', 'Reports.created' => 'ASC'])
            ->all();

        $this->set(compact('reports'));
    }

    /**
     * View method - View a single report (only if case is assigned to doctor)
     *
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found or not accessible.
     */
    public function view($id = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get the report and verify case assignment
        $report = $this->Reports->find()
            ->contain(['Cases' => ['PatientUsers'], 'Hospitals'])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $id])
            ->first();
            
        if (!$report) {
            $this->Flash->error(__('Report not found or you do not have access to this case.'));
            return $this->redirect(['action' => 'index']);
        }

        $this->set(compact('report'));
    }

    /**
     * Add method - Create a new doctor report for a case, potentially using existing scientist report as base
     *
     * @param int|null $caseId Case ID to create report for
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add($caseId = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get case ID from parameter or query string
        $caseId = $caseId ?? $this->request->getQuery('case_id');
        
        // Load the case
        if (!$caseId) {
            $this->Flash->error(__('Invalid case specified.'));
            return $this->redirect(['controller' => 'Cases', 'action' => 'index']);
        }

        // Verify the case is assigned to this doctor
        $caseAssignment = $this->fetchTable('CaseAssignments')->find()
            ->where([
                'case_id' => $caseId,
                'assigned_to' => $userId
            ])
            ->first();
            
        if (!$caseAssignment) {
            $this->Flash->error(__('You do not have access to this case.'));
            return $this->redirect(['controller' => 'Cases', 'action' => 'index']);
        }

        // Check if THIS doctor already has a report for this case
        $existingDoctorReport = $this->Reports->find()
            ->where([
                'case_id' => $caseId,
                'user_id' => $userId,
                'doctor_approval IS NOT' => null
            ])
            ->first();
            
        if ($existingDoctorReport) {
            // Redirect to edit this doctor's existing report
            return $this->redirect(['action' => 'edit', $existingDoctorReport->id]);
        }

        // Check for existing scientist report to use as base
        $scientistReport = $this->Reports->find()
            ->where([
                'case_id' => $caseId,
                'scientist_review IS NOT' => null
            ])
            ->first();

        // Load case with all related data
        $case = $this->fetchTable('Cases')->get($caseId, [
            'contain' => [
                'PatientUsers',
                'Hospitals',
                'Departments',
                'Sedations',
                'CasesExamsProcedures' => [
                    'ExamsProcedures' => [
                        'Exams' => ['Modalities'],
                        'Procedures'
                    ]
                ],
                'Documents'
            ]
        ]);
        
        $report = $this->Reports->newEmptyEntity();
        
        // Default content from scientist report if available
        $reportContent = '';
        if ($scientistReport) {
            $scientistData = json_decode($scientistReport->report_data, true) ?? [];
            $reportContent = $scientistData['content'] ?? '';
        }
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Debug: Log the received data
            \Cake\Log\Log::info('Doctor Report Data Received: ' . json_encode($data));
            
            // Structure the report data with doctor approval
            $reportDataStructure = [
                'content' => $data['report_content'] ?? $reportContent
            ];
            
            $data['report_data'] = json_encode($reportDataStructure);
            $data['case_id'] = $caseId;
            $data['hospital_id'] = $case->hospital_id;
            $data['status'] = 'approved'; // Doctor reports are automatically approved
            $data['user_id'] = $userId;
            
            // Add doctor approval data
            $data['doctor_approval'] = json_encode([
                'approved_by' => $userId,
                'approved_at' => date('Y-m-d H:i:s'),
                'notes' => $data['doctor_notes'] ?? ''
            ]);
            
            // Debug: Log the final data being saved
            \Cake\Log\Log::info('Doctor Report Final Data: ' . json_encode($data));
            
            $report = $this->Reports->patchEntity($report, $data);
            
            // Debug: Log any validation errors
            if ($report->getErrors()) {
                \Cake\Log\Log::error('Doctor Report Validation Errors: ' . json_encode($report->getErrors()));
            }
            
            if ($this->Reports->save($report)) {
                \Cake\Log\Log::info('Doctor Report Saved Successfully with ID: ' . $report->id);
                $this->Flash->success(__('The doctor report has been created successfully.'));
                return $this->redirect(['action' => 'view', $report->id]);
            } else {
                $this->Flash->error(__('The report could not be saved. Please, try again.'));
            }
        }
        
        $this->set(compact('report', 'case', 'reportContent', 'scientistReport'));
    }

    /**
     * Edit method
     *
     * @param int|null $id Report id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     */
    public function edit($id = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get the report with case assignment verification
        $report = $this->Reports->find()
            ->contain(['Cases' => [
                'PatientUsers',
                'Hospitals',
                'Departments',
                'Sedations',
                'CasesExamsProcedures' => [
                    'ExamsProcedures' => [
                        'Exams' => ['Modalities'],
                        'Procedures'
                    ]
                ],
                'Documents'
            ]])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $id])
            ->first();
            
        if (!$report) {
            $this->Flash->error(__('Report not found or you do not have access to this case.'));
            return $this->redirect(['action' => 'index']);
        }
        
        // Check if current user is the creator of this report
        if ($report->user_id != $userId) {
            $this->Flash->error(__('You can only edit reports that you created.'));
            return $this->redirect(['action' => 'index']);
        }
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Store the single editor content as report_data
            $reportDataStructure = [
                'content' => $data['report_content'] ?? ''
            ];
            
            $data['report_data'] = json_encode($reportDataStructure);
            
            // Update doctor approval data
            $data['doctor_approval'] = json_encode([
                'approved_by' => $userId,
                'approved_at' => date('Y-m-d H:i:s'),
                'notes' => $data['doctor_notes'] ?? ''
            ]);
            
            $report = $this->Reports->patchEntity($report, $data);
            
            if ($this->Reports->save($report)) {
                $this->Flash->success(__('The report has been updated.'));
                return $this->redirect(['action' => 'view', $report->id]);
            } else {
                $this->Flash->error(__('The report could not be saved. Please, try again.'));
            }
        }
        
        // Parse existing report data
        $reportData = json_decode($report->report_data, true) ?? [];
        $reportContent = $reportData['content'] ?? '';
        
        $this->set(compact('report', 'reportContent'));
    }

    /**
     * Preview method - Show report in preview format
     *
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function preview($id = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get the report with case assignment verification
        $report = $this->Reports->find()
            ->contain(['Cases' => ['PatientUsers'], 'Hospitals'])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $id])
            ->first();
            
        if (!$report) {
            $this->Flash->error(__('Report not found or you do not have access to this case.'));
            return $this->redirect(['action' => 'index']);
        }
        
        $reportData = json_decode($report->report_data, true) ?? [];
        $reportContent = $reportData['content'] ?? '';
        
        $this->set(compact('report', 'reportContent'));
    }

    /**
     * Download method - Export report in requested format
     *
     * @param string|null $id Report id.
     * @param string $format Export format (pdf, docx, rtf, html, txt)
     * @return \Cake\Http\Response Downloads file
     */
    public function download($id = null, $format = 'pdf')
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get the report with case assignment verification
        $report = $this->Reports->find()
            ->contain(['Cases' => ['PatientUsers'], 'Hospitals'])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $id])
            ->first();
            
        if (!$report) {
            $this->Flash->error(__('Report not found or you do not have access to this case.'));
            return $this->redirect(['action' => 'index']);
        }
        
        // Load the export service
        $exportService = new \App\Service\ReportExportService();
        
        // Parse report_data
        $reportData = json_decode($report->report_data, true) ?? [];
        
        try {
            $result = $exportService->export($report, $reportData, $format);
            
            return $this->response
                ->withType($result['mimeType'])
                ->withDownload($result['filename'])
                ->withStringBody($result['content']);
                
        } catch (\Exception $e) {
            $this->Flash->error(__('Error generating report: {0}', $e->getMessage()));
            return $this->redirect(['action' => 'view', $id]);
        }
    }

    /**
     * Export report from case directly (without creating report record)
     *
     * @param int $caseId Case ID
     * @param string $format Export format
     * @return \Cake\Http\Response Downloads file
     */
    public function exportFromCase($caseId = null, $format = 'pdf')
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        if (!$caseId) {
            $this->Flash->error(__('Invalid case specified.'));
            return $this->redirect(['controller' => 'Cases', 'action' => 'index']);
        }

        // Verify the case is assigned to this doctor
        $caseAssignment = $this->fetchTable('CaseAssignments')->find()
            ->where([
                'case_id' => $caseId,
                'assigned_to' => $userId
            ])
            ->first();
            
        if (!$caseAssignment) {
            $this->Flash->error(__('You do not have access to this case.'));
            return $this->redirect(['controller' => 'Cases', 'action' => 'index']);
        }

        try {
            // Load case
            $case = $this->fetchTable('Cases')->get($caseId, [
                'contain' => [
                    'PatientUsers',
                    'Hospitals',
                    'Departments',
                    'Sedations',
                    'CasesExamsProcedures' => [
                        'ExamsProcedures' => [
                            'Exams' => ['Modalities'],
                            'Procedures'
                        ]
                    ],
                    'Documents'
                ]
            ]);
            
            // Create temporary report entity for export
            $tempReport = $this->Reports->newEntity([
                'case_id' => $caseId,
                'hospital_id' => $case->hospital_id,
                'case' => $case
            ]);
            
            // Load the export service
            $exportService = new \App\Service\ReportExportService();
            
            $result = $exportService->exportFromCase($tempReport, $case, $format);
            
            return $this->response
                ->withType($result['mimeType'])
                ->withDownload($result['filename'])
                ->withStringBody($result['content']);
                
        } catch (\Exception $e) {
            $this->Flash->error(__('Error generating report: {0}', $e->getMessage()));
            return $this->redirect(['controller' => 'Cases', 'action' => 'view', $caseId]);
        }
    }

    /**
     * Delete method
     *
     * @param string|null $id Report id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get the report with case assignment verification
        $report = $this->Reports->find()
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where(['Reports.id' => $id])
            ->first();
            
        if (!$report) {
            $this->Flash->error(__('Report not found or you do not have access to this case.'));
            return $this->redirect(['action' => 'index']);
        }
        
        if ($this->Reports->delete($report)) {
            $this->Flash->success(__('The report has been deleted.'));
        } else {
            $this->Flash->error(__('The report could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Edit MEG Report (PPT) - Manage images and descriptions
     */
    public function editMegReport($id = null)
    {
        $this->request->allowMethod(['get', 'post']);
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get the report with case assignment verification
        $report = $this->Reports->find()
            ->contain([
                'Cases' => [
                    'PatientUsers',
                    'Hospitals',
                    'CaseAssignments'
                ],
                'ReportImages' => ['sort' => ['ReportImages.slide_order' => 'ASC']]
            ])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where([
                'Reports.id' => $id,
                'Reports.type' => 'PPT'
            ])
            ->first();
            
        if (!$report) {
            $this->Flash->error(__('MEG Report not found or you do not have access.'));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['post', 'put'])) {
            // Handle image description updates
            $data = $this->request->getData();
            
            if (isset($data['image_descriptions']) && is_array($data['image_descriptions'])) {
                $reportImagesTable = $this->fetchTable('ReportImages');
                
                foreach ($data['image_descriptions'] as $imageId => $description) {
                    $image = $reportImagesTable->get($imageId);
                    if ($image->report_id === $report->id) {
                        $image->description = $description;
                        $reportImagesTable->save($image);
                    }
                }
                
                $this->Flash->success(__('Image descriptions updated successfully.'));
            }
            
            // Handle slide order updates
            if (isset($data['slide_order']) && is_array($data['slide_order'])) {
                $reportImagesTable = $this->fetchTable('ReportImages');
                
                foreach ($data['slide_order'] as $order => $imageId) {
                    $image = $reportImagesTable->get($imageId);
                    if ($image->report_id === $report->id) {
                        $image->slide_order = $order;
                        $reportImagesTable->save($image);
                    }
                }
                
                $this->Flash->success(__('Slide order updated successfully.'));
            }
            
            return $this->redirect(['action' => 'editMegReport', $id]);
        }
        
        $this->set(compact('report'));
    }

    /**
     * Upload slide image for MEG Report
     */
    public function uploadSlideImage($reportId = null)
    {
        $this->request->allowMethod(['post']);
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Verify report access
        $report = $this->Reports->find()
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where([
                'Reports.id' => $reportId,
                'Reports.type' => 'PPT'
            ])
            ->first();
            
        if (!$report) {
            $this->Flash->error(__('MEG Report not found or you do not have access.'));
            return $this->redirect(['action' => 'index']);
        }

        $file = $this->request->getData('image');
        
        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            $this->Flash->error(__('Please select a valid image file.'));
            return $this->redirect(['action' => 'editMegReport', $reportId]);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file->getClientMediaType(), $allowedTypes)) {
            $this->Flash->error(__('Only JPG, PNG, and GIF images are allowed.'));
            return $this->redirect(['action' => 'editMegReport', $reportId]);
        }

        // Validate file size (max 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            $this->Flash->error(__('Image size must be less than 10MB.'));
            return $this->redirect(['action' => 'editMegReport', $reportId]);
        }

        try {
            // Resize image to fit PowerPoint slide dimensions (1280x960 max)
            $resizedFile = $this->resizeImageForPPT($file);
            
            // Upload to S3 using S3DocumentService
            $s3Service = new \App\Lib\S3DocumentService();
            
            // Get report to access case_id and patient_id
            $report = $this->Reports->get($reportId, ['contain' => ['Cases' => ['PatientUsers']]]);
            
            // Upload document using the standard uploadDocument method
            $uploadResult = $s3Service->uploadDocument(
                $resizedFile,
                $report->case_id,
                $report->case->patient_user->id,
                'report-images'
            );
            
            // Clean up temporary file
            if (is_array($resizedFile) && isset($resizedFile['tmp_name']) && file_exists($resizedFile['tmp_name'])) {
                @unlink($resizedFile['tmp_name']);
            }
            
            if ($uploadResult['success']) {
                // Save to database
                $reportImagesTable = $this->fetchTable('ReportImages');
                
                // Get current max slide order
                $maxOrder = $reportImagesTable->find()
                    ->where(['report_id' => $reportId])
                    ->order(['slide_order' => 'DESC'])
                    ->first();
                
                $nextOrder = $maxOrder ? (int)$maxOrder->slide_order + 1 : 1;
                
                $reportImage = $reportImagesTable->newEntity([
                    'report_id' => $reportId,
                    'user_id' => $userId,
                    's3_key' => $uploadResult['file_path'],
                    'file_path' => $uploadResult['file_path'],
                    'original_filename' => $uploadResult['original_name'],
                    'file_size' => $uploadResult['file_size'],
                    'mime_type' => $uploadResult['mime_type'],
                    'text_above' => '',
                    'description' => '',
                    'slide_order' => $nextOrder
                ]);
                
                if ($reportImagesTable->save($reportImage)) {
                    $this->Flash->success(__('Image uploaded successfully.'));
                } else {
                    // Log validation errors
                    $errors = $reportImage->getErrors();
                    $this->log('Failed to save report image. Errors: ' . print_r($errors, true), 'error');
                    $this->log('Data attempted: ' . print_r([
                        'report_id' => $reportId,
                        'user_id' => $userId,
                        's3_key' => $uploadResult['file_path'],
                        'file_path' => $uploadResult['file_path'],
                        'original_filename' => $uploadResult['original_name'],
                        'file_size' => $uploadResult['file_size'],
                        'mime_type' => $uploadResult['mime_type'],
                        'slide_order' => $nextOrder
                    ], true), 'error');
                    
                    // Delete from S3 if database save fails
                    $s3Service->deleteDocument($uploadResult['file_path']);
                    
                    // Show specific error message if available
                    $errorMsg = 'Failed to save image information.';
                    if (!empty($errors)) {
                        $errorDetails = [];
                        foreach ($errors as $field => $fieldErrors) {
                            $errorDetails[] = $field . ': ' . implode(', ', $fieldErrors);
                        }
                        $errorMsg .= ' (' . implode('; ', $errorDetails) . ')';
                    }
                    $this->Flash->error(__($errorMsg));
                }
            } else {
                $this->Flash->error(__($uploadResult['error'] ?? 'Failed to upload image to storage.'));
            }
            
        } catch (\Exception $e) {
            \Cake\Log\Log::error('MEG Report image upload error: ' . $e->getMessage());
            $this->Flash->error(__('An error occurred while uploading the image.'));
        }
        
        return $this->redirect(['action' => 'editMegReport', $reportId]);
    }

    /**
     * Delete slide image from MEG Report
     */
    public function deleteSlideImage($imageId = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        $reportImagesTable = $this->fetchTable('ReportImages');
        
        // Get image with report verification
        $image = $reportImagesTable->find()
            ->contain(['Reports' => ['Cases' => ['CaseAssignments']]])
            ->where(['ReportImages.id' => $imageId])
            ->first();
            
        if (!$image) {
            $this->Flash->error(__('Image not found.'));
            return $this->redirect(['controller' => 'Reports', 'action' => 'index']);
        }
        
        // Verify access
        $hasAccess = false;
        foreach ($image->report->case->case_assignments as $assignment) {
            if ($assignment->assigned_to === $userId) {
                $hasAccess = true;
                break;
            }
        }
        
        if (!$hasAccess) {
            $this->Flash->error(__('You do not have permission to delete this image.'));
            return $this->redirect(['action' => 'index']);
        }
        
        $reportId = $image->report_id;
        
        try {
            // Delete from S3
            $s3Service = new \App\Lib\S3DocumentService();
            $s3Service->deleteDocument($image->s3_key);
            
            // Delete from database
            if ($reportImagesTable->delete($image)) {
                $this->Flash->success(__('Image deleted successfully.'));
            } else {
                $this->Flash->error(__('Failed to delete image from database.'));
            }
            
        } catch (\Exception $e) {
            \Cake\Log\Log::error('MEG Report image deletion error: ' . $e->getMessage());
            $this->Flash->error(__('An error occurred while deleting the image.'));
        }
        
        return $this->redirect(['action' => 'editMegReport', $reportId]);
    }

    /**
     * Generate and download MEG Report as PowerPoint
     */
    public function downloadMegReport($id = null)
    {
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        // Get the report with images
        $report = $this->Reports->find()
            ->contain([
                'Cases' => ['PatientUsers', 'Hospitals'],
                'ReportImages' => ['sort' => ['ReportImages.slide_order' => 'ASC']]
            ])
            ->matching('Cases.CaseAssignments', function ($q) use ($userId) {
                return $q->where(['CaseAssignments.assigned_to' => $userId]);
            })
            ->where([
                'Reports.id' => $id,
                'Reports.type' => 'PPT'
            ])
            ->first();
            
        if (!$report) {
            $this->Flash->error(__('MEG Report not found or you do not have access.'));
            return $this->redirect(['action' => 'index']);
        }
        
        if (empty($report->report_images)) {
            $this->Flash->error(__('Please add at least one image to the MEG Report before downloading.'));
            return $this->redirect(['action' => 'editMegReport', $id]);
        }
        
        try {
            // Create PowerPoint presentation
            $objPHPPresentation = new \PhpOffice\PhpPresentation\PhpPresentation();
            $objPHPPresentation->getDocumentProperties()
                ->setCreator('MEG System')
                ->setTitle('MEG Report - Case #' . $report->case->id)
                ->setSubject('MEG Report')
                ->setDescription('MEG Report for ' . $report->case->patient_user->first_name . ' ' . $report->case->patient_user->last_name);
            
            // Set layout to 16:9
            $objPHPPresentation->getLayout()->setDocumentLayout(\PhpOffice\PhpPresentation\DocumentLayout::LAYOUT_SCREEN_16X9);
            
            // Remove default slide
            $objPHPPresentation->removeSlideByIndex(0);
            
            $s3Service = new \App\Lib\S3DocumentService();
            $tempImageFiles = []; // Keep track of temp files for cleanup
            
            // Add slides for each image
            foreach ($report->report_images as $index => $image) {
                $slide = $objPHPPresentation->createSlide();
                
                // Download image from S3 to temp file
                $tempImagePath = tempnam(sys_get_temp_dir(), 'ppt_img_');
                $tempImageFiles[] = $tempImagePath; // Track for cleanup later
                
                if ($s3Service->isS3Enabled()) {
                    // Download from S3
                    $s3Client = $s3Service->getS3Client();
                    $result = $s3Client->getObject([
                        'Bucket' => $s3Service->getBucket(),
                        'Key' => $image->s3_key,
                    ]);
                    file_put_contents($tempImagePath, $result['Body']);
                } else {
                    // Copy from local storage
                    $localPath = WWW_ROOT . $image->file_path;
                    if (file_exists($localPath)) {
                        copy($localPath, $tempImagePath);
                    }
                }
                
                // Get image dimensions
                $imageInfo = getimagesize($tempImagePath);
                if ($imageInfo === false) {
                    $this->log('Failed to get image info for: ' . $tempImagePath, 'error');
                    continue;
                }
                
                $imgWidth = $imageInfo[0];
                $imgHeight = $imageInfo[1];
                
                // PHPPresentation 16:9 slide dimensions
                // LAYOUT_SCREEN_16X9 uses: cx=9144000 EMUs, cy=5143500 EMUs
                // At 914400 EMUs per inch and 96 DPI:
                // Width: 10 inches × 96 DPI = 960 pixels
                // Height: 5.625 inches × 96 DPI = 540 pixels
                $slideWidth = 960;
                $slideHeight = 540;
                
                // Add margins for professional spacing
                $marginLeft = 40;   // Left margin
                $marginRight = 40;  // Right margin  
                $marginTop = 50;    // Top margin
                $marginBottom = 50; // Bottom margin
                
                // Calculate available space for image
                $maxWidth = $slideWidth - $marginLeft - $marginRight;   // 880
                $maxHeight = $slideHeight - $marginTop - $marginBottom; // 440
                
                // Calculate scale to fit image within available space while maintaining aspect ratio
                $scaleWidth = $maxWidth / $imgWidth;
                $scaleHeight = $maxHeight / $imgHeight;
                $scale = min($scaleWidth, $scaleHeight);
                
                // Calculate final dimensions
                $newWidth = (int)($imgWidth * $scale);
                $newHeight = (int)($imgHeight * $scale);
                
                // Center the image horizontally and vertically within available space
                $offsetX = $marginLeft + (int)(($maxWidth - $newWidth) / 2);
                $offsetY = $marginTop + (int)(($maxHeight - $newHeight) / 2);
                
                // Add image to slide
                $shape = $slide->createDrawingShape();
                $shape->setPath($tempImagePath);
                $shape->setWidth($newWidth);
                $shape->setHeight($newHeight);
                $shape->setOffsetX($offsetX);
                $shape->setOffsetY($offsetY);
            }
            
            // Generate filename
            $filename = 'MEG_Report_Case_' . $report->case->id . '_' . date('Ymd_His') . '.pptx';
            
            // Save to temp file
            $tempPptPath = tempnam(sys_get_temp_dir(), 'ppt_');
            $oWriterPPTX = new \PhpOffice\PhpPresentation\Writer\PowerPoint2007($objPHPPresentation);
            $oWriterPPTX->save($tempPptPath);
            
            // Now clean up temp image files after presentation is saved
            foreach ($tempImageFiles as $tempFile) {
                @unlink($tempFile);
            }
            
            // Send file to browser
            $this->response = $this->response
                ->withFile($tempPptPath, ['download' => true, 'name' => $filename])
                ->withType('application/vnd.openxmlformats-officedocument.presentationml.presentation');
            
            // Clean up will happen after response is sent
            return $this->response;
            
        } catch (\Exception $e) {
            // Clean up temp image files on error
            if (isset($tempImageFiles)) {
                foreach ($tempImageFiles as $tempFile) {
                    @unlink($tempFile);
                }
            }
            
            $this->log('PowerPoint generation error: ' . $e->getMessage(), 'error');
            $this->Flash->error(__('Failed to generate PowerPoint: ' . $e->getMessage()));
            return $this->redirect(['action' => 'editMegReport', $id]);
        }
    }

    /**
     * Save slide text (AJAX) - NOT USED ANYMORE, keeping for backward compatibility
     */
    public function saveSlideText()
    {
        $this->request->allowMethod(['post']);
        $this->autoRender = false;
        
        // Return success without doing anything
        return $this->response->withType('application/json')
            ->withStringBody(json_encode(['success' => true, 'message' => 'Text fields removed from UI']));
        

    }

    /**
     * Get slide image from S3
     */
    public function getSlideImage($imageId = null)
    {
        $this->request->allowMethod(['get']);
        $user = $this->request->getAttribute('identity');
        $userId = $user->getIdentifier();
        
        $reportImagesTable = $this->fetchTable('ReportImages');
        
        // Get image with report verification
        $image = $reportImagesTable->find()
            ->contain(['Reports' => ['Cases' => ['CaseAssignments']]])
            ->where(['ReportImages.id' => $imageId])
            ->first();
            
        if (!$image) {
            throw new \Cake\Http\Exception\NotFoundException('Image not found');
        }
        
        // Verify access
        $hasAccess = false;
        foreach ($image->report->case->case_assignments as $assignment) {
            if ($assignment->assigned_to === $userId) {
                $hasAccess = true;
                break;
            }
        }
        
        if (!$hasAccess) {
            throw new \Cake\Http\Exception\ForbiddenException('Access denied');
        }
        
        try {
            // Get download URL from S3 (presigned URL)
            $s3Service = new \App\Lib\S3DocumentService();
            $downloadUrl = $s3Service->getDownloadUrl($image->s3_key, 5);
            
            if ($downloadUrl) {
                // Redirect to download URL
                return $this->redirect($downloadUrl);
            } else {
                throw new \Exception('Failed to generate download URL');
            }
            
        } catch (\Exception $e) {
            \Cake\Log\Log::error('Error retrieving slide image: ' . $e->getMessage());
            throw new \Cake\Http\Exception\InternalErrorException('Failed to retrieve image');
        }
    }

    /**
     * Resize image to fit PowerPoint slide dimensions
     * We resize to reasonable dimensions that work well in 16:9 presentations
     * Max dimensions are set to balance quality and file size
     *
     * @param mixed $file Uploaded file object
     * @return array File array with resized image
     */
    private function resizeImageForPPT($file)
    {
        // Maximum dimensions - suitable for 16:9 slides with margins
        // This ensures images look good without being too large
        $maxWidth = 1920;  // Full HD width (good for presentations)
        $maxHeight = 1080; // Full HD height (16:9 ratio)
        
        try {
            // Get file info
            $tmpName = tempnam(sys_get_temp_dir(), 'upload_');
            $stream = $file->getStream();
            file_put_contents($tmpName, $stream->getContents());
            
            $mimeType = $file->getClientMediaType();
            $originalName = $file->getClientFilename();
            
            // Get image info
            $imageInfo = getimagesize($tmpName);
            if ($imageInfo === false) {
                // Not a valid image, return original
                return $file;
            }
            
            list($width, $height) = $imageInfo;
            
            // Check if resizing is needed
            if ($width <= $maxWidth && $height <= $maxHeight) {
                // Image already fits, no need to resize
                return [
                    'tmp_name' => $tmpName,
                    'name' => $originalName,
                    'type' => $mimeType,
                    'size' => filesize($tmpName)
                ];
            }
            
            // Calculate new dimensions maintaining aspect ratio
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int)round($width * $ratio);
            $newHeight = (int)round($height * $ratio);
            
            // Create source image based on type
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    $srcImage = imagecreatefromjpeg($tmpName);
                    break;
                case IMAGETYPE_PNG:
                    $srcImage = imagecreatefrompng($tmpName);
                    break;
                case IMAGETYPE_GIF:
                    $srcImage = imagecreatefromgif($tmpName);
                    break;
                default:
                    // Unsupported type, return original
                    return [
                        'tmp_name' => $tmpName,
                        'name' => $originalName,
                        'type' => $mimeType,
                        'size' => filesize($tmpName)
                    ];
            }
            
            if (!$srcImage) {
                // Failed to create image, return original
                return [
                    'tmp_name' => $tmpName,
                    'name' => $originalName,
                    'type' => $mimeType,
                    'size' => filesize($tmpName)
                ];
            }
            
            // Create new image with resized dimensions
            $dstImage = imagecreatetruecolor($newWidth, $newHeight);
            
            // Preserve transparency for PNG and GIF
            if ($imageInfo[2] == IMAGETYPE_PNG || $imageInfo[2] == IMAGETYPE_GIF) {
                imagealphablending($dstImage, false);
                imagesavealpha($dstImage, true);
                $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
                imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
            }
            
            // Resize the image
            imagecopyresampled(
                $dstImage, 
                $srcImage, 
                0, 0, 0, 0, 
                $newWidth, 
                $newHeight, 
                $width, 
                $height
            );
            
            // Save resized image to temp file
            $resizedTmpName = tempnam(sys_get_temp_dir(), 'resized_');
            
            switch ($imageInfo[2]) {
                case IMAGETYPE_JPEG:
                    imagejpeg($dstImage, $resizedTmpName, 90); // 90% quality
                    break;
                case IMAGETYPE_PNG:
                    imagepng($dstImage, $resizedTmpName, 8); // Compression level 8
                    break;
                case IMAGETYPE_GIF:
                    imagegif($dstImage, $resizedTmpName);
                    break;
            }
            
            // Clean up
            imagedestroy($srcImage);
            imagedestroy($dstImage);
            @unlink($tmpName);
            
            // Log resize info
            \Cake\Log\Log::info('Image resized for PPT', [
                'original' => "{$width}x{$height}",
                'resized' => "{$newWidth}x{$newHeight}",
                'ratio' => round($ratio * 100) . '%'
            ]);
            
            return [
                'tmp_name' => $resizedTmpName,
                'name' => $originalName,
                'type' => $mimeType,
                'size' => filesize($resizedTmpName)
            ];
            
        } catch (\Exception $e) {
            \Cake\Log\Log::error('Image resize error: ' . $e->getMessage());
            // On error, try to return original file
            if (isset($tmpName) && file_exists($tmpName)) {
                return [
                    'tmp_name' => $tmpName,
                    'name' => $originalName ?? 'image.jpg',
                    'type' => $mimeType ?? 'image/jpeg',
                    'size' => filesize($tmpName)
                ];
            }
            return $file;
        }
    }
}