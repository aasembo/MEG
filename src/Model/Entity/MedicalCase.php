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
        'priority' => true,
        'notes' => true,
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

    public function getStatusLabel(): string {
        $statusLabels = [
            'draft' => 'Draft',
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'review' => 'Under Review',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled'
        ];

        return $statusLabels[$this->status] ?? 'Unknown';
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
}