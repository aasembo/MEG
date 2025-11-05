<?php
/**
 * @var \App\View\AppView $this
 * @var array $step1Data
 * @var array $step2Data
 * @var array $departments
 * @var array $sedations
 * @var array $examsProcedures
 * @var array $aiRecommendations
 * @var object $currentHospital
 */
$this->assign('title', 'Add New Case - Step 2: Department & Procedures');
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-plus-circle me-2"></i>Add New Case
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-procedures me-2"></i>Step 2: Department & Procedures
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?= $this->Html->link(
                        '<i class="fas fa-list me-2"></i>All Cases',
                        ['action' => 'index'],
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
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas fa-procedures"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-primary">Step 2</strong>
                                <div class="small text-muted">Department & Procedures</div>
                            </div>
                        </div>
                        <div class="flex-fill">
                            <hr class="border-2 my-0">
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-muted">Step 3</strong>
                                <div class="small text-muted">Review & Notes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-user-injured me-2 text-primary"></i>
                        Patient Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <small class="text-muted fw-semibold">Patient:</small>
                            <div class="fw-medium"><?= h($step1Data['patient_name'] ?? 'Not selected') ?></div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted fw-semibold">Case Date:</small>
                            <div><?= h(date('M d, Y', strtotime($step1Data['date'] ?? 'now'))) ?></div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted fw-semibold">Symptoms:</small>
                            <div class="small text-truncate" style="max-width: 200px;" title="<?= h($step1Data['symptoms'] ?? '') ?>">
                                <?= h(substr($step1Data['symptoms'] ?? '', 0, 50)) ?><?= strlen($step1Data['symptoms'] ?? '') > 50 ? '...' : '' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Recommendations -->
            <?php if (!empty($aiRecommendations)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-robot me-2 text-info"></i>
                        AI Recommendations
                    </h6>
                </div>
                <div class="card-body">
                    <div class="bg-info bg-opacity-10 border border-info border-opacity-25 rounded-3 p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-lightbulb me-2 text-info"></i>
                            <strong class="text-info">Recommended Based on Symptoms</strong>
                        </div>
                        <?php if (!empty($aiRecommendations['procedures'])): ?>
                            <div class="mb-2">
                                <strong>Procedures:</strong>
                                <div class="mt-1">
                                    <?php foreach ($aiRecommendations['procedures'] as $proc): ?>
                                        <span class="badge bg-info me-1"><?= h($proc) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($aiRecommendations['department'])): ?>
                            <div>
                                <strong>Department:</strong> 
                                <span class="badge bg-primary"><?= h($aiRecommendations['department']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Department & Case Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-hospital me-2 text-primary"></i>
                        Department & Case Details
                    </h6>
                </div>
                <div class="card-body">
                    <form id="step2Form" novalidate>
                        <div class="row g-4">
                            <!-- Department Selection -->
                            <div class="col-md-6">
                                <label for="department_id" class="form-label fw-semibold">
                                    <i class="fas fa-building me-2 text-primary"></i>
                                    Department
                                </label>
                                <select name="department_id" id="department_id" class="form-select form-select-lg" required>
                                    <option value="">Select Department...</option>
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?= $department->id ?>" 
                                            data-description="<?= h($department->description ?? '') ?>"
                                            <?= (isset($step2Data['department_id']) && $step2Data['department_id'] == $department->id) ? 'selected' : '' ?>>
                                            <?= h($department->name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a department.
                                </div>
                                <div id="departmentDescription" class="form-text"></div>
                            </div>

                            <!-- Sedation Selection -->
                            <div class="col-md-6">
                                <label for="sedation_id" class="form-label fw-semibold">
                                    <i class="fas fa-pills me-2 text-primary"></i>
                                    Sedation Level
                                </label>
                                <select name="sedation_id" id="sedation_id" class="form-select form-select-lg">
                                    <option value="">No Sedation Required</option>
                                    <?php foreach ($sedations as $sedation): ?>
                                        <option value="<?= $sedation->id ?>" 
                                            data-type="<?= h($sedation->type ?? '') ?>"
                                            data-risk="<?= h($sedation->risk_category ?? '') ?>"
                                            <?= (isset($step2Data['sedation_id']) && $step2Data['sedation_id'] == $sedation->id) ? 'selected' : '' ?>>
                                            <?= h($sedation->level) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="sedationInfo" class="form-text"></div>
                            </div>

                            <!-- Priority Level -->
                            <div class="col-md-6">
                                <label for="priority" class="form-label fw-semibold">
                                    <i class="fas fa-exclamation-circle me-2 text-primary"></i>
                                    Priority Level
                                </label>
                                <select name="priority" id="priority" class="form-select form-select-lg" required>
                                    <option value="low" <?= (isset($step2Data['priority']) && $step2Data['priority'] == 'low') ? 'selected' : '' ?>>
                                        <span class="badge bg-success">Low Priority</span> - Routine case
                                    </option>
                                    <option value="medium" <?= (isset($step2Data['priority']) && $step2Data['priority'] == 'medium') ? 'selected' : 'selected' ?>>
                                        <span class="badge bg-warning">Medium Priority</span> - Standard case
                                    </option>
                                    <option value="high" <?= (isset($step2Data['priority']) && $step2Data['priority'] == 'high') ? 'selected' : '' ?>>
                                        <span class="badge bg-danger">High Priority</span> - Expedited case
                                    </option>
                                    <option value="urgent" <?= (isset($step2Data['priority']) && $step2Data['priority'] == 'urgent') ? 'selected' : '' ?>>
                                        <span class="badge bg-danger">Urgent</span> - Emergency case
                                    </option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a priority level.
                                </div>
                            </div>

                            <!-- Additional Notes -->
                            <div class="col-md-6">
                                <label for="notes" class="form-label fw-semibold">
                                    <i class="fas fa-sticky-note me-2 text-primary"></i>
                                    Additional Notes
                                </label>
                                <textarea 
                                    name="notes" 
                                    id="notes" 
                                    class="form-control form-control-lg" 
                                    rows="3"
                                    placeholder="Any additional information or special requirements..."
                                ><?= h($step2Data['notes'] ?? '') ?></textarea>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Optional: Any special instructions or notes for this case.
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Exams & Procedures Selection -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-procedures me-2 text-primary"></i>
                        Select Exams & Procedures
                        <span class="badge bg-primary rounded-pill ms-2" id="selectedCount">0</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input 
                                type="text" 
                                id="procedureSearch" 
                                class="form-control" 
                                placeholder="Search exams and procedures..."
                            >
                        </div>
                    </div>

                    <div id="examsProceduresContainer">
                        <?php if (!empty($examsProcedures)): ?>
                            <div class="row g-3">
                                <?php foreach ($examsProcedures as $examProc): ?>
                                    <div class="col-md-6 procedure-item" 
                                         data-name="<?= h(strtolower($examProc->exam->name ?? '') . ' ' . strtolower($examProc->procedure->name ?? '')) ?>">
                                        <div class="border rounded-3 p-3 procedure-card h-100" 
                                             style="cursor: pointer; transition: all 0.2s;">
                                            <div class="form-check">
                                                <input 
                                                    class="form-check-input procedure-checkbox" 
                                                    type="checkbox" 
                                                    name="exams_procedures[]" 
                                                    value="<?= $examProc->id ?>" 
                                                    id="proc_<?= $examProc->id ?>"
                                                    <?= (isset($step2Data['exams_procedures']) && in_array($examProc->id, $step2Data['exams_procedures'])) ? 'checked' : '' ?>
                                                >
                                                <label class="form-check-label w-100" for="proc_<?= $examProc->id ?>">
                                                    <div class="fw-semibold text-primary">
                                                        <?= h($examProc->exam->name ?? 'Unknown Exam') ?>
                                                    </div>
                                                    <div class="small text-muted mb-2">
                                                        <?= h($examProc->procedure->name ?? 'Unknown Procedure') ?>
                                                    </div>
                                                    <?php if (!empty($examProc->exam->modality->name)): ?>
                                                        <div class="small">
                                                            <span class="badge bg-secondary"><?= h($examProc->exam->modality->name) ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-procedures fa-2x mb-2"></i>
                                <div>No procedures available</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="alertContainer"></div>

            <!-- Navigation -->
            <div class="d-flex justify-content-between align-items-center py-3">
                <?= $this->Html->link(
                    '<i class="fas fa-arrow-left me-2"></i>Back to Step 1',
                    ['action' => 'addStep1'],
                    ['class' => 'btn btn-outline-secondary btn-lg', 'escape' => false]
                ) ?>
                
                <button type="submit" form="step2Form" class="btn btn-primary btn-lg" id="nextStepBtn">
                    <span id="btnText">
                        Next Step
                        <i class="fas fa-arrow-right ms-2"></i>
                    </span>
                    <span id="btnLoading" class="d-none">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Processing...
                    </span>
                </button>
            </div>

            <!-- Information Alert -->
            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-3 fa-lg"></i>
                    <div>
                        <h6 class="mb-1">Step 2 of 3</h6>
                        <div class="small mb-0">
                            Select the appropriate department, set case priority, and choose the exams/procedures to be performed. You can select multiple procedures as needed.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('step2Form');
    const departmentSelect = document.getElementById('department_id');
    const sedationSelect = document.getElementById('sedation_id');
    const procedureSearch = document.getElementById('procedureSearch');
    const procedureItems = document.querySelectorAll('.procedure-item');
    const procedureCheckboxes = document.querySelectorAll('.procedure-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const nextStepBtn = document.getElementById('nextStepBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    const alertContainer = document.getElementById('alertContainer');

    // Department selection handler
    departmentSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const descriptionDiv = document.getElementById('departmentDescription');
        
        if (this.value && selectedOption.dataset.description) {
            descriptionDiv.innerHTML = '<i class="fas fa-info-circle me-1"></i>' + selectedOption.dataset.description;
            descriptionDiv.classList.add('text-info');
        } else {
            descriptionDiv.innerHTML = '<i class="fas fa-info-circle me-1"></i>Please select a department to see details.';
            descriptionDiv.classList.remove('text-info');
        }
    });

    // Sedation selection handler
    sedationSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const infoDiv = document.getElementById('sedationInfo');
        
        if (this.value) {
            let info = '<i class="fas fa-info-circle me-1"></i>';
            if (selectedOption.dataset.type) {
                info += 'Type: ' + selectedOption.dataset.type;
            }
            if (selectedOption.dataset.risk) {
                info += ' | Risk: ' + selectedOption.dataset.risk;
            }
            infoDiv.innerHTML = info;
            infoDiv.classList.add('text-warning');
        } else {
            infoDiv.innerHTML = '<i class="fas fa-info-circle me-1"></i>No sedation required for this case.';
            infoDiv.classList.remove('text-warning');
        }
    });

    // Search functionality
    procedureSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        procedureItems.forEach(item => {
            const itemName = item.dataset.name;
            if (itemName.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Procedure selection
    procedureCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
        
        // Card click handler
        const card = checkbox.closest('.procedure-card');
        card.addEventListener('click', function(e) {
            if (e.target !== checkbox) {
                checkbox.checked = !checkbox.checked;
                updateSelectedCount();
            }
        });
    });

    // Update selected count and visual feedback
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.procedure-checkbox:checked').length;
        selectedCount.textContent = checked;
        
        // Update card styling
        procedureCheckboxes.forEach(checkbox => {
            const card = checkbox.closest('.procedure-card');
            if (checkbox.checked) {
                card.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
            } else {
                card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            }
        });
    }

    // Initialize
    departmentSelect.dispatchEvent(new Event('change'));
    sedationSelect.dispatchEvent(new Event('change'));
    updateSelectedCount();

    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        e.stopPropagation();

        alertContainer.innerHTML = '';

        // Validate required fields
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }

        // Check if at least one procedure is selected
        const checkedProcedures = document.querySelectorAll('.procedure-checkbox:checked');
        if (checkedProcedures.length === 0) {
            alertContainer.innerHTML = `
                <div class="alert alert-warning border-0" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2 fa-lg"></i>
                        <div>
                            <strong>No Procedures Selected:</strong> Please select at least one exam or procedure.
                        </div>
                    </div>
                </div>
            `;
            alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Disable button and show loading
        nextStepBtn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');

        try {
            // Get form data
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            
            // Add selected procedures
            data.exams_procedures = Array.from(checkedProcedures).map(cb => cb.value);

            // Send AJAX request
            const response = await fetch('<?= $this->Url->build(['action' => 'saveStep2']) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $this->request->getAttribute('csrfToken') ?>'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                alertContainer.innerHTML = `
                    <div class="alert alert-success border-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2 fa-lg"></i>
                            <div>
                                <strong>Step 2 Complete!</strong> Proceeding to review and notes...
                            </div>
                        </div>
                    </div>
                `;

                setTimeout(() => {
                    window.location.href = '<?= $this->Url->build(['action' => 'addStep3']) ?>';
                }, 1000);
            } else {
                // Show errors
                let errorHtml = '<div class="alert alert-danger border-0" role="alert">';
                errorHtml += '<div class="d-flex align-items-start">';
                errorHtml += '<i class="fas fa-exclamation-circle me-2 fa-lg mt-1"></i>';
                errorHtml += '<div class="flex-grow-1">';
                errorHtml += '<h6 class="mb-1">Validation Errors</h6>';
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
                    errorHtml += '<li>An error occurred while saving step 2.</li>';
                }
                
                errorHtml += '</ul></div></div></div>';
                alertContainer.innerHTML = errorHtml;

                nextStepBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');

                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } catch (error) {
            console.error('Error:', error);
            alertContainer.innerHTML = `
                <div class="alert alert-danger border-0" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2 fa-lg"></i>
                        <div>
                            <strong>Network Error:</strong> Please check your connection and try again.
                        </div>
                    </div>
                </div>
            `;
            
            nextStepBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');

            alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});
</script>
            <!-- AI Recommendations Card -->
            <div class="card shadow-sm mb-4 border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-robot me-2"></i>
                        AI Recommendations
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (!empty($aiRecommendations['department_id'])): ?>
                        <div class="col-md-4 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Department:</strong><br>
                                    <span class="badge bg-info"><?= h($departments[$aiRecommendations['department_id']] ?? 'Unknown') ?></span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-info" id="applyDepartment" data-department-id="<?= h($aiRecommendations['department_id']) ?>">
                                    <i class="fas fa-check me-1"></i>Apply
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($aiRecommendations['sedation_id'])): ?>
                        <div class="col-md-4 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Sedation:</strong><br>
                                    <span class="badge bg-warning text-dark"><?= h($sedations[$aiRecommendations['sedation_id']] ?? 'Unknown') ?></span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-warning" id="applySedation" data-sedation-id="<?= h($aiRecommendations['sedation_id']) ?>">
                                    <i class="fas fa-check me-1"></i>Apply
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($aiRecommendations['priority'])): ?>
                        <div class="col-md-4 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Priority:</strong><br>
                                    <span class="badge bg-<?= $aiRecommendations['priority'] === 'urgent' ? 'danger' : ($aiRecommendations['priority'] === 'high' ? 'warning' : 'secondary') ?>">
                                        <?= h(ucfirst($aiRecommendations['priority'])) ?>
                                    </span>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="applyPriority" data-priority="<?= h($aiRecommendations['priority']) ?>">
                                    <i class="fas fa-check me-1"></i>Apply
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($aiRecommendations['recommended_exam_procedure_ids'])): ?>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Recommended Exams/Procedures:</strong>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-success" id="applySelectedRecommendations">
                                    <i class="fas fa-check me-1"></i>Apply Selected
                                </button>
                                <button type="button" class="btn btn-sm btn-info" id="applyAllRecommendations">
                                    <i class="fas fa-check-double me-1"></i>Apply All
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <?php foreach ($aiRecommendations['recommended_exam_procedure_ids'] as $examProcId): ?>
                                <?php if (isset($examsProcedures[$examProcId])): ?>
                                    <div class="form-check form-check-inline mb-2">
                                        <input 
                                            class="form-check-input ai-recommendation-checkbox" 
                                            type="checkbox" 
                                            id="ai_rec_<?= h($examProcId) ?>" 
                                            value="<?= h($examProcId) ?>"
                                            checked
                                        >
                                        <label class="form-check-label badge bg-light text-dark border" for="ai_rec_<?= h($examProcId) ?>">
                                            <?= h($examsProcedures[$examProcId]) ?>
                                        </label>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Step 2 Form -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-procedures me-2"></i>
                        Department & Procedures Selection
                    </h5>
                </div>
                <div class="card-body">
                    <form id="step2Form" novalidate>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="department_id" class="form-label">
                                    Department <span class="text-danger">*</span>
                                </label>
                                <select 
                                    name="department_id" 
                                    id="department_id" 
                                    class="form-select" 
                                    required
                                >
                                    <option value="">-- Select Department --</option>
                                    <?php foreach ($departments as $id => $name): ?>
                                        <option value="<?= h($id) ?>" 
                                            <?= (!empty($step2Data['department_id']) && $step2Data['department_id'] == $id) ? 'selected' : '' ?>
                                            <?= (!empty($aiRecommendations['department_id']) && $aiRecommendations['department_id'] == $id) ? 'data-ai-recommended="true"' : '' ?>
                                        >
                                            <?= h($name) ?>
                                            <?= (!empty($aiRecommendations['department_id']) && $aiRecommendations['department_id'] == $id) ? ' ★' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a department.
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="sedation_id" class="form-label">
                                    Sedation Level
                                </label>
                                <select 
                                    name="sedation_id" 
                                    id="sedation_id" 
                                    class="form-select"
                                >
                                    <option value="">-- No Sedation --</option>
                                    <?php foreach ($sedations as $id => $name): ?>
                                        <option value="<?= h($id) ?>" 
                                            <?= (!empty($step2Data['sedation_id']) && $step2Data['sedation_id'] == $id) ? 'selected' : '' ?>
                                            <?= (!empty($aiRecommendations['sedation_id']) && $aiRecommendations['sedation_id'] == $id) ? 'data-ai-recommended="true"' : '' ?>
                                        >
                                            <?= h($name) ?>
                                            <?= (!empty($aiRecommendations['sedation_id']) && $aiRecommendations['sedation_id'] == $id) ? ' ★' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="priority" class="form-label">
                                    Priority <span class="text-danger">*</span>
                                </label>
                                <select 
                                    name="priority" 
                                    id="priority" 
                                    class="form-select" 
                                    required
                                >
                                    <option value="">-- Select Priority --</option>
                                    <?php 
                                    $priorities = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'];
                                    foreach ($priorities as $value => $label): 
                                    ?>
                                        <option value="<?= h($value) ?>" 
                                            <?= (!empty($step2Data['priority']) && $step2Data['priority'] == $value) ? 'selected' : '' ?>
                                            <?= (!empty($aiRecommendations['priority']) && $aiRecommendations['priority'] == $value) ? 'data-ai-recommended="true"' : '' ?>
                                        >
                                            <?= h($label) ?>
                                            <?= (!empty($aiRecommendations['priority']) && $aiRecommendations['priority'] == $value) ? ' ★' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a priority level.
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Select Exams/Procedures <span class="text-danger">*</span>
                            </label>
                            <div class="form-text mb-2">
                                <i class="fas fa-info-circle"></i>
                                Items marked with ★ are AI-recommended based on patient symptoms
                            </div>
                            <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                                <?php 
                                $selectedExamProcedures = $step2Data['exam_procedures'] ?? [];
                                $aiRecommendedIds = $aiRecommendations['recommended_exam_procedure_ids'] ?? [];
                                
                                foreach ($examsProcedures as $id => $name): 
                                    $isAiRecommended = in_array($id, $aiRecommendedIds);
                                    $isSelected = in_array($id, $selectedExamProcedures);
                                ?>
                                    <div class="form-check mb-2">
                                        <input 
                                            class="form-check-input exam-procedure-checkbox" 
                                            type="checkbox" 
                                            name="exam_procedures[]" 
                                            value="<?= h($id) ?>" 
                                            id="exam_proc_<?= h($id) ?>"
                                            <?= $isSelected ? 'checked' : '' ?>
                                            <?= $isAiRecommended ? 'data-ai-recommended="true"' : '' ?>
                                        >
                                        <label class="form-check-label <?= $isAiRecommended ? 'fw-bold text-info' : '' ?>" for="exam_proc_<?= h($id) ?>">
                                            <?= $isAiRecommended ? '★ ' : '' ?><?= h($name) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="invalid-feedback d-block" id="examProceduresError" style="display: none !important;">
                                Please select at least one exam or procedure.
                            </div>
                        </div>

                        <div id="alertContainer"></div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <?= $this->Html->link(
                                '<i class="fas fa-arrow-left me-2"></i>Back',
                                ['action' => 'addStep1'],
                                ['class' => 'btn btn-outline-secondary', 'escape' => false]
                            ) ?>
                            
                            <button type="submit" class="btn btn-primary btn-lg" id="nextStepBtn">
                                <span id="btnText">
                                    Continue <i class="fas fa-arrow-right ms-2"></i>
                                </span>
                                <span id="btnLoading" class="d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const aiRecommendedExamProcIds = <?= json_encode($aiRecommendations['recommended_exam_procedure_ids'] ?? []) ?>;

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('step2Form');
    const nextStepBtn = document.getElementById('nextStepBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    const alertContainer = document.getElementById('alertContainer');
    const examProceduresError = document.getElementById('examProceduresError');

    // Apply Department button
    const applyDepartmentBtn = document.getElementById('applyDepartment');
    if (applyDepartmentBtn) {
        applyDepartmentBtn.addEventListener('click', function() {
            const departmentId = this.dataset.departmentId;
            document.getElementById('department_id').value = departmentId;
            showToast('Department applied successfully', 'success');
        });
    }

    // Apply Sedation button
    const applySedationBtn = document.getElementById('applySedation');
    if (applySedationBtn) {
        applySedationBtn.addEventListener('click', function() {
            const sedationId = this.dataset.sedationId;
            document.getElementById('sedation_id').value = sedationId;
            showToast('Sedation level applied successfully', 'success');
        });
    }

    // Apply Priority button
    const applyPriorityBtn = document.getElementById('applyPriority');
    if (applyPriorityBtn) {
        applyPriorityBtn.addEventListener('click', function() {
            const priority = this.dataset.priority;
            document.getElementById('priority').value = priority;
            showToast('Priority applied successfully', 'success');
        });
    }

    // Apply Selected Recommendations button (only checked AI recommendations)
    const applySelectedBtn = document.getElementById('applySelectedRecommendations');
    if (applySelectedBtn) {
        applySelectedBtn.addEventListener('click', function() {
            const selectedIds = [];
            document.querySelectorAll('.ai-recommendation-checkbox:checked').forEach(checkbox => {
                selectedIds.push(parseInt(checkbox.value));
            });

            if (selectedIds.length === 0) {
                showToast('Please select at least one recommendation', 'warning');
                return;
            }

            // Check the corresponding exam procedure checkboxes
            document.querySelectorAll('.exam-procedure-checkbox').forEach(checkbox => {
                if (selectedIds.includes(parseInt(checkbox.value))) {
                    checkbox.checked = true;
                }
            });
            validateExamProcedures();
            showToast(`${selectedIds.length} exam(s)/procedure(s) applied`, 'success');
        });
    }

    // Apply All Recommendations button
    const applyAllBtn = document.getElementById('applyAllRecommendations');
    if (applyAllBtn) {
        applyAllBtn.addEventListener('click', function() {
            // Apply department, sedation, and priority
            if (applyDepartmentBtn) applyDepartmentBtn.click();
            if (applySedationBtn) applySedationBtn.click();
            if (applyPriorityBtn) applyPriorityBtn.click();

            // Apply all exam procedures
            document.querySelectorAll('.exam-procedure-checkbox').forEach(checkbox => {
                if (checkbox.dataset.aiRecommended === 'true') {
                    checkbox.checked = true;
                }
            });
            validateExamProcedures();
            showToast('All AI recommendations applied successfully', 'success');
        });
    }

    // Helper function to show toast notifications
    function showToast(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 
                          type === 'danger' ? 'alert-danger' : 'alert-info';
        
        const iconClass = type === 'success' ? 'fa-check-circle' : 
                         type === 'warning' ? 'fa-exclamation-triangle' : 
                         type === 'danger' ? 'fa-times-circle' : 'fa-info-circle';

        const toast = document.createElement('div');
        toast.className = `alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <i class="fas ${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);

        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Custom validation for exam procedures
    function validateExamProcedures() {
        const checkedBoxes = document.querySelectorAll('.exam-procedure-checkbox:checked');
        const isValid = checkedBoxes.length > 0;
        
        if (isValid) {
            examProceduresError.style.display = 'none';
        } else if (form.classList.contains('was-validated')) {
            examProceduresError.style.display = 'block';
        }
        
        return isValid;
    }

    // Listen for checkbox changes
    document.querySelectorAll('.exam-procedure-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', validateExamProcedures);
    });

    // Bootstrap validation
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        e.stopPropagation();

        // Remove previous validation states
        form.classList.remove('was-validated');
        alertContainer.innerHTML = '';

        // Check form validity
        const formValid = form.checkValidity();
        const examProcValid = validateExamProcedures();

        if (!formValid || !examProcValid) {
            form.classList.add('was-validated');
            if (!examProcValid) {
                examProceduresError.style.display = 'block';
            }
            return;
        }

        // Disable button and show loading
        nextStepBtn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');

        try {
            // Get form data
            const formData = new FormData(form);
            const data = {
                department_id: formData.get('department_id'),
                sedation_id: formData.get('sedation_id') || null,
                priority: formData.get('priority'),
                exam_procedures: formData.getAll('exam_procedures[]').map(id => parseInt(id))
            };

            // Send AJAX request to save step 2
            const response = await fetch('<?= $this->Url->build(['action' => 'saveStep2']) ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $this->request->getAttribute('csrfToken') ?>'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                // Redirect to step 3
                window.location.href = '<?= $this->Url->build(['action' => 'addStep3']) ?>';
            } else {
                // Show errors
                let errorHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><ul class="mb-0">';
                if (result.errors) {
                    for (const [field, message] of Object.entries(result.errors)) {
                        errorHtml += `<li>${message}</li>`;
                    }
                } else if (result.error) {
                    errorHtml += `<li>${result.error}</li>`;
                }
                errorHtml += '</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
                alertContainer.innerHTML = errorHtml;

                // Re-enable button
                nextStepBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnLoading.classList.add('d-none');
            }
        } catch (error) {
            console.error('Error:', error);
            alertContainer.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    An error occurred. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Re-enable button
            nextStepBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnLoading.classList.add('d-none');
        }
    });

    // Real-time validation feedback
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (form.classList.contains('was-validated')) {
                this.classList.toggle('is-invalid', !this.checkValidity());
                this.classList.toggle('is-valid', this.checkValidity());
            }
        });

        field.addEventListener('change', function() {
            if (form.classList.contains('was-validated')) {
                this.classList.toggle('is-invalid', !this.checkValidity());
                this.classList.toggle('is-valid', this.checkValidity());
            }
        });
    });
});
</script>
