<?php
declare(strict_types=1);

namespace App\Controller\Doctor;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use App\Lib\UserActivityLogger;
use App\Constants\SiteConstants;
use Cake\Log\Log;
use App\Service\DocumentContentService;
use App\Service\CaseStatusService;
use App\Service\DocumentAnalysisService;
use Cake\Core\Configure;

/**
 * Cases Controller (Doctor)
 *
 * Restrictions:
 * - Can view only cases assigned to them
 * - Can edit cases
 * - Can upload documents
 * - CANNOT create new cases
 * - CANNOT assign cases to others
 * - CANNOT delete cases
 *
 * @property \App\Model\Table\CasesTable $Cases
 * @property \App\Model\Table\CaseVersionsTable $CaseVersions
 * @property \App\Model\Table\CaseAuditsTable $CaseAudits
 * @property \App\Model\Table\UsersTable $Users
 */
class CasesController extends AppController
{
    /**
     * User Activity Logger instance
     */
    private $activityLogger;
    
    /**
     * Case Status Service instance
     */
    private $caseStatusService;

    /**
     * Initialize method
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->activityLogger = new UserActivityLogger();
        $this->caseStatusService = new CaseStatusService();
    }
    
    /**
     * Before filter callback
     *
     * @param \Cake\Event\EventInterface $event The beforeFilter event
     * @return \Cake\Http\Response|null
     */
    public function beforeFilter(\Cake\Event\EventInterface $event): ?\Cake\Http\Response
    {
        parent::beforeFilter($event);
        return null;
    }
    /**
     * Index method - Shows only cases assigned to the current doctor
     *
     * @return \Psr\Http\Message\ResponseInterface|null|void Renders view
     */
    public function index()
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(array('prefix' => 'Doctor', 'controller' => 'Dashboard', 'action' => 'index'));
        }

        // Get filter parameters
        $status = $this->request->getQuery('status');
        $priority = $this->request->getQuery('priority');
        $search = $this->request->getQuery('search');

        // Build query - show cases assigned to current doctor OR where doctor was ever assigned
        $query = $this->Cases->find()
            ->contain(array(
                'Users', 
                'PatientUsers', 
                'CurrentUsers' => array('Roles'),
                'Hospitals',
                'Departments',
                'Sedations',
                'CasesExamsProcedures' => array(
                    'ExamsProcedures' => array(
                        'Exams' => array('Modalities'),
                        'Procedures'
                    )
                ),
                'CaseAssignments'
            ))
            ->where(array(
                'Cases.hospital_id' => $currentHospital->id,
                'OR' => array(
                    array('Cases.current_user_id' => $user->id),
                    function ($exp) use ($user) {
                        $caseAssignments = $this->fetchTable('CaseAssignments');
                        return $exp->exists(
                            $caseAssignments->find()
                                ->select(array('id'))
                                ->where(array(
                                    'CaseAssignments.case_id = Cases.id',
                                    'CaseAssignments.user_id' => $user->id
                                ))
                        );
                    }
                )
            ));

        // Apply filters - use role-based status for doctors
        if ($status && $status !== 'all') {
            $query->where(array('Cases.doctor_status' => $status));
        }
        
        if ($priority && $priority !== 'all') {
            $query->where(array('Cases.priority' => $priority));
        }

        if ($search) {
            $searchConditions = array(
                'OR' => array(
                    'Users.first_name LIKE' => '%' . $search . '%',
                    'Users.last_name LIKE' => '%' . $search . '%',
                )
            );
            
            // Only search by ID if the search term is numeric
            if (is_numeric($search)) {
                $searchConditions['OR']['Cases.id'] = (int)$search;
            }
            
            $query->where($searchConditions);
        }

        // Order by priority and creation date
        $query->orderBy(array(
            'CASE WHEN Cases.priority = "' . SiteConstants::PRIORITY_HIGH . '" THEN 1 '
            . 'WHEN Cases.priority = "' . SiteConstants::PRIORITY_MEDIUM . '" THEN 2 '
            . 'WHEN Cases.priority = "' . SiteConstants::PRIORITY_LOW . '" THEN 3 '
            . 'ELSE 4 END' => 'ASC',
            'Cases.created' => 'DESC'
        ));

        $cases = $this->paginate($query, array('limit' => 20));

        // Get counts for status badges - using role-based status for doctors
        // Doctors don't have 'draft' status - they start with 'assigned' when case is assigned to them
        $statusCounts = array();
        foreach (array(SiteConstants::CASE_STATUS_ASSIGNED, SiteConstants::CASE_STATUS_IN_PROGRESS, SiteConstants::CASE_STATUS_COMPLETED, SiteConstants::CASE_STATUS_CANCELLED) as $statusValue) {
            $count = $this->Cases->find()
                ->contain(array('CaseAssignments'))
                ->where(array(
                    'Cases.hospital_id' => $currentHospital->id,
                    'Cases.doctor_status' => $statusValue,
                    'OR' => array(
                        array('Cases.current_user_id' => $user->id),
                        function ($exp) use ($user) {
                            $caseAssignments = $this->fetchTable('CaseAssignments');
                            return $exp->exists(
                                $caseAssignments->find()
                                    ->select(array('id'))
                                    ->where(array(
                                        'CaseAssignments.case_id = Cases.id',
                                        'CaseAssignments.user_id' => $user->id
                                    ))
                            );
                        }
                    )
                ))
                ->count();
            $statusCounts[$statusValue] = $count;
        }

        $this->set(compact('cases', 'currentHospital', 'statusCounts', 'user'));
    }

    /**
     * View method
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Load full user entity with roles for "You" pattern in templates
        $usersTable = $this->fetchTable('Users');
        $userWithRoles = $usersTable->get($user->id, array('contain' => array('Roles')));

        try {
            $case = $this->Cases->get($id, array(
                'contain' => array(
                    'Users' => array('Roles'),
                    'PatientUsers' => array('Roles'),
                    'CurrentUsers' => array('Roles'),
                    'Hospitals',
                    'Departments',
                    'Sedations',
                    'CaseVersions' => array('Users'),
                    'CasesExamsProcedures' => array(
                        'ExamsProcedures' => array(
                            'Exams' => array('Modalities', 'Departments'),
                            'Procedures'
                        ),
                        'Documents' => array('Users')
                    ),
                    'CaseAssignments',
                    'CaseAssignments' => array(
                        'AssignedToUsers' => array('Roles'),
                        'Users' => array('Roles'),
                        'sort' => array('CaseAssignments.timestamp' => 'DESC')
                    ),
                    'CaseAudits' => function($q) {
                        return $q->contain(['ChangedByUsers'])
                                ->orderBy(['CaseAudits.timestamp' => 'DESC'])
                                ->limit(50);
                    },
                    'Documents' => array(
                        'Users',
                        'sort' => array('Documents.created' => 'DESC')
                    )
                )
            ));

            // Check if doctor has access to this case
            $hasAssignment = false;
            
            // Check if user is current assignee
            if ($case->current_user_id === $user->id) {
                $hasAssignment = true;
            } else {
                // Check if user was ever assigned to this case
                foreach ($case->case_assignments as $assignment) {
                    if ($assignment->user_id === $user->id) {
                        $hasAssignment = true;
                        break;
                    }
                }
            }
            
            if (!$hasAssignment) {
                throw new RecordNotFoundException(__('Case not found or you do not have access to this case.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found or you do not have access to this case.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Auto-progress: Change doctor_status from 'assigned' to 'in_progress' on first view
        if ($case->doctor_status === SiteConstants::CASE_STATUS_ASSIGNED) {
            $case->doctor_status = SiteConstants::CASE_STATUS_IN_PROGRESS;
            
            // Also update global status if it's still 'assigned'
            if ($case->status === SiteConstants::CASE_STATUS_ASSIGNED) {
                $case->status = SiteConstants::CASE_STATUS_IN_PROGRESS;
            }
            
            if ($this->Cases->save($case)) {
                // Log the status change
                $this->activityLogger->log(
                    SiteConstants::EVENT_CASE_UPDATED,
                    array(
                        'user_id' => $user->id,
                        'request' => $this->request,
                        'event_data' => array(
                            'case_id' => $case->id,
                            'action' => 'auto_status_change',
                            'old_doctor_status' => SiteConstants::CASE_STATUS_ASSIGNED,
                            'new_doctor_status' => SiteConstants::CASE_STATUS_IN_PROGRESS,
                            'hospital_id' => $currentHospital->id,
                            'trigger' => 'doctor_first_view'
                        )
                    )
                );
            }
        }

        // Check if S3 is enabled
        $isS3Enabled = Configure::read('S3.enabled', false);

        // Check if case has any reports
        $reportsTable = $this->fetchTable('Reports');
        $existingReports = $reportsTable->find()
            ->contain(['Users' => ['Roles']]) // Include user information
            ->where(['case_id' => $id])
            ->all();

        // Get case version history
        $caseVersionsTable = $this->fetchTable('CaseVersions');
        $caseVersions = $caseVersionsTable->find()
            ->contain(array('Users'))
            ->where(array('case_id' => $case->id))
            ->orderBy(array('version_number' => 'DESC'))
            ->all();

        $this->set(compact('case', 'caseVersions', 'currentHospital', 'isS3Enabled', 'existingReports'));
        // Pass user with roles for role badge helper
        $this->set('user', $userWithRoles);
    }

    /**
     * Edit method - DISABLED for doctors
     * Doctors can view cases but cannot edit them
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null Redirects to view action.
     */
    public function edit($id = null)
    {
        // Doctors cannot edit cases - redirect to view
        $this->Flash->error(__('Doctors do not have permission to edit cases. You can only view case details.'));
        return $this->redirect(array('action' => 'view', $id));
    }

    /**
     * Upload Document method - Doctors CAN upload documents
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects on successful upload, renders view otherwise.
     */
    public function uploadDocument($id = null)
    {
        $this->request->allowMethod(array('post', 'put'));
        
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(array('action' => 'index'));
        }

        try {
            $case = $this->Cases->get($id, array(
                'contain' => array('CaseAssignments')
            ));

            // Check if doctor has access to this case
            $hasAssignment = false;
            if ($case->current_user_id === $user->id) {
                $hasAssignment = true;
            } else {
                foreach ($case->case_assignments as $assignment) {
                    if ($assignment->user_id === $user->id) {
                        $hasAssignment = true;
                        break;
                    }
                }
            }
            
            if (!$hasAssignment) {
                throw new RecordNotFoundException(__('Case not found or you do not have access to this case.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found or you do not have access to this case.'));
            return $this->redirect(array('action' => 'index'));
        }

        if ($this->request->is(array('post', 'put'))) {
            $data = $this->request->getData();
            
            // Get the uploaded file (it's an UploadedFile object in CakePHP 5)
            $uploadedFile = $data['document_file'] ?? null;
            
            if (empty($uploadedFile) || !is_object($uploadedFile)) {
                $this->Flash->error(__('Please select a file to upload.'));
                return $this->redirect(array('action' => 'view', $case->id));
            }
            
            // Check for upload errors with specific messages
            $uploadError = $uploadedFile->getError();
            if ($uploadError !== UPLOAD_ERR_OK) {
                $errorMessage = $this->getUploadErrorMessage($uploadError);
                $this->Flash->error($errorMessage);
                return $this->redirect(array('action' => 'view', $case->id));
            }

            // Get and validate procedure ID (convert empty string to null, cast to int)
            $procedureId = null;
            if (!empty($data['cases_exams_procedure_id'])) {
                $procedureId = (int)$data['cases_exams_procedure_id'];
            }

            // Load S3 service
            $s3Service = new \App\Lib\S3DocumentService();
            
            // Upload to S3 (S3DocumentService should handle UploadedFile object)
            $uploadResult = $s3Service->uploadDocument(
                $uploadedFile,
                $case->id,
                $case->patient_id ?? null,
                $data['document_type'] ?? 'other',
                $procedureId
            );

            if ($uploadResult['success']) {
                // Save document record to database
                $documentsTable = $this->fetchTable('Documents');
                $document = $documentsTable->newEntity(array(
                    'case_id' => $case->id,
                    'user_id' => $user->id,
                    'cases_exams_procedure_id' => $data['cases_exams_procedure_id'] ?? null,
                    'document_type' => $data['document_type'] ?? 'other',
                    'file_path' => $uploadResult['file_path'],
                    'file_type' => $uploadResult['mime_type'],
                    'file_size' => $uploadResult['file_size'],
                    'original_filename' => $uploadResult['original_name'],
                    'description' => $data['description'] ?? '',
                    'uploaded_at' => new \DateTime()
                ));

                if ($documentsTable->save($document)) {
                    // If linked to a procedure, update its status to completed
                    if (!empty($data['cases_exams_procedure_id'])) {
                        $cepTable = $this->fetchTable('CasesExamsProcedures');
                        $cep = $cepTable->get((int)$data['cases_exams_procedure_id']);
                        $cep->status = SiteConstants::CASE_STATUS_COMPLETED;
                        $cepTable->save($cep);
                    }

                    // Log activity
                    $this->activityLogger->log(
                        SiteConstants::EVENT_DOCUMENT_UPLOADED,
                        array(
                            'user_id' => $user->id,
                            'request' => $this->request,
                            'event_data' => array(
                                'case_id' => $case->id,
                                'document_id' => $document->id,
                                'document_type' => $document->document_type,
                                'hospital_id' => $currentHospital->id
                            )
                        )
                    );

                    $this->Flash->success(__('Document uploaded successfully.'));
                } else {
                    // Log validation errors for debugging
                    $errors = $document->getErrors();
                    Log::error('Failed to save document: ' . json_encode($errors));
                    
                    $this->Flash->error(__('Failed to save document record. Please try again.'));
                    // Clean up S3 file if database save failed
                    $s3Service->deleteDocument($uploadResult['file_path']);
                }
            } else {
                $this->Flash->error(__('Upload failed: {0}', $uploadResult['error'] ?? 'Unknown error'));
            }

            return $this->redirect(array('action' => 'view', $case->id));
        }

        $this->set(compact('case', 'currentHospital'));
    }

    /**
     * Download Document method
     *
     * @param string|null $id Document id.
     * @return \Psr\Http\Message\ResponseInterface|null Sends file download
     */
    public function downloadDocument($id = null)
    {
        $user = $this->getAuthUser();
        
        try {
            $documentsTable = $this->fetchTable('Documents');
            $document = $documentsTable->get($id, array(
                'contain' => array('Cases' => array('CaseAssignments'))
            ));

            // Check if doctor has access to this case
            $case = $document->case;
            $hasAssignment = false;
            
            if ($case->current_user_id === $user->id) {
                $hasAssignment = true;
            } else {
                foreach ($case->case_assignments as $assignment) {
                    if ($assignment->user_id === $user->id) {
                        $hasAssignment = true;
                        break;
                    }
                }
            }
            
            if (!$hasAssignment) {
                throw new RecordNotFoundException(__('Document not found or access denied.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Document not found or access denied.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Check if S3 or local storage
        if ($document->is_s3) {
            // Download from S3
            try {
                $s3Client = \Aws\S3\S3Client::factory(array(
                    'version' => 'latest',
                    'region' => Configure::read('S3.region'),
                    'credentials' => array(
                        'key' => Configure::read('S3.key'),
                        'secret' => Configure::read('S3.secret')
                    )
                ));

                $bucket = Configure::read('S3.bucket');
                $cmd = $s3Client->getCommand('GetObject', array(
                    'Bucket' => $bucket,
                    'Key' => $document->file_path,
                    'ResponseContentDisposition' => 'attachment; filename="' . $document->file_name . '"'
                ));

                $request = $s3Client->createPresignedRequest($cmd, '+5 minutes');
                $presignedUrl = (string) $request->getUri();

                return $this->redirect($presignedUrl);
                
            } catch (\Exception $e) {
                Log::error('S3 download error: ' . $e->getMessage());
                $this->Flash->error(__('Failed to download file from cloud storage.'));
                return $this->redirect(array('action' => 'view', $document->case_id));
            }
        } else {
            // Download from local storage
            $filePath = WWW_ROOT . 'files' . DS . $document->file_path;
            
            if (!file_exists($filePath)) {
                $this->Flash->error(__('File not found.'));
                return $this->redirect(array('action' => 'view', $document->case_id));
            }

            $this->response = $this->response->withFile(
                $filePath,
                array('download' => true, 'name' => $document->file_name)
            );
            
            return $this->response;
        }
    }

    /**
     * Add method - DISABLED for doctors
     * Doctors cannot create new cases
     *
     * @return \Psr\Http\Message\ResponseInterface|null Redirects with error
     */
    public function add()
    {
        $this->Flash->error(__('Doctors do not have permission to create new cases.'));
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * Delete method - DISABLED for doctors
     * Doctors cannot delete cases
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null Redirects with error
     */
    public function delete($id = null)
    {
        $this->Flash->error(__('Doctors do not have permission to delete cases.'));
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * Assign method - DISABLED for doctors
     * Doctors cannot assign cases to others
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null Redirects with error
     */
    public function assign($id = null)
    {
        $this->Flash->error(__('Doctors do not have permission to assign cases to others.'));
        return $this->redirect(array('action' => 'view', $id));
    }

    /**
     * Get authenticated user
     *
     * @return \App\Model\Entity\User Current authenticated user
     */
    private function getAuthUser()
    {
        $identity = $this->request->getAttribute('identity');
        return $identity->getOriginalData();
    }

    /**
     * Analyze Document method - AI analysis for uploaded documents
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface JSON response with analysis results.
     */
    public function analyzeDocument($id = null)
    {
        $this->request->allowMethod(array('post'));
        $this->viewBuilder()->setClassName('Json');
        
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(array(
                    'success' => false,
                    'error' => 'No hospital context found'
                )));
        }

        try {
            $case = $this->Cases->get($id, array(
                'contain' => array(
                    'CasesExamsProcedures' => array(
                        'ExamsProcedures' => array(
                            'Exams' => array('Modalities'),
                            'Procedures'
                        )
                    ),
                    'CaseAssignments'
                )
            ));
            
            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found.'));
            }

            // Check if user has access (current assignee OR was ever assigned)
            $hasAssignment = false;
            if ($case->current_user_id === $user->id) {
                $hasAssignment = true;
            } else {
                // Check if user was ever assigned to this case
                foreach ($case->case_assignments as $assignment) {
                    if ($assignment->user_id === $user->id) {
                        $hasAssignment = true;
                        break;
                    }
                }
            }
            if (!$hasAssignment) {
                throw new RecordNotFoundException(__('Case not found.'));
            }

        } catch (RecordNotFoundException $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(array(
                    'success' => false,
                    'error' => 'Case not found'
                )));
        }

        // Get uploaded file
        $file = $this->request->getData('file');
        
        if (empty($file)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(array(
                    'success' => false,
                    'error' => 'No file provided'
                )));
        }

        // Convert UploadedFile object to array for analysis service
        try {
            // Check if it's already an array (shouldn't happen, but defensive coding)
            if (is_array($file)) {
                $fileData = $file;
            } else {
                // It's an UploadedFile object
                $stream = $file->getStream();
                $tmpPath = $stream->getMetadata('uri');
                
                // If stream metadata doesn't give us a path, save to temp file
                if (empty($tmpPath) || !file_exists($tmpPath)) {
                    $tmpPath = tempnam(sys_get_temp_dir(), 'doc_analysis_');
                    $stream->rewind();
                    file_put_contents($tmpPath, $stream->getContents());
                    $cleanupTempFile = true;
                } else {
                    $cleanupTempFile = false;
                }
                
                $fileData = array(
                    'name' => $file->getClientFilename(),
                    'type' => $file->getClientMediaType(),
                    'tmp_name' => $tmpPath,
                    'size' => $file->getSize(),
                    'error' => $file->getError()
                );
            }
        } catch (\Exception $e) {
            Log::error('File conversion error: ' . $e->getMessage());
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(array(
                    'success' => false,
                    'error' => 'Failed to process uploaded file'
                )));
        }

        // Prepare procedures list for analysis
        $procedureOptions = array();
        if (!empty($case->cases_exams_procedures)) {
            foreach ($case->cases_exams_procedures as $cep) {
                $examName = $cep->exams_procedure->exam->name ?? 'Unknown';
                $procedureName = $cep->exams_procedure->procedure->name ?? 'Unknown';
                $modalityName = $cep->exams_procedure->exam->modality->name ?? '';
                $label = "{$examName} - {$procedureName}";
                if ($modalityName) {
                    $label .= " ({$modalityName})";
                }
                $procedureOptions[$cep->id] = $label;
            }
        }

        // Analyze document
        $analysisService = new \App\Service\DocumentAnalysisService();
        $analysis = $analysisService->analyzeDocument($fileData, $procedureOptions);

        // Clean up temporary file if we created one
        if (isset($cleanupTempFile) && $cleanupTempFile && isset($fileData['tmp_name']) && file_exists($fileData['tmp_name'])) {
            @unlink($fileData['tmp_name']);
        }

        // Log activity
        if ($analysis['success']) {
            $this->activityLogger->log(
                SiteConstants::EVENT_CASE_UPDATED,
                array(
                    'user_id' => $user->id,
                    'request' => $this->request,
                    'event_data' => array(
                        'case_id' => $case->id,
                        'action' => 'document_analyzed',
                        'hospital_id' => $currentHospital->id,
                        'detected_type' => $analysis['detected_type'] ?? null,
                        'method' => $analysis['method'] ?? 'unknown'
                    )
                )
            );
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode($analysis));
    }

    /**
     * View Document method - Returns document info for preview modal
     *
     * @param string|null $id Document id.
     * @return \Psr\Http\Message\ResponseInterface JSON response with document info.
     */
    public function viewDocument($id = null)
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(array('action' => 'index'));
        }

        try {
            $documentsTable = $this->fetchTable('Documents');
            $document = $documentsTable->get($id, array(
                'contain' => array('Cases' => ['CaseAssignments'])
            ));
            
            // Verify hospital access
            if ($document->case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Document not found.'));
            }

            // Check if user has access (current assignee OR was ever assigned)
            $hasAssignment = false;
            if ($document->case->current_user_id === $user->id) {
                $hasAssignment = true;
            } else {
                // Check if user was ever assigned to this case
                foreach ($document->case->case_assignments as $assignment) {
                    if ($assignment->user_id === $user->id) {
                        $hasAssignment = true;
                        break;
                    }
                }
            }
            if (!$hasAssignment) {
                throw new RecordNotFoundException(__('Document not found.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Document not found.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Get download URL from S3 (or local path)
        $s3Service = new \App\Lib\S3DocumentService();
        $fileUrl = $s3Service->getDownloadUrl($document->file_path);

        if ($fileUrl) {
            // Log activity
            $this->activityLogger->log(
                'document_viewed',
                array(
                    'user_id' => $user->id,
                    'request' => $this->request,
                    'event_data' => array(
                        'case_id' => $document->case_id,
                        'document_id' => $document->id,
                        'document_type' => $document->document_type,
                        'hospital_id' => $currentHospital->id
                    )
                )
            );

            // Return JSON with document info for preview modal
            $this->viewBuilder()->setLayout('ajax');
            $this->set(compact('document', 'fileUrl'));
            $this->set('_serialize', array('document', 'fileUrl'));
            
            // Set response as JSON
            $this->response = $this->response->withType('application/json');
            $this->response = $this->response->withStringBody(json_encode(array(
                'success' => true,
                'document' => array(
                    'id' => $document->id,
                    'filename' => $document->original_filename,
                    'type' => $document->file_type,
                    'size' => $document->file_size,
                    'url' => $fileUrl
                )
            )));
            
            return $this->response;
        } else {
            $this->response = $this->response->withType('application/json');
            $this->response = $this->response->withStringBody(json_encode(array(
                'success' => false,
                'error' => 'Failed to generate view link'
            )));
            return $this->response;
        }
    }

    /**
     * Proxy document method - Serves document content through application
     *
     * @param string|null $id Document id.
     * @return \Psr\Http\Message\ResponseInterface|null|void
     */
    public function proxyDocument($id = null)
    {
        $this->viewBuilder()->disableAutoLayout();
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            throw new \Cake\Http\Exception\ForbiddenException(__('No hospital context found'));
        }

        try {
            $documentsTable = $this->fetchTable('Documents');
            $document = $documentsTable->get($id, array(
                'contain' => array('Cases' => ['CaseAssignments'])
            ));
            
            // Verify hospital access
            if ($document->case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Document not found or you do not have access to this document.'));
            }

            // Check if user has access (current assignee OR was ever assigned)
            $hasAssignment = false;
            if ($document->case->current_user_id === $user->id) {
                $hasAssignment = true;
            } else {
                // Check if user was ever assigned to this case
                foreach ($document->case->case_assignments as $assignment) {
                    if ($assignment->user_id === $user->id) {
                        $hasAssignment = true;
                        break;
                    }
                }
            }
            if (!$hasAssignment) {
                throw new RecordNotFoundException(__('Document not found or you do not have access to this document.'));
            }

            // Get file content
                // Get file content (S3 or local)
                $s3DocumentService = new \App\Lib\S3DocumentService();
                if ($s3DocumentService->isS3Enabled() && strpos($document->file_path, 'uploads/') !== 0) {
                    $s3Client = $s3DocumentService->getS3Client();
                    $result = $s3Client->getObject([
                        'Bucket' => $s3DocumentService->getBucket(),
                        'Key' => $document->file_path
                    ]);
                    $fileContent = $result['Body']->getContents();
                } else {
                    $fullPath = WWW_ROOT . str_replace('/', DS, $document->file_path);
                    if (!file_exists($fullPath)) {
                        throw new \Exception('File not found');
                    }
                    $fileContent = file_get_contents($fullPath);
                }
                // Return file with proper content type
                return $this->response
                    ->withType($document->file_type)
                    ->withStringBody($fileContent);

        } catch (RecordNotFoundException $e) {
            throw new \Cake\Http\Exception\NotFoundException(__('Document not found or you do not have access to this document.'));
        } catch (\Exception $e) {
            throw new \Cake\Http\Exception\InternalErrorException(__('Error retrieving document'));
        }
    }

    /**
     * Get user-friendly error message for file upload errors
     *
     * @param int $errorCode PHP upload error code
     * @return string User-friendly error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return __('The uploaded file is too large. Maximum allowed size is determined by server configuration. Please contact your administrator or try a smaller file.');
            case UPLOAD_ERR_FORM_SIZE:
                return __('The uploaded file exceeds the maximum file size of 50MB.');
            case UPLOAD_ERR_PARTIAL:
                return __('The file was only partially uploaded. Please try again.');
            case UPLOAD_ERR_NO_FILE:
                return __('No file was uploaded. Please select a file.');
            case UPLOAD_ERR_NO_TMP_DIR:
                return __('Server error: Missing temporary folder. Please contact your administrator.');
            case UPLOAD_ERR_CANT_WRITE:
                return __('Server error: Failed to write file to disk. Please contact your administrator.');
            case UPLOAD_ERR_EXTENSION:
                return __('Server error: File upload was stopped by a PHP extension. Please contact your administrator.');
            default:
                return __('An unknown error occurred during file upload. Please try again.');
        }
    }

    /**
     * Complete method - Mark a case as completed
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects on successful completion.
     */
    public function complete($id = null)
    {
        $this->request->allowMethod(['post', 'get']);
        
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['action' => 'index']);
        }

        try {
            $case = $this->Cases->get($id, [
                'contain' => ['CaseAssignments']
            ]);

            // Check if doctor has access to this case
            $hasAssignment = false;
            
            // Check if user is current assignee
            if ($case->current_user_id === $user->id) {
                $hasAssignment = true;
            } else {
                // Check if user was ever assigned to this case
                foreach ($case->case_assignments as $assignment) {
                    if ($assignment->user_id === $user->id) {
                        $hasAssignment = true;
                        break;
                    }
                }
            }
            
            if (!$hasAssignment) {
                throw new RecordNotFoundException(__('Case not found or you do not have access to this case.'));
            }

            // Check if case is already completed
            if ($case->status === SiteConstants::CASE_STATUS_COMPLETED) {
                $this->Flash->warning(__('This case is already completed.'));
                return $this->redirect(['action' => 'view', $id]);
            }

            // Use CaseStatusService to cascade completion to all roles
            if ($this->caseStatusService->cascadeCompletion($case, $user->id)) {
                // Log activity
                $this->activityLogger->log(
                    SiteConstants::EVENT_CASE_COMPLETED,
                    [
                        'user_id' => $user->id,
                        'request' => $this->request,
                        'event_data' => [
                            'case_id' => $case->id,
                            'hospital_id' => $currentHospital->id,
                            'completed_by_role' => 'doctor'
                        ]
                    ]
                );

                $this->Flash->success(__('Case has been successfully completed.'));
            } else {
                $this->Flash->error(__('Unable to complete the case. Please try again.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found or you do not have access to this case.'));
        } catch (\Exception $e) {
            Log::error("Error completing case #{$id}: " . $e->getMessage());
            $this->Flash->error(__('An error occurred while completing the case. Please try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Create EEG Report (PDF)
     */
    public function createReport($id = null)
    {
        $user = $this->getAuthUser();
        
        // Get the case with all necessary associations
        $case = $this->Cases->get($id, [
            'contain' => [
                'PatientUsers', 
                'Hospitals',
                'Users',
                'Departments',
                'Sedations',
                'CasesExamsProcedures' => [
                    'ExamsProcedures' => [
                        'Exams' => ['Modalities'],
                        'Procedures'
                    ]
                ]
            ]
        ]);
        
        // Check if doctor has access to this case
        $hasAssignment = false;
        if ($case->current_user_id === $user->id) {
            $hasAssignment = true;
        } else {
            foreach ($case->case_assignments ?? [] as $assignment) {
                if ($assignment->assigned_to === $user->id) {
                    $hasAssignment = true;
                    break;
                }
            }
        }
        
        if (!$hasAssignment) {
            $this->Flash->error(__('You do not have permission to create a report for this case.'));
            return $this->redirect(['action' => 'index']);
        }
        
        // Check if case already has a PDF report
        $reportsTable = $this->fetchTable('Reports');
        $existingReport = $reportsTable->find()
            ->where([
                'case_id' => $id,
                'type' => 'PDF'
            ])
            ->first();
            
        if ($existingReport) {
            $this->Flash->info(__('This case already has a PDF report. Redirecting to edit.'));
            return $this->redirect(['controller' => 'Reports', 'action' => 'edit', $existingReport->id]);
        }
        
        // Create new PDF report
        $report = $reportsTable->newEmptyEntity();
        $reportData = [
            'case_id' => $id,
            'hospital_id' => $case->hospital_id,
            'status' => 'pending',
            'user_id' => $user->id,
            'type' => 'PDF',
            'report_data' => json_encode([
                'content' => ''
            ])
        ];
        
        $report = $reportsTable->patchEntity($report, $reportData);
        
        if ($reportsTable->save($report)) {
            $this->Flash->success(__('EEG Report (PDF) created successfully.'));
            
            $this->activityLogger->log(
                'report_created',
                [
                    'user_id' => $user->id,
                    'request' => $this->request,
                    'event_data' => [
                        'case_id' => $id,
                        'report_id' => $report->id,
                        'type' => 'PDF'
                    ]
                ]
            );
            
            return $this->redirect(['controller' => 'Reports', 'action' => 'edit', $report->id]);
        } else {
            $this->Flash->error(__('Unable to create report. Please try again.'));
            return $this->redirect(['action' => 'view', $id]);
        }
    }

    /**
     * Create MEG Report (PPT)
     */
    public function createMegReport($id = null)
    {
        $user = $this->getAuthUser();
        
        // Get the case
        $case = $this->Cases->get($id, [
            'contain' => [
                'PatientUsers', 
                'Hospitals',
                'Users',
                'Departments',
                'CasesExamsProcedures' => [
                    'ExamsProcedures' => [
                        'Exams' => ['Modalities'],
                        'Procedures'
                    ]
                ]
            ]
        ]);
        
        // Check if doctor has access to this case
        $hasAssignment = false;
        if ($case->current_user_id === $user->id) {
            $hasAssignment = true;
        } else {
            foreach ($case->case_assignments ?? [] as $assignment) {
                if ($assignment->assigned_to === $user->id) {
                    $hasAssignment = true;
                    break;
                }
            }
        }
        
        if (!$hasAssignment) {
            $this->Flash->error(__('You do not have permission to create a report for this case.'));
            return $this->redirect(['action' => 'index']);
        }
        
        // Check if case already has a PPT report
        $reportsTable = $this->fetchTable('Reports');
        $existingReport = $reportsTable->find()
            ->where([
                'case_id' => $id,
                'type' => 'PPT'
            ])
            ->first();
            
        if ($existingReport) {
            $this->Flash->info(__('This case already has a MEG Report. Redirecting to edit.'));
            return $this->redirect(['controller' => 'Reports', 'action' => 'editMegReport', $existingReport->id]);
        }
        
        // Create new PPT report
        $report = $reportsTable->newEmptyEntity();
        $reportData = [
            'case_id' => $id,
            'hospital_id' => $case->hospital_id,
            'status' => 'pending',
            'user_id' => $user->id,
            'type' => 'PPT',
            'report_data' => json_encode([])
        ];
        
        $report = $reportsTable->patchEntity($report, $reportData);
        
        if ($reportsTable->save($report)) {
            $this->Flash->success(__('MEG Report (PPT) created successfully. You can now add images and descriptions.'));
            
            $this->activityLogger->log(
                'meg_report_created',
                [
                    'user_id' => $user->id,
                    'request' => $this->request,
                    'event_data' => [
                        'case_id' => $id,
                        'report_id' => $report->id,
                        'type' => 'PPT'
                    ]
                ]
            );
            
            return $this->redirect(['controller' => 'Reports', 'action' => 'editMegReport', $report->id]);
        } else {
            $this->Flash->error(__('Unable to create MEG Report. Please try again.'));
            return $this->redirect(['action' => 'view', $id]);
        }
    }
}
