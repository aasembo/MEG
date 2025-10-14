<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 */

$this->setLayout('technician');
$this->assign('title', 'Case #' . $case->id);
?>

<div class="cases view content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Case #<?php echo h($case->id); ?></h2>
        <div>
            <?php if (in_array($case->status, ['draft', 'assigned'])): ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-edit me-1"></i>' . __('Edit Case'),
                    ['action' => 'edit', $case->id],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ); ?>
                
                <?php echo $this->Html->link(
                    '<i class="fas fa-user-plus me-1"></i>' . __('Assign'),
                    ['action' => 'assign', $case->id],
                    ['class' => 'btn btn-info', 'escape' => false]
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
                                        <?php if ($case->patient_users): ?>
                                            <?php echo h($case->patient_users->first_name . ' ' . $case->patient_users->last_name); ?>
                                            <br><small class="text-muted">ID: <?php echo h($case->patient_users->id); ?></small>
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
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-procedures me-1"></i> Assigned Procedures</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Procedure</th>
                                    <th>Modality</th>
                                    <th>Status</th>
                                    <th>Scheduled</th>
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
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo h($cep->exams_procedure->procedure->name ?? 'N/A'); ?>
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <i class="fas fa-microscope me-1"></i>
                                                <?php echo h($cep->exams_procedure->exam->modality->name ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $cep->getStatusBadgeClass(); ?>">
                                                <?php echo h($cep->getStatusLabel()); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($cep->scheduled_at): ?>
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo $cep->scheduled_at->format('M j, Y'); ?>
                                                <br>
                                                <small class="text-muted"><?php echo $cep->scheduled_at->format('g:i A'); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Not scheduled</span>
                                            <?php endif; ?>
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
                                        <td colspan="6">
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
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-folder me-1"></i> Case Documents</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($case->documents as $document): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-<?php 
                                            echo match(strtolower(pathinfo($document->filename, PATHINFO_EXTENSION))) {
                                                'pdf' => 'pdf text-danger',
                                                'doc', 'docx' => 'word text-primary',
                                                'jpg', 'jpeg', 'png', 'gif' => 'image text-success',
                                                default => 'alt text-secondary'
                                            };
                                        ?> me-2 fs-5"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo h($document->filename); ?></h6>
                                            <small class="text-muted">
                                                Uploaded by <?php echo h($document->user->first_name . ' ' . $document->user->last_name); ?>
                                                on <?php echo $document->created->format('M j, Y'); ?>
                                            </small>
                                        </div>
                                        <div>
                                            <?php echo $this->Html->link(
                                                '<i class="fas fa-download"></i>',
                                                ['controller' => 'Documents', 'action' => 'download', $document->id],
                                                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false, 'title' => 'Download']
                                            ); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
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
                                <br>
                                <small class="text-muted">
                                    By <?php echo h($assignment->user->first_name . ' ' . $assignment->user->last_name); ?>
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
                            
                            <?php echo $this->Html->link(
                                '<i class="fas fa-user-plus me-1"></i> Assign to Scientist',
                                ['action' => 'assign', $case->id],
                                ['class' => 'btn btn-outline-info', 'escape' => false]
                            ); ?>
                            
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
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <?php echo $this->Form->control('document_type', [
                                'type' => 'select',
                                'options' => [
                                    'report' => 'Medical Report',
                                    'image' => 'Medical Image',
                                    'consent' => 'Consent Form',
                                    'lab_result' => 'Lab Result',
                                    'prescription' => 'Prescription',
                                    'referral' => 'Referral Letter',
                                    'other' => 'Other Document'
                                ],
                                'empty' => 'Select document type...',
                                'class' => 'form-select',
                                'label' => 'Document Type',
                                'required' => true
                            ]); ?>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <?php 
                            $procedureOptions = [];
                            if (!empty($case->cases_exams_procedures)) {
                                foreach ($case->cases_exams_procedures as $cep) {
                                    $examName = $cep->exams_procedure->exam->name ?? 'Unknown';
                                    $procedureName = $cep->exams_procedure->procedure->name ?? 'Unknown';
                                    $procedureOptions[$cep->id] = "{$examName} - {$procedureName}";
                                }
                            }
                            ?>
                            <?php echo $this->Form->control('cases_exams_procedure_id', [
                                'type' => 'select',
                                'options' => $procedureOptions,
                                'empty' => 'Not linked to specific procedure',
                                'class' => 'form-select',
                                'label' => 'Link to Procedure (Optional)'
                            ]); ?>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <?php echo $this->Form->control('document_file', [
                        'type' => 'file',
                        'class' => 'form-control',
                        'label' => 'Select File',
                        'required' => true,
                        'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.txt'
                    ]); ?>
                    <div class="form-text">
                        Allowed file types: PDF, DOC, DOCX, JPG, JPEG, PNG, GIF, TXT. Maximum size: 50MB.
                    </div>
                </div>
                
                <div class="mb-3">
                    <?php echo $this->Form->control('description', [
                        'type' => 'textarea',
                        'class' => 'form-control',
                        'label' => 'Description (Optional)',
                        'rows' => 3,
                        'placeholder' => 'Add any notes or description for this document...'
                    ]); ?>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Upload Information:</strong><br>
                    Documents will be securely stored and organized in the following structure:<br>
                    <code>Patient_<?php echo $case->patient_id; ?>_case_<?php echo $case->id; ?>/</code>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <?php echo $this->Form->button(
                    '<i class="fas fa-upload me-1"></i>Upload Document',
                    ['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false]
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
                                            <?php if ($document->description): ?>
                                                <br><small class="text-muted"><?php echo h($document->description); ?></small>
                                            <?php endif; ?>
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

<script>
// Handle procedure-specific document upload
document.addEventListener('DOMContentLoaded', function() {
    // Handle upload button clicks for specific procedures
    const uploadButtons = document.querySelectorAll('[data-procedure-id]');
    const procedureSelect = document.querySelector('#cases-exams-procedure-id');
    
    uploadButtons.forEach(button => {
        button.addEventListener('click', function() {
            const procedureId = this.getAttribute('data-procedure-id');
            if (procedureSelect && procedureId) {
                // Pre-select the procedure in the modal
                procedureSelect.value = procedureId;
            }
        });
    });
    
    // Reset the procedure selection when modal is closed
    const uploadModal = document.getElementById('uploadModal');
    if (uploadModal) {
        uploadModal.addEventListener('hidden.bs.modal', function () {
            if (procedureSelect) {
                procedureSelect.value = '';
            }
            // Reset the form
            const form = document.getElementById('uploadForm');
            if (form) {
                form.reset();
            }
        });
    }
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