<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Procedure $procedure
 * @var \Cake\Collection\CollectionInterface|string[] $departments
 * @var \Cake\Collection\CollectionInterface|string[] $sedations
 */
?>
<?php $this->assign('title', 'Add Procedure'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-plus-circle me-2"></i>Add New Procedure
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-procedures me-2"></i>Create a new medical procedure
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-left me-2"></i>Back to Procedures',
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
                            <div class="alert alert-info border-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> The procedure will be available for scheduling once created and can be linked to patient cases.
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
                                    '<i class="fas fa-plus me-2"></i>Create Procedure',
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
            <!-- Procedure Types Guide -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>Procedure Types
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-info me-2">Diagnostic</span>
                            <strong class="text-dark">Diagnostic Procedures</strong>
                        </div>
                        <small class="text-muted">Colonoscopy, Endoscopy, Biopsy, Catheterization</small>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success me-2">Therapeutic</span>
                            <strong class="text-dark">Therapeutic Procedures</strong>
                        </div>
                        <small class="text-muted">Angioplasty, Ablation, Injection, Drainage</small>
                    </div>
                    
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-danger me-2">Surgical</span>
                            <strong class="text-dark">Surgical Procedures</strong>
                        </div>
                        <small class="text-muted">Laparoscopy, Resection, Repair, Reconstruction</small>
                    </div>
                </div>
            </div>
            
            <!-- Risk Level Guide -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-shield-alt me-2 text-warning"></i>Risk Level Guidelines
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <span class="badge bg-success me-2">Low</span>
                            <small>Minimal invasive, routine procedures</small>
                        </li>
                        <li class="mb-2">
                            <span class="badge bg-warning text-dark me-2">Medium</span>
                            <small>Moderate complexity, some risks</small>
                        </li>
                        <li>
                            <span class="badge bg-danger me-2">High</span>
                            <small>Complex procedures, significant risks</small>
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