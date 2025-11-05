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
 * Ensures users can only access routes for their assigned role.
 * Prevents cross-role access (e.g., scientist accessing technician routes).
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
            Log::warning("Role access denied: User {$user->email} (role: {$user->role_type}) attempted to access {$prefix} route");
            
            // Redirect to appropriate dashboard for their role
            $redirectUrl = $this->getRedirectUrlForRole($user->role_type);
            
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
        if (empty($prefix) || !in_array($prefix, ['Doctor', 'Scientist', 'Technician', 'Admin'])) {
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
        
        // Skip for dashboard index actions to prevent redirect loops
        if ($controller === 'Dashboard' && $action === 'index') {
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
                    $user->role_type = $user->role->type; // Set role_type for consistency
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
                        $user->role_type = $user->role->type; // Set role_type for consistency
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
     * @param object $user The user object
     * @param string|null $prefix The route prefix
     * @return bool True if user has permission
     */
    private function hasRolePermission(object $user, ?string $prefix): bool
    {
        $userRole = $user->role_type ?? null;
        
        if (!$userRole || !$prefix) {
            return false;
        }
        
        // Define role to prefix mapping
        $rolePermissions = [
            SiteConstants::ROLE_TYPE_SCIENTIST => ['Scientist'],
            SiteConstants::ROLE_TYPE_DOCTOR => ['Doctor'],
            SiteConstants::ROLE_TYPE_TECHNICIAN => ['Technician'],
            SiteConstants::ROLE_TYPE_ADMIN => ['Admin', 'Doctor', 'Scientist', 'Technician'], // Admins can access all
            SiteConstants::ROLE_TYPE_SUPER => ['Admin', 'Doctor', 'Scientist', 'Technician', 'System'], // Super admins can access all
        ];
        
        $allowedPrefixes = $rolePermissions[$userRole] ?? [];
        
        return in_array($prefix, $allowedPrefixes);
    }
    
    /**
     * Get appropriate redirect URL for user's role
     *
     * @param string|null $roleType The user's role type
     * @return string The redirect URL
     */
    private function getRedirectUrlForRole(?string $roleType): string
    {
        return match($roleType) {
            SiteConstants::ROLE_TYPE_SCIENTIST => '/scientist/dashboard',
            SiteConstants::ROLE_TYPE_DOCTOR => '/doctor/dashboard',
            SiteConstants::ROLE_TYPE_TECHNICIAN => '/technician/dashboard',
            SiteConstants::ROLE_TYPE_ADMIN => '/admin/dashboard',
            SiteConstants::ROLE_TYPE_SUPER => '/system/dashboard',
            default => '/' // Redirect to home page instead of admin/login
        };
    }
}