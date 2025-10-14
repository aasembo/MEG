<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var \Cake\Collection\CollectionInterface|string[] $roles
 */
?>
<?php $this->assign('title', 'Edit User'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-user-edit me-2 text-primary"></i>Edit User
        </h1>
        <p class="text-muted mb-0">Update user account information</p>
    </div>
    <div>
        <?php echo $this->Html->link(
            '<i class="fas fa-eye me-2"></i>View',
            ['action' => 'view', $user->id],
            ['class' => 'btn btn-info me-2', 'escape' => false]
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
                            ]
                        ]) ?>
                        <?php if ($user->hasErrors('username')): ?>
                            <div class="invalid-feedback d-block">
                                <?php 
                                    $errors = $user->getError('username');
                                    if (is_array($errors)) {
                                        echo h(reset($errors));
                                    } else {
                                        echo h($errors);
                                    }
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="invalid-feedback">
                                Please provide a username.
                            </div>
                        <?php endif; ?>
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
                            ]
                        ]) ?>
                        <?php if ($user->hasErrors('email')): ?>
                            <div class="invalid-feedback d-block">
                                <?php 
                                    $errors = $user->getError('email');
                                    if (is_array($errors)) {
                                        echo h(reset($errors));
                                    } else {
                                        echo h($errors);
                                    }
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?php echo $this->Form->control('first_name', [
                            'type' => 'text',
                            'class' => 'form-control',
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
                            ]
                        ]) ?>
                        <div class="invalid-feedback">
                            Please provide the first name.
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <?php echo $this->Form->control('last_name', [
                            'type' => 'text',
                            'class' => 'form-control',
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
                            ]
                        ]) ?>
                        <div class="invalid-feedback">
                            Please provide the last name.
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <?php echo $this->Form->control('role_id', [
                            'type' => 'select',
                            'options' => $roles,
                            'empty' => 'Select a role...',
                            'class' => 'form-select',
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
                            ]
                        ]) ?>
                        <div class="invalid-feedback">
                            Please select a user role.
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3" id="hospital-field">
                        <label class="form-label">Hospital <span class="text-danger">*</span></label>
                        <div class="form-control-plaintext border p-2 bg-light rounded">
                            <i class="fas fa-hospital me-2 text-primary"></i>
                            <?php echo h($currentHospital->name) ?>
                            <small class="text-muted d-block">Current hospital context</small>
                        </div>
                        <input type="hidden" name="hospital_id" value="<?php echo $currentHospital->id ?>">
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <?php echo $this->Form->control('status', [
                            'type' => 'select',
                            'options' => [
                                'active' => 'Active',
                                'inactive' => 'Inactive'
                            ],
                            'class' => 'form-select',
                            'label' => [
                                'text' => 'Account Status <span class="text-danger">*</span>',
                                'escape' => false,
                                'class' => 'form-label'
                            ],
                            'required' => true,
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'select' => '<select name="{{name}}" {{attrs}}>{{content}}</select>',
                            ]
                        ]) ?>
                        <div class="invalid-feedback">
                            Please select account status.
                        </div>
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
                
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Password Update:</strong> Leave password fields empty to keep the current password unchanged.
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?php echo $this->Form->control('password', [
                            'type' => 'password',
                            'class' => 'form-control',
                            'label' => [
                                'text' => 'New Password',
                                'class' => 'form-label'
                            ],
                            'required' => false,
                            'placeholder' => 'Enter new password (optional)',
                            'minlength' => 8,
                            'value' => '',
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                            ]
                        ]) ?>
                        <div class="form-text">Leave empty to keep current password.</div>
                        <div class="invalid-feedback">
                            Password must be at least 8 characters long.
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <?php echo $this->Form->control('confirm_password', [
                            'type' => 'password',
                            'class' => 'form-control',
                            'label' => [
                                'text' => 'Confirm New Password',
                                'class' => 'form-label'
                            ],
                            'required' => false,
                            'placeholder' => 'Confirm new password',
                            'value' => '',
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                            ]
                        ]) ?>
                        <div class="invalid-feedback">
                            Passwords do not match.
                        </div>
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
                        <i class="fas fa-save me-2"></i>Update User
                    </button>
                </div>
            </div>
            
            <?php echo $this->Form->end() ?>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- User Details -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>User Details
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>User ID:</strong><br>
                    <span class="text-muted">#<?php echo h($user->id) ?></span>
                </div>
                
                <div class="mb-3">
                    <strong>Created:</strong><br>
                    <span class="text-muted"><?php echo $user->created->format('F j, Y \a\t g:i A') ?></span>
                </div>
                
                <div class="mb-3">
                    <strong>Last Modified:</strong><br>
                    <span class="text-muted"><?php echo $user->modified->format('F j, Y \a\t g:i A') ?></span>
                </div>
                
                <div>
                    <strong>Current Status:</strong><br>
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
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-user-tag me-2"></i>Current Role
                </h6>
            </div>
            <div class="card-body">
                <?php if ($user->role): ?>
                    <?php
                    $roleColors = [
                        'super' => 'warning',
                        'admin' => 'info',
                        'user' => 'secondary'
                    ];
                    $roleColor = $roleColors[$user->role->type] ?? 'secondary';
                    $roleDescriptions = [
                        'super' => 'Full system access and administration rights.',
                        'admin' => 'Administrative access with limited system settings.',
                        'user' => 'Standard user access with basic permissions.'
                    ];
                    $roleDescription = $roleDescriptions[$user->role->type] ?? 'Custom role permissions.';
                    ?>
                    <div class="text-center">
                        <span class="badge bg-<?php echo $roleColor ?> fs-6 mb-2">
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
        
        <!-- Actions -->
        <div class="card border-0 shadow-sm mt-3">
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
                
                <?php if (!$isCurrentUser): ?>
                    <?php echo $this->Form->postLink(
                        ($user->active ? '<i class="fas fa-user-slash me-2"></i>Deactivate' : '<i class="fas fa-user-check me-2"></i>Activate') . ' User',
                        ['action' => 'toggleStatus', $user->id],
                        [
                            'class' => 'btn btn-sm ' . ($user->active ? 'btn-warning' : 'btn-success') . ' w-100 mb-2',
                            'escape' => false,
                            'confirm' => 'Are you sure you want to ' . ($user->active ? 'deactivate' : 'activate') . ' this user?'
                        ]
                    ) ?>
                    
                    <?php echo $this->Form->postLink(
                        '<i class="fas fa-trash me-2"></i>Delete User',
                        ['action' => 'delete', $user->id],
                        [
                            'class' => 'btn btn-sm btn-danger w-100',
                            'escape' => false,
                            'confirm' => 'Are you sure you want to delete this user? This action cannot be undone.'
                        ]
                    ) ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> You cannot modify your own account status or delete your account.
                    </div>
                <?php endif; ?>
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
        var hospitalSelect = hospitalField.querySelector('select');
        
        function toggleHospitalField() {
            var selectedOption = roleSelect.options[roleSelect.selectedIndex];
            var roleText = selectedOption.text.toLowerCase();
            
            if (roleText.includes('super')) {
                hospitalField.style.display = 'none';
                hospitalSelect.required = false;
            } else {
                hospitalField.style.display = 'block';
                hospitalSelect.required = true;
            }
        }
        
        // Initial check
        toggleHospitalField();
        
        // Check on role change
        roleSelect.addEventListener('change', toggleHospitalField);
        
        // Specialized role fields functionality
        var specializedFields = document.getElementById('specialized-fields');
        var roleFields = document.querySelectorAll('.role-fields');
        
        // Role type detection using data attributes (most reliable method)
        function getRoleTypeFromSelect() {
            var selectedRoleId = roleSelect.value;
            if (!selectedRoleId) return null;
            
            var roleTypes = JSON.parse(roleSelect.getAttribute('data-role-types') || '{}');
            return roleTypes[selectedRoleId] || null;
        }
        
        function toggleRoleFields() {
            var roleName = getRoleTypeFromSelect();
            
            // Hide all role fields first
            roleFields.forEach(function(field) {
                field.style.display = 'none';
            });
            
            // Show specialized fields container if a specialized role is selected
            if (roleName) {
                specializedFields.style.display = 'block';
                var targetField = document.getElementById(roleName + '-fields');
                if (targetField) {
                    targetField.style.display = 'block';
                }
            } else {
                specializedFields.style.display = 'none';
            }
        }
        
        // Initialize specialized fields on page load
        toggleRoleFields();
        
        // Listen for role changes for specialized fields
        roleSelect.addEventListener('change', toggleRoleFields);
        
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