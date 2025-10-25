<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 * @var array $step1Data
 * @var array $step2Data
 * @var array $departments
 * @var array $sedations
 * @var array $examsProcedures
 * @var array $aiRecommendations
 * @var object $currentHospital
 */
$this->assign('title', 'Edit Case #' . $case->id . ' - Step 2: Department & Procedures');
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
                            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-procedures fa-lg"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-primary">Step 2</strong>
                                <div class="small text-muted">Department & Procedures</div>
                            </div>
                        </div>
                        <div class="flex-fill">
                            <hr class="border-2">
                        </div>
                        <div class="text-center flex-fill">
                            <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fas fa-clipboard-check fa-lg"></i>
                            </div>
                            <div class="mt-2">
                                <strong class="text-muted">Step 3</strong>
                                <div class="small text-muted">Review & Notes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($aiRecommendations['ai_generated'])): ?>
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
                                ['action' => 'editStep1', $case->id],
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
            const response = await fetch('<?= $this->Url->build(['action' => 'saveEditStep2', $case->id]) ?>', {
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
                window.location.href = '<?= $this->Url->build(['action' => 'editStep3', $case->id]) ?>';
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
