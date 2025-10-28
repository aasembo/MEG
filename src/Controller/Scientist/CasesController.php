<?php
declare(strict_types=1);

namespace App\Controller\Scientist;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use App\Lib\UserActivityLogger;
use App\Constants\SiteConstants;
use Cake\Log\Log;
use App\Service\DocumentContentService;
use App\Service\AiReportGenerationService;
use App\Service\ReportAssemblyService;
use App\Service\CaseStatusService;
use Cake\Core\Configure;

/**
 * Cases Controller (Scientist)
 *
 * @property \App\Model\Table\CasesTable $Cases
 * @property \App\Model\Table\CaseVersionsTable $CaseVersions
 * @property \App\Model\Table\CaseAssignmentsTable $CaseAssignments
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
        
        // Set scientist layout for all actions
        $this->viewBuilder()->setLayout('scientist');
    }

    /**
     * Index method - Shows only cases assigned to the current scientist
     *
     * @return \Psr\Http\Message\ResponseInterface|null|void Renders view
     */
    public function index()
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(array('prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index'));
        }

        // Get filter parameters
        $status = $this->request->getQuery('status');
        $priority = $this->request->getQuery('priority');
        $search = $this->request->getQuery('search');

        // Build query - show cases assigned to current scientist OR where scientist was ever assigned
        $query = $this->Cases->find()
            ->contain(array(
                'Users', 
                'PatientUsers', 
                'CurrentUsers', 
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
            ->where([
                'Cases.hospital_id' => $currentHospital->id,
                'OR' => [
                    ['Cases.current_user_id' => $user->id],
                    function ($exp) use ($user) {
                        $caseAssignments = $this->fetchTable('CaseAssignments');
                        return $exp->exists(
                            $caseAssignments->find()
                                ->select(['id'])
                                ->where([
                                    'CaseAssignments.case_id = Cases.id',
                                    'CaseAssignments.user_id' => $user->id
                                ])
                        );
                    }
                ]
            ]);

        // Apply filters - use role-based status for scientists
        if ($status && $status !== 'all') {
            $query->where(array('Cases.scientist_status' => $status));
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

        // Get counts for status badges (matching new visibility logic) - using role-based status for scientists
        // Scientists don't have draft status - their default is 'assigned'
        $statusCounts = array();
        foreach (array(SiteConstants::CASE_STATUS_ASSIGNED, SiteConstants::CASE_STATUS_IN_PROGRESS, SiteConstants::CASE_STATUS_COMPLETED, SiteConstants::CASE_STATUS_CANCELLED) as $statusValue) {
            $count = $this->Cases->find()
                ->contain(['CaseAssignments'])
                ->where([
                    'Cases.hospital_id' => $currentHospital->id,
                    'Cases.scientist_status' => $statusValue,
                    'OR' => [
                        ['Cases.current_user_id' => $user->id],
                        function ($exp) use ($user) {
                            $caseAssignments = $this->fetchTable('CaseAssignments');
                            return $exp->exists(
                                $caseAssignments->find()
                                    ->select(['id'])
                                    ->where([
                                        'CaseAssignments.case_id = Cases.id',
                                        'CaseAssignments.user_id' => $user->id
                                    ])
                            );
                        }
                    ]
                ])
                ->count();
            $statusCounts[$statusValue] = $count;
        }

        $this->set(compact('cases', 'currentHospital', 'statusCounts'));
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
        $userWithRoles = $usersTable->get($user->id, ['contain' => ['Roles']]);

        try {
            $case = $this->Cases->get($id, array(
                'contain' => array(
                    'Users' => ['Roles'],
                    'PatientUsers' => ['Roles'],
                    'CurrentUsers' => ['Roles'],
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
                    'CaseAssignments', // <-- ensure all assignments loaded for access check
                    'CaseAssignments' => array(
                        'AssignedToUsers' => array('Roles'),
                        'Users' => array('Roles'),
                        'sort' => array('CaseAssignments.timestamp' => 'DESC')
                    ),
                    'Documents' => array(
                        'Users',
                        'CasesExamsProcedures' => array(
                            'ExamsProcedures' => array('Exams', 'Procedures')
                        ),
                        'sort' => array('Documents.created' => 'DESC')
                    ),
                    'CaseAudits' => array(
                        'ChangedByUsers' => array('Roles'),
                        'sort' => array('CaseAudits.timestamp' => 'DESC')
                    )
                )
            ));
            
            // Verify hospital access and assignment
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found or you do not have access to this case.'));
            }

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

        // Handle role-based status transition on first view
        $roleColumn = 'scientist_status';
        $currentRoleStatus = $case->{$roleColumn} ?? 'draft';
        
        if ($currentRoleStatus === 'draft') {
            $this->caseStatusService->transitionOnView($case, 'scientist', $user->id);
        }

        // Log activity
        $this->activityLogger->log(
            SiteConstants::EVENT_CASE_VIEWED,
            array(
                'user_id' => $user->id,
                'request' => $this->request,
                'event_data' => array('case_id' => $case->id, 'hospital_id' => $currentHospital->id)
            )
        );

        // Check if S3 is enabled for document preview handling
        $s3Service = new \App\Lib\S3DocumentService();
        $isS3Enabled = $s3Service->isS3Enabled();

        // Get case version history
        $caseVersionsTable = $this->fetchTable('CaseVersions');
        $caseVersions = $caseVersionsTable->find()
            ->contain(array('Users'))
            ->where(array('case_id' => $case->id))
            ->orderBy(array('version_number' => 'DESC'))
            ->all();

        $this->set(compact('case', 'caseVersions', 'currentHospital', 'isS3Enabled'));
        // Pass user with roles for role badge helper
        $this->set('user', $userWithRoles);
    }

    /**
     * Edit method - Scientists cannot edit cases
     * This method exists to prevent access if someone tries to access edit routes
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects with error message
     */
    public function edit($id = null)
    {
        $this->Flash->error(__('Scientists do not have permission to edit cases. You can upload documents and assign cases to doctors.'));
        return $this->redirect(array('action' => 'view', $id));
    }

    /**
     * Assign method - Assigns case to doctor only
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void
     */
    public function assign($id = null)
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(array('action' => 'index'));
        }

        try {
            $case = $this->Cases->get($id, array(
                'contain' => array(
                    'PatientUsers',
                    'CurrentUsers',
                    'Departments',
                    'Sedations',
                    'CaseAssignments' => array(
                        'AssignedToUsers' => array('Roles'),
                        'Users' => array('Roles'),
                        'sort' => array('CaseAssignments.timestamp' => 'DESC')
                    )
                )
            ));
            
            // Verify hospital access and assignment
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found or you do not have access to this case.'));
            }

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

            // Check if case can be assigned - scientists use role-based status (no draft for scientists)
            if (!in_array($case->scientist_status, array(SiteConstants::CASE_STATUS_ASSIGNED, SiteConstants::CASE_STATUS_IN_PROGRESS))) {
                $this->Flash->error(__('This case cannot be assigned in its current status.'));
                return $this->redirect(array('action' => 'view', $id));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found or you do not have access to this case.'));
            return $this->redirect(array('action' => 'index'));
        }

        if ($this->request->is(array('patch', 'post', 'put'))) {
            $data = $this->request->getData();
            
            if (empty($data['assigned_to'])) {
                $this->Flash->error(__('Please select a doctor to assign the case to.'));
            } else {
                // Create assignment record
                $caseAssignmentsTable = $this->fetchTable('CaseAssignments');
                // Ensure case_version_id is set
                $caseVersionId = $case->current_version_id;
                if (empty($caseVersionId)) {
                    $caseVersionsTable = $this->fetchTable('CaseVersions');
                    $latestVersion = $caseVersionsTable->find()
                        ->where(array('case_id' => $case->id))
                        ->orderBy(array('version_number' => 'DESC'))
                        ->first();
                    if ($latestVersion) {
                        $caseVersionId = $latestVersion->id;
                    }
                }
                $assignment = $caseAssignmentsTable->newEntity(array(
                    'case_id' => $case->id,
                    'case_version_id' => $caseVersionId,
                    'user_id' => $user->id,
                    'assigned_to' => $data['assigned_to'],
                    'timestamp' => new \DateTime(),
                    'notes' => isset($data['notes']) ? $data['notes'] : ''
                ));
                
                if ($caseAssignmentsTable->save($assignment)) {
                    // Get the assigned user's role to determine if assigning to doctor
                    // ROLE DETECTION: Users have role_id (belongsTo Roles)
                    // Access via: $user->role->type (singular, not plural)
                    $usersTable = $this->fetchTable('Users');
                    $assignedUser = $usersTable->get($data['assigned_to'], array('contain' => array('Roles')));
                    $assignedRole = null;
                    if ($assignedUser && isset($assignedUser->role) && isset($assignedUser->role->type)) {
                        $assignedRole = strtolower($assignedUser->role->type);
                    }

                    // Handle role-based status transition
                    if ($assignedRole === 'doctor') {
                        $this->caseStatusService->transitionOnAssignment(
                            $case, 
                            'scientist', 
                            'doctor', 
                            $user->id
                        );
                    }
                    
                    // Update case - when scientist assigns to doctor, global status should be 'in_progress'
                    $oldStatus = $case->status;
                    $newGlobalStatus = SiteConstants::CASE_STATUS_IN_PROGRESS;
                    $case->status = $newGlobalStatus;
                    $case->current_user_id = $data['assigned_to'];
                    
                    if ($this->Cases->save($case)) {
                        // Log audit trail - only log global status change if it actually changed
                        $caseAuditsTable = $this->fetchTable('CaseAudits');
                        
                        if ($oldStatus !== $newGlobalStatus) {
                            $caseAuditsTable->logChange(
                                $case->id,
                                $case->current_version_id,
                                'status',
                                $oldStatus,
                                $newGlobalStatus,
                                $user->id
                            );
                        }
                        
                        $caseAuditsTable->logChange(
                            $case->id,
                            $case->current_version_id,
                            'assigned_to',
                            '',
                            $data['assigned_to'],
                            $user->id
                        );

                        // Log activity
                        $this->activityLogger->log(
                            SiteConstants::EVENT_CASE_ASSIGNED,
                            array(
                                'user_id' => $user->id,
                                'request' => $this->request,
                                'event_data' => array('case_id' => $case->id, 'assigned_to' => $data['assigned_to'], 'hospital_id' => $currentHospital->id)
                            )
                        );

                        $this->Flash->success(__('Case has been assigned to doctor successfully.'));
                        return $this->redirect(array('action' => 'view', $case->id));
                    }
                }

                // Show validation errors for debugging
                if ($assignment) {
                    $errors = $assignment->getErrors();
                    if (!empty($errors)) {
                        $this->Flash->error(__('Assignment validation errors: ') . json_encode($errors));
                    }
                }
                
                $this->Flash->error(__('Assignment could not be saved. Please, try again.'));
            }
        }

        // Get doctors ONLY for this hospital (scientists can only assign to doctors)
        $usersTable = $this->fetchTable('Users');
        $doctors = $usersTable->find('list', array(
            'keyField' => 'id',
            'valueField' => function($user) {
                return $user->first_name . ' ' . $user->last_name . ' (' . $user->email . ')';
            }
        ))
        ->innerJoinWith('Roles')
        ->where(array(
            'Users.hospital_id' => $currentHospital->id,
            'Roles.type' => SiteConstants::ROLE_TYPE_DOCTOR // Only doctors
        ))
        ->orderBy(array('Users.last_name' => 'ASC', 'Users.first_name' => 'ASC'))
        ->toArray();

        $this->set(compact('case', 'doctors', 'currentHospital'));
    }

    /**
     * Upload document method
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void
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
                'contain' => array('PatientUsers', 'CaseAssignments')
            ));
            
            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found or you do not have access to this case.'));
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
                throw new RecordNotFoundException(__('Case not found or you do not have access to this case.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found or you do not have access to this case.'));
            return $this->redirect(array('action' => 'index'));
        }

        $data = $this->request->getData();
        
        // Get the uploaded file (it's an UploadedFile object in CakePHP 5)
        $uploadedFile = $data['document_file'] ?? null;
        
        if (empty($uploadedFile) || !is_object($uploadedFile)) {
            $this->Flash->error(__('Please select a file to upload.'));
            return $this->redirect(array('action' => 'view', $id));
        }
        
        // Check for upload errors with specific messages
        $uploadError = $uploadedFile->getError();
        if ($uploadError !== UPLOAD_ERR_OK) {
            $errorMessage = $this->getUploadErrorMessage($uploadError);
            $this->Flash->error($errorMessage);
            Log::error('File upload error: ' . $errorMessage . ' (Error code: ' . $uploadError . ')');
            return $this->redirect(array('action' => 'view', $id));
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
            $case->patient_id,
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
                return $this->redirect(array('action' => 'view', $case->id));
            } else {
                $this->Flash->error(__('Failed to save document record. Please try again.'));
            }
        } else {
            $this->Flash->error(__('Document upload failed: {0}', $uploadResult['error'] ?? 'Unknown error'));
        }

        return $this->redirect(array('action' => 'view', $id));
    }

    /**
     * Get user-friendly upload error message
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
     * Analyze document using AI/OCR for intelligent type detection and procedure linking
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface
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
     * Download document method
     *
     * @param string|null $id Document id.
     * @return \Psr\Http\Message\ResponseInterface|null|void
     */
    public function downloadDocument($id = null)
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

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Document not found or you do not have access to this document.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Use S3DocumentService to stream the file
        $s3DocumentService = new \App\Lib\S3DocumentService();
        
        try {
            // Get file content
                // Get file content (S3 or local)
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
                // Stream the file with proper headers
                $response = $this->response
                    ->withType($document->file_type)
                    ->withStringBody($fileContent)
                    ->withDownload($document->original_filename);
            
            // Log activity
            $this->activityLogger->log(
                SiteConstants::EVENT_DOCUMENT_DOWNLOADED,
                array(
                    'user_id' => $user->id,
                    'request' => $this->request,
                    'event_data' => array(
                        'case_id' => $document->case_id,
                        'document_id' => $document->id,
                        'filename' => $document->original_filename,
                        'hospital_id' => $currentHospital->id
                    )
                )
            );
            
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Document download error: ' . $e->getMessage());
            $this->Flash->error(__('Failed to download document. Please try again.'));
            return $this->redirect(array('action' => 'view', $document->case_id));
        }
    }

    /**
     * View document method - Returns document metadata for preview
     *
     * @param string|null $id Document id.
     * @return \Psr\Http\Message\ResponseInterface|null|void
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
            Log::error('Error proxying document: ' . $e->getMessage());
            throw new \Cake\Http\Exception\InternalErrorException(__('Failed to load document'));
        }
    }

    /**
     * Download case report as PDF
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void
     */
    public function downloadReport($id = null)
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');

        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found.'));
            return $this->redirect(array('action' => 'index'));
        }

        try {
            $case = $this->Cases->get($id, array(
                'contain' => array(
                    'PatientUsers' => array('Patients'),
                    'Users',
                    'Hospitals',
                    'Departments',
                    'Sedations',
                    'CurrentUsers',
                    'CurrentVersions' => array('Users'),
                    'CasesExamsProcedures' => array(
                        'ExamsProcedures' => array('Exams', 'Procedures'),
                        'Documents' // Include documents linked to procedures
                    ),
                    'CaseVersions' => array('Users'),
                    'CaseAssignments' => array('Users', 'AssignedToUsers'),
                    'Documents' // Include general case documents
                )
            ));

            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new \Cake\Datasource\Exception\RecordNotFoundException(__('Case not found.'));
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
                throw new \Cake\Datasource\Exception\RecordNotFoundException(__('Case not found.'));
            }

        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found.'));
            return $this->redirect(array('action' => 'index'));
        }

        // Generate PDF report
        try {
            // Increase PCRE backtrack limit for large HTML content
            ini_set('pcre.backtrack_limit', '5000000');
            
            $mpdf = new \Mpdf\Mpdf(array(
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'margin_header' => 10,
                'margin_footer' => 10
            ));

            // Set document properties
            $mpdf->SetTitle('Case Report #' . $case->id);
            $mpdf->SetAuthor($currentHospital->name);
            $mpdf->SetCreator('MEG System');

            // Prepare data for view using AI services if enabled
            $viewVars = $this->_prepareReportWithAI($case, $currentHospital, $user);

            // Create a new View instance to render the template WITHOUT layout
            $viewBuilder = $this->viewBuilder();
            $viewBuilder->setTemplate('pdf/report');
            $viewBuilder->disableAutoLayout(); // Disable layout for PDF
            
            // Build the view with the current request
            $view = $viewBuilder->build($this->request);
            
            // Set view variables
            $view->set($viewVars);
            
            // Render to HTML (without layout)
            $html = $view->render();

            // Write HTML to PDF (handle large content by splitting into chunks if needed)
            $htmlSize = strlen($html);
            $maxChunkSize = 500000; // 500KB chunks
            
            if ($htmlSize > $maxChunkSize) {
                Log::debug("Large HTML content ({$htmlSize} bytes), splitting into chunks");
                
                // Split HTML by sections to avoid breaking tags
                // First write the header/styles
                preg_match('/(.*?<body[^>]*>)/is', $html, $headerMatch);
                if ($headerMatch) {
                    $mpdf->WriteHTML($headerMatch[1]);
                    $html = substr($html, strlen($headerMatch[1]));
                }
                
                // Split remaining content by section divs
                $sections = preg_split('/(<div class="section">)/i', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
                
                $buffer = '';
                foreach ($sections as $section) {
                    $buffer .= $section;
                    
                    // Write chunk when it reaches a reasonable size
                    if (strlen($buffer) > $maxChunkSize) {
                        $mpdf->WriteHTML($buffer);
                        $buffer = '';
                    }
                }
                
                // Write any remaining content
                if (!empty($buffer)) {
                    $mpdf->WriteHTML($buffer);
                }
            } else {
                // Small enough to write in one go
                $mpdf->WriteHTML($html);
            }

            // Output PDF for download
            $filename = 'Case_Report_' . $case->id . '_' . date('Ymd') . '.pdf';
            
            return $this->response
                ->withType('application/pdf')
                ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->withStringBody($mpdf->Output($filename, 'S'));

        } catch (\Exception $e) {
            $this->Flash->error(__('Error generating PDF report: {0}', $e->getMessage()));
            return $this->redirect(array('action' => 'view', $id));
        }
    }

    /**
     * Prepare report data using AI services
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @param object $hospital The hospital object
     * @param \App\Model\Entity\User $user The current user
     * @return array View variables
     */
    private function _prepareReportWithAI($case, $hospital, $user)
    {
        // Check if AI report generation is enabled
        $aiEnabled = env('OPENAI_ENABLED');
        $reportGenEnabled = env('OPENAI_REPORT_ENABLED');

        if (!$aiEnabled || !$reportGenEnabled) {
            // Fallback to traditional method
            return $this->_prepareReportData($case, $hospital, $user);
        }

        try {
            // Step 1: Extract content from attached documents
            $documentContentService = new DocumentContentService();
            $documentContents = array();

            Log::debug('AI Report: Starting document extraction for case ' . $case->id);
            
            if (!empty($case->cases_exams_procedures)) {
                Log::debug('AI Report: Found ' . count($case->cases_exams_procedures) . ' procedures');
                
                foreach ($case->cases_exams_procedures as $cep) {
                    if (!empty($cep->documents)) {
                        Log::debug('AI Report: Procedure ' . $cep->id . ' has ' . count($cep->documents) . ' documents');
                        
                        foreach ($cep->documents as $document) {
                            try {
                                Log::debug('AI Report: Extracting document ' . $document->id . ' (' . $document->original_filename . ')');
                                $content = $documentContentService->extractContent($document);
                                
                                // Debug: Log extraction result
                                Log::debug('AI Report: Document ' . $document->id . ' extraction result - Success: ' . 
                                          ($content['success'] ? 'YES' : 'NO') . 
                                          ', Text length: ' . (isset($content['text']) ? strlen($content['text']) : 0) .
                                          ', Has analysis: ' . (isset($content['analysis']) ? 'YES' : 'NO'));
                                
                                if (!$content['success'] && isset($content['error'])) {
                                    Log::warning('AI Report: Document ' . $document->id . ' extraction error: ' . $content['error']);
                                }
                                
                                $documentContents[$document->id] = array(
                                    'content' => $content,
                                    'procedure_id' => $cep->id,
                                    'document' => $document
                                );
                                Log::debug('AI Report: Successfully extracted document ' . $document->id);
                            } catch (\Exception $e) {
                                Log::warning('AI Report: Failed to extract document ' . $document->id . ': ' . $e->getMessage());
                            }
                        }
                    } else {
                        Log::debug('AI Report: Procedure ' . $cep->id . ' has no documents');
                    }
                }
            } else {
                Log::debug('AI Report: No procedures found for case');
            }
            
            Log::debug('AI Report: Total documents extracted: ' . count($documentContents));

            // Step 2: Prepare metadata for AI (de-identified)
            $metadata = $this->_prepareAiMetadata($case, $documentContents);

            // Step 3: Generate report structure using AI
            // Use standardized MEG report format
            $reportAssemblyService = new ReportAssemblyService();
            $reportData = $reportAssemblyService->assembleMEGReport($case, $documentContents);

            // Add hospital context
            $reportData['hospital'] = $hospital;
            $reportData['current_user'] = $user;

            return $reportData;

        } catch (\Exception $e) {
            Log::error('AI report generation failed, falling back to traditional: ' . $e->getMessage());
            return $this->_prepareReportData($case, $hospital, $user);
        }
    }

    /**
     * Prepare metadata for AI report generation (de-identified, HIPAA compliant)
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @param array $documentContents Extracted document contents
     * @return array De-identified metadata
     */
    private function _prepareAiMetadata($case, array $documentContents)
    {
        // Get patient age category (not exact age)
        $ageCategory = 'adult';
        $patient = $case->patient_user;
        if ($patient && !empty($patient->patients)) {
            $patientDetails = $patient->patients[0];
            if ($patientDetails->dob) {
                $dob = new \DateTime($patientDetails->dob->format('Y-m-d'));
                $now = new \DateTime();
                $age = $dob->diff($now)->y;
                
                if ($age < 18) {
                    $ageCategory = 'pediatric';
                } elseif ($age >= 65) {
                    $ageCategory = 'geriatric';
                }
            }
        }

        // Get procedure types (no PHI)
        $procedureTypes = array();
        if (!empty($case->cases_exams_procedures)) {
            foreach ($case->cases_exams_procedures as $cep) {
                if (isset($cep->exams_procedure->procedure)) {
                    $procedureTypes[] = $cep->exams_procedure->procedure->name;
                }
            }
        }

        // Get symptoms/indications (generic categories only, not patient-specific text)
        $symptomCategories = $this->_categorizeSymptoms($case->symptoms ?? '');

        // Determine report type
        $aiReportService = new AiReportGenerationService();
        $reportType = $aiReportService->determineReportType($procedureTypes);

        return array(
            'procedures' => $procedureTypes, // Use 'procedures' for consistency
            'procedure_types' => $procedureTypes, // Keep for backwards compatibility
            'report_type' => $reportType,
            'age_category' => $ageCategory,
            'gender' => $patient->gender ?? 'unknown',
            'symptom_categories' => $symptomCategories,
            'sedation_type' => $case->sedation->name ?? 'none',
            'department' => $case->department->name ?? 'general',
            'document_count' => count($documentContents),
            'has_prior_studies' => false, // Could be enhanced to check for prior cases
        );
    }

    /**
     * Categorize symptoms into generic medical categories
     *
     * @param string $symptomsText Patient symptoms text
     * @return array Generic symptom categories
     */
    private function _categorizeSymptoms($symptomsText)
    {
        $categories = array();
        $text = strtolower($symptomsText);

        // Map keywords to categories (no patient-specific data sent to AI)
        if (preg_match('/seizure|epilep|convuls/i', $text)) {
            $categories[] = 'seizure_disorder';
        }
        if (preg_match('/headache|migraine|pain/i', $text)) {
            $categories[] = 'headache';
        }
        if (preg_match('/tumor|mass|lesion/i', $text)) {
            $categories[] = 'structural_abnormality';
        }
        if (preg_match('/memory|cognitive|dementia/i', $text)) {
            $categories[] = 'cognitive_concern';
        }

        return empty($categories) ? array('general_evaluation') : $categories;
    }

    /**
     * Prepare data for case report PDF
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @param object $hospital The hospital object
     * @param \App\Model\Entity\User $user The current user
     * @return array View variables
     */
    private function _prepareReportData($case, $hospital, $user)
    {
        // Get patient details
        $patient = $case->patient_user;
        $patientDetails = null;
        if ($patient && !empty($patient->patients)) {
            $patientDetails = $patient->patients[0];
        }

        // Patient basic info
        $patientFirstName = $patient ? $patient->first_name : 'N/A';
        $patientLastName = $patient ? $patient->last_name : 'N/A';
        $patientDob = $patientDetails && $patientDetails->dob 
            ? $patientDetails->dob->format('m/d/Y') 
            : 'N/A';
        $patientMrn = $patientDetails && $patientDetails->mrn 
            ? $patientDetails->mrn 
            : 'N/A';
        $patientFin = $patientDetails && $patientDetails->fin 
            ? $patientDetails->fin 
            : 'N/A';

        // Calculate age from DOB
        $age = 'N/A';
        if ($patientDetails && $patientDetails->dob) {
            $dob = new \DateTime($patientDetails->dob->format('Y-m-d'));
            $now = new \DateTime();
            $age = $dob->diff($now)->y;
        }

        // Gender
        $gender = $patient && $patient->gender ? $patient->gender : 'N/A';

        // Medications
        $medications = $patientDetails && $patientDetails->medications 
            ? $patientDetails->medications 
            : '';

        // Study details
        $studyDate = $case->date ? $case->date->format('m/d/Y') : 'N/A';
        $referringPhysician = $case->user 
            ? ($case->user->first_name . ' ' . $case->user->last_name) 
            : 'N/A';
        $megId = 'MEG-' . str_pad((string)$case->id, 6, '0', STR_PAD_LEFT);

        // Sedation text
        $sedationText = $case->sedation && $case->sedation->name 
            ? 'with ' . strtolower($case->sedation->name) 
            : 'without sedation';

        // Get procedures list
        $proceduresList = array();
        if (!empty($case->cases_exams_procedures)) {
            foreach ($case->cases_exams_procedures as $cep) {
                if (isset($cep->exams_procedure->procedure)) {
                    $proceduresList[] = $cep->exams_procedure->procedure->name;
                }
            }
        }

        // Generate technical descriptions based on procedures
        $technicalDescriptions = $this->_generateTechnicalDescriptions($case);

        // Generate MSI conclusions based on procedures
        $msiConclusions = $this->_generateMsiConclusions($case);

        // Additional notes
        $additionalNotes = $case->notes ?? '';

        return array(
            'case' => $case,
            'hospital' => $hospital,
            'patientFirstName' => $patientFirstName,
            'patientLastName' => $patientLastName,
            'patientDob' => $patientDob,
            'patientMrn' => $patientMrn,
            'patientFin' => $patientFin,
            'age' => $age,
            'gender' => $gender,
            'medications' => $medications,
            'studyDate' => $studyDate,
            'referringPhysician' => $referringPhysician,
            'megId' => $megId,
            'sedationText' => $sedationText,
            'proceduresList' => $proceduresList,
            'technicalDescriptions' => $technicalDescriptions,
            'msiConclusions' => $msiConclusions,
            'additionalNotes' => $additionalNotes
        );
    }

    /**
     * Generate technical descriptions based on procedures
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @return array Technical description HTML blocks
     */
    private function _generateTechnicalDescriptions($case)
    {
        $descriptions = array();
        
        if (empty($case->cases_exams_procedures)) {
            return $descriptions;
        }

        $procedureTypes = array();
        foreach ($case->cases_exams_procedures as $cep) {
            if (isset($cep->exams_procedure->procedure)) {
                $procedureName = strtolower($cep->exams_procedure->procedure->name);
                $procedureTypes[] = $procedureName;
            }
        }

        // Resting State MEG
        if ($this->_hasProcedureType($procedureTypes, array('resting', 'rest', 'baseline'))) {
            $descriptions[] = '<div class="section-title">Resting State MEG:</div>
                <p>Spontaneous brain activity was recorded during eyes-closed resting conditions. 
                The patient was instructed to remain still and relaxed while avoiding sleep. 
                The recording duration was approximately 5-10 minutes. Data will be analyzed for 
                identification of interictal epileptiform activity, characterization of background 
                rhythms, and assessment of functional connectivity patterns.</p>';
        }

        // Sensory Mapping
        if ($this->_hasProcedureType($procedureTypes, array('sensory', 'somatosensory', 'tactile'))) {
            $descriptions[] = '<div class="section-title">Somatosensory Mapping:</div>
                <p>Somatosensory evoked fields were recorded in response to pneumatic tactile 
                stimulation of the digits. Stimuli were delivered to multiple locations to map 
                the primary somatosensory cortex. The source distributions were analyzed utilizing 
                an equivalent current dipole model to localize the peak responses in relation to 
                the individual patient\'s cortical anatomy as defined by MRI.</p>';
        }

        // Motor Mapping
        if ($this->_hasProcedureType($procedureTypes, array('motor', 'movement'))) {
            $descriptions[] = '<div class="section-title">Motor Mapping:</div>
                <p>Motor evoked fields were recorded during voluntary finger movements. 
                The patient performed self-paced movements of individual digits while MEG data 
                was continuously recorded. Movement-related cortical fields were analyzed to 
                identify the primary motor cortex using equivalent current dipole modeling.</p>';
        }

        // Auditory Mapping
        if ($this->_hasProcedureType($procedureTypes, array('auditory', 'hearing', 'sound'))) {
            $descriptions[] = '<div class="section-title">Auditory Mapping:</div>
                <p>Auditory evoked fields were elicited using tone bursts delivered binaurally 
                through non-magnetic insert earphones. Multiple frequencies were tested to map 
                tonotopic organization of the auditory cortex. Source localization was performed 
                using equivalent current dipole analysis.</p>';
        }

        // Visual Mapping
        if ($this->_hasProcedureType($procedureTypes, array('visual', 'vision', 'sight'))) {
            $descriptions[] = '<div class="section-title">Visual Mapping:</div>
                <p>Visual evoked fields were recorded in response to pattern-reversal checkerboard 
                stimuli presented to different regions of the visual field. Source analysis was 
                performed to localize primary visual cortex and assess retinotopic organization.</p>';
        }

        // Language Mapping
        if ($this->_hasProcedureType($procedureTypes, array('language', 'speech', 'verbal', 'naming'))) {
            $descriptions[] = '<div class="section-title">Language Mapping:</div>
                <p>Language-related cortical activation was assessed using receptive and/or 
                expressive language tasks. Tasks may have included picture naming, verb generation, 
                or auditory word comprehension. Source localization was performed to identify 
                language-dominant hemisphere and map critical language areas in relation to 
                structural lesions.</p>';
        }

        // Memory Mapping
        if ($this->_hasProcedureType($procedureTypes, array('memory', 'recall', 'recognition'))) {
            $descriptions[] = '<div class="section-title">Memory Assessment:</div>
                <p>Memory-related brain activity was evaluated using encoding and retrieval 
                paradigms. The patient was presented with stimuli to encode and later tested 
                for recognition or recall. Analysis focused on identifying medial temporal lobe 
                and prefrontal cortical activation patterns.</p>';
        }

        return $descriptions;
    }

    /**
     * Generate MSI conclusions based on procedures
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @return array Conclusion statements
     */
    private function _generateMsiConclusions($case)
    {
        $conclusions = array();
        
        if (empty($case->cases_exams_procedures)) {
            return $conclusions;
        }

        $procedureTypes = array();
        foreach ($case->cases_exams_procedures as $cep) {
            if (isset($cep->exams_procedure->procedure)) {
                $procedureName = strtolower($cep->exams_procedure->procedure->name);
                $procedureTypes[] = $procedureName;
            }
        }

        // Generic conclusion based on procedure types
        $hasMapping = $this->_hasProcedureType($procedureTypes, 
            array('sensory', 'motor', 'auditory', 'visual', 'language', 'memory'));
        $hasResting = $this->_hasProcedureType($procedureTypes, 
            array('resting', 'rest', 'baseline'));

        if ($hasMapping && $hasResting) {
            $conclusions[] = 'Magnetoencephalography successfully localized eloquent cortical areas and characterized spontaneous brain activity. Detailed analysis of functional mapping data in relation to structural imaging and clinical information will be provided in the comprehensive MSI report.';
        } elseif ($hasMapping) {
            $conclusions[] = 'Magnetoencephalography successfully localized eloquent cortical areas. Detailed analysis of functional mapping data in relation to structural imaging and clinical information will be provided in the comprehensive MSI report.';
        } elseif ($hasResting) {
            $conclusions[] = 'Magnetoencephalography successfully characterized spontaneous brain activity patterns. Detailed analysis in relation to clinical presentation will be provided in the comprehensive report.';
        } else {
            $conclusions[] = 'MEG data acquired successfully. Comprehensive analysis and clinical correlation will be provided in the final report.';
        }

        return $conclusions;
    }

    /**
     * Helper method to check if any procedure matches given keywords
     *
     * @param array $procedureTypes List of procedure type names
     * @param array $keywords Keywords to match
     * @return bool True if any keyword is found
     */
    private function _hasProcedureType(array $procedureTypes, array $keywords)
    {
        foreach ($procedureTypes as $procedure) {
            foreach ($keywords as $keyword) {
                if (stripos($procedure, $keyword) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Get authenticated user helper method
     *
     * @return object User entity
     */
    private function getAuthUser()
    {
        $identity = $this->request->getAttribute('identity');
        return $identity->getOriginalData();
    }
}
