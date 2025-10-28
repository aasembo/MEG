<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 */

$this->setLayout('doctor');
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

    <div class="row">
        <!-- Case Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-medical me-1"></i> Case Information</h5>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <?php 
                        // Get all role-specific statuses for display
                        // Note: Technicians start with draft, Scientists and Doctors start with assigned
                        $technicianStatus = $case->technician_status ? $case->technician_status : 'draft';
                        $scientistStatus = $case->scientist_status ? $case->scientist_status : 'assigned';
                        $doctorStatus = $case->doctor_status ? $case->doctor_status : 'assigned';
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
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Sedation:</td>
                                    <td>
                                        <?php if ($case->sedation): ?>
                                            <i class="fas fa-syringe me-1 text-info"></i>
                                            <?php echo h($case->sedation->name); ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Global Status:</td>
                                    <td><?php echo $this->Status->globalBadge($case); ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-semibold">Created By:</td>
                                    <td>
                                        <?php if ($case->user): ?>
                                            <?php if ($case->user->id === $user->id): ?>
                                                <span class="text-primary fw-bold">You</span>
                                            <?php else: ?>
                                                <?php echo h($case->user->first_name . ' ' . $case->user->last_name); ?>
                                            <?php endif; ?>
                                            <br><small class="text-muted"><?php echo $case->created ? $case->created->format('M d, Y h:i A') : 'N/A'; ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Current User:</td>
                                    <td>
                                        <?php if ($case->current_user): ?>
                                            <?php if ($case->current_user->id === $user->id): ?>
                                                <span class="text-primary fw-bold">You</span>
                                            <?php else: ?>
                                                <?php echo h($case->current_user->first_name . ' ' . $case->current_user->last_name); ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Hospital:</td>
                                    <td>
                                        <?php if ($case->hospital): ?>
                                            <i class="fas fa-hospital me-1 text-success"></i>
                                            <?php echo h($case->hospital->name); ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Modified:</td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1"></i><?php echo $case->modified ? $case->modified->format('M d, Y h:i A') : 'N/A'; ?>
                                        </small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Medical Findings Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="fw-semibold border-bottom pb-2 mb-3">
                                <i class="fas fa-stethoscope me-2 text-primary"></i>Medical Information
                            </h6>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="fw-semibold text-muted d-block mb-2">Finding:</label>
                                <div class="bg-light p-3 rounded">
                                    <?php if ($case->finding): ?>
                                        <?php echo nl2br(h($case->finding)); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No findings recorded</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="fw-semibold text-muted d-block mb-2">Recommendations:</label>
                                <div class="bg-light p-3 rounded">
                                    <?php if ($case->recommendations): ?>
                                        <?php echo nl2br(h($case->recommendations)); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No recommendations recorded</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="fw-semibold text-muted d-block mb-2">Remarks:</label>
                                <div class="bg-light p-3 rounded">
                                    <?php if ($case->remarks): ?>
                                        <?php echo nl2br(h($case->remarks)); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No remarks</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exams and Procedures -->
            <?php if (!empty($case->cases_exams_procedures)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-procedures me-2"></i>Exams & Procedures</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($case->cases_exams_procedures as $cep): ?>
                        <div class="border rounded p-3 mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-2">
                                        <?php if ($cep->exams_procedure && $cep->exams_procedure->exam): ?>
                                            <i class="fas fa-x-ray me-1"></i>Exam: <?php echo h($cep->exams_procedure->exam->name); ?>
                                        <?php elseif ($cep->exams_procedure && $cep->exams_procedure->procedure): ?>
                                            <i class="fas fa-procedures me-1"></i>Procedure: <?php echo h($cep->exams_procedure->procedure->name); ?>
                                        <?php endif; ?>
                                    </h6>
                                    <?php if ($cep->exams_procedure && $cep->exams_procedure->exam && $cep->exams_procedure->exam->description): ?>
                                        <p class="text-muted small mb-2"><?php echo h($cep->exams_procedure->exam->description); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6">
                                    <?php if ($cep->exams_procedure && $cep->exams_procedure->exam && $cep->exams_procedure->exam->modality): ?>
                                        <p class="mb-1">
                                            <span class="badge bg-info">
                                                <i class="fas fa-desktop me-1"></i><?php echo h($cep->exams_procedure->exam->modality->name); ?>
                                            </span>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($cep->exams_procedure && $cep->exams_procedure->exam && $cep->exams_procedure->exam->department): ?>
                                        <p class="mb-1">
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-building me-1"></i><?php echo h($cep->exams_procedure->exam->department->name); ?>
                                            </span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($cep->notes): ?>
                                <div class="mt-2 p-2 bg-light rounded">
                                    <small class="text-muted"><strong>Notes:</strong> <?php echo h($cep->notes); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Documents Section -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Documents</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fas fa-upload me-1"></i>Upload Document
                    </button>
                </div>
                <div class="card-body">
                    <?php if (!empty($case->documents)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Type</th>
                                        <th>Size</th>
                                        <th>Uploaded By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($case->documents as $document): ?>
                                    <tr>
                                        <td>
                                            <i class="fas fa-file me-1 text-primary"></i>
                                            <?php echo h($document->file_name); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo h($document->document_type); ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo number_format($document->file_size / 1024, 2); ?> KB</small>
                                        </td>
                                        <td>
                                            <?php if ($document->user): ?>
                                                <?php if ($document->user->id === $user->id): ?>
                                                    <span class="text-primary fw-bold">You</span>
                                                <?php else: ?>
                                                    <?php echo h($document->user->first_name . ' ' . $document->user->last_name); ?>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo $document->created ? $document->created->format('M d, Y') : 'N/A'; ?></small>
                                        </td>
                                        <td>
                                            <?php echo $this->Html->link(
                                                '<i class="fas fa-download"></i>',
                                                array('action' => 'downloadDocument', $document->id),
                                                array('class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'title' => 'Download')
                                            ); ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No documents uploaded yet.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                                <i class="fas fa-upload me-1"></i>Upload First Document
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Case Audit Trail -->
            <?php if (!empty($case->case_audits)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Change History</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php foreach ($case->case_audits as $audit): ?>
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                        <i class="fas fa-edit fa-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-1">
                                        <strong><?php echo h(ucfirst(str_replace('_', ' ', $audit->field))); ?></strong> changed
                                        <?php if ($audit->user): ?>
                                            by 
                                            <?php if ($audit->user->id === $user->id): ?>
                                                <span class="text-primary fw-bold">You</span>
                                            <?php else: ?>
                                                <?php echo h($audit->user->first_name . ' ' . $audit->user->last_name); ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </p>
                                    <p class="mb-1 small">
                                        <span class="text-muted">From:</span> 
                                        <code class="text-danger"><?php echo $audit->old_value ? h($audit->old_value) : '<empty>'; ?></code>
                                        <span class="text-muted ms-2">To:</span> 
                                        <code class="text-success"><?php echo $audit->new_value ? h($audit->new_value) : '<empty>'; ?></code>
                                    </p>
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i><?php echo $audit->timestamp ? $audit->timestamp->format('M d, Y h:i A') : 'N/A'; ?>
                                    </small>
                                </div>
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
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-bolt me-1"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit me-2"></i>Edit Case',
                            array('action' => 'edit', $case->id),
                            array('class' => 'btn btn-outline-primary', 'escape' => false)
                        ); ?>
                        <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fas fa-upload me-2"></i>Upload Document
                        </button>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-file-pdf me-2"></i>Download Report',
                            array('action' => 'downloadReport', $case->id),
                            array('class' => 'btn btn-outline-success', 'escape' => false, 'target' => '_blank')
                        ); ?>
                    </div>
                </div>
            </div>

            <!-- Workflow Status - All Roles -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-tasks me-1"></i> Workflow Status</h6>
                </div>
                <div class="card-body">
                    <!-- Technician Status -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">
                                <i class="fas fa-user-cog me-1 text-info"></i>Technician
                            </span>
                            <?php echo $this->Status->roleBadge($case, 'technician', $user); ?>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-<?php echo $this->Status->progressColor($technicianStatus); ?>" 
                                 role="progressbar" 
                                 style="width: <?php echo $this->Status->progressPercent($technicianStatus); ?>%"></div>
                        </div>
                    </div>

                    <!-- Scientist Status -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">
                                <i class="fas fa-microscope me-1 text-success"></i>Scientist
                            </span>
                            <?php echo $this->Status->roleBadge($case, 'scientist', $user); ?>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-<?php echo $this->Status->progressColor($scientistStatus); ?>" 
                                 role="progressbar" 
                                 style="width: <?php echo $this->Status->progressPercent($scientistStatus); ?>%"></div>
                        </div>
                    </div>

                    <!-- Doctor Status -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold">
                                <i class="fas fa-user-doctor me-1 text-primary"></i>Doctor
                            </span>
                            <?php echo $this->Status->roleBadge($case, 'doctor', $user); ?>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-<?php echo $this->Status->progressColor($doctorStatus); ?>" 
                                 role="progressbar" 
                                 style="width: <?php echo $this->Status->progressPercent($doctorStatus); ?>%"></div>
                        </div>
                    </div>

                    <hr>

                    <!-- Global Status -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">
                                <i class="fas fa-globe me-1 text-warning"></i>Overall Status
                            </span>
                            <?php echo $this->Status->globalBadge($case); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Case Information Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-1"></i> Case Summary</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-hashtag text-muted me-2"></i>
                            <strong>ID:</strong> <?php echo h($case->id); ?>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-calendar-alt text-muted me-2"></i>
                            <strong>Created:</strong><br>
                            <small class="ms-4"><?php echo $case->created ? $case->created->format('M d, Y h:i A') : 'N/A'; ?></small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock text-muted me-2"></i>
                            <strong>Modified:</strong><br>
                            <small class="ms-4"><?php echo $case->modified ? $case->modified->format('M d, Y h:i A') : 'N/A'; ?></small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-flag text-muted me-2"></i>
                            <strong>Priority:</strong> <?php echo $this->Status->priorityBadge($case->priority); ?>
                        </li>
                        <?php if ($case->current_version_id): ?>
                        <li class="mb-2">
                            <i class="fas fa-code-branch text-muted me-2"></i>
                            <strong>Version:</strong> <?php echo h($case->current_version_id); ?>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Assignment History -->
            <?php if (!empty($case->case_assignments)): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-user-check me-1"></i> Assignment History</h6>
                </div>
                <div class="card-body">
                    <div class="timeline-sm">
                        <?php foreach ($case->case_assignments as $assignment): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="mb-1">
                                        <strong>Assigned by:</strong>
                                        <?php if ($assignment->user): ?>
                                            <?php if ($assignment->user->id === $user->id): ?>
                                                <span class="text-primary fw-bold">You</span>
                                            <?php else: ?>
                                                <?php echo h($assignment->user->first_name . ' ' . $assignment->user->last_name); ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong>Assigned to:</strong>
                                        <?php if ($assignment->assigned_to_user): ?>
                                            <?php if ($assignment->assigned_to_user->id === $user->id): ?>
                                                <span class="text-primary fw-bold">You</span>
                                            <?php else: ?>
                                                <?php echo h($assignment->assigned_to_user->first_name . ' ' . $assignment->assigned_to_user->last_name); ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($assignment->notes): ?>
                                    <p class="mb-1 small text-muted">
                                        <i class="fas fa-comment me-1"></i><?php echo h($assignment->notes); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="far fa-clock me-1"></i><?php echo $assignment->timestamp ? $assignment->timestamp->format('M d, Y h:i A') : 'N/A'; ?>
                            </small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Upload Document Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo $this->Form->create(null, array(
                'url' => array('action' => 'uploadDocument', $case->id),
                'type' => 'file',
                'class' => 'needs-validation',
                'novalidate' => true
            )); ?>
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Upload Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Document File *</label>
                    <?php echo $this->Form->control('file', array(
                        'type' => 'file',
                        'label' => false,
                        'class' => 'form-control',
                        'required' => true
                    )); ?>
                    <small class="text-muted">Max size: 50MB. Allowed: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX</small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Document Type</label>
                    <?php echo $this->Form->control('document_type', array(
                        'type' => 'select',
                        'options' => array(
                            'report' => 'Medical Report',
                            'image' => 'Medical Image',
                            'lab_result' => 'Lab Result',
                            'prescription' => 'Prescription',
                            'other' => 'Other'
                        ),
                        'label' => false,
                        'class' => 'form-select'
                    )); ?>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Description</label>
                    <?php echo $this->Form->control('description', array(
                        'type' => 'textarea',
                        'label' => false,
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => 'Optional description...'
                    )); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <?php echo $this->Form->button('<i class="fas fa-upload me-1"></i>Upload', array(
                    'type' => 'submit',
                    'class' => 'btn btn-primary',
                    'escape' => false
                )); ?>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<style>
.timeline-item {
    position: relative;
}

.timeline-sm .border-bottom:last-child {
    border-bottom: none !important;
}

.progress {
    background-color: #e9ecef;
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>
