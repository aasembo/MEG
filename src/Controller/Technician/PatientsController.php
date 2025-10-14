<?php
declare(strict_types=1);

namespace App\Controller\Technician;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use App\Lib\UserActivityLogger;
use App\Constants\SiteConstants;
use Cake\Core\Configure;

/**
 * Patients Controller
 * Handles patient management for technicians
 *
 * @property \App\Model\Table\UsersTable $Users
 * @property \App\Model\Table\RolesTable $Roles
 */
class PatientsController extends AppController
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
     * Index method - List all patients
     *
     * @return \Psr\Http\Message\ResponseInterface|null|void Renders view
     */
    public function index()
    {
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
        }

        $patientsTable = $this->fetchTable('Patients');
        
        $query = $patientsTable->find()
            ->contain(['Users', 'Hospitals'])
            ->where(['Patients.hospital_id' => $currentHospital->id])
            ->order(['Users.last_name' => 'ASC', 'Users.first_name' => 'ASC']);
        
        // Search functionality
        if ($this->request->getQuery('search')) {
            $search = trim($this->request->getQuery('search'));
            $query->where([
                'OR' => [
                    'Users.first_name LIKE' => '%' . $search . '%',
                    'Users.last_name LIKE' => '%' . $search . '%',
                    'Users.email LIKE' => '%' . $search . '%',
                    'Users.username LIKE' => '%' . $search . '%',
                    'Patients.phone LIKE' => '%' . $search . '%',
                    'Patients.medical_record_number LIKE' => '%' . $search . '%',
                    'Patients.financial_record_number LIKE' => '%' . $search . '%'
                ]
            ]);
        }

        // Status filter
        if ($this->request->getQuery('status')) {
            $query->where(['Users.status' => $this->request->getQuery('status')]);
        }

        $patients = $this->paginate($query);
        
        // Variables for template
        $hospitalName = $currentHospital->name ?? 'Unknown Hospital';
        $search = $this->request->getQuery('search', '');
        $status = $this->request->getQuery('status', '');
        $statusOptions = [
            'all' => 'All Status',
            SiteConstants::USER_STATUS_ACTIVE => 'Active',
            SiteConstants::USER_STATUS_INACTIVE => 'Inactive',
            SiteConstants::USER_STATUS_PENDING => 'Pending'
        ];        // Log activity
        $this->activityLogger->log(
            SiteConstants::EVENT_PATIENTS_VIEWED,
            [
                'user_id' => $user->id,
                'request' => $this->request,
                'event_data' => ['hospital_id' => $currentHospital->id]
            ]
        );

        $this->set(compact('patients', 'hospitalName', 'search', 'status', 'statusOptions'));
    }

    /**
     * Test flash messages - demonstration method
     */
    public function testFlash()
    {
        if ($this->request->is('post')) {
            $type = $this->request->getData('type', 'success');
            $message = $this->request->getData('message', 'This is a test flash message!');
            
            switch ($type) {
                case 'success':
                    $this->Flash->success($message);
                    break;
                case 'error':
                    $this->Flash->error($message);
                    break;
                case 'warning':
                    $this->Flash->warning($message);
                    break;
                case 'info':
                    $this->Flash->info($message);
                    break;
                default:
                    $this->Flash->set($message);
            }
            
            return $this->redirect(['action' => 'testFlash']);
        }
    }

    /**
     * View method - Display patient details
     *
     * @param string|null $id Patient id.
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
            $patientsTable = $this->fetchTable('Patients');
            $patient = $patientsTable->find()
                ->contain(['Users', 'Hospitals', 'Cases'])
                ->where([
                    'Patients.id' => $id,
                    'Patients.hospital_id' => $currentHospital->id
                ])
                ->first();

            if (!$patient) {
                throw new RecordNotFoundException(__('Patient not found.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Patient not found.'));
            return $this->redirect(['action' => 'index']);
        }
        
        // Log activity
        $this->activityLogger->log(
            SiteConstants::EVENT_PATIENT_VIEWED,
            [
                'user_id' => $user->id,
                'request' => $this->request,
                'event_data' => ['patient_id' => $patient->id, 'user_id' => $patient->user->id, 'hospital_id' => $currentHospital->id]
            ]
        );

        // Get cases count and recent cases for the patient
        $casesTable = $this->fetchTable('Cases');
        $casesCount = $casesTable->find()
            ->where(['Cases.patient_id' => $patient->id])
            ->count();
            
        $recentCases = $casesTable->find()
            ->contain(['Users', 'Hospitals'])
            ->where(['Cases.patient_id' => $patient->id])
            ->orderBy(['Cases.created' => 'DESC'])
            ->limit(5)
            ->toArray();

        $this->set(compact('patient', 'currentHospital', 'casesCount', 'recentCases'));
    }    /**
     * Add method - Create a new patient
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

        $usersTable = $this->fetchTable('Users');
        $patientsTable = $this->fetchTable('Patients');
        
        $newUser = $usersTable->newEmptyEntity();
        $patient = $patientsTable->newEmptyEntity();
        
        // Set up the patient entity with user association for form context
        $patient->user = $newUser;
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Get patient role ID
            $rolesTable = $this->fetchTable('Roles');
            $patientRole = $rolesTable->find()->where(['type' => SiteConstants::ROLE_TYPE_PATIENT])->first();
            
            if (!$patientRole) {
                $this->Flash->error(__('Patient role not found in system. Please contact administrator.'));
                return $this->redirect(['action' => 'index']);
            }
            
            // Prepare user data
            $userData = [
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'email' => $data['email'] ?? '',
                'username' => $data['username'] ?? '',
                'hospital_id' => $currentHospital->id,
                'role_id' => $patientRole->id,
                'status' => $data['status'] ?? SiteConstants::USER_STATUS_ACTIVE,
                'password' => null // Patients cannot login
            ];
            
            // Generate username if not provided
            if (empty($userData['username'])) {
                $userData['username'] = $this->generatePatientUsername(
                    $userData['first_name'], 
                    $userData['last_name']
                );
            }
            
            // Prepare patient data
            $patientData = [
                'gender' => $data['gender'] ?? '',
                'dob' => $data['dob'] ?? null,
                'phone' => $data['phone'] ?? '',
                'address' => $data['address'] ?? '',
                'notes' => $data['notes'] ?? '',
                'emergency_contact_name' => $data['emergency_contact_name'] ?? '',
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? '',
                'medical_record_number' => $data['medical_record_number'] ?? '',
                'financial_record_number' => $data['financial_record_number'] ?? '',
                'hospital_id' => $currentHospital->id
            ];
            
            // Use database transaction to ensure both records are created successfully
            $connection = $usersTable->getConnection();
            
            // Validate both entities first WITHOUT saving
            $newUser = $usersTable->patchEntity($newUser, $userData);
            $patient = $patientsTable->patchEntity($patient, $patientData);
            
            // Check validation for both entities
            $userErrors = $newUser->getErrors();
            $patientErrors = $patient->getErrors();
            
            if (!empty($userErrors) || !empty($patientErrors)) {
                // Transfer validation errors for form display
                foreach ($patientErrors as $field => $errors) {
                    $patient->setError($field, $errors);
                }
                foreach ($userErrors as $field => $errors) {
                    $patient->setError($field, $errors);
                }
                
                $this->Flash->error(__('Please correct the validation errors below and try again.'));
            } else {
                // Both entities are valid, now save them in a transaction
                $connection->begin();
                
                try {
                    // Save user record first
                    if (!$usersTable->save($newUser)) {
                        throw new \Exception('Failed to save user record');
                    }
                    
                    // Add user_id to patient and save
                    $patient->user_id = $newUser->id;
                    if (!$patientsTable->save($patient)) {
                        throw new \Exception('Failed to save patient record');
                    }
                    
                    // Commit transaction
                    $connection->commit();
                    
                    // Log activity
                    $this->activityLogger->log(
                        SiteConstants::EVENT_PATIENT_CREATED,
                        [
                            'user_id' => $user->id,
                            'request' => $this->request,
                            'event_data' => ['patient_id' => $patient->id, 'user_id' => $newUser->id, 'hospital_id' => $currentHospital->id]
                        ]
                    );

                    $this->Flash->success(__('The patient has been created successfully.'));
                    return $this->redirect(['action' => 'view', $patient->id]);
                    
                } catch (\Exception $e) {
                    // Rollback transaction on any error
                    $connection->rollback();
                    
                    // Log the specific error for debugging
                    error_log('Patient creation error: ' . $e->getMessage());
                    error_log('Stack trace: ' . $e->getTraceAsString());
                    
                    // Show specific error in development, generic in production
                    if (Configure::read('debug')) {
                        $this->Flash->error(__('Error creating patient: {0}', $e->getMessage()));
                    } else {
                        $this->Flash->error(__('An unexpected error occurred while saving. Please try again.'));
                    }
                }
            }
        }

        $this->set(compact('patient', 'currentHospital'));
    }

    /**
     * Edit method - Edit patient details
     *
     * @param string|null $id Patient id.
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
            $patientsTable = $this->fetchTable('Patients');
            $patient = $patientsTable->find()
                ->contain(['Users', 'Hospitals'])
                ->where([
                    'Patients.id' => $id,
                    'Patients.hospital_id' => $currentHospital->id
                ])
                ->first();

            if (!$patient) {
                throw new RecordNotFoundException(__('Patient not found.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Patient not found.'));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Prepare user data
            $userData = [
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'email' => $data['email'] ?? '',
                'username' => $data['username'] ?? '',
                'status' => $data['status'] ?? SiteConstants::USER_STATUS_ACTIVE
            ];
            
            // Prepare patient data
            $patientData = [
                'gender' => $data['gender'] ?? '',
                'dob' => $data['dob'] ?? null,
                'phone' => $data['phone'] ?? '',
                'address' => $data['address'] ?? '',
                'notes' => $data['notes'] ?? '',
                'emergency_contact_name' => $data['emergency_contact_name'] ?? '',
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? '',
                'medical_record_number' => $data['medical_record_number'] ?? '',
                'financial_record_number' => $data['financial_record_number'] ?? ''
            ];
            
            // Use database transaction to ensure both records are updated successfully
            $usersTable = $this->fetchTable('Users');
            $connection = $usersTable->getConnection();
            
            // Validate both entities separately
            $userEntity = $usersTable->patchEntity($patient->user, $userData);
            $patientEntity = $patientsTable->patchEntity($patient, $patientData);
            
            // Check validation for both entities
            $userErrors = $userEntity->getErrors();
            $patientErrors = $patientEntity->getErrors();
            
            if (!empty($userErrors) || !empty($patientErrors)) {
                // Transfer validation errors for form display
                foreach ($userErrors as $field => $errors) {
                    $patient->setError($field, $errors);
                }
                foreach ($patientErrors as $field => $errors) {
                    $patient->setError($field, $errors);
                }
                
                $this->Flash->error(__('Please correct the validation errors below and try again.'));
            } else {
                // Both entities are valid, now save them in a transaction
                $connection->begin();
                
                try {
                    // Save user record first
                    if (!$usersTable->save($userEntity)) {
                        throw new \Exception('Failed to update user record');
                    }
                    
                    // Save patient record
                    if (!$patientsTable->save($patientEntity)) {
                        throw new \Exception('Failed to update patient record');
                    }
                    
                    // Commit transaction
                    $connection->commit();
                    
                    // Update references for successful save
                    $patient->user = $userEntity;
                    $patient = $patientEntity;
                    
                    // Log activity
                    $this->activityLogger->log(
                        SiteConstants::EVENT_PATIENT_UPDATED,
                        [
                            'user_id' => $user->id,
                            'request' => $this->request,
                            'event_data' => ['patient_id' => $patient->id, 'user_id' => $patient->user->id, 'hospital_id' => $currentHospital->id]
                        ]
                    );

                    $this->Flash->success(__('The patient has been updated successfully.'));
                    return $this->redirect(['action' => 'view', $patient->id]);
                    
                } catch (\Exception $e) {
                    // Rollback transaction on any error
                    $connection->rollback();
                    $this->Flash->error(__('An unexpected error occurred while saving. Please try again.'));
                }
            }
        }

        $statusOptions = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'suspended' => 'Suspended'
        ];

        $this->set(compact('patient', 'statusOptions', 'currentHospital'));
    }

    /**
     * Delete method - Deactivate patient (soft delete)
     *
     * @param string|null $id Patient id.
     * @return \Psr\Http\Message\ResponseInterface|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        
        $user = $this->getAuthUser();
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            return $this->redirect(['action' => 'index']);
        }

        try {
            $patientsTable = $this->fetchTable('Patients');
            $patient = $patientsTable->find()
                ->contain(['Users'])
                ->where([
                    'Patients.id' => $id,
                    'Patients.hospital_id' => $currentHospital->id
                ])
                ->first();

            if (!$patient) {
                throw new RecordNotFoundException(__('Patient not found.'));
            }

            // Check if patient has active cases
            $casesTable = $this->fetchTable('Cases');
            $activeCasesCount = $casesTable->find()
                ->where([
                    'patient_id' => $patient->id,
                    'status NOT IN' => [SiteConstants::CASE_STATUS_COMPLETED, SiteConstants::CASE_STATUS_CANCELLED]
                ])
                ->count();

            if ($activeCasesCount > 0) {
                $this->Flash->error(__('Cannot deactivate patient with active cases. Please complete or cancel all cases first.'));
                return $this->redirect(['action' => 'view', $id]);
            }

            // Soft delete - set user status to inactive
            $usersTable = $this->fetchTable('Users');
            $patient->user->status = SiteConstants::USER_STATUS_INACTIVE;
            
            if ($usersTable->save($patient->user)) {
                // Log activity
                $this->activityLogger->log(
                    SiteConstants::EVENT_PATIENT_DEACTIVATED,
                    [
                        'user_id' => $user->id,
                        'request' => $this->request,
                        'event_data' => ['patient_id' => $patient->id, 'user_id' => $patient->user_id, 'hospital_id' => $currentHospital->id]
                    ]
                );

                $this->Flash->success(__('The patient has been deactivated successfully.'));
            } else {
                $this->Flash->error(__('The patient could not be deactivated. Please, try again.'));
            }

        } catch (RecordNotFoundException $e) {
            $this->Flash->error(__('Patient not found.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Generate unique username for patient
     *
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    private function generatePatientUsername($firstName, $lastName): string
    {
        $usersTable = $this->fetchTable('Users');
        $baseUsername = strtolower(substr($firstName, 0, 1) . $lastName);
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername);
        
        $username = $baseUsername;
        $counter = 1;
        
        while ($usersTable->exists(['username' => $username])) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        return $username;
    }

    /**
     * Check username uniqueness via AJAX
     *
     * @return \Psr\Http\Message\ResponseInterface JSON response
     */
    public function checkUsername()
    {
        $this->request->allowMethod(['post']);
        
        if (!$this->request->is('ajax')) {
            throw new \Cake\Http\Exception\BadRequestException('This endpoint only accepts AJAX requests');
        }

        // Get data from request (form data or JSON)
        $data = $this->request->getData();
        $username = $data['username'] ?? '';
        
        // Also try JSON input as fallback
        if (empty($username)) {
            $jsonInput = json_decode($this->request->getBody()->getContents(), true);
            if ($jsonInput && isset($jsonInput['username'])) {
                $username = $jsonInput['username'];
            }
        }

        if (empty($username)) {
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'available' => false,
                    'message' => 'Username cannot be empty'
                ]));
        }

        // Check if username exists in users table
        $usersTable = $this->fetchTable('Users');
        $existingUser = $usersTable->find()
            ->where(['username' => $username])
            ->first();

        if ($existingUser) {
            // Username exists, generate suggestions
            $suggestion = $this->generateUsernameVariation($username);
            
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'available' => false,
                    'message' => 'Username already exists',
                    'suggestion' => $suggestion
                ]));
        }

        // Username is available
        return $this->response
            ->withType('application/json')
            ->withStringBody(json_encode([
                'available' => true,
                'message' => 'Username is available'
            ]));
    }

    /**
     * Generate username variation if original is taken
     *
     * @param string $baseUsername Base username
     * @return string Modified username
     */
    private function generateUsernameVariation($baseUsername)
    {
        $usersTable = $this->fetchTable('Users');
        $counter = 1;
        
        do {
            $variation = $baseUsername . $counter;
            $exists = $usersTable->find()
                ->where(['username' => $variation])
                ->count() > 0;
            $counter++;
        } while ($exists && $counter <= 999);
        
        return $variation;
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