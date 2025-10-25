<?php
declare(strict_types=1);

namespace App\Controller\Technician;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use App\Lib\UserActivityLogger;
use App\Constants\SiteConstants;
use Cake\Log\Log;
use App\Service\DocumentContentService;
use App\Service\AiReportGenerationService;
use App\Service\ReportAssemblyService;
use Cake\Core\Configure;

/**
 * Cases Controller
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
     * Initialize method
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->activityLogger = new UserActivityLogger();
        
        // Set technician layout for all actions
        $this->viewBuilder()->setLayout('technician');
    }

    /**
     * Index method
     *
     * @return \Psr\Http\Message\ResponseInterface|null|void Renders view
     */
    public function index()
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index']);
        }

        // Get filter parameters
        $status = $this->request->getQuery('status');
        $priority = $this->request->getQuery('priority');
        $search = $this->request->getQuery('search');

        // Build query
        $query = $this->Cases->find()
            ->contain([
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
            ])
            ->where(['Cases.hospital_id' => $currentHospital->id]);

        // Apply filters
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

        $cases = $this->paginate($query->orderBy(['Cases.created' => 'DESC']));

        // Get filter options
        $statusOptions = [
            'all' => 'All Status',
            SiteConstants::CASE_STATUS_DRAFT => 'Draft',
            SiteConstants::CASE_STATUS_ASSIGNED => 'Assigned',
            SiteConstants::CASE_STATUS_IN_PROGRESS => 'In Progress',
            'review' => 'Under Review',
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
            'hospitalName'
        ));
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
            return $this->redirect(['action' => 'index']);
        }

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
                    'CaseAudits' => ['ChangedByUsers'],
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

        $this->set(compact('case', 'currentHospital', 'isS3Enabled'));
    }

    /**
     * Add method - Step 1: Patient Information
     *
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        // Clear any existing wizard data when starting a new case
        $this->request->getSession()->delete('CaseWizard');
        
        return $this->redirect(['action' => 'addStep1']);
    }

    /**
     * Add Case - Step 1: Patient Information
     */
    public function addStep1()
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index']);
        }

        // Get patients for this hospital
        $patientsTable = $this->fetchTable('Users');
        $patients = $patientsTable->find('list', [
            'keyField' => 'id',
            'valueField' => function($user) {
                return $user->first_name . ' ' . $user->last_name . ' (' . $user->username . ')';
            }
        ])
        ->innerJoinWith('Roles')
        ->where([
            'Users.hospital_id' => $currentHospital->id,
            'Users.status' => 'active',
            'Roles.type' => 'patient'
        ])
        ->orderBy(['Users.last_name' => 'ASC', 'Users.first_name' => 'ASC'])
        ->toArray();

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

    /**
     * Add Case - Step 2: Department & Procedures
     */
    public function addStep2()
    {
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
        $examsProceduresQuery = $examsProceduresTable->find()
            ->contain([
                'Exams' => ['Modalities', 'Departments'],
                'Procedures'
            ])
            ->where([
                'Exams.hospital_id' => $currentHospital->id
            ])
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

        // Get step 2 data from session if returning
        $step2Data = $this->request->getSession()->read('CaseWizard.step2') ?? [];
        
        // Get AI recommendations from session
        $aiRecommendations = $this->request->getSession()->read('CaseWizard.aiRecommendations') ?? [];

        $this->set(compact('step1Data', 'step2Data', 'departments', 'sedations', 'examsProcedures', 'aiRecommendations', 'currentHospital'));
    }

    /**
     * Add Case - Step 3: Review & Notes
     */
    public function addStep3()
    {
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

    /**
     * Edit method
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        // Clear any existing edit wizard data and redirect to step 1
        $this->request->getSession()->delete('CaseEditWizard');
        return $this->redirect(['action' => 'editStep1', $id]);
    }

    /**
     * Edit Case - Step 1: Patient Information
     */
    public function editStep1($id = null)
    {
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

            // Check if case can be edited
            if (!in_array($case->status, [SiteConstants::CASE_STATUS_DRAFT, SiteConstants::CASE_STATUS_ASSIGNED])) {
                $this->Flash->error(__('This case cannot be edited in its current status.'));
                return $this->redirect(['action' => 'view', $id]);
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found.'));
            return $this->redirect(['action' => 'index']);
        }

        // Get patients for this hospital
        $patientsTable = $this->fetchTable('Users');
        $patients = $patientsTable->find('list', [
            'keyField' => 'id',
            'valueField' => function($user) {
                return $user->first_name . ' ' . $user->last_name . ' (' . $user->username . ')';
            }
        ])
        ->innerJoinWith('Roles')
        ->where([
            'Users.hospital_id' => $currentHospital->id,
            'Users.status' => 'active',
            'Roles.type' => 'patient'
        ])
        ->orderBy(['Users.last_name' => 'ASC', 'Users.first_name' => 'ASC'])
        ->toArray();

        // Get session data if returning from later steps, otherwise use case data
        $caseData = $this->request->getSession()->read('CaseEditWizard.step1') ?? [
            'patient_id' => $case->patient_id,
            'date' => $case->date ? $case->date->format('Y-m-d') : date('Y-m-d'),
            'symptoms' => $case->symptoms ?? ''
        ];

        $this->set(compact('case', 'patients', 'caseData', 'currentHospital'));
    }

    /**
     * Edit Case - Step 2: Department & Procedures
     */
    public function editStep2($id = null)
    {
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
        $examsProceduresQuery = $examsProceduresTable->find()
            ->contain([
                'Exams' => ['Modalities', 'Departments'],
                'Procedures'
            ])
            ->where([
                'Exams.hospital_id' => $currentHospital->id
            ])
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

    /**
     * Edit Case - Step 3: Review & Notes
     */
    public function editStep3($id = null)
    {
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

    /**
     * AJAX endpoint to save Edit Step 1 data
     */
    public function saveEditStep1($id = null)
    {
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

        // Save to session
        $this->request->getSession()->write('CaseEditWizard.step1', [
            'patient_id' => $data['patient_id'],
            'date' => $data['date'],
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

    /**
     * AJAX endpoint to save Edit Step 2 data
     */
    public function saveEditStep2($id = null)
    {
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

    /**
     * AJAX endpoint to save Edit Step 3 data and update the case
     */
    public function saveEditStep3($id = null)
    {
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

    /**
     * Original edit method (kept for reference/backwards compatibility)
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function editOld($id = null)
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['action' => 'index']);
        }

        try {
            $case = $this->Cases->get($id, [
                'contain' => [
                    'CurrentVersions',
                    'CasesExamsProcedures' => [
                        'ExamsProcedures' => ['Exams', 'Procedures']
                    ]
                ]
            ]);
            
            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new RecordNotFoundException(__('Case not found.'));
            }

                        // Check if case can be edited (only drafts and assigned cases)
            if (!in_array($case->status, [SiteConstants::CASE_STATUS_DRAFT, SiteConstants::CASE_STATUS_ASSIGNED])) {
                $this->Flash->error(__('This case cannot be edited in its current status.'));
                return $this->redirect(['action' => 'view', $id]);
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found.'));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $originalCase = clone $case;
            
            $case = $this->Cases->patchEntity($case, $data);
            
            if ($this->Cases->save($case)) {
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
                }

                // Create new version if significant changes occurred
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
                    // Update case with current version
                    $case->current_version_id = $version->id;
                    $case->current_user_id = $user->id;
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
                        'event_data' => ['case_id' => $case->id, 'hospital_id' => $currentHospital->id, 'changes' => array_keys($changes)]
                    ]
                );

                $this->Flash->success(__('The case has been updated successfully.'));
                return $this->redirect(['action' => 'view', $case->id]);
            }
            
            $this->Flash->error(__('The case could not be saved. Please, try again.'));
        }

        // Get patients for this hospital
        $patientsTable = $this->fetchTable('Users');
        $patients = $patientsTable->find('list', [
            'keyField' => 'id',
            'valueField' => function($user) {
                return $user->first_name . ' ' . $user->last_name . ' (' . $user->username . ')';
            }
        ])
        ->innerJoinWith('Roles')
        ->where([
            'Users.hospital_id' => $currentHospital->id,
            'Users.status' => 'active',
            'Roles.type' => 'patient'
        ])
        ->orderBy(['Users.last_name' => 'ASC', 'Users.first_name' => 'ASC'])
        ->toArray();

        $priorities = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];

        $statuses = [
            SiteConstants::CASE_STATUS_DRAFT => 'Draft',
            SiteConstants::CASE_STATUS_ASSIGNED => 'Assigned',
            SiteConstants::CASE_STATUS_IN_PROGRESS => 'In Progress',
            'review' => 'Under Review',
            SiteConstants::CASE_STATUS_COMPLETED => 'Completed',
            SiteConstants::CASE_STATUS_CANCELLED => 'Cancelled'
        ];

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

        // Load modalities for the hospital
        $modalitiesTable = $this->fetchTable('Modalities');
        $modalities = $modalitiesTable->find('list')
            ->where(['hospital_id' => $currentHospital->id])
            ->orderBy(['name' => 'ASC'])
            ->toArray();

        // Load exams procedures with their related data
        $examsProceduresTable = $this->fetchTable('ExamsProcedures');
        $examsProceduresQuery = $examsProceduresTable->find()
            ->contain([
                'Exams' => ['Modalities', 'Departments'],
                'Procedures'
            ])
            ->where([
                'Exams.hospital_id' => $currentHospital->id
            ])
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

        $this->set(compact('case', 'patients', 'priorities', 'statuses', 'departments', 'sedations', 'modalities', 'examsProcedures', 'assignedExamProcedures', 'currentHospital'));
    }

    /**
     * Assign procedures method - dedicated view for procedure assignment
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects on successful assignment.
     */
    public function assignProcedures($id = null)
    {
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

            // Check if case can have procedures assigned (only drafts and assigned cases)
            if (!in_array($case->status, [SiteConstants::CASE_STATUS_DRAFT, SiteConstants::CASE_STATUS_ASSIGNED])) {
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

    /**
     * Upload document method
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects on successful upload.
     */
    public function uploadDocument($id = null)
    {
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

    /**
     * Download document method
     *
     * @param string|null $id Document id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects or serves file.
     */
    public function downloadDocument($id = null)
    {
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

        // Get download URL from S3
        $s3Service = new \App\Lib\S3DocumentService();
        $downloadUrl = $s3Service->getDownloadUrl($document->file_path);

        if ($downloadUrl) {
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

            return $this->redirect($downloadUrl);
        } else {
            $this->Flash->error(__('Failed to generate download link. Please try again.'));
            return $this->redirect(['action' => 'view', $document->case_id]);
        }
    }

    /**
     * View/Preview document in browser
     *
     * @param string|null $id Document id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Response with document content or redirect
     */
    public function viewDocument($id = null)
    {
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

    /**
     * Proxy document content for preview (allows Office Online Viewer to access S3 documents)
     *
     * @param string|null $id Document id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Response with document content
     */
    public function proxyDocument($id = null)
    {
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

    /**
     * Assign case to scientist method
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects on successful assignment.
     */
    public function assign($id = null)
    {
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

            // Check if case can be assigned
            if (!in_array($case->status, [SiteConstants::CASE_STATUS_DRAFT, SiteConstants::CASE_STATUS_ASSIGNED])) {
                $this->Flash->error(__('This case cannot be assigned in its current status.'));
                return $this->redirect(['action' => 'view', $id]);
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
                    // Update case status
                    $oldStatus = $case->status;
                    $case->status = SiteConstants::CASE_STATUS_ASSIGNED;
                    $case->current_user_id = $data['assigned_to'];
                    
                    if ($this->Cases->save($case)) {
                        // Log audit trail
                        $caseAuditsTable = $this->fetchTable('CaseAudits');
                        $caseAuditsTable->logChange(
                            $case->id,
                            $case->current_version_id,
                            'status',
                            $oldStatus,
                            SiteConstants::CASE_STATUS_ASSIGNED,
                            $user->id
                        );
                        
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

    /**
     * Helper method to detect changed fields
     *
     * @param \App\Model\Entity\MedicalCase $original
     * @param \App\Model\Entity\MedicalCase $updated
     * @return array
     */
    private function getChangedFields($original, $updated): array
    {
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

    /**
     * Analyze document using AI/OCR to detect type and suggest procedure
     *
     * @param string|null $id Case id.
     * @return \Psr\Http\Message\ResponseInterface JSON response
     */
    public function analyzeDocument($id = null)
    {
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

    /**
     * AJAX endpoint to save Step 1 data and get AI recommendations
     */
    public function saveStep1()
    {
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

        // Save to session
        $this->request->getSession()->write('CaseWizard.step1', [
            'patient_id' => $data['patient_id'],
            'date' => $data['date'],
            'symptoms' => $data['symptoms']
        ]);

        // Get AI recommendations if enabled
        $aiRecommendations = [];
        $recommendationService = new \App\Service\CaseRecommendationService();
        
        if ($recommendationService->isEnabled()) {
            // Get patient data
            $patientsTable = $this->fetchTable('Users');
            $patient = $patientsTable->get($data['patient_id']);
            
            // Calculate age from date of birth if available
            $age = 'unknown';
            if (!empty($patient->date_of_birth)) {
                $dob = new \DateTime($patient->date_of_birth);
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

    /**
     * AJAX endpoint to save Step 2 data
     */
    public function saveStep2()
    {
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

    /**
     * AJAX endpoint to save Step 3 data and create the case
     */
    public function saveStep3()
    {
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
        $case = $this->Cases->newEntity([
            'user_id' => $user->id,
            'hospital_id' => $currentHospital->id,
            'patient_id' => $step1Data['patient_id'],
            'current_user_id' => $user->id,
            'date' => $step1Data['date'],
            'department_id' => $step2Data['department_id'],
            'sedation_id' => $step2Data['sedation_id'],
            'priority' => $step2Data['priority'],
            'status' => SiteConstants::CASE_STATUS_DRAFT,
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

    /**
     * Download Case Report as PDF
     *
     * @param int|null $id Case id
     * @return \Cake\Http\Response|null
     */
    public function downloadReport($id = null)
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');

        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found.'));
            return $this->redirect(['action' => 'index']);
        }

        try {
            $case = $this->Cases->get($id, [
                'contain' => [
                    'PatientUsers' => ['Patients'],
                    'Users',
                    'Hospitals',
                    'Departments',
                    'Sedations',
                    'CurrentUsers',
                    'CurrentVersions' => ['Users'],
                    'CasesExamsProcedures' => [
                        'ExamsProcedures' => ['Exams', 'Procedures'],
                        'Documents' // Include documents linked to procedures
                    ],
                    'CaseVersions' => ['Users'],
                    'CaseAssignments' => ['Users', 'AssignedToUsers'],
                    'Documents' // Include general case documents
                ]
            ]);

            // Verify hospital access
            if ($case->hospital_id !== $currentHospital->id) {
                throw new \Cake\Datasource\Exception\RecordNotFoundException(__('Case not found.'));
            }

        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Flash->error(__('Case not found.'));
            return $this->redirect(['action' => 'index']);
        }

        // Generate PDF report
        try {
            // Increase PCRE backtrack limit for large HTML content
            ini_set('pcre.backtrack_limit', '5000000');
            
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 15,
                'margin_bottom' => 15,
                'margin_header' => 10,
                'margin_footer' => 10
            ]);

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
            return $this->redirect(['action' => 'view', $id]);
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
    private function _prepareReportWithAI($case, $hospital, $user): array
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
            $documentContents = [];

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
                                
                                $documentContents[$document->id] = [
                                    'content' => $content,
                                    'procedure_id' => $cep->id,
                                    'document' => $document
                                ];
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
    private function _prepareAiMetadata($case, array $documentContents): array
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
        $procedureTypes = [];
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

        return [
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
        ];
    }

    /**
     * Categorize symptoms into generic medical categories
     *
     * @param string $symptomsText Patient symptoms text
     * @return array Generic symptom categories
     */
    private function _categorizeSymptoms(string $symptomsText): array
    {
        $categories = [];
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

        return empty($categories) ? ['general_evaluation'] : $categories;
    }

    /**
     * Prepare data for case report PDF
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @param object $hospital The hospital object
     * @param \App\Model\Entity\User $user The current user
     * @return array View variables
     */
    private function _prepareReportData($case, $hospital, $user): array
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
        $proceduresList = [];
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

        return [
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
        ];
    }

    /**
     * Generate technical descriptions based on procedures
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @return array Technical description HTML blocks
     */
    private function _generateTechnicalDescriptions($case): array
    {
        $descriptions = [];
        
        if (empty($case->cases_exams_procedures)) {
            return $descriptions;
        }

        $procedureTypes = [];
        foreach ($case->cases_exams_procedures as $cep) {
            if (isset($cep->exams_procedure->procedure)) {
                $procedureName = strtolower($cep->exams_procedure->procedure->name);
                $procedureTypes[] = $procedureName;
            }
        }

        // Resting State MEG
        if ($this->_hasProcedureType($procedureTypes, ['resting', 'rest', 'baseline'])) {
            $descriptions[] = '<div class="section-title">Resting State MEG:</div>
                <p>Spontaneous brain activity was recorded during eyes-closed resting conditions. 
                The patient was instructed to remain still and relaxed while avoiding sleep. 
                The recording duration was approximately 5-10 minutes. Data will be analyzed for 
                identification of interictal epileptiform activity, characterization of background 
                rhythms, and assessment of functional connectivity patterns.</p>';
        }

        // Sensory Mapping
        if ($this->_hasProcedureType($procedureTypes, ['sensory', 'somatosensory', 'tactile'])) {
            $descriptions[] = '<div class="section-title">Somatosensory Mapping:</div>
                <p>Somatosensory evoked fields were recorded in response to pneumatic tactile 
                stimulation of the digits. Stimuli were delivered to multiple locations to map 
                the primary somatosensory cortex. The source distributions were analyzed utilizing 
                an equivalent current dipole model to localize the peak responses in relation to 
                the individual patient\'s cortical anatomy as defined by MRI.</p>';
        }

        // Motor Mapping
        if ($this->_hasProcedureType($procedureTypes, ['motor', 'movement'])) {
            $descriptions[] = '<div class="section-title">Motor Mapping:</div>
                <p>Motor evoked fields were recorded during voluntary finger movements. 
                The patient performed self-paced movements of individual digits while MEG data 
                was continuously recorded. Movement-related cortical fields were analyzed to 
                identify the primary motor cortex using equivalent current dipole modeling.</p>';
        }

        // Auditory Mapping
        if ($this->_hasProcedureType($procedureTypes, ['auditory', 'hearing', 'sound'])) {
            $descriptions[] = '<div class="section-title">Auditory Mapping:</div>
                <p>Auditory evoked fields were elicited using tone bursts delivered binaurally 
                through non-magnetic insert earphones. Multiple frequencies were tested to map 
                tonotopic organization of the auditory cortex. Source localization was performed 
                using equivalent current dipole analysis.</p>';
        }

        // Visual Mapping
        if ($this->_hasProcedureType($procedureTypes, ['visual', 'vision', 'sight'])) {
            $descriptions[] = '<div class="section-title">Visual Mapping:</div>
                <p>Visual evoked fields were recorded in response to pattern-reversal checkerboard 
                stimuli presented to different regions of the visual field. Source analysis was 
                performed to localize primary visual cortex and assess retinotopic organization.</p>';
        }

        // Language Mapping
        if ($this->_hasProcedureType($procedureTypes, ['language', 'speech', 'verbal', 'naming'])) {
            $descriptions[] = '<div class="section-title">Language Mapping:</div>
                <p>Language-related cortical activation was assessed using receptive and/or 
                expressive language tasks. Tasks may have included picture naming, verb generation, 
                or auditory word comprehension. Source localization was performed to identify 
                language-dominant hemisphere and map critical language areas in relation to 
                structural lesions.</p>';
        }

        // Memory Mapping
        if ($this->_hasProcedureType($procedureTypes, ['memory', 'recall', 'recognition'])) {
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
    private function _generateMsiConclusions($case): array
    {
        $conclusions = [];
        
        if (empty($case->cases_exams_procedures)) {
            return $conclusions;
        }

        $procedureTypes = [];
        foreach ($case->cases_exams_procedures as $cep) {
            if (isset($cep->exams_procedure->procedure)) {
                $procedureName = strtolower($cep->exams_procedure->procedure->name);
                $procedureTypes[] = $procedureName;
            }
        }

        // Generic conclusion based on procedure types
        $hasMapping = $this->_hasProcedureType($procedureTypes, 
            ['sensory', 'motor', 'auditory', 'visual', 'language', 'memory']);
        $hasResting = $this->_hasProcedureType($procedureTypes, 
            ['resting', 'rest', 'baseline']);

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
    private function _hasProcedureType(array $procedureTypes, array $keywords): bool
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
     * Get user-friendly upload error message
     *
     * @param int $errorCode PHP upload error code
     * @return string User-friendly error message
     */
    private function getUploadErrorMessage(int $errorCode): string
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
     * Get authenticated user
     *
     * @return \App\Model\Entity\User
     */
    private function getAuthUser()
    {
        $identity = $this->request->getAttribute('identity');
        return $identity->getOriginalData();
    }
}
