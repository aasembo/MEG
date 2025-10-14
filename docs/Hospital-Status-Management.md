# Hospital Status Management

## Overview
The MEG Healthcare Platform includes automatic redirection functionality that ensures users cannot access inactive hospitals through their subdomains.

## How It Works

### Hospital Detection Logic
1. **No Subdomain/Parameter**: Defaults to `hospital1` (must be active)
2. **Subdomain Specified**: Uses exact subdomain - no fallback to default
3. **Invalid/Inactive Subdomain**: Redirects to main domain (no fallback)

### Status Verification Process
- **Active Hospital**: Access granted, session established
- **Inactive Hospital**: Immediate redirect to main domain  
- **Non-existent Hospital**: Immediate redirect to main domain
- **Default Hospital Inactive**: Redirect to main domain with error

### Redirection Logic
1. **Subdomain Detection**: System detects hospital from subdomain or query parameter
2. **Main Domain Access**: No subdomain = no hospital context (shows generic MEG Healthcare)
3. **Subdomain Access**: Checks if hospital exists and is active
4. **Strict Enforcement**: Inactive hospitals redirect to main domain (no fallback)
5. **Session Cleanup**: Clears hospital context from user session during redirects

### Development vs Production
- **Development** (`meg.www:8765`): Redirects to `http://meg.www` (port 80)
- **Localhost** (`localhost:8765`): Redirects to `http://localhost:8765`
- **Production** (`subdomain.domain.com`): Redirects to main domain with proper protocol

### Important Notes
- **Main Domain**: `meg.www` runs on default port 80
- **Development Access**: Can use `meg.www:8765` for testing with port parameter
- **Redirect Target**: Always redirects to `http://meg.www` (no port specified)

## Managing Hospital Status

### Using Console Command
```bash
# Check current status and toggle
bin/cake toggle_hospital_status hospital1

# Set specific status
bin/cake toggle_hospital_status hospital1 --status inactive
bin/cake toggle_hospital_status hospital1 --status active
```

### Direct Database Update
```sql
-- Make hospital inactive
UPDATE hospitals SET status = 'inactive' WHERE subdomain = 'hospital1';

-- Make hospital active
UPDATE hospitals SET status = 'active' WHERE subdomain = 'hospital1';
```

## User Experience

### When Hospital Becomes Inactive
1. User accessing `hospital1.domain.com` sees flash message
2. Automatically redirected to main domain
3. Can still access other active hospitals
4. Admin users lose access to that hospital's admin panel

### Flash Messages
- "This hospital is currently inactive. Redirecting to main site."
- "This hospital is no longer active. Redirecting to main site."
- "No active hospitals available. Redirecting to main site."

## Technical Implementation

### Files Modified
- `src/Controller/AppController.php`: Main redirection logic
- `src/Model/Table/HospitalsTable.php`: Active hospital finder
- `src/Command/ToggleHospitalStatusCommand.php`: Management command

### Key Methods
- `setHospitalContext()`: Checks hospital status and handles redirects
- `buildMainDomainUrl()`: Builds appropriate redirect URL
- `findActive()`: Custom finder for active hospitals only

## Testing

### Development Environment (Localhost)
Since localhost doesn't support subdomains, use query parameters to switch hospitals:

```
# Access hospital1 (default)
http://localhost:8765/

# Access hospital2
http://localhost:8765/?hospital=hospital2

# Access hospital3
http://localhost:8765/?hospital=hospital3
```

### Test Scenarios

#### 1. Main Domain Access (No Hospital Context)
```bash
# No hospital specified - no hospital context set
curl -I http://meg.www:8765/
# Expected: 200 OK (shows generic MEG Healthcare branding)
```

#### 2. Specific Active Hospital
```bash
# Access active hospital
curl -I http://meg.www:8765/?hospital=hospital1
# Expected: 200 OK
```

#### 3. Inactive Hospital (NEW BEHAVIOR)
```bash
# Access inactive hospital - should redirect to port 80
curl -I http://meg.www:8765/?hospital=hospital2
# Expected: 302 Redirect to http://meg.www
```

#### 4. Non-existent Hospital (NEW BEHAVIOR)
```bash
# Access non-existent hospital - should redirect to port 80
curl -I http://meg.www:8765/?hospital=hospital999
# Expected: 302 Redirect to http://meg.www
```

### Test Session Cleanup
1. Login to hospital admin panel
2. Mark hospital as inactive in database
3. Refresh any admin page
4. Verify redirect and session cleanup

### Restore Active Status
```bash
bin/cake toggle_hospital_status hospital1 --status active
```

## Security Considerations
- Inactive hospitals cannot be accessed through any route
- Admin panels automatically become inaccessible
- Session data is cleared to prevent stale references
- Redirects preserve user experience while enforcing business rules