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

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-edit me-2"></i>Edit Case #<?= h($case->id) ?>
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-procedures me-2"></i>Step 2: Department & Procedures
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?= $this->Html->link(
                            '<i class="fas fa-eye me-2"></i>View Case',
                            ['action' => 'view', $case->id],
                            ['class' => 'btn btn-light', 'escape' => false]
                        ) ?>
                        <?= $this->Html->link(
                            '<i class="fas fa-list me-2"></i>All Cases',
                            ['action' => 'index'],
                            ['class' => 'btn btn-light', 'escape' => false]
                        ) ?>
                    </div>
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

            <!-- Current Case Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light border-0">
                    <h6 class="mb-0 fw-semibold">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        Case Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <small class="text-muted fw-semibold">Case ID:</small>
                            <div class="fw-medium">#<?= h($case->id) ?></div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted fw-semibold">Patient:</small>
                            <div class="fw-medium"><?= h($step1Data['patient_name'] ?? 'Not selected') ?></div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted fw-semibold">Case Date:</small>
                            <div><?= h(date('M d, Y', strtotime($step1Data['date'] ?? 'now'))) ?></div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted fw-semibold">Status:</small>
                            <div>
                                <span class="badge bg-info"><?= h(ucfirst($case->technician_status ?? 'draft')) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-12">
                            <small class="text-muted fw-semibold">Symptoms:</small>
                            <div class="small bg-light rounded p-2" style="max-height: 60px; overflow-y: auto;">
                                <?= h($step1Data['symptoms'] ?? 'No symptoms recorded') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- AI Recommendations -->
            <?php if (!empty($aiRecommendations)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header border-0" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold text-white">
                                <i class="fas fa-robot me-2"></i>
                                AI Recommendations
                            </h6>
                            <button type="button" class="btn btn-sm btn-light" id="applyAllMasterRecommendations">
                                <i class="fas fa-magic me-1"></i>Apply All Recommendations
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info border-0 mb-3">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>How to use AI Recommendations:</strong> Review the suggestions below and click individual "Apply" buttons or use "Apply All Recommendations" to accept all suggestions at once.
                        </div>

                        <div class="row g-3">
                            <!-- Department Recommendation -->
                            <?php if (!empty($aiRecommendations['department_id'])): ?>
                                <div class="col-md-6">
                                    <div class="card border-warning">
                                        <div class="card-body py-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><i class="fas fa-building me-2"></i>Department</h6>
                                                    <div class="fw-semibold text-warning">
                                                        <?= h($departments[$aiRecommendations['department_id']] ?? 'Unknown') ?>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-warning" id="applyDepartment" 
                                                        data-department-id="<?= h($aiRecommendations['department_id']) ?>">
                                                    <i class="fas fa-check me-1"></i>Apply
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Sedation Recommendation -->
                            <?php if (!empty($aiRecommendations['sedation_id'])): ?>
                                <div class="col-md-6">
                                    <div class="card border-warning">
                                        <div class="card-body py-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><i class="fas fa-pills me-2"></i>Sedation</h6>
                                                    <div class="fw-semibold text-warning">
                                                        <?= h($sedations[$aiRecommendations['sedation_id']] ?? 'Unknown') ?>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-warning" id="applySedation"
                                                        data-sedation-id="<?= h($aiRecommendations['sedation_id']) ?>">
                                                    <i class="fas fa-check me-1"></i>Apply
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Priority Recommendation -->
                            <?php if (!empty($aiRecommendations['priority'])): ?>
                                <div class="col-md-6">
                                    <div class="card border-warning">
                                        <div class="card-body py-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><i class="fas fa-exclamation-circle me-2"></i>Priority</h6>
                                                    <div class="fw-semibold text-warning">
                                                        <?= h(ucfirst($aiRecommendations['priority'])) ?> Priority
                                                    </div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-warning" id="applyPriority"
                                                        data-priority="<?= h($aiRecommendations['priority']) ?>">
                                                    <i class="fas fa-check me-1"></i>Apply
                                                </button>
                                            </div>
                                        </div>
                                    </div>
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
                                    <?php foreach ($departments as $id => $name): ?>
                                        <option value="<?= h($id) ?>" 
                                            <?= (isset($step2Data['department_id']) && $step2Data['department_id'] == $id) ? 'selected' : '' ?>
                                            <?= (!empty($aiRecommendations['department_id']) && $aiRecommendations['department_id'] == $id) ? 'data-ai-recommended="true"' : '' ?>>
                                            <?= h($name) ?>
                                            <?= (!empty($aiRecommendations['department_id']) && $aiRecommendations['department_id'] == $id) ? ' ★' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a department.
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Choose the medical department for this case.
                                </div>
                            </div>

                            <!-- Sedation Selection -->
                            <div class="col-md-6">
                                <label for="sedation_id" class="form-label fw-semibold">
                                    <i class="fas fa-pills me-2 text-primary"></i>
                                    Sedation Level
                                </label>
                                <select name="sedation_id" id="sedation_id" class="form-select form-select-lg">
                                    <option value="">No Sedation Required</option>
                                    <?php foreach ($sedations as $id => $name): ?>
                                        <option value="<?= h($id) ?>" 
                                            <?= (isset($step2Data['sedation_id']) && $step2Data['sedation_id'] == $id) ? 'selected' : '' ?>
                                            <?= (!empty($aiRecommendations['sedation_id']) && $aiRecommendations['sedation_id'] == $id) ? 'data-ai-recommended="true"' : '' ?>>
                                            <?= h($name) ?>
                                            <?= (!empty($aiRecommendations['sedation_id']) && $aiRecommendations['sedation_id'] == $id) ? ' ★' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Optional: Select if sedation is required for the procedures.
                                </div>
                            </div>

                            <!-- Priority Level -->
                            <div class="col-md-6">
                                <label for="priority" class="form-label fw-semibold">
                                    <i class="fas fa-exclamation-circle me-2 text-primary"></i>
                                    Priority Level
                                </label>
                                <select name="priority" id="priority" class="form-select form-select-lg" required>
                                    <option value="">Choose priority level...</option>
                                    <option value="low" 
                                        <?= (isset($step2Data['priority']) && $step2Data['priority'] == 'low') ? 'selected' : '' ?>
                                        <?= (!empty($aiRecommendations['priority']) && $aiRecommendations['priority'] == 'low') ? 'data-ai-recommended="true"' : '' ?>>
                                        Low Priority - Routine case
                                        <?= (!empty($aiRecommendations['priority']) && $aiRecommendations['priority'] == 'low') ? ' ★' : '' ?>
                                    </option>
                                    <option value="medium" 
                                        <?= (isset($step2Data['priority']) && $step2Data['priority'] == 'medium') ? 'selected' : 'selected' ?>
                                        <?= (!empty($aiRecommendations['priority']) && $aiRecommendations['priority'] == 'medium') ? 'data-ai-recommended="true"' : '' ?>>
                                        Medium Priority - Standard case
                                        <?= (!empty($aiRecommendations['priority']) && $aiRecommendations['priority'] == 'medium') ? ' ★' : '' ?>
                                    </option>
                                    <option value="high" 
                                        <?= (isset($step2Data['priority']) && $step2Data['priority'] == 'high') ? 'selected' : '' ?>
                                        <?= (!empty($aiRecommendations['priority']) && $aiRecommendations['priority'] == 'high') ? 'data-ai-recommended="true"' : '' ?>>
                                        High Priority - Expedited case
                                        <?= (!empty($aiRecommendations['priority']) && $aiRecommendations['priority'] == 'high') ? ' ★' : '' ?>
                                    </option>
                                    <option value="urgent" 
                                        <?= (isset($step2Data['priority']) && $step2Data['priority'] == 'urgent') ? 'selected' : '' ?>
                                        <?= (!empty($aiRecommendations['priority']) && $aiRecommendations['priority'] == 'urgent') ? 'data-ai-recommended="true"' : '' ?>>
                                        Urgent - Emergency case
                                        <?= (!empty($aiRecommendations['priority']) && $aiRecommendations['priority'] == 'urgent') ? ' ★' : '' ?>
                                    </option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a priority level.
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Set the urgency level for this case.
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

                        <!-- Exams & Procedures Selection -->
                        <div class="mt-4">
                            <div class="card border-0 bg-light">
                                <div class="card-header bg-light border-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="fas fa-procedures me-2 text-primary"></i>
                                            Select Exams & Procedures
                                            <span class="badge bg-primary rounded-pill ms-2" id="selectedCount">0</span>
                                        </h6>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" id="selectAllBtn">
                                                <i class="fas fa-check-double me-1"></i>Select All
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" id="clearAllBtn">
                                                <i class="fas fa-times me-1"></i>Clear All
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <!-- Search and Filter Controls -->
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-8">
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
                                        <div class="col-md-4">
                                            <select id="modalityFilter" class="form-select">
                                                <option value="">All Modalities</option>
                                                <?php 
                                                $modalities = [];
                                                foreach ($examsProcedures as $examProc) {
                                                    // Ensure $examProc is an object before accessing properties
                                                    if (is_object($examProc) && !empty($examProc->exam->modality->name)) {
                                                        $modalities[$examProc->exam->modality->name] = true;
                                                    }
                                                }
                                                foreach (array_keys($modalities) as $modality): 
                                                ?>
                                                    <option value="<?= h($modality) ?>"><?= h($modality) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- AI Recommended Section -->
                                    <?php 
                                    $aiRecommendedIds = $aiRecommendations['recommended_exam_procedure_ids'] ?? [];
                                    $aiRecommendedProcs = array_filter($examsProcedures, function($examProc) use ($aiRecommendedIds) {
                                        // Ensure $examProc is an object before accessing properties
                                        if (is_object($examProc) && isset($examProc->id)) {
                                            return in_array($examProc->id, $aiRecommendedIds);
                                        }
                                        return false;
                                    });
                                    ?>
                                    <?php if (!empty($aiRecommendedProcs)): ?>
                                        <div class="alert alert-warning border-0 mb-3">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <i class="fas fa-robot me-2"></i>
                                                    <strong>AI Recommended Procedures</strong> - Based on patient symptoms
                                                </div>
                                                <button type="button" class="btn btn-sm btn-warning" id="selectAiRecommendedBtn">
                                                    <i class="fas fa-magic me-1"></i>Select All AI
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Procedures Container with Fixed Height -->
                                    <div id="examsProceduresContainer" class="border rounded" style="height: 400px; overflow-y: auto;">
                                        <?php if (!empty($examsProcedures)): ?>
                                            <div class="p-3">
                                                <div class="row g-2" id="proceduresList">
                                                    <?php 
                                                    foreach ($examsProcedures as $examProc): 
                                                        // Ensure $examProc is an object before accessing properties
                                                        if (!is_object($examProc) || !isset($examProc->id)) {
                                                            continue; // Skip invalid entries
                                                        }
                                                        $isAiRecommended = in_array($examProc->id, $aiRecommendedIds);
                                                    ?>
                                                        <div class="col-12 procedure-item" 
                                                             data-name="<?= h(strtolower($examProc->exam->name ?? '') . ' ' . strtolower($examProc->procedure->name ?? '')) ?>"
                                                             data-modality="<?= h($examProc->exam->modality->name ?? '') ?>"
                                                             data-ai-recommended="<?= $isAiRecommended ? 'true' : 'false' ?>">
                                                            <div class="card border-0 bg-light procedure-card" style="cursor: pointer;">
                                                                <div class="card-body py-2 px-3">
                                                                    <div class="form-check d-flex align-items-center">
                                                                        <input 
                                                                            class="form-check-input procedure-checkbox me-3" 
                                                                            type="checkbox" 
                                                                            name="exam_procedures[]" 
                                                                            value="<?= $examProc->id ?>" 
                                                                            id="proc_<?= $examProc->id ?>"
                                                                            form="step2Form"
                                                                            <?= (isset($step2Data['exam_procedures']) && in_array($examProc->id, $step2Data['exam_procedures'])) ? 'checked' : '' ?>
                                                                            <?= $isAiRecommended ? 'data-ai-recommended="true"' : '' ?>
                                                                        >
                                                                        <label class="form-check-label flex-grow-1" for="proc_<?= $examProc->id ?>">
                                                                            <div class="d-flex justify-content-between align-items-center">
                                                                                <div class="flex-grow-1">
                                                                                    <div class="fw-semibold text-primary mb-1">
                                                                                        <?= $isAiRecommended ? '★ ' : '' ?><?= h($examProc->exam->name ?? 'Unknown Exam') ?>
                                                                                        <?php if ($isAiRecommended): ?>
                                                                                            <span class="badge bg-warning text-dark ms-2">AI</span>
                                                                                        <?php endif; ?>
                                                                                    </div>
                                                                                    <div class="small text-muted">
                                                                                        <?= h($examProc->procedure->name ?? 'Unknown Procedure') ?>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="text-end">
                                                                                    <?php if (!empty($examProc->exam->modality->name)): ?>
                                                                                        <span class="badge bg-secondary"><?= h($examProc->exam->modality->name) ?></span>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center text-muted py-5">
                                                <i class="fas fa-procedures fa-3x mb-3 opacity-50"></i>
                                                <h6>No procedures available</h6>
                                                <p class="small mb-0">Please contact your administrator to add procedures.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Quick Stats -->
                                    <div class="mt-3 pt-3 border-top">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="small text-muted">Total Available</div>
                                                <div class="fw-bold text-primary" id="totalCount"><?= count($examsProcedures) ?></div>
                                            </div>
                                            <div class="col-4">
                                                <div class="small text-muted">AI Recommended</div>
                                                <div class="fw-bold text-warning" id="aiCount"><?= count($aiRecommendedProcs) ?></div>
                                            </div>
                                            <div class="col-4">
                                                <div class="small text-muted">Selected</div>
                                                <div class="fw-bold text-success" id="selectedCountStats">0</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="alertContainer"></div>

            <!-- Navigation -->
            <div class="d-flex justify-content-between align-items-center py-3">
                <?= $this->Html->link(
                    '<i class="fas fa-arrow-left me-2"></i>Back to Step 1',
                    ['action' => 'editStep1', $case->id],
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
                        <h6 class="mb-1">Step 2 of 3 - Editing Case #<?= h($case->id) ?></h6>
                        <div class="small mb-0">
                            Update department, set case priority, and choose the exams/procedures to be performed. You can select multiple procedures as needed.
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
    const procedureSearch = document.getElementById('procedureSearch');
    const modalityFilter = document.getElementById('modalityFilter');
    const procedureItems = document.querySelectorAll('.procedure-item');
    const procedureCheckboxes = document.querySelectorAll('.procedure-checkbox');
    const selectedCount = document.getElementById('selectedCount');
    const selectedCountStats = document.getElementById('selectedCountStats');
    const selectAllBtn = document.getElementById('selectAllBtn');
    const clearAllBtn = document.getElementById('clearAllBtn');
    const selectAiRecommendedBtn = document.getElementById('selectAiRecommendedBtn');
    const nextStepBtn = document.getElementById('nextStepBtn');
    const btnText = document.getElementById('btnText');
    const btnLoading = document.getElementById('btnLoading');
    const alertContainer = document.getElementById('alertContainer');

    // AI Recommendation Apply Buttons
    const applyDepartmentBtn = document.getElementById('applyDepartment');
    if (applyDepartmentBtn) {
        applyDepartmentBtn.addEventListener('click', function() {
            const departmentId = this.dataset.departmentId;
            document.getElementById('department_id').value = departmentId;
            showToast('Department applied successfully', 'success');
        });
    }

    const applySedationBtn = document.getElementById('applySedation');
    if (applySedationBtn) {
        applySedationBtn.addEventListener('click', function() {
            const sedationId = this.dataset.sedationId;
            document.getElementById('sedation_id').value = sedationId;
            showToast('Sedation level applied successfully', 'success');
        });
    }

    const applyPriorityBtn = document.getElementById('applyPriority');
    if (applyPriorityBtn) {
        applyPriorityBtn.addEventListener('click', function() {
            const priority = this.dataset.priority;
            document.getElementById('priority').value = priority;
            showToast('Priority level applied successfully', 'success');
        });
    }

    // Master Apply All button (applies everything at once)
    const applyAllMasterBtn = document.getElementById('applyAllMasterRecommendations');
    if (applyAllMasterBtn) {
        applyAllMasterBtn.addEventListener('click', function() {
            let appliedCount = 0;
            
            // Apply department, sedation, and priority
            if (applyDepartmentBtn) {
                applyDepartmentBtn.click();
                appliedCount++;
            }
            if (applySedationBtn) {
                applySedationBtn.click();
                appliedCount++;
            }
            if (applyPriorityBtn) {
                applyPriorityBtn.click();
                appliedCount++;
            }

            // Apply all AI recommended exam procedures
            document.querySelectorAll('.procedure-checkbox').forEach(checkbox => {
                if (checkbox.dataset.aiRecommended === 'true') {
                    checkbox.checked = true;
                }
            });
            updateSelectedCount();
            
            showToast(`🤖 All AI recommendations applied! (${appliedCount} form fields + procedures)`, 'success');
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

    // Filter procedures function
    function filterProcedures() {
        const searchTerm = procedureSearch.value.toLowerCase();
        const modalityTerm = modalityFilter.value.toLowerCase();
        
        procedureItems.forEach(item => {
            const name = item.dataset.name || '';
            const modality = item.dataset.modality || '';
            
            const matchesSearch = name.includes(searchTerm);
            const matchesModality = !modalityTerm || modality.toLowerCase() === modalityTerm;
            
            if (matchesSearch && matchesModality) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Search and filter event listeners
    procedureSearch.addEventListener('input', filterProcedures);
    if (modalityFilter) {
        modalityFilter.addEventListener('change', filterProcedures);
    }

    // Button event listeners
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            procedureCheckboxes.forEach(checkbox => {
                if (checkbox.closest('.procedure-item').style.display !== 'none') {
                    checkbox.checked = true;
                }
            });
            updateSelectedCount();
            showToast('All visible procedures selected', 'success');
        });
    }

    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            procedureCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            updateSelectedCount();
            showToast('All procedures cleared', 'info');
        });
    }

    if (selectAiRecommendedBtn) {
        selectAiRecommendedBtn.addEventListener('click', function() {
            procedureCheckboxes.forEach(checkbox => {
                if (checkbox.dataset.aiRecommended === 'true') {
                    checkbox.checked = true;
                }
            });
            updateSelectedCount();
            showToast('AI recommended procedures selected', 'success');
        });
    }

    // Checkbox change listeners
    procedureCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
        
        // Card click handler
        const card = checkbox.closest('.procedure-card');
        if (card) {
            card.addEventListener('click', function(e) {
                if (e.target !== checkbox) {
                    checkbox.checked = !checkbox.checked;
                    updateSelectedCount();
                }
            });
        }
    });

    // Update selected count and visual feedback
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.procedure-checkbox:checked').length;
        selectedCount.textContent = checked;
        if (selectedCountStats) {
            selectedCountStats.textContent = checked;
        }
        
        // Update card styling with pure Bootstrap classes
        procedureCheckboxes.forEach(checkbox => {
            const card = checkbox.closest('.procedure-card');
            if (checkbox.checked) {
                card.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
            } else {
                card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            }
        });
    }

    // Initialize count on page load
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

        console.log(`Selected ${checkedProcedures.length} procedures for submission`);

        // Disable button and show loading
        nextStepBtn.disabled = true;
        btnText.classList.add('d-none');
        btnLoading.classList.remove('d-none');

        try {
            // Get form data and manually construct the object to ensure arrays work correctly
            const formData = new FormData(form);
            
            // Build data object manually to handle checkbox arrays properly
            const data = {};
            
            // Get regular form fields
            data.department_id = formData.get('department_id');
            data.sedation_id = formData.get('sedation_id');
            data.priority = formData.get('priority');
            data.notes = formData.get('notes');
            
            // Get all selected procedures as an array
            const selectedProcedures = [];
            document.querySelectorAll('.procedure-checkbox:checked').forEach(checkbox => {
                selectedProcedures.push(checkbox.value);
            });
            data.exam_procedures = selectedProcedures;
            
            // Debug logging
            console.log('Form data being sent:', data);
            console.log('Selected procedures:', selectedProcedures);
            console.log('Number of procedures:', selectedProcedures.length);

            // Send AJAX request
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
                alertContainer.innerHTML = `
                    <div class="alert alert-success border-0" role="alert">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2 fa-lg"></i>
                            <div>
                                <strong>Step 2 Updated!</strong> Proceeding to final review...
                            </div>
                        </div>
                    </div>
                `;

                setTimeout(() => {
                    window.location.href = '<?= $this->Url->build(['action' => 'editStep3', $case->id]) ?>';
                }, 1000);
            } else {
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