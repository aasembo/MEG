<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 * @var array $step1Data
 * @var array $step2Data
 * @var array $step3Data
 * @var \App\Model\Entity\User $patient
 * @var \App\Model\Entity\Department|null $department
 * @var \App\Model\Entity\Sedation|null $sedation
 * @var array $selectedExamsProcedures
 * @var array $aiRecommendations
 * @var object $currentHospital
 */
$this->assign('title', 'Edit Case #' . $case->id . ' - Step 3: Review & Notes');

// Priority labels and colors
$priorityColors = [
    'low' => 'success',
    'medium' => 'warning',
    'high' => 'danger',
    'urgent' => 'danger'
];
$priorityLabel = ucfirst($step2Data['priority'] ?? 'medium');
$priorityColor = $priorityColors[$step2Data['priority'] ?? 'medium'] ?? 'secondary';
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-edit me-2"></i>Edit Case #<?php echo h($case->id); ?>
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>Step 3: Review & Notes
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?= $this->Html->link(
                        '<i class="fas fa-eye me-2"></i>View Case',
                        ['action' => 'view', $case->id],
                        ['class' => 'btn btn-light', 'escape' => false]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Progress Steps -->
            <div class="card shadow-sm mb-4">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-success">Step 1</strong>
                                <div class="small text-muted">Patient Info</div>
                            </div>
                        </div>
                        <div class="flex-fill">
                            <hr class="border-2 border-success my-0">
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-success">Step 2</strong>
                                <div class="small text-muted">Department & Procedures</div>
                            </div>
                        </div>
                        <div class="flex-fill">
                            <hr class="border-2 border-primary my-0">
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-primary">Step 3</strong>
                                <div class="small text-muted">Review & Notes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Summary -->
            <div class="row mb-4">
                <!-- Patient Information -->
                <div class="col-lg-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0 fw-semibold">
                                <i class="fas fa-user-injured me-2 text-primary"></i>
                                Patient Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-sm-4">
                                    <small class="text-muted fw-semibold">Patient:</small>
                                </div>
                                <div class="col-sm-8">
                                    <div class="fw-medium"><?= $this->PatientMask->displayName($patient) ?></div>
                                </div>
                                
                                <div class="col-sm-4">
                                    <small class="text-muted fw-semibold">Case Date:</small>
                                </div>
                                <div class="col-sm-8">
                                    <div><?= h(date('M d, Y', strtotime($step1Data['date']))) ?></div>
                                </div>
                                
                                <div class="col-sm-4">
                                    <small class="text-muted fw-semibold">Symptoms:</small>
                                </div>
                                <div class="col-sm-8">
                                    <div class="small bg-light rounded p-2" style="max-height: 100px; overflow-y: auto;">
                                        <?= h($step1Data['symptoms']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Case Details -->
                <div class="col-lg-6 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0 fw-semibold">
                                <i class="fas fa-hospital me-2 text-primary"></i>
                                Case Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-sm-4">
                                    <small class="text-muted fw-semibold">Department:</small>
                                </div>
                                <div class="col-sm-8">
                                    <div class="fw-medium"><?= h($department->name ?? 'Not specified') ?></div>
                                </div>
                                
                                <div class="col-sm-4">
                                    <small class="text-muted fw-semibold">Sedation:</small>
                                </div>
                                <div class="col-sm-8">
                                    <div><?= $sedation ? h($sedation->level . ' (' . $sedation->type . ')') : 'None' ?></div>
                                </div>
                                
                                <div class="col-sm-4">
                                    <small class="text-muted fw-semibold">Priority:</small>
                                </div>
                                <div class="col-sm-8">
                                    <span class="badge bg-<?= $priorityColor ?> rounded-pill">
                                        <?= h($priorityLabel) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selected Exams/Procedures -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-procedures me-2 text-primary"></i>
                        Selected Exams & Procedures
                        <span class="badge bg-primary rounded-pill ms-2"><?= count($selectedExamsProcedures) ?></span>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($selectedExamsProcedures)): ?>
                        <div class="row g-3">
                            <?php foreach ($selectedExamsProcedures as $examProc): ?>
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 bg-light">
                                        <div class="fw-semibold text-primary">
                                            <?= h($examProc->exam->name ?? 'Unknown Exam') ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?= h($examProc->procedure->name ?? 'Unknown Procedure') ?>
                                        </div>
                                        <?php if (!empty($examProc->exam->modality->name)): ?>
                                            <div class="small">
                                                <span class="badge bg-secondary"><?= h($examProc->exam->modality->name) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-procedures fa-2x mb-2"></i>
                            <div>No procedures selected</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-notes-medical me-2 text-primary"></i>
                        Case Notes
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($aiRecommendations['notes'])): ?>
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-robot me-2 text-info"></i>
                                <strong class="text-info">AI-Recommended Notes</strong>
                            </div>
                            <div class="border rounded-3 p-3 bg-info bg-opacity-10">
                                <?= nl2br(h($aiRecommendations['notes'])) ?>
                            </div>
                            <div class="form-text mt-2">
                                <i class="fas fa-lightbulb me-1"></i>
                                These notes were automatically generated based on the patient's symptoms and selected procedures.
                            </div>
                        </div>
                    <?php endif; ?>

                    <form id="step3Form" novalidate>
                        <div class="mb-4">
                            <label for="technician_notes" class="form-label fw-semibold">
                                <i class="fas fa-user-md me-2 text-primary"></i>
                                Technician Notes
                            </label>
                            <textarea 
                                name="technician_notes" 
                                id="technician_notes" 
                                class="form-control form-control-lg" 
                                rows="6"
                                placeholder="Add any additional notes, observations, or special instructions for this case..."
                            ><?= h($step3Data['technician_notes'] ?? '') ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Add your own notes, observations, or special instructions for this case.
                            </div>
                        </div>

                        <div id="alertContainer"></div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <?= $this->Html->link(
                                '<i class="fas fa-arrow-left me-2"></i>Back to Step 2',
                                ['action' => 'editStep2', $case->id],
                                ['class' => 'btn btn-outline-secondary btn-lg', 'escape' => false]
                            ) ?>
                            
                            <button type="submit" class="btn btn-success btn-lg" id="updateCaseBtn">
                                <span id="btnText">
                                    <i class="fas fa-save me-2"></i>Update Case
                                </span>
                                <span id="btnLoading" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Updating...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information Alert -->
            <div class="alert alert-success border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-3 fa-lg"></i>
                    <div>
                        <h6 class="mb-1">Ready to Update</h6>
                        <div class="small mb-0">
                            Please review all information above. Once you click "Update Case", the changes will be saved to the system immediately.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('step3Form');
    const updateCaseBtn = document.getElementById('updateCaseBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    const alertContainer = document.getElementById('alertContainer');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        e.stopPropagation();

        alertContainer.innerHTML = '';

        // Confirm before updating
        if (!confirm('Are you sure you want to update this case? Please review all information before proceeding.')) {
            return;
        }

        // Disable button and show loading
        updateCaseBtn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');

        try {
            // Get form data
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Send AJAX request to save step 3 and update case
            const response = await fetch('<?= $this->Url->build(['action' => 'saveEditStep3', $case->id]) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $this->request->getAttribute('csrfToken') ?>'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                // Show success message
                alertContainer.innerHTML = `
                    <div class="alert alert-success border-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2 fa-lg"></i>
                            <div>
                                <strong>Success!</strong> Case has been updated successfully. Redirecting to case view...
                            </div>
                        </div>
                    </div>
                `;

                // Redirect to case view after short delay
                setTimeout(() => {
                    window.location.href = '<?= $this->Url->build(['action' => 'view']) ?>/' + result.case_id;
                }, 1500);
            } else {
                // Show errors
                let errorHtml = '<div class="alert alert-danger border-0" role="alert">';
                errorHtml += '<div class="d-flex align-items-start">';
                errorHtml += '<i class="fas fa-exclamation-circle me-2 fa-lg mt-1"></i>';
                errorHtml += '<div class="flex-grow-1">';
                errorHtml += '<h6 class="mb-1">Update Failed</h6>';
                errorHtml += '<ul class="mb-0">';
                
                if (result.errors) {
                    if (typeof result.errors === 'object') {
                        for (const [field, messages] of Object.entries(result.errors)) {
                            if (typeof messages === 'object') {
                                for (const [key, message] of Object.entries(messages)) {
                                    errorHtml += `<li>${field}: ${message}</li>`;
                                }
                            } else {
                                errorHtml += `<li>${field}: ${messages}</li>`;
                            }
                        }
                    } else {
                        errorHtml += `<li>${result.errors}</li>`;
                    }
                } else if (result.error) {
                    errorHtml += `<li>${result.error}</li>`;
                } else {
                    errorHtml += '<li>An error occurred while updating the case.</li>';
                }
                
                errorHtml += '</ul></div></div></div>';
                alertContainer.innerHTML = errorHtml;

                // Re-enable button
                updateCaseBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');

                // Scroll to errors
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } catch (error) {
            console.error('Error:', error);
            alertContainer.innerHTML = `
                <div class="alert alert-danger border-0" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2 fa-lg"></i>
                        <div>
                            <strong>Network Error:</strong> An error occurred while updating the case. Please check your connection and try again.
                        </div>
                    </div>
                </div>
            `;
            
            // Re-enable button
            updateCaseBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');

            // Scroll to errors
            alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
</script>
