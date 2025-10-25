<?php
/**
 * @var \App\View\AppView $this
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
$this->assign('title', 'Add New Case - Step 3: Review & Notes');

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

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Progress Steps -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-check fa-lg"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-success">Step 1</strong>
                                <div class="small text-muted">Patient Info</div>
                            </div>
                        </div>
                        <div class="flex-fill">
                            <hr class="border-2 border-success">
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-check fa-lg"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-success">Step 2</strong>
                                <div class="small text-muted">Department & Procedures</div>
                            </div>
                        </div>
                        <div class="flex-fill">
                            <hr class="border-2 border-success">
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-clipboard-check fa-lg"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-primary">Step 3</strong>
                                <div class="small text-muted">Review & Notes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Review Summary Cards -->
            <div class="row mb-4">
                <!-- Patient Information -->
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-user-injured me-2"></i>
                                Patient Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Patient:</dt>
                                <dd class="col-sm-8"><?= h($patient->first_name . ' ' . $patient->last_name) ?></dd>
                                
                                <dt class="col-sm-4">Case Date:</dt>
                                <dd class="col-sm-8"><?= h(date('M d, Y', strtotime($step1Data['date']))) ?></dd>
                                
                                <dt class="col-sm-4">Symptoms:</dt>
                                <dd class="col-sm-8">
                                    <div class="small text-muted" style="max-height: 100px; overflow-y: auto;">
                                        <?= h($step1Data['symptoms']) ?>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Case Details -->
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-hospital me-2"></i>
                                Case Details
                            </h6>
                        </div>
                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-4">Department:</dt>
                                <dd class="col-sm-8"><?= h($department->name ?? 'Not specified') ?></dd>
                                
                                <dt class="col-sm-4">Sedation:</dt>
                                <dd class="col-sm-8"><?= $sedation ? h($sedation->level . ' (' . $sedation->type . ')') : 'None' ?></dd>
                                
                                <dt class="col-sm-4">Priority:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-<?= $priorityColor ?>">
                                        <?= h($priorityLabel) ?>
                                    </span>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selected Exams/Procedures -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-procedures me-2"></i>
                        Selected Exams & Procedures (<?= count($selectedExamsProcedures) ?>)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($selectedExamsProcedures as $examProc): ?>
                            <div class="col-md-6 mb-2">
                                <div class="border rounded p-2 bg-light">
                                    <strong><?= h($examProc->exam->name ?? 'Unknown Exam') ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        <?= h($examProc->procedure->name ?? 'Unknown Procedure') ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Notes Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-notes-medical me-2"></i>
                        Case Notes
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($aiRecommendations['notes'])): ?>
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-robot me-2 text-info"></i>
                            <strong>AI-Recommended Notes</strong>
                        </label>
                        <div class="border rounded p-3 bg-light">
                            <?= nl2br(h($aiRecommendations['notes'])) ?>
                        </div>
                        <div class="form-text">
                            These notes were automatically generated based on the patient's symptoms and selected procedures.
                        </div>
                    </div>
                    <?php endif; ?>

                    <form id="step3Form" novalidate>
                        <div class="mb-3">
                            <label for="technician_notes" class="form-label">
                                <i class="fas fa-user-md me-2"></i>
                                <strong>Technician Notes</strong>
                            </label>
                            <textarea 
                                name="technician_notes" 
                                id="technician_notes" 
                                class="form-control" 
                                rows="6"
                                placeholder="Add any additional notes or observations..."
                            ><?= h($step3Data['technician_notes'] ?? '') ?></textarea>
                            <div class="form-text">
                                Add your own notes, observations, or special instructions for this case.
                            </div>
                        </div>

                        <div id="alertContainer"></div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <?= $this->Html->link(
                                '<i class="fas fa-arrow-left me-2"></i>Back',
                                ['action' => 'addStep2'],
                                ['class' => 'btn btn-outline-secondary btn-lg', 'escape' => false]
                            ) ?>
                            
                            <button type="submit" class="btn btn-success btn-lg" id="createCaseBtn">
                                <span id="btnText">
                                    <i class="fas fa-check me-2"></i>Create Case
                                </span>
                                <span id="btnLoading" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Creating Case...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information Card -->
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <h6 class="card-title text-success">
                        <i class="fas fa-info-circle me-2"></i>Ready to Submit
                    </h6>
                    <p class="card-text small mb-0">
                        Please review all information above. Once you click "Create Case", the case will be saved to the system
                        <?php if (!empty($aiRecommendations['ai_generated'])): ?>
                        with AI-assisted recommendations
                        <?php endif; ?>
                        and will be ready for assignment.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('step3Form');
    const createCaseBtn = document.getElementById('createCaseBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    const alertContainer = document.getElementById('alertContainer');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        e.stopPropagation();

        alertContainer.innerHTML = '';

        // Confirm before creating
        if (!confirm('Are you sure you want to create this case? Please review all information before proceeding.')) {
            return;
        }

        // Disable button and show loading
        createCaseBtn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');

        try {
            // Get form data
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Send AJAX request to save step 3 and create case
            const response = await fetch('<?= $this->Url->build(['action' => 'saveStep3']) ?>', {
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
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Success!</strong> Case has been created successfully. Redirecting...
                    </div>
                `;

                // Redirect to case view after short delay
                setTimeout(() => {
                    window.location.href = '<?= $this->Url->build(['action' => 'view']) ?>/' + result.case_id;
                }, 1500);
            } else {
                // Show errors
                let errorHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><ul class="mb-0">';
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
                    errorHtml += '<li>An error occurred while creating the case.</li>';
                }
                errorHtml += '</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                alertContainer.innerHTML = errorHtml;

                // Re-enable button
                createCaseBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');

                // Scroll to errors
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } catch (error) {
            console.error('Error:', error);
            alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    An error occurred while creating the case. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Re-enable button
            createCaseBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');

            // Scroll to errors
            alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
</script>
