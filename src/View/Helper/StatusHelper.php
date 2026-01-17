<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\View\Helper;

/**
 * Status Helper
 * 
 * Provides utility functions for status badge display and formatting
 */
class StatusHelper extends Helper
{
    /**
     * Helpers
     *
     * @var array
     */
    protected array $helpers = ['Html'];

    /**
     * Get Bootstrap color class for a given status
     *
     * @param string|null $status The status value
     * @return string Bootstrap color class (e.g., 'secondary', 'info', 'warning')
     */
    public function colorClass(?string $status): string
    {
        return match($status) {
            'draft' => 'secondary',
            'assigned' => 'info',
            'in_progress' => 'warning',
            'review' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get icon class for a role
     *
     * @param string $role The role name (technician, scientist, doctor)
     * @return string Font Awesome icon class
     */
    public function roleIcon(string $role): string
    {
        return match(strtolower($role)) {
            'technician' => 'fas fa-user-cog',
            'scientist' => 'fas fa-user-md',
            'doctor' => 'fas fa-user-doctor',
            'admin', 'administrator' => 'fas fa-user-shield',
            'nurse' => 'fas fa-user-nurse',
            default => 'fas fa-user'
        };
    }

    /**
     * Render a role-based status badge
     *
     * @param object $case The case entity
     * @param string $role The role (technician, scientist, doctor)
     * @param array $options Additional HTML options
     * @return string HTML for the badge
     */
    public function roleBadge(?object $case, string $role, $currentUser = null): string
    {
        if (!$case) {
            return '';
        }

        // NOTE: Using role-based status fields (technician_status, scientist_status, doctor_status)
        // See docs/REMINDER-Role-Based-Status.md
        $statusField = strtolower($role) . '_status';
        $status = $case->$statusField ?? null;

        // Don't show badge if status is null or 'draft'
        if (!$status || $status === 'draft') {
            return '';
        }

        // ROLE DETECTION: Users have role_id (belongsTo Roles)
        // Access via: $user->role->type (singular, not plural)
        // Ensure 'Roles' is contained when loading user
        $currentUserRole = null;
        if ($currentUser && isset($currentUser->role) && isset($currentUser->role->type)) {
            $currentUserRole = $currentUser->role->type;
        }

        // Show "You" instead of role name if current user's role matches
        // Use null-safe comparison to avoid strtolower error
        $displayRole = ($currentUserRole !== null && strtolower($role) === strtolower($currentUserRole)) 
            ? 'You' 
            : ucfirst($role);
        
        $badgeClass = 'bg-' . $this->colorClass($status);
        
        return sprintf(
            '<span class="badge %s"><i class="fas fa-user-tag me-1"></i>%s: %s</span>',
            $badgeClass,
            h($displayRole),
            h(ucfirst($status))
        );
    }

    /**
     * Render global case status badge
     *
     * @param object $case The case entity
     * @param array $options Additional HTML options
     * @return string HTML for the badge
     */
    public function globalBadge(object $case, array $options = []): string
    {
        $status = $case->status ?? 'draft';
        
        // Don't show badge if status is 'draft' (internal status only)
        if ($status === 'draft') {
            return '';
        }
        
        $colorClass = $this->colorClass($status);
        $statusLabel = ucfirst(str_replace('_', ' ', $status));
        
        // Merge default classes with custom options
        $classes = 'badge bg-' . $colorClass;
        if (isset($options['class'])) {
            $classes .= ' ' . $options['class'];
            unset($options['class']);
        }
        
        // Build HTML attributes
        $attributes = ['class' => $classes];
        foreach ($options as $key => $value) {
            $attributes[$key] = $value;
        }
        
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= sprintf(' %s="%s"', h($key), h($value));
        }
        
        return sprintf(
            '<span%s><i class="fas fa-stream me-2"></i>%s</span>',
            $attrString,
            h($statusLabel)
        );
    }

    /**
     * Get priority badge HTML
     *
     * @param string $priority The priority level
     * @param array $options Additional HTML options
     * @return string HTML for the priority badge
     */
    public function priorityBadge(string $priority, array $options = []): string
    {
        $colorClass = match($priority) {
            'urgent' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'secondary',
            default => 'secondary'
        };
        
        $iconClass = match($priority) {
            'urgent' => 'fas fa-exclamation-triangle',
            'high' => 'fas fa-arrow-up',
            'medium' => 'fas fa-minus',
            'low' => 'fas fa-arrow-down',
            default => 'fas fa-minus'
        };
        
        // Merge default classes with custom options
        $classes = 'badge bg-' . $colorClass;
        if (isset($options['class'])) {
            $classes .= ' ' . $options['class'];
            unset($options['class']);
        }
        
        // Build HTML attributes
        $attributes = ['class' => $classes];
        foreach ($options as $key => $value) {
            $attributes[$key] = $value;
        }
        
        $attrString = '';
        foreach ($attributes as $key => $value) {
            $attrString .= sprintf(' %s="%s"', h($key), h($value));
        }
        
        return sprintf(
            '<span%s><i class="%s"></i> %s</span>',
            $attrString,
            h($iconClass),
            h(ucfirst($priority))
        );
    }

    /**
     * Get progress percentage for a status
     *
     * @param string|null $status The status value
     * @return int Progress percentage (0-100)
     */
    public function progressPercent(?string $status): int
    {
        return match($status) {
            'completed' => 100,
            'review' => 75,
            'in_progress' => 50,
            'assigned' => 25,
            default => 10
        };
    }

    /**
     * Get progress bar color for a status
     *
     * @param string|null $status The status value
     * @return string Bootstrap color class for progress bar
     */
    public function progressColor(?string $status): string
    {
        return match($status) {
            'completed' => 'success',
            'in_progress', 'review' => 'warning',
            'assigned' => 'info',
            default => 'secondary'
        };
    }
}
