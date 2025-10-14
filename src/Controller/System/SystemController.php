<?php
declare(strict_types=1);

namespace App\Controller\System;

use App\Controller\AppController;

/**
 * Base System Controller
 * 
 * Base controller for all system admin controllers to inherit common functionality
 */
class SystemController extends AppController
{
    /**
     * Initialize callback
     */
    public function initialize(): void
    {
        parent::initialize();
        
        // Set system layout for all system admin pages
        $this->viewBuilder()->setLayout('system');
        
        // Check if user is authenticated and has proper permissions
        $this->checkSystemAccess();
    }

    /**
     * Check if the current user has system admin access
     *
     * @return void
     */
    protected function checkSystemAccess(): void
    {
        $identity = $this->Authentication->getIdentity();
        
        if (!$identity) {
            $this->Flash->error(__('You must be logged in to access the system admin panel.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            $this->redirect(['prefix' => 'System', 'controller' => 'Login', 'action' => 'login']);
            return;
        }
        
        // Get user ID and check if it's valid
        $userId = $identity->get('id');
        if (!$userId) {
            $this->Flash->error(__('Invalid user session. Please log in again.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            $this->Authentication->logout();
            $this->redirect(['prefix' => 'System', 'controller' => 'Login', 'action' => 'login']);
            return;
        }
        
        // Load the user with role relationship
        $usersTable = $this->fetchTable('Users');
        try {
            $user = $usersTable->get($userId, [
                'contain' => ['Roles']
            ]);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Flash->error(__('User account not found. Please log in again.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            $this->Authentication->logout();
            $this->redirect(['prefix' => 'System', 'controller' => 'Login', 'action' => 'login']);
            return;
        }
        
        // Check if user has super admin role
        if (!$user->role || $user->role->type !== 'super') {
            $this->Flash->error(__('You do not have permission to access the system admin panel. Super admin access required.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            $this->Authentication->logout();
            $this->redirect(['prefix' => 'System', 'controller' => 'Login', 'action' => 'login']);
            return;
        }
    }
}