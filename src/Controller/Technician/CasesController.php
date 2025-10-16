<?php
declare(strict_types=1);

namespace App\Controller\Technician;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use App\Lib\UserActivityLogger;
use App\Constants\SiteConstants;

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
            $query->where([
                'OR' => [
                    'Cases.id LIKE' => '%' . $search . '%',
                    'Users.first_name LIKE' => '%' . $search . '%',
                    'Users.last_name LIKE' => '%' . $search . '%',
                ]
            ]);
        }

        // Filter by technician's own cases or all cases based on permission
        $viewAll = $this->request->getQuery('view') === 'all';
        if (!$viewAll) {
            $query->where(['Cases.user_id' => $user->id]);
        }

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
                'event_data' => ['hospital_id' => $currentHospital->id, 'filters' => compact('status', 'priority', 'search', 'viewAll')]
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
            'viewAll',
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
                    'CaseAssignments' => ['Users', 'AssignedToUsers'],
                    'CaseAudits' => ['ChangedByUsers'],
                    'Documents' => ['Users', 'CasesExamsProcedures'],
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

        $this->set(compact('case', 'currentHospital'));
    }

    /**
     * Add method
     *
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['action' => 'index']);
        }

        $case = $this->Cases->newEmptyEntity();
        
        // Check if patient_id is provided in query string (from patient view/edit pages)
        $patientId = $this->request->getQuery('patient_id');
        if ($patientId) {
            // Verify the user is a patient and belongs to the current hospital
            $usersTable = $this->fetchTable('Users');
            $patient = $usersTable->find()
                ->innerJoinWith('Roles')
                ->where([
                    'Users.id' => $patientId,
                    'Users.hospital_id' => $currentHospital->id,
                    'Roles.type' => 'patient'
                ])
                ->first();
                
            if ($patient) {
                $case->patient_id = $patientId;
            }
        }
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Set automatic fields
            $data['user_id'] = $user->id;
            $data['hospital_id'] = $currentHospital->id;
            $data['current_user_id'] = $user->id;
            $data['status'] = SiteConstants::CASE_STATUS_DRAFT;
            
            $case = $this->Cases->patchEntity($case, $data);
            
            if ($this->Cases->save($case)) {
                // Handle exam procedures selection
                if (!empty($data['exam_procedures'])) {
                    $casesExamsProceduresTable = $this->fetchTable('CasesExamsProcedures');
                    
                    foreach ($data['exam_procedures'] as $examProcedureId => $selected) {
                        if ($selected) {
                            $casesExamsProcedure = $casesExamsProceduresTable->newEntity([
                                'case_id' => $case->id,
                                'exams_procedure_id' => $examProcedureId,
                                'status' => SiteConstants::CASE_STATUS_PENDING,
                                'scheduled_at' => null,
                                'notes' => ''
                            ]);
                            
                            if (!$casesExamsProceduresTable->save($casesExamsProcedure)) {
                                // Log validation errors
                                $this->log('Failed to save case exam procedure with validation errors', 'error');
                            }
                        }
                    }
                }

                // Log activity
                $this->activityLogger->log(
                    SiteConstants::EVENT_CASE_CREATED,
                    [
                        'user_id' => $user->id,
                        'request' => $this->request,
                        'event_data' => ['case_id' => $case->id, 'hospital_id' => $currentHospital->id]
                    ]
                );

                $this->Flash->success(__('The case has been created successfully.'));
                return $this->redirect(['action' => 'view', $case->id]);
            }
            
            // Log validation errors
            $errors = $case->getErrors();
            $this->log('Case validation failed', 'error');
            
            $this->Flash->error(__('The case could not be saved. Please, try again.'));
            
            // Pass validation errors to the view for debugging
            $this->set('validationErrors', $errors);
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

        $this->set(compact('case', 'patients', 'priorities', 'departments', 'sedations', 'modalities', 'examsProcedures', 'currentHospital'));
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
        
        if (empty($data['document_file']) || !isset($data['document_file']['tmp_name'])) {
            $this->Flash->error(__('Please select a file to upload.'));
            return $this->redirect(['action' => 'view', $id]);
        }

        // Load S3 service
        $s3Service = new \App\Lib\S3DocumentService();
        
        // Upload to S3
        $uploadResult = $s3Service->uploadDocument(
            $data['document_file'],
            $case->id,
            $case->patient_id,
            $data['document_type'] ?? 'other',
            $data['cases_exams_procedure_id'] ?? null
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
            $case = $this->Cases->get($id);
            
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
                $assignment = $caseAssignmentsTable->newEntity([
                    'case_id' => $case->id,
                    'case_version_id' => $case->current_version_id,
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