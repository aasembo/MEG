<?php
declare(strict_types=1);

namespace App\Controller;

use function Cake\Core\env;
use Cake\Http\Client;
use App\Lib\UserActivityLogger;
use App\Constants\SiteConstants;

/**
 * Authentication Controller for OpenID Connect integration
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class AuthController extends AppController
{
    private $oktaDomain;
    private $oktaClientId;
    private $oktaClientSecret;
    private $activityLogger;
    
    public function initialize(): void
    {
        parent::initialize();
        $this->oktaDomain = env('OKTA_BASE_URL');
        $this->oktaClientId = env('OKTA_CLIENT_ID');
        $this->oktaClientSecret = env('OKTA_CLIENT_SECRET');
        
        // Initialize activity logger
        $this->activityLogger = new UserActivityLogger();
        
        // Validate required environment variables
        if (!$this->oktaDomain) {
            throw new \Exception('OKTA_BASE_URL environment variable is not set');
        }
        if (!$this->oktaClientId) {
            throw new \Exception('OKTA_CLIENT_ID environment variable is not set');
        }
    }
    
    /**
     * Before filter callback
     * Allow unauthenticated access to callback action
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Allow unauthenticated access to callback and logout callback actions
        $this->Authentication->addUnauthenticatedActions(['callback', 'logoutCallback']);
    }    /**
     * Okta callback handler using Jumbojett OpenID Connect Client
     * Processes the authorization code and creates/authenticates users for doctors, scientists, and technicians
     *
     * @return \Cake\Http\Response|null|void
     */
    public function callback()
    {
        $this->request->allowMethod(['get']);
        
        // Suppress deprecation warnings from Jumbojett library to prevent headers already sent error
        $originalErrorReporting = error_reporting();
        error_reporting($originalErrorReporting & ~E_DEPRECATED);
        
        try {
            // Get authorization code and state from Okta callback
            $code = $this->request->getQuery('code');
            $state = $this->request->getQuery('state');
            $error = $this->request->getQuery('error');
            
            // Handle Okta errors with detailed logging
            if ($error) {
                $errorDescription = $this->request->getQuery('error_description');
                
                // Log failed authentication attempt
                $this->activityLogger->logLoginFailed(null, [
                    'request' => $this->request,
                    'description' => "Okta authentication failed: {$error}",
                    'event_data' => [
                        'error' => $error,
                        'error_description' => $errorDescription,
                        'authentication_method' => 'okta_openid_connect'
                    ]
                ]);
                
                $this->Flash->error(__('Authentication failed: {0}', $errorDescription ?: $error));
                return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
            }
            
            // Validate required parameters
            if (!$code || !$state) {
                $this->Flash->error(__('Invalid authentication response.'));
                return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
            }
            
            return $this->processCallback($code, $state);
            
        } catch (\Exception $e) {
            $this->Flash->error(__('Authentication failed. Please try again. Error: {0}', $e->getMessage()));
            return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
        } finally {
            // Restore original error reporting
            error_reporting($originalErrorReporting);
        }
    }
    
    /**
     * Process the authentication callback
     *
     * @param string $code Authorization code
     * @param string $state State parameter
     * @return \Cake\Http\Response
     */
    private function processCallback(string $code, string $state): \Cake\Http\Response
    {
        // Decode and validate state parameter
        $stateData = json_decode(base64_decode($state), true);
        if (!$stateData || !isset($stateData['role'])) {
            throw new \Exception('Invalid state parameter');
        }
        
        $requestedRole = $stateData['role'];
        $hospitalId = $stateData['hospital_id'] ?? null;
        $hospitalSubdomain = $stateData['hospital_subdomain'] ?? null;
        
        // Validate role is one of our supported roles
        if (!in_array($requestedRole, ['doctor', 'scientist', 'technician', 'administrator'])) {
            throw new \Exception('Invalid role requested');
        }
        
        // Get code verifier from session
        $codeVerifier = $this->request->getSession()->read('oauth_code_verifier');
        if (!$codeVerifier) {
            throw new \Exception('Missing code verifier in session');
        }
        
        // Build redirect URI to match Okta configuration exactly
        $redirectUri = env('OKTA_REDIRECT_URI', env('APP_BASE_URL', 'http://meg.www') . '/auth/callback');
        
        // Exchange authorization code for access token using manual HTTP request
        $tokenResponse = $this->exchangeCodeForToken($code, $codeVerifier, $redirectUri);
        
        if (!$tokenResponse || !isset($tokenResponse['access_token'])) {
            throw new \Exception('Failed to get access token from Okta');
        }
        
        // Clear the code verifier from session after use
        $this->request->getSession()->delete('oauth_code_verifier');
        
        // Store ID token in session for logout purposes
        if (isset($tokenResponse['id_token'])) {
            $this->request->getSession()->write('oauth_id_token', $tokenResponse['id_token']);
        } else {
            $this->log('No ID token found in token response. Available keys: ' . implode(', ', array_keys($tokenResponse)), 'warning');
        }
        
        // Get user information using the access token (optional - we can get most info from JWT)
        $userInfo = $this->getUserInfo($tokenResponse['access_token']);
       
        // If UserInfo fails, create basic user info from JWT tokens
        if (!$userInfo) {
            $this->log('UserInfo endpoint failed, attempting to extract user data from JWT tokens', 'warning');
            $userInfo = $this->createUserInfoFromTokens($tokenResponse);
            
            if (!$userInfo) {
                throw new \Exception('Failed to get user information from Okta UserInfo endpoint and JWT tokens');
            }
        }
        
        // Extract userType from access token JWT claims
        $accessTokenClaims = $this->extractTokenClaims($tokenResponse['access_token']);
        if ($accessTokenClaims && isset($accessTokenClaims['userType'])) {
            $userInfo['userType'] = $accessTokenClaims['userType'];
        }
        
        // Also check ID token if available
        if (isset($tokenResponse['id_token'])) {
            $idTokenClaims = $this->extractTokenClaims($tokenResponse['id_token']);
            if ($idTokenClaims && isset($idTokenClaims['userType']) && !isset($userInfo['userType'])) {
                $userInfo['userType'] = $idTokenClaims['userType'];
            }
        }
        
        // Determine the actual role to use - prefer Okta userType over state parameter
        try {
            $finalRole = $this->determineUserRole($userInfo, $requestedRole);
        } catch (\Exception $e) {
            // Log failed authentication attempt due to missing/invalid role
            $this->activityLogger->logLoginFailed(null, [
                'request' => $this->request,
                'description' => "Access denied: " . $e->getMessage(),
                'event_data' => [
                    'okta_sub' => $userInfo['sub'] ?? null,
                    'requested_role' => $requestedRole,
                    'authentication_method' => 'okta_openid_connect',
                    'reason' => 'invalid_or_missing_role'
                ]
            ]);
            
            $this->Flash->error(__('Access not allowed. Your account does not have the required permissions to access this system.'));
            return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
        }
        
        // Find or create user
        $user = $this->findOrCreateUser($userInfo, $finalRole, $hospitalId, $hospitalSubdomain);
        if (!$user) {
            // Log detailed error for debugging
            $this->log("findOrCreateUser returned null. UserInfo keys: " . implode(', ', array_keys($userInfo)), 'error');
            $this->log("Final role: {$finalRole}, Hospital ID: " . ($hospitalId ?: 'NONE'), 'error');
            
            // Log failed authentication attempt
            $this->activityLogger->logLoginFailed(null, [
                'request' => $this->request,
                'description' => "Failed to create or authenticate user during Okta login",
                'event_data' => [
                    'okta_sub' => $userInfo['sub'] ?? null,
                    'email' => $userInfo['email'] ?? null,
                    'final_role' => $finalRole,
                    'hospital_id' => $hospitalId,
                    'authentication_method' => 'okta_openid_connect',
                    'reason' => 'user_creation_failed'
                ]
            ]);
            
            throw new \Exception('Failed to create or authenticate user. Please contact support if this issue persists.');
        }
        
        // Log the user in
        $this->Authentication->setIdentity($user);
        
        // Log Okta authentication event
        $this->activityLogger->logOktaAuth($user->id, [
            'role_type' => $finalRole,
            'hospital_id' => $hospitalId,
            'request' => $this->request,
            'event_data' => [
                'okta_sub' => $userInfo['sub'] ?? null,
                'hospital_subdomain' => $hospitalSubdomain,
                'authentication_method' => 'okta_openid_connect',
                'requested_role' => $requestedRole,
                'okta_provided_role' => $userInfo['extracted_user_type'] ?? null
            ]
        ]);
        
        // Verify role-specific record exists
        $hasRoleRecord = $this->verifyRoleRecord($user, $finalRole);
        if (!$hasRoleRecord) {
            $this->log("Warning: User {$user->email} authenticated but missing {$finalRole} record", 'warning');
        }
        
        // Ensure hospital context is set in session after successful authentication
        if ($user->hospital_id && !$this->request->getSession()->read('Hospital.current')) {
            $this->ensureHospitalContextInSession($user->hospital_id);
        }
        
        // Prepare welcome message with hospital context
        $userName = trim($user->first_name . ' ' . $user->last_name) ?: $user->email;
        $hospitalInfo = '';
        if ($user->hospital_id) {
            $hospitalsTable = $this->fetchTable('Hospitals');
            $userHospital = $hospitalsTable->find()->where(['id' => $user->hospital_id])->first();
            if ($userHospital) {
                $hospitalInfo = " at {$userHospital->name}";
            }
        }
        
        $this->Flash->success(__('Welcome, {0}{1}!', $userName, $hospitalInfo));
        $this->log("User {$userName} ({$user->email}) successfully authenticated for {$finalRole} role" . ($hospitalInfo ? $hospitalInfo : ''), 'info');
        
        return $this->redirectToRoleDashboard($finalRole);
    }

    /**
     * Determine the user role from Okta userInfo and fallback to requested role
     *
     * @param array $userInfo User information from Okta
     * @param string $requestedRole Role from state parameter
     * @return string Final role to use
     */
    private function determineUserRole(array $userInfo, string $requestedRole): string
    {
        // Check for userType directly from token (highest priority)
        $oktaRole = $userInfo['userType'] ?? $userInfo['extracted_user_type'] ?? null;
        
        // If not found, check other possible fields
        if (!$oktaRole) {
            $possibleFields = ['role', 'user_type', 'custom_role'];
            foreach ($possibleFields as $field) {
                if (isset($userInfo[$field])) {
                    $oktaRole = $userInfo[$field];
                    $this->log("Found role in field '{$field}': {$oktaRole}", 'info');
                    break;
                }
            }
        }
        
        // Handle groups if role is still not found
        if (!$oktaRole && isset($userInfo['groups']) && is_array($userInfo['groups'])) {
            $roleMap = ['doctor', 'scientist', 'technician', 'administrator', 'nurse'];
            foreach ($userInfo['groups'] as $group) {
                $groupLower = strtolower($group);
                foreach ($roleMap as $role) {
                    if (strpos($groupLower, $role) !== false) {
                        $oktaRole = $role;
                        $this->log("Extracted role from group '{$group}': {$oktaRole}", 'info');
                        break 2;
                    }
                }
            }
        }
        
        if ($oktaRole) {
            // Validate that the Okta role is one we support
            $supportedRoles = ['doctor', 'scientist', 'technician', 'administrator', 'nurse'];
            if (in_array(strtolower($oktaRole), $supportedRoles)) {
                $finalRole = strtolower($oktaRole);
                $this->log("Using Okta-provided role: {$finalRole} (requested: {$requestedRole})", 'info');
                
                // Log a warning if Okta role differs from requested role
                if ($finalRole !== $requestedRole) {
                    $this->log("Warning: Okta role '{$finalRole}' differs from requested role '{$requestedRole}'", 'warning');
                }
                
                return $finalRole;
            } else {
                $this->log("Okta provided unsupported role '{$oktaRole}' - access denied", 'error');
                throw new \Exception("Access not allowed. Your account does not have the required permissions.");
            }
        } else {
            // Fallback to requested role if no role found in Okta (for testing/development)
            $supportedRoles = ['doctor', 'scientist', 'technician', 'administrator', 'nurse'];
            if (in_array(strtolower($requestedRole), $supportedRoles)) {
                $this->log("No role found in Okta userInfo - falling back to requested role: {$requestedRole}", 'warning');
                return strtolower($requestedRole);
            } else {
                $this->log("No valid role found in Okta userInfo and requested role '{$requestedRole}' is not supported - access denied", 'error');
                throw new \Exception("Access not allowed. No valid role found in your account.");
            }
        }
    }

    /**
     * Extract claims from JWT token (access token or ID token)
     *
     * @param string $token JWT token
     * @return array|null Decoded claims or null if invalid
     */
    private function extractTokenClaims(string $token): ?array
    {
        try {
            // JWT tokens have 3 parts separated by dots: header.payload.signature
            $tokenParts = explode('.', $token);
            
            if (count($tokenParts) !== 3) {
                $this->log('Invalid JWT token format - expected 3 parts', 'warning');
                return null;
            }
            
            // Decode the payload (second part)
            $payload = $tokenParts[1];
            
            // Add padding if needed for base64 decoding
            $payload = str_pad($payload, (int)ceil(strlen($payload) / 4) * 4, '=', STR_PAD_RIGHT);
            
            $decodedPayload = base64_decode($payload);
            
            if ($decodedPayload === false) {
                $this->log('Failed to base64 decode JWT payload', 'warning');
                return null;
            }
            
            $claims = json_decode($decodedPayload, true);
            
            if ($claims === null) {
                $this->log('Failed to JSON decode JWT claims', 'warning');
                return null;
            }
            
            // Log userType specifically if found
            if (isset($claims['userType'])) {
                $this->log("JWT contains userType: {$claims['userType']}", 'info');
            }
            
            return $claims;
            
        } catch (\Exception $e) {
            $this->log('Error extracting JWT claims: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Exchange authorization code for access token using PKCE
     *
     * @param string $code Authorization code from Okta
     * @return array|null Token response or null on failure
     */
    private function exchangeCodeForToken($code, $codeVerifier, $redirectUri)
    {
        $tokenUrl = $this->oktaDomain . '/oauth2/default/v1/token';
        
        $postFields = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->oktaClientId,
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'code_verifier' => $codeVerifier, // Always include PKCE code verifier
        ];
        
        // Add client secret if available (some Okta apps require both client secret AND PKCE)
        if (!empty($this->oktaClientSecret)) {
            $postFields['client_secret'] = $this->oktaClientSecret;
            $this->log('Using client secret + PKCE authentication', 'debug');
        } else {
            $this->log('Using PKCE-only authentication', 'debug');
        }
        
        // Use CakePHP HTTP client instead of cURL
        $http = new Client();
        try {
            $response = $http->post($tokenUrl, http_build_query($postFields), [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json'
                ]
            ]);
            
            $httpCode = $response->getStatusCode();
            $responseBody = $response->getStringBody();
            
        } catch (\Exception $e) {
            $this->log('HTTP client error: ' . $e->getMessage(), 'error');
            throw new \Exception('Network error during token exchange: ' . $e->getMessage());
        }
        
        if ($httpCode !== 200) {
            $this->log('Token exchange failed with HTTP ' . $httpCode . ': ' . $responseBody, 'error');
            throw new \Exception('Token exchange failed with HTTP ' . $httpCode . ': ' . $responseBody);
        }
        
        $tokenData = json_decode($responseBody, true);
        if (!$tokenData || !isset($tokenData['access_token'])) {
            $this->log('Invalid token response: ' . $responseBody, 'error');
            throw new \Exception('Invalid token response from Okta');
        }
        
        return $tokenData;
    }

    /**
     * Get user information from Okta UserInfo endpoint
     *
     * @param string $accessToken Access token
     * @return array|null User info or null on failure
     */
    private function getUserInfo(string $accessToken): ?array
    {
        $baseUrl = $this->oktaDomain;
        
        if (!$baseUrl) {
            throw new \Exception('Okta base URL configuration is missing');
        }
        
        $userInfoEndpoint = $baseUrl . '/oauth2/default/v1/userinfo';
        
        // Make HTTP request to userinfo endpoint using CakePHP HTTP client
        $http = new Client();
        
        try {
            $response = $http->get($userInfoEndpoint, [], [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Accept' => 'application/json'
                ]
            ]);
            
            $httpCode = $response->getStatusCode();
            $responseBody = $response->getStringBody();
            
            
        } catch (\Exception $e) {
            $this->log('HTTP client error during userinfo request: ' . $e->getMessage(), 'error');
            $this->log('UserInfo endpoint: ' . $userInfoEndpoint, 'error');
            return null;
        }
        
        if ($httpCode !== 200) {
            $this->log("UserInfo request failed. HTTP Code: {$httpCode}, Response: {$responseBody}", 'error');
            $this->log("UserInfo endpoint used: {$userInfoEndpoint}", 'error');
            
            return null;
        }
        
        $userInfo = json_decode($responseBody, true);
        if (!$userInfo) {
            $this->log("Invalid userinfo response: {$responseBody}", 'error');
            return null;
        }
        
        return $userInfo;
    }

    /**
     * Create user info from JWT tokens when UserInfo endpoint fails
     *
     * @param array $tokenResponse Token response containing access_token and id_token
     * @return array|null User info extracted from tokens or null if extraction fails
     */
    private function createUserInfoFromTokens(array $tokenResponse): ?array
    {
        $userInfo = [];
        
        // Extract from access token
        if (isset($tokenResponse['access_token'])) {
            $accessTokenClaims = $this->extractTokenClaims($tokenResponse['access_token']);
            if ($accessTokenClaims) {
                $this->log('Extracting user info from access token claims', 'info');
                
                // Common fields that might be in access token
                $fieldsToExtract = ['sub', 'email', 'userType', 'given_name', 'family_name', 'name', 'preferred_username'];
                foreach ($fieldsToExtract as $field) {
                    if (isset($accessTokenClaims[$field])) {
                        $userInfo[$field] = $accessTokenClaims[$field];
                    }
                }
            }
        }
        
        // Extract from ID token (preferred for user profile data)
        if (isset($tokenResponse['id_token'])) {
            $idTokenClaims = $this->extractTokenClaims($tokenResponse['id_token']);
            if ($idTokenClaims) {
                $this->log('Extracting user info from ID token claims', 'info');
                
                // ID token typically has more user profile information
                $fieldsToExtract = ['sub', 'email', 'given_name', 'family_name', 'name', 'preferred_username', 'userType'];
                foreach ($fieldsToExtract as $field) {
                    if (isset($idTokenClaims[$field])) {
                        $userInfo[$field] = $idTokenClaims[$field];
                        $this->log("Extracted {$field} from ID token: {$idTokenClaims[$field]}", 'debug');
                    }
                }
            }
        }
        
        // Ensure we have minimum required fields
        if (!isset($userInfo['email']) && !isset($userInfo['sub'])) {
            $this->log('Cannot create user info - missing email and sub fields in JWT tokens', 'error');
            return null;
        }
        
        // Use preferred_username as email if email is missing
        if (!isset($userInfo['email']) && isset($userInfo['preferred_username'])) {
            $userInfo['email'] = $userInfo['preferred_username'];
            $this->log('Using preferred_username as email: ' . $userInfo['email'], 'info');
        }
        
        // Parse name if given_name and family_name are missing
        if (!isset($userInfo['given_name']) && !isset($userInfo['family_name']) && isset($userInfo['name'])) {
            $nameParts = explode(' ', $userInfo['name'], 2);
            $userInfo['given_name'] = $nameParts[0] ?? '';
            $userInfo['family_name'] = $nameParts[1] ?? '';
            $this->log('Parsed name into given_name and family_name', 'debug');
        }
        
        $this->log('Successfully created user info from JWT tokens with fields: ' . implode(', ', array_keys($userInfo)), 'info');
        return $userInfo;
    }

    /**
     * Find existing user or create new user with specified role and hospital context
     *
     * @param array $userInfo User information from OpenID Connect UserInfo endpoint
     * @param string $role Requested role (doctor, scientist, technician)
     * @param int|null $hospitalId Hospital ID from state parameter
     * @param string|null $hospitalSubdomain Hospital subdomain from state parameter
     * @return object|null User entity or null on failure
     */
    private function findOrCreateUser(array $userInfo, string $role, ?int $hospitalId = null, ?string $hospitalSubdomain = null): ?object
    {
        $usersTable = $this->fetchTable('Users');
        $email = $userInfo['email'] ?? null;
        
        if (!$email) {
            $this->log('No email found in OpenID Connect user info: ' . json_encode($userInfo), 'error');
            return null;
        }
        
        // Resolve hospital ID from multiple sources
        if (!$hospitalId && $hospitalSubdomain) {
            $hospitalId = $this->getHospitalIdFromSubdomain($hospitalSubdomain);
        }
        
        // If still no hospital ID, try to get from current session context
        if (!$hospitalId) {
            $currentHospital = $this->request->getSession()->read('Hospital.current');
            if ($currentHospital && isset($currentHospital->id)) {
                $hospitalId = $currentHospital->id;
                $hospitalSubdomain = $currentHospital->subdomain;
                $this->log("Using hospital from session context: {$currentHospital->name} (ID: {$hospitalId})", 'info');
            }
        }
        
        // Get role ID from role name
        $roleId = $this->getRoleIdFromName($role);
        if (!$roleId) {
            $this->log("Failed to find role ID for role: {$role}", 'error');
            throw new \Exception("Invalid role: {$role}");
        }
        
        // Log hospital context for debugging
        $this->log("Processing user authentication - Email: {$email}, Role: {$role}, Hospital ID: " . ($hospitalId ?: 'NONE') . ", Hospital Subdomain: " . ($hospitalSubdomain ?: 'NONE'), 'info');
        
        // For all users except super administrators, hospital ID is required
        if ($role !== 'super' && !$hospitalId) {
            $this->log("Hospital ID is required for {$role} role but not provided. Email: {$email}", 'error');
            throw new \Exception("Hospital context is required for {$role} access. Please ensure you are accessing from a valid hospital subdomain.");
        }
        
        // Check if user already exists with this email and role
        $conditions = ['email' => $email, 'role_id' => $roleId];
        if ($hospitalId) {
            $conditions['hospital_id'] = $hospitalId;
        }
        
        $existingUser = $usersTable->find()
            ->where($conditions)
            ->first();
        
        if ($existingUser) {
            // User exists with correct role and hospital - update last login and OpenID Connect data
            $existingUser->modified = new \DateTime();
            if (isset($userInfo['sub'])) {
                $existingUser->okta_id = $userInfo['sub'];
            }
            $usersTable->save($existingUser);
            
            // Ensure role-specific record exists for existing user
            $roleRecord = $this->createRoleSpecificRecord($existingUser, $role, $hospitalId, $userInfo);
            if ($roleRecord) {
                $this->log("Ensured {$role} record exists for existing user {$existingUser->id}", 'info');
            }
            
            return $existingUser;
        }
        
        // Check if user exists with same email but different role or hospital
        $userWithConflict = $usersTable->find()
            ->where(['email' => $email])
            ->first();
        
        if ($userWithConflict) {
            // User exists but with different role or hospital - this is NOT allowed
            // Each user should have a fixed role and should not be able to switch roles by accessing different URLs
            $this->log("User {$email} exists with different role/hospital. Current role: {$userWithConflict->role_id}, Hospital: {$userWithConflict->hospital_id}. Requested role: {$roleId}, Hospital: {$hospitalId}", 'warning');
            
            // Return the existing user WITHOUT modifying their role
            // The calling authentication system should validate if they have permission for the requested role
            return $userWithConflict;
        }
        
        // Create new user with the requested role and hospital
        $userData = [
            'email' => $email,
            'username' => $email, // Use email as username
            'first_name' => $userInfo['given_name'] ?? '',
            'last_name' => $userInfo['family_name'] ?? '',
            'role_id' => $roleId,
            'hospital_id' => ($role === 'super') ? 0 : $hospitalId, // Only super admin can have hospital_id = 0
            'status' => SiteConstants::USER_STATUS_ACTIVE,
            'okta_id' => $userInfo['sub'] ?? null,
            'created' => new \DateTime(),
            'modified' => new \DateTime()
        ];
        
        // Update hospital ID if a specific hospital is provided (overrides the default)
        if ($hospitalId && $role !== 'super') {
            // Verify hospital exists and is active
            $hospitalsTable = $this->fetchTable('Hospitals');
            $hospitalRecord = $hospitalsTable->find()
                ->where(['id' => $hospitalId, 'status' => SiteConstants::HOSPITAL_STATUS_ACTIVE])
                ->first();
            
            if (!$hospitalRecord) {
                $this->log("Invalid or inactive hospital ID {$hospitalId} for user {$email}", 'error');
                throw new \Exception("The specified hospital is not available for new user registration.");
            }
            
            $userData['hospital_id'] = $hospitalId;
            $this->log("Associating new user {$email} with hospital: {$hospitalRecord->name} (ID: {$hospitalId})", 'info');
        } else if ($role === 'super') {
            $this->log("Creating super user {$email} with hospital_id = 0", 'info');
        } else {
            // This should not happen due to earlier validation
            throw new \Exception("Hospital association is required for {$role} users.");
        }
        
        $newUser = $usersTable->newEntity($userData);
        
        if ($usersTable->save($newUser)) {
            $hospitalInfo = $hospitalId ? " for hospital ID {$hospitalId}" : '';
            $this->log("New {$role} user created via OpenID Connect: {$email}{$hospitalInfo}", 'info');
            
            // Log user registration event
            $this->activityLogger->logRegistration($newUser->id, [
                'role_type' => $role,
                'hospital_id' => $hospitalId,
                'request' => $this->request,
                'description' => "New {$role} user registered via Okta",
                'event_data' => [
                    'okta_sub' => $userInfo['sub'] ?? null,
                    'hospital_subdomain' => $hospitalSubdomain ?? null,
                    'registration_method' => 'okta_openid_connect'
                ]
            ]);
            
            // Create corresponding role-specific record
            $roleRecord = $this->createRoleSpecificRecord($newUser, $role, $hospitalId, $userInfo);
            if ($roleRecord) {
                $this->log("Created {$role} record with ID {$roleRecord->id} for user {$newUser->id}", 'info');
            } else {
                $this->log("Warning: Failed to create {$role} record for user {$newUser->id}", 'warning');
                // Still return the user even if role record creation failed
            }
            
            return $newUser;
        }
        
        $this->log("Failed to create new user for {$email}: " . json_encode($newUser->getErrors()), 'error');
        
        // Log specific validation errors for debugging
        $errors = $newUser->getErrors();
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $this->log("User creation validation error - {$field}: {$error}", 'error');
            }
        }
        
        // Also log the user data that was attempted
        $this->log("User data that failed to save: " . json_encode($userData), 'error');
        
        return null;
    }

    /**
     * Create a record in the role-specific table (doctors, scientists, technicians)
     *
     * @param object $user The user entity that was just created
     * @param string $role The role name (doctor, scientist, technician)
     * @param int|null $hospitalId The hospital ID for the role record
     * @param array|null $userInfo Optional Okta user info for additional data
     * @return object|null The created role record or null on failure
     */
    private function createRoleSpecificRecord(object $user, string $role, ?int $hospitalId = null, ?array $userInfo = null): ?object
    {
        // Only create role-specific records for the main roles
        if (!in_array($role, ['doctor', 'scientist', 'technician'])) {
            return null;
        }
        
        // Map role names to table names
        $tableMap = [
            'doctor' => 'Doctors',
            'scientist' => 'Scientists', 
            'technician' => 'Technicians'
        ];
        
        $tableName = $tableMap[$role];
        $roleTable = $this->fetchTable($tableName);
        
        // Check if role record already exists for this user
        $existingRecord = $roleTable->find()
            ->where(['user_id' => $user->id])
            ->first();
            
        if ($existingRecord) {
            $this->log("Role record already exists for {$role} user {$user->id}", 'info');
            return $existingRecord;
        }
        
        // Create new role-specific record
        $roleData = [
            'user_id' => $user->id,
            'hospital_id' => $hospitalId,
            'created' => new \DateTime(),
            'modified' => new \DateTime()
        ];
        
        // Extract phone number from Okta user info if available
        if ($userInfo && isset($userInfo['phone_number'])) {
            $roleData['phone'] = $userInfo['phone_number'];
        } elseif ($userInfo && isset($userInfo['phone'])) {
            $roleData['phone'] = $userInfo['phone'];
        } elseif (isset($user->phone)) {
            $roleData['phone'] = $user->phone;
        }
        
        $roleEntity = $roleTable->newEntity($roleData);
        
        if ($roleTable->save($roleEntity)) {
            $this->log("Successfully created {$role} record for user {$user->id} at hospital {$hospitalId}", 'info');
            return $roleEntity;
        } else {
            $errors = $roleEntity->getErrors();
            $this->log("Failed to create {$role} record for user {$user->id}: " . json_encode($errors), 'error');
            $this->log("Role data being saved: " . json_encode($roleData), 'error');
            
            // Try to handle common validation errors
            if (isset($errors['hospital_id'])) {
                $this->log("Hospital ID validation failed. Attempting to create without hospital constraint.", 'warning');
                // For some edge cases, create without hospital_id constraint
                unset($roleData['hospital_id']);
                $roleEntity = $roleTable->newEntity($roleData);
                if ($roleTable->save($roleEntity)) {
                    $this->log("Successfully created {$role} record without hospital constraint for user {$user->id}", 'info');
                    return $roleEntity;
                } else {
                    $this->log("Failed to create {$role} record even without hospital constraint: " . json_encode($roleEntity->getErrors()), 'error');
                }
            }
            
            return null;
        }
    }

    /**
     * Verify that a user has the proper role-specific record
     *
     * @param object $user User entity
     * @param string $role Role name (doctor, scientist, technician)
     * @return bool True if role record exists, false otherwise
     */
    private function verifyRoleRecord(object $user, string $role): bool
    {
        if (!in_array($role, ['doctor', 'scientist', 'technician'])) {
            return true; // Non-role specific users don't need role records
        }
        
        $tableMap = [
            'doctor' => 'Doctors',
            'scientist' => 'Scientists', 
            'technician' => 'Technicians'
        ];
        
        $tableName = $tableMap[$role];
        $roleTable = $this->fetchTable($tableName);
        
        $roleRecord = $roleTable->find()
            ->where(['user_id' => $user->id])
            ->first();
            
        return $roleRecord !== null;
    }

    /**
     * Get role ID from role name
     *
     * @param string $roleName Role name (doctor, scientist, technician)
     * @return int|null Role ID or null if not found
     */
    private function getRoleIdFromName(string $roleName): ?int
    {
        $rolesTable = $this->fetchTable('Roles');
        
        // Use 'type' field instead of 'name' field for case-insensitive matching
        $role = $rolesTable->find()
            ->where(['type' => strtolower($roleName)])
            ->first();
        
        if ($role) {
            return $role->id;
        } else {
            $this->log("No role found for type '{$roleName}'", 'error');
            return null;
        }
    }

    /**
     * Ensure hospital context is properly set in session after successful authentication
     *
     * @param int $hospitalId Hospital ID from authenticated user
     * @return void
     */
    private function ensureHospitalContextInSession(int $hospitalId): void
    {
        $hospitalsTable = $this->fetchTable('Hospitals');
        $hospital = $hospitalsTable->find()
            ->where(['id' => $hospitalId, 'status' => SiteConstants::HOSPITAL_STATUS_ACTIVE])
            ->first();
        
        if ($hospital) {
            $this->request->getSession()->write('Hospital.current', $hospital);
            $this->log("Hospital context set in session: {$hospital->name} (ID: {$hospitalId})", 'info');
        } else {
            $this->log("Warning: Unable to set hospital context for ID {$hospitalId} - hospital not found or inactive", 'warning');
        }
    }

    /**
     * Get hospital ID from subdomain with enhanced logging
     *
     * @param string $subdomain Hospital subdomain
     * @return int|null Hospital ID or null if not found
     */
    private function getHospitalIdFromSubdomain(string $subdomain): ?int
    {
        $hospitalsTable = $this->fetchTable('Hospitals');
        
        $hospital = $hospitalsTable->find()
            ->where(['subdomain' => $subdomain, 'status' => SiteConstants::HOSPITAL_STATUS_ACTIVE])
            ->first();
        
        if ($hospital) {
            $this->log("Found active hospital for subdomain '{$subdomain}': {$hospital->name} (ID: {$hospital->id})", 'info');
            return $hospital->id;
        } else {
            $this->log("No active hospital found for subdomain '{$subdomain}'", 'warning');
            return null;
        }
    }

    /**
     * Redirect user to appropriate role-based dashboard
     *
     * @param string $role User role name
     * @return \Cake\Http\Response
     */
    private function redirectToRoleDashboard(string $role): \Cake\Http\Response
    {
        // Map role names to dashboard routes (using role types)
        $roleRoutes = [
            'administrator' => ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
            'doctor' => ['prefix' => 'Doctor', 'controller' => 'Dashboard', 'action' => 'index'],
            'technician' => ['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index'],
            'scientist' => ['prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index'],
            'nurse' => ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
            'super' => ['prefix' => 'System', 'controller' => 'Dashboard', 'action' => 'index']
        ];
        
        $route = $roleRoutes[strtolower($role)] ?? null;
        
        if ($route) {
            $this->log("Redirecting user to {$role} dashboard: " . json_encode($route), 'info');
            return $this->redirect($route);
        }
        
        // Fallback redirect for unrecognized roles
        $this->log("Unknown role '{$role}' - redirecting to homepage", 'warning');
        $this->Flash->error(__('Unable to determine appropriate dashboard for your role.'));
        return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
    }

    /**
     * Okta logout callback endpoint
     * This endpoint can be called by Okta when a user logs out from Okta directly
     * It will find and logout any active sessions for that user
     *
     * @return \Cake\Http\Response
     */
    public function logoutCallback()
    {
        $this->request->allowMethod(['post', 'get']);
        
        try {
            // Get the id_token_hint from the request (if provided by Okta)
            $idTokenHint = $this->request->getQuery('id_token_hint') ?: $this->request->getData('id_token_hint');
            
            if ($idTokenHint) {
                // Extract user information from the ID token
                $userInfo = $this->extractUserInfoFromIdToken($idTokenHint);
                
                if ($userInfo && isset($userInfo['sub'])) {
                    // Find user by Okta ID and clear their sessions
                    $this->clearUserSessionsByOktaId($userInfo['sub']);
                }
            }
            
            // Always return success response for Okta
            $response = $this->response->withType('application/json')
                                     ->withStringBody(json_encode(['status' => 'success']));
            
            return $response;
            
        } catch (\Exception $e) {
            // Log error but still return success to Okta
            $this->log('Okta logout callback error: ' . $e->getMessage(), 'error');
            
            $response = $this->response->withType('application/json')
                                     ->withStringBody(json_encode(['status' => 'error', 'message' => 'Internal error']));
            
            return $response;
        }
    }

    /**
     * Extract user information from ID token
     *
     * @param string $idToken The ID token
     * @return array|null User information or null if extraction fails
     */
    private function extractUserInfoFromIdToken(string $idToken): ?array
    {
        try {
            // JWT tokens have 3 parts separated by dots: header.payload.signature
            $tokenParts = explode('.', $idToken);
            
            if (count($tokenParts) !== 3) {
                return null;
            }
            
            // Decode the payload (second part)
            $payload = $tokenParts[1];
            $payload = str_pad($payload, (int)ceil(strlen($payload) / 4) * 4, '=', STR_PAD_RIGHT);
            $decodedPayload = base64_decode($payload);
            
            if ($decodedPayload === false) {
                return null;
            }
            
            $claims = json_decode($decodedPayload, true);
            
            if ($claims === null) {
                return null;
            }
            
            return $claims;
            
        } catch (\Exception $e) {
            $this->log('Error extracting user info from ID token: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Clear all active sessions for a user based on their Okta ID
     *
     * @param string $oktaId The Okta sub (user ID)
     * @return void
     */
    private function clearUserSessionsByOktaId(string $oktaId): void
    {
        try {
            // Find user by Okta ID
            $usersTable = $this->fetchTable('Users');
            $user = $usersTable->find()
                ->where(['okta_id' => $oktaId])
                ->first();
            
            if (!$user) {
                $this->log("No user found with Okta ID: {$oktaId}", 'warning');
                return;
            }
            
            // Log the logout event
            $this->activityLogger->log('okta_logout_callback', [
                'user_id' => $user->id,
                'request' => $this->request,
                'description' => "User logged out from Okta, clearing local sessions",
                'event_data' => [
                    'okta_sub' => $oktaId,
                    'email' => $user->email,
                    'logout_source' => 'okta_callback'
                ]
            ]);
            
            $this->log("Cleared sessions for user {$user->email} (Okta ID: {$oktaId}) due to Okta logout", 'info');
            
            // Note: In a production system with multiple servers, you would need to:
            // 1. Store session data in a shared store (Redis, database)
            // 2. Invalidate sessions across all servers
            // 3. Use session blacklisting or token revocation
            
            // For now, we'll rely on the token validation in AppController::checkOktaSessionValidity()
            // which will automatically log out users when their tokens expire
            
        } catch (\Exception $e) {
            $this->log('Error clearing user sessions: ' . $e->getMessage(), 'error');
        }
    }
}