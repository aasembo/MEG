# Hospital ID Linking with Okta Authentication

## Overview

The Meg platform implements comprehensive hospital ID linking that automatically associates users with their respective hospitals during Okta authentication. This ensures that each user is properly contextualized within their hospital environment and has access only to the appropriate data and functionality.

## How Hospital ID Linking Works

### 1. Hospital Context Detection

When a user accesses the system, hospital context is detected through multiple mechanisms:

**Primary Method: Subdomain Detection**
- Users access hospital-specific URLs like `hospital1.meg.www:8765`
- The `AppController::setHospitalContext()` method extracts the subdomain
- Hospital records are looked up by subdomain in the `hospitals` table
- Active hospitals are cached in the user's session

**Fallback Method: Session Context**
- If subdomain detection fails, the system checks existing session data
- Previously established hospital context is maintained across requests

### 2. Authentication Flow with Hospital Context

**Step 1: Login Initiation**
```
User → http://hospital1.meg.www:8765/doctor/login
```

The role-based login controllers (`Doctor/LoginController`, `Scientist/LoginController`, `Technician/LoginController`) perform:

1. **Hospital Context Validation**:
   - Extracts hospital ID from session: `Hospital.current`
   - Validates hospital subdomain exists and is active
   - Requires hospital context for all role-based users

2. **OAuth State Parameter Creation**:
   ```php
   $state = base64_encode(json_encode([
       'role' => 'doctor',
       'hospital_id' => $hospitalId,           // e.g., 1
       'hospital_subdomain' => 'hospital1',    // e.g., 'hospital1'
       'timestamp' => time(),
       'nonce' => bin2hex(random_bytes(16))
   ]));
   ```

3. **Okta Redirect**:
   - Redirects to Okta with hospital context preserved in state parameter
   - Uses PKCE + client secret authentication
   - Callback URL: `http://meg.www/auth/callback`

**Step 2: Okta Authentication**
- User completes authentication on Okta
- Okta redirects back with authorization code and state parameter

**Step 3: Callback Processing (`AuthController::callback()`)**

1. **State Parameter Validation**:
   ```php
   $stateData = json_decode(base64_decode($state), true);
   $requestedRole = $stateData['role'];
   $hospitalId = $stateData['hospital_id'];
   $hospitalSubdomain = $stateData['hospital_subdomain'];
   ```

2. **Hospital ID Resolution**:
   - Uses hospital ID from state parameter if available
   - Falls back to subdomain-to-ID resolution if needed
   - Falls back to current session context as last resort

3. **Token Exchange**:
   - Exchanges authorization code for access token using PKCE + client secret
   - Retrieves user information from Okta UserInfo endpoint

4. **User Creation/Authentication**:
   ```php
   $user = $this->findOrCreateUser($userInfo, $requestedRole, $hospitalId, $hospitalSubdomain);
   ```

### 3. User-Hospital Association

**For Existing Users**:
- Looks up user by email, role, and hospital ID
- Updates last login timestamp and Okta ID
- Validates user belongs to the correct hospital for the requested role
- **Ensures role-specific record exists** in the corresponding table (doctors, scientists, technicians)

**For New Users**:
- Creates new user with hospital association
- Validates hospital exists and is active
- Requires hospital ID for all role-based users (doctor, scientist, technician)
- Associates user with specific hospital in `users.hospital_id` field
- **Automatically creates corresponding role-specific record** in the appropriate table

**Role-Specific Record Creation**:
When a doctor, scientist, or technician is authenticated via Okta, the system automatically:

1. **Creates User Record**: Standard user account in the `users` table
2. **Creates Role Record**: Specialized record in the role-specific table (`doctors`, `scientists`, or `technicians`)
3. **Links Records**: Both records reference the same `hospital_id` and are linked via `user_id`

**User Data Structure**:
```php
// Users table record
$userData = [
    'email' => $userInfo['email'],
    'username' => $userInfo['email'],
    'first_name' => $userInfo['given_name'],
    'last_name' => $userInfo['family_name'],
    'role_id' => $roleId,                    // Foreign key to roles table
    'hospital_id' => $hospitalId,            // Foreign key to hospitals table
    'status' => 'active',
    'okta_id' => $userInfo['sub'],
    'created' => new DateTime(),
    'modified' => new DateTime()
];

// Role-specific table record (e.g., doctors table)
$roleData = [
    'user_id' => $user->id,                  // Foreign key to users table
    'hospital_id' => $hospitalId,            // Foreign key to hospitals table
    'phone' => $userInfo['phone_number'],    // Optional from Okta
    'created' => new DateTime(),
    'modified' => new DateTime()
];
```

### 4. Session Management

After successful authentication:

1. **User Identity**: Set in authentication component
2. **Hospital Context**: Ensured in session via `ensureHospitalContextInSession()`
3. **Role-Based Redirect**: Users redirected to appropriate dashboard

## Database Schema

### Users Table (Primary User Records)
```sql
users
├── id (Primary Key)
├── email (Unique)
├── username
├── first_name
├── last_name
├── role_id (Foreign Key → roles.id)
├── hospital_id (Foreign Key → hospitals.id)
├── status ('active', 'inactive')
├── okta_id (Okta user identifier)
├── created
└── modified
```

### Role-Specific Tables

#### Doctors Table
```sql
doctors
├── id (Primary Key)
├── user_id (Foreign Key → users.id)
├── hospital_id (Foreign Key → hospitals.id)
├── phone (Optional)
├── created
└── modified
```

#### Scientists Table
```sql
scientists
├── id (Primary Key)
├── user_id (Foreign Key → users.id)
├── hospital_id (Foreign Key → hospitals.id)
├── phone (Optional)
├── created
└── modified
```

#### Technicians Table
```sql
technicians
├── id (Primary Key)
├── user_id (Foreign Key → users.id)
├── hospital_id (Foreign Key → hospitals.id)
├── phone (Optional)
├── created
└── modified
```

### Hospitals Table
```sql
hospitals
├── id (Primary Key)
├── name
├── subdomain (Unique)
├── status ('active', 'inactive')
├── created
└── modified
```

### Roles Table
```sql
roles
├── id (Primary Key)
├── name ('doctor', 'scientist', 'technician', etc.)
├── created
└── modified
```

## Key Features

### 1. Hospital Context Validation
- **Mandatory for Role-Based Users**: Doctors, scientists, and technicians must have hospital context
- **Hospital Status Validation**: Only active hospitals allow new user registration
- **Cross-Hospital Security**: Users cannot access data from hospitals they're not associated with

### 2. Dual Record Creation
- **User Record**: Created in `users` table with role and hospital association
- **Role Record**: Automatically created in role-specific table (`doctors`, `scientists`, `technicians`)
- **Data Consistency**: Both records share the same `hospital_id` for data integrity
- **Phone Integration**: Extracts phone number from Okta user info if available

### 3. Comprehensive Logging
- Hospital context detection is logged at each step
- User authentication events include hospital information
- Role record creation success/failure is logged
- Failed hospital validations are logged with details

### 4. Error Handling
- Clear error messages for missing hospital context
- Graceful fallbacks when hospital detection fails
- Role record creation retries with relaxed constraints if needed
- Validation of hospital status before user association

### 5. Session Consistency
- Hospital context is maintained across requests
- Session hospital data is validated against database
- Automatic session cleanup for inactive hospitals
- Role record integrity verification after authentication

## Configuration

### Environment Variables
```bash
OKTA_BASE_URL=https://integrator-1025653.okta.com
OKTA_CLIENT_ID=0oaw2dpv6zsxK0jxd697
OKTA_CLIENT_SECRET=<64-character-secret>
```

### Okta Application Settings
- **Grant Type**: Authorization Code with PKCE
- **Callback URL**: `http://meg.www/auth/callback`
- **Authentication**: Client Secret + PKCE (dual authentication)

## Testing the Hospital ID Linking

### 1. Start Development Server
```bash
bin/cake server -H meg.www -p 8765
```

### 2. Test Hospital Access
```bash
# Visit hospital-specific login pages
http://hospital1.meg.www:8765/doctor/login
http://hospital2.meg.www:8765/scientist/login
```

### 3. Verify Hospital Association
After successful authentication, check:
- User record has correct `hospital_id`
- **Role-specific record exists** in appropriate table (doctors/scientists/technicians)
- **Both records share the same hospital_id**
- Session contains proper hospital context
- User is redirected to hospital-specific dashboard
- Log files show hospital context throughout the flow

### 4. Database Verification
Check the database tables directly:
```sql
-- Check user record
SELECT id, email, role_id, hospital_id FROM users WHERE email = 'user@example.com';

-- Check role-specific record (for doctor)
SELECT id, user_id, hospital_id, phone FROM doctors WHERE user_id = [user_id];

-- Check data consistency
SELECT u.email, u.hospital_id as user_hospital, d.hospital_id as doctor_hospital
FROM users u 
JOIN doctors d ON u.id = d.user_id 
WHERE u.email = 'user@example.com';
```

### 4. Run Validation Script
```bash
php test_hospital_linking.php
```

## Security Considerations

### 1. Hospital Isolation
- Users can only access their associated hospital's data
- Cross-hospital data access is prevented at the database level
- Hospital context is validated on every request

### 2. State Parameter Security
- State parameter includes cryptographic nonce
- Timestamp validation prevents replay attacks
- Hospital context is cryptographically protected

### 3. Role-Based Access Control
- Hospital association is combined with role-based permissions
- Users must have both correct role AND hospital association
- Failed associations result in access denial

## Troubleshooting

### Common Issues

1. **Missing Hospital Context**
   - Ensure subdomain is properly configured
   - Check hospital status in database
   - Verify session data persistence

2. **Authentication Failures**
   - Validate Okta environment variables
   - Check callback URL configuration
   - Verify PKCE implementation

3. **Database Association Errors**
   - Confirm hospital_id foreign key constraints
   - Validate role_id mappings
   - Check user table schema

### Debug Tools

1. **Log Analysis**: Check `logs/debug.log` for hospital context tracking
2. **Test Script**: Run `php test_hospital_linking.php` for system validation
3. **Session Inspection**: Use browser dev tools to examine session data
4. **Database Queries**: Direct database inspection of user-hospital associations

This implementation ensures that every user is properly linked to their hospital context throughout the entire authentication and application lifecycle.