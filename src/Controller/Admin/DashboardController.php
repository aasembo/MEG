<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\AdminController;
use App\Constants\SiteConstants;

class DashboardController extends AdminController {
    public function index() {
        $identity = $this->Authentication->getIdentity();
        
        // Load the full user entity with role relationship for the view
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($identity->get('id'), [
            'contain' => ['Roles', 'Hospitals']
        ]);
        
        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        // Ensure we have a valid hospital context
        if (!$currentHospital || !isset($currentHospital->id)) {
            $this->Flash->error(__('No hospital context found. Please contact your administrator.'));
            // Try to get hospital from user record as fallback
            if ($user->hospital_id) {
                $hospitalsTable = $this->fetchTable('Hospitals');
                $currentHospital = $hospitalsTable->get($user->hospital_id);
                $this->request->getSession()->write('Hospital.current', $currentHospital);
            } else {
                // No hospital context available at all
                $this->set([
                    'title' => 'Hospital Admin Dashboard',
                    'welcomeMessage' => 'Welcome to the Hospital Admin Panel',
                    'currentUser' => $user,
                    'currentHospital' => null,
                    'stats' => [],
                    'recentUsers' => []
                ]);
                return;
            }
        }
        
        // Fetch hospital-specific statistics
        $stats = $this->getHospitalDashboardStats($currentHospital->id);
        
        // Get recent users from this hospital (last 5 created)
        $recentUsers = $usersTable->find()
            ->contain(['Roles'])
            ->where(['Users.hospital_id' => $currentHospital->id])
            ->order(['Users.created' => 'DESC'])
            ->limit(5)
            ->toArray();
        
        // Show welcome message for first-time visitors
        if (!$this->request->getSession()->check('Dashboard.welcomed')) {
            $this->Flash->success(__('Welcome to the {0} Hospital Admin Panel!', $currentHospital->name), [
                'element' => 'success',
                'params' => ['autoDismiss' => true]
            ]);
            $this->request->getSession()->write('Dashboard.welcomed', true);
        }
        
        $this->set([
            'title' => 'Hospital Admin Dashboard',
            'welcomeMessage' => 'Welcome to the Hospital Admin Panel',
            'currentUser' => $user,
            'currentHospital' => $currentHospital,
            'stats' => $stats,
            'recentUsers' => $recentUsers
        ]);
    }
    
    private function getHospitalDashboardStats(int $hospitalId) {
        $usersTable = $this->fetchTable('Users');
        $rolesTable = $this->fetchTable('Roles');
        
        // Hospital-specific users statistics
        $totalUsers = $usersTable->find()->where(['hospital_id' => $hospitalId])->count();
        $activeUsers = $usersTable->find()->where(['hospital_id' => $hospitalId, 'status' => SiteConstants::USER_STATUS_ACTIVE])->count();
        $inactiveUsers = $usersTable->find()->where(['hospital_id' => $hospitalId, 'status' => SiteConstants::USER_STATUS_INACTIVE])->count();
        
        // Users created this month for this hospital
        $thisMonth = date('Y-m-01');
        $usersThisMonth = $usersTable->find()
            ->where(['hospital_id' => $hospitalId, 'created >=' => $thisMonth])
            ->count();
        
        // Role-based counts for this hospital - simplified approach
        $allUsers = $usersTable->find()
            ->contain(['Roles'])
            ->where(['Users.hospital_id' => $hospitalId])
            ->toArray();
        
        $roleStats = [];
        foreach ($allUsers as $user) {
            if ($user->role) {
                $key = $user->role->type . '_' . $user->role->name;
                if (!isset($roleStats[$key])) {
                    $roleStats[$key] = [
                        'role_name' => $user->role->name,
                        'role_type' => $user->role->type,
                        'count' => 0
                    ];
                }
                $roleStats[$key]['count']++;
            }
        }
        $roleStats = array_values($roleStats);
        
        // Sort by count descending
        usort($roleStats, function($a, $b) {
            return $b['count'] - $a['count'];
        });
        
        // Specialized records counts for this hospital
        $specializedCounts = [];
        $specializedTables = ['Doctors', 'Nurses', 'Scientists', 'Patients', 'Technicians'];
        
        foreach ($specializedTables as $tableName) {
            $table = $this->fetchTable($tableName);
            $specializedCounts[strtolower($tableName)] = $table->find()
                ->where(['hospital_id' => $hospitalId])
                ->count();
        }
        
        return [
            'users' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'inactive' => $inactiveUsers,
                'this_month' => $usersThisMonth,
                'growth_rate' => $totalUsers > 0 ? round(($usersThisMonth / $totalUsers) * 100, 1) : 0
            ],
            'roles' => $roleStats,
            'specialized' => $specializedCounts
        ];
    }

    /**
     * Manage patient data masking settings
     */
    public function maskingSettings()
    {
        $this->loadComponent('RequestHandler');
        $maskingService = new \App\Service\PatientMaskingService();
        
        if ($this->request->is('post')) {
            $action = $this->request->getData('action');
            
            switch ($action) {
                case 'enable':
                    $maskingService->enableMasking();
                    $this->Flash->success(__('Patient data masking has been enabled.'));
                    break;
                    
                case 'disable':
                    $maskingService->disableMasking();
                    $this->Flash->warning(__('Patient data masking has been disabled. Sensitive information will be visible.'));
                    break;
                    
                default:
                    $this->Flash->error(__('Invalid action specified.'));
                    break;
            }
            
            if ($this->request->is('ajax')) {
                $this->response = $this->response->withType('application/json');
                $status = $maskingService->getMaskingStatus();
                
                return $this->response->withStringBody(json_encode([
                    'success' => true,
                    'status' => $status,
                    'message' => $action === 'enable' ? 'Masking enabled' : 'Masking disabled'
                ]));
            }
            
            return $this->redirect(['action' => 'maskingSettings']);
        }
        
        $status = $maskingService->getMaskingStatus();
        
        $this->set(compact('status'));
        $this->set('title', 'Patient Data Masking Settings');
    }

    /**
     * AJAX endpoint to get current masking status
     */
    public function maskingStatus()
    {
        $this->request->allowMethod(['get']);
        $this->loadComponent('RequestHandler');
        
        $maskingService = new \App\Service\PatientMaskingService();
        $status = $maskingService->getMaskingStatus();
        
        $this->response = $this->response->withType('application/json');
        return $this->response->withStringBody(json_encode($status));
    }
}