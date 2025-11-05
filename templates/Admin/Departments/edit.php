<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Department $department
 */
?>
<?php $this->assign('title', 'Edit Department'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-edit me-2"></i>Edit Department
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-building me-2"></i>Update department: <?php echo h($department->name) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-eye me-1"></i>View',
                            ['action' => 'view', $department->id],
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
                        <i class="fas fa-building me-2 text-warning"></i>Department Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <?php echo $this->Form->create($department, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    
                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <?php echo $this->Form->control('name', [
                                'label' => 'Department Name *',
                                'class' => 'form-control' . ($department->hasErrors('name') ? ' is-invalid' : ''),
                                'required' => true,
                                'placeholder' => 'e.g., Cardiology, Emergency Medicine, Radiology'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <?php echo $this->Form->control('description', [
                                'label' => 'Description',
                                'type' => 'textarea',
                                'class' => 'form-control' . ($department->hasErrors('description') ? ' is-invalid' : ''),
                                'rows' => 4,
                                'required' => false,
                                'placeholder' => 'Describe the department\'s services, specialties, and purpose...'
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
                                    '<i class="fas fa-save me-2"></i>Update Department',
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
            <!-- Department Details -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle me-2 text-warning"></i>Department Details
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Department ID:</strong><br>
                        <span class="text-muted">#<?php echo h($department->id) ?></span>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Created:</strong><br>
                        <span class="text-muted"><?php echo $department->created->format('F j, Y \a\t g:i A') ?></span>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Last Modified:</strong><br>
                        <span class="text-muted"><?php echo $department->modified->format('F j, Y \a\t g:i A') ?></span>
                    </div>
                    
                    <div>
                        <strong class="text-dark">Current Status:</strong><br>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Active
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Current Statistics -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-bar me-2 text-warning"></i>Department Statistics
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-primary mb-1"><?php echo count($department->exams ?? []) ?></h5>
                                <small class="text-muted">Exams</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-success mb-1"><?php echo count($department->procedures ?? []) ?></h5>
                            <small class="text-muted">Procedures</small>
                        </div>
                    </div>
                    
                    <?php if (!empty($department->hospital)): ?>
                    <hr class="my-3">
                    <div class="text-center">
                        <strong class="text-dark">Hospital:</strong><br>
                        <span class="text-muted"><?php echo h($department->hospital->name) ?></span>
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
                    <div class="d-grid gap-2">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-eye me-2"></i>View Department',
                            ['action' => 'view', $department->id],
                            ['class' => 'btn btn-outline-info', 'escape' => false]
                        ) ?>
                        
                        <?php echo $this->Form->postLink(
                            '<i class="fas fa-trash me-2"></i>Delete Department',
                            ['action' => 'delete', $department->id],
                            [
                                'class' => 'btn btn-outline-danger',
                                'escape' => false,
                                'confirm' => 'Are you sure you want to delete this department? This action cannot be undone.'
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add form validation
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }

    // Auto-focus on name field
    const nameField = document.querySelector('#name');
    if (nameField) {
        nameField.focus();
        nameField.select(); // Select existing text for easy editing
    }
});
</script>