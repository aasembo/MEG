<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 */

$this->assign('title', 'Case #' . $case->id);
?>

<div class="container-fluid px-4 py-4" id="caseView<?php echo $case->id; ?>">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-file-medical me-2"></i>Case #<?php echo h($case->id); ?>
                    </h2>
                    <p class="mb-1">
                        <?php if ($case->patient_user): ?>
                            <i class="fas fa-user-injured me-2"></i><?php echo $this->PatientMask->displayName($case->patient_user); ?>
                        <?php endif; ?>
                    </p>
                    <p class="mb-0 text-warning small">
                        <i class="fas fa-shield-alt me-1"></i>Administrative view - Read-only access
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php if (!empty($existingReports)): ?>
                            <?php $firstReport = $existingReports->first(); ?>
                            <?php if ($firstReport): ?>
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-file-medical-alt me-1"></i>View Report',
                                    ['controller' => 'Reports', 'action' => 'view', $firstReport->id],
                                    ['class' => 'btn btn-light', 'escape' => false]
                                ); ?>
                            <?php endif; ?>
                        <?php endif; ?>
                        
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

    <div class="row">
        <!-- Case Details -->
        <div class="col-lg-8">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-file-medical me-2 text-warning"></i>Case Information
                        </h5>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <?php 
                            // Get all role-specific statuses for permission checks
                            $technicianStatus = $case->technician_status ?? 'draft';
                            $scientistStatus = $case->scientist_status ?? 'draft';
                            $doctorStatus = $case->doctor_status ?? 'draft';
                            ?>
                            
                            <!-- Role-Based Statuses -->
                            <div class="d-flex flex-wrap gap-1">
                                <?php echo $this->Status->roleBadge($case, 'technician'); ?>
                                <?php echo $this->Status->roleBadge($case, 'scientist'); ?>
                                <?php echo $this->Status->roleBadge($case, 'doctor'); ?>
                            </div>
                            
                            <!-- Divider -->
                            <span class="text-muted">|</span>
                            
                            <!-- Priority Badge -->
                            <?php echo $this->Status->priorityBadge($case->priority); ?>
                        </div>
                    </div>
                </div>
                <div class="card-body bg-white">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-semibold">Patient:</td>
                                    <td>
                                        <?php if ($case->patient_user): ?>
                                            <?php echo $this->PatientMask->displayName($case->patient_user); ?>
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
                                            <i class="fas fa-building me-1 text-warning"></i>
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
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-procedures me-2 text-warning"></i>Assigned Procedures
                    </h5>
                    <span class="badge rounded-pill bg-warning text-dark">
                        <?php echo count($case->cases_exams_procedures); ?> 
                        <?php echo count($case->cases_exams_procedures) === 1 ? 'Procedure' : 'Procedures'; ?>
                    </span>
                </div>
                <div class="card-body bg-white p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted" style="width: 70%;">Procedure & Modality</th>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted text-end" style="width: 30%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($case->cases_exams_procedures as $cep): ?>
                                    <tr>
                                        <td class="py-3">
                                            <div class="d-flex align-items-start justify-content-between">
                                                <div class="flex-grow-1">
                                                    <div class="mb-2">
                                                        <h6 class="mb-1 fw-bold text-dark d-inline"><?php echo h($cep->exams_procedure->exam->name ?? 'N/A'); ?></h6>
                                                        <span class="badge bg-warning text-dark ms-2" style="font-size: 0.7rem;">
                                                            <i class="fas fa-microscope me-1"></i><?php echo h($cep->exams_procedure->exam->modality->name ?? 'N/A'); ?>
                                                        </span>
                                                    </div>
                                                    <div class="text-muted" style="font-size: 0.9rem;">
                                                        <i class="fas fa-check-circle me-1 text-info"></i>
                                                        <?php echo h($cep->exams_procedure->procedure->name ?? 'N/A'); ?>
                                                    </div>
                                                    <?php if ($cep->hasDocuments()): ?>
                                                        <div class="mt-2">
                                                            <span class="badge bg-success text-white" style="font-size: 0.75rem;" 
                                                                  role="button" 
                                                                  data-bs-toggle="modal" 
                                                                  data-bs-target="#procedureDocumentsModal<?php echo $cep->id; ?>">
                                                                <i class="fas fa-paperclip me-1"></i>
                                                                <?php echo $cep->getDocumentCount(); ?> 
                                                                <?php echo $cep->getDocumentCount() === 1 ? 'Document' : 'Documents'; ?>
                                                            </span>
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if ($cep->notes): ?>
                                                        <div class="mt-2 p-2 bg-light rounded border-start border-3 border-info">
                                                            <small class="text-muted">
                                                                <i class="fas fa-comment me-1"></i>
                                                                <strong>Notes:</strong> <?php echo h($cep->notes); ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 text-end align-top">
                                            <span class="badge rounded-pill <?php echo $cep->getStatusBadgeClass(); ?>" style="font-size: 0.8rem; padding: 0.5rem 0.75rem;">
                                                <?php echo h($cep->getStatusLabel()); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-procedures me-2 text-warning"></i>Assigned Procedures
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>No procedures assigned</strong><br>
                        This case doesn't have any procedures assigned yet.
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Documents -->
            <?php if (!empty($case->documents)): ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-folder-open me-2 text-warning"></i>Case Documents
                    </h5>
                    <span class="badge bg-warning text-dark">
                        <?php echo count($case->documents); ?> <?php echo count($case->documents) === 1 ? 'Document' : 'Documents'; ?>
                    </span>
                </div>
                <div class="card-body bg-white p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted ps-4" style="width: 60px;"><i class="fas fa-file"></i></th>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted">Document Details</th>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted text-center" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($case->documents as $index => $document): ?>
                                <tr>
                                    <td class="ps-4 pe-3 align-top pt-3">
                                        <?php 
                                            // Get file extension from original_filename
                                            $ext = !empty($document->original_filename) ? strtolower(pathinfo($document->original_filename, PATHINFO_EXTENSION)) : '';
                                            
                                            // Determine background color based on file type
                                            $bgClass = match($ext) {
                                                'pdf' => 'bg-danger',
                                                'doc', 'docx' => 'bg-primary',
                                                'ppt', 'pptx' => 'bg-warning',
                                                'xls', 'xlsx' => 'bg-success',
                                                'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp' => 'bg-info',
                                                'txt', 'log', 'csv' => 'bg-secondary',
                                                'zip', 'rar', '7z' => 'bg-dark',
                                                default => 'bg-secondary'
                                            };
                                            
                                            // Format file size
                                            $filesize = !empty($document->file_size) ? $document->file_size : 0;
                                            if ($filesize > 1024 * 1024) {
                                                $filesize_formatted = number_format($filesize / (1024 * 1024), 2) . ' MB';
                                            } elseif ($filesize > 1024) {
                                                $filesize_formatted = number_format($filesize / 1024, 2) . ' KB';
                                            } else {
                                                $filesize_formatted = $filesize . ' bytes';
                                            }
                                            
                                            // Get uploader name
                                            $firstName = $document->user->first_name ?? '';
                                            $lastName = $document->user->last_name ?? '';
                                            $uploaderName = trim($firstName . ' ' . $lastName);
                                            
                                            // Get exam/procedure names
                                            $exam_name = $document->cases_exams_procedure->exams_procedure->exam->name ?? '';
                                            $procedure_name = $document->cases_exams_procedure->exams_procedure->procedure->name ?? '';
                                        ?>
                                        <div class="d-flex align-items-center justify-content-center rounded <?php echo $bgClass; ?>" 
                                             style="width: 42px; height: 42px;">
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
                                    <td class="pe-3 py-3">
                                        <div class="d-flex flex-column gap-2">
                                            <!-- Document Name and Size -->
                                            <div>
                                                <span class="fw-bold text-dark d-block mb-1" style="font-size: 0.95rem;">
                                                    <?php echo h($document->original_filename); ?>
                                                </span>
                                                <span class="text-muted" style="font-size: 0.8rem;">
                                                    <?php echo $filesize_formatted; ?>
                                                    <span class="mx-1">•</span>
                                                    <?php echo strtoupper($ext ?? 'File'); ?>
                                                </span>
                                            </div>
                                            
                                            <!-- Exam and Procedure -->
                                            <div>
                                                <?php if (!empty($document->cases_exams_procedure) && !empty($document->cases_exams_procedure->exams_procedure)): ?>
                                                    <span class="badge rounded-pill bg-warning text-dark me-1" style="font-size: 0.75rem;">
                                                        <i class="fas fa-stethoscope me-1"></i>
                                                        <?php echo h($exam_name); ?>
                                                    </span>
                                                    <?php if (!empty($procedure_name)): ?>
                                                        <span class="badge rounded-pill bg-info text-white" style="font-size: 0.75rem;">
                                                            <i class="fas fa-procedures me-1"></i>
                                                            <?php echo h($procedure_name); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge rounded-pill bg-secondary" style="font-size: 0.75rem;">
                                                        <i class="fas fa-file-medical me-1"></i> General
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- Uploaded By and Date -->
                                            <div class="text-muted" style="font-size: 0.8rem;">
                                                <i class="fas fa-user me-1"></i>
                                                <span class="fw-semibold"><?php echo h($uploaderName); ?></span>
                                                <span class="mx-2">•</span>
                                                <i class="far fa-calendar-alt me-1"></i><?php echo $document->created->format('M j, Y'); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center align-top pt-3">
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
            <?php endif; ?>

            <!-- Version History -->
            <?php if (!empty($case->case_versions)): ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-history me-2 text-warning"></i>Version History
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <?php foreach ($case->case_versions as $version): ?>
                    <div class="d-flex align-items-center mb-2 <?php echo $version->id === $case->current_version_id ? 'bg-light p-2 rounded' : ''; ?>">
                        <div class="flex-grow-1">
                            <strong>Version <?php echo $version->version_number; ?></strong>
                            <?php if ($version->id === $case->current_version_id): ?>
                                <span class="badge rounded-pill bg-warning text-dark ms-2">Current</span>
                            <?php endif; ?>
                            <br>
                            <small class="text-muted">
                                Updated by 
                                <?php echo h($version->user->first_name . ' ' . $version->user->last_name); ?>
                                on <?php echo $version->timestamp->format('M j, Y \a\t g:i A'); ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Assignment History -->
            <?php if (!empty($case->case_assignments)): ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-friends me-2 text-warning"></i>Assignment History
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <?php foreach ($case->case_assignments as $assignment): ?>
                    <div class="border-start border-3 border-warning ps-3 mb-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>Assigned to 
                                    <?php echo h($assignment->assigned_to_user->first_name . ' ' . $assignment->assigned_to_user->last_name); ?>
                                </strong>
                                <?php if (isset($assignment->assigned_to_user->role) && $assignment->assigned_to_user->role): ?>
                                    <span class="badge rounded-pill bg-warning text-dark ms-2">
                                        <?php echo h($this->Role->label($assignment->assigned_to_user->role->type)); ?>
                                    </span>
                                <?php endif; ?>
                                <br>
                                <small class="text-muted">
                                    By 
                                    <?php echo h($assignment->user->first_name . ' ' . $assignment->user->last_name); ?>
                                    <?php if (isset($assignment->user->role) && $assignment->user->role): ?>
                                        <span class="badge rounded-pill bg-secondary ms-1">
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

            <!-- Administrative Overview -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-shield-alt me-2 text-warning"></i>Administrative Overview
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="alert alert-warning border-0 mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Read-Only Access:</strong> You can view all case details but cannot make modifications.
                    </div>
                </div>
            </div>


            <!-- Report Hierarchy Information -->
            <?php if (!empty($existingReports)): ?>
                <?php 
                // Organize reports by role hierarchy
                $reportHierarchy = [
                    'technician' => [],
                    'scientist' => [],
                    'doctor' => []
                ];
                
                // Categorize reports by creator role
                foreach ($existingReports as $report) {
                    if (!empty($report->user->role->type)) {
                        $roleType = strtolower($report->user->role->type);
                        if (array_key_exists($roleType, $reportHierarchy)) {
                            $reportHierarchy[$roleType][] = $report;
                        }
                    }
                }
                
                // Find current user's role for ownership detection
                $hasAnyReports = !empty(array_filter($reportHierarchy, fn($reports) => !empty($reports)));
                ?>
                
                <?php if ($hasAnyReports): ?>
                <div class="card border-0 shadow mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-file-medical-alt me-2 text-warning"></i>
                            Report Workflow Hierarchy
                        </h6>
                    </div>
                    <div class="card-body bg-white">
                        <!-- Workflow Progress Indicator -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-semibold text-muted">Workflow Progress</span>
                                <span class="small fw-bold text-warning">
                                    <?php 
                                    $completedStages = count(array_filter($reportHierarchy));
                                    echo $completedStages . '/3 Stages Complete';
                                    ?>
                                </span>
                            </div>
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: <?php echo  ($completedStages / 3) * 100 ?>%"></div>
                            </div>
                        </div>

                        <!-- Hierarchical Report Display -->
                        <div class="hierarchy-container">
                            <?php 
                            $hierarchyConfig = [
                                'technician' => [
                                    'title' => 'Technician Report',
                                    'icon' => 'fa-user-cog',
                                    'color' => 'info',
                                    'description' => 'Initial Analysis & Data Collection'
                                ],
                                'scientist' => [
                                    'title' => 'Scientist Report', 
                                    'icon' => 'fa-user-graduate',
                                    'color' => 'warning',
                                    'description' => 'Scientific Review & Validation'
                                ],
                                'doctor' => [
                                    'title' => 'Doctor Report',
                                    'icon' => 'fa-user-md', 
                                    'color' => 'danger',
                                    'description' => 'Medical Review & Final Approval'
                                ]
                            ];
                            ?>
                            
                            <?php foreach ($hierarchyConfig as $roleType => $config): ?>
                                <?php $reports = $reportHierarchy[$roleType]; ?>
                                <?php $isCompleted = !empty($reports); ?>
                                
                                <div class="hierarchy-stage mb-3 <?php echo  $isCompleted ? 'completed' : 'pending' ?>">
                                    <div class="d-flex align-items-center p-3 rounded border <?php echo  $isCompleted ? 'border-' . $config['color'] . ' bg-' . $config['color'] . ' bg-opacity-10' : 'border-light bg-light' ?>">
                                        <!-- Stage Icon & Info -->
                                        <div class="flex-shrink-0 me-3">
                                            <div class="rounded-circle bg-<?php echo  $isCompleted ? $config['color'] : 'light' ?> text-<?php echo  $isCompleted ? 'white' : 'muted' ?> d-flex align-items-center justify-content-center" 
                                                 style="width: 45px; height: 45px;">
                                                <i class="fas <?php echo  $config['icon'] ?> fa-lg"></i>
                                            </div>
                                        </div>
                                        
                                        <!-- Stage Content -->
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <h6 class="mb-0 fw-bold text-<?php echo  $isCompleted ? $config['color'] : 'muted' ?>">
                                                    <?php echo  $config['title'] ?>
                                                </h6>
                                                
                                                <?php if ($isCompleted): ?>
                                                    <span class="badge bg-<?php echo  $config['color'] ?> px-2 py-1">
                                                        <i class="fas fa-check-circle me-1"></i>Complete
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted px-2 py-1">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <p class="mb-2 small text-muted"><?php echo  $config['description'] ?></p>
                                            
                                            <?php if ($isCompleted): ?>
                                                <!-- Report Details - Loop through multiple reports -->
                                                <?php foreach ($reports as $report): ?>
                                                <div class="row align-items-center mb-2 p-2 border-start border-3 border-<?php echo $config['color'] ?> bg-white">
                                                    <div class="col-md-7">
                                                        <div class="small">
                                                            <?php 
                                                            $isPPT = ($report->type === 'PPT');
                                                            $reportTypeBadge = $isPPT 
                                                                ? '<span class="badge bg-warning me-1"><i class="fas fa-file-powerpoint me-1"></i>MEG</span>' 
                                                                : '<span class="badge bg-danger me-1"><i class="fas fa-file-pdf me-1"></i>EEG</span>';
                                                            echo $reportTypeBadge;
                                                            ?>
                                                            <strong>Report #<?php echo  h($report->id) ?></strong>
                                                            <?php if (!empty($report->user)): ?>
                                                                <span class="text-muted">
                                                                    by <?php echo  h($report->user->first_name . ' ' . $report->user->last_name) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                            <br>
                                                            <span class="text-muted">
                                                                <i class="fas fa-calendar me-1"></i>
                                                                <?php echo  $report->created->format('M j, Y g:i A') ?>
                                                            </span>
                                                            <?php 
                                                            $statusClass = match($report->status) {
                                                                'in_progress' => 'warning',
                                                                'completed' => 'success',
                                                                'pending' => 'warning',
                                                                'reviewed' => 'info',
                                                                'approved' => 'success',
                                                                'rejected' => 'danger',
                                                                default => 'secondary'
                                                            };
                                                            $statusLabel = ucwords(str_replace('_', ' ', $report->status));
                                                            ?>
                                                            <span class="badge bg-<?php echo  $statusClass ?> ms-2"><?php echo  h($statusLabel) ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5 text-end">
                                                        <div class="btn-group btn-group-sm">
                                                            <?php 
                                                            // Route based on report type
                                                            $downloadUrl = $isPPT 
                                                                ? ['controller' => 'MegReports', 'action' => 'downloadPpt', $report->id]
                                                                : ['controller' => 'Reports', 'action' => 'download', $report->id, 'pdf'];
                                                            ?>
                                                            
                                                            <?php if (!$isPPT): ?>
                                                                <?php echo  $this->Html->link(
                                                                    '<i class="fas fa-eye"></i>',
                                                                    ['controller' => 'Reports', 'action' => 'view', $report->id],
                                                                    [
                                                                        'class' => 'btn btn-outline-' . $config['color'] . ' btn-sm',
                                                                        'escape' => false,
                                                                        'title' => 'View Report'
                                                                    ]
                                                                ); ?>
                                                            <?php endif; ?>
                                                            
                                                            <?php echo  $this->Html->link(
                                                                '<i class="fas fa-download"></i>',
                                                                $downloadUrl,
                                                                [
                                                                    'class' => 'btn btn-outline-' . $config['color'] . ' btn-sm',
                                                                    'escape' => false,
                                                                    'title' => $isPPT ? 'Download PPT' : 'Download PDF'
                                                                ]
                                                            ); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <!-- No Report Available -->
                                                <div class="text-center">
                                                    <small class="text-muted">Awaiting <?php echo  $roleType ?> to complete this stage</small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Hierarchy Connection Line -->
                                        <?php if ($roleType !== 'doctor'): ?>
                                            <div class="hierarchy-line"></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Workflow Summary -->
                        <div class="mt-3 p-3 bg-light rounded">
                            <div class="row text-center small">
                                <div class="col-4">
                                    <strong class="text-info">Technician</strong><br>
                                    <span class="text-muted">Data Collection</span>
                                </div>
                                <div class="col-4">
                                    <strong class="text-warning">Scientist</strong><br>
                                    <span class="text-muted">Analysis & Review</span>
                                </div>
                                <div class="col-4">
                                    <strong class="text-danger">Doctor</strong><br>
                                    <span class="text-muted">Final Approval</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Case Overview -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-pie me-2 text-warning"></i>Case Overview
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <!-- Role-Based Status Workflow -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-project-diagram text-warning me-2"></i>
                            <strong>Workflow Status</strong>
                        </div>
                        <div class="ps-4">
                            <!-- Global Case Status -->
                            <div class="mb-4 p-3 rounded bg-light border-start border-4 border-warning">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small">
                                        <i class="fas fa-stream me-1 text-warning"></i>
                                        <strong>Global Case Status</strong>
                                    </span>
                                    <?php echo $this->Status->globalBadge($case, ['class' => 'px-3 py-2']); ?>
                                </div>
                                <div class="small text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Overall case state in the system
                                </div>
                            </div>
                            
                            <!-- Role-Based Statuses -->
                            <div class="small text-muted mb-2 text-uppercase" style="letter-spacing: 0.5px; font-weight: 600;">
                                <i class="fas fa-users me-1"></i>Individual Role Progress
                            </div>
                            
                            <!-- Technician Status -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small">
                                        <i class="<?php echo $this->Status->roleIcon('technician'); ?> me-1 text-secondary"></i>
                                        <strong>Technician</strong>
                                    </span>
                                    <span class="badge rounded-pill bg-<?php echo $this->Status->colorClass($technicianStatus); ?>">
                                        <?php echo h($case->getStatusLabelForRole('technician')); ?>
                                    </span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?php 
                                        echo match($technicianStatus) {
                                            'completed' => 'success',
                                            'in_progress', 'review' => 'warning',
                                            'assigned' => 'info',
                                            default => 'secondary'
                                        };
                                    ?>" style="width: <?php 
                                        echo match($technicianStatus) {
                                            'completed' => '100',
                                            'review' => '75',
                                            'in_progress' => '50',
                                            'assigned' => '25',
                                            default => '10'
                                        };
                                    ?>%"></div>
                                </div>
                            </div>
                            
                            <!-- Scientist Status -->
                            <?php if ($scientistStatus !== 'draft'): ?>
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small">
                                        <i class="<?php echo $this->Status->roleIcon('scientist'); ?> me-1 text-warning"></i>
                                        <strong>Scientist</strong>
                                    </span>
                                    <span class="badge rounded-pill bg-<?php echo $this->Status->colorClass($scientistStatus); ?>">
                                        <?php echo h($case->getStatusLabelForRole('scientist')); ?>
                                    </span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?php 
                                        echo match($scientistStatus) {
                                            'completed' => 'success',
                                            'in_progress', 'review' => 'warning',
                                            'assigned' => 'info',
                                            default => 'secondary'
                                        };
                                    ?>" style="width: <?php 
                                        echo match($scientistStatus) {
                                            'completed' => '100',
                                            'review' => '75',
                                            'in_progress' => '50',
                                            'assigned' => '25',
                                            default => '10'
                                        };
                                    ?>%"></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Doctor Status -->
                            <?php if ($doctorStatus !== 'draft'): ?>
                            <div class="mb-2">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small">
                                        <i class="<?php echo $this->Status->roleIcon('doctor'); ?> me-1 text-info"></i>
                                        <strong>Doctor</strong>
                                    </span>
                                    <span class="badge rounded-pill bg-<?php echo $this->Status->colorClass($doctorStatus); ?>">
                                        <?php echo h($case->getStatusLabelForRole('doctor')); ?>
                                    </span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?php 
                                        echo match($doctorStatus) {
                                            'completed' => 'success',
                                            'in_progress', 'review' => 'warning',
                                            'assigned' => 'info',
                                            default => 'secondary'
                                        };
                                    ?>" style="width: <?php 
                                        echo match($doctorStatus) {
                                            'completed' => '100',
                                            'review' => '75',
                                            'in_progress' => '50',
                                            'assigned' => '25',
                                            default => '10'
                                        };
                                    ?>%"></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Overall Progress -->
                            <div class="mt-3 pt-3 border-top">
                                <div class="small text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?php 
                                    $statuses = [$technicianStatus, $scientistStatus, $doctorStatus];
                                    $completedCount = count(array_filter($statuses, fn($s) => $s === 'completed'));
                                    $overallProgress = round(($completedCount / 3) * 100);
                                    ?>
                                    Overall Progress: <strong><?php echo $overallProgress; ?>%</strong>
                                    (<?php echo $completedCount; ?> of 3 roles completed)
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Department Info -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-building text-warning me-2"></i>
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
                                <span class="badge rounded-pill bg-warning text-dark">
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
                                    <div class="h5 mb-1 text-warning"><?php echo $totalProcedures; ?></div>
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
                                if (!empty($cep->exams_procedure->exam->modality)) {
                                    $modality = $cep->exams_procedure->exam->modality->name;
                                    if (!in_array($modality, $modalities)) {
                                        $modalities[] = $modality;
                                    }
                                }
                            }
                            ?>
                            <?php foreach ($modalities as $modality): ?>
                                <span class="badge rounded-pill bg-info text-white me-1 mb-1">
                                    <i class="fas fa-microscope me-1"></i><?php echo h($modality); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Case Statistics -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-bar me-2 text-warning"></i>Case Stats
                    </h6>
                </div>
                <div class="card-body bg-white">
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
            <div class="card border-0 shadow">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-route me-2 text-warning"></i>Status Flow
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="small">
                        <?php 
                        // Admin view shows global case status flow
                        $statuses = ['in_progress', 'completed', 'cancelled'];
                        $currentStatus = $case->status ?? 'in_progress';
                        $currentIndex = array_search($currentStatus, $statuses);
                        if ($currentIndex === false) $currentIndex = -1;
                        ?>
                        
                        <?php foreach ($statuses as $index => $status): ?>
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-2">
                                    <?php if ($index < $currentIndex || ($currentStatus === 'completed' && $status === 'completed')): ?>
                                        <i class="fas fa-check-circle text-success"></i>
                                    <?php elseif ($index === $currentIndex || ($currentStatus === 'cancelled' && $status === 'cancelled')): ?>
                                        <i class="fas fa-circle text-danger"></i>
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
</div>

<!-- Change History -->
<?php if (!empty($case->case_audits)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow">
            <div class="card-header bg-light border-0 py-3">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-clipboard-list me-2 text-warning"></i>Change History
                </h5>
            </div>
            <div class="card-body bg-white">
                <div class="timeline">
                    <?php foreach ($case->case_audits as $audit): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong><?php echo h($audit->getChangeDescription()); ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        by 
                                        <?php echo h($audit->changed_by_user->first_name . ' ' . $audit->changed_by_user->last_name); ?>
                                        <?php if (isset($audit->changed_by_user->role) && isset($audit->changed_by_user->role->type)): ?>
                                            <?php echo $this->Role->badge($audit->changed_by_user->role->type, ['class' => 'ms-1']); ?>
                                        <?php endif; ?>
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



<style>
/* Report Hierarchy Styling */
.hierarchy-container .hierarchy-stage {
    position: relative;
}

.hierarchy-stage.completed {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.hierarchy-stage.current-user {
    position: relative;
}

.hierarchy-stage.current-user::before {
    content: '';
    position: absolute;
    left: -3px;
    top: -3px;
    right: -3px;
    bottom: -3px;
    border: 2px solid #0d6efd;
    border-radius: 8px;
    z-index: -1;
}

.hierarchy-line {
    position: absolute;
    left: 22px;
    bottom: -15px;
    width: 2px;
    height: 15px;
    background-color: #dee2e6;
    z-index: 1;
}

.hierarchy-stage.completed .hierarchy-line {
    background-color: #28a745;
}

/* Progress bar styling */
.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.6s ease;
}

/* Button group spacing */
.btn-group-sm .btn {
    margin: 0 1px;
}

/* Role badge styling */
.hierarchy-stage .badge {
    font-size: 0.7rem;
    font-weight: 600;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hierarchy-container .col-md-4 {
        text-align: center !important;
        margin-top: 10px;
    }
    
    .hierarchy-container .btn-group {
        width: 100%;
    }
    
    .hierarchy-container .btn-group .btn {
        flex: 1;
    }
}
</style>