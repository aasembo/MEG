<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;
use App\Constants\SiteConstants;

class CasesExamsProcedure extends Entity {
    protected array $_accessible = [
        'case_id' => true,
        'exams_procedure_id' => true,
        'scheduled_at' => true,
        'status' => true,
        'notes' => true,
        'created' => true,
        'modified' => true,
        'case' => true,
        'exams_procedure' => true,
        'documents' => true,
    ];

    protected array $_hidden = [];

    public function getStatusLabel(): string {
        $statusLabels = [
            SiteConstants::CASE_STATUS_PENDING => 'Pending',
            SiteConstants::CASE_STATUS_SCHEDULED => 'Scheduled',
            SiteConstants::CASE_STATUS_IN_PROGRESS => 'In Progress',
            SiteConstants::CASE_STATUS_COMPLETED => 'Completed',
            SiteConstants::CASE_STATUS_CANCELLED => 'Cancelled'
        ];

        return $statusLabels[$this->status] ?? 'Unknown';
    }

    public function getStatusBadgeClass(): string {
        $badgeClasses = [
            SiteConstants::CASE_STATUS_PENDING => 'bg-warning text-dark',
            SiteConstants::CASE_STATUS_SCHEDULED => 'bg-info',
            SiteConstants::CASE_STATUS_IN_PROGRESS => 'bg-primary',
            SiteConstants::CASE_STATUS_COMPLETED => 'bg-success',
            SiteConstants::CASE_STATUS_CANCELLED => 'bg-danger'
        ];

        return $badgeClasses[$this->status] ?? 'bg-secondary';
    }

    public function hasDocuments(): bool {
        return !empty($this->documents);
    }

    public function getDocumentCount(): int {
        return is_array($this->documents) ? count($this->documents) : 0;
    }
}