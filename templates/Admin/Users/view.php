<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<?php $this->assign('title', 'User Details'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-user-shield me-2"></i><?php echo h($user->first_name . ' ' . $user->last_name) ?>
                    </h2>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit me-1"></i>Edit User',
                            ['action' => 'edit', $user->id],
                            ['class' => 'btn btn-warning text-dark fw-bold', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-arrow-left me-1"></i>Back',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-warning', 'escape' => false]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- User Information -->
        <div class="col-md-8">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-circle me-2 text-warning"></i>User Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Full Name:</div>
                        <div class="col-sm-8 text-dark"><?php echo h($user->first_name . ' ' . $user->last_name) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Username:</div>
                        <div class="col-sm-8">
                            <span class="badge rounded-pill bg-secondary">
                                <i class="fas fa-user-circle me-1"></i><?php echo h($user->username) ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Email:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-envelope me-1 text-warning"></i>
                            <a href="mailto:<?php echo h($user->email) ?>" class="text-decoration-none">
                                <?php echo h($user->email) ?>
                            </a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">User ID:</div>
                        <div class="col-sm-8">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-hashtag"></i><?php echo h($user->id) ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Role:</div>
                        <div class="col-sm-8">
                            <?php if ($user->role): ?>
                                <span class="badge rounded-pill <?php echo $this->Role->badgeClass($user->role->type); ?>">
                                    <i class="fas fa-<?php echo $user->role->type === 'super' ? 'crown' : ($user->role->type === 'administrator' ? 'user-shield' : 'user') ?> me-1"></i>
                                    <?php echo h($this->Role->label($user->role->type)); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge rounded-pill bg-secondary">
                                    <i class="fas fa-question me-1"></i>No role
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Status:</div>
                        <div class="col-sm-8">
                            <?php if ($user->status === 'active'): ?>
                                <span class="badge rounded-pill bg-success text-white">
                                    <i class="fas fa-check-circle me-1"></i>Active
                                </span>
                            <?php else: ?>
                                <span class="badge rounded-pill bg-danger text-white">
                                    <i class="fas fa-pause-circle me-1"></i>Inactive
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Hospital:</div>
                        <div class="col-sm-8">
                            <?php if ($user->role && $user->role->type === 'super'): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-crown me-1"></i>Super User - All Hospitals
                                </span>
                            <?php elseif (!empty($user->hospital_id)): ?>
                                <?php if (isset($user->hospital) && $user->hospital): ?>
                                    <div class="text-dark">
                                        <strong><?php echo h($user->hospital->name) ?></strong>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="fas fa-globe me-1"></i>Subdomain: <?php echo h($user->hospital->subdomain) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-dark">
                                        <strong>Hospital ID: <?php echo h($user->hospital_id) ?></strong>
                                    </div>
                                    <div class="text-muted small mt-1">
                                        <i class="fas fa-info-circle me-1"></i>Hospital details not loaded
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-danger text-white">Not assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Registered:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-calendar-plus me-1 text-info"></i>
                            <?php echo $user->created->format('F d, Y \a\t g:i A') ?>
                            <div class="text-muted small"><?php echo $user->created->timeAgoInWords() ?></div>
                        </div>
                    </div>
                    <?php if ($user->modified != $user->created): ?>
                    <div class="row mb-0">
                        <div class="col-sm-4 fw-semibold text-muted">Last Updated:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-clock me-1 text-muted"></i>
                            <?php echo $user->modified->format('F d, Y \a\t g:i A') ?>
                            <div class="text-muted small"><?php echo $user->modified->timeAgoInWords() ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Stats & Actions -->
        <div class="col-md-4">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-line me-2 text-warning"></i>Account Stats
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Account Age:</span>
                        <span class="badge rounded-pill bg-info fs-6">
                            <?php
                            $diff = $user->created->diff(new DateTime());
                            echo $diff->days . ' day' . ($diff->days != 1 ? 's' : '');
                            ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Account Status:</span>
                        <span class="badge rounded-pill bg-<?php echo $user->status === 'active' ? 'success' : 'danger' ?> text-white">
                            <?php echo ucfirst($user->status) ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Role Level:</span>
                        <span class="badge rounded-pill bg-warning text-dark">
                            <?php echo $user->role ? h($this->Role->label($user->role->type)) : 'No Role' ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Registered:</span>
                        <span class="text-dark small fw-semibold">
                            <?php echo $user->created->format('M d, Y') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <?php
                    // Prevent actions on current user
                    $currentUser = $this->getRequest()->getAttribute('identity');
                    $isCurrentUser = $currentUser && $currentUser->get('id') == $user->id;
                    ?>
                    
                    <div class="d-grid gap-2">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit me-2"></i>Edit User',
                            ['action' => 'edit', $user->id],
                            ['class' => 'btn btn-warning text-dark fw-bold', 'escape' => false]
                        ) ?>
                        
                        <?php if (!$isCurrentUser): ?>
                        <?php echo $this->Form->postLink(
                            (($user->status === 'active') ? '<i class="fas fa-user-slash me-2"></i>Deactivate User' : '<i class="fas fa-user-check me-2"></i>Activate User'),
                            ['action' => 'toggleStatus', $user->id],
                            [
                                'class' => 'btn ' . (($user->status === 'active') ? 'btn-outline-danger' : 'btn-outline-success'),
                                'escape' => false,
                                'confirm' => 'Are you sure you want to ' . (($user->status === 'active') ? 'deactivate' : 'activate') . ' this user?'
                            ]
                        ) ?>
                        
                        <?php echo $this->Form->postLink(
                            '<i class="fas fa-trash me-2"></i>Delete User',
                            ['action' => 'delete', $user->id],
                            [
                                'class' => 'btn btn-outline-danger',
                                'escape' => false,
                                'confirm' => 'Are you sure you want to delete this user? This action cannot be undone.'
                            ]
                        ) ?>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> This is your account. You cannot deactivate or delete your own account.
                            </div>
                        <?php endif; ?>
                        
                        <hr class="my-3">
                        
                        <?php echo $this->Html->link(
                            '<i class="fas fa-list me-2"></i>All Users',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-secondary', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-plus me-2"></i>Add New User',
                            ['action' => 'add'],
                            ['class' => 'btn btn-outline-primary', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-tachometer-alt me-2"></i>Dashboard',
                            ['controller' => 'Dashboard', 'action' => 'index'],
                            ['class' => 'btn btn-outline-info', 'escape' => false]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>