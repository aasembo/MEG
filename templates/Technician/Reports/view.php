<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Report $report
 */
$reportData = json_decode($report->report_data, true) ?? [];
$reportContent = $reportData['content'] ?? '';

$this->assign('title', 'Report #' . $report->id);
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-file-medical-alt me-2"></i>Report #<?php echo  h($report->id) ?>
                    </h2>
                    <p class="mb-0">
                        <?php if (isset($report->case->patient_user)): ?>
                            <i class="fas fa-user-injured me-2"></i><?php echo  $this->PatientMask->displayName($report->case->patient_user) ?>
                        <?php endif; ?>
                        <?php if (isset($report->hospital)): ?>
                            <span class="ms-3"><i class="fas fa-hospital me-2"></i><?php echo  h($report->hospital->name) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-file-pdf me-1"></i>Preview',
                            ['action' => 'preview', $report->id],
                            ['class' => 'btn btn-light', 'escape' => false, 'target' => '_blank']
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-edit me-1"></i>Edit',
                            ['action' => 'edit', $report->id],
                            ['class' => 'btn btn-light', 'escape' => false]
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-arrow-left me-1"></i>Back',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-light', 'escape' => false]
                        ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Report Information -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-file-medical-alt me-2 text-primary"></i>Report Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-semibold">Report ID:</td>
                                    <td><span class="badge bg-primary"><?php echo  h($report->id) ?></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Case:</td>
                                    <td>
                                        <?php echo  $this->Html->link(
                                            '<i class="fas fa-external-link-alt me-1"></i>Case #' . $report->case_id,
                                            ['controller' => 'Cases', 'action' => 'view', $report->case_id],
                                            ['class' => 'text-decoration-none', 'escape' => false]
                                        ) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Patient:</td>
                                    <td>
                                        <?php if (isset($report->case->patient_user)): ?>
                                            <?php echo  $this->PatientMask->displayName($report->case->patient_user) ?>
                                            <br><small class="text-muted">ID: <?php echo  h($report->case->patient_user->id) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">No patient assigned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Hospital:</td>
                                    <td>
                                        <?php if (isset($report->hospital)): ?>
                                            <i class="fas fa-hospital me-1 text-primary"></i>
                                            <?php echo  h($report->hospital->name) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-semibold">Status:</td>
                                    <td>
                                        <span class="badge bg-<?php echo  $report->status === 'approved' ? 'success' : ($report->status === 'reviewed' ? 'warning' : 'secondary') ?>">
                                            <i class="fas fa-<?php echo  $report->status === 'approved' ? 'check-circle' : ($report->status === 'reviewed' ? 'clock' : 'circle') ?> me-1"></i>
                                            <?php echo  h(ucfirst($report->status)) ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Confidence Score:</td>
                                    <td>
                                        <?php if ($report->confidence_score): ?>
                                            <span class="badge bg-info"><?php echo  h($report->confidence_score) ?>%</span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Created:</td>
                                    <td><?php echo  $report->created->format('F j, Y \a\t g:i A') ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Last Modified:</td>
                                    <td><?php echo  $report->modified->format('F j, Y \a\t g:i A') ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Content -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-file-alt me-2 text-primary"></i>MEG Report Content
                        </h5>
                        <span class="badge bg-secondary">Rich Text</span>
                    </div>
                </div>
                <div class="card-body bg-white">
                    <?php if ($reportContent): ?>
                        <div class="report-content-display p-4 rounded">
                            <?php echo  $reportContent ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>No Content Available:</strong> This report doesn't have any content yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Technician Notes -->
            <?php if ($report->technician_notes): ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-warning bg-opacity-10 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-sticky-note me-2 text-warning"></i>Internal Technician Notes
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="alert alert-warning border-0 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Internal Use Only:</strong> These notes are not included in the final report.
                    </div>
                    <div class="mt-3 p-3 bg-light rounded">
                        <?php echo  nl2br(h($report->technician_notes)) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bolt me-2 text-primary"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="d-grid gap-2">
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-edit me-2"></i>Edit Report',
                            ['action' => 'edit', $report->id],
                            ['class' => 'btn btn-primary d-flex align-items-center justify-content-center', 'escape' => false]
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-eye me-2"></i>Preview',
                            ['action' => 'preview', $report->id],
                            [
                                'class' => 'btn btn-success d-flex align-items-center justify-content-center',
                                'escape' => false,
                                'target' => '_blank'
                            ]
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-download me-2"></i>Download PDF',
                            ['action' => 'download', $report->id, 'pdf'],
                            ['class' => 'btn btn-info d-flex align-items-center justify-content-center', 'escape' => false]
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-file-medical me-2"></i>View Case',
                            ['controller' => 'Cases', 'action' => 'view', $report->case_id],
                            ['class' => 'btn btn-outline-primary d-flex align-items-center justify-content-center', 'escape' => false]
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-list me-2"></i>All Reports',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-secondary d-flex align-items-center justify-content-center', 'escape' => false]
                        ); ?>
                    </div>
                </div>
            </div>

            <!-- Report Status -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Report Status
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="text-center mb-3">
                        <div class="h4 mb-2">
                            <span class="badge bg-<?php echo  $report->status === 'approved' ? 'success' : ($report->status === 'reviewed' ? 'warning' : 'secondary') ?> p-3">
                                <i class="fas fa-<?php echo  $report->status === 'approved' ? 'check-circle' : ($report->status === 'reviewed' ? 'clock' : 'circle') ?> me-2"></i>
                                <?php echo  h(ucfirst($report->status)) ?>
                            </span>
                        </div>
                    </div>

                    <div class="progress mb-3" style="height: 8px;">
                        <div class="progress-bar bg-<?php echo  $report->status === 'approved' ? 'success' : ($report->status === 'reviewed' ? 'warning' : 'secondary') ?>" 
                             style="width: <?php echo  $report->status === 'approved' ? '100' : ($report->status === 'reviewed' ? '75' : '25') ?>%">
                        </div>
                    </div>

                    <div class="small">
                        <div class="d-flex align-items-center mb-2">
                            <div class="me-2">
                                <i class="fas fa-circle text-<?php echo  $report->status === 'pending' ? 'primary' : 'success' ?>"></i>
                            </div>
                            <div class="<?php echo  $report->status === 'pending' ? 'fw-semibold' : 'text-muted' ?>">
                                Draft Created
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="me-2">
                                <i class="fas fa-circle text-<?php echo  $report->status === 'reviewed' ? 'primary' : ($report->status === 'approved' ? 'success' : 'muted') ?>"></i>
                            </div>
                            <div class="<?php echo  $report->status === 'reviewed' ? 'fw-semibold' : ($report->status === 'approved' ? 'text-success' : 'text-muted') ?>">
                                Under Review
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <i class="fas fa-circle text-<?php echo  $report->status === 'approved' ? 'success' : 'muted' ?>"></i>
                            </div>
                            <div class="<?php echo  $report->status === 'approved' ? 'fw-semibold text-success' : 'text-muted' ?>">
                                Approved & Final
                            </div>
                        </div>
                    </div>

                    <?php if ($report->confidence_score): ?>
                    <div class="border-top pt-3 mt-3">
                        <div class="small text-muted mb-1">Confidence Score</div>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-<?php echo  $report->confidence_score >= 80 ? 'success' : ($report->confidence_score >= 60 ? 'warning' : 'danger') ?>" 
                                 style="width: <?php echo  $report->confidence_score ?>%">
                                <?php echo  $report->confidence_score ?>%
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Report Statistics -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>Report Stats
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="h5 mb-1"><?php echo  $report->created->diffInDays() ?></div>
                            <div class="small text-muted">Days Old</div>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-1"><?php echo  str_word_count(strip_tags($reportContent)) ?></div>
                            <div class="small text-muted">Words</div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Created:</span>
                            <strong><?php echo  $report->created->format('M j, Y') ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Last modified:</span>
                            <strong><?php echo  $report->modified->diffForHumans() ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Content length:</span>
                            <strong><?php echo  number_format(strlen($reportContent)) ?> chars</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Options -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-file-export me-2 text-primary"></i>Export Options
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="d-grid gap-2">
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-file-pdf me-2"></i>PDF Format',
                            ['action' => 'download', $report->id, 'pdf'],
                            ['class' => 'btn btn-outline-danger d-flex align-items-center justify-content-center', 'escape' => false]
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-file-word me-2"></i>Word Document',
                            ['action' => 'download', $report->id, 'docx'],
                            ['class' => 'btn btn-outline-primary d-flex align-items-center justify-content-center', 'escape' => false]
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-file-code me-2"></i>HTML Format',
                            ['action' => 'download', $report->id, 'html'],
                            ['class' => 'btn btn-outline-success d-flex align-items-center justify-content-center', 'escape' => false]
                        ); ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-file-alt me-2"></i>Plain Text',
                            ['action' => 'download', $report->id, 'txt'],
                            ['class' => 'btn btn-outline-secondary d-flex align-items-center justify-content-center', 'escape' => false]
                        ); ?>
                    </div>
                    
                    <div class="mt-3 p-2 bg-light rounded">
                        <div class="small text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Export Tips:</strong> PDF for official documents, Word for editing, HTML for web sharing.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Bootstrap 5 Styling */
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}


.fw-semibold {
    font-weight: 600;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.badge {
    font-weight: 500;
    border-radius: 6px;
}


/* Progress bar animations */
.progress-bar {
    transition: width 0.6s ease;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 0.5rem;
    }
}
</style>
