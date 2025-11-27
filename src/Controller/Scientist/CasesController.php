<?php
declare(strict_types=1);

namespace App\Controller\Scientist;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use App\Lib\UserActivityLogger;
use App\Constants\SiteConstants;
use Cake\Log\Log;
use App\Service\DocumentContentService;
use App\Service\CaseStatusService;
use App\Service\PatientMaskingService;
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
     * Patient Masking Service instance
     */
    private $patientMaskingService;

    /**
     * Initialize method
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->activityLogger = new UserActivityLogger();
        $this->caseStatusService = new CaseStatusService();
        $this->patientMaskingService = new PatientMaskingService();
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
     * Index method - Shows cases based on scientist's current status
     *    /**
     * Check if user has required role for scientist access
     *
     * @return bool
     */
    private function hasRequiredRole(): bool
    {
        $user = $this->Authentication->getIdentity();
        
        if (!$user) {
            return false;
        }
        
        // Get user with role relationship if role_type is not set
        if (!isset($user->role_type) && isset($user->id)) {
            $usersTable = $this->fetchTable('Users');
            $userWithRole = $usersTable->find()
                ->contain(['Roles'])
                ->where(['Users.id' => $user->id])
                ->first();
            
            if ($userWithRole && $userWithRole->role) {
                $user->role_type = $userWithRole->role->type;
            }
        }
        
        // Allow scientists and admins to access scientist routes
        return isset($user->role_type) && in_array($user->role_type, [
            SiteConstants::ROLE_TYPE_SCIENTIST,
            SiteConstants::ROLE_TYPE_ADMIN,
            SiteConstants::ROLE_TYPE_SUPER
        ]);
    }
    
    /**
     * Get redirect URL based on user role
     *
     * @return array|string
     */
    private function getRedirectUrl()
    {
        $user = $this->Authentication->getIdentity();
        
        if (!$user) {
            return '/scientist/login';
        }
        
        return match($user->role_type) {
            SiteConstants::ROLE_TYPE_DOCTOR => '/doctor/dashboard',
            SiteConstants::ROLE_TYPE_TECHNICIAN => '/technician/dashboard',
            SiteConstants::ROLE_TYPE_ADMIN => '/admin/dashboard',
            SiteConstants::ROLE_TYPE_SUPER => '/system/dashboard',
            default => '/scientist/login'
        };
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
                'PatientUsers' => ['Patient'], 
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
        $userWithRoles = $usersTable->get($user->id, ['contain' => ['Roles']]);

        try {
            $case = $this->Cases->get($id, array(
                'contain' => array(
                    'Users' => ['Roles'],
                    'PatientUsers' => ['Roles', 'Patient'],
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

        // Auto-progress: Change scientist_status from 'assigned' to 'in_progress' on first view
        if ($case->scientist_status === SiteConstants::CASE_STATUS_ASSIGNED) {
            $case->scientist_status = SiteConstants::CASE_STATUS_IN_PROGRESS;
            
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
                            'old_scientist_status' => SiteConstants::CASE_STATUS_ASSIGNED,
                            'new_scientist_status' => SiteConstants::CASE_STATUS_IN_PROGRESS,
                            'hospital_id' => $currentHospital->id,
                            'trigger' => 'scientist_first_view'
                        )
                    )
                );
            }
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
                    'PatientUsers' => ['Roles', 'Patient'],
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

            // Check if case can be assigned - allow as long as global status is not completed
            if ($case->status === SiteConstants::CASE_STATUS_COMPLETED) {
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
                        // Reload case to get updated statuses from transitionOnAssignment
                        $case = $this->Cases->get($case->id);
                    }
                    
                    // Update case current_user_id
                    $case->current_user_id = $data['assigned_to'];
                    
                    if ($this->Cases->save($case)) {
                        // Log audit trail - status change is already logged by transitionOnAssignment
                        $caseAuditsTable = $this->fetchTable('CaseAudits');
                        
                        $caseAuditsTable->logChange(
                            $case->id,
                            $case->current_version_id,
                            'assigned_to',
                            '',
                            (string)$data['assigned_to'],
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
     * Create Report method - Create a pending report for a case and redirect to add report page
     *
     * @param string|null $id Case id.
     * @return \Cake\Http\Response|null Redirects to add report page
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When case not found.
     */
    public function createReport($id = null)
    {
        $user = $this->getAuthUser();
        
        // Get the case with all necessary associations for dynamic content
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
                ],
                'CaseAssignments'
            ]
        ]);
        
        // Check if user has access to this case (scientist assignment check)
        $hasAssignment = false;
        if (!empty($case->case_assignments)) {
            foreach ($case->case_assignments as $assignment) {
                if ($assignment->assigned_to === $user->id) {
                    $hasAssignment = true;
                    break;
                }
            }
        }
        
        if (!$hasAssignment) {
            $this->Flash->error(__('You can only create reports for cases assigned to you.'));
            return $this->redirect(['action' => 'index']);
        }
        
        // Check if case has a report already
        $reportsTable = $this->fetchTable('Reports');
        $existingReport = $reportsTable->find()
            ->where(['case_id' => $id])
            ->first();
            
        if ($existingReport) {
            $this->Flash->info(__('This case already has a report. Redirecting to create/edit your scientist report.'));
            return $this->redirect(['controller' => 'Reports', 'action' => 'add', '?' => ['case_id' => $id]]);
        }
        
        // Create new pending report
        $report = $reportsTable->newEmptyEntity();
        $reportData = [
            'case_id' => $id,
            'hospital_id' => $case->hospital_id,
            'status' => 'pending',
            'user_id' => $user->id,
            'report_data' => json_encode([
                'content' => ''  // Empty content - will be dynamically generated in Reports controller
            ])
        ];
        
        $report = $reportsTable->patchEntity($report, $reportData);
        
        if ($reportsTable->save($report)) {
            $this->Flash->success(__('Report created successfully. Please fill in the report details.'));
            
            // Log activity
            $this->activityLogger->log(
                'report_created',
                [
                    'user_id' => $user->id,
                    'request' => $this->request,
                    'event_data' => [
                        'case_id' => $id,
                        'report_id' => $report->id
                    ]
                ]
            );
            
            return $this->redirect(['controller' => 'Reports', 'action' => 'add', '?' => ['case_id' => $id]]);
        } else {
            $this->Flash->error(__('Unable to create report. Please try again.'));
            return $this->redirect(['action' => 'view', $id]);
        }
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
