<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Core\Configure;
use Cake\Log\Log;

/**
 * Patient Masking Service
 * 
 * Handles masking of sensitive patient information based on user roles
 * Uses the actual MEG database structure with correct field names
 */
class PatientMaskingService
{
    /**
     * Role mapping based on actual roles table
     */
    private $roleMapping = [
        1 => 'administrator',    // Administrator
        2 => 'doctor',          // Doctor  
        3 => 'technician',      // Technician
        4 => 'scientist',       // Scientist
        5 => 'nurse',           // Nurse
        6 => 'super',           // Super Administrator
        7 => 'patient'          // Patient
    ];

        /**
     * Mask patient data based on current user's role
     *
     * @param object $user User entity with hasOne Patient relationship
     * @param object $currentUser Current user object
     * @return object Masked patient object
     */
    public function maskPatientForUser($user, $currentUser)
    {
        // Return null or empty patient if invalid input
        if (!$user || !is_object($user) || !$currentUser || !is_object($currentUser)) {
            return $user;
        }

        // Check if masking is globally disabled
        if (!$this->isMaskingEnabled()) {
            // Log when unmasked data is accessed
            Log::info('Patient data accessed without masking (masking disabled)', [
                'user_id' => $currentUser->id,
                'user_role' => $this->getUserRoleName($currentUser->role_id),
                'patient_id' => $user->patient->id ?? $user->id ?? null,
                'timestamp' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            return $user;
        }

        // Expect User entity with hasOne Patient relationship
        // Create patient-like object for consistent masking
        $patient = new \stdClass();
        $patient->id = $user->patient->id ?? $user->id ?? null;
        $patient->user = $user;

        // Copy patient-specific fields from the hasOne patient record
        if (isset($user->patient) && $user->patient) {
            $p = $user->patient;
            $patient->medical_record_number = $p->medical_record_number ?? null;
            $patient->financial_record_number = $p->financial_record_number ?? null;
            $patient->dob = $p->dob ?? null;
            $patient->phone = $p->phone ?? null;
            $patient->gender = $p->gender ?? null;
            $patient->address = $p->address ?? null;
            $patient->notes = $p->notes ?? null;
            $patient->emergency_contact_name = $p->emergency_contact_name ?? null;
            $patient->emergency_contact_phone = $p->emergency_contact_phone ?? null;
            $patient->hospital_id = $p->hospital_id ?? $user->hospital_id ?? null;
            $patient->created = $p->created ?? $user->created ?? null;
            $patient->modified = $p->modified ?? $user->modified ?? null;
        } else {
            // No patient record - set to null so masking service shows N/A
            $patient->medical_record_number = null;
            $patient->financial_record_number = null;
            $patient->dob = null;
            $patient->phone = null;
            $patient->gender = null;
            $patient->address = null;
            $patient->notes = null;
            $patient->emergency_contact_name = null;
            $patient->emergency_contact_phone = null;
            $patient->hospital_id = $user->hospital_id ?? null;
            $patient->created = $user->created ?? null;
            $patient->modified = $user->modified ?? null;
        }

        // Apply masking rules based on user roles
        $userRole = $this->getUserRoleName($currentUser->role_id);
        return $this->applyMaskingRules($patient, $userRole, $currentUser);
    }

    /**
     * Apply role-based masking rules
     *
     * @param object $patient Patient entity
     * @param string $userRole User role name
     * @param object $currentUser Current user entity
     * @return object Masked patient
     */
    private function applyMaskingRules($patient, $userRole, $currentUser)
    {
        // Check if masking is enabled
        if (!$this->isMaskingEnabled()) {
            // Return original patient data without masking
            return $patient;
        }

        // Additional validation to prevent clone error
        if (!$patient || !is_object($patient)) {
            return $patient;
        }
        $maskingRules = [
            'super' => [
                'first_name' => 'full',
                'last_name' => 'full', 
                'email' => 'full',
                'medical_record_number' => 'full',
                'financial_record_number' => 'full',
                'dob' => 'full',
                'phone' => 'full',
                'gender' => 'full',
                'address' => 'full',
                'emergency_contact_name' => 'full',
                'emergency_contact_phone' => 'full'
            ],
            'administrator' => [
                'first_name' => 'full',
                'last_name' => 'full',
                'email' => 'full', 
                'medical_record_number' => 'full',
                'financial_record_number' => 'full',
                'dob' => 'year_only',
                'phone' => 'partial',
                'gender' => 'full',
                'address' => 'full',
                'emergency_contact_name' => 'full',
                'emergency_contact_phone' => 'partial'
            ],
            'doctor' => [
                'first_name' => 'patient_id',
                'last_name' => 'hidden',
                'email' => 'hidden',
                'medical_record_number' => 'sequential',
                'financial_record_number' => 'sequential',
                'dob' => 'age_only',
                'phone' => 'hidden',
                'gender' => 'full',
                'address' => 'hidden',
                'emergency_contact_name' => 'hidden',
                'emergency_contact_phone' => 'hidden'
            ],
            'nurse' => [
                'first_name' => 'patient_id',
                'last_name' => 'hidden',
                'email' => 'hidden',
                'medical_record_number' => 'sequential',
                'financial_record_number' => 'sequential',
                'dob' => 'age_only',
                'phone' => 'hidden',
                'gender' => 'full',
                'address' => 'hidden',
                'emergency_contact_name' => 'hidden',
                'emergency_contact_phone' => 'hidden'
            ],
            'technician' => [
                'first_name' => 'patient_id',
                'last_name' => 'hidden',
                'email' => 'hidden',
                'medical_record_number' => 'sequential',
                'financial_record_number' => 'sequential',
                'dob' => 'age_only',
                'phone' => 'hidden',
                'gender' => 'full',
                'address' => 'hidden',
                'emergency_contact_name' => 'hidden',
                'emergency_contact_phone' => 'hidden'
            ],
            'scientist' => [
                'first_name' => 'patient_id',
                'last_name' => 'hidden',
                'email' => 'hidden',
                'medical_record_number' => 'sequential',
                'financial_record_number' => 'sequential',
                'dob' => 'age_only',
                'phone' => 'hidden',
                'gender' => 'full',
                'address' => 'hidden',
                'emergency_contact_name' => 'hidden',
                'emergency_contact_phone' => 'hidden'
            ]
        ];
        
        $rules = $maskingRules[$userRole] ?? $maskingRules['scientist'];
        
        // Log the masking access for audit purposes
        $this->logMaskingAccess($patient, $currentUser, $userRole);
        
        return $this->maskPatientData($patient, $rules, $currentUser);
    }

    /**
     * Apply masking to patient data fields
     *
     * @param object $patient Patient entity
     * @param array $rules Masking rules for each field
     * @param object $currentUser Current user
     * @return object Masked patient
     */
    private function maskPatientData($patient, $rules, $currentUser)
    {
        // Ensure we have a valid object before cloning
        if (!$patient || !is_object($patient)) {
            return $patient;
        }
        
        $maskedPatient = clone $patient;
        
        // Mask first_name from users table
        if (isset($rules['first_name']) && isset($patient->user)) {
            $maskedPatient->masked_first_name = $this->maskField(
                $patient->user->first_name ?? '', 
                $rules['first_name'], 
                'first_name',
                $patient
            );
        }
        
        // Mask last_name from users table
        if (isset($rules['last_name']) && isset($patient->user)) {
            $maskedPatient->masked_last_name = $this->maskField(
                $patient->user->last_name ?? '',
                $rules['last_name'],
                'last_name', 
                $patient
            );
        }
        
        // Mask email from users table
        if (isset($rules['email']) && isset($patient->user)) {
            $maskedPatient->masked_email = $this->maskField(
                $patient->user->email ?? '',
                $rules['email'],
                'email',
                $patient
            );
        }
        
        // Mask MRN (medical_record_number)
        if (isset($rules['medical_record_number'])) {
            $maskedPatient->medical_record_number = $this->maskField(
                $patient->medical_record_number ?? '',
                $rules['medical_record_number'], 
                'mrn',
                $patient
            );
        }
        
        // Mask FIN (financial_record_number)
        if (isset($rules['financial_record_number'])) {
            $maskedPatient->financial_record_number = $this->maskField(
                $patient->financial_record_number ?? '',
                $rules['financial_record_number'],
                'fin', 
                $patient
            );
        }
        
        // Mask DOB
        if (isset($rules['dob'])) {
            $maskedPatient->dob = $this->maskField(
                $patient->dob ?? '',
                $rules['dob'],
                'dob',
                $patient
            );
        }
        
        // Mask phone
        if (isset($rules['phone'])) {
            $maskedPatient->phone = $this->maskField(
                $patient->phone ?? '',
                $rules['phone'],
                'phone',
                $patient
            );
        }
        
        // Mask gender (and convert M/F to full text)
        if (isset($rules['gender'])) {
            $maskedPatient->gender = $this->maskField(
                $patient->gender ?? '',
                $rules['gender'],
                'gender',
                $patient
            );
        }
        
        // Mask address
        if (isset($rules['address'])) {
            $maskedPatient->address = $this->maskField(
                $patient->address ?? '',
                $rules['address'],
                'address',
                $patient
            );
        }
        
        // Mask emergency contact name
        if (isset($rules['emergency_contact_name'])) {
            $maskedPatient->emergency_contact_name = $this->maskField(
                $patient->emergency_contact_name ?? '',
                $rules['emergency_contact_name'],
                'emergency_contact_name',
                $patient
            );
        }
        
        // Mask emergency contact phone
        if (isset($rules['emergency_contact_phone'])) {
            $maskedPatient->emergency_contact_phone = $this->maskField(
                $patient->emergency_contact_phone ?? '',
                $rules['emergency_contact_phone'],
                'emergency_contact_phone',
                $patient
            );
        }
        
        return $maskedPatient;
    }
    
    /**
     * Apply specific masking pattern to a field
     *
     * @param string $value Original field value
     * @param string $maskType Type of masking to apply
     * @param string $fieldType Type of field being masked
     * @param object $patient Patient entity for context
     * @return string Masked value
     */
    private function maskField($value, $maskType, $fieldType, $patient)
    {
        // If no value exists in database, return empty/null when masking is disabled
        if ($value === null || $value === '') {
            // If masking is disabled, return empty string instead of N/A
            if (!$this->isMaskingEnabled()) {
                return '';
            }
            return 'N/A';
        }
        
        switch ($maskType) {
            case 'full':
                // For gender field, convert single letters to full words
                if ($fieldType === 'gender') {
                    return match(strtolower($value)) {
                        'm', 'male' => 'male',
                        'f', 'female' => 'female',
                        'o', 'other' => 'other',
                        default => strtolower($value)
                    };
                }
                return $value;
                
            case 'first_and_initial':
                // If masking is disabled, return original value
                if (!$this->isMaskingEnabled()) {
                    return $value;
                }
                return $value ? substr($value, 0, 1) . '***' : '';
                
            case 'initial_only':
                // If masking is disabled, return original value
                if (!$this->isMaskingEnabled()) {
                    return $value;
                }
                return $value ? substr($value, 0, 1) . '.' : '';
                
            case 'partial':
                // If masking is disabled, return original value
                if (!$this->isMaskingEnabled()) {
                    return $value;
                }
                return $value ? substr($value, 0, 2) . str_repeat('*', max(0, strlen($value) - 2)) : '';
                
            case 'last_4':
                // If masking is disabled, return original value
                if (!$this->isMaskingEnabled()) {
                    return $value;
                }
                return strlen($value) > 4 ? '***' . substr($value, -4) : $value;
                
            case 'age_only':
                // If masking is disabled, return original DOB value
                if (!$this->isMaskingEnabled()) {
                    return $value;
                }
                if ($fieldType === 'dob') {
                    try {
                        $dobString = is_object($value) ? $value->format('Y-m-d') : $value;
                        $dob = new \DateTime($dobString);
                        $now = new \DateTime();
                        $age = $now->diff($dob)->y;
                        return $age . ' years old';
                    } catch (\Exception $e) {
                        return 'N/A';
                    }
                }
                return $value;
                
            case 'age_group':
                // If masking is disabled, return original DOB value
                if (!$this->isMaskingEnabled()) {
                    return $value;
                }
                if ($fieldType === 'dob') {
                    try {
                        $dobString = is_object($value) ? $value->format('Y-m-d') : $value;
                        $dob = new \DateTime($dobString);
                        $now = new \DateTime();
                        $age = $now->diff($dob)->y;
                        
                        if ($age < 18) return 'Under 18';
                        if ($age < 30) return '18-29';
                        if ($age < 50) return '30-49'; 
                        if ($age < 70) return '50-69';
                        return '70+';
                    } catch (\Exception $e) {
                        return 'N/A';
                    }
                }
                return $value;
                
            case 'domain_only':
                // If masking is disabled, return original email value
                if (!$this->isMaskingEnabled()) {
                    return $value;
                }
                if ($fieldType === 'email' && strpos($value, '@') !== false) {
                    return '***@' . explode('@', $value)[1];
                }
                return '***';
                
            case 'sequential':
                // If masking is disabled, return original value
                if (!$this->isMaskingEnabled()) {
                    return $value;
                }
                $prefix = $fieldType === 'mrn' ? 'MRN-' : 'FIN-';
                return $prefix . str_pad((string)$patient->id, 3, '0', STR_PAD_LEFT);
                
            case 'patient_id':
                // If masking is disabled, return original value instead of Patient #ID
                if (!$this->isMaskingEnabled()) {
                    return $value;
                }
                return 'Patient #' . $patient->id;
                
            case 'hidden':
                // If masking is disabled, return original value
                if (!$this->isMaskingEnabled()) {
                    return $value;
                }
                return '***';
                
            case 'none':
            default:
                return $value;
        }
    }

    /**
     * Get user role name from role_id
     *
     * @param int $roleId Role ID from users table
     * @return string Role name
     */
    public function getUserRoleName($roleId)
    {
        return $this->roleMapping[$roleId] ?? 'guest';
    }

    /**
     * Check if user can view full patient data
     *
     * @param object $currentUser Current user
     * @param object $patient Patient entity (optional)
     * @return bool
     */
    public function canViewFullPatientData($currentUser, $patient = null)
    {
        $userRole = $this->getUserRoleName($currentUser->role_id);
        
        // Super admin and admin can see everything
        if (in_array($userRole, ['super', 'administrator'])) {
            return true;
        }
        
        // Doctors can see full data
        if ($userRole === 'doctor') {
            return true;
        }
        
        return false;
    }

    /**
     * Mask a collection of patients
     *
     * @param iterable $patients Collection of patient entities
     * @param object $currentUser Current user
     * @return array Masked patients
     */
    public function maskPatientCollection($patients, $currentUser)
    {
        $maskedPatients = [];
        
        foreach ($patients as $patient) {
            $maskedPatients[] = $this->maskPatientForUser($patient, $currentUser);
        }
        
        return $maskedPatients;
    }

    /**
     * Log masking access for audit purposes
     *
     * @param object $patient Patient entity
     * @param object $currentUser Current user
     * @param string $userRole User role
     */
    private function logMaskingAccess($patient, $currentUser, $userRole)
    {
        try {
            Log::info('Patient data accessed with masking', [
                'user_id' => $currentUser->id,
                'user_role' => $userRole,
                'patient_id' => $patient->id,
                'timestamp' => date('Y-m-d H:i:s'),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (\Exception $e) {
            // Don't let logging errors break the masking functionality
            error_log('Failed to log masking access: ' . $e->getMessage());
        }
    }

    /**
     * Check if patient data masking is enabled
     *
     * @return bool True if masking is enabled, false otherwise
     */
    public function isMaskingEnabled(): bool
    {
        // Check configuration setting, default to true (enabled) for security
        return Configure::read('PatientMasking.enabled', true);
    }

    /**
     * Enable patient data masking
     *
     * @return void
     */
    public function enableMasking(): void
    {
        Configure::write('PatientMasking.enabled', true);
        Log::info('Patient data masking enabled');
    }

    /**
     * Disable patient data masking
     *
     * @return void
     */
    public function disableMasking(): void
    {
        Configure::write('PatientMasking.enabled', false);
        Log::warning('Patient data masking disabled - sensitive data will be visible');
    }

    /**
     * Get the current masking status
     *
     * @return array Status information including enabled state and configuration source
     */
    public function getMaskingStatus(): array
    {
        $enabled = $this->isMaskingEnabled();
        
        return [
            'enabled' => $enabled,
            'status' => $enabled ? 'enabled' : 'disabled',
            'config_key' => 'PatientMasking.enabled',
            'default_state' => 'enabled',
            'last_modified' => date('Y-m-d H:i:s'),
            'warning' => !$enabled ? 'Patient data masking is disabled. Sensitive information may be visible.' : null
        ];
    }
}