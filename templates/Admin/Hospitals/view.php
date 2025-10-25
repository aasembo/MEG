<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Hospital $hospital
 */
?>
<?php $this->assign('title', $hospital->name); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-hospital me-2 text-primary"></i><?php echo h($hospital->name) ?>
        </h1>
        <p class="text-muted mb-0">Hospital details and management</p>
    </div>
    <div>
        <?php echo $this->Html->link(
            '<i class="fas fa-arrow-left me-2"></i>Back to Hospitals',
            ['action' => 'index'],
            ['class' => 'btn btn-secondary', 'escape' => false]
        ) ?>
    </div>
</div>

<div class="row">
    <!-- Hospital Information -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Hospital Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted small">Hospital Name</label>
                        <div class="fs-5 fw-bold"><?php echo h($hospital->name) ?></div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted small">Status</label>
                        <div>
                            <?php if ($hospital->status === 'active'): ?>
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-check-circle me-1"></i>Active
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning fs-6">
                                    <i class="fas fa-pause-circle me-1"></i>Inactive
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted small">Subdomain</label>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-light text-dark border fs-6 me-2"><?php echo h($hospital->subdomain) ?></span>
                            <small class="text-muted">.meg.www</small>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted small">Access URL</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm bg-light" readonly value="https://<?php echo h($hospital->subdomain) ?>.meg.www">
                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyUrl()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted small">Created</label>
                        <div class="fw-bold"><?php echo $hospital->created->format('M j, Y \a\t g:i A') ?></div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-muted small">Last Modified</label>
                        <div class="fw-bold"><?php echo $hospital->modified->format('M j, Y \a\t g:i A') ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>Hospital Users
                </h5>
                <?php echo $this->Html->link(
                    '<i class="fas fa-user-plus me-2"></i>Add User',
                    ['controller' => 'Users', 'action' => 'add', '?' => ['hospital_id' => $hospital->id]],
                    ['class' => 'btn btn-primary btn-sm', 'escape' => false]
                ) ?>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($hospital->users)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">User</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Last Login</th>
                                    <th scope="col" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hospital->users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo h($user->first_name . ' ' . $user->last_name) ?></h6>
                                                <small class="text-muted"><?php echo h($user->email) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($user->role): ?>
                                            <span class="badge <?php echo $this->Role->badgeClass($user->role->type); ?>">
                                                <?php echo h($this->Role->label($user->role->type)); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">No role</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($user->status === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-muted">Never</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <?php echo $this->Html->link(
                                                '<i class="fas fa-eye"></i>',
                                                ['controller' => 'Users', 'action' => 'view', $user->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-primary',
                                                    'escape' => false,
                                                    'title' => 'View User'
                                                ]
                                            ) ?>
                                            <?php echo $this->Html->link(
                                                '<i class="fas fa-edit"></i>',
                                                ['controller' => 'Users', 'action' => 'edit', $user->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-secondary',
                                                    'escape' => false,
                                                    'title' => 'Edit User'
                                                ]
                                            ) ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users text-muted mb-3" style="font-size: 3rem;"></i>
                        <h6 class="text-muted">No users assigned</h6>
                        <p class="text-muted mb-3">This hospital doesn't have any users yet.</p>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-user-plus me-2"></i>Add First User',
                            ['controller' => 'Users', 'action' => 'add', '?' => ['hospital_id' => $hospital->id]],
                            ['class' => 'btn btn-primary', 'escape' => false]
                        ) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Hospital Stats -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Hospital Statistics
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary mb-0"><?php echo count($hospital->users ?? []) ?></h4>
                            <small class="text-muted">Total Users</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success mb-0">
                            <?php echo count(array_filter($hospital->users ?? [], function($user) { return $user->status === 'active'; })) ?>
                        </h4>
                        <small class="text-muted">Active Users</small>
                    </div>
                </div>
                
                <?php if (!empty($userCounts)): ?>
                    <hr>
                    <h6 class="mb-3">Users by Role</h6>
                    <?php foreach ($userCounts as $roleCount): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge <?php echo $this->Role->badgeClass($roleCount->role_type); ?>">
                                <?php echo h($this->Role->label($roleCount->role_type)); ?>
                            </span>
                            <span class="fw-bold"><?php echo $roleCount->count ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-2"></i>Hospital Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-edit me-2"></i>Edit Hospital',
                        ['action' => 'edit', $hospital->id],
                        ['class' => 'btn btn-primary', 'escape' => false]
                    ) ?>
                    
                    <?php echo $this->Html->link(
                        '<i class="fas fa-users me-2"></i>Manage Users',
                        ['controller' => 'Users', 'action' => 'index', '?' => ['hospital_id' => $hospital->id]],
                        ['class' => 'btn btn-outline-info', 'escape' => false]
                    ) ?>
                    
                    <?php echo $this->Form->postLink(
                        ($hospital->status === 'active') ? '<i class="fas fa-pause me-2"></i>Deactivate Hospital' : '<i class="fas fa-play me-2"></i>Activate Hospital',
                        ['action' => 'toggleStatus', $hospital->id],
                        [
                            'class' => 'btn btn-outline-' . (($hospital->status === 'active') ? 'warning' : 'success'),
                            'escape' => false,
                            'confirm' => 'Are you sure you want to ' . (($hospital->status === 'active') ? 'deactivate' : 'activate') . ' this hospital?'
                        ]
                    ) ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Info -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Quick Info
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Hospital ID</label>
                    <div class="fw-bold font-monospace"><?php echo $hospital->id ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted small">Subdomain</label>
                    <div class="fw-bold"><?php echo h($hospital->subdomain) ?></div>
                </div>
                
                <div class="mb-0">
                    <label class="form-label text-muted small">Account Age</label>
                    <div class="fw-bold">
                        <?php
                        $now = new DateTime();
                        $created = $hospital->created;
                        $diff = $now->diff($created);
                        
                        if ($diff->days > 0) {
                            echo $diff->days . ' day' . ($diff->days > 1 ? 's' : '');
                        } else if ($diff->h > 0) {
                            echo $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
                        } else {
                            echo $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Danger Zone -->
        <?php if (empty($hospital->users)): ?>
        <div class="card border-danger mt-3">
            <div class="card-header bg-danger bg-opacity-10 border-danger">
                <h6 class="mb-0 text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    This hospital has no users and can be safely deleted. This action cannot be undone.
                </p>
                <?php echo $this->Form->postLink(
                    '<i class="fas fa-trash me-2"></i>Delete Hospital',
                    ['action' => 'delete', $hospital->id],
                    [
                        'class' => 'btn btn-danger',
                        'escape' => false,
                        'confirm' => 'Are you sure you want to delete this hospital? This action cannot be undone.'
                    ]
                ) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Copy URL function
function copyUrl() {
    var input = document.querySelector('input[readonly]');
    
    navigator.clipboard.writeText(input.value).then(function() {
        // Show success feedback
        var button = event.target.closest('button');
        var originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check text-success"></i>';
        setTimeout(function() {
            button.innerHTML = originalHtml;
        }, 1500);
    });
}
</script>