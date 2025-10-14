<?php
declare(strict_types=1);

namespace App\Controller\System;

use App\Constants\SiteConstants;
use App\Controller\AppController;

/**
 * Login Controller for System Admin Panel
 * 
 * Handles authentication for system admin users
 */
class LoginController extends AppController
{
    /**
     * Initialize callback
     */
    public function initialize(): void
    {
        parent::initialize();
        
        // Allow login and logout actions without authentication
        $this->Authentication->allowUnauthenticated(['login']);
    }

    /**
     * Login method
     *
     * @return \Cake\Http\Response|null|void Renders view or redirects
     */
    public function login()
    {
        // Use login layout for login page (no navigation)
        $this->viewBuilder()->setLayout('login');
        
        $this->request->allowMethod(['get', 'post']);
        $result = $this->Authentication->getResult();
        // Regardless of POST or GET, redirect if user is logged in
        if ($result && $result->isValid()) {
            $user = $this->Authentication->getIdentity();
            if ($user) {
                // Load user with role relationship to check role.type
                $usersTable = $this->fetchTable('Users');
                $userWithRole = $usersTable->find()
                    ->contain(['Roles'])
                    ->where(['Users.id' => $user->getIdentifier()])
                    ->first();
                
                if ($userWithRole && $userWithRole->role) {
                    // Check if user has super role type only
                    if ($userWithRole->role->type === SiteConstants::ROLE_TYPE_SUPER) {
                        // Redirect to where the user wanted to go or system admin dashboard
                        $redirect = $this->request->getQuery('redirect', [
                            'prefix' => 'System',
                            'controller' => 'Dashboard',
                            'action' => 'index',
                        ]);
                        return $this->redirect($redirect);
                    } else {
                        // User has different role, redirect to their appropriate dashboard
                        return $this->redirectToUserDashboard($userWithRole);
                    }
                }
            }
        } else {
            // CRITICAL FIX: Check for Okta-authenticated users even when CakePHP auth fails
            // This handles the case where Okta users access form-based login pages
            $sessionAuth = $this->request->getSession()->read('Auth');
            $oktaToken = $this->request->getSession()->read('oauth_id_token');
            
            if ($sessionAuth || $oktaToken) {
                // Try to get user from session
                $sessionUser = $this->request->getSession()->read('Auth.User');
                if ($sessionUser && isset($sessionUser['id'])) {
                    // Load user with role to redirect appropriately  
                    $usersTable = $this->fetchTable('Users');
                    $userWithRole = $usersTable->find()
                        ->contain(['Roles'])
                        ->where(['Users.id' => $sessionUser['id']])
                        ->first();
                        
                    if ($userWithRole && $userWithRole->role) {
                        return $this->redirectToUserDashboard($userWithRole);
                    }
                }
            }
        }
        
        // Display error if user submitted and authentication failed
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error(__('Invalid email or password. Please try again.'), [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
        }
    }

    /**
     * Logout action
     *
     * @return \Cake\Http\Response|null|void
     */
    public function logout()
    {
        $this->Authentication->logout();
        return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
    }

    /**
     * Redirect user to their appropriate dashboard based on role
     *
     * @param object $user User identity with role relationship loaded
     * @return \Cake\Http\Response
     */
    private function redirectToUserDashboard(object $user): \Cake\Http\Response
    {
        // Map role types to dashboard routes
        $roleRoutes = [
            SiteConstants::ROLE_TYPE_ADMINISTRATOR => ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
            SiteConstants::ROLE_TYPE_DOCTOR => ['prefix' => 'Doctor', 'controller' => 'Dashboard', 'action' => 'index'],
            SiteConstants::ROLE_TYPE_TECHNICIAN => ['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index'],
            SiteConstants::ROLE_TYPE_SCIENTIST => ['prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index'],
            SiteConstants::ROLE_TYPE_NURSE => ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'], // Use admin dashboard
            SiteConstants::ROLE_TYPE_SUPER => ['prefix' => 'System', 'controller' => 'Dashboard', 'action' => 'index'],
        ];
        
        $roleType = $user->role ? $user->role->type : null;
        $route = $roleRoutes[$roleType] ?? null;
        
        if ($route) {
            return $this->redirect($route);
        }
        
        // Fallback to homepage if role not recognized
        $this->Flash->error(__('Unable to determine appropriate dashboard for your role.'));
        return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
    }
}