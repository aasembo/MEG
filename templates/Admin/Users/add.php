<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var \Cake\Collection\CollectionInterface|string[] $roles
 */
?>
<?php $this->assign('title', 'Add New User'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-user-plus me-2"></i>Add New User
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-shield-alt me-2"></i>Create a new user account for the system
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-left me-2"></i>Back to Users',
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-warning', 'escape' => false]
                    ) ?>
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
                                'default' => 'active',
                                'required' => true
                            ]) ?>
                        </div>
                    </div>
                    
                    <!-- Password Section -->
                    <hr class="my-4">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-key me-2"></i>Account Security
                    </h6>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('password', [
                                'label' => 'Password *',
                                'type' => 'password',
                                'class' => 'form-control' . ($user->hasErrors('password') ? ' is-invalid' : ''),
                                'required' => true,
                                'placeholder' => 'Enter password',
                                'minlength' => 8
                            ]) ?>
                            <div class="form-text">Password must be at least 8 characters long.</div>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('confirm_password', [
                                'label' => 'Confirm Password *',
                                'type' => 'password',
                                'class' => 'form-control' . ($user->hasErrors('confirm_password') ? ' is-invalid' : ''),
                                'required' => true,
                                'placeholder' => 'Confirm password'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info border-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> The user will be automatically assigned the selected role and can login to the system immediately upon creation.
                            </div>
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
                                    '<i class="fas fa-user-plus me-2"></i>Create User',
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
            <!-- User Roles Guide -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-shield-alt me-2 text-warning"></i>User Roles
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-info me-2">Admin</span>
                            <strong class="text-dark">Administrator</strong>
                        </div>
                        <small class="text-muted">Administrative access with system oversight capabilities. Must be linked to a hospital.</small>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success me-2">Doctor</span>
                            <strong class="text-dark">Doctor</strong>
                        </div>
                        <small class="text-muted">Medical professional with patient and case management access.</small>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-warning text-dark me-2">Scientist</span>
                            <strong class="text-dark">Scientist</strong>
                        </div>
                        <small class="text-muted">Scientific staff with analysis and research capabilities.</small>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary me-2">Technician</span>
                            <strong class="text-dark">Technician</strong>
                        </div>
                        <small class="text-muted">Technical staff with patient data entry and case processing access.</small>
                    </div>
                    
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary me-2">Patient</span>
                            <strong class="text-dark">Patient</strong>
                        </div>
                        <small class="text-muted">Patient record with limited system access. Managed by hospital staff.</small>
                    </div>
                </div>
            </div>
            
            <!-- Password Requirements -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-lock me-2 text-warning"></i>Password Requirements
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>At least 8 characters long</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Mix of letters and numbers recommended</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Special characters allowed</small>
                        </li>
                        <li>
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Case sensitive</small>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bolt me-2 text-warning"></i>After Creation
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <small class="text-muted mb-3 d-block">Once the user is created, you can:</small>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Edit user information</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Change role assignments</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Deactivate/activate account</small>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Reset user password</small>
                        </li>
                    </ul>
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
                
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
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
                if (confirmPassword.value !== '' && password.value !== confirmPassword.value) {
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
            // All admin-accessible roles require hospital assignment
            hospitalField.style.display = 'block';
        }
        
        // Initial check
        toggleHospitalField();
        
        // Check on role change
        roleSelect.addEventListener('change', toggleHospitalField);
    }, false);
})();
</script>