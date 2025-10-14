<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var \Cake\Collection\CollectionInterface|string[] $roles
 */
?>
<?php $this->assign('title', 'Add New User'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-user-plus me-2 text-primary"></i>Add New User
        </h1>
        <p class="text-muted mb-0">Create a new user account</p>
    </div>
    <div>
        <?php echo $this->Html->link(
            '<i class="fas fa-arrow-left me-2"></i>Back to Users',
            ['action' => 'index'],
            ['class' => 'btn btn-secondary', 'escape' => false]
        ) ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-circle me-2"></i>User Information
                </h5>
            </div>
            <div class="card-body">
                <?php echo $this->Form->create($user, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?php echo $this->Form->control('username', [
                            'type' => 'text',
                            'class' => 'form-control' . ($user->hasErrors('username') ? ' is-invalid' : ''),
                            'label' => [
                                'text' => 'Username <span class="text-danger">*</span>',
                                'escape' => false,
                                'class' => 'form-label'
                            ],
                            'required' => true,
                            'placeholder' => 'Enter username',
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                            ]
                        ]) ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <?php echo $this->Form->control('email', [
                            'type' => 'email',
                            'class' => 'form-control' . ($user->hasErrors('email') ? ' is-invalid' : ''),
                            'label' => [
                                'text' => 'Email Address <span class="text-danger">*</span>',
                                'escape' => false,
                                'class' => 'form-label'
                            ],
                            'required' => true,
                            'placeholder' => 'user@example.com',
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                            ]
                        ]) ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?php echo $this->Form->control('first_name', [
                            'type' => 'text',
                            'class' => 'form-control' . ($user->hasErrors('first_name') ? ' is-invalid' : ''),
                            'label' => [
                                'text' => 'First Name <span class="text-danger">*</span>',
                                'escape' => false,
                                'class' => 'form-label'
                            ],
                            'required' => true,
                            'placeholder' => 'Enter first name',
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                            ]
                        ]) ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <?php echo $this->Form->control('last_name', [
                            'type' => 'text',
                            'class' => 'form-control' . ($user->hasErrors('last_name') ? ' is-invalid' : ''),
                            'label' => [
                                'text' => 'Last Name <span class="text-danger">*</span>',
                                'escape' => false,
                                'class' => 'form-label'
                            ],
                            'required' => true,
                            'placeholder' => 'Enter last name',
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                            ]
                        ]) ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <?php echo $this->Form->control('role_id', [
                            'type' => 'select',
                            'options' => $roles,
                            'empty' => 'Select a role...',
                            'class' => 'form-select' . ($user->hasErrors('role_id') ? ' is-invalid' : ''),
                            'label' => [
                                'text' => 'User Role <span class="text-danger">*</span>',
                                'escape' => false,
                                'class' => 'form-label'
                            ],
                            'required' => true,
                            'div' => false,
                            'id' => 'role-select',
                            'data-role-types' => json_encode($roleTypes),
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'select' => '<select name="{{name}}" {{attrs}}>{{content}}</select>',
                                'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                            ]
                        ]) ?>
                    </div>
                    
                    <div class="col-md-4 mb-3" id="hospital-field">
                        <?php echo $this->Form->control('hospital_id', [
                            'type' => 'select',
                            'options' => $hospitals,
                            'empty' => 'Select a hospital...',
                            'class' => 'form-select' . ($user->hasErrors('hospital_id') ? ' is-invalid' : ''),
                            'label' => [
                                'text' => 'Hospital <span class="text-danger">*</span>',
                                'escape' => false,
                                'class' => 'form-label'
                            ],
                            'required' => true,
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'select' => '<select name="{{name}}" {{attrs}}>{{content}}</select>',
                                'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                            ]
                        ]) ?>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <?php echo $this->Form->control('status', [
                            'type' => 'select',
                            'options' => [
                                'active' => 'Active',
                                'inactive' => 'Inactive'
                            ],
                            'class' => 'form-select' . ($user->hasErrors('status') ? ' is-invalid' : ''),
                            'label' => [
                                'text' => 'Account Status <span class="text-danger">*</span>',
                                'escape' => false,
                                'class' => 'form-label'
                            ],
                            'default' => 'active',
                            'required' => true,
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'select' => '<select name="{{name}}" {{attrs}}>{{content}}</select>',
                                'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                            ]
                        ]) ?>
                    </div>
                </div>
                
                <!-- Specialized Role Fields -->
                <div id="specialized-fields" style="display: none;">
                    <hr class="my-4">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-user-cog me-2"></i>Additional Role-Specific Information
                    </h6>
                    
                    <!-- Doctor Fields -->
                    <div id="doctor-fields" class="role-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?php echo $this->Form->control('doctor_phone', [
                                    'type' => 'text',
                                    'class' => 'form-control',
                                    'label' => [
                                        'text' => 'Phone Number',
                                        'class' => 'form-label'
                                    ],
                                    'placeholder' => 'Enter phone number',
                                    'div' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                        'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                                    ]
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Nurse Fields -->
                    <div id="nurse-fields" class="role-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <?php echo $this->Form->control('nurse_phone', [
                                    'type' => 'text',
                                    'class' => 'form-control',
                                    'label' => [
                                        'text' => 'Phone Number',
                                        'class' => 'form-label'
                                    ],
                                    'placeholder' => 'Enter phone number',
                                    'div' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                        'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                                    ]
                                ]) ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <?php echo $this->Form->control('nurse_gender', [
                                    'type' => 'select',
                                    'options' => [
                                        '' => 'Select Gender',
                                        'M' => 'Male',
                                        'F' => 'Female',
                                        'O' => 'Other'
                                    ],
                                    'class' => 'form-select',
                                    'label' => [
                                        'text' => 'Gender <span class="text-danger">*</span>',
                                        'escape' => false,
                                        'class' => 'form-label'
                                    ],
                                    'div' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'select' => '<select name="{{name}}" {{attrs}}>{{content}}</select>',
                                        'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                                    ]
                                ]) ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <?php echo $this->Form->control('nurse_dob', [
                                    'type' => 'date',
                                    'class' => 'form-control',
                                    'label' => [
                                        'text' => 'Date of Birth <span class="text-danger">*</span>',
                                        'escape' => false,
                                        'class' => 'form-label'
                                    ],
                                    'div' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                        'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                                    ]
                                ]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?php echo $this->Form->control('nurse_age', [
                                    'type' => 'number',
                                    'class' => 'form-control',
                                    'label' => [
                                        'text' => 'Age <span class="text-danger">*</span>',
                                        'escape' => false,
                                        'class' => 'form-label'
                                    ],
                                    'min' => 18,
                                    'max' => 100,
                                    'placeholder' => 'Enter age',
                                    'div' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                        'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                                    ]
                                ]) ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <?php echo $this->Form->control('nurse_record_number', [
                                    'type' => 'text',
                                    'class' => 'form-control',
                                    'label' => [
                                        'text' => 'Record Number <span class="text-danger">*</span>',
                                        'escape' => false,
                                        'class' => 'form-label'
                                    ],
                                    'placeholder' => 'Enter unique record number',
                                    'div' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                        'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                                    ]
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Scientist Fields -->
                    <div id="scientist-fields" class="role-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?php echo $this->Form->control('scientist_phone', [
                                    'type' => 'text',
                                    'class' => 'form-control',
                                    'label' => [
                                        'text' => 'Phone Number',
                                        'class' => 'form-label'
                                    ],
                                    'placeholder' => 'Enter phone number',
                                    'div' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                        'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                                    ]
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Patient Fields -->
                    <div id="patient-fields" class="role-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <?php echo $this->Form->control('patient_gender', [
                                    'type' => 'select',
                                    'options' => [
                                        '' => 'Select Gender',
                                        'M' => 'Male',
                                        'F' => 'Female',
                                        'O' => 'Other'
                                    ],
                                    'class' => 'form-select',
                                    'label' => [
                                        'text' => 'Gender <span class="text-danger">*</span>',
                                        'escape' => false,
                                        'class' => 'form-label'
                                    ],
                                    'div' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'select' => '<select name="{{name}}" {{attrs}}>{{content}}</select>',
                                        'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                                    ]
                                ]) ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <?php echo $this->Form->control('patient_dob', [
                                    'type' => 'date',
                                    'class' => 'form-control',
                                    'label' => [
                                        'text' => 'Date of Birth <span class="text-danger">*</span>',
                                        'escape' => false,
                                        'class' => 'form-label'
                                    ],
                                    'div' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                        'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                                    ]
                                ]) ?>
                            </div>
                            <div class="col-md-4 mb-3">
                                <?php echo $this->Form->control('patient_age', [
                                    'type' => 'number',
                                    'class' => 'form-control',
                                    'label' => [
                                        'text' => 'Age <span class="text-danger">*</span>',
                                        'escape' => false,
                                        'class' => 'form-label'
                                    ],
                                    'min' => 0,
                                    'max' => 150,
                                    'placeholder' => 'Enter age',
                                    'div' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                        'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                                    ]
                                ]) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?php echo $this->Form->control('patient_medical_record_number', [
                                    'type' => 'text',
                                    'class' => 'form-control',
                                    'label' => [
                                        'text' => 'Medical Record Number',
                                        'class' => 'form-label'
                                    ],
                                    'placeholder' => 'Enter medical record number',
                                    'div' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                        'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                                    ]
                                ]) ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <?php echo $this->Form->control('patient_financial_record_number', [
                                    'type' => 'text',
                                    'class' => 'form-control',
                                    'label' => [
                                        'text' => 'Financial Record Number',
                                        'class' => 'form-label'
                                    ],
                                    'placeholder' => 'Enter financial record number',
                                    'div' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                        'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                                    ]
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Technician Fields -->
                    <div id="technician-fields" class="role-fields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?php echo $this->Form->control('technician_phone', [
                                    'type' => 'text',
                                    'class' => 'form-control',
                                    'label' => [
                                        'text' => 'Phone Number',
                                        'class' => 'form-label'
                                    ],
                                    'placeholder' => 'Enter phone number',
                                    'div' => false,
                                    'templates' => [
                                        'inputContainer' => '{{content}}',
                                        'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                        'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                                    ]
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?php echo $this->Form->control('password', [
                            'type' => 'password',
                            'class' => 'form-control' . ($user->hasErrors('password') ? ' is-invalid' : ''),
                            'label' => [
                                'text' => 'Password <span class="text-danger">*</span>',
                                'escape' => false,
                                'class' => 'form-label'
                            ],
                            'required' => true,
                            'placeholder' => 'Enter password',
                            'minlength' => 8,
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                            ]
                        ]) ?>
                        <div class="form-text">Password must be at least 8 characters long.</div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <?php echo $this->Form->control('confirm_password', [
                            'type' => 'password',
                            'class' => 'form-control' . ($user->hasErrors('confirm_password') ? ' is-invalid' : ''),
                            'label' => [
                                'text' => 'Confirm Password <span class="text-danger">*</span>',
                                'escape' => false,
                                'class' => 'form-label'
                            ],
                            'required' => true,
                            'placeholder' => 'Confirm password',
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                            ]
                        ]) ?>
                    </div>
                </div>
                
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times me-2"></i>Cancel',
                        ['action' => 'index'],
                        ['class' => 'btn btn-secondary', 'escape' => false]
                    ) ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create User
                    </button>
                </div>
            </div>
            
            <?php echo $this->Form->end() ?>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- User Roles Information -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>User Roles
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-warning me-2">Super</span>
                        <strong>Super User</strong>
                    </div>
                    <small class="text-muted">Full system access and administration rights. <strong>Not linked to any hospital.</strong></small>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-info me-2">Admin</span>
                        <strong>Administrator</strong>
                    </div>
                    <small class="text-muted">Administrative access with limited system settings. <strong>Must be linked to a hospital.</strong></small>
                </div>
                
                <div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-secondary me-2">User</span>
                        <strong>Regular User</strong>
                    </div>
                    <small class="text-muted">Standard user access with basic permissions. <strong>Must be linked to a hospital.</strong></small>
                </div>
            </div>
        </div>
        
        <!-- Password Requirements -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-lock me-2"></i>Password Requirements
                </h6>
            </div>
            <div class="card-body">
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
        var hospitalSelect = hospitalField.querySelector('select');
        var specializedFieldsContainer = document.getElementById('specialized-fields');
        
        // Role type detection using data attributes (most reliable method)
        function getRoleTypeFromSelect() {
            var selectedRoleId = roleSelect.value;
            if (!selectedRoleId) return null;
            
            var roleTypes = JSON.parse(roleSelect.getAttribute('data-role-types') || '{}');
            return roleTypes[selectedRoleId] || null;
        }
        
        function toggleHospitalField() {
            var selectedOption = roleSelect.options[roleSelect.selectedIndex];
            var roleText = selectedOption.text.toLowerCase();
            
            if (roleText.includes('super')) {
                hospitalField.style.display = 'none';
                hospitalSelect.required = false;
                hospitalSelect.value = '';
            } else {
                hospitalField.style.display = 'block';
                hospitalSelect.required = true;
            }
        }
        
        function toggleSpecializedFields() {
            var roleType = getRoleTypeFromSelect();
            
            // Hide all role fields first
            var allRoleFields = document.querySelectorAll('.role-fields');
            allRoleFields.forEach(function(field) {
                field.style.display = 'none';
            });
            
            // Hide specialized container
            specializedFieldsContainer.style.display = 'none';
            
            // Show appropriate role fields if specialized role is selected
            if (roleType) {
                specializedFieldsContainer.style.display = 'block';
                var targetFields = document.getElementById(roleType + '-fields');
                if (targetFields) {
                    targetFields.style.display = 'block';
                    
                    // Make required fields required
                    var requiredFields = targetFields.querySelectorAll('input[required], select[required]');
                    requiredFields.forEach(function(field) {
                        field.required = true;
                    });
                }
            }
            
            // Also toggle hospital field
            toggleHospitalField();
        }
        
        // Initial check
        toggleSpecializedFields();
        
        // Check on role change
        roleSelect.addEventListener('change', toggleSpecializedFields);
        
        // Auto-calculate age from date of birth
        function setupAgeCalculation(dobFieldId, ageFieldId) {
            var dobField = document.getElementById(dobFieldId);
            var ageField = document.getElementById(ageFieldId);
            
            if (dobField && ageField) {
                dobField.addEventListener('change', function() {
                    if (this.value) {
                        var birthDate = new Date(this.value);
                        var today = new Date();
                        var age = today.getFullYear() - birthDate.getFullYear();
                        var monthDiff = today.getMonth() - birthDate.getMonth();
                        
                        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                            age--;
                        }
                        
                        ageField.value = age;
                    }
                });
            }
        }
        
        // Setup age calculation for nurse and patient
        setupAgeCalculation('nurse-dob', 'nurse-age');
        setupAgeCalculation('patient-dob', 'patient-age');
    }, false);
})();
</script>