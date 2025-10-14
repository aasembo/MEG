<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Lib\UserActivityLogger;

class LoginController extends AppController {
    private $activityLogger;
    
    public function initialize(): void {
        parent::initialize();
        $this->activityLogger = new UserActivityLogger();
        
        // Allow login and logout actions without authentication
        $this->Authentication->allowUnauthenticated(['login']);
    }

    public function login() {
        // Use login layout for login page (no navigation)
        $this->viewBuilder()->setLayout('login');
        $this->request->allowMethod(['get', 'post']);
        
        // Get hospital context from middleware
        $hospital = $this->request->getAttribute('hospital_context');
        $hospitalSubdomain = $this->request->getAttribute('hospital_subdomain');
        $hospitalId = $this->request->getAttribute('hospital_id');
        
        // For admin access, we need a valid hospital context
        if (!$hospital) {
            $this->Flash->error(__('Admin access requires a valid hospital context. Please access from a hospital subdomain or the main site for Hospital1 access.'));
            return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
        }
        
        $result = $this->Authentication->getResult();
        
        // Check if user is already authenticated
        if ($result && $result->isValid()) {
            $user = $this->Authentication->getIdentity();
            error_log('Authenticated user: ' . ($user ? 'ID: ' . $user->getIdentifier() : 'NULL'));
            
            if ($user) {
                // Load user with role relationship to check role.type and hospital
                $usersTable = $this->fetchTable('Users');
                $userWithRole = $usersTable->find()
                    ->contain(['Roles'])
                    ->where(['Users.id' => $user->getIdentifier()])
                    ->first();
                
                error_log('User hospital_id: ' . ($userWithRole ? $userWithRole->hospital_id : 'NULL'));
                error_log('Required hospital_id: ' . $hospitalId);
                error_log('User role type: ' . ($userWithRole && $userWithRole->role ? $userWithRole->role->type : 'NULL'));
                
                if ($userWithRole && $userWithRole->role) {
                    // Check if user has administrator role type
                    if ($userWithRole->role->type === 'administrator') {
                        // Check if user belongs to the current hospital context
                        if ($userWithRole->hospital_id == $hospitalId) {
                            // Store hospital context in session
                            $this->request->getSession()->write('Hospital.current', $hospital);
                            
                            // Redirect to admin dashboard
                            $redirect = $this->request->getQuery('redirect', [
                                'prefix' => 'Admin',
                                'controller' => 'Dashboard',
                                'action' => 'index',
                            ]);
                            return $this->redirect($redirect);
                        } else {
                            // Admin user but wrong hospital
                            $this->activityLogger->logAccessDenied($userWithRole->id, 'admin_wrong_hospital', [
                                'role_type' => $userWithRole->role->type,
                                'user_hospital_id' => $userWithRole->hospital_id,
                                'required_hospital_id' => $hospitalId,
                                'request' => $this->request,
                                'description' => "Admin user from hospital {$userWithRole->hospital_id} attempted to access hospital {$hospitalId}",
                            ]);
                            
                            $this->Flash->error(__('You can only access admin functions for your assigned hospital.'));
                            $this->Authentication->logout();
                            return $this->redirect(['action' => 'login']);
                        }
                    } else {
                        // User has different role, redirect to their appropriate dashboard
                        return $this->redirectToUserDashboard($userWithRole);
                    }
                }
            }
        }
        
        // Handle form submission
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Validate that the user exists and is an admin for the current hospital
            if (!empty($data['email']) && !empty($data['password'])) {
                $usersTable = $this->fetchTable('Users');
                $userForValidation = $usersTable->find()
                    ->contain(['Roles'])
                    ->where([
                        'Users.email' => $data['email'],
                        'Users.hospital_id' => $hospitalId,
                        'Users.status' => 'active'
                    ])
                    ->first();
                
                if (!$userForValidation) {
                    error_log('User not found or not authorized for this hospital');
                    $this->Flash->error(__('Invalid credentials or unauthorized for this hospital.'));
                } elseif (!$userForValidation->role || $userForValidation->role->type !== 'administrator') {
                    error_log('User exists but is not an administrator');
                    $this->Flash->error(__('Admin access required.'));
                } else {
                    // User exists and is valid admin for this hospital, proceed with authentication
                    $result = $this->Authentication->getResult();
                    if ($result && $result->isValid()) {
                        // Store hospital context in session
                        $this->request->getSession()->write('Hospital.current', $hospital);
                        
                        // Log successful login
                        $this->activityLogger->log('admin_login_success', [
                            'user_id' => $userForValidation->id,
                            'hospital_id' => $hospitalId,
                            'request' => $this->request,
                            'event_data' => ['hospital_name' => $hospital->name]
                        ]);
                        
                        return $this->redirect(['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index']);
                    } else {
                        $this->Flash->error(__('Invalid email or password. Please try again.'));
                    }
                }
            } else {
                $this->Flash->error(__('Please enter both email and password.'));
            }
        }
        
        // Set hospital name for the login form
        $this->set('hospitalName', $hospital->name);
        
        error_log('=== ADMIN LOGIN DEBUG END ===');
    }

    public function logout() {
        $this->Authentication->logout();
        return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
    }

    private function redirectToUserDashboard(object $user): \Cake\Http\Response {
        // Debug logging
        error_log('=== REDIRECT TO USER DASHBOARD START ===');
        error_log('User role type: ' . ($user->role ? $user->role->type : 'NULL'));
        
        // Map role types to dashboard routes
        $roleRoutes = [
            'administrator' => ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
            'doctor' => ['prefix' => 'Doctor', 'controller' => 'Dashboard', 'action' => 'index'],
            'technician' => ['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index'],
            'scientist' => ['prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index'],
            'nurse' => ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'], // Use admin dashboard
            'super' => ['prefix' => 'System', 'controller' => 'Dashboard', 'action' => 'index'],
        ];
        
        $roleType = $user->role ? $user->role->type : null;
        $route = $roleRoutes[$roleType] ?? null;
        
        error_log('Role type: ' . $roleType . ', Route: ' . ($route ? json_encode($route) : 'NULL'));
        
        if ($route) {
            $this->log('Redirecting authenticated user to ' . $roleType . ' dashboard: ' . json_encode($route), 'info');
            error_log('Redirecting to: ' . json_encode($route));
            return $this->redirect($route);
        }
        
        // Fallback to homepage if role not recognized
        error_log('Role not recognized, redirecting to homepage');
        $this->Flash->error(__('Unable to determine appropriate dashboard for your role.'));
        return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
    }
}