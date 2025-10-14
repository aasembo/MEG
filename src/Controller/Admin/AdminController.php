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
        $userId = $identity->get('id');
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
            $this->Flash->error(__('User account not found. Please log in again.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            $this->Authentication->logout();
            $this->redirect(['prefix' => 'Admin', 'controller' => 'Login', 'action' => 'login']);
            return;
        }
        
        // Check if user has hospital admin role (not super admin)
        if (!$user->role || $user->role->type !== 'administrator') {
            $this->Flash->error(__('You do not have permission to access the hospital admin panel. Administrator access required.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            $this->Authentication->logout();
            $this->redirect(['prefix' => 'Admin', 'controller' => 'Login', 'action' => 'login']);
            return;
        }

        // Ensure user is assigned to current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        if ($currentHospital && $user->hospital_id !== $currentHospital->id) {
            $this->Flash->error(__('You do not have permission to access this hospital\'s admin panel.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            $this->Authentication->logout();
            $this->redirect(['prefix' => 'Admin', 'controller' => 'Login', 'action' => 'login']);
            return;
        }
    }
}