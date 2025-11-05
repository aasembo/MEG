<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;
use App\Service\PatientMaskingService;

/**
 * Patient Mask Helper
 * 
 * Provides easy-to-use methods for displaying masked patient information in templates
 */
class PatientMaskHelper extends Helper
{
    /**
     * @var PatientMaskingService
     */
    private $maskingService;

    /**
     * Initialize the helper
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->maskingService = new PatientMaskingService();
    }

    /**
     * Display masked patient name
     *
     * @param object $user User entity with hasOne Patient relationship
     * @param object|null $currentUser Current user (if null, gets from request)
     * @return string Masked patient name
     */
    public function displayName($user, $currentUser = null)
    {
        if (!$user || !is_object($user)) {
            return '';
        }
        
        $currentUser = $currentUser ?? $this->getCurrentUser();
        if (!$currentUser) {
            return 'Patient #' . ($user->id ?? '');
        }

        // If masking is disabled globally, return actual names
        if (!$this->maskingService->isMaskingEnabled()) {
            $firstName = $user->first_name ?? '';
            $lastName = $user->last_name ?? '';
            return trim($firstName . ' ' . $lastName) ?: 'Patient #' . ($user->id ?? '');
        }

        $maskedPatient = $this->maskingService->maskPatientForUser($user, $currentUser);
        
        // Use the masked values from the service
        $firstName = $maskedPatient->masked_first_name ?? ($maskedPatient->user->first_name ?? '');
        $lastName = $maskedPatient->masked_last_name ?? ($maskedPatient->user->last_name ?? '');
        
        return trim($firstName . ' ' . $lastName) ?: 'Patient #' . ($user->id ?? '');
    }

    /**
     * Display masked MRN (Medical Record Number)
     *
     * @param object $user User entity with hasOne Patient relationship
     * @param object|null $currentUser Current user
     * @return string Masked MRN
     */
    public function displayMrn($user, $currentUser = null)
    {
        if (!$user || !is_object($user)) {
            return '';
        }

        $currentUser = $currentUser ?? $this->getCurrentUser();
        if (!$currentUser) {
            return '***';
        }

        // If masking is disabled globally, return actual MRN
        if (!$this->maskingService->isMaskingEnabled()) {
            return $user->patient->medical_record_number ?? '';
        }

        $maskedPatient = $this->maskingService->maskPatientForUser($user, $currentUser);
        return $maskedPatient->medical_record_number ?? 'N/A';
    }

    /**
    /**
     * Display masked FIN (Financial Record Number)
     *
     * @param object $user User entity with hasOne Patient relationship
     * @param object|null $currentUser Current user
     * @return string Masked FIN
     */
    public function displayFin($user, $currentUser = null)
    {
        if (!$user || !is_object($user)) {
            return '';
        }

        $currentUser = $currentUser ?? $this->getCurrentUser();
        if (!$currentUser) {
            return '***';
        }

        // If masking is disabled globally, return actual FIN
        if (!$this->maskingService->isMaskingEnabled()) {
            return $user->patient->financial_record_number ?? '';
        }

        $maskedPatient = $this->maskingService->maskPatientForUser($user, $currentUser);
        return $maskedPatient->financial_record_number ?? 'N/A';
    }

    /**
     * Display masked email
     *
     * @param object $user User entity with hasOne Patient relationship
     * @param object|null $currentUser Current user
     * @return string Masked email
     */
    public function displayEmail($user, $currentUser = null)
    {
        if (!$user || !is_object($user)) {
            return '';
        }

        $currentUser = $currentUser ?? $this->getCurrentUser();
        if (!$currentUser) {
            return '***';
        }

        // If masking is disabled globally, return actual email
        if (!$this->maskingService->isMaskingEnabled()) {
            return $user->email ?? '';
        }

        $maskedPatient = $this->maskingService->maskPatientForUser($user, $currentUser);
        return $maskedPatient->masked_email ?? ($user->email ?? 'N/A');
    }

    /**
     * Display date of birth and age information based on masking settings
     *
     * @param object $user User entity with hasOne Patient relationship
     * @param object|null $currentUser Current user
     * @return string DOB and age when masking disabled, age only when enabled
     */
    public function displayDob($user, $currentUser = null)
    {
        if (!$user || !is_object($user)) {
            return '';
        }

        $currentUser = $currentUser ?? $this->getCurrentUser();
        if (!$currentUser) {
            return '***';
        }

        $dobValue = $user->patient->dob ?? null;
        
        // If masking is disabled globally, show both DOB and calculated age
        if (!$this->maskingService->isMaskingEnabled()) {
            if ($dobValue) {
                $dobFormatted = $this->formatDob($dobValue);
                $age = $this->calculateAge($dobValue);
                return $dobFormatted . ' (' . $age . ')';
            }
            return '';
        }

        // When masking is enabled, show only age (no DOB)
        $maskedPatient = $this->maskingService->maskPatientForUser($user, $currentUser);
        if (isset($maskedPatient->dob) && $maskedPatient->dob) {
            return $maskedPatient->dob;
        }

        // Fallback: if masked dob not present but real dob exists, return calculated age only
        if ($dobValue) {
            return $this->calculateAge($dobValue);
        }

        return 'N/A';
    }

    /**
     * Calculate age string from a DOB value
     *
     * @param \DateTime|string $dob
     * @return string Age as "X years old" or 'N/A' on error
     */
    private function calculateAge($dob)
    {
        if (!$dob) {
            return 'N/A';
        }

        try {
            $dobString = is_object($dob) && method_exists($dob, 'format') ? $dob->format('Y-m-d') : (string)$dob;
            $dobDate = new \DateTime($dobString);
            $now = new \DateTime();
            $age = $now->diff($dobDate)->y;
            return $age . ' years old';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Format DOB for display
     *
     * @param \DateTime|string $dob
     * @return string Formatted DOB or 'N/A' on error
     */
    private function formatDob($dob)
    {
        if (!$dob) {
            return 'N/A';
        }

        try {
            $dobString = is_object($dob) && method_exists($dob, 'format') ? $dob->format('Y-m-d') : (string)$dob;
            $dobDate = new \DateTime($dobString);
            return $dobDate->format('M j, Y'); // e.g., "Jan 15, 1990"
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Display masked phone number
     *
     * @param object $user User entity with hasOne Patient relationship
     * @param object|null $currentUser Current user
     * @return string Masked phone
     */
    public function displayPhone($user, $currentUser = null)
    {
        if (!$user || !is_object($user)) {
            return '';
        }

        $currentUser = $currentUser ?? $this->getCurrentUser();
        if (!$currentUser) {
            return '***';
        }

        // If masking is disabled globally, return actual phone
        if (!$this->maskingService->isMaskingEnabled()) {
            return $user->patient->phone ?? '';
        }

        $maskedPatient = $this->maskingService->maskPatientForUser($user, $currentUser);
        return $maskedPatient->phone ?? 'N/A';
    }

    /**
     * Display masked gender (converts M/F to Male/Female)
     *
     * @param object $user User entity with hasOne Patient relationship
     * @param object|null $currentUser Current user
     * @return string Masked gender
     */
    public function displayGender($user, $currentUser = null)
    {
        if (!$user || !is_object($user)) {
            return '';
        }

        $currentUser = $currentUser ?? $this->getCurrentUser();
        if (!$currentUser) {
            return '***';
        }

        // If masking is disabled globally, return actual gender
        if (!$this->maskingService->isMaskingEnabled()) {
            $gender = $user->patient->gender ?? '';
            // Convert single letters to full words
            return match(strtolower($gender)) {
                'm', 'male' => 'Male',
                'f', 'female' => 'Female',
                'o', 'other' => 'Other',
                default => $gender
            };
        }

        $maskedPatient = $this->maskingService->maskPatientForUser($user, $currentUser);
        return $maskedPatient->gender ?? 'N/A';
    }

    /**
     * Display masked address
     *
     * @param object $user User entity with hasOne Patient relationship
     * @param object|null $currentUser Current user
     * @return string Masked address
     */
    public function displayAddress($user, $currentUser = null)
    {
        if (!$user || !is_object($user)) {
            return '';
        }

        $currentUser = $currentUser ?? $this->getCurrentUser();
        if (!$currentUser) {
            return '***';
        }

        // If masking is disabled globally, return actual address
        if (!$this->maskingService->isMaskingEnabled()) {
            return $user->patient->address ?? '';
        }

        $maskedPatient = $this->maskingService->maskPatientForUser($user, $currentUser);
        return $maskedPatient->address ?? 'N/A';
    }

    /**
     * Display masked emergency contact name
     *
     * @param object $user User entity with hasOne Patient relationship
     * @param object|null $currentUser Current user
     * @return string Masked emergency contact name
     */
    public function displayEmergencyContactName($user, $currentUser = null)
    {
        if (!$user || !is_object($user)) {
            return '';
        }

        $currentUser = $currentUser ?? $this->getCurrentUser();
        if (!$currentUser) {
            return '***';
        }

        // If masking is disabled globally, return actual emergency contact name
        if (!$this->maskingService->isMaskingEnabled()) {
            return $user->patient->emergency_contact_name ?? '';
        }

        $maskedPatient = $this->maskingService->maskPatientForUser($user, $currentUser);
        return $maskedPatient->emergency_contact_name ?? 'N/A';
    }

    /**
     * Display masked emergency contact phone
     *
     * @param object $user User entity with hasOne Patient relationship
     * @param object|null $currentUser Current user
     * @return string Masked emergency contact phone
     */
    public function displayEmergencyContactPhone($user, $currentUser = null)
    {
        if (!$user || !is_object($user)) {
            return '';
        }

        $currentUser = $currentUser ?? $this->getCurrentUser();
        if (!$currentUser) {
            return '***';
        }

        // If masking is disabled globally, return actual emergency contact phone
        if (!$this->maskingService->isMaskingEnabled()) {
            return $user->patient->emergency_contact_phone ?? '';
        }

        $maskedPatient = $this->maskingService->maskPatientForUser($user, $currentUser);
        return $maskedPatient->emergency_contact_phone ?? 'N/A';
    }

    /**
     * Display patient info with icon and proper formatting
     *
     * @param object $patient Patient entity
     * @param string $field Field to display (name, mrn, fin, email, dob, phone)
     * @param array $options Display options
     * @return string Formatted patient info with icon
     */
    public function displayField($patient, $field, $options = [])
    {
        $icons = [
            'name' => 'fas fa-user',
            'mrn' => 'fas fa-id-card', 
            'fin' => 'fas fa-credit-card',
            'email' => 'fas fa-envelope',
            'dob' => 'fas fa-birthday-cake',
            'phone' => 'fas fa-phone'
        ];

        $currentUser = $this->getCurrentUser();
        
        switch ($field) {
            case 'name':
                $value = $this->displayName($patient, $currentUser);
                break;
            case 'mrn':
                $value = $this->displayMrn($patient, $currentUser);
                break;
            case 'fin':
                $value = $this->displayFin($patient, $currentUser);
                break;
            case 'email':
                $value = $this->displayEmail($patient, $currentUser);
                break;
            case 'dob':
                $value = $this->displayDob($patient, $currentUser);
                break;
            case 'phone':
                $value = $this->displayPhone($patient, $currentUser);
                break;
            default:
                $value = '';
        }

        if (empty($value)) {
            return '';
        }

        $showIcon = $options['icon'] ?? true;
        $classes = $options['class'] ?? '';
        
        $icon = $showIcon && isset($icons[$field]) ? '<i class="' . $icons[$field] . ' me-2"></i>' : '';
        
        return '<span class="' . $classes . '">' . $icon . h($value) . '</span>';
    }

    /**
     * Check if current user can view full patient data
     *
     * @param object $patient Patient entity
     * @return bool
     */
    public function canViewFullData($patient = null)
    {
        // If masking is disabled globally, everyone can view full data
        if (!$this->maskingService->isMaskingEnabled()) {
            return true;
        }

        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return false;
        }

        return $this->maskingService->canViewFullPatientData($currentUser, $patient);
    }

    /**
     * Get current user role name
     *
     * @return string Role name
     */
    public function getCurrentUserRole()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return 'guest';
        }

        return $this->maskingService->getUserRoleName($currentUser->role_id);
    }

    /**
     * Display masked patient card with all key information
     *
     * @param object $patient Patient entity
     * @param array $options Display options
     * @return string HTML for patient card
     */
    public function patientCard($patient, $options = [])
    {
        if (!$patient || !is_object($patient)) {
            return '';
        }

        $compact = $options['compact'] ?? false;
        $showActions = $options['actions'] ?? true;
        $cardClass = $options['class'] ?? 'card border-0 shadow-sm';

        $html = '<div class="' . $cardClass . '">';
        $html .= '<div class="card-body p-3">';
        
        // Patient name
        $html .= '<h6 class="card-title mb-2">';
        $html .= $this->displayField($patient, 'name', ['icon' => true]);
        $html .= '</h6>';
        
        if (!$compact) {
            // Patient details
            $html .= '<div class="row g-2 small text-muted">';
            
            $mrn = $this->displayMrn($patient);
            if ($mrn) {
                $html .= '<div class="col-sm-6">';
                $html .= $this->displayField($patient, 'mrn', ['icon' => true, 'class' => 'text-muted']);
                $html .= '</div>';
            }
            
            $fin = $this->displayFin($patient);
            if ($fin) {
                $html .= '<div class="col-sm-6">';
                $html .= $this->displayField($patient, 'fin', ['icon' => true, 'class' => 'text-muted']);
                $html .= '</div>';
            }
            
            $dob = $this->displayDob($patient);
            if ($dob) {
                $html .= '<div class="col-sm-6">';
                $html .= $this->displayField($patient, 'dob', ['icon' => true, 'class' => 'text-muted']);
                $html .= '</div>';
            }
            
            $html .= '</div>';
        }
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Get current user from request
     *
     * @return object|null Current user entity
     */
    private function getCurrentUser()
    {
        try {
            return $this->getView()->getRequest()->getAttribute('identity');
        } catch (\Exception $e) {
            return null;
        }
    }
}