<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

class AdminController extends AppController {
    public function initialize(): void {
        parent::initialize();
        
        // Set admin layout for all admin pages
        $this->viewBuilder()->setLayout('admin');
        
        // Check if user is authenticated and has hospital admin permissions
        $this->checkAdminAccess();
    }

    protected function checkAdminAccess(): void {
        $result = $this->Authentication->getResult();
        
        // If authentication failed or user is not authenticated, redirect to login
        if (!$result || !$result->isValid()) {
            $this->Flash->error(__('You must be logged in to access the admin panel.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            $this->redirect(['prefix' => 'Admin', 'controller' => 'Login', 'action' => 'login']);
            return;
        }
        
        $identity = $this->Authentication->getIdentity();
        
        // Get user ID and check if it's valid
        $userId = $identity ? $identity->get('id') : null;
        
        if (!$userId) {
            $this->Flash->error(__('Invalid user session. Please log in again.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            $this->Authentication->logout();
            $this->redirect(['prefix' => 'Admin', 'controller' => 'Login', 'action' => 'login']);
            return;
        }
        
        // Load the user with role relationship
        $usersTable = $this->fetchTable('Users');
        try {
            $user = $usersTable->get($userId, [
                'contain' => ['Roles', 'Hospitals']
            ]);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->log('User not found in database - User ID: ' . $userId, 'error');
            $this->Flash->error(__('User account not found. Please log in again.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            $this->Authentication->logout();
            $this->redirect(['prefix' => 'Admin', 'controller' => 'Login', 'action' => 'login']);
            return;
        }
        
        // Check if user has hospital admin role
        if (!$user->role || $user->role->type !== 'administrator') {
            $this->Flash->error(__('You do not have permission to access the hospital admin panel. Administrator access required.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            
            // DO NOT logout the user - redirect them to their appropriate dashboard based on their actual role
            $roleType = $user->role ? $user->role->type : 'unknown';
            $this->redirectToRoleDashboard($roleType);
            return;
        }

        // Ensure user is assigned to current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        if ($currentHospital && $user->hospital_id !== $currentHospital->id) {
            $this->Flash->error(__('You do not have permission to access this hospital\'s admin panel.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            
            // DO NOT logout the user - redirect them to their admin dashboard for their hospital
            $this->redirect([
                'prefix' => 'Admin',
                'controller' => 'Dashboard',
                'action' => 'index'
            ]);
            return;
        }
    }
    
    /**
     * Redirect user to appropriate role-based dashboard
     *
     * @param string $roleType User role type
     * @return \Cake\Http\Response
     */
    private function redirectToRoleDashboard(string $roleType): \Cake\Http\Response
    {
        // Map role types to dashboard routes
        $roleRoutes = [
            'administrator' => ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
            'doctor' => ['prefix' => 'Doctor', 'controller' => 'Dashboard', 'action' => 'index'],
            'technician' => ['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index'],
            'scientist' => ['prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index'],
            'nurse' => ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
            'super' => ['prefix' => 'System', 'controller' => 'Dashboard', 'action' => 'index']
        ];
        
        $route = $roleRoutes[strtolower($roleType)] ?? null;
        
        if ($route) {
            return $this->redirect($route);
        }
        
        // Fallback to homepage if role not recognized
        return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
    }
}