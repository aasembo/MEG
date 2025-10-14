# User Activity Logger System

## Overview
The User Activity Logger system provides comprehensive tracking of user activities throughout the MEG application, including authentication events, user actions, and system events.

## Components

### 1. UserActivityLogger Library (`src/Lib/UserActivityLogger.php`)
A centralized library for logging user activities with the following features:

#### Event Types
- `login` - User login events
- `logout` - User logout events
- `register` - User registration events
- `login_failed` - Failed login attempts
- `logout_failed` - Failed logout attempts
- `password_change` - Password change events
- `profile_update` - Profile update events
- `role_change` - Role change events
- `hospital_change` - Hospital context changes
- `access_denied` - Access denied events
- `session_expired` - Session expiration events
- `okta_auth` - Okta authentication events
- `okta_logout` - Okta logout events

#### Status Types
- `success` - Successful events
- `failed` - Failed events
- `error` - Error events
- `warning` - Warning events

#### Key Methods
- `log($eventType, $options)` - General logging method
- `logLogin($userId, $options)` - Log login events
- `logLogout($userId, $options)` - Log logout events
- `logRegistration($userId, $options)` - Log registration events
- `logLoginFailed($email, $options)` - Log failed login attempts
- `logOktaAuth($userId, $options)` - Log Okta authentication
- `logOktaLogout($userId, $options)` - Log Okta logout
- `getRecentActivities($userId, $limit, $eventTypes)` - Retrieve recent activities
- `getActivityStats($userId, $since)` - Get activity statistics

### 2. Database Schema (`user_logs` table)
Created via migration `20251003150147_CreateUserLogs.php`:

```sql
CREATE TABLE user_logs (
    id INTEGER AUTO_INCREMENT PRIMARY KEY,
    user_id INTEGER UNSIGNED,
    event_type VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    event_data TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    hospital_id INTEGER UNSIGNED,
    role_type VARCHAR(50),
    status VARCHAR(20) NOT NULL DEFAULT 'success',
    created DATETIME NOT NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_event_type (event_type),
    INDEX idx_created (created),
    INDEX idx_hospital_id (hospital_id),
    INDEX idx_ip_address (ip_address),
    INDEX idx_status (status),
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id)
);
```

### 3. CakePHP Models
- `UserLogsTable.php` - Table class with validation and associations
- `UserLog.php` - Entity class with accessible fields

### 4. Integration Points

#### AuthController (`src/Controller/AuthController.php`)
- Logs Okta authentication events in `processCallback()`
- Logs user registration events when new users are created
- Logs authentication failures when Okta returns errors

#### Role-Specific Login Controllers
- Doctor: `src/Controller/Doctor/LoginController.php`
- Scientist: `src/Controller/Scientist/LoginController.php`
- Technician: `src/Controller/Technician/LoginController.php`

Each includes:
- Okta logout event logging with session duration tracking
- IP address and user agent capture
- Role-specific event context

## Usage Examples

### Basic Login Event
```php
$logger = new UserActivityLogger();
$logger->logLogin($userId, [
    'role_type' => 'doctor',
    'hospital_id' => 1,
    'request' => $this->request
]);
```

### Failed Login Attempt
```php
$logger->logLoginFailed('user@example.com', [
    'event_data' => ['reason' => 'invalid_credentials'],
    'ip_address' => '192.168.1.100'
]);
```

### Custom Event
```php
$logger->log(UserActivityLogger::EVENT_ROLE_CHANGE, [
    'user_id' => $userId,
    'description' => 'Role changed from scientist to doctor',
    'event_data' => [
        'from_role' => 'scientist',
        'to_role' => 'doctor'
    ]
]);
```

### Retrieving Activity Data
```php
// Get recent activities for a user
$activities = $logger->getRecentActivities($userId, 50);

// Get activity statistics
$stats = $logger->getActivityStats($userId);

// Get login events only
$logins = $logger->getRecentActivities(null, 100, ['login', 'okta_auth']);
```

## Features

### Automatic Data Capture
- **IP Address**: Extracted from request headers with proxy support
- **User Agent**: Browser/client identification
- **Timestamps**: Automatic creation time tracking
- **Session Duration**: Calculated for logout events
- **Hospital Context**: Associated hospital for multi-tenant support
- **Role Context**: User role at time of event

### Extensible Architecture
- Easy to add new event types
- Flexible event data structure (JSON)
- Status tracking for success/failure
- Optional user association (supports anonymous events)

### Performance Optimizations
- Database indexes on commonly queried fields
- Truncated user agent strings to prevent oversized data
- Efficient JSON storage for event data
- Optional foreign key validation

## Security Considerations
- IP address logging for audit trails
- User agent tracking for session security
- Event data is JSON-encoded to prevent injection
- Foreign key constraints maintain data integrity
- No sensitive data stored in event_data field

## Testing
Use the test command to verify functionality:
```bash
bin/cake test_activity_logger
```

This command tests:
- Basic event logging
- Data validation
- Database operations
- Activity retrieval
- Statistics generation

## Future Enhancements
- Real-time activity monitoring dashboard
- Automated security alerts for suspicious patterns
- Activity export functionality
- Advanced filtering and search capabilities
- Activity retention policies
- Integration with external monitoring systems