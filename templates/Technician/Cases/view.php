<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 */

$this->setLayout('technician');
$this->assign('title', 'Case #' . $case->id);
?>

<div class="cases view content" id="caseView<?php echo $case->id; ?>">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Case #<?php echo h($case->id); ?></h2>
        <div>
            <?php echo $this->Html->link(
                '<i class="fas fa-file-pdf me-1"></i>' . __('Download Report'),
                ['action' => 'downloadReport', $case->id],
                ['class' => 'btn btn-success me-2', 'escape' => false, 'target' => '_blank']
            ); ?>
            
            <?php if (in_array($case->status, ['draft', 'assigned'])): ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-edit me-1"></i>' . __('Edit Case'),
                    ['action' => 'edit', $case->id],
                    ['class' => 'btn btn-primary me-2', 'escape' => false]
                ); ?>
                
                <?php echo $this->Html->link(
                    '<i class="fas fa-user-plus me-1"></i>' . __('Assign'),
                    ['action' => 'assign', $case->id],
                    ['class' => 'btn btn-info me-2', 'escape' => false]
                ); ?>
            <?php endif; ?>
            
            <?php echo $this->Html->link(
                '<i class="fas fa-arrow-left me-1"></i>' . __('Back to Cases'),
                ['action' => 'index'],
                ['class' => 'btn btn-outline-secondary', 'escape' => false]
            ); ?>
        </div>
    </div>

    <div class="row">
        <!-- Case Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-medical me-1"></i> Case Information</h5>
                    <div>
                        <span class="badge bg-<?php 
                            echo match($case->status) {
                                'draft' => 'secondary',
                                'assigned' => 'info',
                                'in_progress' => 'warning',
                                'review' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            };
                        ?> me-2">
                            <?php echo h(ucfirst(str_replace('_', ' ', $case->status))); ?>
                        </span>
                        
                        <span class="badge bg-<?php 
                            echo match($case->priority) {
                                'urgent' => 'danger',
                                'high' => 'warning',
                                'medium' => 'info',
                                'low' => 'secondary',
                                default => 'secondary'
                            };
                        ?>">
                            <i class="fas fa-<?php 
                                echo match($case->priority) {
                                    'urgent' => 'exclamation-triangle',
                                    'high' => 'arrow-up',
                                    'medium' => 'minus',
                                    'low' => 'arrow-down',
                                    default => 'minus'
                                };
                            ?>"></i>
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
                                        <?php if ($case->patient_user): ?>
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
                                        <?php if ($case->department): ?>
                                            <i class="fas fa-building me-1 text-primary"></i>
                                            <?php echo h($case->department->name); ?>
                                            <?php if ($case->department->description): ?>
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
                                        <?php if ($case->sedation): ?>
                                            <i class="fas fa-pills me-1 text-warning"></i>
                                            <?php echo h($case->sedation->level); ?>
                                            <?php if ($case->sedation->type): ?>
                                                <span class="text-muted">(<?php echo h($case->sedation->type); ?>)</span>
                                            <?php endif; ?>
                                            <?php if ($case->sedation->risk_category): ?>
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
                                        <?php if ($case->current_user): ?>
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
                    <?php if ($case->notes): ?>
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-procedures me-1"></i> Assigned Procedures</h5>
                    <?php if (in_array($case->status, ['draft', 'assigned', 'in_progress'])): ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-plus-circle me-1"></i>Assign Procedures',
                            ['action' => 'assignProcedures', $case->id],
                            ['class' => 'btn btn-sm btn-primary', 'escape' => false]
                        ); ?>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Procedure & Modality</th>
                                    <th>Status</th>
                                    <th>Documents</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($case->cases_exams_procedures as $cep): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?php echo h($cep->exams_procedure->exam->name ?? 'N/A'); ?></strong>
                                                <span class="badge bg-info ms-2">
                                                    <i class="fas fa-microscope me-1"></i>
                                                    <?php echo h($cep->exams_procedure->exam->modality->name ?? 'N/A'); ?>
                                                </span>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo h($cep->exams_procedure->procedure->name ?? 'N/A'); ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $cep->getStatusBadgeClass(); ?>">
                                                <?php echo h($cep->getStatusLabel()); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($cep->hasDocuments()): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-file me-1"></i>
                                                    <?php echo $cep->getDocumentCount(); ?> files
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">No files</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#documentsModal"
                                                        title="View Documents">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if (in_array($case->status, ['draft', 'assigned', 'in_progress'])): ?>
                                                    <button class="btn btn-outline-secondary btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#uploadModal"
                                                            data-procedure-id="<?php echo $cep->id; ?>"
                                                            title="Upload Document for this Procedure">
                                                        <i class="fas fa-upload"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Procedure Notes Row -->
                                    <?php if ($cep->notes): ?>
                                    <tr class="table-light">
                                        <td colspan="5">
                                            <small>
                                                <i class="fas fa-comment me-1"></i>
                                                <strong>Notes:</strong> <?php echo h($cep->notes); ?>
                                            </small>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-procedures me-1"></i> Assigned Procedures</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>No procedures assigned</strong><br>
                        This case doesn't have any procedures assigned yet. 
                        <?php if (in_array($case->status, ['draft', 'assigned'])): ?>
                            <?php echo $this->Html->link(
                                'Edit case',
                                ['action' => 'edit', $case->id],
                                ['class' => 'alert-link']
                            ); ?> to add procedures.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Documents -->
            <?php if (!empty($case->documents)): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-folder-open me-2"></i>Case Documents
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary">
                            <?php echo count($case->documents); ?> <?php echo count($case->documents) === 1 ? 'Document' : 'Documents'; ?>
                        </span>
                        <?php if (in_array($case->status, ['draft', 'assigned', 'in_progress'])): ?>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-upload me-1"></i>Upload Documents
                            </button>
                        <?php endif; ?>
                    </div>
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
                                            // Get file extension from original_filename
                                            $ext = !empty($document->original_filename) ? strtolower(pathinfo($document->original_filename, PATHINFO_EXTENSION)) : '';
                                        ?>
                                        <div class="document-icon-wrapper d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px; border-radius: 8px; background: <?php 
                                                echo match($ext) {
                                                    'pdf' => 'linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%)',
                                                    'doc', 'docx' => 'linear-gradient(135deg, #4e73df 0%, #224abe 100%)',
                                                    'ppt', 'pptx' => 'linear-gradient(135deg, #fd7e14 0%, #e56b0f 100%)',
                                                    'xls', 'xlsx' => 'linear-gradient(135deg, #1cc88a 0%, #17a673 100%)',
                                                    'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp' => 'linear-gradient(135deg, #36b9cc 0%, #2c9faf 100%)',
                                                    'txt', 'log', 'csv' => 'linear-gradient(135deg, #858796 0%, #60616f 100%)',
                                                    'zip', 'rar', '7z' => 'linear-gradient(135deg, #f6c23e 0%, #dda20a 100%)',
                                                    default => 'linear-gradient(135deg, #858796 0%, #60616f 100%)'
                                                };
                                        ?>;">
                                            <i class="<?php 
                                                echo match($ext) {
                                                    'pdf' => 'fas fa-file-pdf',
                                                    'doc', 'docx' => 'fas fa-file-word',
                                                    'ppt', 'pptx' => 'fas fa-file-powerpoint',
                                                    'xls', 'xlsx' => 'fas fa-file-excel',
                                                    'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp' => 'fas fa-file-image',
                                                    'txt', 'log', 'csv' => 'fas fa-file-alt',
                                                    'zip', 'rar', '7z' => 'fas fa-file-archive',
                                                    'mp4', 'avi', 'mov', 'wmv' => 'fas fa-file-video',
                                                    'mp3', 'wav', 'ogg' => 'fas fa-file-audio',
                                                    'html', 'css', 'js', 'php' => 'fas fa-file-code',
                                                    default => 'fas fa-file'
                                                };
                                            ?> text-white fs-5"></i>
                                        </div>
                                    </td>
                                    <td style="max-width: 250px;">
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark text-truncate" 
                                                  style="cursor: pointer;" 
                                                  title="<?php echo h($document->original_filename); ?>">
                                                <?php echo h($document->original_filename); ?>
                                            </span>
                                            <span class="text-muted small">
                                                <?php 
                                                    $filesize = !empty($document->file_size) ? $document->file_size : 0;
                                                    if ($filesize > 1024 * 1024) {
                                                        echo number_format($filesize / (1024 * 1024), 2) . ' MB';
                                                    } elseif ($filesize > 1024) {
                                                        echo number_format($filesize / 1024, 2) . ' KB';
                                                    } else {
                                                        echo $filesize . ' bytes';
                                                    }
                                                ?>
                                                <span class="mx-1">•</span>
                                                <?php echo strtoupper($ext ?? 'File'); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td style="max-width: 200px;">
                                        <?php if (!empty($document->cases_exams_procedure) && !empty($document->cases_exams_procedure->exams_procedure)): ?>
                                            <div class="d-flex flex-column">
                                                <span class="badge bg-primary-subtle text-primary border border-primary" style="width: fit-content;">
                                                    <i class="fas fa-stethoscope me-1"></i>
                                                    <?php echo h($document->cases_exams_procedure->exams_procedure->exam->name ?? ''); ?>
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
                                                $firstName = $document->user->first_name ?? '';
                                                $lastName = $document->user->last_name ?? '';
                                            ?>
                                            <span class="text-dark small fw-semibold"><?php echo h($firstName . ' ' . $lastName); ?></span>
                                            <span class="text-muted" style="font-size: 0.75rem;">
                                                <i class="far fa-calendar-alt me-1"></i><?php echo $document->created->format('M j, Y'); ?>
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
                                                            echo h($document->cases_exams_procedure->exams_procedure->exam->name ?? '') . ' - ' . 
                                                                 h($document->cases_exams_procedure->exams_procedure->procedure->name ?? '');
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
                                                ['action' => 'downloadDocument', $document->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-success', 
                                                    'escape' => false, 
                                                    'data-bs-toggle' => 'tooltip',
                                                    'title' => 'Download Document'
                                                ]
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
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    transition: transform 0.2s ease;
                }
                
                .document-row:hover .document-icon-wrapper {
                    transform: scale(1.05);
                }
                
                .avatar-circle {
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
                
                .btn-group .btn {
                    padding: 0.375rem 0.75rem;
                }
                
                /* Text truncation for long filenames */
                .text-truncate {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    display: block;
                }
                
                /* Ensure table cells respect max-width */
                .table td {
                    white-space: nowrap;
                }
                
                .table td > div {
                    max-width: 100%;
                }
                
                /* File type specific colors - for reference */
                /* PDF: Red (#ff6b6b → #ee5a6f) */
                /* Word: Blue (#4e73df → #224abe) */
                /* PowerPoint: Orange (#fd7e14 → #e56b0f) */
                /* Excel: Green (#1cc88a → #17a673) */
                /* Images: Cyan (#36b9cc → #2c9faf) */
                /* Text: Gray (#858796 → #60616f) */
                /* Archive: Yellow (#f6c23e → #dda20a) */
            </style>
            <?php endif; ?>

            <!-- Case Versions -->
            <?php if (!empty($case->case_versions)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-1"></i> Version History</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($case->case_versions as $version): ?>
                    <div class="d-flex align-items-center mb-2 <?php echo $version->id === $case->current_version_id ? 'bg-light p-2 rounded' : ''; ?>">
                        <div class="flex-grow-1">
                            <strong>Version <?php echo $version->version_number; ?></strong>
                            <?php if ($version->id === $case->current_version_id): ?>
                                <span class="badge bg-primary ms-2">Current</span>
                            <?php endif; ?>
                            <br>
                            <small class="text-muted">
                                Updated by <?php echo h($version->user->first_name . ' ' . $version->user->last_name); ?>
                                on <?php echo $version->timestamp->format('M j, Y \a\t g:i A'); ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Assignments -->
            <?php if (!empty($case->case_assignments)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-friends me-1"></i> Assignment History</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($case->case_assignments as $assignment): ?>
                    <div class="border-start border-3 border-info ps-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <div>
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
                        <?php if ($assignment->notes): ?>
                            <div class="mt-2">
                                <em><?php echo h($assignment->notes); ?></em>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-bolt me-1"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <?php if (in_array($case->status, ['draft', 'assigned'])): ?>
                        <div class="d-grid gap-2">
                            <?php echo $this->Html->link(
                                '<i class="fas fa-edit me-1"></i> Edit Case Details',
                                ['action' => 'edit', $case->id],
                                ['class' => 'btn btn-outline-primary', 'escape' => false]
                            ); ?>
                            
                            <?php echo $this->Html->link(
                                '<i class="fas fa-procedures me-1"></i> Assign Procedures',
                                ['action' => 'assignProcedures', $case->id],
                                ['class' => 'btn btn-outline-secondary', 'escape' => false]
                            ); ?>
                            
                            <?php 
                            // Check if case has an assignment
                            $hasAssignment = !empty($case->case_assignments);
                            $assignedUser = $hasAssignment ? $case->case_assignments[0]->assigned_to_user : null;
                            
                            if ($hasAssignment && $assignedUser): 
                                // Show reassign button with current scientist name
                            ?>
                                <div class="border border-warning rounded p-2 bg-warning bg-opacity-10">
                                    <div class="small text-muted mb-1">
                                        <i class="fas fa-user-check me-1"></i>Assigned to: <strong><?php echo h($assignedUser->first_name . ' ' . $assignedUser->last_name); ?></strong>
                                    </div>
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-exchange-alt me-1"></i> Change Assignment',
                                        ['action' => 'assign', $case->id],
                                        ['class' => 'btn btn-warning btn-sm w-100', 'escape' => false]
                                    ); ?>
                                </div>
                            <?php else: 
                                // Show initial promote button
                            ?>
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-level-up-alt me-1"></i> Promote to Scientist',
                                    ['action' => 'assign', $case->id],
                                    ['class' => 'btn btn-outline-success', 'escape' => false]
                                ); ?>
                            <?php endif; ?>
                            
                            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-upload me-1"></i> Upload Documents
                            </button>
                        </div>
                    <?php elseif (in_array($case->status, ['in_progress', 'review'])): ?>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-upload me-1"></i> Upload Documents
                            </button>
                            
                            <?php echo $this->Html->link(
                                '<i class="fas fa-eye me-1"></i> View Reports',
                                ['action' => 'reports', $case->id],
                                ['class' => 'btn btn-outline-primary', 'escape' => false]
                            ); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">
                            <i class="fas fa-lock me-1"></i>
                            Case is locked for editing in current status.
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Case Overview -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-1"></i> Case Overview</h6>
                </div>
                <div class="card-body">
                    <!-- Department Info -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-building text-primary me-2"></i>
                            <strong>Department</strong>
                        </div>
                        <div class="ps-4">
                            <?php if ($case->department): ?>
                                <?php echo h($case->department->name); ?>
                                <?php if ($case->department->description): ?>
                                    <br><small class="text-muted"><?php echo h($case->department->description); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Not assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Sedation Info -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-pills text-warning me-2"></i>
                            <strong>Sedation Requirements</strong>
                        </div>
                        <div class="ps-4">
                            <?php if ($case->sedation): ?>
                                <span class="badge bg-warning text-dark">
                                    <?php echo h($case->sedation->level); ?>
                                </span>
                                <?php if ($case->sedation->type): ?>
                                    <br><small class="text-muted">Type: <?php echo h($case->sedation->type); ?></small>
                                <?php endif; ?>
                                <?php if ($case->sedation->risk_category): ?>
                                    <br><small class="text-muted">Risk: <?php echo h($case->sedation->risk_category); ?></small>
                                <?php endif; ?>
                                <?php if ($case->sedation->monitoring_required): ?>
                                    <br><small class="text-success"><i class="fas fa-eye me-1"></i>Monitoring Required</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-success">
                                    <i class="fas fa-check me-1"></i>None required
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Procedure Count -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-procedures text-info me-2"></i>
                            <strong>Procedures</strong>
                        </div>
                        <div class="ps-4">
                            <?php 
                            $totalProcedures = count($case->cases_exams_procedures ?? []);
                            $completedProcedures = 0;
                            $inProgressProcedures = 0;
                            $pendingProcedures = 0;
                            
                            foreach ($case->cases_exams_procedures ?? [] as $cep) {
                                switch ($cep->status) {
                                    case 'completed':
                                        $completedProcedures++;
                                        break;
                                    case 'in_progress':
                                        $inProgressProcedures++;
                                        break;
                                    default:
                                        $pendingProcedures++;
                                }
                            }
                            ?>
                            
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h5 mb-1 text-primary"><?php echo $totalProcedures; ?></div>
                                    <div class="small text-muted">Total</div>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-1 text-success"><?php echo $completedProcedures; ?></div>
                                    <div class="small text-muted">Done</div>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-1 text-warning"><?php echo $pendingProcedures; ?></div>
                                    <div class="small text-muted">Pending</div>
                                </div>
                            </div>
                            
                            <?php if ($totalProcedures > 0): ?>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo ($completedProcedures / $totalProcedures) * 100; ?>%"></div>
                                    <div class="progress-bar bg-warning" style="width: <?php echo ($inProgressProcedures / $totalProcedures) * 100; ?>%"></div>
                                </div>
                                <small class="text-muted">
                                    <?php echo round(($completedProcedures / $totalProcedures) * 100); ?>% complete
                                </small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Modalities Used -->
                    <?php if (!empty($case->cases_exams_procedures)): ?>
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-microscope text-secondary me-2"></i>
                            <strong>Modalities</strong>
                        </div>
                        <div class="ps-4">
                            <?php 
                            $modalities = [];
                            foreach ($case->cases_exams_procedures as $cep) {
                                if ($cep->exams_procedure->exam->modality) {
                                    $modalities[$cep->exams_procedure->exam->modality->id] = $cep->exams_procedure->exam->modality->name;
                                }
                            }
                            ?>
                            <?php foreach ($modalities as $modalityName): ?>
                                <span class="badge bg-info me-1 mb-1">
                                    <i class="fas fa-microscope me-1"></i><?php echo h($modalityName); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Case Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-1"></i> Case Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="h5 mb-1"><?php echo count($case->case_versions ?? []); ?></div>
                            <div class="small text-muted">Versions</div>
                        </div>
                        <div class="col-6">
                            <div class="h5 mb-1"><?php echo count($case->case_assignments ?? []); ?></div>
                            <div class="small text-muted">Assignments</div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Days since creation:</span>
                            <strong><?php echo $case->created->diffInDays(\Cake\I18n\DateTime::now()); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Last activity:</span>
                            <strong><?php echo $case->modified->diffForHumans(); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Flow -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-route me-1"></i> Status Flow</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <?php 
                        $statuses = ['draft', 'assigned', 'in_progress', 'review', 'completed'];
                        $currentIndex = array_search($case->status, $statuses);
                        if ($currentIndex === false) $currentIndex = -1;
                        ?>
                        
                        <?php foreach ($statuses as $index => $status): ?>
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-2">
                                    <?php if ($index < $currentIndex): ?>
                                        <i class="fas fa-check-circle text-success"></i>
                                    <?php elseif ($index === $currentIndex): ?>
                                        <i class="fas fa-circle text-primary"></i>
                                    <?php else: ?>
                                        <i class="far fa-circle text-muted"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="<?php echo $index === $currentIndex ? 'fw-semibold' : ($index < $currentIndex ? 'text-success' : 'text-muted'); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Audit Trail -->
    <?php if (!empty($case->case_audits)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-1"></i> Change History</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($case->case_audits as $audit): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?php echo h($audit->getChangeDescription()); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            by <?php echo h($audit->changed_by_user->first_name . ' ' . $audit->changed_by_user->last_name); ?>
                                        </small>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo $audit->timestamp->format('M j, Y g:i A'); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <?php echo $this->Form->create(null, [
                'url' => ['action' => 'uploadDocument', $case->id],
                'type' => 'file',
                'id' => 'uploadForm'
            ]); ?>
            
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">
                    <i class="fas fa-upload me-2"></i>Upload Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <!-- Document Upload Instructions -->
                <div class="alert alert-primary mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Document Upload Guidelines:</strong>
                    <ul class="mb-0 mt-2 small">
                        <li><i class="fas fa-robot me-1 text-success"></i> <strong>Intelligent Analysis:</strong> Document type and procedure link auto-detected using OCR & NLP</li>
                        <li>Select the document type to categorize the file</li>
                        <li>Optionally link the document to a specific exam procedure</li>
                        <li>Documents without procedure links will be stored as general case documents</li>
                        <li>All documents are securely stored and accessible from this case</li>
                    </ul>
                </div>

                <!-- File Selection -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-file me-1"></i>Select File <span class="text-danger">*</span>
                    </label>
                    <?php echo $this->Form->control('document_file', [
                        'type' => 'file',
                        'class' => 'form-control',
                        'label' => false,
                        'required' => true,
                        'accept' => '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.txt,.dicom,.dcm',
                        'id' => 'documentFileInput'
                    ]); ?>
                    <div class="form-text">
                        <i class="fas fa-check-circle text-success me-1"></i>
                        Allowed: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, JPEG, PNG, GIF, TXT, DICOM. Max size: 50MB
                    </div>
                    <div id="filePreview" class="mt-2" style="display: none;">
                        <div class="alert alert-success py-2">
                            <i class="fas fa-file-check me-2"></i>
                            <strong>Selected:</strong> <span id="fileName"></span>
                            <span class="badge bg-success ms-2" id="fileSize"></span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Document Type Selection -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-tag me-1"></i>Document Type <span class="text-danger">*</span>
                            </label>
                            <?php echo $this->Form->control('document_type', [
                                'type' => 'select',
                                'options' => [
                                    'report' => 'Medical Report',
                                    'image' => 'Medical Image / Scan',
                                    'dicom' => 'DICOM Image',
                                    'consent' => 'Consent Form',
                                    'lab_result' => 'Lab Result',
                                    'prescription' => 'Prescription',
                                    'referral' => 'Referral Letter',
                                    'pathology' => 'Pathology Report',
                                    'radiology' => 'Radiology Report',
                                    'discharge_summary' => 'Discharge Summary',
                                    'other' => 'Other Document'
                                ],
                                'empty' => '-- Select document type --',
                                'class' => 'form-select',
                                'label' => false,
                                'required' => true,
                                'id' => 'documentTypeSelect'
                            ]); ?>
                            <div class="form-text">
                                <i class="fas fa-lightbulb text-warning me-1"></i>
                                Categorize document for easy retrieval
                            </div>
                        </div>
                    </div>
                    
                    <!-- Link to Procedure -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-link me-1"></i>Link to Procedure <span class="text-muted">(Optional)</span>
                            </label>
                            <?php 
                            $procedureOptions = [];
                            if (!empty($case->cases_exams_procedures)) {
                                foreach ($case->cases_exams_procedures as $cep) {
                                    $examName = $cep->exams_procedure->exam->name ?? 'Unknown';
                                    $procedureName = $cep->exams_procedure->procedure->name ?? 'Unknown';
                                    $modalityName = $cep->exams_procedure->exam->modality->name ?? '';
                                    $label = "{$examName} - {$procedureName}";
                                    if ($modalityName) {
                                        $label .= " ({$modalityName})";
                                    }
                                    $procedureOptions[$cep->id] = $label;
                                }
                            }
                            ?>
                            <?php echo $this->Form->control('cases_exams_procedure_id', [
                                'type' => 'select',
                                'options' => $procedureOptions,
                                'empty' => '-- Keep as general case document --',
                                'class' => 'form-select',
                                'label' => false,
                                'id' => 'procedureLinkSelect'
                            ]); ?>
                            <div class="form-text">
                                <i class="fas fa-info-circle text-info me-1"></i>
                                <?php if (empty($procedureOptions)): ?>
                                    No procedures assigned to this case yet
                                <?php else: ?>
                                    Link document to specific procedure or keep as general
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-comment-alt me-1"></i>Description <span class="text-muted">(Optional)</span>
                    </label>
                    <?php echo $this->Form->control('description', [
                        'type' => 'textarea',
                        'class' => 'form-control',
                        'label' => false,
                        'rows' => 3,
                        'placeholder' => 'Add any notes, findings, or context about this document...',
                        'id' => 'documentDescription'
                    ]); ?>
                    <div class="form-text">
                        <i class="fas fa-pen text-secondary me-1"></i>
                        Help others understand what this document contains
                    </div>
                </div>

                <!-- Storage Information -->
                <div class="alert alert-light border">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-shield-alt text-success me-2 mt-1"></i>
                        <div>
                            <strong>Secure Storage:</strong>
                            <div class="small text-muted mt-1">
                                Documents are encrypted and stored securely in: 
                                <code class="bg-white px-2 py-1">Meg_<?php echo str_pad((string)$case->id, 6, 'X', STR_PAD_LEFT); ?>/{Document_Type}/</code>
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="fas fa-check text-success me-1"></i> HIPAA compliant storage
                                <i class="fas fa-check text-success ms-2 me-1"></i> Audit trail enabled
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Summary (shown after selection) -->
                <div id="uploadSummary" class="alert alert-info" style="display: none;">
                    <strong><i class="fas fa-clipboard-check me-2"></i>Upload Summary:</strong>
                    <ul class="mb-0 mt-2 small" id="summaryList">
                        <!-- Populated by JavaScript -->
                    </ul>
                </div>
            </div>
            
            <div class="modal-footer">
                <div class="flex-grow-1">
                    <div id="uploadProgress" class="progress" style="display: none; height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: 0%"
                             id="uploadProgressBar">
                            <span id="uploadProgressText">Uploading...</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelUploadBtn">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <?php echo $this->Form->button(
                    '<i class="fas fa-upload me-1"></i><span id="uploadBtnText">Upload Document</span>',
                    [
                        'type' => 'submit', 
                        'class' => 'btn btn-primary', 
                        'escapeTitle' => false,
                        'id' => 'uploadSubmitBtn'
                    ]
                ); ?>
            </div>
            
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<!-- Documents List Modal -->
<div class="modal fade" id="documentsModal" tabindex="-1" aria-labelledby="documentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentsModalLabel">
                    <i class="fas fa-files me-2"></i>Case Documents
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (!empty($case->documents)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Uploaded</th>
                                    <th>Linked Procedure</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($case->documents as $document): ?>
                                    <tr>
                                        <td>
                                            <i class="<?php echo $document->getFileIcon(); ?> me-2"></i>
                                            <?php echo h($document->original_filename ?: 'Unknown file'); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo h($document->getDocumentTypeLabel()); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $document->getHumanFileSize(); ?></td>
                                        <td>
                                            <?php echo $document->uploaded_at->format('M j, Y'); ?>
                                            <br><small class="text-muted"><?php echo $document->uploaded_at->format('g:i A'); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($document->cases_exams_procedure): ?>
                                                <small>
                                                    <?php echo h($document->cases_exams_procedure->exams_procedure->exam->name ?? 'N/A'); ?>
                                                    <br><?php echo h($document->cases_exams_procedure->exams_procedure->procedure->name ?? 'N/A'); ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">General</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-outline-info btn-sm me-1 preview-doc-btn" 
                                                    data-document-id="<?php echo $document->id; ?>"
                                                    data-filename="<?php echo h($document->original_filename); ?>"
                                                    data-procedure="<?php 
                                                        if ($document->cases_exams_procedure) {
                                                            echo h($document->cases_exams_procedure->exams_procedure->exam->name ?? '') . ' - ' . 
                                                                 h($document->cases_exams_procedure->exams_procedure->procedure->name ?? '');
                                                        } else {
                                                            echo 'General Case Document';
                                                        }
                                                    ?>"
                                                    title="Preview">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php echo $this->Html->link(
                                                '<i class="fas fa-download"></i>',
                                                ['action' => 'downloadDocument', $document->id],
                                                [
                                                    'class' => 'btn btn-outline-primary btn-sm',
                                                    'title' => 'Download',
                                                    'escape' => false
                                                ]
                                            ); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-upload fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No documents uploaded</h5>
                        <p class="text-muted">Upload documents using the "Upload Documents" button.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
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
                <a href="#" id="previewDownloadLink" class="btn btn-primary" target="_blank" download>
                    <i class="fas fa-download me-2"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Handle procedure-specific document upload and enhanced UI interactions
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Elements
    const uploadModal = document.getElementById('uploadModal');
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('documentFileInput');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const documentTypeSelect = document.getElementById('documentTypeSelect');
    const procedureLinkSelect = document.getElementById('procedureLinkSelect');
    const documentDescription = document.getElementById('documentDescription');
    const uploadSummary = document.getElementById('uploadSummary');
    const summaryList = document.getElementById('summaryList');
    const uploadSubmitBtn = document.getElementById('uploadSubmitBtn');
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadProgressBar = document.getElementById('uploadProgressBar');
    const uploadProgressText = document.getElementById('uploadProgressText');
    const cancelUploadBtn = document.getElementById('cancelUploadBtn');
    const uploadBtnText = document.getElementById('uploadBtnText');
    
    // Handle procedure-specific upload button clicks
    const uploadButtons = document.querySelectorAll('[data-procedure-id]');
    uploadButtons.forEach(button => {
        button.addEventListener('click', function() {
            const procedureId = this.getAttribute('data-procedure-id');
            if (procedureLinkSelect && procedureId) {
                // Pre-select the procedure in the modal
                procedureLinkSelect.value = procedureId;
                updateUploadSummary();
            }
        });
    });
    
    // File input change handler
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Show file preview
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                filePreview.style.display = 'block';
                
                // Auto-analyze document using AI/OCR
                analyzeDocumentAI(file);
                
                // Update summary
                updateUploadSummary();
            } else {
                filePreview.style.display = 'none';
                uploadSummary.style.display = 'none';
            }
        });
    }
    
    // Document type change handler
    if (documentTypeSelect) {
        documentTypeSelect.addEventListener('change', updateUploadSummary);
    }
    
    // Procedure link change handler
    if (procedureLinkSelect) {
        procedureLinkSelect.addEventListener('change', updateUploadSummary);
    }
    
    // Description change handler
    if (documentDescription) {
        documentDescription.addEventListener('input', updateUploadSummary);
    }
    
    // Update upload summary
    function updateUploadSummary() {
        if (!fileInput || !fileInput.files.length) {
            uploadSummary.style.display = 'none';
            return;
        }
        
        const file = fileInput.files[0];
        const docType = documentTypeSelect.options[documentTypeSelect.selectedIndex].text;
        const linkedProc = procedureLinkSelect.value ? 
            procedureLinkSelect.options[procedureLinkSelect.selectedIndex].text : 
            'General case document (not linked to procedure)';
        const hasDesc = documentDescription.value.trim() !== '';
        
        summaryList.innerHTML = `
            <li><strong>File:</strong> ${file.name} (${formatFileSize(file.size)})</li>
            <li><strong>Type:</strong> ${docType}</li>
            <li><strong>Link:</strong> ${linkedProc}</li>
            <li><strong>Description:</strong> ${hasDesc ? 'Provided' : 'None'}</li>
        `;
        
        uploadSummary.style.display = 'block';
    }
    
    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    // Analyze document using AI/OCR
    function analyzeDocumentAI(file) {
        // Show analyzing indicator
        if (uploadSummary) {
            uploadSummary.className = 'alert alert-warning';
            uploadSummary.style.display = 'block';
            summaryList.innerHTML = '<li><i class="fas fa-spinner fa-spin me-2"></i>Analyzing document with OCR & NLP...</li>';
        }
        
        // Prepare form data
        const formData = new FormData();
        formData.append('file', file);
        
        // Get CSRF token from meta tag or cookie
        const csrfToken = document.querySelector('meta[name="csrfToken"]')?.content || 
                         document.cookie.split('; ').find(row => row.startsWith('csrfToken='))?.split('=')[1];
        
        // Make API call to analyze document
        fetch('<?php echo $this->Url->build(['action' => 'analyzeDocument', $case->id]); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Apply AI suggestions
                applyAISuggestions(data);
                
                // Update summary to show AI results
                uploadSummary.className = 'alert alert-success';
                const confidencePercent = Math.round((data.confidence || 0.5) * 100);
                summaryList.innerHTML = `
                    <li><i class="fas fa-check-circle me-2"></i><strong>Analysis Complete</strong></li>
                    <li>Detected type: <strong>${getDocumentTypeLabel(data.detected_type)}</strong> (${confidencePercent}% confidence)</li>
                    <li>Method: ${data.method || 'OCR + NLP analysis'}</li>
                    ${data.suggested_procedure_id ? '<li><i class="fas fa-link me-2"></i>Suggested procedure link applied</li>' : '<li>No specific procedure suggested</li>'}
                    ${data.suggested_description ? '<li><i class="fas fa-file-alt me-2"></i>Description auto-generated from document content</li>' : ''}
                `;
            } else {
                // AI analysis failed, use fallback
                uploadSummary.className = 'alert alert-info';
                summaryList.innerHTML = `
                    <li><i class="fas fa-info-circle me-2"></i>Automated analysis unavailable</li>
                    <li>Please manually select document type and procedure link</li>
                    ${data.error ? '<li class="small text-muted">' + data.error + '</li>' : ''}
                `;
            }
        })
        .catch(error => {
            console.error('Analysis Error:', error);
            uploadSummary.className = 'alert alert-info';
            summaryList.innerHTML = `
                <li><i class="fas fa-exclamation-triangle me-2"></i>Could not connect to analysis service</li>
                <li>Please manually select document type and procedure link</li>
            `;
        });
    }
    
    // Apply AI suggestions to form
    function applyAISuggestions(data) {
        // Set document type if detected
        if (data.detected_type && documentTypeSelect) {
            documentTypeSelect.value = data.detected_type;
            // Trigger change event to update UI
            documentTypeSelect.dispatchEvent(new Event('change'));
        }
        
        // Set procedure link if suggested
        if (data.suggested_procedure_id && procedureLinkSelect) {
            procedureLinkSelect.value = data.suggested_procedure_id;
            // Trigger change event to update UI
            procedureLinkSelect.dispatchEvent(new Event('change'));
        }
        
        // Set description if suggested
        if (data.suggested_description && documentDescription) {
            documentDescription.value = data.suggested_description;
            // Trigger input event to update UI
            documentDescription.dispatchEvent(new Event('input'));
        }
        
        // Update upload summary
        updateUploadSummary();
    }
    
    // Get document type label
    function getDocumentTypeLabel(type) {
        const labels = {
            'report': 'Medical Report',
            'image': 'Medical Image / Scan',
            'dicom': 'DICOM Image',
            'consent': 'Consent Form',
            'lab_result': 'Lab Result',
            'prescription': 'Prescription',
            'referral': 'Referral Letter',
            'pathology': 'Pathology Report',
            'radiology': 'Radiology Report',
            'discharge_summary': 'Discharge Summary',
            'other': 'Other Document'
        };
        return labels[type] || 'Unknown';
    }
    
    // Combined form validation and submission handler
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            // Get fresh references to ensure we have the latest values
            const currentFileInput = document.getElementById('documentFileInput');
            const currentDocTypeSelect = document.getElementById('documentTypeSelect');
            
            const file = currentFileInput && currentFileInput.files && currentFileInput.files.length > 0 
                ? currentFileInput.files[0] 
                : null;
            const docType = currentDocTypeSelect ? currentDocTypeSelect.value : '';
            
            console.log('Form validation:', { 
                hasFileInput: !!currentFileInput, 
                filesLength: currentFileInput ? currentFileInput.files.length : 0,
                hasFile: !!file, 
                docType: docType,
                fileName: file ? file.name : 'none'
            });
            
            // Validate file selection
            if (!file) {
                e.preventDefault();
                e.stopPropagation();
                alert('Please select a file to upload.');
                return false;
            }
            
            // Validate document type
            if (!docType) {
                e.preventDefault();
                e.stopPropagation();
                alert('Please select a document type.');
                return false;
            }
            
            // Check file size (50MB limit)
            const maxSize = 50 * 1024 * 1024; // 50MB in bytes
            if (file.size > maxSize) {
                e.preventDefault();
                e.stopPropagation();
                alert('File size exceeds the maximum limit of 50MB. Please select a smaller file.');
                return false;
            }
            
            // Validation passed - show progress UI
            console.log('Validation passed, submitting form');
            uploadProgress.style.display = 'block';
            uploadSubmitBtn.disabled = true;
            cancelUploadBtn.disabled = true;
            uploadBtnText.textContent = 'Uploading...';
            
            // Simulate progress (actual progress would require AJAX)
            let progress = 0;
            const progressInterval = setInterval(function() {
                progress += 10;
                if (progress <= 90) {
                    uploadProgressBar.style.width = progress + '%';
                    uploadProgressText.textContent = 'Uploading... ' + progress + '%';
                } else {
                    clearInterval(progressInterval);
                    uploadProgressText.textContent = 'Processing...';
                }
            }, 200);
            
            // Allow form submission
            return true;
        });
    }
    
    // Reset form when modal is closed
    if (uploadModal) {
        uploadModal.addEventListener('hidden.bs.modal', function () {
            if (uploadForm) {
                uploadForm.reset();
            }
            if (filePreview) {
                filePreview.style.display = 'none';
            }
            if (uploadSummary) {
                uploadSummary.style.display = 'none';
            }
            if (uploadProgress) {
                uploadProgress.style.display = 'none';
                uploadProgressBar.style.width = '0%';
            }
            if (uploadSubmitBtn) {
                uploadSubmitBtn.disabled = false;
            }
            if (cancelUploadBtn) {
                cancelUploadBtn.disabled = false;
            }
            if (uploadBtnText) {
                uploadBtnText.textContent = 'Upload Document';
            }
        });
    }
    
    // ===== DOCUMENT PREVIEW FUNCTIONALITY =====
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    const previewIframe = document.getElementById('previewIframe');
    const previewOfficeContainer = document.getElementById('previewOfficeContainer');
    const previewOfficeIframe = document.getElementById('previewOfficeIframe');
    const previewTextContainer = document.getElementById('previewTextContainer');
    const previewTextContent = document.getElementById('previewTextContent');
    const previewUnsupported = document.getElementById('previewUnsupported');
    const previewLoading = document.getElementById('previewLoading');
    const previewError = document.getElementById('previewError');
    const previewErrorMessage = document.getElementById('previewErrorMessage');
    const previewFileName = document.getElementById('previewFileName');
    const previewProcedureName = document.getElementById('previewProcedureName');
    const previewDownloadLink = document.getElementById('previewDownloadLink');
    const previewDownloadBtn = document.getElementById('previewDownloadBtn');
    const prevDocBtn = document.getElementById('prevDocBtn');
    const nextDocBtn = document.getElementById('nextDocBtn');
    const docCounter = document.getElementById('docCounter');
    
    // Build array of all documents for navigation (scoped to current case only)
    let allDocuments = [];
    let currentDocIndex = 0;
    
    const caseViewContainer = document.querySelector('#caseView<?php echo $case->id; ?>');
    const previewButtons = caseViewContainer ? caseViewContainer.querySelectorAll('.preview-doc-btn') : [];
    
    previewButtons.forEach((button, index) => {
        allDocuments.push({
            id: button.getAttribute('data-document-id'),
            filename: button.getAttribute('data-filename'),
            procedure: button.getAttribute('data-procedure'),
            index: index
        });
    });
    
    // Handle preview button clicks using event delegation to support dynamically loaded content
    // This handles both buttons in the main case view AND in the documents modal
    function handlePreviewClick(e) {
        // Check if the clicked element or its parent is a preview button
        const button = e.target.closest('.preview-doc-btn');
        if (button) {
            e.preventDefault();
            e.stopPropagation();
            
            const documentId = button.getAttribute('data-document-id');
            const filename = button.getAttribute('data-filename');
            const procedure = button.getAttribute('data-procedure');
            
            console.log('Preview button clicked:', { documentId, filename, procedure });
            
            // Rebuild documents array to include all visible buttons (case view + modal)
            const allButtons = document.querySelectorAll('#caseView<?php echo $case->id; ?> .preview-doc-btn, #documentsModal .preview-doc-btn');
            console.log('Found preview buttons:', allButtons.length);
            
            allDocuments = [];
            allButtons.forEach((btn, idx) => {
                const btnDocId = btn.getAttribute('data-document-id');
                allDocuments.push({
                    id: btnDocId,
                    filename: btn.getAttribute('data-filename'),
                    procedure: btn.getAttribute('data-procedure'),
                    index: idx
                });
                if (btnDocId === documentId) {
                    currentDocIndex = idx;
                    console.log('Setting current index to:', idx);
                }
            });
            
            console.log('Total documents in array:', allDocuments.length, 'Current index:', currentDocIndex);
            previewDocument(documentId, filename, procedure);
        }
    }
    
    // Attach to case view container
    if (caseViewContainer) {
        caseViewContainer.addEventListener('click', handlePreviewClick);
    }
    
    // Also attach to documents modal
    const documentsModal = document.getElementById('documentsModal');
    if (documentsModal) {
        documentsModal.addEventListener('click', handlePreviewClick);
        console.log('Event listener attached to documentsModal');
    }
    
    // Navigation button handlers
    prevDocBtn.addEventListener('click', function() {
        if (currentDocIndex > 0) {
            currentDocIndex--;
            const doc = allDocuments[currentDocIndex];
            previewDocument(doc.id, doc.filename, doc.procedure);
        }
    });
    
    nextDocBtn.addEventListener('click', function() {
        if (currentDocIndex < allDocuments.length - 1) {
            currentDocIndex++;
            const doc = allDocuments[currentDocIndex];
            previewDocument(doc.id, doc.filename, doc.procedure);
        }
    });
    
    // Update navigation UI
    function updateNavigationUI() {
        // Update counter
        docCounter.textContent = `Document ${currentDocIndex + 1} of ${allDocuments.length}`;
        
        // Enable/disable buttons
        prevDocBtn.disabled = currentDocIndex === 0;
        nextDocBtn.disabled = currentDocIndex === allDocuments.length - 1;
    }
    
    // Preview document function
    function previewDocument(documentId, filename, procedure) {
        // Show modal and loading state
        previewModal.show();
        resetPreviewModal();
        previewFileName.textContent = filename;
        previewProcedureName.innerHTML = procedure ? 
            '<i class="fas fa-folder-open me-1"></i>' + procedure : '';
        previewLoading.style.display = 'block';
        
        // Update navigation
        updateNavigationUI();
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrfToken"]')?.content || 
                         document.cookie.split('; ').find(row => row.startsWith('csrfToken='))?.split('=')[1];
        
        // Fetch document info
        fetch('<?php echo $this->Url->build(['action' => 'viewDocument']); ?>/' + documentId, {
            method: 'GET',
            headers: {
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            previewLoading.style.display = 'none';
            
            if (data.success && data.document) {
                const doc = data.document;
                const fileUrl = doc.url;
                const fileType = doc.type.toLowerCase();
                const fileExt = doc.filename.split('.').pop().toLowerCase();
                
                // For Office documents, determine the best URL to use
                const proxyUrl = '<?php echo $this->Url->build(['action' => 'proxyDocument', '_full' => true]); ?>/' + documentId;
                
                // Check if S3 is enabled from backend
                const isS3Enabled = <?php echo json_encode($isS3Enabled ?? false); ?>;
                
                // For Office documents:
                // - If S3 enabled: Use presigned S3 URL directly (Microsoft Office Viewer can access it)
                // - If local storage on localhost: Show download message
                // - If local storage on public URL: Use proxy URL
                const isLocalhost = window.location.hostname === 'localhost' || 
                                   window.location.hostname === '127.0.0.1' ||
                                   window.location.hostname.includes('meg.www') ||
                                   window.location.hostname.match(/^192\.168\.|^10\.|^172\.(1[6-9]|2\d|3[01])\./);
                
                const useDirectIframe = !isS3Enabled && isLocalhost && (['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'].includes(fileExt));
                
                // For Office docs: Use presigned S3 URL if S3 enabled, otherwise use proxy (local storage)
                const displayUrl = (['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'].includes(fileExt))
                    ? (isS3Enabled ? fileUrl : proxyUrl)
                    : fileUrl;
                
                // Set download link (use downloadDocument controller action for consistency and security)
                const downloadUrl = '<?php echo $this->Url->build(['action' => 'downloadDocument']); ?>/' + documentId;
                previewDownloadLink.href = downloadUrl;
                previewDownloadBtn.onclick = () => window.open(downloadUrl, '_blank');
                
                // Determine how to display based on file type
                if (fileType.includes('pdf') || fileExt === 'pdf') {
                    // PDF - direct iframe
                    showPdfPreview(fileUrl);
                } else if (fileType.includes('image') || ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileExt)) {
                    // Image - direct iframe
                    showImagePreview(fileUrl);
                } else if (['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'].includes(fileExt)) {
                    if (useDirectIframe) {
                        // Local storage on local network - show download message
                        showOfficeLocalPreview(fileUrl, doc.filename);
                    } else {
                        // S3 enabled or production - use proxy URL with Microsoft Office Online Viewer
                        showOfficePreview(displayUrl, fileExt, documentId);
                    }
                } else if (fileType.includes('text') || ['txt', 'log', 'csv'].includes(fileExt)) {
                    // Text files - fetch and display
                    showTextPreview(fileUrl);
                } else {
                    // Unsupported - show download option
                    showUnsupportedPreview();
                }
            } else {
                showPreviewError(data.error || 'Failed to load document');
            }
        })
        .catch(error => {
            previewLoading.style.display = 'none';
            showPreviewError('Network error: ' + error.message);
        });
    }
    
    function resetPreviewModal() {
        previewIframe.style.display = 'none';
        previewOfficeContainer.style.display = 'none';
        previewTextContainer.style.display = 'none';
        previewUnsupported.style.display = 'none';
        previewError.style.display = 'none';
        previewIframe.src = '';
        previewOfficeIframe.src = '';
        previewTextContent.textContent = '';
    }
    
    function showPdfPreview(url) {
        previewIframe.src = url;
        previewIframe.style.display = 'block';
    }
    
    function showImagePreview(url) {
        // Create image wrapper in iframe
        previewIframe.srcdoc = `
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { 
                        margin: 0; 
                        padding: 20px; 
                        display: flex; 
                        justify-content: center; 
                        align-items: center;
                        min-height: 100vh;
                        background: #f5f5f5;
                    }
                    img { 
                        max-width: 100%; 
                        height: auto; 
                        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                        background: white;
                        padding: 10px;
                    }
                </style>
            </head>
            <body>
                <img src="${url}" alt="Document Preview">
            </body>
            </html>
        `;
        previewIframe.style.display = 'block';
    }
    
    function showOfficePreview(url, ext, documentId) {
        // URL is already the proxy URL from our server
        // Microsoft Office Online Viewer needs a publicly accessible URL
        const encodedUrl = encodeURIComponent(url);
        
        // Microsoft Office Online Viewer (primary)
        const msViewerUrl = `https://view.officeapps.live.com/op/embed.aspx?src=${encodedUrl}`;
        
        // Google Docs Viewer (fallback)
        const googleViewerUrl = `https://docs.google.com/viewer?url=${encodedUrl}&embedded=true`;
        
        // Try Microsoft Office Viewer first
        previewOfficeIframe.src = msViewerUrl;
        previewOfficeContainer.style.display = 'block';
        
        // Add load event listener to detect success
        let viewerLoaded = false;
        const loadTimeout = setTimeout(() => {
            if (!viewerLoaded) {
                console.log('Microsoft Office Viewer timeout, trying Google Docs Viewer');
                previewOfficeIframe.src = googleViewerUrl;
            }
        }, 5000); // 5 second timeout
        
        previewOfficeIframe.onload = () => {
            viewerLoaded = true;
            clearTimeout(loadTimeout);
        };
        
        // Error handler for fallback
        previewOfficeIframe.onerror = () => {
            clearTimeout(loadTimeout);
            console.log('Microsoft Office Viewer failed, trying Google Docs Viewer');
            previewOfficeIframe.src = googleViewerUrl;
        };
    }
    
    function showOfficeLocalPreview(url, filename) {
        // For local storage on local network - show download option with message
        previewUnsupported.innerHTML = `
            <div class="alert alert-info">
                <h5><i class="fas fa-info-circle me-2"></i>Office Document Preview (Local Storage)</h5>
                <p class="mb-3">Office document preview requires publicly accessible URLs for Microsoft Office Online Viewer. Since files are stored locally and accessed on a local network, please download the file to view it.</p>
                <div class="d-grid gap-2">
                    <a href="${url}" class="btn btn-primary" download>
                        <i class="fas fa-download me-2"></i>Download ${filename}
                    </a>
                    <a href="${url}" class="btn btn-outline-primary" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i>Open in New Tab
                    </a>
                </div>
                <hr class="my-3">
                <p class="small mb-0">
                    <strong>Note:</strong> If you enable S3 storage, Office documents will automatically preview using Microsoft Office Online Viewer.
                </p>
            </div>
        `;
        previewUnsupported.style.display = 'block';
    }
    
    function showTextPreview(url) {
        fetch(url)
            .then(response => response.text())
            .then(text => {
                previewTextContent.textContent = text;
                previewTextContainer.style.display = 'block';
            })
            .catch(error => {
                showPreviewError('Failed to load text content: ' + error.message);
            });
    }
    
    function showUnsupportedPreview() {
        previewUnsupported.style.display = 'block';
    }
    
    function showPreviewError(message) {
        previewErrorMessage.textContent = message;
        previewError.style.display = 'block';
    }
    
    // Clean up when preview modal is closed
    document.getElementById('previewModal').addEventListener('hidden.bs.modal', function() {
        resetPreviewModal();
    });
    
    // Keyboard navigation for preview modal
    document.addEventListener('keydown', function(e) {
        // Only handle if preview modal is open
        if (document.getElementById('previewModal').classList.contains('show')) {
            if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                // Previous document
                e.preventDefault();
                if (currentDocIndex > 0) {
                    prevDocBtn.click();
                }
            } else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                // Next document
                e.preventDefault();
                if (currentDocIndex < allDocuments.length - 1) {
                    nextDocBtn.click();
                }
            }
        }
    });
    // ===== END DOCUMENT PREVIEW FUNCTIONALITY =====
});
</script>

<style>
.timeline {
    position: relative;
}

.timeline-item {
    position: relative;
    padding-left: 2rem;
    padding-bottom: 1rem;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 1.5rem;
    bottom: -1rem;
    width: 2px;
    background-color: #dee2e6;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 0.25rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.table-borderless td {
    padding: 0.25rem 0.5rem 0.25rem 0;
}

.border-start {
    border-left-width: 3px !important;
}
</style>