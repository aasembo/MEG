<?php
/**
 * @var \App\View\AppView $this
 * @var string $title
 * @var string $welcomeMessage
 * @var \Authentication\IdentityInterface|null $currentUser
 * @var array $stats
 * @var array $recentUsers
 */
$this->assign('title', 'Administrator Dashboard');
?>

<div class="container-fluid px-4 py-4">
    <!-- Welcome Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2rem;">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                        </div>
                        <div>
                            <h2 class="mb-2 fw-bold">
                                <?php if ($currentUser): ?>
                                    Welcome back, Administrator!
                                <?php else: ?>
                                    Administrator Dashboard
                                <?php endif; ?>
                            </h2>
                            <p class="mb-0 fs-5">
                                <i class="fas fa-cogs me-2"></i>Complete system administration and hospital management
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php if (isset($currentHospital) && $currentHospital): ?>
                        <div class="d-inline-block bg-white bg-opacity-25 rounded-pill px-4 py-2">
                            <i class="fas fa-hospital me-2"></i>
                            <strong><?php echo h($currentHospital->name); ?></strong>
                        </div>
                    <?php else: ?>
                        <div class="d-inline-block bg-white bg-opacity-25 rounded-pill px-4 py-2">
                            <i class="fas fa-globe me-2"></i>
                            <strong>System Administration</strong>
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
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow h-100 border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 text-uppercase small fw-semibold">Total Users</p>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($stats['users']['total']); ?></h2>
                        </div>
                        <div class="bg-warning text-dark rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.75rem;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-circle me-1"></i><?php echo $stats['users']['active']; ?> active
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow h-100 border-start border-dark border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-2 text-uppercase small fw-semibold">Medical Staff</p>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($stats['specialized']['doctors'] + $stats['specialized']['nurses']); ?></h2>
                        </div>
                        <div class="bg-dark text-white rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.75rem;">
                            <i class="fas fa-user-md"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-stethoscope me-1"></i><?php echo $stats['specialized']['doctors']; ?> doctors
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
                            <p class="text-muted mb-2 text-uppercase small fw-semibold">New This Month</p>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($stats['users']['this_month']); ?></h2>
                        </div>
                        <div class="bg-success text-white rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.75rem;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-success bg-opacity-10 text-success">
                            <i class="fas fa-arrow-up me-1"></i><?php echo $stats['users']['growth_rate']; ?>% growth
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
                            <p class="text-muted mb-2 text-uppercase small fw-semibold">Research Staff</p>
                            <h2 class="mb-0 fw-bold"><?php echo number_format($stats['specialized']['scientists'] + $stats['specialized']['technicians']); ?></h2>
                        </div>
                        <div class="bg-info text-white rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.75rem;">
                            <i class="fas fa-microscope"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-info bg-opacity-10 text-info">
                            <i class="fas fa-flask me-1"></i><?php echo $stats['specialized']['scientists']; ?> scientists
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-warning bg-opacity-10 text-warning rounded d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Manage Users</h5>
                    <p class="card-text text-muted small mb-3">Add, edit, and manage all system users and their roles</p>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-right me-2"></i>User Management',
                        ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index'],
                        ['class' => 'btn btn-warning btn-sm', 'escape' => false]
                    ); ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-dark bg-opacity-10 text-dark rounded d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-briefcase-medical"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Case Management</h5>
                    <p class="card-text text-muted small mb-3">Oversee all cases and coordinate medical workflows</p>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-right me-2"></i>View Cases',
                        ['prefix' => 'Admin', 'controller' => 'Cases', 'action' => 'index'],
                        ['class' => 'btn btn-dark btn-sm', 'escape' => false]
                    ); ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-success bg-opacity-10 text-success rounded d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-building"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Departments</h5>
                    <p class="card-text text-muted small mb-3">Manage hospital departments and organizational structure</p>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-right me-2"></i>Departments',
                        ['prefix' => 'Admin', 'controller' => 'Departments', 'action' => 'index'],
                        ['class' => 'btn btn-success btn-sm', 'escape' => false]
                    ); ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow h-100">
                <div class="card-body text-center p-4">
                    <div class="bg-info bg-opacity-10 text-info rounded d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-2">Reports & Analytics</h5>
                    <p class="card-text text-muted small mb-3">Generate comprehensive reports and view system analytics</p>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-right me-2"></i>View Reports',
                        '#',
                        ['class' => 'btn btn-info btn-sm', 'escape' => false]
                    ); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Role Distribution and Recent Users -->
        <div class="col-lg-8">
            <!-- Role Distribution Chart -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-users-cog text-warning me-2"></i>Role Distribution
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['roles'])): ?>
                        <div class="row g-3">
                            <?php foreach ($stats['roles'] as $role): ?>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded">
                                        <div>
                                            <span class="fw-bold"><?php echo h($role['role_name']); ?></span>
                                            <br><small class="text-muted"><?php echo h(ucfirst($role['role_type'])); ?></small>
                                        </div>
                                        <span class="badge bg-<?php 
                                            echo match($role['role_type']) {
                                                'administrator' => 'warning',
                                                'doctor' => 'danger',
                                                'scientist' => 'success',
                                                'technician' => 'primary',
                                                'nurse' => 'info',
                                                default => 'secondary'
                                            };
                                        ?> fs-6"><?php echo $role['count']; ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No role data available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Users Table -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-user-clock text-dark me-2"></i>Recently Created Users
                        </h5>
                        <?php echo $this->Html->link(
                            'View All <i class="fas fa-arrow-right ms-1"></i>',
                            ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index'],
                            ['class' => 'btn btn-sm btn-outline-dark', 'escape' => false]
                        ); ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recentUsers)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 ps-4 fw-semibold text-uppercase small">User</th>
                                        <th class="border-0 fw-semibold text-uppercase small">Role</th>
                                        <th class="border-0 fw-semibold text-uppercase small">Created</th>
                                        <th class="border-0 fw-semibold text-uppercase small">Status</th>
                                        <th class="border-0 text-end pe-4 fw-semibold text-uppercase small">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo h($user->username); ?></div>
                                                        <div class="small text-muted"><?php echo h($user->email); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $user->role ? match($user->role->type) {
                                                        'administrator' => 'warning',
                                                        'doctor' => 'danger',
                                                        'scientist' => 'success',
                                                        'technician' => 'primary',
                                                        'nurse' => 'info',
                                                        default => 'secondary'
                                                    } : 'secondary';
                                                ?>">
                                                    <?php echo h($user->role ? $user->role->name : 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <i class="far fa-calendar-alt me-1"></i>
                                                    <?php echo $user->created->timeAgoInWords(); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $user->status === 'active' ? 'success' : 'warning'; ?> rounded-pill">
                                                    <?php echo h(ucfirst($user->status)); ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <?php echo $this->Html->link(
                                                    '<i class="fas fa-eye"></i>',
                                                    ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'view', $user->id],
                                                    ['class' => 'btn btn-sm btn-outline-dark', 'escape' => false, 'title' => 'View User']
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
                                <i class="fas fa-users fa-4x text-muted opacity-50"></i>
                            </div>
                            <h5 class="fw-bold mb-3">No Users Created Yet</h5>
                            <p class="text-muted mb-4">Start managing your hospital by creating user accounts</p>
                            <?php echo $this->Html->link(
                                '<i class="fas fa-plus-circle me-2"></i>Create First User',
                                ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'add'],
                                ['class' => 'btn btn-warning btn-lg', 'escape' => false]
                            ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar with Profile and System Info -->
        <div class="col-lg-4">
            <!-- Current User Profile -->
            <?php if ($currentUser): ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-shield text-warning me-2"></i>Administrator Profile
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-warning text-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo h($currentUser->get('email')); ?></h5>
                        <span class="badge bg-warning text-dark rounded-pill">
                            <?php echo h($currentUser->get('role') ? ucfirst($currentUser->get('role')->type) : 'Administrator'); ?>
                        </span>
                    </div>
                    <hr>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="fas fa-envelope me-2"></i>Email</span>
                            <strong class="small"><?php echo h($currentUser->get('email')); ?></strong>
                        </div>
                        <?php if (isset($currentHospital) && $currentHospital): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small"><i class="fas fa-hospital me-2"></i>Hospital</span>
                            <strong class="small"><?php echo h($currentHospital->name); ?></strong>
                        </div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small"><i class="fas fa-check-circle me-2"></i>Status</span>
                            <span class="badge bg-success rounded-pill">
                                <?php echo h($currentUser->get('status') ?? 'Active'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Specialized Records Overview -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-pie text-info me-2"></i>Specialized Records
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6">
                            <div class="p-3 bg-danger bg-opacity-10 rounded">
                                <div class="h4 mb-1 text-danger"><?php echo $stats['specialized']['doctors']; ?></div>
                                <div class="small text-muted">Doctors</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <div class="h4 mb-1 text-info"><?php echo $stats['specialized']['nurses']; ?></div>
                                <div class="small text-muted">Nurses</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <div class="h4 mb-1 text-success"><?php echo $stats['specialized']['scientists']; ?></div>
                                <div class="small text-muted">Scientists</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <div class="h4 mb-1 text-primary"><?php echo $stats['specialized']['technicians']; ?></div>
                                <div class="small text-muted">Technicians</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-secondary bg-opacity-10 rounded">
                                <div class="h4 mb-1 text-secondary"><?php echo $stats['specialized']['patients']; ?></div>
                                <div class="small text-muted">Patients</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- System Status -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-server text-success me-2"></i>System Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="status-item d-flex justify-content-between align-items-center mb-3 p-2 rounded bg-light">
                        <div>
                            <i class="fas fa-database text-success me-2"></i>
                            <span class="fw-semibold">Database</span>
                        </div>
                        <span class="badge bg-success rounded-pill">
                            <i class="fas fa-check-circle me-1"></i>Online
                        </span>
                    </div>
                    <div class="status-item d-flex justify-content-between align-items-center mb-3 p-2 rounded bg-light">
                        <div>
                            <i class="fas fa-network-wired text-success me-2"></i>
                            <span class="fw-semibold">Network</span>
                        </div>
                        <span class="badge bg-success rounded-pill">
                            <i class="fas fa-check-circle me-1"></i>Connected
                        </span>
                    </div>
                    <div class="status-item d-flex justify-content-between align-items-center mb-3 p-2 rounded bg-light">
                        <div>
                            <i class="fas fa-shield-alt text-warning me-2"></i>
                            <span class="fw-semibold">Security</span>
                        </div>
                        <span class="badge bg-warning rounded-pill">
                            <i class="fas fa-lock me-1"></i>Protected
                        </span>
                    </div>
                    <div class="status-item d-flex justify-content-between align-items-center p-2 rounded bg-light">
                        <div>
                            <i class="fas fa-chart-line text-info me-2"></i>
                            <span class="fw-semibold">Performance</span>
                        </div>
                        <span class="badge bg-info rounded-pill">
                            <i class="fas fa-rocket me-1"></i>Optimal
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>