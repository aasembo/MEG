<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 */

$this->setLayout('doctor');
$this->assign('title', 'Edit Case #' . $case->id);
?>

<div class="cases form content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><?php echo __('Edit Case #'); ?><?php echo h($case->id); ?></h2>
            <p class="text-muted mb-0">Update medical findings and recommendations</p>
        </div>
        <div>
            <?php echo $this->Html->link(
                '<i class="fas fa-eye me-1"></i>' . __('View Case'),
                array('action' => 'view', $case->id),
                array('class' => 'btn btn-info', 'escape' => false)
            ); ?>
            <?php echo $this->Html->link(
                '<i class="fas fa-arrow-left me-1"></i>' . __('Back to Cases'),
                array('action' => 'index'),
                array('class' => 'btn btn-outline-secondary', 'escape' => false)
            ); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-stethoscope me-2"></i>Medical Information
                    </h5>
                </div>
                <div class="card-body">
                    <?php echo $this->Form->create($case, array('class' => 'needs-validation', 'novalidate' => true)); ?>
                    
                    <!-- Priority Field (Editable) -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('priority', array(
                                'type' => 'select',
                                'options' => $priorities,
                                'label' => 'Priority Level *',
                                'class' => 'form-select',
                                'required' => true
                            )); ?>
                            <div class="form-text">Set case priority level</div>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('doctor_status', array(
                                'type' => 'select',
                                'options' => array(
                                    'assigned' => 'Assigned',
                                    'in_progress' => 'In Progress',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled'
                                ),
                                'label' => 'Doctor Status *',
                                'class' => 'form-select',
                                'required' => true
                            )); ?>
                            <div class="form-text">Update your role status</div>
                        </div>
                    </div>

                    <!-- Medical Findings (Editable) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-notes-medical me-2 text-primary"></i>Medical Finding
                            </label>
                            <?php echo $this->Form->control('finding', array(
                                'type' => 'textarea',
                                'label' => false,
                                'class' => 'form-control',
                                'rows' => 6,
                                'placeholder' => 'Enter detailed medical findings from examination and analysis...'
                            )); ?>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Document your clinical findings, observations, and diagnostic impressions
                            </div>
                        </div>
                    </div>

                    <!-- Recommendations (Editable) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-clipboard-check me-2 text-success"></i>Recommendations
                            </label>
                            <?php echo $this->Form->control('recommendations', array(
                                'type' => 'textarea',
                                'label' => false,
                                'class' => 'form-control',
                                'rows' => 6,
                                'placeholder' => 'Provide treatment recommendations, follow-up care, and additional tests needed...'
                            )); ?>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Include treatment plans, medication suggestions, and follow-up instructions
                            </div>
                        </div>
                    </div>

                    <!-- Remarks (Editable) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-comment-medical me-2 text-info"></i>Additional Remarks
                            </label>
                            <?php echo $this->Form->control('remarks', array(
                                'type' => 'textarea',
                                'label' => false,
                                'class' => 'form-control',
                                'rows' => 4,
                                'placeholder' => 'Any additional notes, observations, or special considerations...'
                            )); ?>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Add any supplementary information or special notes relevant to the case
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between pt-3 border-top">
                        <?php echo $this->Form->button(__('Update Case'), array(
                            'type' => 'submit',
                            'class' => 'btn btn-primary px-4'
                        )); ?>
                        
                        <?php echo $this->Html->link(
                            __('Cancel'),
                            array('action' => 'view', $case->id),
                            array('class' => 'btn btn-outline-secondary px-4')
                        ); ?>
                    </div>

                    <?php echo $this->Form->end(); ?>
                </div>
            </div>

            <!-- Read-Only Case Information -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-lock me-2 text-muted"></i>Read-Only Case Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> You can only edit medical findings, recommendations, remarks, and priority.
                        Other case details can only be modified by technicians or scientists.
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-semibold">Patient:</td>
                                    <td>
                                        <?php if ($case->patient_user): ?>
                                            <?php echo h($case->patient_user->first_name . ' ' . $case->patient_user->last_name); ?>
                                            <br><small class="text-muted">ID: <?php echo h($case->patient_user->id); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Department:</td>
                                    <td>
                                        <?php if ($case->department): ?>
                                            <i class="fas fa-building me-1 text-primary"></i>
                                            <?php echo h($case->department->name); ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-semibold">Hospital:</td>
                                    <td>
                                        <?php if ($case->hospital): ?>
                                            <i class="fas fa-hospital me-1 text-success"></i>
                                            <?php echo h($case->hospital->name); ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Sedation:</td>
                                    <td>
                                        <?php if ($case->sedation): ?>
                                            <i class="fas fa-syringe me-1 text-info"></i>
                                            <?php echo h($case->sedation->name); ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Current Case Status -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-1"></i> Current Case Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Global Status</h6>
                        <?php echo $this->Status->globalBadge($case); ?>
                    </div>

                    <div class="mb-3">
                        <h6>Workflow Status</h6>
                        <div class="small">
                            <div class="mb-2">
                                <?php echo $this->Status->roleBadge($case, 'technician', $user); ?>
                            </div>
                            <div class="mb-2">
                                <?php echo $this->Status->roleBadge($case, 'scientist', $user); ?>
                            </div>
                            <div class="mb-2">
                                <?php echo $this->Status->roleBadge($case, 'doctor', $user); ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <h6>Priority</h6>
                        <?php echo $this->Status->priorityBadge($case->priority); ?>
                    </div>

                    <div class="mb-3">
                        <h6>Current User</h6>
                        <p class="small mb-0">
                            <?php if ($case->current_user): ?>
                                <?php if ($case->current_user->id === $user->id): ?>
                                    <i class="fas fa-user-check me-1 text-primary"></i>
                                    <span class="text-primary fw-bold">You</span>
                                <?php else: ?>
                                    <i class="fas fa-user me-1"></i>
                                    <?php echo h($case->current_user->first_name . ' ' . $case->current_user->last_name); ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Unassigned</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Doctor Guidelines -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-user-doctor me-1"></i> Doctor Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Editable Fields</h6>
                        <ul class="small text-muted mb-0">
                            <li>Priority Level</li>
                            <li>Doctor Status</li>
                            <li>Medical Finding</li>
                            <li>Recommendations</li>
                            <li>Additional Remarks</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h6>Restricted Actions</h6>
                        <ul class="small text-muted mb-0">
                            <li>Cannot change patient</li>
                            <li>Cannot modify department</li>
                            <li>Cannot change sedation</li>
                            <li>Cannot assign to others</li>
                            <li>Cannot delete case</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h6>Version Control</h6>
                        <p class="small text-muted">
                            All changes are tracked and create audit entries. Your modifications will be recorded with timestamp.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Procedures Information -->
            <?php if (!empty($case->cases_exams_procedures)): ?>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-procedures me-1"></i> Assigned Procedures</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">
                        This case has <?php echo count($case->cases_exams_procedures); ?> procedure(s) assigned:
                    </p>
                    <ul class="list-unstyled small mb-0">
                        <?php foreach ($case->cases_exams_procedures as $cep): ?>
                            <li class="mb-1">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                <?php if ($cep->exams_procedure && $cep->exams_procedure->exam): ?>
                                    <?php echo h($cep->exams_procedure->exam->name); ?>
                                <?php elseif ($cep->exams_procedure && $cep->exams_procedure->procedure): ?>
                                    <?php echo h($cep->exams_procedure->procedure->name); ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <!-- Case Timeline -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-history me-1"></i> Case Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Created:</span>
                            <strong><?php echo $case->created ? $case->created->format('M j, Y') : 'N/A'; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Last Modified:</span>
                            <strong><?php echo $case->modified ? $case->modified->format('M j, Y') : 'N/A'; ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Created By:</span>
                            <strong>
                                <?php if ($case->user): ?>
                                    <?php if ($case->user->id === $user->id): ?>
                                        <span class="text-primary">You</span>
                                    <?php else: ?>
                                        <?php echo h($case->user->first_name . ' ' . $case->user->last_name); ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </strong>
                        </div>
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
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Character counter for textareas
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('textarea');
    
    textareas.forEach(function(textarea) {
        const maxLength = textarea.getAttribute('maxlength');
        if (maxLength) {
            const counter = document.createElement('div');
            counter.className = 'text-muted small mt-1';
            counter.innerHTML = `<i class="fas fa-info-circle me-1"></i>Characters: <span class="char-count">0</span> / ${maxLength}`;
            textarea.parentNode.appendChild(counter);
            
            textarea.addEventListener('input', function() {
                const count = textarea.value.length;
                const countSpan = counter.querySelector('.char-count');
                countSpan.textContent = count;
                
                if (count > maxLength * 0.9) {
                    counter.classList.add('text-warning');
                    counter.classList.remove('text-muted');
                } else {
                    counter.classList.add('text-muted');
                    counter.classList.remove('text-warning');
                }
            });
        }
    });
});
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.form-control:focus,
.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

textarea {
    resize: vertical;
    min-height: 100px;
}

.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

.table-borderless td,
.table-borderless th {
    border: none;
    padding: 0.5rem 0.25rem;
}

.alert {
    border-radius: 0.375rem;
}
</style>
