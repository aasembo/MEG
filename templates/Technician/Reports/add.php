<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Report $report
 * @var \App\Model\Entity\MedicalCase $case
 * @var string $reportContent
 */
$isEdit = !$report->isNew();
$this->assign('title', $isEdit ? 'Edit MEG Report' : 'Create MEG Report');
?>

<!-- Include Custom Rich Text Editor -->
<script src="<?= $this->Url->build('/assets/js/rich-text-editor.js') ?>"></script>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-file-medical-alt me-2"></i><?= $isEdit ? 'Edit MEG Report' : 'Create MEG Report' ?>
                    </h2>
                    <?php if (isset($case)): ?>
                    <p class="mb-0">
                        <i class="fas fa-user-injured me-2"></i><?= $this->PatientMask->displayName($case->patient_user) ?>
                        <span class="ms-3"><i class="fas fa-hospital me-2"></i><?= h($case->hospital->name) ?></span>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php if ($isEdit): ?>
                            <?= $this->Html->link(
                                '<i class="fas fa-eye me-1"></i>Preview',
                                ['action' => 'preview', $report->id],
                                ['class' => 'btn btn-light', 'escape' => false, 'target' => '_blank']
                            ); ?>
                        <?php endif; ?>
                        
                        <?= $this->Html->link(
                            '<i class="fas fa-arrow-left me-1"></i>Back',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-light', 'escape' => false]
                        ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Multiple Reports Policy Notice -->
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle fa-2x text-info"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h6 class="alert-heading mb-1 text-info">Report Creation Policy</h6>
                <p class="mb-2">
                    <strong>Only one technician can be assigned to each case.</strong> As the assigned technician, you can create and edit the technician report for this case. 
                    You can view reports created by scientists and doctors for the same case as part of the workflow hierarchy.
                </p>
                <small class="text-muted">
                    <i class="fas fa-user-cog me-1"></i>
                    This allows collaborative technical analysis while maintaining individual accountability for each technician's work.
                </small>
            </div>
        </div>
    </div>

    <?= $this->Form->create($report, ['type' => 'post', 'class' => 'report-form']) ?>
    
    <div class="row">
        <!-- Main Content Area -->
        <div class="col-lg-8">
            <!-- Case Information Card -->
            <?php if (isset($case)): ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-file-medical me-2 text-primary"></i>Case Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-semibold">Case ID:</td>
                                    <td><span class="badge bg-primary"><?= h($case->id) ?></span></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Patient:</td>
                                    <td><?= $this->PatientMask->displayName($case->patient_user) ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Hospital:</td>
                                    <td><?= h($case->hospital->name) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-semibold">Department:</td>
                                    <td><?= $case->department ? h($case->department->name) : '<span class="text-muted">Not assigned</span>' ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Case Date:</td>
                                    <td><?= $case->date ? $case->date->format('F j, Y') : '<span class="text-muted">Not set</span>' ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Procedures:</td>
                                    <td>
                                        <?php if (!empty($case->cases_exams_procedures)): ?>
                                            <span class="badge bg-info"><?= count($case->cases_exams_procedures) ?> assigned</span>
                                        <?php else: ?>
                                            <span class="text-muted">None assigned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Report Content Card -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-edit me-2 text-primary"></i>MEG Report Content
                        </h5>
                        <span class="badge bg-secondary">Rich Text Editor</span>
                    </div>
                </div>
                <div class="card-body bg-white">
                    <!-- Quick Format Buttons -->
                    <div class="mb-3">
                        
                        <div class="alert alert-info border-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Pro Tip:</strong> The report content is pre-populated from case data. Use the rich text editor to format and customize the report structure.
                        </div>
                    </div>
                    
                    <!-- Rich Text Editor -->
                    <div class="form-group">
                        <label class="form-label fw-semibold mb-3">
                            <i class="fas fa-file-alt me-1"></i>Complete Report Content <span class="text-danger">*</span>
                        </label>
                        <textarea 
                            name="report_content" 
                            class="rich-text-editor form-control" 
                            rows="25" 
                            data-height="700px" 
                            placeholder="Complete MEG clinical report content will be generated here..."
                            required
                        ><?= $reportContent ?? '' ?></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Edit the complete report using the rich text editor. All patient and case data is dynamically populated.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Internal Notes Card -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-warning bg-opacity-10 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-sticky-note me-2 text-warning"></i>Internal Notes
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="alert alert-warning border-0 mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Internal Use Only:</strong> These notes are for technician reference and will not appear in the final report.
                    </div>
                    
                    <?= $this->Form->control('technician_notes', [
                        'type' => 'textarea',
                        'rows' => 4,
                        'class' => 'form-control',
                        'label' => ['text' => 'Technician Notes', 'class' => 'form-label fw-semibold'],
                        'placeholder' => 'Add internal notes, quality control comments, technical issues, workflow notes, etc...'
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Report Settings Card -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-cog me-2 text-primary"></i>Report Settings
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <!-- Status Selection -->
                    <div class="mb-3">
                        <?= $this->Form->control('status', [
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
                        <?= $this->Form->control('confidence_score', [
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

            <!-- Quick Actions Card -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bolt me-2 text-primary"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center">
                            <i class="fas fa-save me-2"></i><?= $isEdit ? 'Update Report' : 'Create Report' ?>
                        </button>
                        
                        <?php if ($isEdit): ?>
                            <?= $this->Html->link(
                                '<i class="fas fa-eye me-2"></i>Preview & Download',
                                ['action' => 'preview', $report->id],
                                [
                                    'class' => 'btn btn-success d-flex align-items-center justify-content-center',
                                    'escape' => false,
                                    'target' => '_blank'
                                ]
                            ); ?>
                        <?php endif; ?>
                        
                        <?= $this->Html->link(
                            '<i class="fas fa-times me-2"></i>Cancel',
                            ['action' => 'index'],
                            [
                                'class' => 'btn btn-outline-secondary d-flex align-items-center justify-content-center',
                                'escape' => false
                            ]
                        ); ?>
                    </div>
                </div>
            </div>

            <!-- Medical Approval Workflow -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-route me-2 text-primary"></i>Workflow Progress
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="workflow-steps">
                        <div class="workflow-step active">
                            <div class="step-icon bg-info text-white">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-title">Technician Analysis</div>
                                <small class="text-muted">Initial technical assessment</small>
                            </div>
                        </div>
                        
                        <div class="workflow-step pending">
                            <div class="step-icon bg-light text-muted">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-title">Scientific Review</div>
                                <small class="text-muted">Expert scientific analysis</small>
                            </div>
                        </div>
                        
                        <div class="workflow-step pending">
                            <div class="step-icon bg-light text-muted">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div class="step-content">
                                <div class="step-title">Medical Approval</div>
                                <small class="text-muted">Final medical authorization</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3 p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            <span class="small">
                                <strong>Current Stage:</strong> You are performing the initial technical analysis. 
                                This report will proceed through scientific review and medical approval.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Guidelines Card -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-book-open me-2 text-primary"></i>Report Guidelines
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="small">
                        <div class="mb-3">
                            <strong class="text-primary">
                                <i class="fas fa-check-circle me-1"></i>Structure
                            </strong>
                            <ul class="list-unstyled ms-3 mt-1 text-muted">
                                <li>• Patient demographics</li>
                                <li>• Case history & indication</li>
                                <li>• Technical parameters</li>
                                <li>• Procedures performed</li>
                                <li>• MEG findings</li>
                                <li>• Clinical conclusions</li>
                            </ul>
                        </div>
                        
                        <div class="mb-3">
                            <strong class="text-success">
                                <i class="fas fa-lightbulb me-1"></i>Best Practices
                            </strong>
                            <ul class="list-unstyled ms-3 mt-1 text-muted">
                                <li>• Use medical terminology appropriately</li>
                                <li>• Include confidence levels</li>
                                <li>• Reference technical standards</li>
                                <li>• Provide clear recommendations</li>
                            </ul>
                        </div>
                        
                        <div class="mb-0">
                            <strong class="text-info">
                                <i class="fas fa-shield-alt me-1"></i>Compliance
                            </strong>
                            <ul class="list-unstyled ms-3 mt-1 text-muted">
                                <li>• HIPAA compliant formatting</li>
                                <li>• Professional medical standards</li>
                                <li>• Institution guidelines</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Stats Card (if editing) -->
            <?php if ($isEdit): ?>
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>Report Stats
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="h6 mb-1"><?= $report->created ? $report->created->diffInDays() : 0 ?></div>
                            <div class="small text-muted">Days Old</div>
                        </div>
                        <div class="col-6">
                            <div class="h6 mb-1"><?= $report->modified ? $report->modified->diffForHumans() : 'Never' ?></div>
                            <div class="small text-muted">Last Edit</div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Created:</span>
                            <strong><?= $report->created ? $report->created->format('M j, Y') : 'N/A' ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Status:</span>
                            <span class="badge bg-<?php 
                                echo match($report->status ?? 'pending') {
                                    'approved' => 'success',
                                    'reviewed' => 'warning',
                                    default => 'secondary'
                                };
                            ?>"><?= h($report->status ?? 'pending') ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?= $this->Form->end() ?>
</div>

<!-- Enhanced JavaScript for Modern UI -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quick format button functionality
    const quickButtons = {
        'insertPatientHeader': {
            icon: 'user',
            content: `<div class="patient-header">
<h3 style="text-align: center; text-decoration: underline; margin-bottom: 20px;">Magnetoencephalography Report (MEG)</h3>
<p><strong>Name:</strong> Last, First</p>
<p><strong>Date of Birth:</strong> XX/XX/XXXX</p>
<p><strong>MRN:</strong> XXXXX <strong>-FIN:</strong> XXXXX</p>
<p><strong>Date of Study:</strong> XX/XX/XXXX</p>
<p><strong>Referring Physician:</strong> Doctor Name, MD</p>
<p><strong>MEG ID:</strong> case_XXXXXX</p>
</div>`
        },
        'insertTechnicalNote': {
            icon: 'cogs',
            content: `<div class="technical-section">
<h4><strong><em>MSI Technical Note:</em></strong></h4>
<p>Spontaneous and evoked brain activity were recorded as the patient rested quietly in the supine position. Cortical electrical fields were recorded using a whole-head 306-channel gradiometer/magnetometer system (Neuromag Triux, Elekta, Inc.). Electroencephalography (EEG) was recorded simultaneously using the international 10-20 electrode placement system.</p>
<p>The source distributions were analyzed utilizing an equivalent current dipole model with the best fit judged by statistical criteria of goodness of fit, confidence volume, and signal to noise ratio.</p>
</div>`
        },
        'insertProcedures': {
            icon: 'list',
            content: `<div class="procedures-section">
<h4><strong>The following procedures were performed:</strong></h4>
<ul>
    <li>Localization of interictal/ictal discharges</li>
    <li>Localization of sensory cortex</li>
    <li>Localization of primary auditory cortex</li>
    <li>Localization of motor cortex</li>
    <li>Receptive language lateralization</li>
    <li>Expressive language lateralization</li>
    <li>Localization of visual cortex</li>
</ul>
</div>`
        },
        'insertFindings': {
            icon: 'search',
            content: `<div class="findings-section">
<h4><strong>MEG FINDINGS:</strong></h4>
<p><strong><em>Spontaneous Activity:</em></strong></p>
<p>The patient showed normal background brain activity during the MEG recording session. No abnormal epileptiform discharges were identified during the recording period.</p>
<p><strong><em>Evoked Responses:</em></strong></p>
<p>Somatosensory evoked fields (SEF), auditory evoked fields (AEF), and visual evoked fields (VEF) were successfully recorded and localized to their respective cortical areas.</p>
</div>`
        },
        'insertConclusion': {
            icon: 'check-circle',
            content: `<div class="conclusion-section">
<h4><strong>IMPRESSION:</strong></h4>
<p>1. No epileptiform activity was detected during the MEG recording session.</p>
<p>2. Successful localization of primary sensory, motor, auditory, and visual cortical areas.</p>
<p>3. Language lateralization study completed successfully.</p>
<p>4. Results are consistent with normal cortical functional organization.</p>
</div>`
        }
    };

    // Attach click handlers to quick format buttons
    Object.keys(quickButtons).forEach(buttonId => {
        const button = document.getElementById(buttonId);
        if (button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                insertContentAtCursor(quickButtons[buttonId].content);
                
                // Visual feedback
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check me-1"></i>Inserted!';
                this.classList.add('btn-success');
                
                setTimeout(() => {
                    this.innerHTML = originalHtml;
                    this.classList.remove('btn-success');
                }, 1500);
            });
        }
    });

    // Function to insert content at cursor position in rich text editor
    function insertContentAtCursor(content) {
        const textarea = document.querySelector('textarea[name="report_content"]');
        if (textarea) {
            // Check if rich text editor is active
            const richTextContainer = textarea.parentNode.querySelector('.rich-text-editor-container');
            if (richTextContainer) {
                const contentArea = richTextContainer.querySelector('.rich-text-content');
                if (contentArea) {
                    // Insert into rich text editor
                    const selection = window.getSelection();
                    if (selection.rangeCount > 0) {
                        const range = selection.getRangeAt(0);
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = content;
                        range.insertNode(tempDiv);
                        
                        // Move cursor after inserted content
                        range.setStartAfter(tempDiv);
                        range.collapse(true);
                        selection.removeAllRanges();
                        selection.addRange(range);
                        
                        // Trigger input event
                        contentArea.dispatchEvent(new Event('input', { bubbles: true }));
                    } else {
                        // No selection, append to end
                        contentArea.innerHTML += content;
                        contentArea.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    
                    // Focus the editor
                    contentArea.focus();
                }
            } else {
                // Fallback for plain textarea
                const cursorPos = textarea.selectionStart;
                const textBefore = textarea.value.substring(0, cursorPos);
                const textAfter = textarea.value.substring(cursorPos);
                
                // Convert HTML to plain text for textarea
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = content;
                const plainContent = tempDiv.textContent || tempDiv.innerText || '';
                
                textarea.value = textBefore + plainContent + textAfter;
                textarea.selectionStart = textarea.selectionEnd = cursorPos + plainContent.length;
                textarea.focus();
            }
        }
    }

    // Form validation
    const form = document.querySelector('.report-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const contentTextarea = document.querySelector('textarea[name="report_content"]');
            const status = document.querySelector('select[name="status"]');
            
            // Check if content is provided
            if (!contentTextarea.value.trim()) {
                e.preventDefault();
                alert('Please provide report content before saving.');
                contentTextarea.focus();
                return false;
            }
            
            // Show loading state on submit button
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
            }
        });
    }

    // Auto-save functionality (optional)
    let autoSaveTimeout;
    const contentTextarea = document.querySelector('textarea[name="report_content"]');
    
    if (contentTextarea) {
        contentTextarea.addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                // Auto-save logic could be implemented here
                console.log('Auto-save triggered');
            }, 30000); // Auto-save after 30 seconds of inactivity
        });
    }

    // Initialize rich text editor with enhanced settings
    setTimeout(() => {
        const richTextEditors = document.querySelectorAll('.rich-text-editor');
        richTextEditors.forEach(editor => {
            // The editor initialization is handled by rich-text-editor.js
            // We can add custom styling here
            if (editor.parentNode.querySelector('.rich-text-editor-container')) {
                const container = editor.parentNode.querySelector('.rich-text-editor-container');
                container.style.minHeight = '600px';
                container.style.border = '2px solid #e9ecef';
                container.style.borderRadius = '8px';
            }
        });
    }, 500);
});
</script>

<style>
/* Modern Bootstrap 5 Styling */
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn {
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.alert {
    border: none;
    border-radius: 10px;
    border-left: 4px solid;
}

.alert-info {
    background-color: #e7f3ff;
    border-left-color: #0d6efd;
    color: #084298;
}

.alert-warning {
    background-color: #fff3cd;
    border-left-color: #ffc107;
    color: #664d03;
}

.badge {
    font-weight: 500;
    border-radius: 6px;
}

.table-borderless td {
    border: none;
    padding: 0.5rem 0.75rem;
}

.fw-semibold {
    font-weight: 600;
}

/* Rich text editor enhancements */
.rich-text-editor-container {
    border-radius: 8px !important;
    overflow: hidden;
}

.rich-text-editor-toolbar {
    background: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
    padding: 10px;
}

.rich-text-content {
    padding: 20px;
    min-height: 500px;
    line-height: 1.6;
    font-family: 'Times New Roman', serif;
}

/* Status-based styling */
.status-pending { border-left-color: #6c757d; }
.status-reviewed { border-left-color: #ffc107; }
.status-approved { border-left-color: #198754; }

/* Loading states */
.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .container-fluid {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 0.5rem;
    }
}

/* Animation for quick buttons */
@keyframes buttonSuccess {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.btn.btn-success {
    animation: buttonSuccess 0.5s ease;
}

/* Workflow Progress Styles */
.workflow-steps {
    position: relative;
}

.workflow-step {
    display: flex;
    align-items: center;
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
    justify-content: center;
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
    color: #0d6efd;
}
</style>