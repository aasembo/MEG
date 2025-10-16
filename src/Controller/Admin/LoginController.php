<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Lib\UserActivityLogger;
use Cake\Core\Configure;
use function Cake\Core\env;

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
        
        // Check if Okta is enabled
        $oktaEnabled = Configure::read('Okta.enabled', false);
        
        // If Okta is enabled and this is a GET request, redirect to Okta login
        if ($oktaEnabled && $this->request->is('get') && !$this->request->getQuery('force_local')) {
            return $this->redirectToOktaLogin($hospitalId, $hospitalSubdomain);
        }
        
        $result = $this->Authentication->getResult();
        
        // Check if user is already authenticated
        if ($result && $result->isValid()) {
            $user = $this->Authentication->getIdentity();
            
            if ($user) {
                // Load user with role relationship to check role.type and hospital
                $usersTable = $this->fetchTable('Users');
                $userWithRole = $usersTable->find()
                    ->contain(['Roles'])
                    ->where(['Users.id' => $user->getIdentifier()])
                    ->first();
                
                if ($userWithRole && $userWithRole->role) {
                    // Check if user has administrator role type
                    if ($userWithRole->role->type === 'administrator') {
                        // Check if user belongs to the current hospital context
                        if ($userWithRole->hospital_id == $hospitalId) {
                            // Store hospital context in session
                            $this->request->getSession()->write('Hospital.current', $hospital);
                            
                            // Log successful admin access
                            $this->activityLogger->log('admin_login_success', [
                                'user_id' => $userWithRole->id,
                                'hospital_id' => $hospitalId,
                                'request' => $this->request,
                                'event_data' => [
                                    'hospital_name' => $hospital->name,
                                    'user_role' => $userWithRole->role->type,
                                    'authentication_method' => $oktaEnabled ? 'okta' : 'local'
                                ]
                            ]);
                            
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
                            
                            // DO NOT logout the user - redirect them to their proper admin dashboard
                            return $this->redirect([
                                'prefix' => 'Admin',
                                'controller' => 'Dashboard',
                                'action' => 'index'
                            ]);
                        }
                    } else {
                        // User has non-admin role, deny access to admin panel
                        $this->activityLogger->logAccessDenied($userWithRole->id, 'admin_access_denied', [
                            'role_type' => $userWithRole->role->type,
                            'hospital_id' => $hospitalId,
                            'request' => $this->request,
                            'description' => "User with {$userWithRole->role->type} role attempted to access admin panel",
                        ]);
                        
                        $this->Flash->error(__('You do not have permission to access the admin panel. Administrator access required.'));
                        
                        // DO NOT logout the user - just redirect them to their appropriate dashboard
                        return $this->redirectToRoleDashboard($userWithRole->role->type);
                    }
                }
            }
        }
        
        // Handle form submission
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Validate that the user exists and is an admin for the current hospital (or super user)
            if (!empty($data['email']) && !empty($data['password'])) {
                $usersTable = $this->fetchTable('Users');
                $userForValidation = $usersTable->find()
                    ->contain(['Roles'])
                    ->where([
                        'Users.email' => $data['email'],
                        'Users.status' => 'active'
                    ])
                    ->where([
                        'OR' => [
                            ['Users.hospital_id' => $hospitalId], // Admin for this hospital
                            ['Users.hospital_id' => 0, 'Roles.type' => 'super'] // Super user
                        ]
                    ])
                    ->first();
                
                if (!$userForValidation) {
                    $this->Flash->error(__('Invalid credentials or unauthorized for this hospital.'));
                } elseif (!$userForValidation->role || !in_array($userForValidation->role->type, ['administrator', 'super'])) {
                    $this->Flash->error(__('Admin or Super access required.'));
                } else {
                    // User exists and is valid admin/super for this hospital, proceed with authentication
                    $result = $this->Authentication->getResult();
                    if ($result && $result->isValid()) {
                        // Store hospital context in session
                        $this->request->getSession()->write('Hospital.current', $hospital);
                        
                        // Log successful login
                        $this->activityLogger->log('admin_login_success', [
                            'user_id' => $userForValidation->id,
                            'hospital_id' => $hospitalId,
                            'request' => $this->request,
                            'event_data' => [
                                'hospital_name' => $hospital->name,
                                'user_role' => $userForValidation->role->type
                            ]
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
        
        // Set hospital name and Okta status for the login form
        $this->set('hospitalName', $hospital->name);
        $this->set('oktaEnabled', $oktaEnabled);
    }

    /**
     * Redirect to Okta login for administrator or super role
     *
     * @param int $hospitalId Hospital ID
     * @param string|null $hospitalSubdomain Hospital subdomain
     * @return \Cake\Http\Response
     */
    private function redirectToOktaLogin(int $hospitalId, ?string $hospitalSubdomain = null): \Cake\Http\Response
    {
        // Generate state parameter with admin role (Okta will determine actual role)
        $state = base64_encode(json_encode([
            'role' => 'administrator', // Default to administrator, actual role determined by Okta userType
            'hospital_id' => $hospitalId,
            'hospital_subdomain' => $hospitalSubdomain
        ]));
        
        // Generate PKCE parameters
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);
        
        // Store code verifier in session
        $this->request->getSession()->write('oauth_code_verifier', $codeVerifier);
        
        // Build Okta authorization URL
        $oktaDomain = env('OKTA_BASE_URL');
        $clientId = env('OKTA_CLIENT_ID');
        $redirectUri = env('OKTA_REDIRECT_URI', env('APP_BASE_URL', 'http://meg.www') . '/auth/callback'); // Use the same callback as other roles
        
        $authUrl = $oktaDomain . '/oauth2/default/v1/authorize?' . http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256'
        ]);
        
        return $this->redirect($authUrl);
    }

    /**
     * Generate PKCE code verifier
     *
     * @return string
     */
    private function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * Generate PKCE code challenge
     *
     * @param string $codeVerifier
     * @return string
     */
    private function generateCodeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

    public function logout() {
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $user = $this->Authentication->getIdentity();
            $userId = $user ? $user->id : null;
            
            // Check if user logged in via Okta (has ID token) or form
            $idToken = $this->request->getSession()->read('oauth_id_token');
            $loginMethod = $idToken ? 'okta' : 'form';
            
            // Log logout event BEFORE clearing session
            if ($loginMethod === 'okta') {
                $this->activityLogger->logOktaLogout($userId, [
                    'role_type' => 'administrator',
                    'request' => $this->request,
                    'description' => 'Administrator logged out from Okta',
                    'event_data' => [
                        'logout_method' => 'okta_complete',
                        'session_duration' => $this->calculateSessionDuration()
                    ]
                ]);
            } else {
                $this->activityLogger->log('admin_logout', [
                    'user_id' => $userId,
                    'request' => $this->request,
                    'description' => 'Administrator logged out from form authentication',
                    'event_data' => [
                        'logout_method' => 'form_logout',
                        'session_duration' => $this->calculateSessionDuration()
                    ]
                ]);
            }
            
            // Logout from local CakePHP session
            $this->Authentication->logout();
            
            $this->Flash->success(__('You have been logged out.'));
            
            // Redirect based on login method
            if ($loginMethod === 'okta' && Configure::read('Okta.enabled', false)) {
                // Build Okta logout URL first (while ID token is still in session)
                $oktaLogoutUrl = $this->buildOktaLogoutUrl();
                
                // Clear OAuth tokens from session AFTER building logout URL
                $this->request->getSession()->delete('oauth_id_token');
                $this->request->getSession()->delete('oauth_access_token');
                $this->request->getSession()->delete('okta_last_validation');
                
                return $this->redirect($oktaLogoutUrl);
            } else {
                // Clear OAuth tokens from session if they exist
                $this->request->getSession()->delete('oauth_id_token');
                $this->request->getSession()->delete('oauth_access_token');
                $this->request->getSession()->delete('okta_last_validation');
                
                // Form-based login or Okta disabled - redirect to login page
                return $this->redirect(['action' => 'login']);
            }
        } else {
            // User not authenticated, just redirect to login
            $this->Authentication->logout();
            return $this->redirect(['action' => 'login']);
        }
    }

    /**
     * Build Okta logout URL to logout from Okta session
     *
     * @return string Okta logout URL with post-logout redirect
     */
    private function buildOktaLogoutUrl(): string
    {
        $baseUrl = env('OKTA_BASE_URL');
        $postLogoutRedirectUri = env('APP_BASE_URL', 'http://meg.www');
        
        // Get ID token from session for logout hint
        $idToken = $this->request->getSession()->read('oauth_id_token');
        
        // Okta logout endpoint with post-logout redirect and id_token_hint
        $params = [
            'post_logout_redirect_uri' => $postLogoutRedirectUri
        ];
        
        // Include id_token_hint if available for proper Okta logout
        if ($idToken) {
            $params['id_token_hint'] = $idToken;
        }
        
        return $baseUrl . '/oauth2/default/v1/logout?' . http_build_query($params);
    }
    
    /**
     * Calculate session duration for logging purposes
     *
     * @return array Session duration information
     */
    private function calculateSessionDuration(): array
    {
        $sessionStartTime = $this->request->getSession()->read('Auth.sessionStart');
        $currentTime = time();
        
        if ($sessionStartTime) {
            $duration = $currentTime - $sessionStartTime;
            return [
                'duration_seconds' => $duration,
                'duration_minutes' => round($duration / 60, 2),
                'session_start' => date('Y-m-d H:i:s', $sessionStartTime),
                'session_end' => date('Y-m-d H:i:s', $currentTime)
            ];
        }
        
        return [
            'duration_seconds' => 0,
            'duration_minutes' => 0,
            'session_start' => 'unknown',
            'session_end' => date('Y-m-d H:i:s', $currentTime)
        ];
    }

    private function redirectToUserDashboard(object $user): \Cake\Http\Response {
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
        
        if ($route) {
            return $this->redirect($route);
        }
        
        // Fallback to homepage if role not recognized
        $this->Flash->error(__('Unable to determine appropriate dashboard for your role.'));
        return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
    }
}