# Constants Usage Guidelines

## Overview
This document outlines the usage of constants throughout the MEG application to maintain consistency and reduce hardcoded values.

## Constants Location
All application constants are defined in `src/Constants/SiteConstants.php`

## Available Constants

### User Status Constants
- `SiteConstants::USER_STATUS_ACTIVE` - 'active'
- `SiteConstants::USER_STATUS_INACTIVE` - 'inactive'
- `SiteConstants::USER_STATUS_SUSPENDED` - 'suspended'
- `SiteConstants::USER_STATUS_PENDING` - 'pending'

### Role Type Constants
- `SiteConstants::ROLE_TYPE_ADMINISTRATOR` - 'administrator'
- `SiteConstants::ROLE_TYPE_ADMIN` - 'admin'
- `SiteConstants::ROLE_TYPE_SUPER` - 'super'
- `SiteConstants::ROLE_TYPE_DOCTOR` - 'doctor'
- `SiteConstants::ROLE_TYPE_SCIENTIST` - 'scientist'
- `SiteConstants::ROLE_TYPE_TECHNICIAN` - 'technician'
- `SiteConstants::ROLE_TYPE_NURSE` - 'nurse'
- `SiteConstants::ROLE_TYPE_PATIENT` - 'patient'

### Case Status Constants
- `SiteConstants::CASE_STATUS_DRAFT` - 'draft'
- `SiteConstants::CASE_STATUS_ASSIGNED` - 'assigned'
- `SiteConstants::CASE_STATUS_PENDING` - 'pending'
- `SiteConstants::CASE_STATUS_SCHEDULED` - 'scheduled'
- `SiteConstants::CASE_STATUS_IN_PROGRESS` - 'in_progress'
- `SiteConstants::CASE_STATUS_UNDER_REVIEW` - 'under_review'
- `SiteConstants::CASE_STATUS_REVIEW` - 'review'
- `SiteConstants::CASE_STATUS_COMPLETED` - 'completed'
- `SiteConstants::CASE_STATUS_CANCELLED` - 'cancelled'
- `SiteConstants::CASE_STATUS_ON_HOLD` - 'on_hold'

### Priority Constants
- `SiteConstants::PRIORITY_LOW` - 'low'
- `SiteConstants::PRIORITY_MEDIUM` - 'medium'
- `SiteConstants::PRIORITY_HIGH` - 'high'
- `SiteConstants::PRIORITY_URGENT` - 'urgent'

### Hospital Status Constants
- `SiteConstants::HOSPITAL_STATUS_ACTIVE` - 'active'
- `SiteConstants::HOSPITAL_STATUS_INACTIVE` - 'inactive'
- `SiteConstants::HOSPITAL_STATUS_MAINTENANCE` - 'maintenance'

### Activity Logger Event Constants
- User events: `EVENT_USER_LOGIN`, `EVENT_USER_LOGOUT`, `EVENT_USER_CREATED`, etc.
- Patient events: `EVENT_PATIENT_CREATED`, `EVENT_PATIENT_UPDATED`, etc.
- Case events: `EVENT_CASE_CREATED`, `EVENT_CASE_UPDATED`, `EVENT_CASE_ASSIGNED`, etc.
- Document events: `EVENT_DOCUMENT_UPLOADED`, `EVENT_DOCUMENT_DOWNLOADED`, etc.

## Usage Guidelines

### 1. Import the Constants Class
```php
use App\Constants\SiteConstants;
```

### 2. Use Constants Instead of Hardcoded Values

**❌ Bad - Hardcoded values:**
```php
$user->status = 'active';
$case->status = 'draft';
$query->where(['Roles.type' => 'patient']);
```

**✅ Good - Using constants:**
```php
$user->status = SiteConstants::USER_STATUS_ACTIVE;
$case->status = SiteConstants::CASE_STATUS_DRAFT;
$query->where(['Roles.type' => SiteConstants::ROLE_TYPE_PATIENT]);
```

### 3. Status Arrays with Constants
```php
$statusOptions = [
    SiteConstants::CASE_STATUS_DRAFT => 'Draft',
    SiteConstants::CASE_STATUS_ASSIGNED => 'Assigned',
    SiteConstants::CASE_STATUS_IN_PROGRESS => 'In Progress',
    SiteConstants::CASE_STATUS_COMPLETED => 'Completed',
    SiteConstants::CASE_STATUS_CANCELLED => 'Cancelled'
];
```

### 4. Validation Rules with Constants
```php
$validator->inList('status', [
    SiteConstants::CASE_STATUS_PENDING,
    SiteConstants::CASE_STATUS_SCHEDULED,
    SiteConstants::CASE_STATUS_IN_PROGRESS,
    SiteConstants::CASE_STATUS_COMPLETED,
    SiteConstants::CASE_STATUS_CANCELLED
]);
```

## Updated Files
The following files have been updated to use constants:

### Controllers
- `src/Controller/Technician/CasesController.php`
- `src/Controller/Technician/PatientsController.php`
- `src/Controller/System/DashboardController.php`
- `src/Controller/AppController.php`
- `src/Controller/AuthController.php`
- `src/Controller/PagesController.php`

### Models
- `src/Model/Table/CasesExamsProceduresTable.php`
- `src/Model/Entity/CasesExamsProcedure.php`

### Commands
- `src/Command/CreateSuperUserCommand.php`
- `src/Command/SetupSpecializedRolesCommand.php`

## Benefits
1. **Consistency**: All status/type values are centralized
2. **Maintainability**: Easy to update values in one place
3. **Type Safety**: IDE autocompletion and error detection
4. **Documentation**: Self-documenting code with meaningful constant names
5. **Refactoring**: Easier to find and replace values across the codebase

## Best Practices
1. Always import `SiteConstants` when using constants
2. Use descriptive constant names that clearly indicate their purpose
3. Group related constants together with comments
4. Document constant values and their usage
5. Prefer constants over magic strings/numbers throughout the application