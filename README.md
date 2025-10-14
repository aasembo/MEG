# MEG Healthcare Platform

A comprehensive application for magnetoencephalography (MEG) healthcare data management with role-based authentication via Okta.



## Features- **User Management**: Complete CRUD operations for users with role-based access control

- **Hospital Management**: Full hospital management system with filtering and status management

- **Role-Based Access Control**: Separate dashboards for doctors, nurses, scientists, patients, and technicians- **Authentication**: Secure authentication system using CakePHP Authentication plugin

- **Okta Authentication**: Secure OAuth2 integration for enterprise-grade authentication- **Role-Based Access**: Super users, Admins, and regular users with appropriate permissions

- **Admin Panel**: Comprehensive administration interface with real-time statistics- **Specialized User Records**: Automatic creation of corresponding records in specialized tables:

- **Hospital Management**: Multi-hospital support with user analytics  - **Doctors**: When a user is created with 'Doctor' role, a corresponding record is created in the `doctors` table

- **Responsive Design**: Bootstrap 5-powered frontend with custom theming  - **Nurses**: When a user is created with 'Nurse' role, a corresponding record is created in the `nurses` table

- **Real-time Analytics**: Dynamic dashboard with live statistics and user counting  - **Scientists**: When a user is created with 'Scientist' role, a corresponding record is created in the `scientists` table

  - **Patients**: When a user is created with 'Patient' role, a corresponding record is created in the `patients` table

## Technology Stack  - **Technicians**: When a user is created with 'Technician' role, a corresponding record is created in the `technicians` table

- **Advanced Filtering**: Comprehensive filtering system for both Users and Hospitals sections

- **Backend**: Modern PHP Framework- **Responsive Design**: Bootstrap 5.3.2 with custom pink theme and Font Awesome icons

- **Database**: MySQL 8.0+

- **Authentication**: Okta OAuth2 (foxworth42/oauth2-okta)## Specialized User Records System

- **Frontend**: Bootstrap 5.3.2, Font Awesome 6.4.0, Custom CSS

- **Development**: Composer, PHP 8.1+The system automatically manages specialized records when creating, editing, or deleting users:



## Prerequisites### Create Operation

- When a user is created with a specialized role (doctor, nurse, scientist, patient, technician), a corresponding record is automatically created in the appropriate specialized table

- PHP 8.1 or higher- Required fields are populated with sensible defaults where necessary

- MySQL 8.0 or higher

- Composer### Update Operation  

- Okta Developer Account (for authentication setup)- When a user's role is changed, the old specialized record is deleted and a new one is created for the new role

- When other user details are updated (like hospital assignment), the corresponding specialized record is also updated

## Installation

### Delete Operation

1. **Clone the repository**:- When a user is deleted, their corresponding specialized record is automatically cleaned up

   ```bash

   git clone <repository-url>### Role Management

   cd MEGThe system includes the following specialized roles:

   ```- **Doctor** (type: 'doctor') → Creates records in `doctors` table

- **Nurse** (type: 'nurse') → Creates records in `nurses` table  

2. **Install dependencies**:- **Scientist** (type: 'scientist') → Creates records in `scientists` table

   ```bash- **Patient** (type: 'patient') → Creates records in `patients` table

   composer install- **Technician** (type: 'technician') → Creates records in `technicians` table

   ```

![Build Status](https://github.com/cakephp/app/actions/workflows/ci.yml/badge.svg?branch=5.x)

3. **Database Setup**:[![Total Downloads](https://img.shields.io/packagist/dt/cakephp/app.svg?style=flat-square)](https://packagist.org/packages/cakephp/app)

   ```bash[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat-square)](https://github.com/phpstan/phpstan)

   # Create database
   mysql -u root -p
   
   CREATE DATABASE meg;

A comprehensive healthcare application with a complete admin panel accessible via `/admin` route. Built for managing application data and administrative tasks.

   ```## Features



4. **Environment Configuration**:- **Admin Panel**: Full administrative interface accessible via `/admin`

   ```bash- **Authentication System**: Secure login system with password hashing

   # Copy and edit the environment file- **User Role Management**: Support for 'super', 'admin', and 'user' roles

   cp config/.env.example config/.env- **MySQL Database**: Configured for MySQL database 'meg'

   ```- **Responsive Design**: Clean and modern admin interface

- **Modern Framework**: Latest framework with modern PHP features

5. **Configure Okta**:

   - Create an Okta application in your Okta Developer Console## Authentication

   - Update `config/.env` with your Okta credentials:

   ```bashThe admin panel is protected by an authentication system that requires:

   export OKTA_CLIENT_ID="your_okta_client_id_here"- Valid email and password

   export OKTA_CLIENT_SECRET="your_okta_client_secret_here"- User type must be 'super' to access admin panel

   export OKTA_BASE_URL="https://your-domain.okta.com"- Active user status

   export OKTA_REDIRECT_URI="http://localhost:8765/auth/callback"

   ```### Creating Admin Users



6. **Database Migration** (if available):To create a super admin user, use the built-in command:

   ```bash

   bin/cake migrations migrate```bash

   ```bin/cake create_super_user --email=admin@example.com --password=your_password

```

## Running the Application

### Login Credentials

### Development Server

For testing purposes, you can create a super user with:

Start the CakePHP development server:- Email: admin@example.com

- Password: password123

```bash- Type: super

# Default server (localhost:8765)

bin/cake server## Quick Start



# Custom host and port### Prerequisites

bin/cake server -H 0.0.0.0 -p 8765- PHP 8.1 or higher

```- Composer

- MySQL database named 'meg'

### Available Tasks

### Installation

The project includes predefined VS Code tasks:

1. Clone or download this project

1. **Start CakePHP Development Server**: Default localhost setup2. Install dependencies:

2. **Start CakePHP Development Server on meg.www**: Custom domain setup```bash

composer install

## Application Structure```



### Frontend Routes3. Configure database in `config/app_local.php`:

```php

- `/` - Homepage with role-based login options'Datasources' => [

- `/auth/login/{userType}` - Okta authentication for specific user roles    'default' => [

- `/auth/callback` - OAuth callback handler        'className' => 'Cake\Database\Connection',

- `/auth/logout` - Logout functionality        'driver' => 'Cake\Database\Driver\Mysql',

- `/dashboard/{role}` - Role-specific dashboards        'host' => 'localhost',

        'username' => 'root',

### Admin Routes        'password' => 'Capital@143#',

        'database' => 'meg',

- `/admin` - Admin dashboard with real-time statistics    ],

- `/admin/login` - Admin authentication],

- `/admin/hospitals` - Hospital management with user analytics```

- `/admin/users` - User management

## Deployment

### User Roles

### Production Deployment (meg.www)

1. **Doctors**: Patient records, medical histories, treatment plansThe application is configured to run on `meg.www` domain:

2. **Nurses**: Patient vitals, record updates, care coordination- Web server document root should point to: `webroot/`

3. **Scientists**: Research data analysis, study management, reports- Database runs on `localhost`

4. **Patients**: Medical records, test results, appointment history- Application accessible at: `http://meg.www`

5. **Technicians**: Equipment maintenance, device calibration, technical systems- Admin panel accessible at: `http://meg.www/admin`

6. **Admins**: System management and configuration

### Development Server

## DevelopmentFor local development, you can use the built-in server:

```bash

### File Structurebin/cake server -H localhost -p 8765

# Then access: http://localhost:8765

``````

├── src/

│   ├── Controller/### Access Points

│   │   ├── Admin/           # Admin panel controllers

│   │   ├── AuthController.php   # Okta authentication- **Frontend**: `http://meg.www`

│   │   └── PagesController.php  # Frontend pages- **Admin Panel**: `http://meg.www/admin`

│   ├── Model/

│   └── View/## Admin Panel

├── templates/

│   ├── Admin/               # Admin panel templatesThe admin panel provides:

│   ├── Pages/              # Frontend templates- Dashboard with overview and quick actions

│   └── layout/             # Layout files- Clean navigation structure

├── webroot/- Responsive design that works on all devices

│   ├── css/                # Custom stylesheets- Extensible architecture for adding new admin features

│   └── js/                 # Frontend JavaScript

└── config/                 # Configuration files### Admin Routes

```

All admin routes are prefixed with `/admin`:

### CSS Customization- `/admin` - Dashboard home (requires authentication)

- `/admin/login` - Login page

The application uses a custom pink theme (`#e91e63`) with responsive design. Main stylesheets:- `/admin/logout` - Logout action

- `/admin/dashboard` - Main dashboard

- `webroot/css/frontend.css` - Frontend styling with animations and responsive design- Additional admin controllers can be added following the same pattern

- Bootstrap 5.3.2 for base styling

- Font Awesome 6.4.0 for icons## Development



### JavaScript Features### Adding New Admin Controllers



- Smooth scrolling and animations1. Create controller in `src/Controller/Admin/`

- Interactive login cards with ripple effects2. Extend from `AppController`

- Responsive navigation with scroll effects3. Set layout to 'admin' in `initialize()` method

- Stats counter animations4. Create corresponding templates in `templates/Admin/`

- Toast notifications

### Database Configuration

## Configuration

The application is configured for MySQL database:

### Database- Database: `meg`

- Username: `root`

Update `config/.env` with your database credentials:- Password: `Capital@143#`

- Host: `localhost`

```bash

export DATABASE_URL="mysql://root:password@localhost/meg?encoding=utf8&timezone=UTC&cacheMetadata=true&quoteIdentifiers=false&persistent=false"### Authentication Requirements

```

Users must meet the following criteria to access the admin panel:

### Okta Setup- Must have a role with `roles.type='super'`

- Must have valid `email` and `password`

1. Log into your Okta Developer Console- Status must be 'active'

2. Create a new Web Application- System uses `users.role_id` to link to the super role

3. Set the redirect URI to: `http://localhost:8765/auth/callback`

4. Note down the Client ID, Client Secret, and your Okta domainThe `create_super_user` command automatically creates the 'super' role if it doesn't exist.

5. Update the `.env` file with these values

## File Structure

### Security

```

- Update `SECURITY_SALT` in `config/.env`src/Controller/Admin/     # Admin controllers

- Ensure `.env` file is not committed to version controltemplates/Admin/          # Admin view templates

- Configure appropriate CORS settings for productiontemplates/layout/admin.php # Admin layout template

config/routes.php         # Routing configuration

## Deploymentconfig/app_local.php      # Local database configuration

```

### Production Setup

## Framework Information

1. **Environment Variables**: Set production environment variables

2. **Database**: Configure production database settings

3. **Security**: Update security salt and disable debug mode

4. **Okta**: Update Okta redirect URIs for production domain## Layout

5. **Web Server**: Configure Apache/Nginx for CakePHP

The admin panel uses custom styling with a modern, clean design. The main application uses [Milligram](https://milligram.io/) minimalist CSS framework, which can be replaced with any other library or custom styles.

### Performance

- Enable OPcache for PHP
- Configure MySQL query caching
- Use CDN for static assets
- Enable gzip compression

## Troubleshooting

### Common Issues

1. **Okta Authentication Errors**:
   - Verify client ID and secret
   - Check redirect URI configuration
   - Ensure Okta domain is correct

2. **Database Connection**:
   - Verify MySQL service is running
   - Check database credentials in `.env`
   - Ensure database exists

3. **File Permissions**:
   - Ensure `tmp/` and `logs/` directories are writable
   - Set appropriate permissions for cache directories

### Debug Mode

Enable debug mode in `config/.env`:

```bash
export DEBUG="true"
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions:

- Check the CakePHP documentation: https://book.cakephp.org/5/
- Review Okta OAuth2 documentation: https://developer.okta.com/
- Create an issue in the project repository