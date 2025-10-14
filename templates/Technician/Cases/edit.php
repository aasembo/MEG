<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 */

$this->setLayout('technician');
$this->assign('title', 'Edit Case #' . $case->id);
?>

<div class="cases form content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><?php echo __('Edit Case #'); ?><?php echo h($case->id); ?></h2>
            <p class="text-muted mb-0">Update case information and procedures</p>
        </div>
        <div>
            <?php echo $this->Html->link(
                '<i class="fas fa-eye me-1"></i>' . __('View Case'),
                ['action' => 'view', $case->id],
                ['class' => 'btn btn-info', 'escape' => false]
            ); ?>
            <?php echo $this->Html->link(
                '<i class="fas fa-arrow-left me-1"></i>' . __('Back to Cases'),
                ['action' => 'index'],
                ['class' => 'btn btn-outline-secondary', 'escape' => false]
            ); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Case Information
                    </h5>
                </div>
                <div class="card-body">
                    <?php echo $this->Form->create($case, ['class' => 'needs-validation', 'novalidate' => true]); ?>
                    
                    <!-- Patient and Basic Info -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('patient_id', [
                                'type' => 'select',
                                'options' => $patients,
                                'empty' => 'Select a patient...',
                                'label' => 'Patient *',
                                'class' => 'form-select',
                                'required' => true
                            ]); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('date', [
                                'type' => 'date',
                                'label' => 'Case Date *',
                                'class' => 'form-control',
                                'required' => true
                            ]); ?>
                        </div>
                    </div>

                    <!-- Department and Sedation -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('department_id', [
                                'type' => 'select',
                                'options' => $departments,
                                'empty' => 'Select department...',
                                'label' => 'Department *',
                                'class' => 'form-select',
                                'required' => true
                            ]); ?>
                            <div class="form-text">Department handling this case</div>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('sedation_id', [
                                'type' => 'select',
                                'options' => $sedations,
                                'empty' => 'No sedation required',
                                'label' => 'Sedation Level',
                                'class' => 'form-select'
                            ]); ?>
                            <div class="form-text">Required sedation level</div>
                        </div>
                    </div>

                    <!-- Priority and Status -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('priority', [
                                'type' => 'select',
                                'options' => $priorities,
                                'label' => 'Priority *',
                                'class' => 'form-select',
                                'required' => true
                            ]); ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('status', [
                                'type' => 'select',
                                'options' => $statuses,
                                'label' => 'Status *',
                                'class' => 'form-select',
                                'required' => true
                            ]); ?>
                        </div>
                    </div>

                    <!-- Case Notes -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <?php echo $this->Form->control('notes', [
                                'type' => 'textarea',
                                'label' => 'Case Notes',
                                'class' => 'form-control',
                                'rows' => 4,
                                'placeholder' => 'Enter case notes, symptoms, observations...'
                            ]); ?>
                            <div class="form-text">Update case information and details</div>
                        </div>
                    </div>

                    <!-- Exam Procedures Management -->
                    <div class="card border-secondary mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-procedures me-2"></i>Manage Procedures
                                <small class="text-muted ms-2">(Check to assign, uncheck to remove)</small>
                            </h6>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($examsProcedures)): ?>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label class="form-label">Available Procedures</label>
                                        <div id="procedures-container">
                                            <div class="row">
                                                <?php foreach ($examsProcedures as $epId => $epName): ?>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-check">
                                                            <?php 
                                                            $isChecked = !empty($assignedExamProcedures) && in_array($epId, $assignedExamProcedures);
                                                            echo $this->Form->checkbox("exam_procedures.$epId", [
                                                                'class' => 'form-check-input',
                                                                'id' => "exam_procedure_$epId",
                                                                'checked' => $isChecked
                                                            ]); 
                                                            ?>
                                                            <label class="form-check-label" for="exam_procedure_<?php echo $epId; ?>">
                                                                <?php echo h($epName); ?>
                                                                <?php if ($isChecked): ?>
                                                                    <span class="badge bg-success ms-2">Currently Assigned</span>
                                                                <?php endif; ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Check procedures to assign them to this case. Uncheck to remove assigned procedures.
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Show currently assigned procedures count -->
                                <?php if (!empty($assignedExamProcedures)): ?>
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-procedures me-2"></i>
                                        Currently <strong><?php echo count($assignedExamProcedures); ?></strong> procedure(s) assigned to this case.
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        No procedures assigned to this case yet. Select procedures above to assign them.
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No procedures available for this hospital.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between pt-3 border-top">
                        <?php echo $this->Form->button(__('Update Case'), [
                            'type' => 'submit',
                            'class' => 'btn btn-primary px-4'
                        ]); ?>
                        
                        <?php echo $this->Html->link(
                            __('Cancel'),
                            ['action' => 'view', $case->id],
                            ['class' => 'btn btn-outline-secondary px-4']
                        ); ?>
                    </div>

                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Current Case Info -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-1"></i> Current Case Info</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Status</h6>
                        <span class="badge bg-<?php 
                            echo match($case->status) {
                                'draft' => 'secondary',
                                'assigned' => 'info',
                                'in_progress' => 'warning',
                                'review' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            };
                        ?>">
                            <?php echo h(ucfirst(str_replace('_', ' ', $case->status))); ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <h6>Department</h6>
                        <p class="small mb-0">
                            <?php if ($case->department): ?>
                                <i class="fas fa-building me-1"></i><?php echo h($case->department->name); ?>
                            <?php else: ?>
                                <span class="text-muted">Not assigned</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6>Sedation</h6>
                        <p class="small mb-0">
                            <?php if ($case->sedation): ?>
                                <i class="fas fa-pills me-1"></i><?php echo h($case->sedation->level); ?>
                                <?php if ($case->sedation->type): ?>
                                    <span class="text-muted">(<?php echo h($case->sedation->type); ?>)</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">None required</span>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6>Procedures</h6>
                        <p class="small mb-0">
                            <i class="fas fa-procedures me-1"></i>
                            <?php echo count($case->cases_exams_procedures ?? []); ?> assigned
                        </p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-1"></i> Edit Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Status Changes</h6>
                        <p class="small text-muted">
                            Only draft and assigned cases can be edited. Status changes will create a new version.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6>Version Control</h6>
                        <p class="small text-muted">
                            All changes are tracked and will create a new case version with audit trail.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6>Procedure Changes</h6>
                        <p class="small text-muted">
                            You can add new procedures but cannot remove existing ones once assigned.
                        </p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-history me-1"></i> Case History</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Created:</span>
                            <strong><?php echo $case->created->format('M j, Y'); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Last Modified:</span>
                            <strong><?php echo $case->modified->format('M j, Y'); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Current User:</span>
                            <strong>
                                <?php if ($case->current_user): ?>
                                    <?php echo h($case->current_user->first_name . ' ' . $case->current_user->last_name); ?>
                                <?php else: ?>
                                    Unassigned
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

// Dynamic procedure selection feedback
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('#additional-procedures-container input[type="checkbox"]');
    
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const selectedCount = document.querySelectorAll('#additional-procedures-container input[type="checkbox"]:checked').length;
            const feedback = document.getElementById('additional-procedure-feedback');
            
            if (!feedback && selectedCount > 0) {
                const feedbackDiv = document.createElement('div');
                feedbackDiv.id = 'additional-procedure-feedback';
                feedbackDiv.className = 'alert alert-success mt-2';
                feedbackDiv.innerHTML = `<i class="fas fa-plus me-2"></i>${selectedCount} additional procedure(s) will be added to this case.`;
                document.getElementById('additional-procedures-container').appendChild(feedbackDiv);
            } else if (feedback) {
                if (selectedCount > 0) {
                    feedback.innerHTML = `<i class="fas fa-plus me-2"></i>${selectedCount} additional procedure(s) will be added to this case.`;
                    feedback.className = 'alert alert-success mt-2';
                } else {
                    feedback.remove();
                }
            }
        });
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
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}
</style>