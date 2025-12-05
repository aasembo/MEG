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
 * @var int $highPriorityCases
 * @var \Cake\ORM\ResultSet $recentCases
 */
$this->assign('title', 'Medical Dashboard');
?>

<div class="container-fluid px-4 py-4">
    <!-- Welcome Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-danger text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <div class="bg-white text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2rem;">
                                <i class="fas fa-user-md"></i>
                            </div>
                        </div>
                        <div>
                            <h2 class="mb-2 fw-bold">Welcome back, Dr. <?php echo h($userWithRole->first_name . ' ' . $userWithRole->last_name); ?>!</h2>
                            <p class="mb-0 fs-5">
                                <i class="fas fa-stethoscope me-2"></i>Medical Dashboard - Provide expert medical care and oversight
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

    <!-- Management Action Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-danger bg-opacity-10 text-danger rounded d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-folder-medical"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Manage Cases</h5>
                    <p class="card-text text-muted small mb-3">Review, approve, and oversee medical cases</p>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-right me-2"></i>Go to Cases',
                        ['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'index'],
                        ['class' => 'btn btn-danger btn-sm', 'escape' => false]
                    ); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-warning bg-opacity-10 text-warning rounded d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-file-medical-alt"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Manage Reports</h5>
                    <p class="card-text text-muted small mb-3">Create, review, and approve medical reports</p>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-right me-2"></i>Go to Reports',
                        ['prefix' => 'Doctor', 'controller' => 'Reports', 'action' => 'index'],
                        ['class' => 'btn btn-outline-warning btn-sm', 'escape' => false]
                    ); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="row g-4">
        <!-- Recent Cases -->
        <div class="col-lg-8">
            <div class="card border-0 shadow h-100">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-clock text-danger me-2"></i>Recent Medical Cases
                        </h5>
                        <?php echo $this->Html->link(
                            'View All <i class="fas fa-arrow-right ms-1"></i>',
                            ['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'index'],
                            ['class' => 'btn btn-sm btn-outline-danger', 'escape' => false]
                        ); ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recentCases) && $recentCases->count() > 0): ?>
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
                                <?php foreach ($recentCases as $case): ?>
                                <tr>
                                    <td class="ps-4">
                                        <strong class="text-danger">#<?php echo $case->id; ?></strong>
                                    </td>
                                    <td>
                                        <?php if (isset($case->patient_user) && $case->patient_user): ?>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-light text-danger rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
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
                                            echo match($case->doctor_status ?? $case->status ?? 'draft') {
                                                'draft' => 'bg-secondary',
                                                'assigned' => 'bg-info',
                                                'in_progress' => 'bg-warning',
                                                'review' => 'bg-danger',
                                                'completed' => 'bg-success',
                                                'cancelled' => 'bg-dark',
                                                default => 'bg-secondary'
                                            };
                                        ?>"><?php echo h(ucfirst(str_replace('_', ' ', $case->doctor_status ?? $case->status ?? 'draft'))); ?></span>
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
                                            ['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'view', $case->id],
                                            ['class' => 'btn btn-sm btn-outline-danger', 'escape' => false, 'title' => 'View Case']
                                        ); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-user-md fa-4x text-muted opacity-50"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Welcome to Medical Platform!</h5>
                        <p class="text-muted mb-4">As a doctor, you can:</p>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-stethoscope text-danger me-2"></i>
                                    Review and approve medical cases
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-file-medical-alt text-info me-2"></i>
                                    Create final medical reports
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-user-check text-success me-2"></i>
                                    Provide medical oversight
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded">
                                    <i class="fas fa-signature text-warning me-2"></i>
                                    Sign off on treatments
                                </div>
                            </div>
                        </div>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-stethoscope me-2"></i>Start Medical Review',
                            ['prefix' => 'Doctor', 'controller' => 'Cases', 'action' => 'index'],
                            ['class' => 'btn btn-danger btn-lg', 'escape' => false]
                        ); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar with Profile and System Status -->
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-circle text-danger me-2"></i>Medical Profile
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-user-md fa-2x"></i>
                        </div>
                        <h5 class="fw-bold mb-1">Dr. <?php echo h($userWithRole->first_name . ' ' . $userWithRole->last_name); ?></h5>
                        <?php if (isset($userWithRole->role) && $userWithRole->role): ?>
                            <span class="badge bg-danger rounded-pill">
                                Medical Doctor
                            </span>
                        <?php endif; ?>
                    </div>
                    <hr>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="fas fa-user me-2"></i>Username</span>
                            <strong class="small"><?php echo h($userWithRole->username ?? 'N/A'); ?></strong>
                        </div>
                        <?php if ($currentHospital): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="fas fa-hospital me-2"></i>Hospital</span>
                            <strong class="small"><?php echo h($currentHospital->name); ?></strong>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small"><i class="fas fa-clock me-2"></i>Last Active</span>
                            <strong class="small"><?php echo $this->Time->format($userWithRole->modified ?? 'now', 'MMM d, HH:mm'); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
