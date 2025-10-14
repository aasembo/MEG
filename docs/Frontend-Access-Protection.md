# Frontend Access Protection

## Overview
The MEG application now prevents authenticated users from accessing the frontend homepage. Instead, authenticated users are automatically redirected to their appropriate role-specific dashboards.

## Implementation

### PagesController Modifications
The `PagesController::home()` method now includes authentication checking:

1. **Authentication Check**: Verifies if a user is currently authenticated
2. **Role Detection**: Loads the user's role type from the database
3. **Automatic Redirect**: Redirects authenticated users to their role-specific dashboard
4. **Activity Logging**: Logs frontend access attempts by authenticated users for security tracking

### Behavior

#### Non-Authenticated Users
- Can access the frontend homepage (`/` and `/home`)
- See the public interface with role selection and statistics
- Can navigate to role-specific login pages

#### Authenticated Users
- **Cannot** access the frontend homepage
- Are automatically redirected to their role-specific dashboard:
  - **Doctors** → `/doctor/dashboard`
  - **Scientists** → `/scientist/dashboard`
  - **Technicians** → `/technician/dashboard`
  - **Administrators** → `/admin/dashboard`
  - **Super Users** → `/system/dashboard`

### Security Features

#### Activity Logging
When an authenticated user attempts to access the frontend, the system:
- Logs the attempt as an `ACCESS_DENIED` event
- Records the attempted URL and redirect reason
- Tracks user ID, role type, IP address, and timestamp
- Marks the event with `WARNING` status for security monitoring

#### Fallback Protection
- If a user's role is not recognized, they are redirected to logout
- All redirects are logged for audit purposes
- No sensitive information is exposed during the redirect process

## Code Changes

### Files Modified
1. `src/Controller/PagesController.php`
   - Added authentication checking in `home()` method
   - Added `redirectToRoleDashboard()` method
   - Integrated UserActivityLogger for security tracking

### New Dependencies
- `App\Lib\UserActivityLogger` - For logging frontend access attempts

## Testing

### Automated Testing
Run the test command to verify protection is active:
```bash
bin/cake test_frontend_protection
```

### Manual Testing

#### Test Non-Authenticated Access
```bash
curl -I http://meg.www/
# Should return 200 OK and show frontend
```

#### Test Authenticated Access
1. Log in as any role through the appropriate login page
2. Navigate to `/` or `/home`
3. Should be automatically redirected to role dashboard
4. Check logs for the security event

## Integration Points

### Existing Authentication Flow
- **Login Controllers**: Continue to redirect to dashboards after successful authentication
- **Logout Flow**: Users are redirected to homepage, which shows frontend (correct behavior)
- **Error Handling**: Authentication errors redirect to homepage (correct behavior)

### Activity Logging
Frontend access attempts by authenticated users are logged with:
- Event Type: `access_denied`
- Status: `warning`
- Description: Details about the attempted access and redirect
- Event Data: Attempted URL and redirect reason

## Security Benefits
1. **Prevents Information Disclosure**: Authenticated users cannot see public statistics or role information
2. **Enforces Role Separation**: Users are immediately directed to their authorized areas
3. **Audit Trail**: All frontend access attempts by authenticated users are logged
4. **Consistent User Experience**: Users always land in their appropriate work environment

## Future Enhancements
- Dashboard for monitoring frontend access attempts by authenticated users
- Automated alerts for suspicious access patterns
- Enhanced redirect logic for complex role hierarchies
- Integration with external security monitoring systems