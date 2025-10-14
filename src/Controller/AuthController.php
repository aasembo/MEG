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
        
        // Debug logging to help troubleshoot configuration
        $this->log('Okta Domain: ' . $this->oktaDomain, 'debug');
        $this->log('Okta Client ID: ' . $this->oktaClientId, 'debug');
        $this->log('Okta Client Secret: ' . ($this->oktaClientSecret ? 'SET' : 'NOT SET'), 'debug');
        
        // Also check environment variables directly
        error_log('ENV OKTA_BASE_URL: ' . ($this->oktaDomain ?? 'NOT SET'));
        error_log('ENV OKTA_CLIENT_ID: ' . ($this->oktaClientId ?? 'NOT SET'));
        error_log('ENV OKTA_CLIENT_SECRET: ' . ($this->oktaClientSecret ? 'SET' : 'NOT SET'));
    }
    
    /**
     * Before filter callback
     * Allow unauthenticated access to callback action
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Allow unauthenticated access to callback action
        $this->Authentication->addUnauthenticatedActions(['callback']);
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
                $this->log("Okta authentication error: {$error}. Description: {$errorDescription}", 'error');
                
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
            $this->log('Okta authentication error: ' . $e->getMessage(), 'error');
            $this->log('Exception trace: ' . $e->getTraceAsString(), 'error');
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
        if (!in_array($requestedRole, ['doctor', 'scientist', 'technician'])) {
            throw new \Exception('Invalid role requested');
        }
        
        // Get code verifier from session
        $codeVerifier = $this->request->getSession()->read('oauth_code_verifier');
        if (!$codeVerifier) {
            throw new \Exception('Missing code verifier in session');
        }
        
        // Build redirect URI to match Okta configuration exactly
        $redirectUri = 'http://meg.www/auth/callback';
        
        // Exchange authorization code for access token using manual HTTP request
        $tokenResponse = $this->exchangeCodeForToken($code, $codeVerifier, $redirectUri);
        
        if (!$tokenResponse || !isset($tokenResponse['access_token'])) {
            throw new \Exception('Failed to get access token from Okta');
        }
        
        // Clear the code verifier from session after use
        $this->request->getSession()->delete('oauth_code_verifier');
        
        // Store ID token in session for logout purposes
        if (isset($tokenResponse['id_token'])) {
            $this->log('=== STORING ID TOKEN ===', 'debug');
            $this->log('ID token found in response, storing in session', 'debug');
            $this->log('ID token length: ' . strlen($tokenResponse['id_token']), 'debug');
            $this->request->getSession()->write('oauth_id_token', $tokenResponse['id_token']);
            
            // Verify it was stored
            $storedToken = $this->request->getSession()->read('oauth_id_token');
            $this->log('Verification - ID token stored successfully: ' . ($storedToken ? 'YES' : 'NO'), 'debug');
            $this->log('=== END STORING ID TOKEN ===', 'debug');
        } else {
            $this->log('No ID token found in token response. Available keys: ' . implode(', ', array_keys($tokenResponse)), 'warning');
        }
        
        // Get user information using the access token
        $userInfo = $this->getUserInfo($tokenResponse['access_token']);
        
        if (!$userInfo) {
            throw new \Exception('Failed to get user information from Okta');
        }
        
        // Find or create user
        $user = $this->findOrCreateUser($userInfo, $requestedRole, $hospitalId, $hospitalSubdomain);
        if (!$user) {
            throw new \Exception('Failed to create or authenticate user');
        }
        
        // Log the user in
        $this->Authentication->setIdentity($user);
        
        // Log Okta authentication event
        $this->activityLogger->logOktaAuth($user->id, [
            'role_type' => $requestedRole,
            'hospital_id' => $hospitalId,
            'request' => $this->request,
            'event_data' => [
                'okta_sub' => $userInfo['sub'] ?? null,
                'hospital_subdomain' => $hospitalSubdomain,
                'authentication_method' => 'okta_openid_connect'
            ]
        ]);
        
        // Verify role-specific record exists
        $hasRoleRecord = $this->verifyRoleRecord($user, $requestedRole);
        if (!$hasRoleRecord) {
            $this->log("Warning: User {$user->email} authenticated but missing {$requestedRole} record", 'warning');
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
        $this->log("User {$userName} ({$user->email}) successfully authenticated for {$requestedRole} role" . ($hospitalInfo ? $hospitalInfo : ''), 'info');
        
        return $this->redirectToRoleDashboard($requestedRole);
    }

    /**
     * Exchange authorization code for access token using PKCE
     *
     * @param string $code Authorization code from Okta
     * @return array|null Token response or null on failure
     */
    private function exchangeCodeForToken($code, $codeVerifier, $redirectUri)
    {
        $this->log('Starting token exchange with code: ' . substr($code, 0, 10) . '...', 'debug');
        $this->log('Code verifier: ' . substr($codeVerifier, 0, 10) . '...', 'debug');
        $this->log('Redirect URI: ' . $redirectUri, 'debug');
        
        $tokenUrl = $this->oktaDomain . '/oauth2/v1/token';
        
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
        
        $this->log('Token request URL: ' . $tokenUrl, 'debug');
        $this->log('Token request data: ' . json_encode($postFields), 'debug');
        $this->log('Redirect URI being sent: ' . $redirectUri, 'debug');
        
        // Debug output for immediate visibility
        error_log('=== OKTA TOKEN EXCHANGE DEBUG ===');
        error_log('Token URL: ' . $tokenUrl);
        error_log('Client ID from config: ' . $this->oktaClientId);
        error_log('Okta Domain from config: ' . $this->oktaDomain);
        error_log('Redirect URI: ' . $redirectUri);
        error_log('POST Fields: ' . json_encode($postFields));
        error_log('=== END DEBUG ===');
        
        // Also write to a file we can easily read
        file_put_contents('/tmp/okta_debug.log', 
            "=== OKTA TOKEN EXCHANGE DEBUG ===\n" .
            "Timestamp: " . date('Y-m-d H:i:s') . "\n" .
            "Token URL: " . $tokenUrl . "\n" .
            "Client ID: " . $this->oktaClientId . "\n" .
            "Okta Domain: " . $this->oktaDomain . "\n" .
            "Redirect URI: " . $redirectUri . "\n" .
            "POST Fields: " . json_encode($postFields, JSON_PRETTY_PRINT) . "\n" .
            "=== END DEBUG ===\n\n", 
            FILE_APPEND
        );
        
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
        
        $this->log('Token response HTTP code: ' . $httpCode, 'debug');
        $this->log('Token response: ' . $responseBody, 'debug');
        
        error_log('Token Response HTTP Code: ' . $httpCode);
        error_log('Token Response: ' . $responseBody);
        
        // Also write response to debug file
        file_put_contents('/tmp/okta_debug.log', 
            "Response HTTP Code: " . $httpCode . "\n" .
            "Response Body: " . $responseBody . "\n" .
            "=== END RESPONSE ===\n\n", 
            FILE_APPEND
        );
        
        if ($httpCode !== 200) {
            $this->log('Token exchange failed with HTTP ' . $httpCode . ': ' . $responseBody, 'error');
            throw new \Exception('Token exchange failed with HTTP ' . $httpCode . ': ' . $responseBody);
        }
        
        $tokenData = json_decode($responseBody, true);
        if (!$tokenData || !isset($tokenData['access_token'])) {
            $this->log('Invalid token response: ' . $responseBody, 'error');
            throw new \Exception('Invalid token response from Okta');
        }
        
        // Log what tokens are included in the response
        $availableTokens = array_keys($tokenData);
        $this->log('Token response contains: ' . implode(', ', $availableTokens), 'debug');
        
        $this->log('Token exchange successful', 'debug');
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
        
        $userInfoEndpoint = $baseUrl . '/oauth2/v1/userinfo';
        
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
            return null;
        }
        
        if ($httpCode !== 200) {
            $this->log("UserInfo request failed. HTTP Code: {$httpCode}, Response: {$responseBody}", 'error');
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
        $this->log("Looking up role ID for role: {$role}", 'debug');
        $roleId = $this->getRoleIdFromName($role);
        if (!$roleId) {
            $this->log("Failed to find role ID for role: {$role}", 'error');
            throw new \Exception("Invalid role: {$role}");
        }
        $this->log("Found role ID: {$roleId} for role: {$role}", 'debug');
        
        // Log hospital context for debugging
        $this->log("Processing user authentication - Email: {$email}, Role: {$role}, Hospital ID: " . ($hospitalId ?: 'NONE') . ", Hospital Subdomain: " . ($hospitalSubdomain ?: 'NONE'), 'info');
        
        // For role-based users (doctor, scientist, technician), hospital ID is required
        if (in_array($role, ['doctor', 'scientist', 'technician']) && !$hospitalId) {
            $this->log("Hospital ID is required for {$role} role but not provided. Email: {$email}", 'error');
            throw new \Exception("Hospital context is required for {$role} access. Please ensure you are accessing from a valid hospital subdomain.");
        }
        
        // Check if user already exists with this email and role
        $conditions = ['email' => $email, 'role_id' => $roleId];
        if ($hospitalId) {
            $conditions['hospital_id'] = $hospitalId;
        }
        
        $this->log("Looking for existing user with conditions: " . json_encode($conditions), 'debug');
        
        $existingUser = $usersTable->find()
            ->where($conditions)
            ->first();
        
        if ($existingUser) {
            $this->log("Found existing user with ID: {$existingUser->id}", 'debug');
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
        $this->log("No existing user found with exact conditions. Checking for email conflicts...", 'debug');
        $userWithConflict = $usersTable->find()
            ->where(['email' => $email])
            ->first();
        
        if ($userWithConflict) {
            $this->log("Found conflicting user with ID: {$userWithConflict->id}, role_id: {$userWithConflict->role_id}, hospital_id: {$userWithConflict->hospital_id}", 'debug');
            // User exists but with different role or hospital
            $this->log("User {$email} exists with different role/hospital. Current role: {$userWithConflict->role_id}, Hospital: {$userWithConflict->hospital_id}. Requested role: {$roleId}, Hospital: {$hospitalId}", 'info');
            
            // For now, update the user to the new role and hospital context
            // This allows users to switch between roles/hospitals
            $userWithConflict->role_id = $roleId;
            if ($hospitalId) {
                $userWithConflict->hospital_id = $hospitalId;
            }
            $userWithConflict->modified = new \DateTime();
            if (isset($userInfo['sub'])) {
                $userWithConflict->okta_id = $userInfo['sub'];
            }
            
            if ($usersTable->save($userWithConflict)) {
                $this->log("Updated existing user {$email} to role {$role} at hospital {$hospitalId}", 'info');
                
                // Ensure role-specific record exists for the updated user
                $roleRecord = $this->createRoleSpecificRecord($userWithConflict, $role, $hospitalId, $userInfo);
                if ($roleRecord) {
                    $this->log("Created {$role} record for updated user {$userWithConflict->id}", 'info');
                } else {
                    $this->log("Warning: Failed to create {$role} record for updated user {$userWithConflict->id}", 'warning');
                }
                
                return $userWithConflict;
            } else {
                $this->log("Failed to update existing user {$email}: " . json_encode($userWithConflict->getErrors()), 'error');
                return null;
            }
        }
        
        // Create new user with the requested role and hospital
        $this->log("No conflicting user found. Creating new user...", 'debug');
        $userData = [
            'email' => $email,
            'username' => $email, // Use email as username
            'first_name' => $userInfo['given_name'] ?? '',
            'last_name' => $userInfo['family_name'] ?? '',
            'role_id' => $roleId,
            'status' => SiteConstants::USER_STATUS_ACTIVE,
            'okta_id' => $userInfo['sub'] ?? null,
            'created' => new \DateTime(),
            'modified' => new \DateTime()
        ];
        
        // Add hospital ID if available (required for role-based users)
        if ($hospitalId) {
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
        } else if (in_array($role, ['doctor', 'scientist', 'technician'])) {
            // This should not happen due to earlier validation, but double-check
            throw new \Exception("Hospital association is required for {$role} users.");
        }
        
        $newUser = $usersTable->newEntity($userData);
        
        $this->log("Attempting to create new user with data: " . json_encode($userData), 'debug');
        
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
            $this->log("Found role ID {$role->id} for role type '{$roleName}'", 'debug');
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
}