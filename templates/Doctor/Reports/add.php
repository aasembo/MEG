<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Report $report
 * @var \App\Model\Entity\MedicalCase $case
 * @var string $reportContent
 * @var \App\Model\Entity\Report|null $scientistReport
 */
$isEdit = !$report->isNew();
$this->assign('title', $isEdit ? 'Edit Medical Report' : 'Create Medical Report');
?>

<!-- Include Custom Rich Text Editor -->
<script src="<?php echo  $this->Url->build('/assets/js/rich-text-editor.js') ?>"></script>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-danger text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-file-medical-alt me-2"></i><?php echo  $isEdit ? 'Edit Medical Report' : 'Create Medical Report' ?>
                    </h2>
                    <?php if (isset($case)): ?>
                    <p class="mb-0">
                        <i class="fas fa-user-injured me-2"></i><?php echo  $this->PatientMask->displayName($case->patient_user) ?>
                        <span class="ms-3"><i class="fas fa-hospital me-2"></i><?php echo  h($case->hospital->name) ?></span>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php if ($isEdit): ?>
                            <?php echo  $this->Html->link(
                                '<i class="fas fa-eye me-1"></i>Preview',
                                ['action' => 'preview', $report->id],
                                ['class' => 'btn btn-light', 'escape' => false, 'target' => '_blank']
                            ); ?>
                        <?php endif; ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-arrow-left me-1"></i>Back',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-light', 'escape' => false]
                        ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow Context Alert -->
    <?php if (isset($scientistReport) && $scientistReport): ?>
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle fa-2x text-info"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h6 class="alert-heading mb-1">Workflow Context</h6>
                <p class="mb-2">
                    This doctor report is being created based on the scientific review (Report #<?php echo  h($scientistReport->id) ?>).
                    The content below has been pre-loaded from the scientist's report for your review and approval.
                </p>
                <small class="text-muted">
                    <i class="fas fa-route me-1"></i>
                    <strong>Workflow Progress:</strong> Technician Analysis → Scientific Review → <span class="text-danger fw-bold">Medical Approval</span>
                </small>
            </div>
        </div>
    </div>
    <?php elseif (!$isEdit): ?>
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h6 class="alert-heading mb-1">Direct Medical Report</h6>
                <p class="mb-2">
                    You are creating a direct medical report without a preceding scientific review. 
                    This report will represent the final medical approval step in the workflow.
                </p>
                <small class="text-muted">
                    <i class="fas fa-user-md me-1"></i>
                    <strong>Authority Level:</strong> Medical Approval (Final Step)
                </small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Edit Restriction Notice -->
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle fa-2x text-info"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h6 class="alert-heading mb-1 text-info">Report Creation Policy</h6>
                <p class="mb-2">
                    <strong>Only one doctor can be assigned to each case.</strong> As the assigned doctor, you can create and edit the final medical report for this case. 
                    You can view technician and scientist reports as part of the workflow hierarchy to make your final medical determination.
                </p>
                <small class="text-muted">
                    <i class="fas fa-user-md me-1"></i>
                    You are responsible for the final medical review and approval of this case.
                </small>
            </div>
        </div>
    </div>

    <?php echo  $this->Form->create($report, ['type' => 'post']) ?>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Report Content -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-edit me-2 text-danger"></i>Medical Report Content
                    </h5>
                </div>
                <div class="card-body p-4">
                    <?php echo  $this->Form->control('report_content', [
                        'type' => 'textarea',
                        'label' => false,
                        'value' => $reportContent,
                        'class' => 'form-control rich-text-editor',
                        'style' => 'min-height: 500px;',
                        'data-editor-height' => '500',
                        'placeholder' => 'Enter your medical report content here...'
                    ]) ?>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-lightbulb me-1"></i>
                            <strong>Tip:</strong> Use the rich text editor toolbar to format your medical report with headers, lists, tables, and medical terminology.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Report Settings -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-cog me-2 text-danger"></i>Report Settings
                    </h6>
                </div>
                <div class="card-body p-3">
                    <!-- Status Selection -->
                    <div class="mb-3">
                        <?php echo  $this->Form->control('status', [
                            'options' => [
                                'pending' => 'Pending (Draft)',
                                'reviewed' => 'Ready for Review',
                                'approved' => 'Approved & Final',
                            ],
                            'class' => 'form-select',
                            'label' => ['text' => 'Report Status', 'class' => 'form-label fw-semibold'],
                            'help' => 'Select the current status of this report'
                        ]) ?>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Status determines report availability and editing permissions
                        </div>
                    </div>

                    <!-- Confidence Score -->
                    <div class="mb-3">
                        <?php echo  $this->Form->control('confidence_score', [
                            'type' => 'number',
                            'min' => 0,
                            'max' => 100,
                            'step' => 0.1,
                            'class' => 'form-control',
                            'label' => ['text' => 'Confidence Score (%)', 'class' => 'form-label fw-semibold'],
                            'placeholder' => '95.5'
                        ]) ?>
                        <div class="form-text">
                            <i class="fas fa-chart-line me-1"></i>
                            Overall confidence in the analysis and findings (0-100%)
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Approval -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-stamp me-2 text-danger"></i>Medical Approval
                    </h6>
                </div>
                <div class="card-body p-3">
                    <?php echo  $this->Form->control('doctor_notes', [
                        'type' => 'textarea',
                        'label' => 'Doctor Notes & Approval Comments',
                        'class' => 'form-control',
                        'rows' => 4,
                        'placeholder' => 'Add medical approval notes, clinical observations, or recommendations...'
                    ]) ?>
                    
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            These notes will be recorded with your medical approval and can include clinical observations, recommendations, or administrative notes.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Case Information -->
            <?php if (isset($case)): ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-briefcase-medical me-2 text-danger"></i>Case Information
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">CASE ID</label>
                            <div class="fw-semibold">#<?php echo  h($case->id) ?></div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">PATIENT</label>
                            <div class="fw-semibold"><?php echo  $this->PatientMask->displayName($case->patient_user) ?></div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">HOSPITAL</label>
                            <div class="fw-semibold"><?php echo  h($case->hospital->name) ?></div>
                        </div>
                        
                        <?php if (isset($case->department)): ?>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">DEPARTMENT</label>
                            <div class="fw-semibold"><?php echo  h($case->department->name) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($case->case_date): ?>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">CASE DATE</label>
                            <div class="fw-semibold"><?php echo  h($case->case_date->format('M j, Y')) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Workflow Progress -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-route me-2 text-danger"></i>Workflow Progress
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="workflow-steps">
                        <div class="workflow-step completed">
                            <div class="step-icon bg-info text-white">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-title">Technician Analysis</div>
                                <small class="text-muted">Initial technical assessment</small>
                            </div>
                        </div>
                        
                        <div class="workflow-step completed">
                            <div class="step-icon bg-warning text-white">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-title">Scientific Review</div>
                                <small class="text-muted">Expert scientific analysis</small>
                            </div>
                        </div>
                        
                        <div class="workflow-step active">
                            <div class="step-icon bg-danger text-white">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-title">Medical Approval</div>
                                <small class="text-muted">Final medical authorization</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card border-0 shadow">
                <div class="card-body p-3">
                    <div class="d-grid gap-2">
                        <?php echo  $this->Form->button(
                            '<i class="fas fa-save me-2"></i>' . ($isEdit ? 'Update Report' : 'Create Report'),
                            [
                                'type' => 'submit',
                                'class' => 'btn btn-danger btn-lg fw-bold',
                                'escapeTitle' => false
                            ]
                        ) ?>
                        
                        <?php echo  $this->Html->link(
                            '<i class="fas fa-times me-2"></i>Cancel',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-secondary', 'escape' => false]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php echo  $this->Form->end() ?>
</div>

<style>
.workflow-steps {
    position: relative;
}

.workflow-step {
    display: flex;
    align-items-center;
    margin-bottom: 1rem;
    position: relative;
}

.workflow-step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    width: 2px;
    height: 20px;
    background-color: #dee2e6;
}

.workflow-step.completed::after,
.workflow-step.active::after {
    background-color: #28a745;
}

.step-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content-center;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.step-content {
    flex-grow: 1;
}

.step-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.workflow-step.active .step-title {
    color: #dc3545;
}

.rich-text-editor {
    border: 2px solid #dee2e6;
    border-radius: 0.375rem;
    transition: border-color 0.15s ease-in-out;
}

.rich-text-editor:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize rich text editor if available
    if (typeof initializeRichTextEditor === 'function') {
        initializeRichTextEditor('.rich-text-editor', {
            height: 500,
            placeholder: 'Enter your medical report content here...',
            toolbar: [
                'bold', 'italic', 'underline', 'strikethrough',
                '|', 'heading', 'quote', 'unordered-list', 'ordered-list',
                '|', 'link', 'table',
                '|', 'preview', 'fullscreen'
            ]
        });
    }
    
    // Auto-save functionality
    let autoSaveTimeout;
    const reportContent = document.querySelector('textarea[name="report_content"]');
    
    if (reportContent) {
        reportContent.addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(function() {
                // Auto-save logic could be implemented here
                console.log('Auto-saving report...');
            }, 30000); // Auto-save every 30 seconds
        });
    }
});
</script>