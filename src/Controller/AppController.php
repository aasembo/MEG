<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use App\Constants\SiteConstants;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // Add SSL detector to prevent "No detector set for type `ssl`" error
        $this->request->addDetector('ssl', function ($request) {
            return $request->is('https') || 
                   $request->getEnv('HTTPS') || 
                   $request->getEnv('HTTP_X_FORWARDED_PROTO') === 'https';
        });

        $this->loadComponent('Flash');
        
        // Set hospital context for all controllers except Auth and System controllers
        // System controllers manage multiple hospitals and don't need specific hospital context
        $prefix = $this->request->getParam('prefix');
        if ($this->getName() !== 'Auth' && $prefix !== 'System') {
            $this->setHospitalContext();
        }
        
        // Load authentication for role-based controllers, admin controllers, and auth controller
        // Only load for Pages controller if it's NOT the home action (to allow public access to homepage)
        if (in_array($prefix, ['Doctor', 'Scientist', 'Technician', 'Admin', 'System']) || 
            $this->getName() === 'Auth' ||
            ($this->getName() === 'Pages' && $this->request->getParam('action') !== 'home')) {
            $this->loadComponent('Authentication.Authentication');
            
            // Check Okta session validity for authenticated users
            $this->checkOktaSessionValidity();
        }

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    /**
     * Before render callback
     * 
     * @param \Cake\Event\EventInterface $event The beforeRender event
     * @return void
     */
    public function beforeRender(\Cake\Event\EventInterface $event): void
    {
        parent::beforeRender($event);
        
        // Set layout based on prefix
        $prefix = $this->request->getParam('prefix');
        
        if ($prefix === 'Technician') {
            $this->viewBuilder()->setLayout('technician');
        } elseif ($prefix === 'Scientist') {
            $this->viewBuilder()->setLayout('scientist');
        } elseif ($prefix === 'Doctor') {
            $this->viewBuilder()->setLayout('doctor');
        }
    }

    /**
     * Detect hospital from subdomain and set context
     *
     * @return void
     */
    protected function setHospitalContext(): void
    {
        // Check if hospital context is already set in session
        $hospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$hospital) {
            // First, check if the middleware has set hospital context in request attributes
            $hospitalFromMiddleware = $this->request->getAttribute('hospital_context');
            
            if ($hospitalFromMiddleware) {
                // Use the hospital context from middleware
                $hospital = $hospitalFromMiddleware;
                $this->request->getSession()->write('Hospital.current', $hospital);
            } else {
                $host = $this->request->host();
                if ($host === null) {
                    // Handle cases where host is not available (e.g., CLI context)
                    $host = 'localhost';
                }
                $subdomain = $this->extractSubdomain($host);
                
                // For development: Allow hospital switching via query parameter
                $queryHospital = $this->request->getQuery('hospital');
                $mainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
                if ($queryHospital && ($host === 'localhost' || strpos($host, 'localhost:') === 0 || $host === $mainDomain || strpos($host, $mainDomain) !== false)) {
                    $subdomain = $queryHospital;
                }
                
                $hospitalsTable = $this->fetchTable('Hospitals');
                
                // If no subdomain detected, try to use a default hospital for development
                if (empty($subdomain)) {
                    // For development/localhost access, use the first active hospital as default
                    if ($host === 'localhost' || strpos($host, 'localhost:') === 0) {
                        $defaultHospital = $hospitalsTable->find()
                            ->where(['status' => SiteConstants::HOSPITAL_STATUS_ACTIVE])
                            ->orderBy(['id' => 'ASC'])
                            ->first();
                            
                        if ($defaultHospital) {
                            $hospital = $defaultHospital;
                            $this->request->getSession()->write('Hospital.current', $hospital);
                        }
                    }
                    // For main domain without subdomain, don't set hospital context
                    if (!$hospital) {
                        return;
                    }
                } else {
                    // Subdomain was specified - check if it exists (both active and inactive)
                    $hospitalCheck = $hospitalsTable->find()
                        ->where(['subdomain' => $subdomain])
                        ->first();
                    
                    $mainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
                
                // If hospital doesn't exist at all, redirect
                if (!$hospitalCheck) {
                    // Clear any existing hospital session
                    $this->request->getSession()->delete('Hospital.current');
                    
                    $this->Flash->error(__('Hospital "{0}" does not exist. Redirecting to main site.', $subdomain), [
                        'element' => 'error',
                        'params' => ['autoDismiss' => true]
                    ]);
                    
                    $redirectUrl = $this->buildMainDomainUrl($host);
                    
                    // Add headers to prevent caching of redirect
                    $response = $this->response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                                               ->withHeader('Pragma', 'no-cache')
                                               ->withHeader('Expires', '0');
                    $this->setResponse($response);
                    
                    $this->redirect($redirectUrl);
                    return;
                }
                
                // If hospital exists but is inactive, redirect
                if ($hospitalCheck->status !== SiteConstants::HOSPITAL_STATUS_ACTIVE) {
                    // Clear any existing hospital session
                    $this->request->getSession()->delete('Hospital.current');
                    
                    // Set a session flag to show message after redirect
                    $this->request->getSession()->write('Hospital.redirected', [
                        'hospital' => $hospitalCheck->subdomain,
                        'reason' => SiteConstants::HOSPITAL_STATUS_INACTIVE
                    ]);
                    
                    // For development, show a more helpful message
                    $mainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
                    if ($host === 'localhost' || strpos($host, 'localhost:') === 0 || $host === $mainDomain || strpos($host, $mainDomain) !== false) {
                        $this->Flash->error(__('Hospital "{0}" is currently inactive. Please try a different hospital or go to the main site.', $subdomain), [
                            'element' => 'error',
                            'params' => ['autoDismiss' => false]
                        ]);
                    } else {
                        $this->Flash->error(__('This hospital is currently inactive. Redirecting to main site.'), [
                            'element' => 'error',
                            'params' => ['autoDismiss' => true]
                        ]);
                    }
                    
                    // Redirect to main domain (without subdomain)
                    $redirectUrl = $this->buildMainDomainUrl($host);
                    
                    // Add headers to prevent caching of redirect
                    $response = $this->response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                                               ->withHeader('Pragma', 'no-cache')
                                               ->withHeader('Expires', '0');
                    $this->setResponse($response);
                    
                    $this->redirect($redirectUrl);
                    return;
                }
                
                // Hospital exists and is active - use it
                $hospital = $hospitalCheck;
                    
                    // If hospital doesn't exist at all, redirect
                    if (!$hospitalCheck) {
                        // Clear any existing hospital session
                        $this->request->getSession()->delete('Hospital.current');
                        
                        $this->Flash->error(__('Hospital "{0}" does not exist. Redirecting to main site.', $subdomain), [
                            'element' => 'error',
                            'params' => ['autoDismiss' => true]
                        ]);
                        
                        $redirectUrl = $this->buildMainDomainUrl($host);
                        
                        // Add headers to prevent caching of redirect
                        $response = $this->response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                                                   ->withHeader('Pragma', 'no-cache')
                                                   ->withHeader('Expires', '0');
                        $this->setResponse($response);
                        
                        $this->redirect($redirectUrl);
                        return;
                    }
                    
                    // If hospital exists but is inactive, redirect
                    if ($hospitalCheck->status !== SiteConstants::HOSPITAL_STATUS_ACTIVE) {
                        // Clear any existing hospital session
                        $this->request->getSession()->delete('Hospital.current');
                        
                        // Set a session flag to show message after redirect
                        $this->request->getSession()->write('Hospital.redirected', [
                            'hospital' => $hospitalCheck->subdomain,
                            'reason' => SiteConstants::HOSPITAL_STATUS_INACTIVE
                        ]);
                        
                        // For development, show a more helpful message
                        $mainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
                        if ($host === 'localhost' || strpos($host, 'localhost:') === 0 || $host === $mainDomain || strpos($host, $mainDomain) !== false) {
                            $this->Flash->error(__('Hospital "{0}" is currently inactive. Please try a different hospital or go to the main site.', $subdomain), [
                                'element' => 'error',
                                'params' => ['autoDismiss' => false]
                            ]);
                        } else {
                            $this->Flash->error(__('This hospital is currently inactive. Redirecting to main site.'), [
                                'element' => 'error',
                                'params' => ['autoDismiss' => true]
                            ]);
                        }
                        
                        // Redirect to main domain (without subdomain)
                        $redirectUrl = $this->buildMainDomainUrl($host);
                        
                        // Add headers to prevent caching of redirect
                        $response = $this->response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                                                   ->withHeader('Pragma', 'no-cache')
                                                   ->withHeader('Expires', '0');
                        $this->setResponse($response);
                        
                        $this->redirect($redirectUrl);
                        return;
                    }
                    
                    // Hospital exists and is active - use it
                    $hospital = $hospitalCheck;
                }
            }
            
            if ($hospital) {
                // Store hospital context in session
                $this->request->getSession()->write('Hospital.current', $hospital);
            }
        } else {
            // Check if the current hospital in session is still active
            $hospitalsTable = $this->fetchTable('Hospitals');
            $hospitalCheck = $hospitalsTable->find()
                ->where(['id' => $hospital->id])
                ->first();
                
            if (!$hospitalCheck || $hospitalCheck->status !== SiteConstants::HOSPITAL_STATUS_ACTIVE) {
                // Clear session and redirect to main domain
                $this->request->getSession()->delete('Hospital.current');
                
                // Set a session flag to show message after redirect
                $this->request->getSession()->write('Hospital.redirected', [
                    'hospital' => $hospital->subdomain,
                    'reason' => 'deactivated'
                ]);
                
                $this->Flash->error(__('Your hospital session has expired or the hospital is no longer active. Redirecting to main site.'), [
                    'element' => 'error',
                    'params' => ['autoDismiss' => true]
                ]);
                
                $host = $this->request->host();
                $redirectUrl = $this->buildMainDomainUrl($host);
                $this->redirect($redirectUrl);
                return;
            }
        }
        
        if ($hospital) {
            $this->set('currentHospital', $hospital);
        }
        
        // Check if we were redirected from an inactive hospital
        $redirected = $this->request->getSession()->read('Hospital.redirected');
        if ($redirected) {
            $this->request->getSession()->delete('Hospital.redirected');
            
            $messages = [
                SiteConstants::HOSPITAL_STATUS_INACTIVE => __('You were redirected from "{0}" hospital because it is currently inactive.', $redirected['hospital']),
                'deactivated' => __('Your session for "{0}" hospital expired because it was deactivated.', $redirected['hospital']),
                'nonexistent' => __('You were redirected because "{0}" hospital does not exist.', $redirected['hospital'])
            ];
            
            $message = $messages[$redirected['reason']] ?? __('You were redirected from "{0}" hospital.', $redirected['hospital']);
            
            $this->Flash->warning($message, [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
        }
    }

    /**
     * Extract subdomain from host
     *
     * @param string $host Full hostname
     * @return string|null Subdomain or null if none
     */
    protected function extractSubdomain(string $host): ?string
    {
        // Remove port if present
        $host = explode(':', $host)[0];
        
        // Split by dots
        $parts = explode('.', $host);
        
        // If localhost or IP address, no subdomain
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }
        
        // Special handling for meg.www domain
        if ($host === 'meg.www') {
            return null; // meg.www is the main domain, no subdomain
        }
        
        // If it's a subdomain of meg.www (like hospital1.meg.www)
        if (count($parts) === 3 && $parts[1] === 'meg' && $parts[2] === 'www') {
            return $parts[0]; // Return the subdomain part
        }
        
        // If only domain.tld, no subdomain
        if (count($parts) <= 2) {
            return null;
        }
        
        // Return first part as subdomain for standard domains
        return $parts[0];
    }
    
    /**
     * Get main domain without subdomain
     *
     * @param string $host Full hostname
     * @return string Main domain
     */
    protected function getMainDomain(string $host): string
    {
        // Remove port if present
        $host = explode(':', $host)[0];
        
        // Split by dots
        $parts = explode('.', $host);
        
        // If localhost or IP address, return as is
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return $host;
        }
        
        // Special handling for meg.www domain
        if ($host === 'meg.www') {
            return 'meg.www'; // meg.www is the main domain
        }
        
        // If it's a subdomain of meg.www (like hospital1.meg.www)
        if (count($parts) === 3 && $parts[1] === 'meg' && $parts[2] === 'www') {
            return 'meg.www'; // Return main domain without subdomain
        }
        
        // If only domain.tld, return as is
        if (count($parts) <= 2) {
            return $host;
        }
        
        // Return domain without subdomain (last two parts) for standard domains
        return implode('.', array_slice($parts, -2));
    }
    
    /**
     * Build main domain URL with proper protocol and port
     *
     * @param string $host Full hostname with port
     * @return string Complete URL to main domain
     */
    protected function buildMainDomainUrl(string $host): string
    {
        $originalHost = $host;
        $port = '';
        
        // Extract port if present
        if (strpos($host, ':') !== false) {
            $parts = explode(':', $host);
            $host = $parts[0];
            $port = ':' . $parts[1];
        }
        
        $mainDomain = $this->getMainDomain($host);
        
        // For localhost development, redirect to localhost with port
        if ($mainDomain === 'localhost') {
            return 'http://localhost' . $port;
        }
        
        // Get configured main domain
        $configuredMainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
        
        // For configured main domain, always redirect to port 80 (no port in URL)
        if ($mainDomain === $configuredMainDomain) {
            return 'http://' . $configuredMainDomain;
        }
        
        // For production domains, use HTTPS and no port
        $isSecure = $this->request->is('ssl') || 
                   $this->request->is('https') || 
                   $this->request->getEnv('HTTPS') ||
                   $this->request->getEnv('HTTP_X_FORWARDED_PROTO') === 'https';
        $protocol = $isSecure ? 'https' : 'http';
        return $protocol . '://' . $mainDomain;
    }

    /**
     * Check if Okta session is still valid for authenticated users
     * If user is logged out from Okta but still logged into our system, log them out
     *
     * @return void
     */
    protected function checkOktaSessionValidity(): void
    {
        // Skip for auth callback and logout actions to avoid infinite loops
        $action = $this->request->getParam('action');
        if (in_array($action, ['callback', 'logout'])) {
            return;
        }

        // Only check if Okta is enabled
        if (!\Cake\Core\Configure::read('Okta.enabled', false)) {
            return;
        }

        // Check if user is authenticated in our system
        $result = $this->Authentication->getResult();
        if (!$result || !$result->isValid()) {
            return; // User not logged in, nothing to check
        }

        // Get ID token from session
        $idToken = $this->request->getSession()->read('oauth_id_token');
        if (!$idToken) {
            // User is authenticated but no Okta token - might be local login
            return;
        }

        // Validate the ID token to check if it's still valid
        if (!$this->validateOktaIdToken($idToken)) {
            // ID token is invalid or expired, log user out
            $this->logoutFromOktaExpiry();
        }
    }

    /**
     * Validate Okta ID token
     *
     * @param string $idToken The ID token to validate
     * @return bool True if valid, false if invalid or expired
     */
    protected function validateOktaIdToken(string $idToken): bool
    {
        try {
            // First, check token expiration locally (fast check)
            if (!$this->checkTokenExpiration($idToken)) {
                return false;
            }

            // For production, validate against Okta's userinfo endpoint periodically
            // This checks if the user is still logged into Okta
            return $this->validateTokenWithOkta($idToken);

        } catch (\Exception $e) {
            // Any error means token is invalid
            return false;
        }
    }

    /**
     * Validate token with Okta's userinfo endpoint
     *
     * @param string $idToken The ID token to validate
     * @return bool True if valid, false if invalid
     */
    protected function validateTokenWithOkta(string $idToken): bool
    {
        try {
            // To reduce load, only validate against Okta every few minutes
            $lastValidation = $this->request->getSession()->read('okta_last_validation');
            $now = time();
            
            // For admin routes, validate more frequently (30 seconds)
            // For regular routes, validate every 2 minutes
            $prefix = $this->request->getParam('prefix');
            $validationInterval = ($prefix === 'Admin') ? 30 : 120;
            
            // If we validated within the interval, assume still valid
            if ($lastValidation && ($now - $lastValidation) < $validationInterval) {
                return true;
            }

            // Extract access token or use the ID token for validation
            $accessToken = $this->request->getSession()->read('oauth_access_token');
            if (!$accessToken) {
                // If no access token, we can't validate reliably, fallback to local validation
                return true;
            }

            // Validate using Okta's userinfo endpoint
            $oktaDomain = \Cake\Core\Configure::read('Okta.baseUrl');
            if (!$oktaDomain) {
                return true; // No Okta domain configured, skip validation
            }

            $userInfoEndpoint = $oktaDomain . '/oauth2/default/v1/userinfo';
            
            // Use CakePHP HTTP client
            $http = new \Cake\Http\Client([
                'timeout' => 5 // 5 second timeout to avoid blocking requests
            ]);

            $response = $http->get($userInfoEndpoint, [], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json'
                ]
            ]);

            $httpCode = $response->getStatusCode();
            
            if ($httpCode === 200) {
                // Token is valid, update last validation time
                $this->request->getSession()->write('okta_last_validation', $now);
                return true;
            } else {
                // Token is invalid (401, 403, etc.)
                return false;
            }

        } catch (\Exception $e) {
            // Network error or other issue - assume token is still valid to avoid false logouts
            // Log the error for monitoring
            $this->log('Okta token validation error: ' . $e->getMessage(), 'warning');
            return true;
        }
    }

    /**
     * Check if token is expired based on local claims
     *
     * @param string $idToken The ID token to check
     * @return bool True if valid, false if expired or invalid
     */
    protected function checkTokenExpiration(string $idToken): bool
    {
        try {
            // Extract payload from JWT token
            $tokenParts = explode('.', $idToken);
            if (count($tokenParts) !== 3) {
                return false;
            }

            // Decode the payload (second part)
            $payload = $tokenParts[1];
            $payload = str_pad($payload, (int)ceil(strlen($payload) / 4) * 4, '=', STR_PAD_RIGHT);
            $decodedPayload = base64_decode($payload);
            
            if ($decodedPayload === false) {
                return false;
            }

            $claims = json_decode($decodedPayload, true);
            if ($claims === null) {
                return false;
            }

            // Check if token is expired
            if (isset($claims['exp'])) {
                $expirationTime = $claims['exp'];
                $currentTime = time();
                
                // Add a 5-minute buffer to handle clock skew
                if ($currentTime >= ($expirationTime - 300)) {
                    return false; // Token expired or about to expire
                }
            }

            // Additional validation: Check issuer
            if (isset($claims['iss'])) {
                $expectedIssuer = env('OKTA_BASE_URL') . '/oauth2/default';
                if ($claims['iss'] !== $expectedIssuer) {
                    return false; // Invalid issuer
                }
            }

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Log user out when Okta session expires
     *
     * @return void
     */
    protected function logoutFromOktaExpiry(): void
    {
        // Get user info for logging
        $user = $this->Authentication->getIdentity();
        $userId = $user ? $user->getIdentifier() : null;

        // Clear all authentication and session data
        $this->Authentication->logout();
        $this->request->getSession()->delete('oauth_id_token');
        $this->request->getSession()->delete('oauth_access_token');
        $this->request->getSession()->delete('okta_last_validation');
        $this->request->getSession()->delete('Hospital.current');

        // Log the automatic logout
        if ($userId) {
            $this->log("User {$userId} automatically logged out due to Okta session expiry", 'info');
        }

        // Set flash message for user
        $this->Flash->warning(__('Your session has expired. Please log in again.'), [
            'element' => 'error',
            'params' => ['autoDismiss' => false]
        ]);

        // Determine appropriate redirect based on current context
        $prefix = $this->request->getParam('prefix');
        $redirectRoute = ['controller' => 'Pages', 'action' => 'home', 'prefix' => false];

        // Redirect to appropriate login page based on current prefix
        if ($prefix === 'Admin') {
            $redirectRoute = ['prefix' => 'Admin', 'controller' => 'Login', 'action' => 'login'];
        } elseif ($prefix === 'Doctor') {
            $redirectRoute = ['prefix' => 'Doctor', 'controller' => 'Login', 'action' => 'login'];
        } elseif ($prefix === 'Scientist') {
            $redirectRoute = ['prefix' => 'Scientist', 'controller' => 'Login', 'action' => 'login'];
        } elseif ($prefix === 'Technician') {
            $redirectRoute = ['prefix' => 'Technician', 'controller' => 'Login', 'action' => 'login'];
        } elseif ($prefix === 'System') {
            $redirectRoute = ['prefix' => 'System', 'controller' => 'Login', 'action' => 'login'];
        }

        $this->redirect($redirectRoute);
    }
}
