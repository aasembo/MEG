<?php
/**
 * @var \App\View\AppView $this
 * @var string $title
 * @var string $welcomeMessage
 * @var \Authentication\IdentityInterface|null $currentUser
 * @var array $stats
 * @var array $recentUsers
 */
?>
<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 12px;
}
</style>
<div class="dashboard-content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">
            <i class="fas fa-tachometer-alt me-2 text-primary"></i>
            <?php echo h($title) ?>
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-refresh me-1"></i>Refresh
                </button>
            </div>
        </div>
    </div>
    
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-primary border-0 shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-3 fa-2x"></i>
                    <div>
                        <h4 class="alert-heading mb-1"><?php echo h($welcomeMessage) ?></h4>
                        <p class="mb-0">Welcome to your admin dashboard. You can manage your entire application from here.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">Total Users</div>
                            <div class="h5 mb-0"><?php echo number_format($stats['users']['total']) ?></div>
                            <div class="small text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                <?php echo $stats['users']['active'] ?> active
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-hospital fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">Active Hospitals</div>
                            <div class="h5 mb-0"><?php echo number_format($stats['hospitals']['active']) ?></div>
                            <div class="small text-muted">
                                <?php echo $stats['hospitals']['total'] ?> total
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line fa-2x text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">New Users This Month</div>
                            <div class="h5 mb-0"><?php echo number_format($stats['users']['this_month']) ?></div>
                            <div class="small text-info">
                                <i class="fas fa-arrow-up me-1"></i>
                                <?php echo $stats['users']['growth_rate'] ?>% growth
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-md fa-2x text-warning"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="small text-muted">Medical Staff</div>
                            <div class="h5 mb-0"><?php echo number_format($stats['specialized']['doctors'] + $stats['specialized']['nurses']) ?></div>
                            <div class="small text-muted">
                                <?php echo $stats['specialized']['doctors'] ?> doctors, <?php echo $stats['specialized']['nurses'] ?> nurses
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Role Distribution and Specialized Records Row -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users-cog me-2"></i>Role Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($stats['roles'])): ?>
                        <?php foreach ($stats['roles'] as $role): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <span class="fw-bold"><?php echo h($role->role_name) ?></span>
                                    <small class="text-muted">(<?php echo h($role->role_type) ?>)</small>
                                </div>
                                <span class="badge bg-primary"><?php echo $role->count ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No role data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Specialized Records
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 mb-1 text-primary"><?php echo $stats['specialized']['doctors'] ?></div>
                            <div class="small text-muted">Doctors</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 mb-1 text-success"><?php echo $stats['specialized']['nurses'] ?></div>
                            <div class="small text-muted">Nurses</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 mb-1 text-info"><?php echo $stats['specialized']['scientists'] ?></div>
                            <div class="small text-muted">Scientists</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 mb-1 text-warning"><?php echo $stats['specialized']['patients'] ?></div>
                            <div class="small text-muted">Patients</div>
                        </div>
                        <div class="col-12">
                            <div class="h4 mb-1 text-secondary"><?php echo $stats['specialized']['technicians'] ?></div>
                            <div class="small text-muted">Technicians</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row">
        <!-- Current User Info -->
        <?php if ($currentUser): ?>
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-circle me-2"></i>Current User Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="fw-bold text-muted">
                                        <i class="fas fa-envelope me-2"></i>Email:
                                    </td>
                                    <td><?php echo h($currentUser->get('email')) ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">
                                        <i class="fas fa-user-tag me-2"></i>Role:
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php echo h($currentUser->get('role') ? $currentUser->get('role')->type : 'N/A') ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">
                                        <i class="fas fa-check-circle me-2"></i>Status:
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?php echo h($currentUser->get('status')) ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-users me-2"></i>Manage Users', 
                            ['controller' => 'Users', 'action' => 'index'], 
                            ['class' => 'btn btn-primary btn-lg', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-hospital me-2"></i>Manage Hospitals', 
                            ['controller' => 'Hospitals', 'action' => 'index'], 
                            ['class' => 'btn btn-info btn-lg', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-user-plus me-2"></i>Add New User', 
                            ['controller' => 'Users', 'action' => 'add'], 
                            ['class' => 'btn btn-success btn-lg', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-building me-2"></i>Add New Hospital', 
                            ['controller' => 'Hospitals', 'action' => 'add'], 
                            ['class' => 'btn btn-warning btn-lg', 'escape' => false]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-clock me-2"></i>Recently Created Users
                    </h5>
                    <?php echo $this->Html->link(
                        'View All Users <i class="fas fa-arrow-right ms-1"></i>', 
                        ['controller' => 'Users', 'action' => 'index'], 
                        ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false]
                    ) ?>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentUsers)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Hospital</th>
                                        <th>Created</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold"><?php echo h($user->username) ?></div>
                                                        <div class="small text-muted"><?php echo h($user->email) ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo h($user->role ? $user->role->name : 'N/A') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user->hospital): ?>
                                                    <span class="badge bg-info"><?php echo h($user->hospital->name) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">No Hospital</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo $user->created->timeAgoInWords() ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $user->status === 'active' ? 'success' : 'warning' ?>">
                                                    <?php echo h($user->status) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $this->Html->link(
                                                    '<i class="fas fa-eye"></i>', 
                                                    ['controller' => 'Users', 'action' => 'view', $user->id], 
                                                    ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'title' => 'View User']
                                                ) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No users have been created yet.</p>
                            <?php echo $this->Html->link(
                                'Create First User', 
                                ['controller' => 'Users', 'action' => 'add'], 
                                ['class' => 'btn btn-primary']
                            ) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>