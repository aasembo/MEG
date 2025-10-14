<?php
declare(strict_types=1);

namespace App\Controller\System;

use App\Controller\System\SystemController;
use Cake\Http\Exception\NotFoundException;
use App\Constants\SiteConstants;

/**
 * Admin Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends SystemController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Users->find()->contain(['Roles', 'Hospitals']);
        
        // Handle search filter
        if ($this->request->getQuery('search')) {
            $search = $this->request->getQuery('search');
            $query->where([
                'OR' => [
                    'Users.username LIKE' => '%' . $search . '%',
                    'Users.email LIKE' => '%' . $search . '%',
                    'Users.first_name LIKE' => '%' . $search . '%',
                    'Users.last_name LIKE' => '%' . $search . '%'
                ]
            ]);
        }
        
        // Handle status filter
        if ($this->request->getQuery('status')) {
            $status = $this->request->getQuery('status');
            $query->where(['Users.status' => $status]);
        }
        
        // Handle role filter
        if ($this->request->getQuery('role_id')) {
            $roleId = $this->request->getQuery('role_id');
            $query->where(['Users.role_id' => $roleId]);
        }
        
        // Handle hospital filter
        if ($this->request->getQuery('hospital_id')) {
            $hospitalId = $this->request->getQuery('hospital_id');
            if ($hospitalId === '0') {
                // Super users (no hospital)
                $query->where(['Users.hospital_id IS' => null]);
            } else {
                $query->where(['Users.hospital_id' => $hospitalId]);
            }
        }
        
        // Order by creation date (newest first)
        $query->order(['Users.created' => 'DESC']);
        
        // Paginate results
        $users = $this->paginate($query, [
            'limit' => 15
        ]);
        
        // Get filter counts for badges
        $totalCount = $this->Users->find()->count();
        $activeCount = $this->Users->find()->where(['status' => SiteConstants::USER_STATUS_ACTIVE])->count();
        $inactiveCount = $this->Users->find()->where(['status' => SiteConstants::USER_STATUS_INACTIVE])->count();
        
        // Get role counts
        $roleCounts = $this->Users->find()
            ->select([
                'role_type' => 'Roles.type',
                'role_name' => 'Roles.name',
                'count' => $this->Users->find()->func()->count('Users.id')
            ])
            ->contain(['Roles'])
            ->group(['Roles.type', 'Roles.name'])
            ->toArray();
        
        // Get options for filters
        $roles = $this->Users->Roles->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ])->toArray();
        
        $hospitals = $this->Users->Hospitals->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ])->where(['status' => SiteConstants::USER_STATUS_ACTIVE])->toArray();
        
        $this->set(compact('users', 'totalCount', 'activeCount', 'inactiveCount', 'roleCounts', 'roles', 'hospitals'));
    }

    /**
     * View method - Display a single user
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => ['Roles', 'Hospitals'],
        ]);

        $this->set(compact('user'));
    }

    /**
     * Add method - Create a new user
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Set hospital_id to 0 for super users
            if (isset($data['role_id'])) {
                $role = $this->Users->Roles->get($data['role_id']);
                if ($role->type === 'super') {
                    $data['hospital_id'] = 0;
                }
            }
            
            $user = $this->Users->patchEntity($user, $data);
            
            if ($this->Users->save($user)) {
                // Reload user with role relationship for specialized record creation
                $user = $this->Users->get($user->id, ['contain' => ['Roles']]);
                
                // Create corresponding record in specialized table
                $this->createSpecializedRecord($user, $data);
                
                $this->Flash->success(__('The user has been created successfully.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        
        $roles = $this->Users->Roles->find('list', ['limit' => 200])->all();
        $roleTypes = $this->Users->Roles->find('list', [
            'keyField' => 'id',
            'valueField' => 'type',
            'limit' => 200
        ])->toArray();
        $hospitals = $this->Users->Hospitals->find('list', ['conditions' => ['status' => SiteConstants::HOSPITAL_STATUS_ACTIVE], 'limit' => 200])->all();
        $this->set(compact('user', 'roles', 'roleTypes', 'hospitals'));
    }

    /**
     * Edit method - Update an existing user
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => ['Roles', 'Hospitals'],
        ]);
        
        // Store original role for comparison
        $originalRoleId = $user->role_id;
        
        // Load existing specialized record data for form population
        $specializedData = $this->getSpecializedRecordData($user);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Set hospital_id to 0 for super users
            if (isset($data['role_id'])) {
                $role = $this->Users->Roles->get($data['role_id']);
                if ($role->type === 'super') {
                    $data['hospital_id'] = 0;
                }
            }
            
            $user = $this->Users->patchEntity($user, $data);
            
            if ($this->Users->save($user)) {
                // Reload user with role relationship for specialized record operations
                $user = $this->Users->get($user->id, ['contain' => ['Roles']]);
                
                // If role changed, handle specialized record creation/deletion
                if ($originalRoleId != $user->role_id) {
                    $this->handleRoleChange($user, $originalRoleId, $data);
                } else {
                    // Role didn't change, just update existing specialized record if it exists
                    $this->updateSpecializedRecord($user, $data);
                }
                
                $this->Flash->success(__('The user has been updated successfully.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        
        // Merge specialized data with user data for form population
        if ($specializedData) {
            foreach ($specializedData as $key => $value) {
                $user->set($key, $value);
            }
        }
        
        $roles = $this->Users->Roles->find('list', ['limit' => 200])->all();
        $roleTypes = $this->Users->Roles->find('list', [
            'keyField' => 'id',
            'valueField' => 'type',
            'limit' => 200
        ])->toArray();
        $hospitals = $this->Users->Hospitals->find('list', ['conditions' => ['status' => SiteConstants::HOSPITAL_STATUS_ACTIVE], 'limit' => 200])->all();
        $this->set(compact('user', 'roles', 'roleTypes', 'hospitals'));
    }

    /**
     * Delete method - Remove a user
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id, ['contain' => ['Roles']]);
        
        // Prevent deletion of current user
        $currentUser = $this->request->getAttribute('identity');
        if ($currentUser && $currentUser->get('id') == $user->id) {
            $this->Flash->error(__('You cannot delete your own account.'));
            return $this->redirect(['action' => 'index']);
        }
        
        // Delete corresponding specialized record first
        if ($user->role) {
            $this->deleteSpecializedRecord($user->id, $user->role_id);
        }
        
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted successfully.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Toggle Status method - Activate/Deactivate a user
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function toggleStatus($id = null)
    {
        $this->request->allowMethod(['post', 'put']);
        $user = $this->Users->get($id);
        
        // Prevent status change of current user
        $currentUser = $this->request->getAttribute('identity');
        if ($currentUser && $currentUser->get('id') == $user->id) {
            $this->Flash->error(__('You cannot change your own account status.'));
            return $this->redirect(['action' => 'index']);
        }
        
        $user->status = ($user->status === SiteConstants::USER_STATUS_ACTIVE) ? SiteConstants::USER_STATUS_INACTIVE : SiteConstants::USER_STATUS_ACTIVE;
        
        if ($this->Users->save($user)) {
            $status = ($user->status === SiteConstants::USER_STATUS_ACTIVE) ? 'activated' : 'deactivated';
            $this->Flash->success(__('The user has been {0} successfully.', $status));
        } else {
            $this->Flash->error(__('The user status could not be changed. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Create a corresponding record in specialized table based on user role
     *
     * @param \App\Model\Entity\User $user The user entity
     * @param array $formData The form data containing specialized fields
     * @return void
     */
    private function createSpecializedRecord($user, $formData = [])
    {
        if (!$user->role) {
            return;
        }

        $roleType = $user->role->type;
        $specializedData = [
            'user_id' => $user->id,
            'hospital_id' => $user->hospital_id ?: 0,
        ];

        switch ($roleType) {
            case 'doctor':
                $doctorsTable = $this->fetchTable('Doctors');
                $doctor = $doctorsTable->newEmptyEntity();
                
                // Add phone from form data if provided
                if (!empty($formData['doctor_phone'])) {
                    $specializedData['phone'] = $formData['doctor_phone'];
                }
                
                $doctor = $doctorsTable->patchEntity($doctor, $specializedData);
                $doctorsTable->save($doctor);
                break;

            case 'nurse':
                $nursesTable = $this->fetchTable('Nurses');
                $nurse = $nursesTable->newEmptyEntity();
                $nurseData = array_merge($specializedData, [
                    'gender' => $formData['nurse_gender'] ?? 'F',
                    'dob' => $formData['nurse_dob'] ?? '1990-01-01',
                    'age' => $formData['nurse_age'] ?? 30,
                    'record_number' => $formData['nurse_record_number'] ?? time(),
                ]);
                
                // Add phone from form data if provided
                if (!empty($formData['nurse_phone'])) {
                    $nurseData['phone'] = $formData['nurse_phone'];
                }
                
                $nurse = $nursesTable->patchEntity($nurse, $nurseData);
                $nursesTable->save($nurse);
                break;

            case 'scientist':
                $scientistsTable = $this->fetchTable('Scientists');
                $scientist = $scientistsTable->newEmptyEntity();
                
                // Add phone from form data if provided
                if (!empty($formData['scientist_phone'])) {
                    $specializedData['phone'] = $formData['scientist_phone'];
                }
                
                $scientist = $scientistsTable->patchEntity($scientist, $specializedData);
                $scientistsTable->save($scientist);
                break;

            case 'patient':
                $patientsTable = $this->fetchTable('Patients');
                $patient = $patientsTable->newEmptyEntity();
                $patientData = array_merge($specializedData, [
                    'gender' => $formData['patient_gender'] ?? 'M',
                    'dob' => $formData['patient_dob'] ?? '1990-01-01',
                    'age' => $formData['patient_age'] ?? 30,
                ]);
                
                // Add optional fields from form data if provided
                if (!empty($formData['patient_medical_record_number'])) {
                    $patientData['medical_record_number'] = $formData['patient_medical_record_number'];
                }
                if (!empty($formData['patient_financial_record_number'])) {
                    $patientData['financial_record_number'] = $formData['patient_financial_record_number'];
                }
                
                $patient = $patientsTable->patchEntity($patient, $patientData);
                $patientsTable->save($patient);
                break;

            case 'technician':
                $techniciansTable = $this->fetchTable('Technicians');
                $technician = $techniciansTable->newEmptyEntity();
                
                // Add phone from form data if provided
                if (!empty($formData['technician_phone'])) {
                    $specializedData['phone'] = $formData['technician_phone'];
                }
                
                $technician = $techniciansTable->patchEntity($technician, $specializedData);
                $techniciansTable->save($technician);
                break;
        }
    }

    /**
     * Update existing specialized record
     *
     * @param \App\Model\Entity\User $user The user entity
     * @param array $formData The form data containing specialized fields
     * @return void
     */
    private function updateSpecializedRecord($user, $formData = [])
    {
        if (!$user->role) {
            return;
        }

        $roleType = $user->role->type;
        $updateData = [
            'hospital_id' => $user->hospital_id ?: 0,
        ];

        switch ($roleType) {
            case 'doctor':
                $doctorsTable = $this->fetchTable('Doctors');
                $doctor = $doctorsTable->find()->where(['user_id' => $user->id])->first();
                if ($doctor) {
                    if (!empty($formData['doctor_phone'])) {
                        $updateData['phone'] = $formData['doctor_phone'];
                    }
                    $doctor = $doctorsTable->patchEntity($doctor, $updateData);
                    $doctorsTable->save($doctor);
                }
                break;

            case 'nurse':
                $nursesTable = $this->fetchTable('Nurses');
                $nurse = $nursesTable->find()->where(['user_id' => $user->id])->first();
                if ($nurse) {
                    if (!empty($formData['nurse_phone'])) {
                        $updateData['phone'] = $formData['nurse_phone'];
                    }
                    if (!empty($formData['nurse_gender'])) {
                        $updateData['gender'] = $formData['nurse_gender'];
                    }
                    if (!empty($formData['nurse_dob'])) {
                        $updateData['dob'] = $formData['nurse_dob'];
                    }
                    if (!empty($formData['nurse_age'])) {
                        $updateData['age'] = $formData['nurse_age'];
                    }
                    if (!empty($formData['nurse_record_number'])) {
                        $updateData['record_number'] = $formData['nurse_record_number'];
                    }
                    $nurse = $nursesTable->patchEntity($nurse, $updateData);
                    $nursesTable->save($nurse);
                }
                break;

            case 'scientist':
                $scientistsTable = $this->fetchTable('Scientists');
                $scientist = $scientistsTable->find()->where(['user_id' => $user->id])->first();
                if ($scientist) {
                    if (!empty($formData['scientist_phone'])) {
                        $updateData['phone'] = $formData['scientist_phone'];
                    }
                    $scientist = $scientistsTable->patchEntity($scientist, $updateData);
                    $scientistsTable->save($scientist);
                }
                break;

            case 'patient':
                $patientsTable = $this->fetchTable('Patients');
                $patient = $patientsTable->find()->where(['user_id' => $user->id])->first();
                if ($patient) {
                    if (!empty($formData['patient_gender'])) {
                        $updateData['gender'] = $formData['patient_gender'];
                    }
                    if (!empty($formData['patient_dob'])) {
                        $updateData['dob'] = $formData['patient_dob'];
                    }
                    if (!empty($formData['patient_age'])) {
                        $updateData['age'] = $formData['patient_age'];
                    }
                    if (!empty($formData['patient_medical_record_number'])) {
                        $updateData['medical_record_number'] = $formData['patient_medical_record_number'];
                    }
                    if (!empty($formData['patient_financial_record_number'])) {
                        $updateData['financial_record_number'] = $formData['patient_financial_record_number'];
                    }
                    $patient = $patientsTable->patchEntity($patient, $updateData);
                    $patientsTable->save($patient);
                }
                break;

            case 'technician':
                $techniciansTable = $this->fetchTable('Technicians');
                $technician = $techniciansTable->find()->where(['user_id' => $user->id])->first();
                if ($technician) {
                    if (!empty($formData['technician_phone'])) {
                        $updateData['phone'] = $formData['technician_phone'];
                    }
                    $technician = $techniciansTable->patchEntity($technician, $updateData);
                    $techniciansTable->save($technician);
                }
                break;
        }
    }

    /**
     * Handle role change - remove old specialized record and create new one
     *
     * @param \App\Model\Entity\User $user The user entity
     * @param int $originalRoleId The original role ID
     * @param array $formData The form data containing specialized fields
     * @return void
     */
    private function handleRoleChange($user, $originalRoleId, $formData = [])
    {
        // First, delete the old specialized record
        $this->deleteSpecializedRecord($user->id, $originalRoleId);
        
        // Then, create the new specialized record
        $this->createSpecializedRecord($user, $formData);
    }

    /**
     * Get specialized record data for form population
     *
     * @param \App\Model\Entity\User $user The user entity
     * @return array|null The specialized record data or null if none exists
     */
    private function getSpecializedRecordData($user)
    {
        if (!$user->role) {
            return null;
        }

        $roleType = $user->role->type;
        $data = [];

        switch ($roleType) {
            case 'doctor':
                $doctorsTable = $this->fetchTable('Doctors');
                $doctor = $doctorsTable->find()->where(['user_id' => $user->id])->first();
                if ($doctor) {
                    $data['doctor_phone'] = $doctor->phone;
                }
                break;

            case 'nurse':
                $nursesTable = $this->fetchTable('Nurses');
                $nurse = $nursesTable->find()->where(['user_id' => $user->id])->first();
                if ($nurse) {
                    $data['nurse_phone'] = $nurse->phone;
                    $data['nurse_gender'] = $nurse->gender;
                    $data['nurse_dob'] = $nurse->dob ? $nurse->dob->format('Y-m-d') : '';
                    $data['nurse_age'] = $nurse->age;
                    $data['nurse_record_number'] = $nurse->record_number;
                }
                break;

            case 'scientist':
                $scientistsTable = $this->fetchTable('Scientists');
                $scientist = $scientistsTable->find()->where(['user_id' => $user->id])->first();
                if ($scientist) {
                    $data['scientist_phone'] = $scientist->phone;
                }
                break;

            case 'patient':
                $patientsTable = $this->fetchTable('Patients');
                $patient = $patientsTable->find()->where(['user_id' => $user->id])->first();
                if ($patient) {
                    $data['patient_gender'] = $patient->gender;
                    $data['patient_dob'] = $patient->dob ? $patient->dob->format('Y-m-d') : '';
                    $data['patient_age'] = $patient->age;
                    $data['patient_medical_record_number'] = $patient->medical_record_number;
                    $data['patient_financial_record_number'] = $patient->financial_record_number;
                }
                break;

            case 'technician':
                $techniciansTable = $this->fetchTable('Technicians');
                $technician = $techniciansTable->find()->where(['user_id' => $user->id])->first();
                if ($technician) {
                    $data['technician_phone'] = $technician->phone;
                }
                break;
        }

        return empty($data) ? null : $data;
    }

    /**
     * Delete specialized record based on user ID and role
     *
     * @param int $userId The user ID
     * @param int $roleId The role ID
     * @return void
     */
    private function deleteSpecializedRecord($userId, $roleId)
    {
        $role = $this->Users->Roles->get($roleId);
        $roleType = $role->type;

        switch ($roleType) {
            case 'doctor':
                $doctorsTable = $this->fetchTable('Doctors');
                $doctor = $doctorsTable->find()->where(['user_id' => $userId])->first();
                if ($doctor) {
                    $doctorsTable->delete($doctor);
                }
                break;

            case 'nurse':
                $nursesTable = $this->fetchTable('Nurses');
                $nurse = $nursesTable->find()->where(['user_id' => $userId])->first();
                if ($nurse) {
                    $nursesTable->delete($nurse);
                }
                break;

            case 'scientist':
                $scientistsTable = $this->fetchTable('Scientists');
                $scientist = $scientistsTable->find()->where(['user_id' => $userId])->first();
                if ($scientist) {
                    $scientistsTable->delete($scientist);
                }
                break;

            case 'patient':
                $patientsTable = $this->fetchTable('Patients');
                $patient = $patientsTable->find()->where(['user_id' => $userId])->first();
                if ($patient) {
                    $patientsTable->delete($patient);
                }
                break;

            case 'technician':
                $techniciansTable = $this->fetchTable('Technicians');
                $technician = $techniciansTable->find()->where(['user_id' => $userId])->first();
                if ($technician) {
                    $techniciansTable->delete($technician);
                }
                break;
        }
    }
}