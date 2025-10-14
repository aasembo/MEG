<?php
declare(strict_types=1);

namespace App\Controller\Technician;

use App\Controller\AppController;

/**
 * Technician Dashboard Controller
 *
 * Handles the technician dashboard and related functionality
 */
class DashboardController extends AppController
{
    /**
     * Technician dashboard index
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        // Check if user is authenticated and has technician role
        $user = $this->Authentication->getIdentity();
        if (!$user) {
            $this->Flash->error(__('Authentication required.'));
            return $this->redirect(['prefix' => 'Technician', 'controller' => 'Login', 'action' => 'login']);
        }
        
        // Load user with role relationship to check role.type
        $usersTable = $this->fetchTable('Users');
        $userWithRole = $usersTable->find()
            ->contain(['Roles'])
            ->where(['Users.id' => $user->id])
            ->first();
            
        if (!$userWithRole || !$userWithRole->role || $userWithRole->role->type !== 'technician') {
            $this->Flash->error(__('Access denied. Technician privileges required.'));
            return $this->redirect(['prefix' => 'Technician', 'controller' => 'Login', 'action' => 'login']);
        }

        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        // Get case statistics if hospital context is available
        $caseStats = [];
        if ($currentHospital && isset($currentHospital->id)) {
            $casesTable = $this->fetchTable('Cases');
            
            // Total cases by technician
            $myCasesCount = $casesTable->find()
                ->where([
                    'Cases.user_id' => $userWithRole->id,
                    'Cases.hospital_id' => $currentHospital->id
                ])
                ->count();
            
            // Cases by status for this technician
            $casesByStatus = $casesTable->find()
                ->select(['status', 'count' => $casesTable->find()->func()->count('*')])
                ->where([
                    'Cases.user_id' => $userWithRole->id,
                    'Cases.hospital_id' => $currentHospital->id
                ])
                ->group(['status'])
                ->toArray();
            
            // Recent cases created by this technician
            $recentCases = $casesTable->find()
                ->contain(['PatientUsers'])
                ->where([
                    'Cases.user_id' => $userWithRole->id,
                    'Cases.hospital_id' => $currentHospital->id
                ])
                ->order(['Cases.created' => 'DESC'])
                ->limit(5)
                ->toArray();
            
            $caseStats = [
                'total_cases' => $myCasesCount,
                'cases_by_status' => $casesByStatus,
                'recent_cases' => $recentCases
            ];
        }
        
        $this->set(compact('user', 'currentHospital', 'caseStats'));
    }
}