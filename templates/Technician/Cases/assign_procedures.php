<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 * @var array $examsProcedures
 * @var array $assignedExamProcedures
 * @var array $proceduresByModality
 * @var object $currentHospital
 */

$this->assign('title', 'Assign Procedures - Case #' . $case->id);
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-tasks me-2"></i>Assign Procedures
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-file-medical me-2"></i>Case #<?php echo h($case->id); ?>
                        <?php if ($case->patient_user): ?>
                            - <?php echo $this->PatientMask->displayName($case->patient_user); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-eye me-1"></i>View Case',
                            ['action' => 'view', $case->id],
                            ['class' => 'btn btn-light', 'escape' => false]
                        ); ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-arrow-left me-1"></i>Back',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-light', 'escape' => false]
                        ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Case Information Card -->
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light border-0 py-3">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-file-medical me-2 text-primary"></i>Case Information
            </h5>
        </div>
        <div class="card-body bg-white">
            <div class="row">
                <div class="col-md-3">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="fw-semibold text-muted">Patient:</td>
                            <td>
                                <?php if ($case->patient_user): ?>
                                    <?php echo $this->PatientMask->displayName($case->patient_user); ?>
                                    <br><small class="text-muted">ID: <?php echo h($case->patient_user->id); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">No patient assigned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-3">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="fw-semibold text-muted">Department:</td>
                            <td>
                                <?php if ($case->department): ?>
                                    <i class="fas fa-building me-1 text-primary"></i>
                                    <?php echo h($case->department->name); ?>
                                <?php else: ?>
                                    <span class="text-muted">Not assigned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-3">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="fw-semibold text-muted">Priority:</td>
                            <td><?php echo $this->Status->priorityBadge($case->priority); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-3">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="fw-semibold text-muted">Case Date:</td>
                            <td><?php echo $case->date ? $case->date->format('M j, Y') : '<span class="text-muted">Not set</span>'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Current Procedures -->
        <div class="col-lg-4">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-list me-2 text-primary"></i>Currently Assigned
                    </h5>
                </div>
                <div class="card-body bg-white" style="max-height: 600px; overflow-y: auto;">
                    <?php if (!empty($case->cases_exams_procedures)): ?>
                        <div class="alert alert-info border-0 mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong><?php echo count($case->cases_exams_procedures); ?> procedures assigned</strong>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($case->cases_exams_procedures as $cep): ?>
                                <div class="list-group-item border-0 px-0 py-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-semibold">
                                                <?php echo h($cep->exams_procedure->exam->name ?? 'N/A'); ?>
                                            </h6>
                                            <p class="mb-2 text-muted small">
                                                <?php echo h($cep->exams_procedure->procedure->name ?? 'N/A'); ?>
                                            </p>
                                            <?php if ($cep->exams_procedure->exam->modality ?? null): ?>
                                                <span class="badge rounded-pill bg-info text-white">
                                                    <i class="fas fa-microscope me-1"></i>
                                                    <?php echo h($cep->exams_procedure->exam->modality->name); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge rounded-pill <?php echo $cep->getStatusBadgeClass(); ?> ms-2">
                                            <?php echo h($cep->getStatusLabel()); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning border-0 mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>No procedures assigned</strong><br>
                            Select procedures from the available options to assign them to this case.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Available Procedures -->
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-header bg-light border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-stethoscope me-2 text-primary"></i>Available Procedures
                        </h5>
                        <div id="selection-counter" class="badge rounded-pill bg-primary fs-6"></div>
                    </div>
                </div>
                <div class="card-body bg-white" style="max-height: 600px; overflow-y: auto;">
                    <?php echo $this->Form->create(null, ['novalidate' => true]); ?>
                    
                    <div class="alert alert-info border-0 mb-4">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Instructions:</strong> Check the procedures you want to assign to this case. 
                        Unchecking will remove the procedure from the case.
                    </div>

                    <?php if (!empty($proceduresByModality)): ?>
                        <?php foreach ($proceduresByModality as $modalityName => $procedures): ?>
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                                    <i class="fas fa-microscope me-2 text-primary"></i>
                                    <?php echo h($modalityName); ?> Procedures
                                </h6>
                                <div class="row g-3">
                                    <?php foreach ($procedures as $procedure): ?>
                                        <div class="col-md-6">
                                            <div class="card h-100 procedure-card border" data-procedure-id="<?php echo $procedure->id; ?>">
                                                <div class="card-body p-3">
                                                    <div class="form-check">
                                                        <?php echo $this->Form->checkbox("exam_procedures.{$procedure->id}", [
                                                            'value' => 1,
                                                            'checked' => in_array($procedure->id, $assignedExamProcedures),
                                                            'class' => 'form-check-input procedure-checkbox',
                                                            'id' => "procedure_{$procedure->id}"
                                                        ]); ?>
                                                        <label class="form-check-label w-100 cursor-pointer" for="procedure_<?php echo $procedure->id; ?>">
                                                            <div class="fw-semibold text-dark mb-1">
                                                                <?php echo h($procedure->exam->name); ?>
                                                            </div>
                                                            <div class="text-muted small mb-2">
                                                                <?php echo h($procedure->procedure->name); ?>
                                                                <?php if ($procedure->procedure->description): ?>
                                                                    <br><span class="text-muted"><?php echo h($procedure->procedure->description); ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <span class="badge rounded-pill bg-secondary">
                                                                <?php echo h($procedure->exam->modality->name ?? 'N/A'); ?>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning border-0 mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>No procedures available</strong><br>
                            There are no exam procedures configured for this hospital. Please contact your administrator.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light border-0">
                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-times me-1"></i> Cancel',
                            ['action' => 'view', $case->id],
                            ['class' => 'btn btn-outline-secondary', 'escape' => false]
                        ); ?>
                        
                        <?php echo $this->Form->button(
                            '<i class="fas fa-save me-1"></i> Update Procedures',
                            ['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false]
                        ); ?>
                    </div>

                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('.procedure-checkbox');
    const selectionCounter = document.getElementById('selection-counter');
    
    function updateSelectionCounter() {
        const checkedBoxes = document.querySelectorAll('.procedure-checkbox:checked');
        const selectedCount = checkedBoxes.length;
        
        if (selectedCount > 0) {
            selectionCounter.textContent = `${selectedCount} selected`;
            selectionCounter.className = 'badge rounded-pill bg-success fs-6';
        } else {
            selectionCounter.textContent = 'None selected';
            selectionCounter.className = 'badge rounded-pill bg-secondary fs-6';
        }
    }
    
    function updateProcedureCardStyle(checkbox) {
        const card = checkbox.closest('.procedure-card');
        if (checkbox.checked) {
            card.classList.remove('border');
            card.classList.add('border-success', 'bg-light');
        } else {
            card.classList.remove('border-success', 'bg-light');
            card.classList.add('border');
        }
    }
    
    // Initialize
    updateSelectionCounter();
    
    checkboxes.forEach(function(checkbox) {
        // Set initial card styles
        updateProcedureCardStyle(checkbox);
        
        // Add change event listener
        checkbox.addEventListener('change', function() {
            updateSelectionCounter();
            updateProcedureCardStyle(this);
        });
    });
    
    // Add click handler to cards for better UX
    document.querySelectorAll('.procedure-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox' && e.target.tagName !== 'LABEL') {
                const checkbox = this.querySelector('.procedure-checkbox');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            }
        });
        
        // Add cursor pointer
        card.style.cursor = 'pointer';
    });
});
</script>