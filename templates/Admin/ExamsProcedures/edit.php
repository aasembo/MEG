<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ExamsProcedure $examsProcedure
 * @var \Cake\Collection\CollectionInterface|string[] $exams
 * @var \Cake\Collection\CollectionInterface|string[] $procedures
 */
?>
<?php $this->assign('title', 'Edit Exam-Procedure Association'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-edit me-2"></i>Edit Exam-Procedure Association
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-link me-2"></i>Update association: 
                        <?php if ($examsProcedure->hasValue('exam') && $examsProcedure->hasValue('procedure')): ?>
                            <?php echo h($examsProcedure->exam->name) ?> → <?php echo h($examsProcedure->procedure->name) ?>
                        <?php else: ?>
                            Association #<?php echo h($examsProcedure->id) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-eye me-1"></i>View',
                            ['action' => 'view', $examsProcedure->id],
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
                        <i class="fas fa-link me-2 text-warning"></i>Association Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <?php echo $this->Form->create($examsProcedure, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    
                    <!-- Basic Association -->
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">
                            <i class="fas fa-link me-1"></i>Basic Association
                        </h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <?php echo $this->Form->control('exam_id', [
                                    'label' => 'Exam *',
                                    'options' => $exams,
                                    'empty' => 'Select Exam',
                                    'class' => 'form-select' . ($examsProcedure->hasErrors('exam_id') ? ' is-invalid' : ''),
                                    'required' => true
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?php echo $this->Form->control('procedure_id', [
                                    'label' => 'Procedure *',
                                    'options' => $procedures,
                                    'empty' => 'Select Procedure',
                                    'class' => 'form-select' . ($examsProcedure->hasErrors('procedure_id') ? ' is-invalid' : ''),
                                    'required' => true
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Requirements -->
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">
                            <i class="fas fa-exclamation-triangle me-1"></i>Requirements
                        </h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <?php echo $this->Form->control('contrast_required', [
                                        'type' => 'checkbox',
                                        'class' => 'form-check-input',
                                        'label' => [
                                            'text' => 'Contrast Required',
                                            'class' => 'form-check-label fw-semibold'
                                        ],
                                        'div' => false
                                    ]) ?>
                                    <small class="text-muted d-block">Check if contrast agent is required for this procedure</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <?php echo $this->Form->control('sedation_required', [
                                        'type' => 'checkbox',
                                        'class' => 'form-check-input',
                                        'label' => [
                                            'text' => 'Sedation Required',
                                            'class' => 'form-check-label fw-semibold'
                                        ],
                                        'div' => false
                                    ]) ?>
                                    <small class="text-muted d-block">Check if sedation is required for this procedure</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">
                            <i class="fas fa-clipboard-list me-1"></i>Instructions
                        </h6>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <?php echo $this->Form->control('preparation_instructions', [
                                    'label' => 'Preparation Instructions',
                                    'type' => 'textarea',
                                    'class' => 'form-control' . ($examsProcedure->hasErrors('preparation_instructions') ? ' is-invalid' : ''),
                                    'rows' => 4,
                                    'placeholder' => 'Describe any special preparation required before this procedure...'
                                ]) ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <?php echo $this->Form->control('post_procedure_care', [
                                    'label' => 'Post-Procedure Care',
                                    'type' => 'textarea',
                                    'class' => 'form-control' . ($examsProcedure->hasErrors('post_procedure_care') ? ' is-invalid' : ''),
                                    'rows' => 4,
                                    'placeholder' => 'Describe post-procedure care instructions and recovery guidelines...'
                                ]) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small fw-bold mb-3">
                            <i class="fas fa-sticky-note me-1"></i>Additional Information
                        </h6>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <?php echo $this->Form->control('notes', [
                                    'label' => 'Notes',
                                    'type' => 'textarea',
                                    'class' => 'form-control' . ($examsProcedure->hasErrors('notes') ? ' is-invalid' : ''),
                                    'rows' => 3,
                                    'placeholder' => 'Any additional notes or special considerations for this association...'
                                ]) ?>
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
                                    '<i class="fas fa-save me-2"></i>Update Association',
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
            <!-- Association Details -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle me-2 text-warning"></i>Association Details
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Association ID:</strong><br>
                        <span class="text-muted">#<?php echo h($examsProcedure->id) ?></span>
                    </div>
                    
                    <?php if ($examsProcedure->hasValue('exam')): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Current Exam:</strong><br>
                        <span class="text-muted">
                            <i class="fas fa-stethoscope me-1 text-primary"></i>
                            <?php echo h($examsProcedure->exam->name) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($examsProcedure->hasValue('procedure')): ?>
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Current Procedure:</strong><br>
                        <span class="text-muted">
                            <i class="fas fa-procedures me-1 text-success"></i>
                            <?php echo h($examsProcedure->procedure->name) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Created:</strong><br>
                        <span class="text-muted"><?php echo $examsProcedure->created->format('F j, Y \a\t g:i A') ?></span>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Last Modified:</strong><br>
                        <span class="text-muted"><?php echo $examsProcedure->modified->format('F j, Y \a\t g:i A') ?></span>
                    </div>
                    
                    <div>
                        <strong class="text-dark">Current Status:</strong><br>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Active
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Current Requirements -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-exclamation-triangle me-2 text-warning"></i>Current Requirements
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="border-end">
                                <div class="<?php echo $examsProcedure->contrast_required ? 'text-warning' : 'text-muted' ?>">
                                    <i class="fas fa-contrast fs-3 mb-2"></i>
                                    <div class="fw-bold"><?php echo $examsProcedure->contrast_required ? 'Required' : 'Not Required' ?></div>
                                    <small>Contrast</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="<?php echo $examsProcedure->sedation_required ? 'text-danger' : 'text-muted' ?>">
                                <i class="fas fa-bed fs-3 mb-2"></i>
                                <div class="fw-bold"><?php echo $examsProcedure->sedation_required ? 'Required' : 'Not Required' ?></div>
                                <small>Sedation</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="<?php echo !empty($examsProcedure->preparation_instructions) ? 'text-info' : 'text-muted' ?>">
                                <i class="fas fa-clipboard-list fs-5"></i>
                                <div class="small"><?php echo !empty($examsProcedure->preparation_instructions) ? 'Has Prep' : 'No Prep' ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="<?php echo !empty($examsProcedure->post_procedure_care) ? 'text-success' : 'text-muted' ?>">
                                <i class="fas fa-heart fs-5"></i>
                                <div class="small"><?php echo !empty($examsProcedure->post_procedure_care) ? 'Has Care' : 'No Care' ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="<?php echo !empty($examsProcedure->notes) ? 'text-secondary' : 'text-muted' ?>">
                                <i class="fas fa-sticky-note fs-5"></i>
                                <div class="small"><?php echo !empty($examsProcedure->notes) ? 'Has Notes' : 'No Notes' ?></div>
                            </div>
                        </div>
                    </div>
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
                            '<i class="fas fa-eye me-2"></i>View Association',
                            ['action' => 'view', $examsProcedure->id],
                            ['class' => 'btn btn-outline-info', 'escape' => false]
                        ) ?>
                        
                        <?php echo $this->Html->link(
                            '<i class="fas fa-plus me-2"></i>Add New Association',
                            ['action' => 'add'],
                            ['class' => 'btn btn-outline-success', 'escape' => false]
                        ) ?>
                        
                        <?php echo $this->Form->postLink(
                            '<i class="fas fa-trash me-2"></i>Delete Association',
                            ['action' => 'delete', $examsProcedure->id],
                            [
                                'class' => 'btn btn-outline-danger',
                                'escape' => false,
                                'confirm' => 'Are you sure you want to delete this association? This action cannot be undone.'
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

    // Auto-focus on exam selection
    const examField = document.querySelector('#exam-id');
    if (examField) {
        examField.focus();
    }
    
    // Add visual feedback for requirements checkboxes
    const requirementCheckboxes = document.querySelectorAll('input[type="checkbox"]');
    requirementCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.nextElementSibling;
            if (this.checked) {
                label.classList.add('text-warning', 'fw-bold');
            } else {
                label.classList.remove('text-warning', 'fw-bold');
            }
        });
    });
});
</script>