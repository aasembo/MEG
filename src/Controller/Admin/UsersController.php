<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AdminController;
use Cake\Http\Exception\NotFoundException;
use App\Constants\SiteConstants;

class UsersController extends AdminController {
    public function index() {
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        if (!$currentHospital) {
            $this->Flash->error(__('Hospital context not found. Please try again.'));
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
        }

        // Base query - only users from current hospital
        $query = $this->Users->find()
            ->contain(['Roles'])
            ->where(['Users.hospital_id' => $currentHospital->id]);
        
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
        
        // Order by creation date (newest first)
        $query->order(['Users.created' => 'DESC']);
        
        // Paginate results
        $users = $this->paginate($query, [
            'limit' => 15,
        ]);
        
        // Get filter counts for badges (hospital-specific)
        $totalCount = $this->Users->find()->where(['hospital_id' => $currentHospital->id])->count();
        $activeCount = $this->Users->find()->where(['hospital_id' => $currentHospital->id, 'status' => SiteConstants::USER_STATUS_ACTIVE])->count();
        $inactiveCount = $this->Users->find()->where(['hospital_id' => $currentHospital->id, 'status' => SiteConstants::USER_STATUS_INACTIVE])->count();
        
        // Get options for filters (exclude administrator and super roles)
        $roles = $this->Users->Roles->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ])->where(['type NOT IN' => ['administrator', 'super']])->toArray();
        
        $this->set(compact('users', 'totalCount', 'activeCount', 'inactiveCount', 'roles', 'currentHospital'));
    }

    public function view($id = null) {
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        if (!$currentHospital) {
            $this->Flash->error(__('Hospital context not found. Please try again.'));
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
        }

        $user = $this->Users->get($id, [
            'contain' => ['Roles'],
        ]);
        
        // Ensure user belongs to current hospital
        if ($user->hospital_id !== $currentHospital->id) {
            throw new NotFoundException(__('User not found in your hospital.'));
        }

        $this->set(compact('user', 'currentHospital'));
    }

    public function add() {
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        if (!$currentHospital) {
            $this->Flash->error(__('Hospital context not found. Please try again.'));
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
        }

        $user = $this->Users->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Force hospital_id to current hospital
            $data['hospital_id'] = $currentHospital->id;
            
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
        
        // Only show non-admin roles (exclude administrator and super)
        $roles = $this->Users->Roles->find('list', ['limit' => 200])
            ->where(['type NOT IN' => ['administrator', 'super']])
            ->all();
        $roleTypes = $this->Users->Roles->find('list', [
            'keyField' => 'id',
            'valueField' => 'type',
            'limit' => 200
        ])->where(['type NOT IN' => ['administrator', 'super']])->toArray();
        
        $this->set(compact('user', 'roles', 'roleTypes', 'currentHospital'));
    }

    public function edit($id = null) {
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        if (!$currentHospital) {
            $this->Flash->error(__('Hospital context not found. Please try again.'));
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
        }

        $user = $this->Users->get($id, [
            'contain' => ['Roles'],
        ]);
        
        // Ensure user belongs to current hospital
        if ($user->hospital_id !== $currentHospital->id) {
            throw new NotFoundException(__('User not found in your hospital.'));
        }
        
        // Store original role for comparison
        $originalRoleId = $user->role_id;
        
        // Load existing specialized record data for form population
        $specializedData = $this->getSpecializedRecordData($user);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Force hospital_id to current hospital
            $data['hospital_id'] = $currentHospital->id;
            
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
        
        // Only show non-admin roles (exclude administrator and super)
        $roles = $this->Users->Roles->find('list', ['limit' => 200])
            ->where(['type NOT IN' => ['administrator', 'super']])
            ->all();
        $roleTypes = $this->Users->Roles->find('list', [
            'keyField' => 'id',
            'valueField' => 'type',
            'limit' => 200
        ])->where(['type NOT IN' => ['administrator', 'super']])->toArray();
        
        $this->set(compact('user', 'roles', 'roleTypes', 'currentHospital'));
    }

    public function delete($id = null) {
        $this->request->allowMethod(['post', 'delete']);
        
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        if (!$currentHospital) {
            $this->Flash->error(__('Hospital context not found. Please try again.'));
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
        }
        
        $user = $this->Users->get($id, ['contain' => ['Roles']]);
        
        // Ensure user belongs to current hospital
        if ($user->hospital_id !== $currentHospital->id) {
            throw new NotFoundException(__('User not found in your hospital.'));
        }
        
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

    public function toggleStatus($id = null) {
        $this->request->allowMethod(['post', 'put']);
        
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        if (!$currentHospital) {
            $this->Flash->error(__('Hospital context not found. Please try again.'));
            return $this->redirect(['controller' => 'Dashboard', 'action' => 'index']);
        }
        
        $user = $this->Users->get($id);
        
        // Ensure user belongs to current hospital
        if ($user->hospital_id !== $currentHospital->id) {
            throw new NotFoundException(__('User not found in your hospital.'));
        }
        
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

    private function createSpecializedRecord($user, $formData = []) {
        if (!$user->role) {
            return;
        }

        // Get current hospital from session for hospital-scoped context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        if (!$currentHospital) {
            return;
        }

        $roleType = $user->role->type;
        $specializedData = [
            'user_id' => $user->id,
            'hospital_id' => $currentHospital->id,
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

    private function updateSpecializedRecord($user, $formData = []) {
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

    private function handleRoleChange($user, $originalRoleId, $formData = []) {
        // First, delete the old specialized record
        $this->deleteSpecializedRecord($user->id, $originalRoleId);
        
        // Then, create the new specialized record
        $this->createSpecializedRecord($user, $formData);
    }

    private function getSpecializedRecordData($user) {
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

    private function deleteSpecializedRecord($userId, $roleId) {
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