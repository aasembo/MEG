<?php
declare(strict_types=1);

namespace App\Controller\Technician;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use App\Lib\UserActivityLogger;
use App\Constants\SiteConstants;
use Cake\Log\Log;
use App\Service\DocumentContentService;
use App\Service\CaseStatusService;
use App\Service\PatientMaskingService;
use Cake\Core\Configure;

class CasesController extends AppController {
    private $activityLogger;
    private $caseStatusService;
    private $patientMaskingService;
    
    public function initialize(): void {
        parent::initialize();
        $this->activityLogger = new UserActivityLogger();
        $this->caseStatusService = new CaseStatusService();
        $this->patientMaskingService = new PatientMaskingService();
        
        // Set technician layout for all actions
        $this->viewBuilder()->setLayout('technician');
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
    
    public function index() {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index']);
        }

        // Get filter parameters
        $status = $this->request->getQuery('status', 'all');
        $priority = $this->request->getQuery('priority', 'all');
        $search = $this->request->getQuery('search', '');

        // Build query
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
            ->where(['Cases.hospital_id' => $currentHospital->id]);

        // Apply filters (use main case status)
        if ($status && $status !== 'all') {
            $query->where(['Cases.status' => $status]);
        }
        
        if ($priority && $priority !== 'all') {
            $query->where(['Cases.priority' => $priority]);
        }

        if ($search) {
            $searchConditions = [
                'OR' => [
                    'Users.first_name LIKE' => '%' . $search . '%',
                    'Users.last_name LIKE' => '%' . $search . '%',
                ]
            ];
            
            // Only search by ID if the search term is numeric
            if (is_numeric($search)) {
                $searchConditions['OR']['Cases.id'] = (int)$search;
            }
            
            $query->where($searchConditions);
        }

        // Technicians can only see their own cases
        $query->where(['Cases.user_id' => $user->id]);

        // Configure pagination with sorting
        $this->paginate = [
            'limit' => 20,
            'order' => ['Cases.created' => 'DESC'],
            'sortableFields' => [
                'Cases.id',
                'Cases.patient_id',
                'Cases.status',
                'Cases.priority',
                'Cases.date',
                'Cases.current_user_id',
                'Cases.created'
            ]
        ];

        $cases = $this->paginate($query);

        // Apply patient masking to all cases
        // (Removed - now handled by PatientMask helper in templates)

        // Get filter options (main case statuses: in_progress, completed, cancelled)
        $statusOptions = [
            'all' => 'All Status',
            SiteConstants::CASE_STATUS_IN_PROGRESS => 'In Progress',
            SiteConstants::CASE_STATUS_COMPLETED => 'Completed',
            SiteConstants::CASE_STATUS_CANCELLED => 'Cancelled'
        ];

        $priorityOptions = [
            'all' => 'All Priorities',
            SiteConstants::PRIORITY_LOW => 'Low',
            SiteConstants::PRIORITY_MEDIUM => 'Medium',
            SiteConstants::PRIORITY_HIGH => 'High',
            SiteConstants::PRIORITY_URGENT => 'Urgent'
        ];

        // Log activity
        $this->activityLogger->log(
            SiteConstants::EVENT_CASE_LIST_VIEWED,
            [
                'user_id' => $user->id,
                'request' => $this->request,
                'event_data' => ['hospital_id' => $currentHospital->id, 'filters' => compact('status', 'priority', 'search')]
            ]
        );

        // Additional template variables
        $hospitalName = $currentHospital->name ?? 'Unknown Hospital';

        $this->set(compact(
            'cases', 
            'statusOptions', 
            'priorityOptions', 
            'status', 
            'priority', 
            'search',
            'currentHospital',
            'hospitalName',
            'user'
        ));
    }

    public function view($id = null) {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['action' => 'index']);
        }

        // Load full user entity with roles for "You" pattern in templates
        $usersTable = $this->fetchTable('Users');
        $userWithRoles = $usersTable->get($user->id, ['contain' => ['Roles']]);

        try {
            $case = $this->Cases->get($id, [
                'contain' => [
                    'Users',
                    'Hospitals', 
                    'PatientUsers',
                    'CurrentUsers',
                    'CurrentVersions',
                    'Departments',
                    'Sedations',
                    'CaseVersions' => ['Users'],
                    'CaseAssignments' => [
                        'Users' => ['Roles'],
                        'AssignedToUsers' => ['Roles']
                    ],
                    'CaseAudits' => [
                        'ChangedByUsers' => ['Roles']
                    ],
                    'Documents' => [
                        'Users',
                        'CasesExamsProcedures' => [
                            'ExamsProcedures' => ['Exams', 'Procedures']
                        ]
                    ],
                    'CasesExamsProcedures' => [
                        'ExamsProcedures' => [
                            'Exams' => ['Modalities', 'Departments'],
                            'Procedures'
                        ],
                        'Documents' => ['Users']
                    ]
                ]
            ]);

            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found.'));
            return $this->redirect(['action' => 'index']);
        }

        // Handle role-based status transition on first view
        $roleColumn = 'technician_status';
        $currentRoleStatus = $case->{$roleColumn} ?? 'draft';
        
        if ($currentRoleStatus === 'draft') {
            $this->caseStatusService->transitionOnView($case, 'technician', $user->id);
        }

        // Log activity
        $this->activityLogger->log(
            SiteConstants::EVENT_CASE_VIEWED,
            [
                'user_id' => $user->id,
                'request' => $this->request,
                'event_data' => ['case_id' => $case->id, 'hospital_id' => $currentHospital->id]
            ]
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

        // Apply patient masking
        // (Removed - now handled by PatientMask helper in templates)

        $this->set(compact('case', 'currentHospital', 'isS3Enabled', 'user', 'existingReports'));
        // Pass user with roles for role badge helper
        $this->set('user', $userWithRoles);
    }

    public function add() {
        // Clear any existing wizard data when starting a new case
        $this->request->getSession()->delete('CaseWizard');
        
        return $this->redirect(['action' => 'addStep1']);
    }

    public function addStep1() {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index']);
        }

        // Get patients for this hospital (build masked list for dropdowns)
        $patientsTable = $this->fetchTable('Patients');
        $patientsQuery = $patientsTable->find()
            ->contain(['Users' => ['Roles']])
            ->where([
                'Patients.hospital_id' => $currentHospital->id,
                'Users.status' => 'active',
                'Roles.type' => 'patient'
            ])
            ->order(['Users.last_name' => 'ASC', 'Users.first_name' => 'ASC'])
            ->all();

        $patients = [];
        foreach ($patientsQuery as $patientEntity) {
            $userEntity = $patientEntity->user ?? null;
            // Build masked display: Name (Gender initial/Age)
            $masked = $this->patientMaskingService->maskPatientForUser($patientEntity, $user);
            $first = $masked->masked_first_name ?? ($userEntity->first_name ?? '');
            $last = $masked->masked_last_name ?? ($userEntity->last_name ?? '');
            $gender = $patientEntity->gender ?? '';
            $genderInitial = $gender ? strtoupper(substr($gender, 0, 1)) : '';
            
            // Calculate age from DOB
            $age = '';
            if (!empty($patientEntity->dob)) {
                $dobString = is_object($patientEntity->dob) ? $patientEntity->dob->format('Y-m-d') : $patientEntity->dob;
                $dob = new \DateTime($dobString);
                $now = new \DateTime();
                $age = $dob->diff($now)->y;
            }
            
            $label = trim($first . ' ' . $last);
            $extraInfo = [];
            if (!empty($genderInitial)) {
                $extraInfo[] = $genderInitial;
            }
            if (!empty($age)) {
                $extraInfo[] = $age;
            }
            if (!empty($extraInfo)) {
                $label .= ' (' . implode('/', $extraInfo) . ')';
            }
            $patients[$userEntity->id ?? $patientEntity->user_id] = $label;
        }

        // Get session data if returning from later steps
        $caseData = $this->request->getSession()->read('CaseWizard.step1') ?? [];
        
        // Check if patient_id is provided in query string
        $patientId = $this->request->getQuery('patient_id');
        if ($patientId && empty($caseData['patient_id'])) {
            $caseData['patient_id'] = $patientId;
        }

        // Set default case date to today
        if (empty($caseData['date'])) {
            $caseData['date'] = date('Y-m-d');
        }

        $this->set(compact('patients', 'caseData', 'currentHospital'));
    }

    public function addStep2() {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index']);
        }

        // Check if step 1 data exists
        $step1Data = $this->request->getSession()->read('CaseWizard.step1');
        if (empty($step1Data)) {
            $this->Flash->error(__('Please complete Step 1 first.'));
            return $this->redirect(['action' => 'addStep1']);
        }

        // Load departments for the hospital
        $departmentsTable = $this->fetchTable('Departments');
        $departments = $departmentsTable->find('list')
            ->where(['hospital_id' => $currentHospital->id])
            ->orderBy(['name' => 'ASC'])
            ->toArray();

        // Load sedations for the hospital
        $sedationsTable = $this->fetchTable('Sedations');
        $sedations = $sedationsTable->find('list', [
            'keyField' => 'id',
            'valueField' => function($sedation) {
                return $sedation->level . ' (' . $sedation->type . ')';
            }
        ])
        ->where(['hospital_id' => $currentHospital->id])
        ->orderBy(['level' => 'ASC'])
        ->toArray();

        // Load exams procedures with their related data
        $examsProceduresTable = $this->fetchTable('ExamsProcedures');
        $examsProcedures = $examsProceduresTable->find()
            ->contain([
                'Exams' => ['Modalities', 'Departments'],
                'Procedures'
            ])
            ->where([
                'Exams.hospital_id' => $currentHospital->id
            ])
            ->toArray();

        // Get step 2 data from session if returning
        $step2Data = $this->request->getSession()->read('CaseWizard.step2') ?? [];
        
        // Get AI recommendations from session
        $aiRecommendations = $this->request->getSession()->read('CaseWizard.aiRecommendations') ?? [];

        $this->set(compact('step1Data', 'step2Data', 'departments', 'sedations', 'examsProcedures', 'aiRecommendations', 'currentHospital'));
    }

    public function addStep3() {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index']);
        }

        // Check if previous steps data exists
        $step1Data = $this->request->getSession()->read('CaseWizard.step1');
        $step2Data = $this->request->getSession()->read('CaseWizard.step2');
        
        if (empty($step1Data) || empty($step2Data)) {
            $this->Flash->error(__('Please complete all previous steps first.'));
            return $this->redirect(['action' => 'addStep1']);
        }

        // Get step 3 data from session if returning
        $step3Data = $this->request->getSession()->read('CaseWizard.step3') ?? [];
        
        // Get AI recommendations
        $aiRecommendations = $this->request->getSession()->read('CaseWizard.aiRecommendations') ?? [];

        // Load patient info for display
        $patientsTable = $this->fetchTable('Users');
        $patient = $patientsTable->get($step1Data['patient_id']);

        // Load department info
        $department = null;
        if (!empty($step2Data['department_id'])) {
            $departmentsTable = $this->fetchTable('Departments');
            $department = $departmentsTable->get($step2Data['department_id']);
        }

        // Load sedation info
        $sedation = null;
        if (!empty($step2Data['sedation_id'])) {
            $sedationsTable = $this->fetchTable('Sedations');
            $sedation = $sedationsTable->get($step2Data['sedation_id']);
        }

        // Load selected exams/procedures
        $selectedExamsProcedures = [];
        if (!empty($step2Data['exam_procedures'])) {
            $examsProceduresTable = $this->fetchTable('ExamsProcedures');
            $selectedExamsProcedures = $examsProceduresTable->find()
                ->contain(['Exams', 'Procedures'])
                ->where(['ExamsProcedures.id IN' => $step2Data['exam_procedures']])
                ->toArray();
        }

        $this->set(compact('step1Data', 'step2Data', 'step3Data', 'patient', 'department', 'sedation', 'selectedExamsProcedures', 'aiRecommendations', 'currentHospital'));
    }

    public function edit($id = null) {
        // Clear any existing edit wizard data and redirect to step 1
        $this->request->getSession()->delete('CaseEditWizard');
        return $this->redirect(['action' => 'editStep1', $id]);
    }

    public function editStep1($id = null) {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index']);
        }

        try {
            $case = $this->Cases->get($id, [
                'contain' => ['PatientUsers']
            ]);
            
            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found.'));
            }

            // Check if case can be edited (technician can edit until global status is completed/cancelled)
            if (in_array($case->status, [SiteConstants::CASE_STATUS_COMPLETED, SiteConstants::CASE_STATUS_CANCELLED])) {
                $this->Flash->error(__('This case cannot be edited in its current status.'));
                return $this->redirect(['action' => 'view', $id]);
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found.'));
            return $this->redirect(['action' => 'index']);
        }

        // Get patients for this hospital (build masked list for dropdowns)
        $patientsTable = $this->fetchTable('Patients');
        $patientsQuery = $patientsTable->find()
            ->contain(['Users' => ['Roles']])
            ->where([
                'Patients.hospital_id' => $currentHospital->id,
                'Users.status' => 'active',
                'Roles.type' => 'patient'
            ])
            ->order(['Users.last_name' => 'ASC', 'Users.first_name' => 'ASC'])
            ->all();

        $patients = [];
        foreach ($patientsQuery as $patientEntity) {
            $userEntity = $patientEntity->user ?? null;
            // Build masked display: Name (Gender initial/Age)
            $masked = $this->patientMaskingService->maskPatientForUser($patientEntity, $user);
            $first = $masked->masked_first_name ?? ($userEntity->first_name ?? '');
            $last = $masked->masked_last_name ?? ($userEntity->last_name ?? '');
            $gender = $patientEntity->gender ?? '';
            $genderInitial = $gender ? strtoupper(substr($gender, 0, 1)) : '';
            
            // Calculate age from DOB
            $age = '';
            if (!empty($patientEntity->dob)) {
                $dobString = is_object($patientEntity->dob) ? $patientEntity->dob->format('Y-m-d') : $patientEntity->dob;
                $dob = new \DateTime($dobString);
                $now = new \DateTime();
                $age = $dob->diff($now)->y;
            }
            
            $label = trim($first . ' ' . $last);
            $extraInfo = [];
            if (!empty($genderInitial)) {
                $extraInfo[] = $genderInitial;
            }
            if (!empty($age)) {
                $extraInfo[] = $age;
            }
            if (!empty($extraInfo)) {
                $label .= ' (' . implode('/', $extraInfo) . ')';
            }
            $patients[$userEntity->id ?? $patientEntity->user_id] = $label;
        }

        // Get session data if returning from later steps, otherwise use case data
        $caseData = $this->request->getSession()->read('CaseEditWizard.step1') ?? [
            'patient_id' => $case->patient_id,
            'date' => $case->date ? $case->date->format('Y-m-d') : date('Y-m-d'),
            'symptoms' => $case->symptoms ?? ''
        ];

        $this->set(compact('case', 'patients', 'caseData', 'currentHospital'));
    }

    public function editStep2($id = null) {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index']);
        }

        try {
            $case = $this->Cases->get($id, [
                'contain' => [
                    'CasesExamsProcedures' => [
                        'ExamsProcedures' => ['Exams', 'Procedures']
                    ]
                ]
            ]);
            
            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found.'));
            }

            // Check if case can be edited (technician can edit until global status is completed/cancelled)
            if (in_array($case->status, [SiteConstants::CASE_STATUS_COMPLETED, SiteConstants::CASE_STATUS_CANCELLED])) {
                $this->Flash->error(__('This case cannot be edited in its current status.'));
                return $this->redirect(['action' => 'view', $id]);
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found.'));
            return $this->redirect(['action' => 'index']);
        }

        // Check if step 1 data exists
        $step1Data = $this->request->getSession()->read('CaseEditWizard.step1');
        if (empty($step1Data)) {
            $this->Flash->error(__('Please complete Step 1 first.'));
            return $this->redirect(['action' => 'editStep1', $id]);
        }

        // Load departments
        $departmentsTable = $this->fetchTable('Departments');
        $departments = $departmentsTable->find('list')
            ->where(['hospital_id' => $currentHospital->id])
            ->orderBy(['name' => 'ASC'])
            ->toArray();

        // Load sedations
        $sedationsTable = $this->fetchTable('Sedations');
        $sedations = $sedationsTable->find('list', [
            'keyField' => 'id',
            'valueField' => function($sedation) {
                return $sedation->level . ' (' . $sedation->type . ')';
            }
        ])
        ->where(['hospital_id' => $currentHospital->id])
        ->orderBy(['level' => 'ASC'])
        ->toArray();

        // Load exams procedures
        $examsProceduresTable = $this->fetchTable('ExamsProcedures');
        $examsProcedures = $examsProceduresTable->find()
            ->contain([
                'Exams' => ['Modalities', 'Departments'],
                'Procedures'
            ])
            ->where([
                'Exams.hospital_id' => $currentHospital->id
            ])
            ->toArray();

        // Get step 2 data from session if returning, otherwise use case data
        $step2Data = $this->request->getSession()->read('CaseEditWizard.step2') ?? [
            'department_id' => $case->department_id,
            'sedation_id' => $case->sedation_id,
            'priority' => $case->priority,
            'exam_procedures' => collection($case->cases_exams_procedures)->extract('exams_procedure_id')->toArray()
        ];
        
        // Get AI recommendations from session (if any from step 1)
        $aiRecommendations = $this->request->getSession()->read('CaseEditWizard.aiRecommendations') ?? [];

        $this->set(compact('case', 'step1Data', 'step2Data', 'departments', 'sedations', 'examsProcedures', 'aiRecommendations', 'currentHospital'));
    }

    public function editStep3($id = null) {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index']);
        }

        try {
            $case = $this->Cases->get($id);
            
            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found.'));
            }

            // Check if case can be edited (technician can edit until global status is completed/cancelled)
            if (in_array($case->status, [SiteConstants::CASE_STATUS_COMPLETED, SiteConstants::CASE_STATUS_CANCELLED])) {
                $this->Flash->error(__('This case cannot be edited in its current status.'));
                return $this->redirect(['action' => 'view', $id]);
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found.'));
            return $this->redirect(['action' => 'index']);
        }

        // Check if previous steps data exists
        $step1Data = $this->request->getSession()->read('CaseEditWizard.step1');
        $step2Data = $this->request->getSession()->read('CaseEditWizard.step2');
        
        if (empty($step1Data) || empty($step2Data)) {
            $this->Flash->error(__('Please complete all previous steps first.'));
            return $this->redirect(['action' => 'editStep1', $id]);
        }

        // Get step 3 data from session if returning
        $step3Data = $this->request->getSession()->read('CaseEditWizard.step3') ?? [
            'technician_notes' => $case->notes ?? ''
        ];
        
        // Get AI recommendations
        $aiRecommendations = $this->request->getSession()->read('CaseEditWizard.aiRecommendations') ?? [];

        // Load patient info
        $patientsTable = $this->fetchTable('Users');
        $patient = $patientsTable->get($step1Data['patient_id']);

        // Load department info
        $department = null;
        if (!empty($step2Data['department_id'])) {
            $departmentsTable = $this->fetchTable('Departments');
            $department = $departmentsTable->get($step2Data['department_id']);
        }

        // Load sedation info
        $sedation = null;
        if (!empty($step2Data['sedation_id'])) {
            $sedationsTable = $this->fetchTable('Sedations');
            $sedation = $sedationsTable->get($step2Data['sedation_id']);
        }

        // Load selected exams/procedures
        $selectedExamsProcedures = [];
        if (!empty($step2Data['exam_procedures'])) {
            $examsProceduresTable = $this->fetchTable('ExamsProcedures');
            $selectedExamsProcedures = $examsProceduresTable->find()
                ->contain(['Exams', 'Procedures'])
                ->where(['ExamsProcedures.id IN' => $step2Data['exam_procedures']])
                ->toArray();
        }

        $this->set(compact('case', 'step1Data', 'step2Data', 'step3Data', 'patient', 'department', 'sedation', 'selectedExamsProcedures', 'aiRecommendations', 'currentHospital'));
    }

    public function saveEditStep1($id = null) {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setClassName('Json');
        
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'No hospital context found'
                ]));
        }

        $data = $this->request->getData();
        
        // Validate required fields
        $errors = [];
        if (empty($data['patient_id'])) {
            $errors['patient_id'] = 'Patient is required';
        }
        if (empty($data['date'])) {
            $errors['date'] = 'Case date is required';
        }
        if (empty($data['symptoms'])) {
            $errors['symptoms'] = 'Symptoms are required';
        }

        if (!empty($errors)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'errors' => $errors
                ]));
        }

        // Get patient data for display name
        $patientsTable = $this->fetchTable('Users');
        $patient = $patientsTable->get($data['patient_id']);
        
        // Create patient display name with masking
        $patientMaskHelper = new \App\View\Helper\PatientMaskHelper(new \Cake\View\View());
        $maskedPatientName = $patientMaskHelper->displayName($patient, $user);

        // Save to session
        $this->request->getSession()->write('CaseEditWizard.step1', [
            'patient_id' => $data['patient_id'],
            'patient_name' => $maskedPatientName,
            'date' => $data['date'],
            'time' => $data['time'] ?? null,
            'symptoms' => $data['symptoms']
        ]);

        // Note: We don't re-run AI recommendations for edit (use original or manual selection)
        // Clear any old recommendations
        $this->request->getSession()->delete('CaseEditWizard.aiRecommendations');

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'case_id' => $id
            ]));
    }

    public function saveEditStep2($id = null) {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setClassName('Json');
        
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'No hospital context found'
                ]));
        }

        $data = $this->request->getData();
        
        // Validate required fields
        $errors = [];
        if (empty($data['department_id'])) {
            $errors['department_id'] = 'Department is required';
        }
        if (empty($data['priority'])) {
            $errors['priority'] = 'Priority is required';
        }
        if (empty($data['exam_procedures']) || !is_array($data['exam_procedures'])) {
            $errors['exam_procedures'] = 'At least one exam/procedure must be selected';
        }

        if (!empty($errors)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'errors' => $errors
                ]));
        }

        // Save to session
        $this->request->getSession()->write('CaseEditWizard.step2', [
            'department_id' => $data['department_id'],
            'sedation_id' => $data['sedation_id'] ?? null,
            'priority' => $data['priority'],
            'exam_procedures' => $data['exam_procedures']
        ]);

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'case_id' => $id
            ]));
    }

    public function saveEditStep3($id = null) {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setClassName('Json');
        
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'No hospital context found'
                ]));
        }

        try {
            $case = $this->Cases->get($id, [
                'contain' => ['CasesExamsProcedures']
            ]);
            
            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found.'));
            }

        } catch (RecordNotFoundException $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Case not found'
                ]));
        }

        // Get all step data from session
        $step1Data = $this->request->getSession()->read('CaseEditWizard.step1');
        $step2Data = $this->request->getSession()->read('CaseEditWizard.step2');
        $step3Data = $this->request->getData();
        
        if (empty($step1Data) || empty($step2Data)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Previous steps data is missing'
                ]));
        }

        // Clone original case for tracking changes
        $originalCase = clone $case;

        // Update case data
        $case = $this->Cases->patchEntity($case, [
            'patient_id' => $step1Data['patient_id'],
            'date' => $step1Data['date'],
            'symptoms' => $step1Data['symptoms'],
            'department_id' => $step2Data['department_id'],
            'sedation_id' => $step2Data['sedation_id'],
            'priority' => $step2Data['priority'],
            'notes' => $step3Data['technician_notes'] ?? '',
            'current_user_id' => $user->id
        ]);

        if ($this->Cases->save($case)) {
            // Handle exam procedures - full edit approach (add/remove)
            $casesExamsProceduresTable = $this->fetchTable('CasesExamsProcedures');
            
            // Get currently assigned procedures
            $currentAssignments = $casesExamsProceduresTable->find()
                ->where(['case_id' => $case->id])
                ->all()
                ->indexBy('exams_procedure_id')
                ->toArray();
            
            $selectedProcedures = $step2Data['exam_procedures'];
            
            // Remove procedures that are no longer selected
            foreach ($currentAssignments as $procedureId => $assignment) {
                if (!in_array($procedureId, $selectedProcedures)) {
                    $casesExamsProceduresTable->delete($assignment);
                }
            }
            
            // Add new procedures that were selected
            foreach ($selectedProcedures as $procedureId) {
                if (!isset($currentAssignments[$procedureId])) {
                    $casesExamsProcedure = $casesExamsProceduresTable->newEntity([
                        'case_id' => $case->id,
                        'exams_procedure_id' => $procedureId,
                        'status' => SiteConstants::CASE_STATUS_PENDING,
                        'scheduled_at' => null,
                        'notes' => ''
                    ]);
                    
                    $casesExamsProceduresTable->save($casesExamsProcedure);
                }
            }

            // Create new version
            $caseVersionsTable = $this->fetchTable('CaseVersions');
            $latestVersion = $caseVersionsTable->find()
                ->where(['case_id' => $case->id])
                ->orderBy(['version_number' => 'DESC'])
                ->first();

            $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 1;
            
            $version = $caseVersionsTable->newEntity([
                'case_id' => $case->id,
                'version_number' => $newVersionNumber,
                'user_id' => $user->id,
                'timestamp' => new \DateTime()
            ]);
            
            if ($caseVersionsTable->save($version)) {
                $case->current_version_id = $version->id;
                $this->Cases->save($case);
                
                // Log changes in audit trail
                $caseAuditsTable = $this->fetchTable('CaseAudits');
                $changes = $this->getChangedFields($originalCase, $case);
                
                foreach ($changes as $field => $change) {
                    $caseAuditsTable->logChange(
                        $case->id,
                        $version->id,
                        $field,
                        $change['old'],
                        $change['new'],
                        $user->id
                    );
                }
            }

            // Log activity
            $this->activityLogger->log(
                SiteConstants::EVENT_CASE_UPDATED,
                [
                    'user_id' => $user->id,
                    'request' => $this->request,
                    'event_data' => [
                        'case_id' => $case->id,
                        'hospital_id' => $currentHospital->id,
                        'via_wizard' => true
                    ]
                ]
            );

            // Clear wizard session data
            $this->request->getSession()->delete('CaseEditWizard');

            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'case_id' => $case->id
                ]));
        }

        $errors = $case->getErrors();
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => false,
                'errors' => $errors
            ]));
    }


    public function assignProcedures($id = null) {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['action' => 'index']);
        }

        try {
            $case = $this->Cases->get($id, [
                'contain' => [
                    'Users', 
                    'PatientUsers', 
                    'CurrentUsers', 
                    'Hospitals',
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
            
            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found.'));
            }

            // Check if technician can modify procedures (until global status is completed/cancelled)
            if (in_array($case->status, [SiteConstants::CASE_STATUS_COMPLETED, SiteConstants::CASE_STATUS_CANCELLED])) {
                $this->Flash->error(__('Procedures cannot be modified for this case in its current status.'));
                return $this->redirect(['action' => 'view', $id]);
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found.'));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Handle exam procedures - full edit approach (add/remove)
            if (isset($data['exam_procedures'])) {
                $casesExamsProceduresTable = $this->fetchTable('CasesExamsProcedures');
                
                // Get currently assigned procedures
                $currentAssignments = $casesExamsProceduresTable->find()
                    ->where(['case_id' => $case->id])
                    ->all()
                    ->indexBy('exams_procedure_id')
                    ->toArray();
                
                $selectedProcedures = array_keys(array_filter($data['exam_procedures']));
                
                // Remove procedures that are no longer selected
                foreach ($currentAssignments as $procedureId => $assignment) {
                    if (!in_array($procedureId, $selectedProcedures)) {
                        if (!$casesExamsProceduresTable->delete($assignment)) {
                            $this->log('Failed to remove case exam procedure: ' . $procedureId, 'error');
                        }
                    }
                }
                
                // Add new procedures that were selected
                foreach ($selectedProcedures as $procedureId) {
                    if (!isset($currentAssignments[$procedureId])) {
                        $casesExamsProcedure = $casesExamsProceduresTable->newEntity([
                            'case_id' => $case->id,
                            'exams_procedure_id' => $procedureId,
                            'status' => SiteConstants::CASE_STATUS_PENDING,
                            'scheduled_at' => null,
                            'notes' => ''
                        ]);
                        
                        if (!$casesExamsProceduresTable->save($casesExamsProcedure)) {
                            $this->log('Failed to save new case exam procedure', 'error');
                        }
                    }
                }

                // Log activity
                $this->activityLogger->log(
                    SiteConstants::EVENT_CASE_UPDATED,
                    [
                        'user_id' => $user->id,
                        'request' => $this->request,
                        'event_data' => [
                            'case_id' => $case->id,
                            'updated_field' => 'procedures',
                            'hospital_id' => $currentHospital->id
                        ]
                    ]
                );

                $this->Flash->success(__('Procedures have been updated successfully.'));
                return $this->redirect(['action' => 'view', $case->id]);
            }
            
            $this->Flash->error(__('No procedures were selected.'));
        }

        // Get available exam procedures for current hospital
        $examsProceduresTable = $this->fetchTable('ExamsProcedures');
        $examsProceduresQuery = $examsProceduresTable->find()
            ->contain(['Exams' => ['Modalities'], 'Procedures'])
            ->matching('Exams', function ($q) use ($currentHospital) {
                return $q->where(['Exams.hospital_id' => $currentHospital->id]);
            })
            ->orderBy(['Exams.name' => 'ASC', 'Procedures.name' => 'ASC'])
            ->toArray();

        $examsProcedures = collection($examsProceduresQuery)
            ->combine('id', function($ep) {
                $examName = $ep->exam->name ?? 'Unknown Exam';
                $procedureName = $ep->procedure->name ?? 'Unknown Procedure';
                $modalityName = $ep->exam->modality->name ?? '';
                $displayName = $examName . ' - ' . $procedureName;
                if ($modalityName) {
                    $displayName .= ' (' . $modalityName . ')';
                }
                return $displayName;
            })
            ->toArray();

        // Get currently assigned exam procedures for pre-selection
        $assignedExamProcedures = [];
        if (!empty($case->cases_exams_procedures)) {
            $assignedExamProcedures = collection($case->cases_exams_procedures)
                ->extract('exams_procedure_id')
                ->toArray();
        }

        // Group procedures by modality for better organization
        $proceduresByModality = [];
        foreach ($examsProceduresQuery as $ep) {
            $modalityName = $ep->exam->modality->name ?? 'General';
            if (!isset($proceduresByModality[$modalityName])) {
                $proceduresByModality[$modalityName] = [];
            }
            $proceduresByModality[$modalityName][] = $ep;
        }

        $this->set(compact('case', 'examsProcedures', 'assignedExamProcedures', 'proceduresByModality', 'currentHospital'));
    }

    public function uploadDocument($id = null) {
        $this->request->allowMethod(['post', 'put']);
        
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['action' => 'index']);
        }

        try {
            $case = $this->Cases->get($id, [
                'contain' => ['PatientUsers']
            ]);
            
            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found.'));
            }

            // Check if case can be modified (technician can upload documents until global status is completed)
            if ($case->status === SiteConstants::CASE_STATUS_COMPLETED) {
                $this->Flash->error(__('Cannot upload documents to a completed case.'));
                return $this->redirect(['action' => 'view', $id]);
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found.'));
            return $this->redirect(['action' => 'index']);
        }

        $data = $this->request->getData();
        
        // Get the uploaded file (it's an UploadedFile object in CakePHP 5)
        $uploadedFile = $data['document_file'] ?? null;
        
        if (empty($uploadedFile) || !is_object($uploadedFile)) {
            $this->Flash->error(__('Please select a file to upload.'));
            return $this->redirect(['action' => 'view', $id]);
        }
        
        // Check for upload errors with specific messages
        $uploadError = $uploadedFile->getError();
        if ($uploadError !== UPLOAD_ERR_OK) {
            $errorMessage = $this->getUploadErrorMessage($uploadError);
            $this->Flash->error($errorMessage);
            Log::error('File upload error: ' . $errorMessage . ' (Error code: ' . $uploadError . ')');
            return $this->redirect(['action' => 'view', $id]);
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
            $document = $documentsTable->newEntity([
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
            ]);

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
                    [
                        'user_id' => $user->id,
                        'request' => $this->request,
                        'event_data' => [
                            'case_id' => $case->id,
                            'document_id' => $document->id,
                            'document_type' => $document->document_type,
                            'hospital_id' => $currentHospital->id
                        ]
                    ]
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
            $this->Flash->error(__('Upload failed: {0}', $uploadResult['error']));
        }

        return $this->redirect(['action' => 'view', $id]);
    }

    public function downloadDocument($id = null) {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['action' => 'index']);
        }

        try {
            $documentsTable = $this->fetchTable('Documents');
            $document = $documentsTable->get($id, [
                'contain' => ['Cases']
            ]);
            
            // Verify hospital access through case
            if ($document->case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Document not found.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Document not found.'));
            return $this->redirect(['action' => 'index']);
        }

        // Stream file content directly instead of redirecting
        $s3Service = new \App\Lib\S3DocumentService();
        
        try {
            // Check if S3 is enabled
            if ($s3Service->isS3Enabled() && strpos($document->file_path, 'uploads/') !== 0) {
                // Get from S3 and stream
                $s3Client = $s3Service->getS3Client();
                $result = $s3Client->getObject([
                    'Bucket' => $s3Service->getBucket(),
                    'Key' => $document->file_path
                ]);
                
                $content = $result['Body']->getContents();
                $contentType = $result['ContentType'] ?? $document->file_type;
            } else {
                // Get from local storage
                $fullPath = WWW_ROOT . str_replace('/', DS, $document->file_path);
                if (!file_exists($fullPath)) {
                    $this->Flash->error(__('File not found.'));
                    return $this->redirect(['action' => 'view', $document->case_id]);
                }
                $content = file_get_contents($fullPath);
                $contentType = $document->file_type;
            }

            // Log activity
            $this->activityLogger->log(
                SiteConstants::EVENT_DOCUMENT_DOWNLOADED,
                [
                    'user_id' => $user->id,
                    'request' => $this->request,
                    'event_data' => [
                        'case_id' => $document->case_id,
                        'document_id' => $document->id,
                        'document_type' => $document->document_type,
                        'hospital_id' => $currentHospital->id
                    ]
                ]
            );

            // Return document with download headers
            return $this->response
                ->withType($contentType)
                ->withStringBody($content)
                ->withHeader('Content-Disposition', 'attachment; filename="' . $document->original_filename . '"')
                ->withHeader('Content-Length', (string)strlen($content))
                ->withHeader('Cache-Control', 'no-cache, must-revalidate')
                ->withHeader('Pragma', 'no-cache');

        } catch (\Exception $e) {
            Log::error('Failed to download document: ' . $e->getMessage());
            $this->Flash->error(__('Failed to download document. Please try again.'));
            return $this->redirect(['action' => 'view', $document->case_id]);
        }
    }

    public function viewDocument($id = null) {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['action' => 'index']);
        }

        try {
            $documentsTable = $this->fetchTable('Documents');
            $document = $documentsTable->get($id, [
                'contain' => ['Cases']
            ]);
            
            // Verify hospital access through case
            if ($document->case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Document not found.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Document not found.'));
            return $this->redirect(['action' => 'index']);
        }

        // Get download URL from S3 (or local path)
        $s3Service = new \App\Lib\S3DocumentService();
        $fileUrl = $s3Service->getDownloadUrl($document->file_path);

        if ($fileUrl) {
            // Log activity
            $this->activityLogger->log(
                'document_viewed',
                [
                    'user_id' => $user->id,
                    'request' => $this->request,
                    'event_data' => [
                        'case_id' => $document->case_id,
                        'document_id' => $document->id,
                        'document_type' => $document->document_type,
                        'hospital_id' => $currentHospital->id
                    ]
                ]
            );

            // Return JSON with document info for preview modal
            $this->viewBuilder()->setLayout('ajax');
            $this->set(compact('document', 'fileUrl'));
            $this->set('_serialize', ['document', 'fileUrl']);
            
            // Set response as JSON
            $this->response = $this->response->withType('application/json');
            $this->response = $this->response->withStringBody(json_encode([
                'success' => true,
                'document' => [
                    'id' => $document->id,
                    'filename' => $document->original_filename,
                    'type' => $document->file_type,
                    'size' => $document->file_size,
                    'url' => $fileUrl
                ]
            ]));
            
            return $this->response;
        } else {
            $this->response = $this->response->withType('application/json');
            $this->response = $this->response->withStringBody(json_encode([
                'success' => false,
                'error' => 'Failed to generate view link'
            ]));
            return $this->response;
        }
    }

    public function proxyDocument($id = null) {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            return $this->response
                ->withStatus(403)
                ->withStringBody('Access denied');
        }

        try {
            $documentsTable = $this->fetchTable('Documents');
            $document = $documentsTable->get($id, [
                'contain' => ['Cases']
            ]);
            
            // Verify hospital access through case
            if ($document->case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Document not found.'));
            }

        } catch (RecordNotFoundException $e) {
            return $this->response
                ->withStatus(404)
                ->withStringBody('Document not found');
        }

        // Get document content from S3 or local storage
        $s3Service = new \App\Lib\S3DocumentService();
        
        try {
            // Check if S3 is enabled
            if ($s3Service->isS3Enabled() && strpos($document->file_path, 'uploads/') !== 0) {
                // Get from S3
                $s3Client = $s3Service->getS3Client();
                $result = $s3Client->getObject([
                    'Bucket' => env('AWS_S3_BUCKET'),
                    'Key' => $document->file_path
                ]);
                
                $content = $result['Body']->getContents();
            } else {
                // Get from local storage
                $fullPath = WWW_ROOT . str_replace('/', DS, $document->file_path);
                if (!file_exists($fullPath)) {
                    return $this->response
                        ->withStatus(404)
                        ->withStringBody('File not found');
                }
                $content = file_get_contents($fullPath);
            }

            // Log activity
            $this->activityLogger->log(
                'document_proxy_accessed',
                [
                    'user_id' => $user->id,
                    'request' => $this->request,
                    'event_data' => [
                        'case_id' => $document->case_id,
                        'document_id' => $document->id,
                        'document_type' => $document->document_type,
                        'hospital_id' => $currentHospital->id
                    ]
                ]
            );

            // Return document with proper headers
            return $this->response
                ->withType($document->file_type)
                ->withHeader('Content-Disposition', 'inline; filename="' . $document->original_filename . '"')
                ->withHeader('Content-Length', (string)strlen($content))
                ->withHeader('Cache-Control', 'private, max-age=3600')
                ->withHeader('X-Content-Type-Options', 'nosniff')
                ->withStringBody($content);

        } catch (\Exception $e) {
            Log::error('Failed to proxy document: ' . $e->getMessage(), [
                'document_id' => $id,
                'file_path' => $document->file_path
            ]);
            
            return $this->response
                ->withStatus(500)
                ->withStringBody('Failed to load document');
        }
    }

    public function assign($id = null) {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['action' => 'index']);
        }

        try {
            $case = $this->Cases->get($id, [
                'contain' => [
                    'PatientUsers',
                    'CurrentUsers',
                    'Departments',
                    'Sedations',
                    'CaseAssignments' => [
                        'AssignedToUsers' => ['Roles'],
                        'Users' => ['Roles'],
                        'sort' => ['CaseAssignments.timestamp' => 'DESC']
                    ]
                ]
            ]);
            
            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found.'));
            }

            // Check if case can be assigned (main status should be in_progress, not completed/cancelled)
            if (!in_array($case->status, [SiteConstants::CASE_STATUS_IN_PROGRESS])) {
                $this->Flash->error(__('This case cannot be assigned in its current status.'));
                return $this->redirect(['action' => 'view', $id]);
            }

            // Check if technician has created a PDF report for this case
            $reportsTable = $this->fetchTable('Reports');
            $existingReport = $reportsTable->find()
                ->where([
                    'case_id' => $id,
                    'user_id' => $user->id,
                    'type' => 'PDF'
                ])
                ->first();
            
            // If no report exists, auto-create one with default content
            if (!$existingReport) {
                // Load case with all data for report generation
                $caseWithData = $this->Cases->get($id, [
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
                
                // Generate default report content
                $view = new \Cake\View\View($this->request, $this->response);
                $defaultContent = $view->element('Reports/report_content', ['case' => $caseWithData]);
                
                $report = $reportsTable->newEntity([
                    'case_id' => $id,
                    'hospital_id' => $case->hospital_id,
                    'status' => SiteConstants::CASE_STATUS_IN_PROGRESS,
                    'user_id' => $user->id,
                    'type' => 'PDF',
                    'status' => SiteConstants::CASE_STATUS_COMPLETED,
                    'report_data' => json_encode(['content' => $defaultContent])
                ]);
                
                if ($reportsTable->save($report)) {
                    // Log activity
                    $this->activityLogger->log(
                        'report_created',
                        [
                            'user_id' => $user->id,
                            'request' => $this->request,
                            'event_data' => [
                                'case_id' => $id,
                                'report_id' => $report->id,
                                'auto_created' => true,
                                'status' => SiteConstants::CASE_STATUS_IN_PROGRESS
                            ]
                        ]
                    );
                    
                    $this->Flash->success(__('PDF report has been automatically created. You can now proceed with assignment.'));
                } else {
                    $this->Flash->error(__('Unable to create report. Please try again.'));
                    return $this->redirect(['action' => 'view', $id]);
                }
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found.'));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            if (empty($data['assigned_to'])) {
                $this->Flash->error(__('Please select a scientist to assign the case to.'));
            } else {
                // Create assignment record
                $caseAssignmentsTable = $this->fetchTable('CaseAssignments');
                // Ensure case_version_id is set
                $caseVersionId = $case->current_version_id;
                if (empty($caseVersionId)) {
                    $caseVersionsTable = $this->fetchTable('CaseVersions');
                    $latestVersion = $caseVersionsTable->find()
                        ->where(['case_id' => $case->id])
                        ->orderBy(['version_number' => 'DESC'])
                        ->first();
                    if ($latestVersion) {
                        $caseVersionId = $latestVersion->id;
                    }
                }
                $assignment = $caseAssignmentsTable->newEntity([
                    'case_id' => $case->id,
                    'case_version_id' => $caseVersionId,
                    'user_id' => $user->id,
                    'assigned_to' => $data['assigned_to'],
                    'timestamp' => new \DateTime(),
                    'notes' => $data['notes'] ?? ''
                ]);
                
                if ($caseAssignmentsTable->save($assignment)) {
                    // Get the assigned user's role to determine if assigning to scientist
                    $usersTable = $this->fetchTable('Users');
                    $assignedUser = $usersTable->get($data['assigned_to'], ['contain' => ['Roles']]);
                    $assignedRole = null;
                    if ($assignedUser && isset($assignedUser->role) && isset($assignedUser->role->type)) {
                        $assignedRole = $assignedUser->role->type;
                    }

                    // Handle role-based status transition
                    if ($assignedRole === SiteConstants::ROLE_TYPE_SCIENTIST) {
                        $this->caseStatusService->transitionOnAssignment(
                            $case, 
                            'technician', 
                            'scientist', 
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
                            [
                                'user_id' => $user->id,
                                'request' => $this->request,
                                'event_data' => ['case_id' => $case->id, 'assigned_to' => $data['assigned_to'], 'hospital_id' => $currentHospital->id]
                            ]
                        );

                        $this->Flash->success(__('Case has been assigned successfully.'));
                        return $this->redirect(['action' => 'view', $case->id]);
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

        // Get scientists for this hospital
        $usersTable = $this->fetchTable('Users');
        $scientists = $usersTable->find('list', [
            'keyField' => 'id',
            'valueField' => function($user) {
                return $user->first_name . ' ' . $user->last_name . ' (' . $user->email . ')';
            }
        ])
        ->innerJoinWith('Roles')
        ->where([
            'Users.hospital_id' => $currentHospital->id,
            'Roles.type' => SiteConstants::ROLE_TYPE_SCIENTIST
        ])
        ->orderBy(['Users.last_name' => 'ASC', 'Users.first_name' => 'ASC'])
        ->toArray();

        $this->set(compact('case', 'scientists', 'currentHospital'));
    }

    private function getChangedFields($original, $updated): array {
        $changes = [];
        $trackableFields = ['status', 'priority', 'patient_id', 'date', 'department_id', 'sedation_id', 'notes'];
        
        foreach ($trackableFields as $field) {
            if ($original->$field !== $updated->$field) {
                $changes[$field] = [
                    'old' => (string)$original->$field,
                    'new' => (string)$updated->$field
                ];
            }
        }
        
        return $changes;
    }

    public function analyzeDocument($id = null) {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setClassName('Json');
        
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'No hospital context found'
                ]));
        }

        try {
            $case = $this->Cases->get($id, [
                'contain' => [
                    'CasesExamsProcedures' => [
                        'ExamsProcedures' => [
                            'Exams' => ['Modalities'],
                            'Procedures'
                        ]
                    ]
                ]
            ]);
            
            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found.'));
            }

        } catch (RecordNotFoundException $e) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Case not found'
                ]));
        }

        // Get uploaded file
        $file = $this->request->getData('file');
        
        if (empty($file)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'No file provided'
                ]));
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
                
                $fileData = [
                    'name' => $file->getClientFilename(),
                    'type' => $file->getClientMediaType(),
                    'tmp_name' => $tmpPath,
                    'size' => $file->getSize(),
                    'error' => $file->getError()
                ];
            }
        } catch (\Exception $e) {
            Log::error('File conversion error: ' . $e->getMessage());
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Failed to process uploaded file'
                ]));
        }

        // Prepare procedures list for analysis
        $procedureOptions = [];
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
                [
                    'user_id' => $user->id,
                    'request' => $this->request,
                    'event_data' => [
                        'case_id' => $case->id,
                        'action' => 'document_analyzed',
                        'hospital_id' => $currentHospital->id,
                        'detected_type' => $analysis['detected_type'] ?? null,
                        'method' => $analysis['method'] ?? 'unknown'
                    ]
                ]
            );
        }

        return $this->response->withType('application/json')
            ->withStringBody(json_encode($analysis));
    }

    public function saveStep1() {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setClassName('Json');
        
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'No hospital context found'
                ]));
        }

        $data = $this->request->getData();
        
        // Validate required fields
        $errors = [];
        if (empty($data['patient_id'])) {
            $errors['patient_id'] = 'Patient is required';
        }
        if (empty($data['date'])) {
            $errors['date'] = 'Case date is required';
        }
        if (empty($data['symptoms'])) {
            $errors['symptoms'] = 'Symptoms are required';
        }

        if (!empty($errors)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'errors' => $errors
                ]));
        }

        // Get patient data
        $patientsTable = $this->fetchTable('Users');
        $patient = $patientsTable->get($data['patient_id']);
        
        // Create patient display name with masking
        $patientMaskHelper = new \App\View\Helper\PatientMaskHelper(new \Cake\View\View());
        $maskedPatientName = $patientMaskHelper->displayName($patient, $user);

        // Save to session
        $this->request->getSession()->write('CaseWizard.step1', [
            'patient_id' => $data['patient_id'],
            'patient_name' => $maskedPatientName,
            'date' => $data['date'],
            'time' => $data['time'] ?? null,
            'symptoms' => $data['symptoms']
        ]);

        // Get AI recommendations if enabled
        $aiRecommendations = [];
        $hospitalId = $currentHospital->id ?? 1;
        $userId = $this->Authentication->getIdentity()->getIdentifier() ?? null;
        
        $recommendationService = new \App\Service\CaseRecommendationService($hospitalId, $userId);
        
        if ($recommendationService->isEnabled()) {
            Log::info('Case recommendations using AI', [
                'hospital_id' => $hospitalId,
                'provider' => $recommendationService->getProvider()
            ]);
            
            // Get patient data
            $patientsTable = $this->fetchTable('Users');
            $patient = $patientsTable->get($data['patient_id']);
            
            // Calculate age from date of birth if available
            $age = 'unknown';
            if (!empty($patient->dob)) {
                $dobString = is_object($patient->dob) ? $patient->dob->format('Y-m-d') : $patient->dob;
                $dob = new \DateTime($dobString);
                $now = new \DateTime();
                $age = $dob->diff($now)->y;
            }

            $patientData = [
                'age' => $age,
                'gender' => $patient->gender ?? 'unknown'
            ];

            // Get available options
            $departmentsTable = $this->fetchTable('Departments');
            $departments = $departmentsTable->find('list')
                ->where(['hospital_id' => $currentHospital->id])
                ->toArray();

            $sedationsTable = $this->fetchTable('Sedations');
            $sedations = $sedationsTable->find('list', [
                'keyField' => 'id',
                'valueField' => function($sedation) {
                    return $sedation->level . ' (' . $sedation->type . ')';
                }
            ])
            ->where(['hospital_id' => $currentHospital->id])
            ->toArray();

            $examsProceduresTable = $this->fetchTable('ExamsProcedures');
            $examsProceduresQuery = $examsProceduresTable->find()
                ->contain(['Exams', 'Procedures'])
                ->where(['Exams.hospital_id' => $currentHospital->id])
                ->toArray();

            $examsProcedures = collection($examsProceduresQuery)
                ->combine('id', function($ep) {
                    $examName = $ep->exam->name ?? 'Unknown Exam';
                    $procedureName = $ep->procedure->name ?? 'Unknown Procedure';
                    return $examName . ' - ' . $procedureName;
                })
                ->toArray();

            $aiRecommendations = $recommendationService->getRecommendations(
                $patientData,
                $data['symptoms'],
                $examsProcedures,
                $departments,
                $sedations
            );

            // Log the recommendations for debugging
            Log::write('debug', 'AI Recommendations: ' . json_encode($aiRecommendations));

            // Save AI recommendations to session
            $this->request->getSession()->write('CaseWizard.aiRecommendations', $aiRecommendations);
        } else {
            // Clear old AI recommendations from session when AI is disabled
            $this->request->getSession()->delete('CaseWizard.aiRecommendations');
            Log::write('debug', 'AI disabled - cleared old recommendations from session');
        }

        // Log final response for debugging
        Log::write('debug', 'SaveStep1 Response - AI Enabled: ' . ($recommendationService->isEnabled() ? 'true' : 'false') . ', Recommendations: ' . json_encode($aiRecommendations));

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'ai_enabled' => $recommendationService->isEnabled(),
                'recommendations' => $aiRecommendations
            ]));
    }

    public function saveStep2() {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setClassName('Json');
        
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'No hospital context found'
                ]));
        }

        $data = $this->request->getData();
        
        // Validate required fields
        $errors = [];
        if (empty($data['department_id'])) {
            $errors['department_id'] = 'Department is required';
        }
        if (empty($data['priority'])) {
            $errors['priority'] = 'Priority is required';
        }
        if (empty($data['exam_procedures']) || !is_array($data['exam_procedures'])) {
            $errors['exam_procedures'] = 'At least one exam/procedure must be selected';
        }

        if (!empty($errors)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'errors' => $errors
                ]));
        }

        // Save to session
        $this->request->getSession()->write('CaseWizard.step2', [
            'department_id' => $data['department_id'],
            'sedation_id' => $data['sedation_id'] ?? null,
            'priority' => $data['priority'],
            'exam_procedures' => $data['exam_procedures']
        ]);

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true
            ]));
    }


    public function saveStep3() {
        $this->request->allowMethod(['post']);
        $this->viewBuilder()->setClassName('Json');
        
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'No hospital context found'
                ]));
        }

        // Get all step data from session
        $step1Data = $this->request->getSession()->read('CaseWizard.step1');
        $step2Data = $this->request->getSession()->read('CaseWizard.step2');
        $step3Data = $this->request->getData();
        
        if (empty($step1Data) || empty($step2Data)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Previous steps data is missing'
                ]));
        }

        // Save step 3 to session
        $this->request->getSession()->write('CaseWizard.step3', [
            'technician_notes' => $step3Data['technician_notes'] ?? ''
        ]);

        // Combine AI notes and technician notes
        $aiRecommendations = $this->request->getSession()->read('CaseWizard.aiRecommendations') ?? [];
        $aiNotes = $aiRecommendations['notes'] ?? '';
        $technicianNotes = $step3Data['technician_notes'] ?? '';
        
        $combinedNotes = '';
        if (!empty($aiNotes)) {
            $combinedNotes .= "AI Recommendations:\n" . $aiNotes . "\n\n";
        }
        if (!empty($technicianNotes)) {
            $combinedNotes .= "Technician Notes:\n" . $technicianNotes;
        }

        // Create the case
        // Note: scientist_status and doctor_status start as 'draft' until assigned
        $case = $this->Cases->newEntity([
            'user_id' => $user->id,
            'hospital_id' => $currentHospital->id,
            'patient_id' => $step1Data['patient_id'],
            'current_user_id' => $user->id,
            'date' => $step1Data['date'],
            'department_id' => $step2Data['department_id'],
            'sedation_id' => $step2Data['sedation_id'],
            'priority' => $step2Data['priority'],
            'status' => SiteConstants::CASE_STATUS_IN_PROGRESS,
            'technician_status' => SiteConstants::CASE_STATUS_IN_PROGRESS,
            'scientist_status' => SiteConstants::CASE_STATUS_DRAFT,
            'doctor_status' => SiteConstants::CASE_STATUS_DRAFT,
            'notes' => $combinedNotes,
            'symptoms' => $step1Data['symptoms']
        ]);

        if ($this->Cases->save($case)) {
            // Handle exam procedures selection
            if (!empty($step2Data['exam_procedures'])) {
                $casesExamsProceduresTable = $this->fetchTable('CasesExamsProcedures');
                
                foreach ($step2Data['exam_procedures'] as $examProcedureId) {
                    $casesExamsProcedure = $casesExamsProceduresTable->newEntity([
                        'case_id' => $case->id,
                        'exams_procedure_id' => $examProcedureId,
                        'status' => SiteConstants::CASE_STATUS_PENDING,
                        'scheduled_at' => null,
                        'notes' => ''
                    ]);
                    
                    $casesExamsProceduresTable->save($casesExamsProcedure);
                }
            }

            // Create initial version
            $caseVersionsTable = $this->fetchTable('CaseVersions');
            $version = $caseVersionsTable->newEntity([
                'case_id' => $case->id,
                'version_number' => 1,
                'user_id' => $user->id,
                'timestamp' => new \DateTime()
            ]);
            
            if ($caseVersionsTable->save($version)) {
                // Update case with initial version
                $case->current_version_id = $version->id;
                $this->Cases->save($case);
                
                // Log initial case creation in audit trail
                $caseAuditsTable = $this->fetchTable('CaseAudits');
                $caseAuditsTable->logChange(
                    $case->id,
                    $version->id,
                    'case_created',
                    '',
                    'Initial case creation',
                    $user->id
                );
            }

            // Log activity
            $this->activityLogger->log(
                SiteConstants::EVENT_CASE_CREATED,
                [
                    'user_id' => $user->id,
                    'request' => $this->request,
                    'event_data' => [
                        'case_id' => $case->id,
                        'hospital_id' => $currentHospital->id,
                        'ai_assisted' => !empty($aiRecommendations['ai_generated'])
                    ]
                ]
            );

            // Clear wizard session data
            $this->request->getSession()->delete('CaseWizard');

            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'case_id' => $case->id
                ]));
        }

        $errors = $case->getErrors();
        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => false,
                'errors' => $errors
            ]));
    }

    private function getUploadErrorMessage(int $errorCode): string {
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
                ]
            ]
        ]);
        
        // Check if user has permission to create report for this case
        if ($case->user_id !== $user->id) {
            $this->Flash->error(__('You can only create reports for your own cases.'));
            return $this->redirect(['action' => 'index']);
        }
        
        // Check if case has a report already
        $reportsTable = $this->fetchTable('Reports');
        $existingReport = $reportsTable->find()
            ->where(['case_id' => $id])
            ->first();
            
        if ($existingReport) {
            $this->Flash->info(__('This case already has a report. Redirecting to edit the existing report.'));
            return $this->redirect(['controller' => 'Reports', 'action' => 'add', $id]);
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
            
            return $this->redirect(['controller' => 'Reports', 'action' => 'add', $id]);
        } else {
            $this->Flash->error(__('Unable to create report. Please try again.'));
            return $this->redirect(['action' => 'view', $id]);
        }
    }


    private function getAuthUser() {
        $identity = $this->request->getAttribute('identity');
        return $identity->getOriginalData();
    }
}
