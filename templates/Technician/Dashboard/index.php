<?php
/**
 * @var \App\View\AppView $this
 * @var object $user
 * @var object|null $currentHospital
 * @var array $caseStats
 * @var array $reportStats
 */
$this->assign('title', 'Technician Dashboard');
?>

<div class="container-fluid px-4 py-4">
    <!-- Welcome Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2rem;">
                                <i class="fas fa-user-md"></i>
                            </div>
                        </div>
                        <div>
                            <h2 class="mb-2 fw-bold">Welcome back, <?php echo h($user->name ?? $user->username); ?>!</h2>
                            <p class="mb-0 fs-5">
                                <i class="fas fa-briefcase me-2"></i>Technician Dashboard - Manage cases, reports and coordinate workflow
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php if ($currentHospital): ?>
                        <div class="d-inline-block bg-white bg-opacity-25 rounded-pill px-4 py-2">
                            <i class="fas fa-hospital me-2"></i>
                            <strong><?php echo h($currentHospital->name); ?></strong>
                        </div>
                    <?php else: ?>
                        <div class="d-inline-block bg-white bg-opacity-25 rounded-pill px-4 py-2">
                            <i class="fas fa-globe me-2"></i>
                            <strong>System Wide</strong>
                        </div>
                    <?php endif; ?>
                    <div class="mt-2">
                        <small><i class="fas fa-clock me-1"></i><?php echo date('l, F j, Y'); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Overview Cards -->
    <?php if (!empty($caseStats) && $currentHospital): ?>
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow h-100 border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 text-uppercase small fw-semibold">Total Cases</p>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($caseStats['total_cases']); ?></h2>
                        </div>
                        <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.75rem;">
                            <i class="fas fa-file-medical"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-arrow-up me-1"></i>Created by you
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <?php 
        $statusCounts = [];
        foreach ($caseStats['cases_by_status'] as $statusCase) {
            $statusCounts[$statusCase->status] = $statusCase->count;
        }
        
        $reportStatusCounts = [];
        if (!empty($reportStats['reports_by_status'])) {
            foreach ($reportStats['reports_by_status'] as $statusReport) {
                $reportStatusCounts[$statusReport->status] = $statusReport->count;
            }
        }
        ?>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 text-uppercase small fw-semibold">Pending Cases</p>
                            <h2 class="mb-0 fw-bold"><?php echo number_format(($statusCounts['draft'] ?? 0) + ($statusCounts['assigned'] ?? 0)); ?></h2>
                        </div>
                        <div class="bg-warning text-white rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.75rem;">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-hourglass-half me-1"></i>Needs attention
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow h-100 border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 text-uppercase small fw-semibold">Total Reports</p>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($reportStats['total_reports'] ?? 0); ?></h2>
                        </div>
                        <div class="bg-success text-white rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.75rem;">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check me-1"></i>Hospital-wide
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow h-100 border-start border-info border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 text-uppercase small fw-semibold">Completed</p>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($statusCounts['completed'] ?? 0); ?></h2>
                        </div>
                        <div class="bg-info text-white rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.75rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-info bg-opacity-10 text-info">
                            <i class="fas fa-chart-line me-1"></i>This month
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Action Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Create New Case</h5>
                    <p class="card-text text-muted small mb-3">Start a new case with patient details and medical information</p>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-right me-2"></i>Create Case',
                        ['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'add'],
                        ['class' => 'btn btn-primary btn-sm', 'escape' => false]
                    ); ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-success bg-opacity-10 text-success rounded d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Create Report</h5>
                    <p class="card-text text-muted small mb-3">Generate detailed reports with multi-format export options</p>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-right me-2"></i>View Cases',
                        ['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'index'],
                        ['class' => 'btn btn-success btn-sm', 'escape' => false]
                    ); ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-info bg-opacity-10 text-info rounded d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">View Reports</h5>
                    <p class="card-text text-muted small mb-3">Browse and manage all reports with status tracking</p>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-right me-2"></i>All Reports',
                        ['prefix' => 'Technician', 'controller' => 'Reports', 'action' => 'index'],
                        ['class' => 'btn btn-info btn-sm', 'escape' => false]
                    ); ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-warning bg-opacity-10 text-warning rounded d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Manage Patients</h5>
                    <p class="card-text text-muted small mb-3">Add and manage patient records and medical history</p>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-right me-2"></i>View Patients',
                        ['prefix' => 'Technician', 'controller' => 'Patients', 'action' => 'index'],
                        ['class' => 'btn btn-warning btn-sm', 'escape' => false]
                    ); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="row g-4">
        <!-- Recent Cases -->
        <?php if (!empty($caseStats['recent_cases'])): ?>
        <div class="col-lg-8">
            <div class="card border-0 shadow h-100">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-clock text-primary me-2"></i>Recent Cases
                        </h5>
                        <?php echo $this->Html->link(
                            'View All <i class="fas fa-arrow-right ms-1"></i>',
                            ['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'index'],
                            ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false]
                        ); ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 ps-4 fw-semibold text-uppercase small">Case ID</th>
                                    <th class="border-0 fw-semibold text-uppercase small">Patient</th>
                                    <th class="border-0 fw-semibold text-uppercase small">Status</th>
                                    <th class="border-0 fw-semibold text-uppercase small">Date</th>
                                    <th class="border-0 text-end pe-4 fw-semibold text-uppercase small">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($caseStats['recent_cases'] as $case): ?>
                                <tr>
                                    <td class="ps-4">
                                        <strong class="text-primary">#<?php echo $case->id; ?></strong>
                                    </td>
                                    <td>
                                        <?php if (isset($case->patient_user) && $case->patient_user): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <span><?php echo $this->PatientMask->displayName($case->patient_user); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No patient</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill <?php 
                                            echo match($case->status) {
                                                'draft' => 'bg-secondary',
                                                'assigned' => 'bg-info',
                                                'in_progress' => 'bg-warning',
                                                'review' => 'bg-primary',
                                                'completed' => 'bg-success',
                                                'cancelled' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        ?>"><?php echo h(ucfirst(str_replace('_', ' ', $case->status))); ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="far fa-calendar-alt me-1"></i>
                                            <?php echo $case->created->format('M j, Y'); ?>
                                        </small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <?php echo $this->Html->link(
                                            '<i class="fas fa-eye"></i>',
                                            ['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'view', $case->id],
                                            ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'title' => 'View Case']
                                        ); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="col-lg-8">
            <div class="card border-0 shadow h-100">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle text-primary me-2"></i>Getting Started
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-file-medical fa-4x text-muted opacity-50"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Welcome to Case Management!</h5>
                        <p class="text-muted mb-4">As a technician, you can:</p>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-plus text-primary me-2"></i>
                                    Create new cases with patient details
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-file-alt text-success me-2"></i>
                                    Generate and export reports
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-chart-line text-info me-2"></i>
                                    Track case progress and status
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-users text-warning me-2"></i>
                                    Coordinate with scientists
                                </div>
                            </div>
                        </div>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-plus-circle me-2"></i>Create Your First Case',
                            ['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'add'],
                            ['class' => 'btn btn-primary btn-lg', 'escape' => false]
                        ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Sidebar with Profile and System Status -->
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-circle text-primary me-2"></i>Profile
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-md fa-2x"></i>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo h($user->name ?? $user->username); ?></h5>
                        <?php if (isset($user->role) && $user->role): ?>
                            <span class="badge bg-primary rounded-pill">
                                <?php echo h($this->Role->label($user->role->type)); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <hr>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="fas fa-user me-2"></i>Username</span>
                            <strong class="small"><?php echo h($user->username ?? 'N/A'); ?></strong>
                        </div>
                        <?php if ($currentHospital): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="fas fa-hospital me-2"></i>Hospital</span>
                            <strong class="small"><?php echo h($currentHospital->name); ?></strong>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small"><i class="fas fa-clock me-2"></i>Last Active</span>
                            <strong class="small"><?php echo $this->Time->format($user->modified ?? 'now', 'MMM d, HH:mm'); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Reports (if available) -->
            <?php if (!empty($reportStats['recent_reports'])): ?>
            <div class="card border-0 shadow mt-4">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-file-alt text-info me-2"></i>Recent Reports
                        </h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($reportStats['recent_reports'], 0, 3) as $report): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold small mb-1">
                                        Case #<?php echo $report->case_id; ?>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="far fa-clock me-1"></i>
                                        <?php echo $report->created->format('M j'); ?>
                                    </div>
                                </div>
                                <span class="badge bg-<?php echo $report->status === 'completed' ? 'success' : 'warning'; ?> rounded-pill">
                                    <?php echo h(ucwords(str_replace('_', ' ', $report->status))); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer bg-white text-center py-2">
                        <?php echo $this->Html->link(
                            'View All Reports <i class="fas fa-arrow-right ms-1"></i>',
                            ['prefix' => 'Technician', 'controller' => 'Reports', 'action' => 'index'],
                            ['class' => 'btn btn-sm btn-link text-decoration-none', 'escape' => false]
                        ); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>