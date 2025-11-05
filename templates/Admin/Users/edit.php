<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var \Cake\Collection\CollectionInterface|string[] $roles
 */
?>
<?php $this->assign('title', 'Edit User'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-user-edit me-2"></i>Edit User
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-shield-alt me-2"></i>Update user account: <?php echo h($user->first_name . ' ' . $user->last_name) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-eye me-1"></i>View',
                            ['action' => 'view', $user->id],
                            ['class' => 'btn btn-outline-warning', 'escape' => false]
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
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-circle me-2 text-warning"></i>User Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <?php echo $this->Form->create($user, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    
                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('first_name', [
                                'label' => 'First Name *',
                                'class' => 'form-control' . ($user->hasErrors('first_name') ? ' is-invalid' : ''),
                                'required' => true,
                                'placeholder' => 'Enter first name'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('last_name', [
                                'label' => 'Last Name *',
                                'class' => 'form-control' . ($user->hasErrors('last_name') ? ' is-invalid' : ''),
                                'required' => true,
                                'placeholder' => 'Enter last name'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('username', [
                                'label' => 'Username *',
                                'class' => 'form-control' . ($user->hasErrors('username') ? ' is-invalid' : ''),
                                'required' => true,
                                'placeholder' => 'Enter username'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('email', [
                                'label' => 'Email Address *',
                                'type' => 'email',
                                'class' => 'form-control' . ($user->hasErrors('email') ? ' is-invalid' : ''),
                                'required' => true,
                                'placeholder' => 'user@example.com'
                            ]) ?>
                        </div>
                    </div>
                    
                    <!-- Role and Status -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <?php echo $this->Form->control('role_id', [
                                'label' => 'User Role *',
                                'type' => 'select',
                                'options' => $roles,
                                'empty' => 'Select a role...',
                                'class' => 'form-select' . ($user->hasErrors('role_id') ? ' is-invalid' : ''),
                                'required' => true,
                                'id' => 'role-select',
                                'data-role-types' => json_encode($roleTypes ?? [])
                            ]) ?>
                        </div>
                        <div class="col-md-4" id="hospital-field">
                            <label class="form-label">Hospital *</label>
                            <div class="form-control-plaintext border p-2 bg-light rounded">
                                <i class="fas fa-hospital me-2 text-warning"></i>
                                <?php echo h($currentHospital->name ?? 'System Hospital') ?>
                                <small class="text-muted d-block">Current hospital context</small>
                            </div>
                            <input type="hidden" name="hospital_id" value="<?php echo $currentHospital->id ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <?php echo $this->Form->control('status', [
                                'label' => 'Account Status *',
                                'type' => 'select',
                                'options' => [
                                    'active' => 'Active',
                                    'inactive' => 'Inactive'
                                ],
                                'class' => 'form-select' . ($user->hasErrors('status') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    
                    <!-- Password Section -->
                    <hr class="my-4">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-key me-2"></i>Account Security
                    </h6>
                    
                    <div class="alert alert-info border-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Password Update:</strong> Leave password fields empty to keep the current password unchanged.
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('password', [
                                'label' => 'New Password',
                                'type' => 'password',
                                'class' => 'form-control',
                                'required' => false,
                                'placeholder' => 'Enter new password (optional)',
                                'minlength' => 8,
                                'value' => ''
                            ]) ?>
                            <div class="form-text">Leave empty to keep current password.</div>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('confirm_password', [
                                'label' => 'Confirm New Password',
                                'type' => 'password',
                                'class' => 'form-control',
                                'required' => false,
                                'placeholder' => 'Confirm new password',
                                'value' => ''
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-times me-2"></i>Cancel',
                                    ['action' => 'index'],
                                    ['class' => 'btn btn-outline-secondary', 'escape' => false]
                                ) ?>
                                <?php echo $this->Form->button(
                                    '<i class="fas fa-save me-2"></i>Update User',
                                    [
                                        'class' => 'btn btn-warning text-dark fw-bold',
                                        'type' => 'submit',
                                        'escapeTitle' => false
                                    ]
                                ) ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php echo $this->Form->end() ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- User Details -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle me-2 text-warning"></i>User Details
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">User ID:</strong><br>
                        <span class="text-muted">#<?php echo h($user->id) ?></span>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Created:</strong><br>
                        <span class="text-muted"><?php echo $user->created->format('F j, Y \a\t g:i A') ?></span>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Last Modified:</strong><br>
                        <span class="text-muted"><?php echo $user->modified->format('F j, Y \a\t g:i A') ?></span>
                    </div>
                    
                    <div>
                        <strong class="text-dark">Current Status:</strong><br>
                        <?php if ($user->status === 'active'): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>Active
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger">
                                <i class="fas fa-times me-1"></i>Inactive
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Current Role Information -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-tag me-2 text-warning"></i>Current Role
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <?php if ($user->role): ?>
                        <?php
                        $roleColors = [
                            'super' => 'warning',
                            'administrator' => 'info',
                            'doctor' => 'success',
                            'technician' => 'primary',
                            'scientist' => 'secondary',
                            'patient' => 'light'
                        ];
                        $roleColor = $roleColors[$user->role->type] ?? 'secondary';
                        $roleDescriptions = [
                            'super' => 'Full system access and administration rights.',
                            'administrator' => 'Administrative access with limited system settings.',
                            'doctor' => 'Medical professional with patient and case management access.',
                            'technician' => 'Technical staff with patient data entry and case processing.',
                            'scientist' => 'Research and laboratory management access.',
                            'patient' => 'Patient record with limited system access.'
                        ];
                        $roleDescription = $roleDescriptions[$user->role->type] ?? 'Custom role permissions.';
                        ?>
                        <div class="text-center">
                            <span class="badge bg-<?php echo $roleColor ?> <?php echo $roleColor === 'light' ? 'text-dark' : 'text-white' ?> fs-6 mb-2">
                                <?php echo h(ucfirst($user->role->type)) ?>
                            </span>
                            <p class="text-muted small mb-0"><?php echo $roleDescription ?></p>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <span class="badge bg-light text-dark fs-6 mb-2">No Role</span>
                            <p class="text-muted small mb-0">This user has no assigned role.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <?php
                    // Prevent actions on current user
                    $currentUser = $this->getRequest()->getAttribute('identity');
                    $isCurrentUser = $currentUser && $currentUser->get('id') == $user->id;
                    ?>
                    
                    <div class="d-grid gap-2">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-eye me-2"></i>View User Details',
                            ['action' => 'view', $user->id],
                            ['class' => 'btn btn-outline-info', 'escape' => false]
                        ) ?>
                        
                        <?php if (!$isCurrentUser): ?>
                        <?php echo $this->Form->postLink(
                            ($user->status === 'active' ? '<i class="fas fa-user-slash me-2"></i>Deactivate' : '<i class="fas fa-user-check me-2"></i>Activate') . ' User',
                            ['action' => 'toggleStatus', $user->id],
                            [
                                'class' => 'btn ' . ($user->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success'),
                                'escape' => false,
                                'confirm' => 'Are you sure you want to ' . ($user->status === 'active' ? 'deactivate' : 'activate') . ' this user?'
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
                                <strong>Note:</strong> You cannot modify your own account status or delete your account.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                // Check password confirmation
                var password = document.querySelector('input[name="password"]');
                var confirmPassword = document.querySelector('input[name="confirm_password"]');
                
                // Only validate password confirmation if password is being changed
                if (password.value !== '' || confirmPassword.value !== '') {
                    if (password.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity('Passwords do not match');
                    } else if (password.value !== '' && password.value.length < 8) {
                        password.setCustomValidity('Password must be at least 8 characters long');
                    } else {
                        password.setCustomValidity('');
                        confirmPassword.setCustomValidity('');
                    }
                } else {
                    password.setCustomValidity('');
                    confirmPassword.setCustomValidity('');
                }
                
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
        
        // Real-time password confirmation check
        var password = document.querySelector('input[name="password"]');
        var confirmPassword = document.querySelector('input[name="confirm_password"]');
        
        if (password && confirmPassword) {
            function checkPasswordMatch() {
                if ((password.value !== '' || confirmPassword.value !== '') && password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            password.addEventListener('input', checkPasswordMatch);
            confirmPassword.addEventListener('input', checkPasswordMatch);
        }
        
        // Hospital field visibility based on role
        var roleSelect = document.getElementById('role-select');
        var hospitalField = document.getElementById('hospital-field');
        
        function toggleHospitalField() {
            var selectedOption = roleSelect.options[roleSelect.selectedIndex];
            var roleText = selectedOption.text.toLowerCase();
            
            if (roleText.includes('super')) {
                hospitalField.style.display = 'none';
            } else {
                hospitalField.style.display = 'block';
            }
        }
        
        // Initial check
        toggleHospitalField();
        
        // Check on role change
        roleSelect.addEventListener('change', toggleHospitalField);
    }, false);
})();
</script>