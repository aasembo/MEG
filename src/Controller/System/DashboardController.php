<?php
declare(strict_types=1);

namespace App\Controller\System;

use App\Constants\SiteConstants;
use App\Controller\System\SystemController;

/**
 * Dashboard Controller for System Admin Panel
 * 
 * Handles the main dashboard functionality for the system admin interface
 */
class DashboardController extends SystemController
{
    /**
     * Index method - Admin Dashboard home page
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $identity = $this->Authentication->getIdentity();
        
        // Load the full user entity with role relationship for the view
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($identity->get('id'), [
            'contain' => ['Roles']
        ]);
        
        // Fetch dynamic statistics
        $stats = $this->getDashboardStats();
        
        // Get recent users (last 5 created)
        $recentUsers = $usersTable->find()
            ->contain(['Roles', 'Hospitals'])
            ->order(['Users.created' => 'DESC'])
            ->limit(5)
            ->toArray();
        
        // Show welcome message for first-time visitors
        if (!$this->request->getSession()->check('Dashboard.welcomed')) {
            $this->Flash->success(__('Welcome to the System Admin Panel! You have successfully logged in.'), [
                'element' => 'success',
                'params' => ['autoDismiss' => true]
            ]);
            $this->request->getSession()->write('Dashboard.welcomed', true);
        }
        
        $this->set([
            'title' => 'System Admin Dashboard',
            'welcomeMessage' => 'Welcome to the System Administration Panel',
            'currentUser' => $user,
            'stats' => $stats,
            'recentUsers' => $recentUsers
        ]);
    }
    
    /**
     * Get dashboard statistics
     *
     * @return array Dashboard statistics
     */
    private function getDashboardStats()
    {
        $usersTable = $this->fetchTable('Users');
        $rolesTable = $this->fetchTable('Roles');
        $hospitalsTable = $this->fetchTable('Hospitals');
        
        // Users statistics
        $totalUsers = $usersTable->find()->count();
        $activeUsers = $usersTable->find()->where(['status' => SiteConstants::USER_STATUS_ACTIVE])->count();
        $inactiveUsers = $usersTable->find()->where(['status' => SiteConstants::USER_STATUS_INACTIVE])->count();
        
        // Users created this month
        $thisMonth = date('Y-m-01');
        $usersThisMonth = $usersTable->find()
            ->where(['created >=' => $thisMonth])
            ->count();
        
        // Role-based counts
        $roleStats = $usersTable->find()
            ->select([
                'role_name' => 'Roles.name',
                'role_type' => 'Roles.type',
                'count' => $usersTable->find()->func()->count('Users.id')
            ])
            ->contain(['Roles'])
            ->group(['Roles.id', 'Roles.name', 'Roles.type'])
            ->orderDesc('count')
            ->toArray();
        
        // Hospital statistics
        $totalHospitals = $hospitalsTable->find()->count();
        $activeHospitals = $hospitalsTable->find()->where(['status' => SiteConstants::HOSPITAL_STATUS_ACTIVE])->count();
        
        // Specialized records counts
        $specializedCounts = [];
        $specializedTables = ['Doctors', 'Nurses', 'Scientists', 'Patients', 'Technicians'];
        
        foreach ($specializedTables as $tableName) {
            $table = $this->fetchTable($tableName);
            $specializedCounts[strtolower($tableName)] = $table->find()->count();
        }
        
        return [
            'users' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'inactive' => $inactiveUsers,
                'this_month' => $usersThisMonth,
                'growth_rate' => $totalUsers > 0 ? round(($usersThisMonth / $totalUsers) * 100, 1) : 0
            ],
            'hospitals' => [
                'total' => $totalHospitals,
                'active' => $activeHospitals,
                'inactive' => $totalHospitals - $activeHospitals
            ],
            'roles' => $roleStats,
            'specialized' => $specializedCounts
        ];
    }
}