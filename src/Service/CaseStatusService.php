<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\TableRegistry;
use Cake\Log\Log;
use App\Constants\SiteConstants;

/**
 * CaseStatusService
 * 
 * Handles role-specific status transitions for cases
 * - Automatic status change on first view
 * - Status transitions on assignment
 * - Cascade completion to all roles
 * - Audit logging and notifications
 */
class CaseStatusService
{
    private $CasesTable;
    private $CaseAuditsTable;
    
    public function __construct()
    {
        $this->CasesTable = TableRegistry::getTableLocator()->get('Cases');
        $this->CaseAuditsTable = TableRegistry::getTableLocator()->get('CaseAudits');
    }
    
    /**
     * Transition status when a user views a case for the first time
     * Only changes from 'draft' to 'in_progress' on first view
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @param string $userRole Role type: 'technician', 'scientist', or 'doctor'
     * @param int $userId User ID for audit logging
     * @return bool True if status was changed
     */
    public function transitionOnView($case, string $userRole, int $userId): bool
    {
        $statusColumn = $this->getRoleStatusColumn($userRole);
        $currentStatus = $case->{$statusColumn};
        
        // Only transition from 'draft' to 'in_progress' on first view
        if ($currentStatus === SiteConstants::CASE_STATUS_DRAFT) {
            $case->{$statusColumn} = SiteConstants::CASE_STATUS_IN_PROGRESS;
            
            // Also update global status if it's still draft
            if ($case->status === SiteConstants::CASE_STATUS_DRAFT) {
                $case->status = SiteConstants::CASE_STATUS_IN_PROGRESS;
            }
            
            if ($this->CasesTable->save($case)) {
                // Log the status change
                $this->logStatusChange(
                    $case->id,
                    $statusColumn,
                    SiteConstants::CASE_STATUS_DRAFT,
                    SiteConstants::CASE_STATUS_IN_PROGRESS,
                    $userId,
                    "Auto-transitioned to in_progress on first view by {$userRole}"
                );
                
                Log::info("Case #{$case->id}: {$userRole} status changed to in_progress on first view");
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Transition status when case is assigned from one role to another
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @param string $fromRole Role assigning the case: 'technician' or 'scientist'
     * @param string $toRole Role receiving the case: 'scientist' or 'doctor'
     * @param int $userId User ID performing the assignment
     * @return bool True if status was changed
     */
    public function transitionOnAssignment($case, string $fromRole, string $toRole, int $userId): bool
    {
        $fromColumn = $this->getRoleStatusColumn($fromRole);
        $toColumn = $this->getRoleStatusColumn($toRole);
        
        // Set assigner's status to 'completed' (they finished their part)
        $oldFromStatus = $case->{$fromColumn};
        $case->{$fromColumn} = SiteConstants::CASE_STATUS_COMPLETED;
        
        // Set assignee's status to 'assigned' (ready for them to start)
        $oldToStatus = $case->{$toColumn};
        $case->{$toColumn} = SiteConstants::CASE_STATUS_ASSIGNED;
        
        // Update global status to 'in_progress' (not 'assigned')
        $oldGlobalStatus = $case->status;
        $newGlobalStatus = SiteConstants::CASE_STATUS_IN_PROGRESS;
        $case->status = $newGlobalStatus;
        
        if ($this->CasesTable->save($case)) {
            // Log status changes
            $this->logStatusChange(
                $case->id,
                $fromColumn,
                $oldFromStatus,
                SiteConstants::CASE_STATUS_COMPLETED,
                $userId,
                "{$fromRole} completed and assigned to {$toRole}"
            );
            
            $this->logStatusChange(
                $case->id,
                $toColumn,
                $oldToStatus,
                SiteConstants::CASE_STATUS_ASSIGNED,
                $userId,
                "Case assigned to {$toRole} from {$fromRole}"
            );
            
            // Only log global status change if it actually changed
            if ($oldGlobalStatus !== $newGlobalStatus) {
                $this->logStatusChange(
                    $case->id,
                    'status',
                    $oldGlobalStatus,
                    $newGlobalStatus,
                    $userId,
                    "Global status updated to in_progress on assignment"
                );
            }
            
            Log::info("Case #{$case->id}: Assigned from {$fromRole} to {$toRole}, global status: {$newGlobalStatus}");
            
            // TODO: Send notification to assignee
            // $this->sendAssignmentNotification($case, $toRole);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Cascade completion status to all roles when case is completed
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @param int $userId User ID completing the case
     * @return bool True if status was changed
     */
    public function cascadeCompletion($case, int $userId): bool
    {
        // Set all role statuses to 'completed'
        $case->technician_status = SiteConstants::CASE_STATUS_COMPLETED;
        $case->scientist_status = SiteConstants::CASE_STATUS_COMPLETED;
        $case->doctor_status = SiteConstants::CASE_STATUS_COMPLETED;
        $case->status = SiteConstants::CASE_STATUS_COMPLETED;
        
        if ($this->CasesTable->save($case)) {
            // Log completion for all roles
            foreach (['technician_status', 'scientist_status', 'doctor_status'] as $statusColumn) {
                $this->logStatusChange(
                    $case->id,
                    $statusColumn,
                    'in_progress', // Assume it was in progress
                    SiteConstants::CASE_STATUS_COMPLETED,
                    $userId,
                    "Case completed - status cascaded to all roles"
                );
            }
            
            Log::info("Case #{$case->id}: Completed and cascaded to all roles");
            
            // TODO: Send completion notifications
            // $this->sendCompletionNotifications($case);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get the status column name for a given role
     *
     * @param string $role Role type
     * @return string Column name
     */
    private function getRoleStatusColumn(string $role): string
    {
        return match($role) {
            'technician' => 'technician_status',
            'scientist' => 'scientist_status',
            'doctor' => 'doctor_status',
            default => 'status' // Fallback to global status
        };
    }
    
    /**
     * Log status change to case audits table
     *
     * @param int $caseId Case ID
     * @param string $field Field name that changed
     * @param string $oldValue Old status value
     * @param string $newValue New status value
     * @param int $userId User who made the change
     * @param string $notes Additional notes
     */
    private function logStatusChange(
        int $caseId,
        string $field,
        ?string $oldValue,
        string $newValue,
        int $userId,
        string $notes = ''
    ): void {
        try {
            $audit = $this->CaseAuditsTable->newEntity([
                'case_id' => $caseId,
                'changed_by' => $userId,
                'field_name' => $field,
                'old_value' => $oldValue ?? '',
                'new_value' => $newValue,
                'notes' => $notes,
                'timestamp' => new \DateTime()
            ]);
            
            $this->CaseAuditsTable->save($audit);
        } catch (\Exception $e) {
            Log::error("Failed to log status change for case #{$caseId}: " . $e->getMessage());
        }
    }
    
    /**
     * Get the current role-specific status for display
     *
     * @param \App\Model\Entity\MedicalCase $case The case entity
     * @param string $userRole Current user's role
     * @return string Status value for the role
     */
    public function getRoleStatus($case, string $userRole): string
    {
        $statusColumn = $this->getRoleStatusColumn($userRole);
        return $case->{$statusColumn} ?? SiteConstants::CASE_STATUS_DRAFT;
    }
}
