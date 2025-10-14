# Cross-Role Access Protection Testing Guide

## Issue Description
Users logged in with one role type can access login pages for other roles, which should not be allowed.

## Expected Behavior
When a user is logged in with any role and tries to access a login page for a different role:
- **Doctor** accessing `/admin/login` → Should be redirected to `/doctor/dashboard`
- **Admin** accessing `/doctor/login` → Should be redirected to `/admin/dashboard`
- **Any role** accessing wrong login page → Should be redirected to their appropriate dashboard

## Protection Implementation

### 1. Authentication Check
All login controllers check if user is already authenticated:
```php
$result = $this->Authentication->getResult();
if ($result && $result->isValid()) {
    // User is authenticated, check their role
}
```

### 2. Role Validation
Each controller checks if the user has the correct role type:
```php
if ($userWithRole->role->type === 'expected_role') {
    // Correct role - redirect to dashboard
} else {
    // Wrong role - redirect to their dashboard
    return $this->redirectToUserDashboard($userWithRole);
}
```

### 3. Activity Logging
Cross-role access attempts are logged as security events with:
- Event type: `access_denied`
- Status: `failed`
- User role vs attempted role
- Full request details

## Testing Procedures

### Manual Testing Steps
1. **Login as Doctor**:
   ```
   - Go to http://meg.www/doctor/login
   - Complete Okta authentication
   - Verify you're on doctor dashboard
   ```

2. **Test Cross-Role Access**:
   ```
   - While logged in as doctor, try to access:
     - http://meg.www/admin/login
     - http://meg.www/scientist/login
     - http://meg.www/technician/login
   ```

3. **Expected Results**:
   ```
   - Should be immediately redirected to /doctor/dashboard
   - Should NOT see any login forms
   - Should see security event logged in user_logs table
   ```

### Browser Testing Considerations
- **Clear browser cache** before testing
- **Disable browser cache** for development
- **Check Network tab** to see actual HTTP responses
- **Look for redirects** (302/301 status codes)

### Command Line Testing
```bash
# Test with curl (replace SESSIONID with actual session cookie)
curl -v -H "Cookie: CAKEPHP=your_session_id" http://meg.www/admin/login

# Should return 302 redirect to appropriate dashboard
```

### Debug Logging
Check `logs/debug.log` for entries like:
```
Admin login page accessed. Authentication result: valid
User role type: doctor
User with doctor role attempted to access admin login page
```

## Troubleshooting

### If Login Pages Are Still Accessible

1. **Check Session State**:
   ```bash
   bin/cake debug_cross_role_access
   ```

2. **Verify Authentication**:
   ```bash
   bin/cake test_cross_role_protection
   ```

3. **Check Browser Network**:
   - Look for 302 redirects
   - Verify session cookies are being sent
   - Check if JavaScript is interfering

4. **Clear All Cache**:
   ```bash
   # Clear CakePHP cache
   bin/cake cache clear
   
   # Clear browser cache completely
   # Or use incognito/private browsing
   ```

### Common Issues

1. **Browser Cache**:
   - Browser showing cached login page
   - **Solution**: Hard refresh (Ctrl+F5) or clear cache

2. **JavaScript Redirects**:
   - Client-side code preventing server redirects
   - **Solution**: Check browser console for errors

3. **Session Issues**:
   - Session not properly maintained
   - **Solution**: Check session configuration and storage

4. **Middleware Order**:
   - Authentication middleware not running first
   - **Solution**: Verify middleware stack in Application.php

## Security Events Logging

When cross-role access is attempted, the system logs:
```json
{
  "event_type": "access_denied",
  "user_id": 123,
  "role_type": "doctor",
  "description": "User with doctor role attempted to access admin login page",
  "event_data": {
    "attempted_url": "/admin/login",
    "user_role": "doctor",
    "attempted_role": "administrator",
    "redirect_reason": "cross_role_access_denied"
  },
  "status": "failed",
  "ip_address": "192.168.1.100",
  "created": "2025-10-03 15:30:00"
}
```

## Monitoring Cross-Role Access Attempts

```bash
# View recent access denied events
bin/cake test_activity_logger

# Check for specific user's attempts
SELECT * FROM user_logs 
WHERE event_type = 'access_denied' 
AND event_data LIKE '%cross_role_access_denied%'
ORDER BY created DESC;
```

## Implementation Status

### ✅ Completed
- Authentication checks in all login controllers
- Role type validation
- Automatic redirects to appropriate dashboards
- Security event logging
- Debug logging for troubleshooting

### 🔧 Enhanced Features
- Activity logging with detailed event data
- Debug commands for testing
- Comprehensive redirect logic
- Cross-role access attempt tracking

The protection is implemented at the controller level, so any access to login pages should trigger the authentication check and redirect logic. If you're still seeing login pages when logged in with different roles, it's likely a browser caching issue or the redirects are happening but not being followed properly.