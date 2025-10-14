<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<?php $this->assign('title', 'View User'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-user me-2 text-primary"></i>User Details
        </h1>
        <p class="text-muted mb-0">View user account information</p>
    </div>
    <div>
        <?php echo $this->Html->link(
            '<i class="fas fa-edit me-2"></i>Edit',
            ['action' => 'edit', $user->id],
            ['class' => 'btn btn-primary me-2', 'escape' => false]
        ) ?>
        <?php echo $this->Html->link(
            '<i class="fas fa-arrow-left me-2"></i>Back to Users',
            ['action' => 'index'],
            ['class' => 'btn btn-secondary', 'escape' => false]
        ) ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- User Profile Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row">
                    <div class="col-auto">
                        <div class="avatar-lg">
                            <div class="avatar-img bg-primary text-white d-flex align-items-center justify-content-center rounded-circle" style="width: 80px; height: 80px; font-size: 2rem;">
                                <?php echo strtoupper(substr(h($user->email), 0, 1)) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-flex align-items-center mb-2">
                            <h3 class="mb-0 me-3"><?php echo h($user->email) ?></h3>
                            <?php if ($user->active): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>Active
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="fas fa-times me-1"></i>Inactive
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($user->role): ?>
                            <?php
                            $roleColors = [
                                'super' => 'warning',
                                'admin' => 'info',
                                'user' => 'secondary'
                            ];
                            $roleColor = $roleColors[$user->role->type] ?? 'secondary';
                            ?>
                            <div class="mb-2">
                                <span class="badge bg-<?php echo $roleColor ?> fs-6">
                                    <?php if ($user->role->type === 'super'): ?>
                                        <i class="fas fa-crown me-1"></i>
                                    <?php elseif ($user->role->type === 'admin'): ?>
                                        <i class="fas fa-user-shield me-1"></i>
                                    <?php else: ?>
                                        <i class="fas fa-user me-1"></i>
                                    <?php endif; ?>
                                    <?php echo h(ucfirst($user->role->type)) ?>
                                </span>
                            </div>
                        <?php else: ?>
                            <div class="mb-2">
                                <span class="badge bg-light text-dark fs-6">
                                    <i class="fas fa-user-times me-1"></i>No Role
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <p class="text-muted mb-0">
                            User ID: <strong>#<?php echo h($user->id) ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Account Information -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Account Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted fw-semibold">Email Address:</td>
                                <td><?php echo h($user->email) ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Hospital:</td>
                                <td>
                                    <?php if ($user->role && $user->role->type === 'super'): ?>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-crown me-1"></i>Super User - No Hospital
                                        </span>
                                    <?php elseif ($user->hospital): ?>
                                        <strong><?php echo h($user->hospital->name) ?></strong>
                                        <br><small class="text-muted">Subdomain: <?php echo h($user->hospital->subdomain) ?></small>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">User ID:</td>
                                <td><span class="badge bg-light text-dark">#<?php echo h($user->id) ?></span></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Role:</td>
                                <td>
                                    <?php if ($user->role): ?>
                                        <?php
                                        $roleColors = [
                                            'super' => 'warning',
                                            'admin' => 'info',
                                            'user' => 'secondary'
                                        ];
                                        $roleColor = $roleColors[$user->role->type] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $roleColor ?>">
                                            <?php echo h(ucfirst($user->role->type)) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">No Role</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Status:</td>
                                <td>
                                    <?php if ($user->status === 'active'): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Active
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i>Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="text-muted fw-semibold" style="width: 140px;">Created:</td>
                                <td>
                                    <?php echo $user->created->format('F j, Y') ?><br>
                                    <small class="text-muted"><?php echo $user->created->format('g:i A') ?></small>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Last Modified:</td>
                                <td>
                                    <?php echo $user->modified->format('F j, Y') ?><br>
                                    <small class="text-muted"><?php echo $user->modified->format('g:i A') ?></small>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Account Age:</td>
                                <td>
                                    <?php
                                    $diff = $user->created->diff(new DateTime());
                                    if ($diff->days > 0) {
                                        echo $diff->days . ' day' . ($diff->days > 1 ? 's' : '');
                                    } else {
                                        echo 'Less than a day';
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Role Permissions -->
        <?php if ($user->role): ?>
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-key me-2"></i>Role Permissions
                </h5>
            </div>
            <div class="card-body">
                <?php
                $permissions = [
                    'super' => [
                        'Full system administration',
                        'User management',
                        'Role management',
                        'System settings',
                        'Database management',
                        'Security configuration',
                        'All features access'
                    ],
                    'admin' => [
                        'User management',
                        'Content management',
                        'Basic reports',
                        'Limited system settings',
                        'Most features access'
                    ],
                    'user' => [
                        'Basic profile management',
                        'View own data',
                        'Standard user features',
                        'Limited access'
                    ]
                ];
                
                $userPermissions = $permissions[$user->role->type] ?? ['Custom permissions'];
                ?>
                
                <div class="row">
                    <?php foreach (array_chunk($userPermissions, ceil(count($userPermissions) / 2)) as $chunk): ?>
                    <div class="col-md-6">
                        <ul class="list-unstyled">
                            <?php foreach ($chunk as $permission): ?>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <?php echo h($permission) ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <?php
                // Prevent actions on current user
                $currentUser = $this->getRequest()->getAttribute('identity');
                $isCurrentUser = $currentUser && $currentUser->get('id') == $user->id;
                ?>
                
                <div class="d-grid gap-2">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-edit me-2"></i>Edit User',
                        ['action' => 'edit', $user->id],
                        ['class' => 'btn btn-primary', 'escape' => false]
                    ) ?>
                    
                    <?php if (!$isCurrentUser): ?>
                    <?php echo $this->Form->postLink(
                        (($user->status === 'active') ? '<i class="fas fa-user-slash me-2"></i>Deactivate' : '<i class="fas fa-user-check me-2"></i>Activate') . ' User',
                        ['action' => 'toggleStatus', $user->id],
                        [
                            'class' => 'btn ' . (($user->status === 'active') ? 'btn-warning' : 'btn-success'),
                            'escape' => false,
                            'confirm' => 'Are you sure you want to ' . (($user->status === 'active') ? 'deactivate' : 'activate') . ' this user?'
                        ]
                    ) ?>
                    
                    <?php echo $this->Form->postLink(
                        '<i class="fas fa-trash me-2"></i>Delete User',
                        ['action' => 'delete', $user->id],
                        [
                            'class' => 'btn btn-danger',
                            'escape' => false,
                            'confirm' => 'Are you sure you want to delete this user? This action cannot be undone.'
                        ]
                    ) ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> This is your account. You cannot deactivate or delete your own account.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- User Statistics -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Account Statistics
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <div class="h4 mb-0 text-primary">
                                <?php
                                $diff = $user->created->diff(new DateTime());
                                echo $diff->days;
                                ?>
                            </div>
                            <small class="text-muted">Days Active</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="h4 mb-0 text-success">
                            <?php echo ($user->status === 'active') ? '✓' : '✗' ?>
                        </div>
                        <small class="text-muted">Status</small>
                    </div>
                </div>
                
                <hr>
                
                <div class="small text-muted">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Account Status:</span>
                        <span class="<?php echo ($user->status === 'active') ? 'text-success' : 'text-danger' ?>">
                            <?php echo ($user->status === 'active') ? 'Active' : 'Inactive' ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Last Updated:</span>
                        <span><?php echo $user->modified->timeAgoInWords() ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-compass me-2"></i>Navigation
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-list me-2"></i>All Users',
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-secondary btn-sm', 'escape' => false]
                    ) ?>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-plus me-2"></i>Add New User',
                        ['action' => 'add'],
                        ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
                    ) ?>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-home me-2"></i>Dashboard',
                        ['controller' => 'Dashboard', 'action' => 'index'],
                        ['class' => 'btn btn-outline-info btn-sm', 'escape' => false]
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>