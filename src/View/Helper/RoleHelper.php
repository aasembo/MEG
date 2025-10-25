<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;
use App\Constants\SiteConstants;

/**
 * Role Helper
 * 
 * Provides utility functions for role display and formatting
 */
class RoleHelper extends Helper
{
    /**
     * Get human-readable label for a role type
     *
     * @param string|null $roleType The role type from the database
     * @return string The display label for the role
     */
    public function label(?string $roleType): string
    {
        if (!$roleType) {
            return 'Unknown';
        }
        
        return match($roleType) {
            SiteConstants::ROLE_TYPE_SCIENTIST => 'Scientist',
            SiteConstants::ROLE_TYPE_DOCTOR => 'Doctor',
            SiteConstants::ROLE_TYPE_TECHNICIAN => 'Technician',
            SiteConstants::ROLE_TYPE_ADMIN, 
            SiteConstants::ROLE_TYPE_ADMINISTRATOR => 'Administrator',
            SiteConstants::ROLE_TYPE_SUPER => 'Super Admin',
            SiteConstants::ROLE_TYPE_NURSE => 'Nurse',
            SiteConstants::ROLE_TYPE_PATIENT => 'Patient',
            default => ucfirst($roleType)
        };
    }
    
    /**
     * Get badge class for a role type
     *
     * @param string|null $roleType The role type from the database
     * @return string Bootstrap badge class
     */
    public function badgeClass(?string $roleType): string
    {
        if (!$roleType) {
            return 'bg-secondary';
        }
        
        return match($roleType) {
            SiteConstants::ROLE_TYPE_SCIENTIST => 'bg-info',
            SiteConstants::ROLE_TYPE_DOCTOR => 'bg-primary',
            SiteConstants::ROLE_TYPE_TECHNICIAN => 'bg-secondary',
            SiteConstants::ROLE_TYPE_ADMIN,
            SiteConstants::ROLE_TYPE_ADMINISTRATOR,
            SiteConstants::ROLE_TYPE_SUPER => 'bg-danger',
            SiteConstants::ROLE_TYPE_NURSE => 'bg-success',
            SiteConstants::ROLE_TYPE_PATIENT => 'bg-warning',
            default => 'bg-secondary'
        };
    }
    
    /**
     * Render a role badge with icon and label
     *
     * @param string|null $roleType The role type from the database
     * @param array $options Additional HTML attributes for the badge
     * @return string HTML for the badge
     */
    public function badge(?string $roleType, array $options = []): string
    {
        $label = $this->label($roleType);
        $class = $this->badgeClass($roleType);
        
        // Merge user options with defaults
        $defaultOptions = [
            'class' => "badge {$class}",
            'escape' => false
        ];
        $options = array_merge($defaultOptions, $options);
        
        // Add user classes to default
        if (isset($options['class']) && $options['class'] !== $defaultOptions['class']) {
            $options['class'] = $defaultOptions['class'] . ' ' . $options['class'];
        }
        
        return sprintf(
            '<span class="%s">%s</span>',
            h($options['class']),
            h($label)
        );
    }
}
