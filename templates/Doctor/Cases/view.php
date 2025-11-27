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
        <div class="card-body bg-danger text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-user-md me-2"></i>Case #<?php echo h($case->id); ?>
                    </h2>
                    <p class="mb-0">
                        <?php if ($case->patient_user): ?>
                            <i class="fas fa-user-injured me-2"></i><?php echo $this->PatientMask->displayName($case->patient_user); ?>
                        <?php endif; ?>
                        <span class="mx-2">•</span>
                        <i class="fas fa-hospital me-2"></i><?php echo h($currentHospital->name ?? 'Hospital') ?>
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
                        
                        <?php 
                        // Check doctor's role-based status for action permissions
                        $doctorStatus = $case->doctor_status ?? 'assigned';
                        ?>
                        <?php if (in_array($doctorStatus, ['assigned', 'in_progress']) && $case->status !== 'completed'): ?>
                            <?php echo $this->Html->link(
                                '<i class="fas fa-file-medical me-1"></i>Create Report',
                                ['controller' => 'Reports', 'action' => 'add', '?' => ['case_id' => $case->id]],
                                ['class' => 'btn btn-light', 'escape' => false]
                            ); ?>
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
                            <i class="fas fa-file-medical me-2 text-danger"></i>Case Information
                        </h5>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <?php 
                            // Get all role-specific statuses for permission checks
                            $technicianStatus = $case->technician_status ?? 'draft';
                            $scientistStatus = $case->scientist_status ?? 'assigned';
                            $doctorStatus = $case->doctor_status ?? 'assigned';
                            ?>
                            
                            <!-- Role-Based Statuses -->
                            <div class="d-flex flex-wrap gap-1">
                                <?php echo $this->Status->roleBadge($case, 'technician', $user); ?>
                                <?php echo $this->Status->roleBadge($case, 'scientist', $user); ?>
                                <?php echo $this->Status->roleBadge($case, 'doctor', $user); ?>
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
                                            <br><small class="text-muted">
                                                <i class="fas fa-id-card me-1"></i>
                                                MRN: <?php echo $this->PatientMask->displayMrn($case->patient_user); ?>
                                            </small>
                                            <br><small class="text-muted">
                                                <i class="fas fa-birthday-cake me-1"></i>
                                                <?php echo $this->PatientMask->displayDob($case->patient_user); ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">No patient assigned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Department:</td>
                                    <td>
                                        <?php if ($case->department): ?>
                                            <i class="fas fa-building me-1 text-danger"></i>
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
                                        <?php if ($case->user_id === $user->id): ?>
                                            <span class="text-danger fw-semibold">You</span>
                                        <?php else: ?>
                                            <?php echo h($case->user->first_name . ' ' . $case->user->last_name); ?>
                                        <?php endif; ?>
                                        <br><small class="text-muted"><?php echo h($case->user->email); ?></small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Current User:</td>
                                    <td>
                                        <?php if ($case->current_user): ?>
                                            <?php if ($case->current_user_id === $user->id): ?>
                                                <span class="text-danger fw-semibold">
                                                    <i class="fas fa-user-circle me-1"></i>You (Currently Assigned)
                                                </span>
                                            <?php else: ?>
                                                <?php echo h($case->current_user->first_name . ' ' . $case->current_user->last_name); ?>
                                            <?php endif; ?>
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
                        <i class="fas fa-procedures me-2 text-danger"></i>Assigned Procedures
                    </h5>
                    <span class="badge rounded-pill bg-danger">
                        <?php echo count($case->cases_exams_procedures); ?> 
                        <?php echo count($case->cases_exams_procedures) === 1 ? 'Procedure' : 'Procedures'; ?>
                    </span>
                </div>
                <div class="card-body bg-white p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted">Procedure & Modality</th>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted">Status</th>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted">Documents</th>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($case->cases_exams_procedures as $cep): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?php echo h($cep->exams_procedure->exam->name ?? 'N/A'); ?></strong>
                                                <span class="badge rounded-pill bg-danger text-white ms-2">
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
                                            <span class="badge rounded-pill <?php echo $cep->getStatusBadgeClass(); ?>">
                                                <?php echo h($cep->getStatusLabel()); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($cep->hasDocuments()): ?>
                                                <span class="badge rounded-pill bg-danger text-white">
                                                    <i class="fas fa-file me-1"></i>
                                                    <?php echo $cep->getDocumentCount(); ?> files
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">No files</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#documentsModal"
                                                        title="View Documents">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if (in_array($doctorStatus, ['assigned', 'in_progress']) && $case->status !== 'completed'): ?>
                                                    <button class="btn btn-outline-secondary" 
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
            <?php endif; ?>

            <!-- Documents Section -->
            <?php if (!empty($case->documents)): ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-folder-open me-2 text-danger"></i>Case Documents
                    </h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-danger">
                            <?php echo count($case->documents); ?> <?php echo count($case->documents) === 1 ? 'Document' : 'Documents'; ?>
                        </span>
                        <?php if (in_array($case->status, ['assigned', 'in_progress']) && $case->status !== 'completed'): ?>
                            <button class="btn btn-sm btn-danger d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-upload"></i>Upload Documents
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body bg-white p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted ps-4" style="width: 50px;"><i class="fas fa-file"></i></th>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted" style="width: 250px;">Document Name</th>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted" style="width: 200px;">Procedure</th>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted">Uploaded</th>
                                    <th class="border-0 fw-semibold text-uppercase small text-muted text-center" style="width: 130px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($case->documents as $index => $document): ?>
                                <tr>
                                    <td class="ps-4">
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
                                        ?>
                                        <div class="d-flex align-items-center justify-content-center rounded <?php echo $bgClass; ?>" 
                                             style="width: 40px; height: 40px;">
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
                                            <div class="d-flex flex-column gap-1">
                                                <span class="badge rounded-pill bg-danger" style="width: fit-content;">
                                                    <i class="fas fa-stethoscope me-1"></i>
                                                    <?php echo h($document->cases_exams_procedure->exams_procedure->exam->name ?? ''); ?>
                                                </span>
                                                <?php if (!empty($document->cases_exams_procedure->exams_procedure->procedure->name)): ?>
                                                    <span class="badge rounded-pill bg-info text-white" style="width: fit-content;">
                                                        <i class="fas fa-procedures me-1"></i>
                                                        <?php echo h($document->cases_exams_procedure->exams_procedure->procedure->name); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge rounded-pill bg-secondary">
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
                                            <span class="text-dark small fw-semibold">
                                                <?php if ($document->user_id === $user->id): ?>
                                                    <span class="text-danger">You</span>
                                                <?php else: ?>
                                                    <?php echo h($firstName . ' ' . $lastName); ?>
                                                <?php endif; ?>
                                            </span>
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
                                                    'class' => 'btn btn-sm btn-outline-danger', 
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

            <!-- Case Audit Trail -->
            <?php if (!empty($case->case_audits)): ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-clipboard-list me-2 text-danger"></i>Change History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($case->case_audits as $audit): ?>
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fas fa-edit fa-sm"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="mb-1">
                                    <strong><?php echo h(ucfirst(str_replace('_', ' ', $audit->field_name))); ?></strong> changed
                                    <?php if ($audit->changed_by_user): ?>
                                        by 
                                        <?php if ($audit->changed_by_user->id === $user->id): ?>
                                            <span class="text-primary fw-bold">You</span>
                                        <?php else: ?>
                                            <?php echo h($audit->changed_by_user->first_name . ' ' . $audit->changed_by_user->last_name); ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </p>
                                <p class="mb-1 small">
                                    From: <code class="text-danger"><?php echo $audit->old_value ? h($audit->old_value) : '<empty>'; ?></code>
                                    To: <code class="text-success"><?php echo $audit->new_value ? h($audit->new_value) : '<empty>'; ?></code>
                                </p>
                                <p class="mb-0 text-muted small">
                                    <i class="far fa-clock me-1"></i><?php echo $audit->timestamp ? $audit->timestamp->format('M d, Y h:i A') : 'N/A'; ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bolt me-2 text-danger"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <?php 
                    // Check if case is editable for doctors
                    if ($case->status !== 'completed'): 
                    ?>
                        <div class="d-grid gap-2">
                            <?php 
                            // Check if doctor has their own report for this case
                            $doctorReport = null;
                            $hasOtherReports = false;
                            
                            if (!empty($existingReports)) {
                                foreach ($existingReports as $report) {
                                    if ($report->user_id === $user->id) {
                                        $doctorReport = $report;
                                    } else {
                                        $hasOtherReports = true;
                                    }
                                }
                            }
                            ?>
                            
                            <?php if ($doctorReport): ?>
                                <!-- Doctor has their own report -->
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-edit me-2"></i>Edit My Report',
                                    ['controller' => 'Reports', 'action' => 'edit', $doctorReport->id],
                                    ['class' => 'btn btn-outline-info d-flex align-items-center justify-content-center', 'escape' => false]
                                ); ?>
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-eye me-2"></i>View My Report',
                                    ['controller' => 'Reports', 'action' => 'view', $doctorReport->id],
                                    ['class' => 'btn btn-outline-danger d-flex align-items-center justify-content-center', 'escape' => false]
                                ); ?>
                            <?php elseif ($hasOtherReports): ?>
                                <!-- Other reports exist, but doctor hasn't created their own -->
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-file-medical-alt me-2"></i>Create My Report',
                                    ['controller' => 'Reports', 'action' => 'add', '?' => ['case_id' => $case->id]],
                                    [
                                        'class' => 'btn btn-outline-info d-flex align-items-center justify-content-center', 
                                        'escape' => false,
                                        'confirm' => 'This will create a new doctor report based on existing case data. Continue?'
                                    ]
                                ); ?>
                                <?php 
                                // Show view link for the first available report
                                $firstOtherReport = $existingReports->first();
                                ?>
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-eye me-2"></i>View Previous Report',
                                    ['controller' => 'Reports', 'action' => 'view', $firstOtherReport->id],
                                    ['class' => 'btn btn-outline-secondary d-flex align-items-center justify-content-center', 'escape' => false]
                                ); ?>
                            <?php else: ?>
                                <!-- No reports exist yet -->
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-file-medical-alt me-2"></i>Create Report',
                                    ['controller' => 'Reports', 'action' => 'add', '?' => ['case_id' => $case->id]],
                                    [
                                        'class' => 'btn btn-outline-info d-flex align-items-center justify-content-center', 
                                        'escape' => false,
                                        'confirm' => 'Are you sure you want to create a report for this case?'
                                    ]
                                ); ?>
                            <?php endif; ?>
                            
                            <button class="btn btn-outline-danger d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-upload"></i>Upload Documents
                            </button>
                        </div>
                    <?php elseif (in_array($case->status, ['in_progress', 'review'])): ?>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#uploadModal">
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

            <!-- Report Hierarchy Information -->
            <?php if (!empty($existingReports)): ?>
                <?php 
                // Organize reports by role hierarchy
                $reportHierarchy = [
                    'technician' => null,
                    'scientist' => null,
                    'doctor' => null
                ];
                
                // Categorize reports by creator role
                foreach ($existingReports as $report) {
                    if (!empty($report->user->role->type)) {
                        $roleType = strtolower($report->user->role->type);
                        if (array_key_exists($roleType, $reportHierarchy)) {
                            $reportHierarchy[$roleType] = $report;
                        }
                    }
                }
                
                // Find current user's role for ownership detection
                $currentUserRole = strtolower($user->role->type ?? 'unknown');
                $hasAnyReports = array_filter($reportHierarchy) !== [];
                ?>
                
                <?php if ($hasAnyReports): ?>
                <div class="card border-0 shadow mb-4">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-file-medical-alt me-2 text-danger"></i>
                            Report Workflow Hierarchy
                        </h6>
                    </div>
                    <div class="card-body bg-white">
                        <!-- Workflow Progress Indicator -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-semibold text-muted">Workflow Progress</span>
                                <span class="small fw-bold text-danger">
                                    <?php 
                                    $completedStages = count(array_filter($reportHierarchy));
                                    echo $completedStages . '/3 Stages Complete';
                                    ?>
                                </span>
                            </div>
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: <?php echo  ($completedStages / 3) * 100 ?>%"></div>
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
                                <?php $report = $reportHierarchy[$roleType]; ?>
                                <?php $isCurrentUserRole = ($roleType === $currentUserRole); ?>
                                <?php $isCompleted = ($report !== null); ?>
                                
                                <div class="hierarchy-stage mb-3 <?php echo  $isCompleted ? 'completed' : 'pending' ?> <?php echo  $isCurrentUserRole ? 'current-user' : '' ?>">
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
                                                    <?php if ($isCurrentUserRole): ?>
                                                        <span class="badge bg-<?php echo  $config['color'] ?> ms-2">You</span>
                                                    <?php endif; ?>
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
                                                <!-- Report Details -->
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <div class="small">
                                                            <strong>Report #<?php echo  h($report->id) ?></strong>
                                                            <?php if (!$isCurrentUserRole && !empty($report->user)): ?>
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
                                                                'pending' => 'warning',
                                                                'reviewed' => 'info', 
                                                                'approved' => 'success',
                                                                'rejected' => 'danger',
                                                                default => 'secondary'
                                                            };
                                                            ?>
                                                            <span class="badge bg-<?php echo  $statusClass ?> ms-2"><?php echo  h(ucfirst($report->status)) ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <div class="btn-group btn-group-sm">
                                                            <?php echo  $this->Html->link(
                                                                '<i class="fas fa-eye"></i>',
                                                                ['controller' => 'Reports', 'action' => 'view', $report->id],
                                                                [
                                                                    'class' => 'btn btn-outline-' . $config['color'] . ' btn-sm',
                                                                    'escape' => false,
                                                                    'title' => 'View Report'
                                                                ]
                                                            ); ?>
                                                            
                                                            <?php if ($isCurrentUserRole): ?>
                                                                <?php echo  $this->Html->link(
                                                                    '<i class="fas fa-edit"></i>',
                                                                    ['controller' => 'Reports', 'action' => 'edit', $report->id],
                                                                    [
                                                                        'class' => 'btn btn-outline-' . $config['color'] . ' btn-sm',
                                                                        'escape' => false,
                                                                        'title' => 'Edit My Report'
                                                                    ]
                                                                ); ?>
                                                            <?php endif; ?>
                                                            
                                                            <?php echo  $this->Html->link(
                                                                '<i class="fas fa-download"></i>',
                                                                ['controller' => 'Reports', 'action' => 'download', $report->id, 'pdf'],
                                                                [
                                                                    'class' => 'btn btn-outline-' . $config['color'] . ' btn-sm',
                                                                    'escape' => false,
                                                                    'title' => 'Download PDF'
                                                                ]
                                                            ); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <!-- No Report Available -->
                                                <?php if ($isCurrentUserRole): ?>
                                                    <div class="text-center">
                                                        <?php echo  $this->Html->link(
                                                            '<i class="fas fa-plus me-2"></i>Create My ' . ucfirst($roleType) . ' Report',
                                                            ['controller' => 'Reports', 'action' => 'add', '?' => ['case_id' => $case->id]],
                                                            [
                                                                'class' => 'btn btn-' . $config['color'] . ' btn-sm',
                                                                'escape' => false,
                                                                'confirm' => 'This will create a new ' . $roleType . ' report. Continue?'
                                                            ]
                                                        ); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-center">
                                                        <small class="text-muted">Awaiting <?php echo  $roleType ?> to complete this stage</small>
                                                    </div>
                                                <?php endif; ?>
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
                        <i class="fas fa-chart-pie me-2 text-danger"></i>Case Overview
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Role-Based Status Workflow -->
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-project-diagram text-danger me-2"></i>
                            <strong>Workflow Status</strong>
                        </div>
                        <div class="ps-3">
                            <!-- Global Case Status -->
                            <div class="mb-4 p-3 rounded bg-danger bg-opacity-10 border-start border-danger border-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small">
                                        <i class="fas fa-stream me-1 text-danger"></i>
                                        <strong>Global Case Status</strong>
                                    </span>
                                    <?php echo $this->Status->globalBadge($case, ['class' => 'badge']); ?>
                                </div>
                                <div class="small text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Overall case state in the system
                                </div>
                            </div>
                            
                            <!-- Role-Based Statuses -->
                            <div class="small text-muted mb-2 text-uppercase fw-semibold" style="letter-spacing: 0.5px;">
                                <i class="fas fa-users me-1"></i>Individual Role Progress
                            </div>
                            
                            <!-- Technician Status -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small">
                                        <i class="<?php echo $this->Status->roleIcon('technician'); ?> me-1 text-secondary"></i>
                                        <strong>Technician</strong>
                                    </span>
                                    <span class="badge bg-<?php echo $this->Status->colorClass($technicianStatus); ?>">
                                        <?php echo h($case->getStatusLabelForRole('technician')); ?>
                                    </span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?php echo $this->Status->progressColor($technicianStatus); ?>" 
                                         style="width: <?php echo $this->Status->progressPercent($technicianStatus); ?>%"></div>
                                </div>
                            </div>
                            
                            <!-- Scientist Status -->
                            <?php if ($scientistStatus !== 'draft'): ?>
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small">
                                        <i class="<?php echo $this->Status->roleIcon('scientist'); ?> me-1 text-success"></i>
                                        <strong>Scientist</strong>
                                    </span>
                                    <span class="badge bg-<?php echo $this->Status->colorClass($scientistStatus); ?>">
                                        <?php echo h($case->getStatusLabelForRole('scientist')); ?>
                                    </span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?php echo $this->Status->progressColor($scientistStatus); ?>" 
                                         style="width: <?php echo $this->Status->progressPercent($scientistStatus); ?>%"></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Doctor Status -->
                            <?php if ($doctorStatus !== 'draft'): ?>
                            <div class="mb-2">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="small">
                                        <i class="<?php echo $this->Status->roleIcon('doctor'); ?> me-1 text-danger"></i>
                                        <strong>Doctor</strong>
                                    </span>
                                    <span class="badge bg-<?php echo $this->Status->colorClass($doctorStatus); ?>">
                                        <?php echo h($case->getStatusLabelForRole('doctor')); ?>
                                    </span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?php echo $this->Status->progressColor($doctorStatus); ?>" 
                                         style="width: <?php echo $this->Status->progressPercent($doctorStatus); ?>%"></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Procedure Progress -->
                    <?php if (!empty($case->cases_exams_procedures)): ?>
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-tasks text-danger me-2"></i>
                            <strong>Procedures</strong>
                        </div>
                        <div class="ps-3">
                            <?php 
                            $totalProcedures = count($case->cases_exams_procedures);
                            $completedProcedures = 0;
                            $inProgressProcedures = 0;
                            $pendingProcedures = 0;
                            
                            foreach ($case->cases_exams_procedures as $cep) {
                                if ($cep->status === 'completed') {
                                    $completedProcedures++;
                                } elseif ($cep->status === 'in_progress') {
                                    $inProgressProcedures++;
                                } else {
                                    $pendingProcedures++;
                                }
                            }
                            ?>
                            
                            <div class="row text-center g-2">
                                <div class="col-4">
                                    <div class="h5 mb-1 text-danger"><?php echo $totalProcedures; ?></div>
                                    <div class="small text-muted">Total</div>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-1 text-danger"><?php echo $completedProcedures; ?></div>
                                    <div class="small text-muted">Done</div>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-1 text-warning"><?php echo $pendingProcedures; ?></div>
                                    <div class="small text-muted">Pending</div>
                                </div>
                            </div>
                            
                            <?php if ($totalProcedures > 0): ?>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar bg-danger" style="width: <?php echo ($completedProcedures / $totalProcedures) * 100; ?>%"></div>
                                    <div class="progress-bar bg-warning" style="width: <?php echo ($inProgressProcedures / $totalProcedures) * 100; ?>%"></div>
                                </div>
                                <div class="small text-muted mt-1">
                                    <?php echo round(($completedProcedures / $totalProcedures) * 100); ?>% complete
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Modalities Used -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-microscope text-secondary me-2"></i>
                            <strong>Modalities</strong>
                        </div>
                        <div class="ps-3">
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
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-bar me-2 text-danger"></i>Case Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center g-2">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="h5 mb-1 text-danger"><?php echo count($case->case_versions ?? []); ?></div>
                                <div class="small text-muted">Versions</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded">
                                <div class="h5 mb-1 text-danger"><?php echo count($case->case_assignments ?? []); ?></div>
                                <div class="small text-muted">Assignments</div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Days since creation:</span>
                            <strong class="text-danger"><?php echo $case->created->diffInDays(\Cake\I18n\DateTime::now()); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Last activity:</span>
                            <strong class="text-danger"><?php echo $case->modified->diffForHumans(); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Flow -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-route me-2 text-danger"></i>Status Flow
                    </h6>
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
                                        <i class="fas fa-check-circle text-danger"></i>
                                    <?php elseif ($index === $currentIndex): ?>
                                        <i class="fas fa-circle text-danger"></i>
                                    <?php else: ?>
                                        <i class="far fa-circle text-muted"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="<?php echo $index === $currentIndex ? 'fw-semibold' : ($index < $currentIndex ? 'text-danger' : 'text-muted'); ?>">
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

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <?php echo $this->Form->create(null, [
                'url' => ['action' => 'uploadDocument', $case->id],
                'type' => 'file',
                'id' => 'uploadForm'
            ]); ?>
            
            <!-- Explicit CSRF token for additional security -->
            <?php echo $this->Form->hidden('_csrfToken', ['value' => $this->request->getAttribute('csrfToken')]); ?>
            
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
                        <li><i class="fas fa-robot me-1 text-danger"></i> <strong>Intelligent Analysis:</strong> Document type and procedure link auto-detected using OCR & NLP</li>
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
                        <i class="fas fa-check-circle text-danger me-1"></i>
                        Allowed: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, JPEG, PNG, GIF, TXT, DICOM. Max size: 50MB
                    </div>
                    <div id="filePreview" class="mt-2" style="display: none;">
                        <div class="alert alert-danger py-2">
                            <i class="fas fa-file-check me-2"></i>
                            <strong>Selected:</strong> <span id="fileName"></span>
                            <span class="badge bg-danger ms-2" id="fileSize"></span>
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
                        <i class="fas fa-shield-alt text-danger me-2 mt-1"></i>
                        <div>
                            <strong>Secure Storage:</strong>
                            <div class="small text-muted mt-1">
                                Documents are encrypted and stored securely in: 
                                <code class="bg-white px-2 py-1">Meg_<?php echo str_pad((string)$case->id, 6, 'X', STR_PAD_LEFT); ?>/{Document_Type}/</code>
                            </div>
                            <div class="small text-muted mt-1">
                                <i class="fas fa-check text-danger me-1"></i> HIPAA compliant storage
                                <i class="fas fa-check text-danger ms-2 me-1"></i> Audit trail enabled
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
                        'class' => 'btn btn-danger', 
                        'escapeTitle' => false,
                        'id' => 'uploadSubmitBtn'
                    ]
                ); ?>
            </div>
            
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<!-- Documents Modal -->
<div class="modal fade" id="documentsModal" tabindex="-1" aria-labelledby="documentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentsModalLabel">
                    <i class="fas fa-folder-open me-2"></i>Case Documents
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Documents will be loaded here -->
                <div id="documentsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-danger" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading documents...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
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
                    <div class="spinner-border text-danger" role="status">
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
                    <button type="button" class="btn btn-danger" id="previewDownloadBtn">
                        <i class="fas fa-download me-2"></i>Download File
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="previewDownloadLink" class="btn btn-danger" target="_blank" download>
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
    
    // Helper function to get CSRF token
    function getCsrfToken() {
        // Try meta tag first
        let token = document.querySelector('meta[name="csrfToken"]')?.content;
        if (token) return token;
        
        // Try from cookie with different possible names
        const cookies = document.cookie.split('; ');
        const tokenNames = ['csrfToken', '_csrfToken', 'CakeCookie[csrfToken]'];
        
        for (const name of tokenNames) {
            const cookie = cookies.find(row => row.startsWith(name + '='));
            if (cookie) {
                token = cookie.split('=')[1];
                if (token) return decodeURIComponent(token);
            }
        }
        
        // Try from form if available
        const hiddenField = document.querySelector('input[name="_csrfToken"]');
        if (hiddenField) return hiddenField.value;
        
        console.warn('CSRF token not found');
        return null;
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
        
        // Get CSRF token
        const csrfToken = getCsrfToken();
        
        // Add CSRF token to form data for file uploads
        if (csrfToken) {
            formData.append('_csrfToken', csrfToken);
        }
        
        // Make API call to analyze document
        fetch('<?php echo $this->Url->build(['action' => 'analyzeDocument', $case->id]); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken || '',
                'Accept': 'application/json'
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
            
            // Deduplicate documents by ID (same document appears in both case view and modal)
            const seenIds = new Set();
            allDocuments = [];
            let uniqueIndex = 0;
            
            allButtons.forEach((btn) => {
                const btnDocId = btn.getAttribute('data-document-id');
                
                // Only add if we haven't seen this document ID yet
                if (!seenIds.has(btnDocId)) {
                    seenIds.add(btnDocId);
                    allDocuments.push({
                        id: btnDocId,
                        filename: btn.getAttribute('data-filename'),
                        procedure: btn.getAttribute('data-procedure'),
                        index: uniqueIndex
                    });
                    
                    if (btnDocId === documentId) {
                        currentDocIndex = uniqueIndex;
                        console.log('Setting current index to:', uniqueIndex);
                    }
                    
                    uniqueIndex++;
                }
            });
            
            console.log('Total unique documents:', allDocuments.length, 'Current index:', currentDocIndex);
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
        const csrfToken = getCsrfToken();
        
        // Fetch document info
        fetch('<?php echo $this->Url->build(['action' => 'viewDocument']); ?>/' + documentId, {
            method: 'GET',
            headers: {
                'X-CSRF-Token': csrfToken || '',
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
                    <a href="${url}" class="btn btn-danger" download>
                        <i class="fas fa-download me-2"></i>Download ${filename}
                    </a>
                    <a href="${url}" class="btn btn-outline-danger" target="_blank">
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
    border: 2px solid #dc3545;
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