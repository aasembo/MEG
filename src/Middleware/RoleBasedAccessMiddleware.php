<?php
declare(strict_types=1);

namespace App\Middleware;

use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use App\Constants\SiteConstants;
use Cake\Log\Log;

/**
 * Role-Based Access Control Middleware
 * 
 * IMPORTANT NOTE: Database structure uses users.role_id -> roles.id -> roles.type
 * 
 * STRICT ROLE TO ROUTE MAPPING:
 * - roles.type = 'administrator' -> ONLY /admin/* routes
 * - roles.type = 'doctor' -> ONLY /doctor/* routes  
 * - roles.type = 'technician' -> ONLY /technician/* routes
 * - roles.type = 'scientist' -> ONLY /scientist/* routes
 * - roles.type = 'super' -> ONLY /system/* routes
 * 
 * No cross-role access allowed. Each role is strictly confined to their prefix.
 */
class RoleBasedAccessMiddleware implements MiddlewareInterface
{
    /**
     * Process the request and enforce role-based access control
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The handler.
     * @return \Psr\Http\Message\ResponseInterface The response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get route parameters
        $params = $request->getAttribute('params', []);
        $prefix = $params['prefix'] ?? null;
        $controller = $params['controller'] ?? null;
        $action = $params['action'] ?? null;
        
        // Skip role checking for certain routes
        if ($this->shouldSkipRoleCheck($prefix, $controller, $action)) {
            return $handler->handle($request);
        }
        
        // Get current user from session/authentication
        $user = $this->getCurrentUser($request);
        
        if (!$user) {
            // User not authenticated - let authentication middleware handle this
            return $handler->handle($request);
        }
        
        // Check if user has permission to access this prefix
        if (!$this->hasRolePermission($user, $prefix)) {
            $roleType = $user->role ? $user->role->type : 'unknown';
            Log::warning("Role access denied: User {$user->email} (role: {$roleType}) attempted to access {$prefix} route");
            
            // Redirect to appropriate dashboard for their role
            $redirectUrl = $this->getRedirectUrlForRole($roleType);
            
            $response = new Response();
            return $response->withStatus(302)->withHeader('Location', $redirectUrl);
        }
        
        return $handler->handle($request);
    }
    
    /**
     * Check if role checking should be skipped for this route
     *
     * @param string|null $prefix The route prefix
     * @param string|null $controller The controller name
     * @param string|null $action The action name
     * @return bool True if role checking should be skipped
     */
    private function shouldSkipRoleCheck(?string $prefix, ?string $controller, ?string $action): bool
    { 
        // Skip for non-role-based routes (no prefix or non-role prefixes)
        if (empty($prefix) || !in_array($prefix, ['Doctor', 'Scientist', 'Technician', 'Admin', 'System'])) { 
            return true;
        }
        
        // Skip for auth-related controllers and actions
        if ($controller === 'Login' || $controller === 'Auth') {
            return true;
        }
        
        // Skip for specific auth actions
        if (in_array($action, ['login', 'logout', 'callback', 'authenticate'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get current authenticated user
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request
     * @return object|null The user object or null if not authenticated
     */
    private function getCurrentUser(ServerRequestInterface $request): ?object
    {
        // Try to get user from Authentication component identity first
        $identity = $request->getAttribute('identity');
        if ($identity && method_exists($identity, 'getOriginalData')) {
            $userData = $identity->getOriginalData();
            if ($userData && isset($userData->id)) {
                // Get user with role relationship using correct database structure
                $usersTable = TableRegistry::getTableLocator()->get('Users');
                $user = $usersTable->find()
                    ->contain(['Roles'])
                    ->where(['Users.id' => $userData->id])
                    ->first();
                
                if ($user && $user->role) {
                    // User entity now has role relationship loaded (users.role_id -> roles.id)
                    // Access role type via $user->role->type (from roles table)
                    return $user;
                }
            }
        }
        
        // Try to get user from session
        $session = $request->getAttribute('session');
        if ($session) {
            $userData = $session->read('Auth.User');
            if ($userData) {
                // Convert array to object if necessary
                if (is_array($userData)) {
                    $userData = (object) $userData;
                }
                if (isset($userData->id)) {
                    // Get user with role relationship
                    $usersTable = TableRegistry::getTableLocator()->get('Users');
                    $user = $usersTable->find()
                        ->contain(['Roles'])
                        ->where(['Users.id' => $userData->id])
                        ->first();
                    
                    if ($user && $user->role) {
                        // User entity now has role relationship loaded (users.role_id -> roles.id)
                        // Access role type via $user->role->type (from roles table)
                        return $user;
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Check if user has permission to access the given prefix
     *
     * @param object $user The user object with role relationship loaded
     * @param string|null $prefix The route prefix
     * @return bool True if user has permission
     */
    private function hasRolePermission(object $user, ?string $prefix): bool
    {
        // Get role type from related roles table (users.role_id -> roles.type)
        $userRole = $user->role ? $user->role->type : null;
        
        if (!$userRole || !$prefix) {
            return false;
        }
        
        // STRICT role to prefix mapping - users can ONLY access their specific role routes
        // Database: users.role_id -> roles.id -> roles.type
        $rolePermissions = [
            'scientist' => ['Scientist'],      // roles.type = 'scientist' -> /scientist/* only
            'doctor' => ['Doctor'],            // roles.type = 'doctor' -> /doctor/* only
            'technician' => ['Technician'],    // roles.type = 'technician' -> /technician/* only
            'administrator' => ['Admin'],      // roles.type = 'administrator' -> /admin/* only
            'super' => ['System'],             // roles.type = 'super' -> /system/* only
        ];
        
        $allowedPrefixes = $rolePermissions[$userRole] ?? [];
        
        return in_array($prefix, $allowedPrefixes);
    }
    
    /**
     * Get appropriate redirect URL for user's role
     *
     * @param string|null $roleType The user's role type from roles.type
     * @return string The redirect URL
     */
    private function getRedirectUrlForRole(?string $roleType): string
    {
        return match($roleType) {
            'scientist' => '/scientist/dashboard',       // roles.type = 'scientist'
            'doctor' => '/doctor/dashboard',             // roles.type = 'doctor'
            'technician' => '/technician/dashboard',     // roles.type = 'technician'
            'administrator' => '/admin/dashboard',       // roles.type = 'administrator'
            'super' => '/system/dashboard',              // roles.type = 'super'
            default => '/' // Redirect to home page for unknown roles
        };
    }
}