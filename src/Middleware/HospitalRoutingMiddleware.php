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

/**
 * Hospital Routing Middleware
 * 
 * Handles hospital identification and context setting based on subdomain.
 * For admin routes: restricts access to hospital-specific admins.
 * For no subdomain: defaults to hospital1 for admin access.
 */
class HospitalRoutingMiddleware implements MiddlewareInterface
{
    /**
     * Process the request and set hospital context
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Server\RequestHandlerInterface $handler The handler.
     * @return \Psr\Http\Message\ResponseInterface The response.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get the host and extract subdomain
        $host = $request->getUri()->getHost();
        $subdomain = $this->extractSubdomain($host);
        
        // Get route parameters
        $params = $request->getAttribute('params', []);
        $prefix = $params['prefix'] ?? null;
        $controller = $params['controller'] ?? null;
        $action = $params['action'] ?? null;
        
        // Log for debugging
        error_log("HospitalRoutingMiddleware: Host={$host}, Subdomain=" . ($subdomain ?: 'NONE') . ", Prefix={$prefix}");
        
        // Set hospital context
        $hospital = null;
        $hospitalId = null;
        
        if ($subdomain) {
            // Look up hospital by subdomain
            $hospitalsTable = TableRegistry::getTableLocator()->get('Hospitals');
            $hospital = $hospitalsTable->find()
                ->where(['subdomain' => $subdomain, 'status' => SiteConstants::HOSPITAL_STATUS_ACTIVE])
                ->first();
                
            if ($hospital) {
                $hospitalId = $hospital->id;
                error_log("Found hospital for subdomain '{$subdomain}': {$hospital->name} (ID: {$hospitalId})");
            } else {
                error_log("No active hospital found for subdomain '{$subdomain}'");
            }
        } else {
            $hospitalsTable = TableRegistry::getTableLocator()->get('Hospitals');
            $hospital = $hospitalsTable->find()
                ->where(['subdomain' => 'hospital1', 'status' => SiteConstants::HOSPITAL_STATUS_ACTIVE])
                ->first();
                
            if ($hospital) {
                $hospitalId = $hospital->id;
                error_log("No subdomain detected, defaulting to hospital1 for admin access: {$hospital->name} (ID: {$hospitalId})");
            }
        }
        
        // Add hospital context to request attributes
        $request = $request->withAttribute('hospital_context', $hospital);
        $request = $request->withAttribute('hospital_subdomain', $subdomain);
        $request = $request->withAttribute('hospital_id', $hospitalId);
        
        // For admin routes, enforce hospital-specific access
        if ($prefix === 'Admin' && $controller === 'Login' && $action !== 'logout') {
            // Allow login page to be accessed, but we'll validate hospital context during authentication
            error_log("Admin login page accessed with hospital context: " . ($hospital ? $hospital->name : 'NONE'));
        } elseif ($prefix === 'Admin' && $controller !== 'Login') {
            // For other admin pages, validate hospital context exists
            if (!$hospital) {
                error_log("Admin route accessed without valid hospital context - redirecting to login");
                // Redirect to admin login with error
                $response = new Response();
                return $response->withStatus(302)->withHeader('Location', '/admin/login?error=hospital_required');
            }
        }
        
        return $handler->handle($request);
    }
    
    /**
     * Extract subdomain from host
     *
     * @param string $host The host name
     * @return string|null The subdomain or null if none
     */
    private function extractSubdomain(string $host): ?string
    {
        // Handle localhost and development scenarios
        if (in_array($host, ['localhost', '127.0.0.1', 'meg.www'])) {
            return null;
        }
        
        // Extract subdomain from host like "hospital1.meg.com"
        $parts = explode('.', $host);
        if (count($parts) >= 3) {
            return $parts[0]; // Return the first part as subdomain
        }
        
        // For development, check if host is like "hospital1.localhost"
        if (count($parts) === 2 && $parts[1] === 'localhost') {
            return $parts[0];
        }
        
        return null;
    }
}