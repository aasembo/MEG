<?php
/**
 * @var \App\View\AppView $this
 * @var object $user
 * @var object $userWithRole
 * @var object|null $currentHospital
 * @var int $totalCases
 * @var int $assignedCases
 * @var int $inProgressCases
 * @var int $completedCases
 * @var int $cancelledCases
 * @var int $urgentCases
 * @var int $highPriorityCase                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Cancelled</span>
                            <span class="small fw-semibold"><?php echo $cancelledCases; ?></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" style="width: <?php echo $totalCases > 0 ? round(($cancelledCases / $totalCases * 100), 1) : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>              </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" style="width: <?php echo $totalCases > 0 ? round(($cancelledCases / $totalCases * 100), 1) : 0; ?>%"></div>
                        </div>
                    </div>var \Cake\ORM\ResultSet $recentCases
 */
$this->setLayout('doctor');
$this->assign('title', 'Dashboard');
?>

<div class="dashboard-content">
    <!-- Welcome Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-tachometer-alt me-2 text-primary"></i>Dashboard
            </h2>
            <p class="text-muted mb-0">Welcome back, Dr. <?php echo h($userWithRole->first_name . ' ' . $userWithRole->last_name); ?></p>
        </div>
        <div>
            <?php if ($currentHospital): ?>
                <span class="badge bg-success fs-6 px-3 py-2">
                    <i class="fas fa-hospital me-2"></i><?php echo h($currentHospital->name); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Total Cases -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Total Cases</p>
                            <h3 class="mb-0 fw-bold"><?php echo number_format($totalCases); ?></h3>
                        </div>
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-folder-open"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <?php echo $this->Html->link(
                            'View All Cases <i class="fas fa-arrow-right ms-1"></i>',
                            array('controller' => 'Cases', 'action' => 'index'),
                            array('class' => 'text-primary text-decoration-none small', 'escape' => false)
                        ); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- In Progress -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">In Progress</p>
                            <h3 class="mb-0 fw-bold text-warning"><?php echo number_format($inProgressCases); ?></h3>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <?php echo $this->Html->link(
                            'View Active Cases <i class="fas fa-arrow-right ms-1"></i>',
                            array('controller' => 'Cases', 'action' => 'index', '?' => array('status' => 'in_progress')),
                            array('class' => 'text-warning text-decoration-none small', 'escape' => false)
                        ); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Cases -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Assigned</p>
                            <h3 class="mb-0 fw-bold text-info"><?php echo number_format($assignedCases); ?></h3>
                        </div>
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <?php echo $this->Html->link(
                            'View Assigned <i class="fas fa-arrow-right ms-1"></i>',
                            array('controller' => 'Cases', 'action' => 'index', '?' => array('status' => 'assigned')),
                            array('class' => 'text-info text-decoration-none small', 'escape' => false)
                        ); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Cases -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Completed</p>
                            <h3 class="mb-0 fw-bold text-success"><?php echo number_format($completedCases); ?></h3>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <?php echo $this->Html->link(
                            'View Completed <i class="fas fa-arrow-right ms-1"></i>',
                            array('controller' => 'Cases', 'action' => 'index', '?' => array('status' => 'completed')),
                            array('class' => 'text-success text-decoration-none small', 'escape' => false)
                        ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Priority Alerts -->
    <?php if ($urgentCases > 0 || $highPriorityCases > 0): ?>
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle fa-2x"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h5 class="alert-heading mb-1">Priority Cases Require Attention</h5>
                <p class="mb-0">
                    <?php if ($urgentCases > 0): ?>
                        <span class="badge bg-danger me-2"><?php echo $urgentCases; ?> Urgent</span>
                    <?php endif; ?>
                    <?php if ($highPriorityCases > 0): ?>
                        <span class="badge bg-warning"><?php echo $highPriorityCases; ?> High Priority</span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="flex-shrink-0">
                <?php echo $this->Html->link(
                    'Review Cases',
                    array('controller' => 'Cases', 'action' => 'index', '?' => array('priority' => 'urgent,high')),
                    array('class' => 'btn btn-warning')
                ); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <!-- Recent Cases -->
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2 text-primary"></i>Recent Cases
                        </h5>
                        <?php echo $this->Html->link(
                            'View All',
                            array('controller' => 'Cases', 'action' => 'index'),
                            array('class' => 'btn btn-sm btn-outline-primary')
                        ); ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if ($recentCases->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Case ID</th>
                                        <th>Patient</th>
                                        <th>Department</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Last Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentCases as $case): ?>
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-primary">#<?php echo h($case->id); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($case->patient_user): ?>
                                                <?php echo h($case->patient_user->first_name . ' ' . $case->patient_user->last_name); ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($case->department): ?>
                                                <small><?php echo h($case->department->name); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $this->Status->priorityBadge($case->priority); ?>
                                        </td>
                                        <td>
                                            <?php echo $this->Status->roleBadge($case, 'doctor', $userWithRole); ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo $case->modified ? $case->modified->format('M d, Y') : 'N/A'; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php echo $this->Html->link(
                                                    '<i class="fas fa-eye"></i>',
                                                    array('controller' => 'Cases', 'action' => 'view', $case->id),
                                                    array('class' => 'btn btn-outline-primary', 'escape' => false, 'title' => 'View')
                                                ); ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No cases assigned yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Quick Actions Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-folder-open me-2"></i>View All Cases',
                            array('controller' => 'Cases', 'action' => 'index'),
                            array('class' => 'btn btn-primary', 'escape' => false)
                        ); ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-filter me-2"></i>Filter by Status',
                            array('controller' => 'Cases', 'action' => 'index'),
                            array('class' => 'btn btn-outline-primary', 'escape' => false)
                        ); ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-exclamation-triangle me-2"></i>Priority Cases',
                            array('controller' => 'Cases', 'action' => 'index', '?' => array('priority' => 'urgent,high')),
                            array('class' => 'btn btn-outline-warning', 'escape' => false)
                        ); ?>
                    </div>
                </div>
            </div>

            <!-- Status Distribution -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2 text-info"></i>Case Status Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Assigned</span>
                            <span class="small fw-semibold"><?php echo $assignedCases; ?></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: <?php echo $totalCases > 0 ? round(($assignedCases / $totalCases * 100), 1) : 0; ?>%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">In Progress</span>
                            <span class="small fw-semibold"><?php echo $inProgressCases; ?></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: <?php echo $totalCases > 0 ? round(($inProgressCases / $totalCases * 100), 1) : 0; ?>%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Completed</span>
                            <span class="small fw-semibold"><?php echo $completedCases; ?></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $totalCases > 0 ? round(($completedCases / $totalCases * 100), 1) : 0; ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small">Cancelled</span>
                            <span class="small fw-semibold"><?php echo $cancelledCases; ?></span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-danger" style="width: <?php echo $totalCases > 0 ? round(($cancelledCases / $totalCases * 100), 1) : 0; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}

.table tbody tr {
    transition: background-color 0.2s;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.progress {
    background-color: #e9ecef;
}

.alert {
    border-left: 4px solid #ffc107;
}
</style>