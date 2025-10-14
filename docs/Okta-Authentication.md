# OpenID Connect Authentication Integration

## Overview

The system has been updated to use pure OpenID Connect authentication with Okta for doctors, scientists, and technicians. Generic login forms have been removed and replaced with automatic redirects to Okta for authentication. No third-party SDK dependencies are used - the implementation uses standard OpenID Connect protocols directly.

## How It Works

### 1. Role-Based Login Flow

- When users click login buttons for Doctor/Scientist/Technician roles, they are redirected to Okta
- Each role has its own login controller that builds role-specific OpenID Connect authorization URLs
- The state parameter includes the requested role for callback processing

### 2. OpenID Connect Flow with Hospital Context

1. **Initial Redirect**: User clicks role-specific login → System captures hospital context (ID and subdomain) → Redirected to Okta OpenID Connect authorization endpoint with role and hospital data in state parameter
2. **Okta Authentication**: User authenticates with their Okta credentials  
3. **Callback Processing**: Okta redirects back to `/auth/callback` with authorization code
4. **Token Exchange**: System exchanges code for access token using OpenID Connect token endpoint
5. **User Info Retrieval**: System gets user profile from OpenID Connect UserInfo endpoint
6. **User Creation/Login**: System finds existing user or creates new user with requested role and hospital assignment
7. **Dashboard Redirect**: User is logged in and redirected to appropriate role dashboard

### 3. User Management with Hospital Context

- **Existing Users**: If email exists with correct role and hospital → Login successful
- **New Users**: If email doesn't exist → New user created automatically with requested role and hospital assignment
- **Role Conflicts**: If email exists with different role → Access denied
- **Hospital Conflicts**: If email exists with same role but different hospital → Access denied
- **Hospital Assignment**: Users are automatically assigned to the hospital from which they first logged in

## Files Modified

### Controllers

- `src/Controller/AuthController.php` - Main OpenID Connect callback handler (no SDK dependencies)
- `src/Controller/Doctor/LoginController.php` - Doctor OpenID Connect redirect
- `src/Controller/Scientist/LoginController.php` - Scientist OpenID Connect redirect  
- `src/Controller/Technician/LoginController.php` - Technician OpenID Connect redirect

### Configuration

- `config/app.php` - Added Okta configuration section
- `config/routes.php` - Added `/auth/callback` route
- `.env.example` - Added Okta environment variables

### Templates

- Removed generic login forms from all role-specific login templates
- Updated home page to show only Doctor/Scientist/Technician login options

### Dependencies

- **Removed**: `foxworth42/oauth2-okta` package
- **Uses**: Pure OpenID Connect implementation with cURL for HTTP requests

## Configuration Required

### Environment Variables

Add these to your `.env` file:

```bash
OKTA_BASE_URL=https://your-okta-domain.okta.com
OKTA_CLIENT_ID=your-okta-client-id
# OKTA_CLIENT_SECRET not needed when using PKCE
```

### Okta Application Setup

1. Create a new Okta application with these settings:
   - **Application Type**: Web Application (OpenID Connect)
   - **Grant Types**: Authorization Code
   - **Client Authentication**: None (PKCE replaces client secret)
   - **Redirect URI**: `http://meg.www/auth/callback` (no port number needed)
   - **Scopes**: `openid`, `email`, `profile`
   - **Response Mode**: Query (default)
   - **PKCE**: Required

2. Note down only the Client ID for your environment variables (no client secret needed)

## OpenID Connect Endpoints Used

### Authorization Endpoint
- **URL**: `{OKTA_BASE_URL}/oauth2/default/v1/authorize`
- **Parameters**: `client_id`, `response_type=code`, `scope=openid email profile`, `redirect_uri`, `state`, `response_mode=query`

### Token Endpoint  
- **URL**: `{OKTA_BASE_URL}/oauth2/default/v1/token`
- **Method**: POST
- **Parameters**: `grant_type=authorization_code`, `code`, `redirect_uri`, `client_id`, `code_verifier` (PKCE - no client secret needed)

### UserInfo Endpoint
- **URL**: `{OKTA_BASE_URL}/oauth2/default/v1/userinfo`
- **Method**: GET
- **Headers**: `Authorization: Bearer {access_token}`

## URL Structure

### Login URLs
- Doctor Login: `/doctor/login` → Redirects to Okta
- Scientist Login: `/scientist/login` → Redirects to Okta  
- Technician Login: `/technician/login` → Redirects to Okta

### Callback URL
- Auth Callback: `/auth/callback` → Processes Okta response

### Dashboard URLs (post-login)
- Doctor Dashboard: `/doctor/dashboard`
- Scientist Dashboard: `/scientist/dashboard`
- Technician Dashboard: `/technician/dashboard`

## Security Features

- **State Parameter Validation**: Prevents CSRF attacks using cryptographically secure random nonces
- **Role-Based Access Control**: Users can only access roles they're authorized for
- **Hospital-Based Access Control**: Users are tied to specific hospitals and cannot access other hospitals
- **Automatic User Creation**: New users are created with appropriate roles and hospital assignments based on OpenID Connect claims
- **Token Validation**: Proper OAuth2/OpenID Connect token exchange and validation
- **No SDK Dependencies**: Pure implementation reduces attack surface and dependency vulnerabilities
- **Secure HTTP Requests**: Uses cURL with SSL verification for all API calls
- **Hospital Context Preservation**: Hospital information is securely passed through the authentication flow

## Hospital Integration

### Hospital Context Detection
- **Subdomain Method**: Hospital identified by subdomain (e.g., `hospital1.meg.www`)
- **Session Method**: Hospital context stored in user session
- **State Parameter**: Hospital ID and subdomain passed through Okta authentication flow

### User-Hospital Assignment
- **First Login**: Users are automatically assigned to the hospital from their first successful login
- **Single Hospital**: Users can only belong to one hospital per role
- **Hospital Validation**: System validates hospital is active before creating user accounts

## Testing

1. Set up Okta application with correct redirect URI
2. Configure environment variables
3. Access role-specific login pages: `/doctor/login`, `/scientist/login`, `/technician/login`
4. Verify redirect to Okta and successful callback processing
5. Confirm user creation and dashboard access

## Troubleshooting

### Common Issues

1. **Invalid Redirect URI**: Ensure Okta app has correct callback URL configured
2. **Configuration Errors**: Check environment variables are set correctly in `.env`
3. **Role Conflicts**: Users trying to access wrong role will be denied
4. **Token Exchange Failures**: Verify Okta client credentials and network connectivity
5. **Missing OpenID Connect Claims**: Check Okta user profile has required email and name fields

### Logs

Check `logs/error.log` for authentication errors and callback processing issues. The system logs detailed information about:
- Token exchange failures with HTTP response codes
- UserInfo endpoint errors
- User creation attempts and validation errors
- OpenID Connect claim processing