<?php
/**
 * @var \App\View\AppView $this
 * @var object $user
 * @var object|null $currentHospital
 * @var array $caseStats
 */
$this->setLayout('technician');
$this->assign('title', 'Technician Dashboard');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="avatar-wrapper me-3">
                                    <div class="avatar bg-secondary text-white">
                                        <i class="fas fa-tools"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="mb-1">Welcome back, <?php echo h($user->name ?? $user->username); ?>!</h4>
                                    <p class="text-muted mb-0">Manage cases and coordinate with scientists</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <?php if ($currentHospital): ?>
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-hospital me-1"></i><?php echo h($currentHospital->name); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary fs-6">
                                    <i class="fas fa-globe me-1"></i>System Wide
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Case Management Quick Actions -->
            <div class="row g-4 mb-4">
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 feature-card">
                        <div class="card-body text-center">
                            <div class="feature-icon text-primary mb-3">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <h5 class="card-title">Create Case</h5>
                            <p class="card-text text-muted">Start a new case with patient details</p>
                            <?php echo $this->Html->link(
                                'Create New Case',
                                ['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'add'],
                                ['class' => 'btn btn-primary']
                            ); ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 feature-card">
                        <div class="card-body text-center">
                            <div class="feature-icon text-info mb-3">
                                <i class="fas fa-list-alt"></i>
                            </div>
                            <h5 class="card-title">View Cases</h5>
                            <p class="card-text text-muted">Browse and manage all cases</p>
                            <?php echo $this->Html->link(
                                'View All Cases',
                                ['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'index'],
                                ['class' => 'btn btn-info']
                            ); ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 feature-card">
                        <div class="card-body text-center">
                            <div class="feature-icon text-warning mb-3">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h5 class="card-title">Assignments</h5>
                            <p class="card-text text-muted">Assign cases to scientists</p>
                            <?php echo $this->Html->link(
                                'Manage Assignments',
                                ['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'index', '?' => ['status' => 'assigned']],
                                ['class' => 'btn btn-warning']
                            ); ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 feature-card">
                        <div class="card-body text-center">
                            <div class="feature-icon text-success mb-3">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h5 class="card-title">Reports</h5>
                            <p class="card-text text-muted">View case statistics and reports</p>
                            <button class="btn btn-outline-success" disabled>
                                Coming Soon
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Row -->
            <?php if (!empty($caseStats) && $currentHospital): ?>
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="display-6 text-primary mb-2">
                                <?php echo $caseStats['total_cases']; ?>
                            </div>
                            <h6 class="card-title">Total Cases</h6>
                            <p class="small text-muted">Cases created by you</p>
                        </div>
                    </div>
                </div>
                
                <?php 
                $statusCounts = [];
                foreach ($caseStats['cases_by_status'] as $statusCase) {
                    $statusCounts[$statusCase->status] = $statusCase->count;
                }
                ?>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="display-6 text-secondary mb-2">
                                <?php echo $statusCounts['draft'] ?? 0; ?>
                            </div>
                            <h6 class="card-title">Draft Cases</h6>
                            <p class="small text-muted">Awaiting completion</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="display-6 text-info mb-2">
                                <?php echo $statusCounts['assigned'] ?? 0; ?>
                            </div>
                            <h6 class="card-title">Assigned</h6>
                            <p class="small text-muted">With scientists</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <div class="display-6 text-success mb-2">
                                <?php echo $statusCounts['completed'] ?? 0; ?>
                            </div>
                            <h6 class="card-title">Completed</h6>
                            <p class="small text-muted">Finished cases</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Cases and Information -->
            <div class="row g-4">
                <?php if (!empty($caseStats['recent_cases'])): ?>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clock me-2"></i>Recent Cases
                            </h5>
                            <?php echo $this->Html->link(
                                'View All <i class="fas fa-arrow-right ms-1"></i>',
                                ['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'index'],
                                ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false]
                            ); ?>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Case ID</th>
                                            <th>Patient</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($caseStats['recent_cases'] as $case): ?>
                                        <tr>
                                            <td><strong>#<?php echo $case->id; ?></strong></td>
                                            <td>
                                                <?php if ($case->patient_users): ?>
                                                    <?php echo h($case->patient_users->first_name . ' ' . $case->patient_users->last_name); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No patient</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($case->status) {
                                                        'draft' => 'secondary',
                                                        'assigned' => 'info',
                                                        'in_progress' => 'warning',
                                                        'review' => 'primary',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>"><?php echo h(ucfirst($case->status)); ?></span>
                                            </td>
                                            <td><?php echo $case->created->format('M j'); ?></td>
                                            <td>
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
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Getting Started
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6>Welcome to Case Management!</h6>
                            <p>As a technician, you can:</p>
                            <ul>
                                <li>Create new cases with patient details</li>
                                <li>Assign cases to scientists for analysis</li>
                                <li>Track case progress and status changes</li>
                                <li>View audit trails and version history</li>
                            </ul>
                            <p class="mb-0">
                                <?php echo $this->Html->link(
                                    'Create your first case',
                                    ['prefix' => 'Technician', 'controller' => 'Cases', 'action' => 'add'],
                                    ['class' => 'btn btn-primary']
                                ); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user me-2"></i>Profile Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($user->role) && $user->role): ?>
                                <p><strong>Role:</strong> 
                                    <span class="badge <?php echo $this->Role->badgeClass($user->role->type); ?>">
                                        <?php echo h($this->Role->label($user->role->type)); ?>
                                    </span>
                                </p>
                            <?php endif; ?>
                            <p><strong>Username:</strong> <?php echo h($user->username ?? 'N/A'); ?></p>
                            <?php if ($currentHospital): ?>
                                <p><strong>Hospital:</strong> <?php echo h($currentHospital->name); ?></p>
                            <?php endif; ?>
                            <p><strong>Last Login:</strong> <?php echo $this->Time->format($user->modified ?? 'now', 'MMM d, yyyy HH:mm'); ?></p>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>System Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="status-item d-flex justify-content-between align-items-center mb-2">
                                <span>Platform Status</span>
                                <span class="badge bg-success">Online</span>
                            </div>
                            <div class="status-item d-flex justify-content-between align-items-center mb-2">
                                <span>Database</span>
                                <span class="badge bg-success">Connected</span>
                            </div>
                            <div class="status-item d-flex justify-content-between align-items-center">
                                <span>Case System</span>
                                <span class="badge bg-success">Operational</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-wrapper .avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.feature-card {
    transition: transform 0.2s;
}

.feature-card:hover {
    transform: translateY(-2px);
}

.feature-icon {
    font-size: 2.5rem;
}

.status-item {
    padding: 0.25rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.status-item:last-child {
    border-bottom: none;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.display-6 {
    font-size: 2.5rem;
    font-weight: 600;
}
</style>