<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 */

$this->setLayout('admin');
$this->assign('title', 'Case #' . $case->id);
?>

<div class="cases view content" id="caseView<?php echo $case->id; ?>">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Case #<?php echo h($case->id); ?></h2>
        <div>
            <?php echo $this->Html->link(
                '<i class="fas fa-file-pdf me-1"></i>' . __('Download Report'),
                array('action' => 'downloadReport', $case->id),
                array('class' => 'btn btn-success me-2', 'escape' => false, 'target' => '_blank')
            ); ?>
            
            <?php echo $this->Html->link(
                '<i class="fas fa-arrow-left me-1"></i>' . __('Back to Cases'),
                array('action' => 'index'),
                array('class' => 'btn btn-outline-secondary', 'escape' => false)
            ); ?>
        </div>
    </div>

    <!-- Read-Only Notice -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Read-Only Access:</strong> As a hospital administrator, you can view case details but cannot make changes.
    </div>

    <div class="row">
        <!-- Case Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-medical me-1"></i> Case Information</h5>
                    <div>
                        <?php
                        $statusClass = 'secondary';
                        if ($case->status === 'draft') {
                            $statusClass = 'secondary';
                        } elseif ($case->status === 'assigned') {
                            $statusClass = 'info';
                        } elseif ($case->status === 'in_progress') {
                            $statusClass = 'warning';
                        } elseif ($case->status === 'review') {
                            $statusClass = 'primary';
                        } elseif ($case->status === 'completed') {
                            $statusClass = 'success';
                        } elseif ($case->status === 'cancelled') {
                            $statusClass = 'danger';
                        }
                        ?>
                        <span class="badge bg-<?php echo $statusClass ?> me-2">
                            <?php echo h(ucfirst(str_replace('_', ' ', $case->status))); ?>
                        </span>
                        
                        <?php
                        $priorityClass = 'secondary';
                        $priorityIcon = 'minus';
                        if ($case->priority === 'urgent') {
                            $priorityClass = 'danger';
                            $priorityIcon = 'exclamation-triangle';
                        } elseif ($case->priority === 'high') {
                            $priorityClass = 'warning';
                            $priorityIcon = 'arrow-up';
                        } elseif ($case->priority === 'medium') {
                            $priorityClass = 'info';
                            $priorityIcon = 'minus';
                        } elseif ($case->priority === 'low') {
                            $priorityClass = 'secondary';
                            $priorityIcon = 'arrow-down';
                        }
                        ?>
                        <span class="badge bg-<?php echo $priorityClass ?>">
                            <i class="fas fa-<?php echo $priorityIcon ?>"></i>
                            <?php echo h(ucfirst($case->priority)); ?>
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-semibold">Patient:</td>
                                    <td>
                                        <?php if (isset($case->patient_user)): ?>
                                            <?php echo h($case->patient_user->first_name . ' ' . $case->patient_user->last_name); ?>
                                            <br><small class="text-muted">ID: <?php echo h($case->patient_user->id); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">No patient assigned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Department:</td>
                                    <td>
                                        <?php if (isset($case->department)): ?>
                                            <i class="fas fa-building me-1 text-primary"></i>
                                            <?php echo h($case->department->name); ?>
                                            <?php if (isset($case->department->description) && $case->department->description): ?>
                                                <br><small class="text-muted"><?php echo h($case->department->description); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Sedation:</td>
                                    <td>
                                        <?php if (isset($case->sedation)): ?>
                                            <i class="fas fa-pills me-1 text-warning"></i>
                                            <?php echo h(isset($case->sedation->level) ? $case->sedation->level : 'N/A'); ?>
                                            <?php if (isset($case->sedation->type) && $case->sedation->type): ?>
                                                <span class="text-muted">(<?php echo h($case->sedation->type); ?>)</span>
                                            <?php endif; ?>
                                            <?php if (isset($case->sedation->risk_category) && $case->sedation->risk_category): ?>
                                                <br><small class="text-muted">Risk: <?php echo h($case->sedation->risk_category); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">None required</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Case Date:</td>
                                    <td><?php echo $case->date ? $case->date->format('F j, Y') : '<span class="text-muted">Not set</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Symptoms:</td>
                                    <td>
                                        <?php if (isset($case->symptoms) && $case->symptoms): ?>
                                            <?php echo h($case->symptoms); ?>
                                        <?php else: ?>
                                            <span class="text-muted">None specified</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-semibold">Created By:</td>
                                    <td>
                                        <?php echo h($case->user->first_name . ' ' . $case->user->last_name); ?>
                                        <br><small class="text-muted"><?php echo h($case->user->email); ?></small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Current User:</td>
                                    <td>
                                        <?php if (isset($case->current_user)): ?>
                                            <?php echo h($case->current_user->first_name . ' ' . $case->current_user->last_name); ?>
                                            <br><small class="text-muted"><?php echo h($case->current_user->email); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Created:</td>
                                    <td>
                                        <?php echo $case->created->format('F j, Y \a\t g:i A'); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Last Modified:</td>
                                    <td>
                                        <?php echo $case->modified->format('F j, Y \a\t g:i A'); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Case Notes -->
                    <?php if (isset($case->notes) && $case->notes): ?>
                    <div class="border-top pt-3 mt-3">
                        <h6><i class="fas fa-sticky-note me-1"></i> Case Notes</h6>
                        <div class="bg-light p-3 rounded">
                            <?php echo nl2br(h($case->notes)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Assigned Procedures -->
            <?php if (!empty($case->cases_exams_procedures)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-procedures me-1"></i> Assigned Procedures</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Exam</th>
                                    <th>Modality</th>
                                    <th>Procedure</th>
                                    <th>Status</th>
                                    <th>Documents</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($case->cases_exams_procedures as $cep): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo h(isset($cep->exams_procedure->exam->name) ? $cep->exams_procedure->exam->name : 'N/A'); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <i class="fas fa-microscope me-1"></i>
                                                <?php echo h(isset($cep->exams_procedure->exam->modality->name) ? $cep->exams_procedure->exam->modality->name : 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo h(isset($cep->exams_procedure->procedure->name) ? $cep->exams_procedure->procedure->name : 'N/A'); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php
                                            $statusBadgeClass = 'bg-secondary';
                                            if (isset($cep->status)) {
                                                if ($cep->status === 'pending') {
                                                    $statusBadgeClass = 'bg-warning';
                                                } elseif ($cep->status === 'in_progress') {
                                                    $statusBadgeClass = 'bg-primary';
                                                } elseif ($cep->status === 'completed') {
                                                    $statusBadgeClass = 'bg-success';
                                                } elseif ($cep->status === 'cancelled') {
                                                    $statusBadgeClass = 'bg-danger';
                                                }
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusBadgeClass; ?>">
                                                <?php echo h(isset($cep->status) ? ucfirst(str_replace('_', ' ', $cep->status)) : 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($cep->documents)): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-file me-1"></i>
                                                    <?php echo count($cep->documents); ?> files
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">No files</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Documents Section -->
            <?php if (!empty($case->documents)): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-folder-open me-2"></i>Case Documents
                    </h5>
                    <span class="badge bg-primary">
                        <?php echo count($case->documents); ?> <?php echo count($case->documents) === 1 ? 'Document' : 'Documents'; ?>
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 ps-4" style="width: 50px;"><i class="fas fa-file"></i></th>
                                    <th class="border-0" style="width: 250px;">Document Name</th>
                                    <th class="border-0" style="width: 200px;">Procedure</th>
                                    <th class="border-0">Uploaded</th>
                                    <th class="border-0 text-center" style="width: 130px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($case->documents as $index => $document): ?>
                                <tr class="document-row" style="transition: all 0.2s;">
                                    <td class="ps-4">
                                        <?php 
                                            $ext = !empty($document->original_filename) ? strtolower(pathinfo($document->original_filename, PATHINFO_EXTENSION)) : '';
                                            
                                            $iconColor = 'linear-gradient(135deg, #858796 0%, #60616f 100%)';
                                            if ($ext === 'pdf') {
                                                $iconColor = 'linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%)';
                                            } elseif (in_array($ext, array('doc', 'docx'))) {
                                                $iconColor = 'linear-gradient(135deg, #4e73df 0%, #224abe 100%)';
                                            } elseif (in_array($ext, array('ppt', 'pptx'))) {
                                                $iconColor = 'linear-gradient(135deg, #fd7e14 0%, #e56b0f 100%)';
                                            } elseif (in_array($ext, array('xls', 'xlsx'))) {
                                                $iconColor = 'linear-gradient(135deg, #1cc88a 0%, #17a673 100%)';
                                            } elseif (in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'))) {
                                                $iconColor = 'linear-gradient(135deg, #36b9cc 0%, #2c9faf 100%)';
                                            } elseif (in_array($ext, array('txt', 'log', 'csv'))) {
                                                $iconColor = 'linear-gradient(135deg, #858796 0%, #60616f 100%)';
                                            } elseif (in_array($ext, array('zip', 'rar', '7z'))) {
                                                $iconColor = 'linear-gradient(135deg, #f6c23e 0%, #dda20a 100%)';
                                            }
                                        ?>
                                        <div class="document-icon-wrapper d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px; border-radius: 8px; background: <?php echo $iconColor; ?>; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </td>
                                    <td style="max-width: 250px;">
                                        <div class="d-flex flex-column">
                                            <span class="text-dark fw-semibold text-truncate" 
                                                  style="display: block;"
                                                  title="<?php echo h($document->original_filename); ?>">
                                                <?php echo h($document->original_filename); ?>
                                            </span>
                                            <span class="text-muted small">
                                                <?php 
                                                    $filesize = isset($document->file_size) ? $document->file_size : 0;
                                                    if ($filesize > 1024 * 1024) {
                                                        echo number_format($filesize / (1024 * 1024), 2) . ' MB';
                                                    } elseif ($filesize > 1024) {
                                                        echo number_format($filesize / 1024, 2) . ' KB';
                                                    } else {
                                                        echo $filesize . ' bytes';
                                                    }
                                                ?>
                                                <span class="mx-1">•</span>
                                                <?php echo strtoupper($ext ? $ext : 'File'); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td style="max-width: 200px;">
                                        <?php if (!empty($document->cases_exams_procedure) && !empty($document->cases_exams_procedure->exams_procedure)): ?>
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-primary-subtle text-primary border border-primary" style="width: fit-content;">
                                                    <i class="fas fa-stethoscope me-1"></i>
                                                    <?php echo h(isset($document->cases_exams_procedure->exams_procedure->exam->name) ? $document->cases_exams_procedure->exams_procedure->exam->name : ''); ?>
                                                </span>
                                                <?php if (!empty($document->cases_exams_procedure->exams_procedure->procedure->name)): ?>
                                                    <span class="badge bg-info-subtle text-info border border-info mt-1" style="width: fit-content;">
                                                        <i class="fas fa-procedures me-1"></i>
                                                        <?php echo h($document->cases_exams_procedure->exams_procedure->procedure->name); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary">
                                                <i class="fas fa-file-medical me-1"></i> General
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <?php 
                                                $firstName = isset($document->user->first_name) ? $document->user->first_name : '';
                                                $lastName = isset($document->user->last_name) ? $document->user->last_name : '';
                                            ?>
                                            <span class="text-dark small fw-semibold"><?php echo h($firstName . ' ' . $lastName); ?></span>
                                            <span class="text-muted" style="font-size: 0.75rem;">
                                                <i class="far fa-calendar-alt me-1"></i><?php echo isset($document->created) ? $document->created->format('M j, Y') : 'N/A'; ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary preview-doc-btn"
                                                    data-document-id="<?php echo $document->id; ?>"
                                                    data-filename="<?php echo h($document->original_filename); ?>"
                                                    data-procedure="<?php
                                                        if (!empty($document->cases_exams_procedure) && !empty($document->cases_exams_procedure->exams_procedure)) {
                                                            echo h(isset($document->cases_exams_procedure->exams_procedure->exam->name) ? $document->cases_exams_procedure->exams_procedure->exam->name : '') . ' - ' . 
                                                                 h(isset($document->cases_exams_procedure->exams_procedure->procedure->name) ? $document->cases_exams_procedure->exams_procedure->procedure->name : '');
                                                        } else {
                                                            echo 'General Case Document';
                                                        }
                                                    ?>"
                                                    data-bs-toggle="tooltip"
                                                    title="Preview Document">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php echo $this->Html->link(
                                                '<i class="fas fa-download"></i>',
                                                array('action' => 'downloadDocument', $document->id),
                                                array(
                                                    'class' => 'btn btn-sm btn-outline-success', 
                                                    'escape' => false, 
                                                    'data-bs-toggle' => 'tooltip',
                                                    'title' => 'Download Document'
                                                )
                                            ); ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <style>
                .document-row:hover {
                    background-color: #f8f9fc !important;
                    transform: translateX(2px);
                }
                
                .document-icon-wrapper {
                    transition: transform 0.2s ease;
                }
                
                .document-row:hover .document-icon-wrapper {
                    transform: scale(1.05);
                }
                
                .badge.bg-primary-subtle {
                    font-weight: 500;
                    padding: 0.35rem 0.65rem;
                }
                
                .badge.bg-info-subtle {
                    font-weight: 500;
                    padding: 0.35rem 0.65rem;
                }
                
                .badge.bg-secondary-subtle {
                    font-weight: 500;
                    padding: 0.35rem 0.65rem;
                }
                
                .text-truncate {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    display: block;
                }
            </style>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Case Assignments -->
            <?php if (!empty($case->case_assignments)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-users me-1"></i> Assignments</h6>
                </div>
                <div class="card-body">
                    <?php foreach ($case->case_assignments as $assignment): ?>
                    <div class="border-start border-3 border-info ps-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <strong>Assigned to <?php echo h($assignment->assigned_to_user->first_name . ' ' . $assignment->assigned_to_user->last_name); ?></strong>
                                <?php if (isset($assignment->assigned_to_user->role) && $assignment->assigned_to_user->role): ?>
                                    <span class="badge bg-info ms-2">
                                        <?php echo h($this->Role->label($assignment->assigned_to_user->role->type)); ?>
                                    </span>
                                <?php endif; ?>
                                <br>
                                <small class="text-muted">
                                    By <?php echo h($assignment->user->first_name . ' ' . $assignment->user->last_name); ?>
                                    <?php if (isset($assignment->user->role) && $assignment->user->role): ?>
                                        <span class="badge bg-secondary ms-1">
                                            <?php echo h($this->Role->label($assignment->user->role->type)); ?>
                                        </span>
                                    <?php endif; ?>
                                    on <?php echo $assignment->timestamp->format('M j, Y \a\t g:i A'); ?>
                                </small>
                            </div>
                        </div>
                        <?php if (isset($assignment->notes) && $assignment->notes): ?>
                            <div class="mt-2">
                                <em><?php echo h($assignment->notes); ?></em>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Case Audit Trail -->
            <?php if (!empty($case->case_audits)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history me-1"></i> Activity History</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach (array_slice($case->case_audits, 0, 5) as $audit): ?>
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-circle text-primary" style="font-size: 8px;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong><?php echo h($audit->getChangeDescription()); ?></strong>
                                            <?php if (isset($audit->changed_by_user)): ?>
                                                <br><small class="text-muted">
                                                    by <?php echo h($audit->changed_by_user->first_name . ' ' . $audit->changed_by_user->last_name); ?>
                                                    <?php if (isset($audit->changed_by_user->role) && isset($audit->changed_by_user->role->type)): ?>
                                                        <?php echo $this->Role->badge($audit->changed_by_user->role->type, ['class' => 'ms-1']); ?>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted"><?php echo isset($audit->created) ? $audit->created->format('M d, Y g:i A') : 'N/A'; ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-fullscreen-lg-down">
        <div class="modal-content">
            <div class="modal-header">
                <div class="flex-grow-1">
                    <h5 class="modal-title mb-1" id="previewModalLabel">
                        <i class="fas fa-eye me-2"></i>Document Preview
                    </h5>
                    <div class="small text-muted">
                        <span id="previewFileName"></span>
                        <span id="previewProcedureName" class="ms-2"></span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Navigation Bar -->
            <div class="bg-light border-bottom px-3 py-2 d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="prevDocBtn" title="Previous Document">
                    <i class="fas fa-chevron-left me-1"></i>Previous
                </button>
                <span class="text-muted small" id="docCounter">Document 1 of 1</span>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="nextDocBtn" title="Next Document">
                    Next<i class="fas fa-chevron-right ms-1"></i>
                </button>
            </div>
            
            <div class="modal-body p-0" style="height: 70vh;">
                <!-- Loading Indicator -->
                <div id="previewLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading document...</p>
                </div>
                
                <!-- Error Display -->
                <div id="previewError" class="alert alert-danger m-4" style="display: none;">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <span id="previewErrorMessage"></span>
                </div>
                
                <!-- PDF/Image Direct View -->
                <iframe id="previewIframe" style="width: 100%; height: 100%; border: none; display: none;"></iframe>
                
                <!-- Office Documents Viewer -->
                <div id="previewOfficeContainer" style="width: 100%; height: 100%; display: none;">
                    <iframe id="previewOfficeIframe" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
                
                <!-- Text File Viewer -->
                <div id="previewTextContainer" style="width: 100%; height: 100%; overflow: auto; display: none;">
                    <pre id="previewTextContent" style="padding: 20px; white-space: pre-wrap; font-family: monospace;"></pre>
                </div>
                
                <!-- Unsupported Format Message -->
                <div id="previewUnsupported" class="text-center py-5" style="display: none;">
                    <i class="fas fa-file-excel fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Preview not available for this file type</h5>
                    <p class="text-muted">Please download the file to view it.</p>
                    <button type="button" class="btn btn-primary" id="previewDownloadBtn">
                        <i class="fas fa-download me-2"></i>Download File
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="previewDownloadLink" class="btn btn-primary" target="_blank">
                    <i class="fas fa-download me-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewModal = document.getElementById('previewModal');
    const modal = new bootstrap.Modal(previewModal);
    
    // Document preview elements
    const previewFileName = document.getElementById('previewFileName');
    const previewProcedureName = document.getElementById('previewProcedureName');
    const docCounter = document.getElementById('docCounter');
    const previewDownloadLink = document.getElementById('previewDownloadLink');
    const previewDownloadBtn = document.getElementById('previewDownloadBtn');
    const prevBtn = document.getElementById('prevDocBtn');
    const nextBtn = document.getElementById('nextDocBtn');
    
    // Preview containers
    const loadingDiv = document.getElementById('previewLoading');
    const errorDiv = document.getElementById('previewError');
    const errorMessage = document.getElementById('previewErrorMessage');
    const iframePreview = document.getElementById('previewIframe');
    const officeContainer = document.getElementById('previewOfficeContainer');
    const officeIframe = document.getElementById('previewOfficeIframe');
    const textContainer = document.getElementById('previewTextContainer');
    const textContent = document.getElementById('previewTextContent');
    const unsupportedDiv = document.getElementById('previewUnsupported');
    
    let allDocuments = [];
    let currentDocumentIndex = -1;
    
    // Collect all documents from the page (deduplicate by ID)
    const seenIds = new Set();
    document.querySelectorAll('.preview-doc-btn').forEach(function(btn, index) {
        const docId = btn.getAttribute('data-document-id');
        
        // Only add if we haven't seen this document ID yet
        if (!seenIds.has(docId)) {
            seenIds.add(docId);
            allDocuments.push({
                id: docId,
                filename: btn.getAttribute('data-filename'),
                procedure: btn.getAttribute('data-procedure'),
                button: btn
            });
        }
    });
    
    // Preview button click handler
    document.querySelectorAll('.preview-doc-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const documentId = this.getAttribute('data-document-id');
            const filename = this.getAttribute('data-filename');
            const procedure = this.getAttribute('data-procedure');
            
            currentDocumentIndex = allDocuments.findIndex(function(doc) {
                return doc.id === documentId;
            });
            
            previewDocument(documentId, filename, procedure);
            modal.show();
        });
    });
    
    // Previous document button
    prevBtn.addEventListener('click', function() {
        if (currentDocumentIndex > 0) {
            currentDocumentIndex--;
            const doc = allDocuments[currentDocumentIndex];
            previewDocument(doc.id, doc.filename, doc.procedure);
        }
    });
    
    // Next document button
    nextBtn.addEventListener('click', function() {
        if (currentDocumentIndex < allDocuments.length - 1) {
            currentDocumentIndex++;
            const doc = allDocuments[currentDocumentIndex];
            previewDocument(doc.id, doc.filename, doc.procedure);
        }
    });
    
    // Download button in unsupported section
    if (previewDownloadBtn) {
        previewDownloadBtn.addEventListener('click', function() {
            if (previewDownloadLink.href && previewDownloadLink.href !== '#') {
                window.open(previewDownloadLink.href, '_blank');
            }
        });
    }
    
    function previewDocument(documentId, filename, procedure) {
        // Update modal title and info
        previewFileName.textContent = filename;
        previewProcedureName.textContent = procedure ? '(' + procedure + ')' : '';
        
        // Update document counter
        docCounter.textContent = 'Document ' + (currentDocumentIndex + 1) + ' of ' + allDocuments.length;
        
        // Reset all containers
        hideAllContainers();
        loadingDiv.style.display = 'block';
        
        // Update navigation buttons
        prevBtn.disabled = currentDocumentIndex <= 0;
        nextBtn.disabled = currentDocumentIndex >= allDocuments.length - 1;
        
        // Fetch document info
        fetch('/admin/cases/view-document/' + documentId)
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Failed to load document');
                }
                return response.json();
            })
            .then(function(data) {
                loadingDiv.style.display = 'none';
                
                if (data.success) {
                    const fileUrl = data.url;
                    const ext = filename.split('.').pop().toLowerCase();
                    
                    // Set download links - use downloadDocument controller action for consistency and security
                    const downloadUrl = '<?php echo $this->Url->build(array('action' => 'downloadDocument')); ?>/' + documentId;
                    previewDownloadLink.href = downloadUrl;
                    // Note: download attribute removed - controller handles Content-Disposition header
                    
                    // Determine preview type based on file extension
                    if (ext === 'pdf') {
                        // PDF Preview
                        showPdfPreview(fileUrl);
                    } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'].indexOf(ext) !== -1) {
                        // Image Preview
                        showImagePreview(fileUrl);
                    } else if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].indexOf(ext) !== -1) {
                        // Office Document Preview
                        showOfficePreview(fileUrl, data.storage_type);
                    } else if (['txt', 'log', 'csv'].indexOf(ext) !== -1) {
                        // Text File Preview
                        showTextPreview(fileUrl);
                    } else {
                        // Unsupported file type
                        showUnsupported(fileUrl);
                    }
                } else {
                    showError(data.message ? data.message : 'Unable to load document preview');
                }
            })
            .catch(function(error) {
                loadingDiv.style.display = 'none';
                showError(error.message ? error.message : 'An error occurred while loading the document');
            });
    }
    
    function hideAllContainers() {
        errorDiv.style.display = 'none';
        iframePreview.style.display = 'none';
        officeContainer.style.display = 'none';
        textContainer.style.display = 'none';
        unsupportedDiv.style.display = 'none';
    }
    
    function showPdfPreview(url) {
        iframePreview.src = url;
        iframePreview.style.display = 'block';
    }
    
    function showImagePreview(url) {
        iframePreview.srcdoc = '<html><body style="margin:0;display:flex;align-items:center;justify-content:center;background:#000;height:100vh;"><img src="' + url + '" style="max-width:100%;max-height:100vh;object-fit:contain;"/></body></html>';
        iframePreview.style.display = 'block';
    }
    
    function showOfficePreview(url, storageType) {
        // For S3 documents, use proxy endpoint for Office Online Viewer
        if (storageType === 's3') {
            const documentId = allDocuments[currentDocumentIndex].id;
            const proxyUrl = encodeURIComponent(window.location.origin + '/admin/cases/proxy-document/' + documentId);
            officeIframe.src = 'https://view.officeapps.live.com/op/embed.aspx?src=' + proxyUrl;
        } else {
            // For local files, use direct URL
            const encodedUrl = encodeURIComponent(url);
            officeIframe.src = 'https://view.officeapps.live.com/op/embed.aspx?src=' + encodedUrl;
        }
        
        officeContainer.style.display = 'block';
    }
    
    function showTextPreview(url) {
        fetch(url)
            .then(function(response) {
                return response.text();
            })
            .then(function(text) {
                textContent.textContent = text;
                textContainer.style.display = 'block';
            })
            .catch(function() {
                showError('Unable to load text file content');
            });
    }
    
    function showUnsupported(url) {
        // Download link already set in previewDocument function
        unsupportedDiv.style.display = 'block';
    }
    
    function showError(message) {
        errorMessage.textContent = message;
        errorDiv.style.display = 'block';
    }
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
.timeline-item {
    position: relative;
    padding-left: 20px;
    border-left: 2px solid #e9ecef;
}

.timeline-item:last-child {
    border-left-color: transparent;
}

.timeline-item .fa-circle {
    position: absolute;
    left: -5px;
    top: 8px;
}
</style>
