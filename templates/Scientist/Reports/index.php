<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Report> $reports
 */

// Helper function to determine report creator role
function getReportCreatorRole($report) {
    // First check if we have the user relationship loaded with role
    if (!empty($report->user) && !empty($report->user->role) && !empty($report->user->role->name)) {
        $roleName = strtolower($report->user->role->name);
        // Map database role names to our system role names
        return match($roleName) {
            'doctor' => 'doctor',
            'scientist' => 'scientist', 
            'technician' => 'technician',
            default => strtolower($report->user->role->name)
        };
    }
    
    // Fallback: determine by workflow data (less reliable)
    // Check if it has scientist review data - if yes, scientist has worked on it
    if (!empty($report->scientist_review)) {
        // If it also has doctor approval, doctor has final say
        if (!empty($report->doctor_approval)) {
            return 'doctor';
        }
        return 'scientist';
    }
    // Default to technician if no scientist review
    return 'technician';
}

// Helper function to get role hierarchy level
function getRoleLevel($role) {
    return match($role) {
        'technician' => 1,
        'scientist' => 2,
        'doctor' => 3,
        default => 0
    };
}

// Group reports by case for hierarchy display
$reportsByCase = [];
foreach ($reports as $report) {
    $caseId = $report->case_id;
    if (!isset($reportsByCase[$caseId])) {
        $reportsByCase[$caseId] = [
            'case' => $report->case,
            'reports' => []
        ];
    }
    $reportsByCase[$caseId]['reports'][] = $report;
}

// Sort reports within each case by hierarchy
foreach ($reportsByCase as &$caseData) {
    usort($caseData['reports'], function($a, $b) {
        $roleA = getReportCreatorRole($a);
        $roleB = getReportCreatorRole($b);
        $levelA = getRoleLevel($roleA);
        $levelB = getRoleLevel($roleB);
        
        if ($levelA === $levelB) {
            return $b->created <=> $a->created; // Newer first if same role
        }
        return $levelA <=> $levelB; // Lower level first (technician -> scientist -> doctor)
    });
}
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-success text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-file-alt me-2"></i>Reports Management
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Reports are organized by case showing the workflow hierarchy: Technician → Scientist → Doctor
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <!-- Role Legend -->
                    <div class="small">
                        <div class="mb-1">
                            <i class="fas fa-circle text-info me-1"></i> Technician Report
                        </div>
                        <div class="mb-1">
                            <i class="fas fa-circle text-warning me-1"></i> Scientist Report
                        </div>
                        <div class="mb-1">
                            <i class="fas fa-circle text-danger me-1"></i> Doctor Approved
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($reportsByCase)): ?>
    
    <?php foreach ($reportsByCase as $caseId => $caseData): ?>
    <!-- Case Group -->
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-folder-open me-2 text-success"></i>
                        Case #<?php echo  h($caseId) ?>
                        <?php if (isset($caseData['case']->patient_user)): ?>
                            - <?php echo  $this->PatientMask->displayName($caseData['case']->patient_user) ?>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="col-md-4 text-md-end">
                    <span class="badge bg-success">
                        <?php echo  count($caseData['reports']) ?> Report<?php echo  count($caseData['reports']) !== 1 ? 's' : '' ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 fw-semibold text-uppercase small text-muted ps-4">Report</th>
                            <th class="border-0 fw-semibold text-uppercase small text-muted">Creator Role</th>
                            <th class="border-0 fw-semibold text-uppercase small text-muted">Hospital</th>
                            <th class="border-0 fw-semibold text-uppercase small text-muted">Status</th>
                            <th class="border-0 fw-semibold text-uppercase small text-muted">Workflow Stage</th>
                            <th class="border-0 fw-semibold text-uppercase small text-muted">Created</th>
                            <th class="border-0 fw-semibold text-uppercase small text-muted text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($caseData['reports'] as $index => $report): ?>
                        <?php 
                        $creatorRole = getReportCreatorRole($report);
                        $roleLevel = getRoleLevel($creatorRole);
                        $isFirstReport = $index === 0;
                        
                        // Role-based styling
                        $roleColors = [
                            'technician' => 'info',
                            'scientist' => 'warning', 
                            'doctor' => 'danger'
                        ];
                        $roleColor = $roleColors[$creatorRole] ?? 'secondary';
                        
                        // Hierarchy indicators
                        $hierarchyIcon = match($creatorRole) {
                            'technician' => 'fa-user-cog',
                            'scientist' => 'fa-user-graduate',
                            'doctor' => 'fa-user-md',
                            default => 'fa-user'
                        };
                        
                        // Progress indicators
                        $workflowStage = match($creatorRole) {
                            'technician' => 'Initial Analysis',
                            'scientist' => 'Scientific Review',
                            'doctor' => 'Medical Approval',
                            default => 'Unknown'
                        };
                        ?>
                        <tr class="<?php echo  $isFirstReport ? 'table-active' : '' ?>">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <!-- Hierarchy Line for non-first reports -->
                                    <?php if (!$isFirstReport): ?>
                                    <div class="me-2" style="width: 20px;">
                                        <div class="border-start border-2 border-muted position-relative" style="height: 30px; margin-left: 10px;">
                                            <div class="position-absolute" style="top: 15px; left: -5px; width: 10px; height: 2px; background-color: #6c757d;"></div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="bg-<?php echo  $roleColor ?> text-white rounded d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas <?php echo  $hierarchyIcon ?>"></i>
                                    </div>
                                    <div>
                                        <span class="fw-semibold">Report #<?php echo  h($report->id) ?></span>
                                        <br><small class="text-muted">
                                            <?php echo  $isFirstReport ? 'Primary Report' : 'Follow-up Report' ?>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-<?php echo  $roleColor ?> me-2">
                                        <i class="fas <?php echo  $hierarchyIcon ?> me-1"></i>
                                        <?php echo  h(ucfirst($creatorRole)) ?>
                                    </span>
                                    <?php if ($roleLevel > 1): ?>
                                    <small class="text-muted">
                                        Level <?php echo  $roleLevel ?>
                                    </small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-success">
                                    <?php echo  h($report->hospital->name ?? 'Unknown') ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClass = match($report->status) {
                                    'pending' => 'warning',
                                    'reviewed' => 'info',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo  $statusClass ?>">
                                    <?php echo  h(ucfirst($report->status)) ?>
                                </span>
                                
                                <?php if ($report->confidence_score): ?>
                                <br><small class="text-muted">
                                    Confidence: <?php echo  h($report->confidence_score) ?>%
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-<?php echo  $roleColor ?>"><?php echo  $workflowStage ?></span>
                                    
                                    <!-- Progress indicators -->
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-<?php echo  $roleColor ?>" 
                                             style="width: <?php echo  ($roleLevel / 3) * 100 ?>%"></div>
                                    </div>
                                    
                                    <small class="text-muted mt-1">
                                        Step <?php echo  $roleLevel ?> of 3
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold"><?php echo  h($report->created->format('M j, Y')) ?></span>
                                    <small class="text-muted"><?php echo  h($report->created->format('g:i A')) ?></small>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <?php echo  $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        ['action' => 'view', $report->id],
                                        [
                                            'class' => 'btn btn-sm btn-outline-success',
                                            'escape' => false,
                                            'data-bs-toggle' => 'tooltip',
                                            'title' => 'View Report'
                                        ]
                                    ) ?>
                                    
                                    <?php 
                                    $currentUserId = $this->request->getAttribute('identity')?->getIdentifier();
                                    if ($creatorRole === 'scientist' && $currentUserId && $report->user_id == $currentUserId): 
                                    ?>
                                    <?php echo  $this->Html->link(
                                        '<i class="fas fa-edit"></i>',
                                        ['action' => 'edit', $report->id],
                                        [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'escape' => false,
                                            'data-bs-toggle' => 'tooltip',
                                            'title' => 'Edit Report'
                                        ]
                                    ) ?>
                                    <?php endif; ?>
                                    
                                    <?php echo  $this->Html->link(
                                        '<i class="fas fa-download"></i>',
                                        ['action' => 'download', $report->id, 'pdf'],
                                        [
                                            'class' => 'btn btn-sm btn-outline-info',
                                            'escape' => false,
                                            'data-bs-toggle' => 'tooltip',
                                            'title' => 'Download PDF'
                                        ]
                                    ) ?>
                                    
                                    <?php if ($creatorRole === 'technician'): ?>
                                    <?php echo  $this->Html->link(
                                        '<i class="fas fa-level-up-alt"></i>',
                                        ['action' => 'add', '?' => ['case_id' => $caseId]],
                                        [
                                            'class' => 'btn btn-sm btn-outline-warning',
                                            'escape' => false,
                                            'data-bs-toggle' => 'tooltip',
                                            'title' => 'Create Scientist Report'
                                        ]
                                    ) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <?php else: ?>
    <!-- Empty State -->
    <div class="card border-0 shadow">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                <h5 class="fw-bold">No Reports Yet</h5>
                <p class="text-muted">
                    Reports will appear here when they are created from cases.<br>
                    <small>The workflow hierarchy is: <strong>Technician → Scientist → Doctor</strong></small>
                </p>
            </div>
            <?php echo  $this->Html->link(
                '<i class="fas fa-arrow-left me-2"></i>Go to Cases',
                ['controller' => 'Cases', 'action' => 'index'],
                ['class' => 'btn btn-success', 'escape' => false]
            ) ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Hierarchy styling */
.table-active {
    --bs-table-bg: rgba(40, 167, 69, 0.05) !important;
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.3s ease;
}

/* Role-based hover effects */
.table-hover tbody tr:hover {
    background-color: rgba(40, 167, 69, 0.02) !important;
}

/* Hierarchy line styling */
.border-start {
    border-color: #dee2e6 !important;
}

/* Badge animations */
.badge {
    transition: all 0.2s ease;
}

.badge:hover {
    transform: scale(1.05);
}

/* Button group enhancements */
.btn-group .btn {
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add smooth animations for cards
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.style.animation = 'fadeInUp 0.5s ease forwards';
    });
});

// CSS Animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);
</script>