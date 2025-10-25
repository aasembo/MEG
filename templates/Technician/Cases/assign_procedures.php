<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 * @var array $examsProcedures
 * @var array $assignedExamProcedures
 * @var array $proceduresByModality
 * @var object $currentHospital
 */
?>

<div class="cases-assign-procedures">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h2><i class="fas fa-procedures me-2"></i>Assign Procedures</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><?php echo $this->Html->link('Dashboard', ['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index']); ?></li>
                            <li class="breadcrumb-item"><?php echo $this->Html->link('Cases', ['action' => 'index']); ?></li>
                            <li class="breadcrumb-item"><?php echo $this->Html->link('Case #' . $case->id, ['action' => 'view', $case->id]); ?></li>
                            <li class="breadcrumb-item active" aria-current="page">Assign Procedures</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-left me-1"></i> Back to Case',
                        ['action' => 'view', $case->id],
                        ['class' => 'btn btn-outline-secondary', 'escape' => false]
                    ); ?>
                </div>
            </div>

            <!-- Case Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-1"></i> Case Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Patient:</strong> <?php echo h($case->patient_user->first_name . ' ' . $case->patient_user->last_name); ?><br>
                            <strong>Case ID:</strong> #<?php echo h($case->id); ?><br>
                            <strong>Status:</strong> 
                            <span class="badge <?php echo $case->getStatusClass(); ?>">
                                <?php echo h($case->getStatusLabel()); ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Priority:</strong> 
                            <span class="badge <?php echo $case->getPriorityClass(); ?>">
                                <?php echo h($case->getPriorityLabel()); ?>
                            </span><br>
                            <strong>Date:</strong> <?php echo h($case->date); ?><br>
                            <strong>Department:</strong> <?php echo h($case->department->name ?? 'Not specified'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Procedures Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-1"></i> Current Procedures Summary</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($case->cases_exams_procedures)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong><?php echo count($case->cases_exams_procedures); ?> procedures currently assigned</strong>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Exam</th>
                                        <th>Procedure</th>
                                        <th>Modality</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($case->cases_exams_procedures as $cep): ?>
                                        <tr>
                                            <td><?php echo h($cep->exams_procedure->exam->name ?? 'N/A'); ?></td>
                                            <td><?php echo h($cep->exams_procedure->procedure->name ?? 'N/A'); ?></td>
                                            <td>
                                                <?php if ($cep->exams_procedure->exam->modality ?? null): ?>
                                                    <span class="badge bg-secondary">
                                                        <?php echo h($cep->exams_procedure->exam->modality->name); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $cep->getStatusBadgeClass(); ?>">
                                                    <?php echo h($cep->getStatusLabel()); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>No procedures currently assigned</strong><br>
                            Please select procedures below to assign to this case.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Procedure Assignment Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tasks me-1"></i> Available Procedures</h5>
                </div>
                <div class="card-body">
                    <?php echo $this->Form->create(null, ['novalidate' => true, 'class' => 'needs-validation']); ?>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Instructions:</strong> Check the procedures you want to assign to this case. 
                        Unchecking will remove the procedure from the case.
                    </div>

                    <?php if (!empty($proceduresByModality)): ?>
                        <?php foreach ($proceduresByModality as $modalityName => $procedures): ?>
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-stethoscope me-1"></i>
                                        <?php echo h($modalityName); ?> Procedures
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <?php foreach ($procedures as $procedure): ?>
                                            <div class="col-lg-6 col-md-12 mb-2">
                                                <div class="form-check">
                                                    <?php echo $this->Form->checkbox("exam_procedures.{$procedure->id}", [
                                                        'value' => 1,
                                                        'checked' => in_array($procedure->id, $assignedExamProcedures),
                                                        'class' => 'form-check-input procedure-checkbox',
                                                        'id' => "procedure_{$procedure->id}"
                                                    ]); ?>
                                                    <label class="form-check-label" for="procedure_<?php echo $procedure->id; ?>">
                                                        <strong><?php echo h($procedure->exam->name); ?></strong>
                                                        <br><small class="text-muted">
                                                            <?php echo h($procedure->procedure->name); ?>
                                                            <?php if ($procedure->procedure->description): ?>
                                                                - <?php echo h($procedure->procedure->description); ?>
                                                            <?php endif; ?>
                                                        </small>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>No procedures available</strong><br>
                            There are no exam procedures configured for this hospital. Please contact your administrator.
                        </div>
                    <?php endif; ?>

                    <!-- Selection Summary -->
                    <div id="selection-summary" class="mt-3"></div>

                    <!-- Submit Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-times me-1"></i> Cancel',
                                    ['action' => 'view', $case->id],
                                    ['class' => 'btn btn-outline-secondary', 'escape' => false]
                                ); ?>
                                
                                <div>
                                    <?php echo $this->Form->button(
                                        '<i class="fas fa-save me-1"></i> Update Procedures',
                                        ['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false]
                                    ); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php echo $this->Form->end(); ?>
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

// Dynamic procedure selection tracking
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.procedure-checkbox');
    const summaryDiv = document.getElementById('selection-summary');
    
    function updateSelectionSummary() {
        const checkedBoxes = document.querySelectorAll('.procedure-checkbox:checked');
        const selectedCount = checkedBoxes.length;
        
        if (selectedCount > 0) {
            summaryDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="fas fa-check me-2"></i>
                    <strong>${selectedCount} procedure(s) selected</strong>
                    <br>These procedures will be assigned to the case when you click "Update Procedures".
                </div>
            `;
        } else {
            summaryDiv.innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>No procedures selected</strong>
                    <br>All procedures will be removed from this case if you proceed.
                </div>
            `;
        }
    }
    
    // Initial summary
    updateSelectionSummary();
    
    // Update summary when checkboxes change
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updateSelectionSummary);
    });
});
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.form-check {
    padding: 0.75rem;
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
    transition: all 0.2s;
}

.form-check:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.form-check-input:checked ~ .form-check-label {
    color: #0d6efd;
    font-weight: 500;
}

.badge {
    font-size: 0.75em;
}

.table th {
    background-color: #f8f9fa;
    border-top: none;
    font-weight: 600;
}
</style>