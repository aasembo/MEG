<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Procedure $procedure
 * @var \Cake\Collection\CollectionInterface|string[] $departments
 * @var \Cake\Collection\CollectionInterface|string[] $sedations
 */
?>
<?php $this->assign('title', 'Edit Procedure'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-edit me-2"></i>Edit Procedure
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-procedures me-2"></i>Update procedure: <?php echo h($procedure->name) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-eye me-1"></i>View',
                            ['action' => 'view', $procedure->id],
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
                        <i class="fas fa-procedures me-2 text-warning"></i>Procedure Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <?php echo $this->Form->create($procedure, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    
                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('name', [
                                'label' => 'Procedure Name *',
                                'class' => 'form-control' . ($procedure->hasErrors('name') ? ' is-invalid' : ''),
                                'required' => true,
                                'placeholder' => 'e.g., Colonoscopy, CT Guided Biopsy, Cardiac Catheterization'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('type', [
                                'label' => 'Procedure Type',
                                'class' => 'form-control' . ($procedure->hasErrors('type') ? ' is-invalid' : ''),
                                'required' => false,
                                'placeholder' => 'e.g., Diagnostic, Therapeutic, Surgical'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('department_id', [
                                'label' => 'Department *',
                                'type' => 'select',
                                'options' => $departments,
                                'empty' => 'Select a department...',
                                'class' => 'form-select' . ($procedure->hasErrors('department_id') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('sedation_id', [
                                'label' => 'Sedation Level',
                                'type' => 'select',
                                'options' => $sedations,
                                'empty' => 'Select sedation level...',
                                'class' => 'form-select' . ($procedure->hasErrors('sedation_id') ? ' is-invalid' : ''),
                                'required' => false
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('risk_level', [
                                'label' => 'Risk Level',
                                'type' => 'select',
                                'options' => [
                                    'low' => 'Low Risk',
                                    'medium' => 'Medium Risk',
                                    'high' => 'High Risk'
                                ],
                                'empty' => 'Select risk level...',
                                'class' => 'form-select' . ($procedure->hasErrors('risk_level') ? ' is-invalid' : ''),
                                'required' => false
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <div class="mt-4 pt-2">
                                <div class="form-check">
                                    <?php echo $this->Form->control('consent_required', [
                                        'type' => 'checkbox',
                                        'class' => 'form-check-input',
                                        'label' => [
                                            'text' => 'Consent Required',
                                            'class' => 'form-check-label fw-semibold'
                                        ]
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('duration_minutes', [
                                'label' => 'Duration (Minutes)',
                                'type' => 'number',
                                'class' => 'form-control' . ($procedure->hasErrors('duration_minutes') ? ' is-invalid' : ''),
                                'min' => 1,
                                'required' => false,
                                'placeholder' => 'Enter duration in minutes'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('cost', [
                                'label' => 'Cost ($)',
                                'type' => 'number',
                                'step' => 0.01,
                                'class' => 'form-control' . ($procedure->hasErrors('cost') ? ' is-invalid' : ''),
                                'required' => false,
                                'placeholder' => 'Enter procedure cost'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <?php echo $this->Form->control('description', [
                                'label' => 'Description',
                                'type' => 'textarea',
                                'class' => 'form-control' . ($procedure->hasErrors('description') ? ' is-invalid' : ''),
                                'rows' => 3,
                                'required' => false,
                                'placeholder' => 'Describe the procedure purpose, what it involves, and any relevant details...'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <?php echo $this->Form->control('notes', [
                                'label' => 'Additional Notes',
                                'type' => 'textarea',
                                'class' => 'form-control' . ($procedure->hasErrors('notes') ? ' is-invalid' : ''),
                                'rows' => 2,
                                'required' => false,
                                'placeholder' => 'Any additional notes, instructions, or special considerations...'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-times me-2"></i>Cancel',
                                    ['action' => 'view', $procedure->id],
                                    ['class' => 'btn btn-outline-secondary', 'escape' => false]
                                ) ?>
                                <?php echo $this->Form->button(
                                    '<i class="fas fa-save me-2"></i>Update Procedure',
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
            <!-- Procedure Details -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle me-2 text-warning"></i>Procedure Details
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Procedure ID:</strong><br>
                        <span class="text-muted">#<?php echo h($procedure->id) ?></span>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Created:</strong><br>
                        <span class="text-muted"><?php echo $procedure->created->format('F j, Y \a\t g:i A') ?></span>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Last Modified:</strong><br>
                        <span class="text-muted"><?php echo $procedure->modified->format('F j, Y \a\t g:i A') ?></span>
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
                        <i class="fas fa-chart-bar me-2 text-warning"></i>Procedure Statistics
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-primary mb-1"><?php echo $procedure->duration_minutes ?? 0 ?></h5>
                                <small class="text-muted">Minutes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-success mb-1"><?php echo $procedure->cost ? '$' . number_format($procedure->cost, 0) : '$0' ?></h5>
                            <small class="text-muted">Cost</small>
                        </div>
                    </div>
                    
                    <?php if (!empty($procedure->department)): ?>
                    <hr class="my-3">
                    <div class="text-center">
                        <strong class="text-dark">Department:</strong><br>
                        <span class="text-muted"><?php echo h($procedure->department->name) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($procedure->risk_level)): ?>
                    <hr class="my-3">
                    <div class="text-center">
                        <strong class="text-dark">Risk Level:</strong><br>
                        <span class="badge bg-<?php echo $procedure->risk_level === 'high' ? 'danger' : ($procedure->risk_level === 'medium' ? 'warning text-dark' : 'success') ?>">
                            <?php echo ucfirst(h($procedure->risk_level)) ?>
                        </span>
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
                            '<i class="fas fa-eye me-2"></i>View Procedure',
                            ['action' => 'view', $procedure->id],
                            ['class' => 'btn btn-outline-info', 'escape' => false]
                        ) ?>
                        
                        <?php echo $this->Form->postLink(
                            '<i class="fas fa-trash me-2"></i>Delete Procedure',
                            ['action' => 'delete', $procedure->id],
                            [
                                'class' => 'btn btn-outline-danger',
                                'escape' => false,
                                'confirm' => 'Are you sure you want to delete this procedure? This action cannot be undone.'
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