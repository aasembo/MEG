<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class MedicalCase extends Entity {
    protected array $_accessible = [
        'user_id' => true,
        'hospital_id' => true,
        'patient_id' => true,
        'department_id' => true,
        'sedation_id' => true,
        'current_user_id' => true,
        'current_version_id' => true,
        'date' => true,
        'status' => true,
        'technician_status' => true,
        'scientist_status' => true,
        'doctor_status' => true,
        'priority' => true,
        'notes' => true,
        'symptoms' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'hospital' => true,
        'patient_user' => true,
        'current_user' => true,
        'current_version' => true,
        'department' => true,
        'sedation' => true,
        'case_versions' => true,
        'case_assignments' => true,
        'case_audits' => true,
        'cases_exams_procedures' => true,
        'documents' => true,
    ];

    protected array $_virtual = [
        'overall_status',
        'technician_status_label',
        'scientist_status_label',
        'doctor_status_label'
    ];

    /**
     * Get status label for a specific status value or the entity's status
     *
     * @param string|null $status Status value (null to use entity's status)
     * @return string
     */
    public function getStatusLabel(?string $status = null): string {
        $status = $status ?? $this->status;
        
        $statusLabels = [
            'draft' => 'Draft',
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'review' => 'Under Review',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];

        return $statusLabels[$status] ?? 'Unknown';
    }

    public function getPriorityLabel(): string {
        $priorityLabels = [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent'
        ];

        return $priorityLabels[$this->priority] ?? 'Unknown';
    }

    public function getPriorityColorClass(): string {
        $colorClasses = [
            'low' => 'text-success',
            'medium' => 'text-warning',
            'high' => 'text-danger',
            'urgent' => 'text-danger fw-bold'
        ];

        return $colorClasses[$this->priority] ?? 'text-muted';
    }

    public function getStatusColorClass(): string {
        $colorClasses = [
            'draft' => 'text-muted',
            'assigned' => 'text-info',
            'in_progress' => 'text-warning',
            'review' => 'text-primary',
            'completed' => 'text-success',
            'cancelled' => 'text-secondary'
        ];

        return $colorClasses[$this->status] ?? 'text-muted';
    }

    public function getStatusClass(): string {
        $badgeClasses = [
            'draft' => 'bg-secondary',
            'assigned' => 'bg-info',
            'in_progress' => 'bg-warning text-dark',
            'review' => 'bg-primary',
            'completed' => 'bg-success',
            'cancelled' => 'bg-dark'
        ];

        return $badgeClasses[$this->status] ?? 'bg-secondary';
    }

    public function getPriorityClass(): string {
        $badgeClasses = [
            'low' => 'bg-success',
            'medium' => 'bg-warning text-dark',
            'high' => 'bg-danger',
            'urgent' => 'bg-danger'
        ];

        return $badgeClasses[$this->priority] ?? 'bg-secondary';
    }

    /**
     * Get the overall status (most advanced of all role statuses)
     * Priority: completed > assigned > in_progress > draft
     *
     * @return string
     */
    protected function _getOverallStatus(): string
    {
        $statuses = [
            $this->technician_status ?? 'draft',
            $this->scientist_status ?? 'draft',
            $this->doctor_status ?? 'draft'
        ];

        // Priority order
        if (in_array('completed', $statuses)) {
            return 'completed';
        }
        if (in_array('assigned', $statuses)) {
            return 'assigned';
        }
        if (in_array('in_progress', $statuses)) {
            return 'in_progress';
        }
        return 'draft';
    }

    /**
     * Get technician status label
     *
     * @return string
     */
    protected function _getTechnicianStatusLabel(): string
    {
        return $this->getStatusLabel($this->technician_status ?? 'draft');
    }

    /**
     * Get scientist status label
     *
     * @return string
     */
    protected function _getScientistStatusLabel(): string
    {
        return $this->getStatusLabel($this->scientist_status ?? 'draft');
    }

    /**
     * Get doctor status label
     *
     * @return string
     */
    protected function _getDoctorStatusLabel(): string
    {
        return $this->getStatusLabel($this->doctor_status ?? 'draft');
    }

    public function getStatusLabelForRole(string $role): string
    {
        $statusColumn = match($role) {
            'technician' => 'technician_status',
            'scientist' => 'scientist_status',
            'doctor' => 'doctor_status',
            default => 'status'
        };

        return $this->getStatusLabel($this->{$statusColumn} ?? 'draft');
    }

    public function getStatusColorClassForRole(string $role): string
    {
        $statusColumn = match($role) {
            'technician' => 'technician_status',
            'scientist' => 'scientist_status',
            'doctor' => 'doctor_status',
            default => 'status'
        };

        $status = $this->{$statusColumn} ?? 'draft';
        
        $colorClasses = [
            'draft' => 'text-muted',
            'assigned' => 'text-info',
            'in_progress' => 'text-warning',
            'review' => 'text-primary',
            'completed' => 'text-success',
            'cancelled' => 'text-secondary'
        ];

        return $colorClasses[$status] ?? 'text-muted';
    }

    public function getStatusClassForRole(string $role): string
    {
        $statusColumn = match($role) {
            'technician' => 'technician_status',
            'scientist' => 'scientist_status',
            'doctor' => 'doctor_status',
            default => 'status'
        };

        $status = $this->{$statusColumn} ?? 'draft';
        
        $badgeClasses = [
            'draft' => 'bg-secondary',
            'assigned' => 'bg-info',
            'in_progress' => 'bg-warning text-dark',
            'review' => 'bg-primary',
            'completed' => 'bg-success',
            'cancelled' => 'bg-dark'
        ];

        return $badgeClasses[$status] ?? 'bg-secondary';
    }
}