<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Department $department
 */
?>
<?php $this->assign('title', 'Add Department'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-plus-circle me-2"></i>Add New Department
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-building me-2"></i>Create a new hospital department
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-left me-2"></i>Back to Departments',
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
                            <div class="alert alert-info border-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> The department will be automatically linked to your hospital and will be available for exam and procedure assignment.
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
                                    '<i class="fas fa-plus me-2"></i>Create Department',
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
            <!-- Naming Guidelines -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>Naming Guidelines
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Use standard medical terminology</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Keep names concise but descriptive</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Avoid abbreviations when possible</small>
                        </li>
                        <li>
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Consider department hierarchy</small>
                        </li>
                    </ul>
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
    }
});
</script>