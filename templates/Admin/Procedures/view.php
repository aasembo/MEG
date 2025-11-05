<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Procedure $procedure
 */
?>
<?php $this->assign('title', 'Procedure Details'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-procedures me-2"></i><?php echo h($procedure->name) ?>
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-hashtag me-2"></i>Procedure ID: <?php echo h($procedure->id) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit me-1"></i>Edit Procedure',
                            ['action' => 'edit', $procedure->id],
                            ['class' => 'btn btn-warning text-dark fw-bold', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-arrow-left me-1"></i>Back',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-warning', 'escape' => false]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Procedure Information -->
        <div class="col-md-8">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-procedures me-2 text-warning"></i>Procedure Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Procedure Name:</div>
                        <div class="col-sm-8 text-dark fw-bold"><?php echo h($procedure->name) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Procedure ID:</div>
                        <div class="col-sm-8">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-hashtag"></i><?php echo h($procedure->id) ?>
                            </span>
                        </div>
                    </div>
                    <?php if (!empty($procedure->type)): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Type:</div>
                        <div class="col-sm-8">
                            <span class="badge bg-info bg-opacity-10 text-info">
                                <?php echo h($procedure->type) ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($procedure->hasValue('department')): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Department:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-building me-1 text-warning"></i>
                            <?php echo $this->Html->link(
                                h($procedure->department->name),
                                ['controller' => 'Departments', 'action' => 'view', $procedure->department->id],
                                ['class' => 'text-decoration-none text-dark fw-bold']
                            ) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($procedure->hasValue('sedation')): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Sedation Level:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-bed me-1 text-primary"></i>
                            <?php echo $this->Html->link(
                                h($procedure->sedation->level),
                                ['controller' => 'Sedations', 'action' => 'view', $procedure->sedation->id],
                                ['class' => 'text-decoration-none text-dark fw-bold']
                            ) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($procedure->risk_level)): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Risk Level:</div>
                        <div class="col-sm-8">
                            <span class="badge bg-<?php echo $procedure->risk_level === 'high' ? 'danger' : ($procedure->risk_level === 'medium' ? 'warning text-dark' : 'success') ?>">
                                <i class="fas fa-exclamation-triangle me-1"></i><?php echo ucfirst(h($procedure->risk_level)) ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Consent Required:</div>
                        <div class="col-sm-8">
                            <?php if ($procedure->consent_required): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-check me-1"></i>Required
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-times me-1"></i>Not Required
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!empty($procedure->duration_minutes)): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Duration:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-clock me-1 text-info"></i>
                            <span class="text-dark"><?php echo h($procedure->duration_minutes) ?> minutes</span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($procedure->cost)): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Cost:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-dollar-sign me-1 text-success"></i>
                            <span class="text-success fw-bold"><?php echo number_format($procedure->cost, 2) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Created:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-calendar-plus me-1 text-success"></i>
                            <span class="text-dark"><?php echo h($procedure->created->format('F j, Y')) ?></span>
                            <small class="text-muted ms-2"><?php echo h($procedure->created->format('g:i A')) ?></small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Last Modified:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-edit me-1 text-info"></i>
                            <span class="text-dark"><?php echo h($procedure->modified->format('F j, Y')) ?></span>
                            <small class="text-muted ms-2"><?php echo h($procedure->modified->format('g:i A')) ?></small>
                        </div>
                    </div>
                    
                    <?php if (!empty($procedure->description)): ?>
                    <div class="row">
                        <div class="col-sm-4 fw-semibold text-muted">Description:</div>
                        <div class="col-sm-8">
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <p class="mb-0 text-dark"><?php echo $this->Text->autoParagraph(h($procedure->description)) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notes Section -->
            <?php if (!empty($procedure->notes)): ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-sticky-note me-2 text-warning"></i>Additional Notes
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <p class="mb-0 text-dark"><?php echo $this->Text->autoParagraph(h($procedure->notes)) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-md-4">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-bar me-2 text-info"></i>Procedure Statistics
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                <i class="fas fa-clock text-primary fs-1 mb-2"></i>
                                <h4 class="text-primary mb-1"><?php echo $procedure->duration_minutes ?? 0 ?></h4>
                                <small class="text-muted">Minutes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <i class="fas fa-dollar-sign text-success fs-1 mb-2"></i>
                                <h4 class="text-success mb-1"><?php echo $procedure->cost ? number_format($procedure->cost, 0) : 0 ?></h4>
                                <small class="text-muted">Cost</small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($procedure->risk_level)): ?>
                    <hr class="my-3">
                    <div class="text-center">
                        <h6 class="text-muted mb-2">Risk Assessment</h6>
                        <span class="badge bg-<?php echo $procedure->risk_level === 'high' ? 'danger' : ($procedure->risk_level === 'medium' ? 'warning text-dark' : 'success') ?> fs-6">
                            <i class="fas fa-shield-alt me-1"></i><?php echo ucfirst(h($procedure->risk_level)) ?> Risk
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="d-grid gap-2">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit me-2"></i>Edit Procedure',
                            ['action' => 'edit', $procedure->id],
                            ['class' => 'btn btn-warning text-dark fw-bold', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-list me-2"></i>All Procedures',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-secondary', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-plus me-2"></i>Add New Procedure',
                            ['action' => 'add'],
                            ['class' => 'btn btn-outline-success', 'escape' => false]
                        ) ?>
                        <?php echo $this->Form->postLink(
                            '<i class="fas fa-trash me-2"></i>Delete Procedure',
                            ['action' => 'delete', $procedure->id],
                            [
                                'class' => 'btn btn-outline-danger',
                                'escape' => false,
                                'confirm' => 'Are you sure you want to delete this procedure? This action cannot be undone.'
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Exams -->
    <?php if (!empty($procedure->exams)): ?>
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-file-medical me-2 text-primary"></i>Related Exams (<?php echo count($procedure->exams) ?>)
            </h5>
        </div>
        <div class="card-body bg-white p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4 fw-semibold text-uppercase small">ID</th>
                            <th class="border-0 fw-semibold text-uppercase small">Exam Name</th>
                            <th class="border-0 fw-semibold text-uppercase small">Duration</th>
                            <th class="border-0 fw-semibold text-uppercase small">Cost</th>
                            <th class="border-0 text-center fw-semibold text-uppercase small">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($procedure->exams as $exam): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-hashtag"></i><?php echo h($exam->id) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo h($exam->name) ?></div>
                            </td>
                            <td>
                                <?php if ($exam->duration_minutes): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <i class="fas fa-clock me-1"></i><?php echo h($exam->duration_minutes) ?> min
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($exam->cost): ?>
                                    <span class="text-success fw-bold">
                                        <i class="fas fa-dollar-sign me-1"></i><?php echo number_format($exam->cost, 2) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-eye"></i>',
                                    ['controller' => 'Exams', 'action' => 'view', $exam->id],
                                    ['class' => 'btn btn-outline-info btn-sm', 'escape' => false, 'title' => 'View Exam']
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>