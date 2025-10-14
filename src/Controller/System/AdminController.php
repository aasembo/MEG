<?php
declare(strict_types=1);

namespace App\Controller\System;

use App\Controller\AppController;

/**
 * Base Admin Controller
 * 
 * Base controller for all admin controllers to inherit common functionality
 */
class AdminController extends AppController
{
    /**
     * Initialize callback
     */
    public function initialize(): void
    {
        parent::initialize();
        
        // Set admin layout for all admin pages
        $this->viewBuilder()->setLayout('admin');
        
        // Check if user is authenticated and has proper permissions
        $this->checkAdminAccess();
    }

    /**
     * Check if the current user has admin access
     *
     * @return void
     */
    protected function checkAdminAccess(): void
    {
        $identity = $this->Authentication->getIdentity();
        
        if (!$identity) {
            $this->Flash->error(__('You must be logged in to access the admin panel.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            $this->redirect(['prefix' => 'Admin', 'controller' => 'Login', 'action' => 'login']);
            return;
        }
        
        // Load the user with role relationship
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->get($identity->get('id'), [
            'contain' => ['Roles']
        ]);
        
        // Check if user has admin or super role
        if (!$user->role || !in_array($user->role->type, [SiteConstants::ROLE_TYPE_ADMIN, SiteConstants::ROLE_TYPE_SUPER])) {
            $this->Flash->error(__('You do not have permission to access the admin panel.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
            $this->Authentication->logout();
            $this->redirect(['prefix' => 'Admin', 'controller' => 'Login', 'action' => 'login']);
            return;
        }
    }
}