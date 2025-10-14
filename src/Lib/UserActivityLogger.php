<?php
declare(strict_types=1);

namespace App\Lib;

use App\Constants\SiteConstants;
use Cake\Http\ServerRequest;
use Cake\ORM\TableRegistry;
use Cake\Log\Log;

/**
 * User Activity Logger Library
 * 
 * Provides a centralized way to log user activities like login, logout, registration, etc.
 * Can be easily extended to add new event types.
 */
class UserActivityLogger
{
    /**
     * UserLogs table instance
     */
    private $userLogsTable;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->userLogsTable = TableRegistry::getTableLocator()->get('UserLogs');
    }
    
    /**
     * Log user activity
     *
     * @param string $eventType Event type (use constants)
     * @param array $options Options array with the following keys:
     *   - user_id: User ID (optional)
     *   - description: Event description (optional)
     *   - event_data: Additional data array (optional)
     *   - hospital_id: Hospital ID (optional)
     *   - role_type: Role type (optional)
     *   - status: Event status (optional, defaults to 'success')
     *   - request: ServerRequest object for IP/User-Agent (optional)
     *   - ip_address: Manual IP address (optional)
     *   - user_agent: Manual user agent (optional)
     * @return bool Success status
     */
    public function log(string $eventType, array $options = []): bool
    {
        try {
            // Extract request data if provided
            $request = $options['request'] ?? null;
            $ipAddress = $options['ip_address'] ?? null;
            $userAgent = $options['user_agent'] ?? null;
            
            if ($request instanceof ServerRequest) {
                $ipAddress = $ipAddress ?? $this->getClientIp($request);
                $userAgent = $userAgent ?? $request->getHeaderLine('User-Agent');
            }
            
            // Prepare log data
            $logData = [
                'user_id' => $options['user_id'] ?? null,
                'event_type' => $eventType,
                'description' => $options['description'] ?? $this->getDefaultDescription($eventType),
                'event_data' => isset($options['event_data']) ? json_encode($options['event_data']) : null,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null, // Truncate to fit DB
                'hospital_id' => $options['hospital_id'] ?? null,
                'role_type' => $options['role_type'] ?? null,
                'status' => $options['status'] ?? SiteConstants::ACTIVITY_STATUS_SUCCESS,
                'created' => new \DateTime(),
            ];
            
            // Create and save log entry
            $userLog = $this->userLogsTable->newEntity($logData);
            $result = $this->userLogsTable->save($userLog);
            
            if ($result) {
                Log::debug("User activity logged: {$eventType} for user " . ($options['user_id'] ?? 'anonymous'));
                return true;
            } else {
                Log::error("Failed to save user activity log: " . json_encode($userLog->getErrors()));
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error("Error logging user activity: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log user login event
     *
     * @param int|null $userId User ID
     * @param array $options Additional options
     * @return bool Success status
     */
    public function logLogin(?int $userId, array $options = []): bool
    {
        $options['user_id'] = $userId;
        $options['description'] = $options['description'] ?? 'User logged in successfully';
        
        return $this->log(SiteConstants::EVENT_USER_LOGIN, $options);
    }
    
    /**
     * Log user logout event
     *
     * @param int|null $userId User ID
     * @param array $options Additional options
     * @return bool Success status
     */
    public function logLogout(?int $userId, array $options = []): bool
    {
        $options['user_id'] = $userId;
        $options['description'] = $options['description'] ?? 'User logged out successfully';
        
        return $this->log(SiteConstants::EVENT_USER_LOGOUT, $options);
    }
    
    /**
     * Log user registration event
     *
     * @param int|null $userId User ID
     * @param array $options Additional options
     * @return bool Success status
     */
    public function logRegistration(?int $userId, array $options = []): bool
    {
        $options['user_id'] = $userId;
        $options['description'] = $options['description'] ?? 'New user registered';
        
        return $this->log(SiteConstants::EVENT_USER_CREATED, $options);
    }
    
    /**
     * Log failed login attempt
     *
     * @param string|null $email Email attempted
     * @param array $options Additional options
     * @return bool Success status
     */
    public function logLoginFailed(?string $email, array $options = []): bool
    {
        $options['status'] = SiteConstants::ACTIVITY_STATUS_FAILED;
        $options['description'] = $options['description'] ?? 'Login attempt failed';
        $options['event_data'] = array_merge($options['event_data'] ?? [], [
            'attempted_email' => $email
        ]);
        
        return $this->log(SiteConstants::EVENT_AUTH_FAILURE, $options);
    }
    
    /**
     * Log Okta authentication event
     *
     * @param int|null $userId User ID
     * @param array $options Additional options
     * @return bool Success status
     */
    public function logOktaAuth(?int $userId, array $options = []): bool
    {
        $options['user_id'] = $userId;
        $options['description'] = $options['description'] ?? 'User authenticated via Okta';
        
        return $this->log(SiteConstants::EVENT_AUTH_OKTA_SUCCESS, $options);
    }
    
    /**
     * Log Okta logout event
     *
     * @param int|null $userId User ID
     * @param array $options Additional options
     * @return bool Success status
     */
    public function logOktaLogout(?int $userId, array $options = []): bool
    {
        $options['user_id'] = $userId;
        $options['description'] = $options['description'] ?? 'User logged out from Okta';
        
        return $this->log(SiteConstants::EVENT_USER_LOGOUT, $options);
    }
    
    /**
     * Log role change event
     *
     * @param int|null $userId User ID
     * @param string $fromRole Previous role
     * @param string $toRole New role
     * @param array $options Additional options
     * @return bool Success status
     */
    public function logRoleChange(?int $userId, string $fromRole, string $toRole, array $options = []): bool
    {
        $options['user_id'] = $userId;
        $options['description'] = $options['description'] ?? "Role changed from {$fromRole} to {$toRole}";
        $options['role_type'] = $toRole;
        $options['event_data'] = array_merge($options['event_data'] ?? [], [
            'from_role' => $fromRole,
            'to_role' => $toRole
        ]);
        
        return $this->log(SiteConstants::EVENT_ROLE_ASSIGNED, $options);
    }
    
    /**
     * Log hospital change event
     *
     * @param int|null $userId User ID
     * @param int|null $fromHospitalId Previous hospital ID
     * @param int|null $toHospitalId New hospital ID
     * @param array $options Additional options
     * @return bool Success status
     */
    public function logHospitalChange(?int $userId, ?int $fromHospitalId, ?int $toHospitalId, array $options = []): bool
    {
        $options['user_id'] = $userId;
        $options['hospital_id'] = $toHospitalId;
        $options['description'] = $options['description'] ?? "Hospital context changed";
        $options['event_data'] = array_merge($options['event_data'] ?? [], [
            'from_hospital_id' => $fromHospitalId,
            'to_hospital_id' => $toHospitalId
        ]);
        
        return $this->log(SiteConstants::EVENT_HOSPITAL_UPDATED, $options);
    }
    
    /**
     * Log access denied event
     *
     * @param int|null $userId User ID
     * @param string $resource Resource that was denied access to
     * @param array $options Additional options
     * @return bool Success status
     */
    public function logAccessDenied(?int $userId, string $resource, array $options = []): bool
    {
        $options['user_id'] = $userId;
        $options['status'] = SiteConstants::ACTIVITY_STATUS_FAILED;
        $options['description'] = $options['description'] ?? "Access denied to {$resource}";
        $options['event_data'] = array_merge($options['event_data'] ?? [], [
            'denied_resource' => $resource
        ]);
        
        return $this->log(SiteConstants::EVENT_UNAUTHORIZED_ACCESS_ATTEMPT, $options);
    }
    
    /**
     * Get default description for event type
     *
     * @param string $eventType Event type
     * @return string Default description
     */
    private function getDefaultDescription(string $eventType): string
    {
        $descriptions = [
            SiteConstants::EVENT_USER_LOGIN => 'User logged in',
            SiteConstants::EVENT_USER_LOGOUT => 'User logged out',
            SiteConstants::EVENT_USER_CREATED => 'User registered',
            SiteConstants::EVENT_AUTH_FAILURE => 'Login attempt failed',
            SiteConstants::EVENT_USER_PASSWORD_CHANGED => 'Password changed',
            SiteConstants::EVENT_USER_UPDATED => 'Profile updated',
            SiteConstants::EVENT_ROLE_ASSIGNED => 'Role changed',
            SiteConstants::EVENT_HOSPITAL_UPDATED => 'Hospital context changed',
            SiteConstants::EVENT_UNAUTHORIZED_ACCESS_ATTEMPT => 'Access denied',
            SiteConstants::EVENT_SESSION_EXPIRED => 'Session expired',
            SiteConstants::EVENT_AUTH_OKTA_SUCCESS => 'Okta authentication',
        ];
        
        return $descriptions[$eventType] ?? 'User activity';
    }
    
    /**
     * Get client IP address from request
     *
     * @param ServerRequest $request Request object
     * @return string|null Client IP address
     */
    private function getClientIp(ServerRequest $request): ?string
    {
        // Check for IP from various headers (for proxies, load balancers, etc.)
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($ipHeaders as $header) {
            $ip = $request->getEnv($header);
            if (!empty($ip) && $ip !== 'unknown') {
                // Handle comma-separated IPs (X-Forwarded-For can have multiple IPs)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
                
                // Also accept private range IPs for local development
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Get recent user activities
     *
     * @param int|null $userId User ID (null for all users)
     * @param int $limit Number of records to return
     * @param array $eventTypes Event types to filter by
     * @return array User log records
     */
    public function getRecentActivities(?int $userId = null, int $limit = 50, array $eventTypes = []): array
    {
        $query = $this->userLogsTable->find()
            ->contain(['Users', 'Hospitals'])
            ->order(['UserLogs.created' => 'DESC'])
            ->limit($limit);
        
        if ($userId !== null) {
            $query->where(['UserLogs.user_id' => $userId]);
        }
        
        if (!empty($eventTypes)) {
            $query->where(['UserLogs.event_type IN' => $eventTypes]);
        }
        
        return $query->toArray();
    }
    
    /**
     * Get activity statistics
     *
     * @param int|null $userId User ID (null for all users)
     * @param \DateTime|null $since Date to count from
     * @return array Statistics array
     */
    public function getActivityStats(?int $userId = null, ?\DateTime $since = null): array
    {
        $query = $this->userLogsTable->find();
        
        if ($userId !== null) {
            $query->where(['user_id' => $userId]);
        }
        
        if ($since !== null) {
            $query->where(['created >=' => $since]);
        }
        
        $stats = $query
            ->select([
                'event_type',
                'count' => $query->func()->count('*')
            ])
            ->group(['event_type'])
            ->toArray();
        
        $result = [];
        foreach ($stats as $stat) {
            $result[$stat->event_type] = $stat->count;
        }
        
        return $result;
    }
}