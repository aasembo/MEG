<?php
declare(strict_types=1);

namespace App\Controller\Technician;

use App\Controller\AppController;
use function Cake\Core\env;
use App\Lib\UserActivityLogger;
use App\Constants\SiteConstants;

/**
 * Technician Login Controller
 *
 * Handles authentication for technicians using OpenID Connect
 */
class LoginController extends AppController
{
    private $activityLogger;
    
    public function initialize(): void
    {
        parent::initialize();
        $this->activityLogger = new UserActivityLogger();
    }
    /**
     * Before filter callback
     * Allow unauthenticated access to login action
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Allow unauthenticated access to login action
        $this->Authentication->addUnauthenticatedActions(['login']);
    }
    
    /**
     * Technician login action - redirects to Okta
     *
     * @return \Cake\Http\Response|null|void Redirects to Okta for authentication.
     */
    public function login()
    {
        $this->request->allowMethod(['get', 'post']);
        
        // Set login layout for login page (no navigation)
        $this->viewBuilder()->setLayout('login');
        
        // Get hospital context from middleware
        $hospital = $this->request->getAttribute('hospital_context');
        $hospitalSubdomain = $this->request->getAttribute('hospital_subdomain');
        $hospitalId = $this->request->getAttribute('hospital_id');
        
        // For technician access, we need a valid hospital context
        if (!$hospital) {
            $this->Flash->error(__('Technician access requires a valid hospital context. Please access from a hospital subdomain or the main site for Hospital1 access.'));
            return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
        }
        
        // Check if user is already logged in
        $result = $this->Authentication->getResult();
        
        if ($result && $result->isValid()) {
            $user = $this->Authentication->getIdentity();
            if ($user) {
                // Load user with role relationship to check role.type
                $usersTable = $this->fetchTable('Users');
                $userWithRole = $usersTable->find()
                    ->contain(['Roles'])
                    ->where(['Users.id' => $user->id])
                    ->first();
                
                if ($userWithRole && $userWithRole->role) {
                    // Check if user has technician role type only
                    if ($userWithRole->role->type === 'technician') {
                        return $this->redirect([
                            'prefix' => 'Technician',
                            'controller' => 'Dashboard',
                            'action' => 'index'
                        ]);
                    } else {
                        // User has different role, redirect to their appropriate dashboard
                        return $this->redirectToUserDashboard($userWithRole);
                    }
                }
            }
        }
        
        // Handle POST request (form submission)
        if ($this->request->is('post')) {
            $result = $this->Authentication->getResult();
            
            if ($result && $result->isValid()) {
                // Authentication successful
                $user = $this->Authentication->getIdentity();
                
                // Load user with role relationship
                $usersTable = $this->fetchTable('Users');
                $userWithRole = $usersTable->find()
                    ->contain(['Roles', 'Hospitals'])
                    ->where(['Users.id' => $user->id])
                    ->first();
                
                if ($userWithRole && $userWithRole->role) {
                    // Check if user has technician role type
                    if ($userWithRole->role->type === 'technician') {
                        // Check if user belongs to current hospital context
                        if ($userWithRole->hospital_id != $hospitalId) {
                            $this->Flash->error(__('You do not have access to this hospital\'s technician panel.'));
                            $this->Authentication->logout();
                            return $this->redirect(['action' => 'login']);
                        }
                        
                        // Log successful login
                        $this->activityLogger->logLogin($userWithRole->id, [
                            'role_type' => 'technician',
                            'hospital_id' => $hospitalId,
                            'request' => $this->request,
                            'description' => 'Technician logged in via form authentication'
                        ]);
                        
                        $this->Flash->success(__('Welcome, {0}!', $userWithRole->first_name));
                        return $this->redirect([
                            'prefix' => 'Technician',
                            'controller' => 'Dashboard', 
                            'action' => 'index'
                        ]);
                    } else {
                        // User has different role, redirect to their appropriate dashboard
                        $this->Flash->info(__('Redirecting to your assigned dashboard...'));
                        return $this->redirectToUserDashboard($userWithRole);
                    }
                } else {
                    $this->Flash->error(__('User account not found or has no assigned role.'));
                    $this->Authentication->logout();
                }
            } else {
                // Authentication failed
                $this->Flash->error(__('Invalid email or password. Please try again.'));
            }
        }
        
        // Check if Okta is enabled and handle Okta login
        if (\Cake\Core\Configure::read('Okta.enabled', false)) {
            // If GET request and Okta enabled, redirect to Okta
            if ($this->request->is('get')) {
                $oktaLoginUrl = $this->buildOktaLoginUrl('technician');
                if ($oktaLoginUrl === null) {
                    // Hospital context missing, redirect to homepage
                    return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
                }
                return $this->redirect($oktaLoginUrl);
            }
        }
        
        // If we reach here, show the login form (GET request with Okta disabled or POST with failed auth)
    }

    /**
     * Technician logout action - logs out from local session and Okta if applicable
     *
     * @return \Cake\Http\Response|null|void Redirects appropriately based on login method.
     */
    public function logout()
    {
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
                    'role_type' => 'technician',
                    'request' => $this->request,
                    'description' => 'Technician logged out from Okta',
                    'event_data' => [
                        'logout_method' => 'okta_complete',
                        'session_duration' => $this->calculateSessionDuration()
                    ]
                ]);
            } else {
                $this->activityLogger->logLogin($userId, [
                    'role_type' => 'technician',
                    'request' => $this->request,
                    'description' => 'Technician logged out from form authentication',
                    'event_data' => [
                        'logout_method' => 'form_logout',
                        'session_duration' => $this->calculateSessionDuration()
                    ]
                ]);
            }
            
            // Logout from local CakePHP session
            $this->Authentication->logout();
            
            // Clear OAuth tokens from session if they exist
            $this->request->getSession()->delete('oauth_id_token');
            
            $this->Flash->success(__('You have been logged out.'));
            
            // Redirect based on login method
            if ($loginMethod === 'okta' && \Cake\Core\Configure::read('Okta.enabled', false)) {
                // Build Okta logout URL and redirect to complete Okta logout
                $oktaLogoutUrl = $this->buildOktaLogoutUrl();
                return $this->redirect($oktaLogoutUrl);
            } else {
                // Form-based login or Okta disabled - redirect to login page
                return $this->redirect(['action' => 'login']);
            }
        }
        
        // If not authenticated, just redirect to login page
        return $this->redirect(['action' => 'login']);
    }

    /**
     * Build OpenID Connect authorization URL with role-specific parameters and PKCE
     *
     * @param string $role The role for this login (doctor, scientist, technician)
     * @return string|null OpenID Connect authorization URL or null if hospital context missing
     */
    private function buildOktaLoginUrl(string $role): ?string
    {
        $baseUrl = env('OKTA_BASE_URL');
        $clientId = env('OKTA_CLIENT_ID');
        $redirectUri = 'http://meg.www/auth/callback'; // Match Okta configuration exactly
        
        // Get hospital context from session or subdomain with improved detection
        $hospital = $this->request->getSession()->read('Hospital.current');
        $hospitalId = $hospital ? $hospital->id : null;
        $hospitalSubdomain = $this->getHospitalSubdomain();
        
        // Log hospital context for debugging
        $this->log("Technician login - Hospital ID: " . ($hospitalId ?: 'NONE') . ", Hospital Subdomain: " . ($hospitalSubdomain ?: 'NONE') . ", Hospital Name: " . ($hospital ? $hospital->name : 'NONE'), 'info');
        
        // Ensure we have hospital context for role-based access
        if (!$hospitalId && !$hospitalSubdomain) {
            // Use default hospital subdomain 'hospital1' when no subdomain provided
            $hospitalSubdomain = 'hospital1';
            $this->log("Technician login - No hospital context provided, using default subdomain: hospital1", 'info');
            
            // Load the default hospital from database
            $hospitalsTable = $this->fetchTable('Hospitals');
            $defaultHospital = $hospitalsTable->find()
                ->where(['subdomain' => $hospitalSubdomain, 'status' => SiteConstants::HOSPITAL_STATUS_ACTIVE])
                ->first();
                
            if ($defaultHospital) {
                $hospitalId = $defaultHospital->id;
                // Store hospital context in session for future requests
                $this->request->getSession()->write('Hospital.current', $defaultHospital);
                $this->log("Technician login - Set default hospital context: {$defaultHospital->name} (ID: {$hospitalId})", 'info');
            } else {
                $this->Flash->error(__('Default hospital is not available. Please contact support.'));
                // Return null to indicate failure, let calling method handle redirect
                return null;
            }
        }
        
        // Generate PKCE code verifier and challenge
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);
        
        // Store code verifier in session for token exchange
        $this->request->getSession()->write('oauth_code_verifier', $codeVerifier);
        
        // Generate CSRF protection state parameter with hospital context
        $state = base64_encode(json_encode([
            'role' => $role,
            'hospital_id' => $hospitalId,
            'hospital_subdomain' => $hospitalSubdomain,
            'timestamp' => time(),
            'nonce' => bin2hex(random_bytes(16))
        ]));
    
        // Standard OpenID Connect authorization parameters with PKCE
        $params = [
            'client_id' => $clientId,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'redirect_uri' => $redirectUri,
            'state' => $state,
            'response_mode' => 'query',
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
            'nonce' => bin2hex(random_bytes(16)) // Add nonce for ID token validation
        ];
        
        // Use standard OpenID Connect authorization endpoint
        return $baseUrl . '/oauth2/v1/authorize?' . http_build_query($params);
    }

    /**
     * Generate PKCE code verifier
     *
     * @return string Base64 URL-encoded code verifier
     */
    private function generateCodeVerifier(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    /**
     * Generate PKCE code challenge from verifier
     *
     * @param string $codeVerifier Code verifier
     * @return string Base64 URL-encoded code challenge
     */
    private function generateCodeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

    /**
     * Extract hospital subdomain from request
     *
     * @return string|null Hospital subdomain
     */
    private function getHospitalSubdomain(): ?string
    {
        $host = $this->request->host();
        $mainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
        
        // Extract subdomain if not on main domain
        if ($host !== $mainDomain && strpos($host, '.') !== false) {
            $parts = explode('.', $host);
            return $parts[0]; // First part is the subdomain
        }
        
        return null;
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

    /**
     * Build Okta logout URL to logout from Okta session
     *
     * @return string Okta logout URL with post-logout redirect
     */
    private function buildOktaLogoutUrl(): string
    {
        $baseUrl = env('OKTA_BASE_URL');
        $postLogoutRedirectUri = 'http://meg.www/';
        
        // Get ID token from session for logout hint
        $idToken = $this->request->getSession()->read('oauth_id_token');
        
        // Log for debugging
        if ($idToken) {
            $this->log('ID token found in session for logout', 'debug');
        } else {
            $this->log('No ID token found in session during logout', 'warning');
        }
        
        // Okta logout endpoint with post-logout redirect and id_token_hint
        $params = [
            'post_logout_redirect_uri' => $postLogoutRedirectUri
        ];
        
        // Include id_token_hint if available for proper Okta logout
        if ($idToken) {
            $params['id_token_hint'] = $idToken;
        }
        
        return $baseUrl . '/oauth2/v1/logout?' . http_build_query($params);
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
            'duration_seconds' => null,
            'session_start' => null,
            'session_end' => date('Y-m-d H:i:s', $currentTime)
        ];
    }
}