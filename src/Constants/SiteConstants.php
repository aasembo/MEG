<?php
declare(strict_types=1);

namespace App\Constants;

/**
 * Site-wide constants for the MEG application
 */
class SiteConstants
{
    // User Activity Logger Event Constants
    public const EVENT_USER_LOGIN = 'user_login';
    public const EVENT_USER_LOGOUT = 'user_logout';
    public const EVENT_USER_CREATED = 'user_created';
    public const EVENT_USER_UPDATED = 'user_updated';
    public const EVENT_USER_DELETED = 'user_deleted';
    public const EVENT_USER_DEACTIVATED = 'user_deactivated';
    public const EVENT_USER_ACTIVATED = 'user_activated';
    public const EVENT_USER_PASSWORD_CHANGED = 'user_password_changed';
    public const EVENT_USER_PASSWORD_RESET = 'user_password_reset';
    
    // Patient Activity Logger Event Constants
    public const EVENT_PATIENT_CREATED = 'patient_created';
    public const EVENT_PATIENT_UPDATED = 'patient_updated';
    public const EVENT_PATIENT_DELETED = 'patient_deleted';
    public const EVENT_PATIENT_DEACTIVATED = 'patient_deactivated';
    public const EVENT_PATIENT_ACTIVATED = 'patient_activated';
    public const EVENT_PATIENT_VIEWED = 'patient_viewed';
    public const EVENT_PATIENTS_VIEWED = 'patients_viewed';
    
    // Case Activity Logger Event Constants
    public const EVENT_CASE_CREATED = 'case_created';
    public const EVENT_CASE_UPDATED = 'case_updated';
    public const EVENT_CASE_DELETED = 'case_deleted';
    public const EVENT_CASE_ASSIGNED = 'case_assigned';
    public const EVENT_CASE_UNASSIGNED = 'case_unassigned';
    public const EVENT_CASE_STATUS_CHANGED = 'case_status_changed';
    public const EVENT_CASE_VIEWED = 'case_viewed';
    public const EVENT_CASE_LIST_VIEWED = 'case_list_viewed';
    public const EVENT_CASES_VIEWED = 'cases_viewed';
    
    // Document Activity Logger Event Constants
    public const EVENT_DOCUMENT_UPLOADED = 'document_uploaded';
    public const EVENT_DOCUMENT_DOWNLOADED = 'document_downloaded';
    public const EVENT_DOCUMENT_DELETED = 'document_deleted';
    public const EVENT_DOCUMENT_VIEWED = 'document_viewed';
    
    // Hospital Activity Logger Event Constants
    public const EVENT_HOSPITAL_CREATED = 'hospital_created';
    public const EVENT_HOSPITAL_UPDATED = 'hospital_updated';
    public const EVENT_HOSPITAL_DELETED = 'hospital_deleted';
    public const EVENT_HOSPITAL_ACTIVATED = 'hospital_activated';
    public const EVENT_HOSPITAL_DEACTIVATED = 'hospital_deactivated';
    
    // Role Activity Logger Event Constants
    public const EVENT_ROLE_CREATED = 'role_created';
    public const EVENT_ROLE_UPDATED = 'role_updated';
    public const EVENT_ROLE_DELETED = 'role_deleted';
    public const EVENT_ROLE_ASSIGNED = 'role_assigned';
    public const EVENT_ROLE_REVOKED = 'role_revoked';
    
    // Authentication Activity Logger Event Constants
    public const EVENT_AUTH_SUCCESS = 'auth_success';
    public const EVENT_AUTH_FAILURE = 'auth_failure';
    public const EVENT_AUTH_OKTA_SUCCESS = 'auth_okta_success';
    public const EVENT_AUTH_OKTA_FAILURE = 'auth_okta_failure';
    public const EVENT_SESSION_STARTED = 'session_started';
    public const EVENT_SESSION_EXPIRED = 'session_expired';
    public const EVENT_CROSS_ROLE_ACCESS_ATTEMPT = 'cross_role_access_attempt';
    public const EVENT_UNAUTHORIZED_ACCESS_ATTEMPT = 'unauthorized_access_attempt';
    
    // System Activity Logger Event Constants
    public const EVENT_SYSTEM_BACKUP = 'system_backup';
    public const EVENT_SYSTEM_MAINTENANCE = 'system_maintenance';
    public const EVENT_SYSTEM_ERROR = 'system_error';
    public const EVENT_SYSTEM_WARNING = 'system_warning';
    public const EVENT_DATABASE_QUERY_ERROR = 'database_query_error';
    public const EVENT_FILE_SYSTEM_ERROR = 'file_system_error';
    
    // User Status Constants
    public const USER_STATUS_ACTIVE = 'active';
    public const USER_STATUS_INACTIVE = 'inactive';
    public const USER_STATUS_SUSPENDED = 'suspended';
    public const USER_STATUS_PENDING = 'pending';
    
    // Role Types Constants
    public const ROLE_TYPE_ADMINISTRATOR = 'administrator';
    public const ROLE_TYPE_ADMIN = 'admin';
    public const ROLE_TYPE_SUPER = 'super';
    public const ROLE_TYPE_DOCTOR = 'doctor';
    public const ROLE_TYPE_SCIENTIST = 'scientist';
    public const ROLE_TYPE_TECHNICIAN = 'technician';
    public const ROLE_TYPE_NURSE = 'nurse';
    public const ROLE_TYPE_PATIENT = 'patient';
    
    // Priority Constants
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';
    
    // Case Status Constants
    public const CASE_STATUS_DRAFT = 'draft';
    public const CASE_STATUS_ASSIGNED = 'assigned';
    public const CASE_STATUS_PENDING = 'pending';
    public const CASE_STATUS_SCHEDULED = 'scheduled';
    public const CASE_STATUS_IN_PROGRESS = 'in_progress';
    public const CASE_STATUS_UNDER_REVIEW = 'under_review';
    public const CASE_STATUS_REVIEW = 'review';
    public const CASE_STATUS_COMPLETED = 'completed';
    public const CASE_STATUS_CANCELLED = 'cancelled';
    public const CASE_STATUS_ON_HOLD = 'on_hold';
    
    // Hospital Status Constants
    public const HOSPITAL_STATUS_ACTIVE = 'active';
    public const HOSPITAL_STATUS_INACTIVE = 'inactive';
    public const HOSPITAL_STATUS_MAINTENANCE = 'maintenance';
    
    // Gender Constants
    public const GENDER_MALE = 'M';
    public const GENDER_FEMALE = 'F';
    public const GENDER_OTHER = 'O';
    
    // File Upload Constants
    public const MAX_FILE_SIZE = 10485760; // 10MB in bytes
    public const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    public const ALLOWED_DOCUMENT_TYPES = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    
    // Pagination Constants
    public const DEFAULT_PAGE_LIMIT = 25;
    public const MAX_PAGE_LIMIT = 100;
    
    // Session Constants
    public const SESSION_TIMEOUT = 3600; // 1 hour in seconds
    public const SESSION_REMEMBER_TIMEOUT = 604800; // 1 week in seconds
    
    // Security Constants
    public const PASSWORD_MIN_LENGTH = 8;
    public const PASSWORD_MAX_LENGTH = 255;
    public const USERNAME_MIN_LENGTH = 3;
    public const USERNAME_MAX_LENGTH = 50;
    
    // Cache Constants
    public const CACHE_SHORT_DURATION = 300; // 5 minutes
    public const CACHE_MEDIUM_DURATION = 3600; // 1 hour
    public const CACHE_LONG_DURATION = 86400; // 24 hours
    
    // Activity Logger Status Constants
    public const ACTIVITY_STATUS_SUCCESS = 'success';
    public const ACTIVITY_STATUS_FAILED = 'failed';
    public const ACTIVITY_STATUS_ERROR = 'error';
    public const ACTIVITY_STATUS_WARNING = 'warning';
}