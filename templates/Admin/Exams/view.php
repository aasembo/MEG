<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Exam $exam
 */
?>
<?php $this->assign('title', 'Exam Details'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-file-medical me-2"></i><?php echo h($exam->name) ?>
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-hashtag me-2"></i>Exam ID: <?php echo h($exam->id) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit me-1"></i>Edit Exam',
                            ['action' => 'edit', $exam->id],
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
        <!-- Exam Information -->
        <div class="col-md-8">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-file-medical me-2 text-warning"></i>Exam Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Exam Name:</div>
                        <div class="col-sm-8 text-dark fw-bold"><?php echo h($exam->name) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Exam ID:</div>
                        <div class="col-sm-8">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-hashtag"></i><?php echo h($exam->id) ?>
                            </span>
                        </div>
                    </div>
                    <?php if ($exam->hasValue('department')): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Department:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-building me-1 text-warning"></i>
                            <?php echo $this->Html->link(
                                h($exam->department->name),
                                ['controller' => 'Departments', 'action' => 'view', $exam->department->id],
                                ['class' => 'text-decoration-none text-dark fw-bold']
                            ) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($exam->hasValue('modality')): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Modality:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-desktop me-1 text-primary"></i>
                            <?php echo $this->Html->link(
                                h($exam->modality->name),
                                ['controller' => 'Modalities', 'action' => 'view', $exam->modality->id],
                                ['class' => 'text-decoration-none text-dark fw-bold']
                            ) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($exam->duration_minutes)): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Duration:</div>
                        <div class="col-sm-8">
                            <span class="badge bg-info bg-opacity-10 text-info">
                                <i class="fas fa-clock me-1"></i><?php echo h($exam->duration_minutes) ?> minutes
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Preparation Required:</div>
                        <div class="col-sm-8">
                            <?php if ($exam->preparation_required): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Yes
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>No
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Contrast Required:</div>
                        <div class="col-sm-8">
                            <?php if ($exam->contrast_required): ?>
                                <span class="badge bg-danger">
                                    <i class="fas fa-syringe me-1"></i>Yes
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>No
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Created:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-calendar-plus me-1 text-success"></i>
                            <span class="text-dark"><?php echo h($exam->created->format('F j, Y')) ?></span>
                            <small class="text-muted ms-2"><?php echo h($exam->created->format('g:i A')) ?></small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Last Modified:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-edit me-1 text-info"></i>
                            <span class="text-dark"><?php echo h($exam->modified->format('F j, Y')) ?></span>
                            <small class="text-muted ms-2"><?php echo h($exam->modified->format('g:i A')) ?></small>
                        </div>
                    </div>
                    
                    <?php if (!empty($exam->description)): ?>
                    <div class="row">
                        <div class="col-sm-4 fw-semibold text-muted">Description:</div>
                        <div class="col-sm-8">
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <p class="mb-0 text-dark"><?php echo h($exam->description) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($exam->preparation_instructions)): ?>
            <!-- Preparation Instructions -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-clipboard-list me-2 text-warning"></i>Preparation Instructions
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="alert alert-info border-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Patient Preparation:</strong>
                    </div>
                    <p class="text-dark mb-0"><?php echo nl2br(h($exam->preparation_instructions)) ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-md-4">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-bar me-2 text-info"></i>Exam Statistics
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                <i class="fas fa-clock text-primary fs-1 mb-2"></i>
                                <h4 class="text-primary mb-1"><?php echo $exam->duration_minutes ?? 'N/A' ?></h4>
                                <small class="text-muted">Minutes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <i class="fas fa-procedures text-success fs-1 mb-2"></i>
                                <h4 class="text-success mb-1">0</h4>
                                <small class="text-muted">Cases</small>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($exam->hasValue('hospital')): ?>
                    <hr class="my-3">
                    <div class="text-center">
                        <strong class="text-dark">Hospital:</strong><br>
                        <span class="text-muted"><?php echo h($exam->hospital->name) ?></span>
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
                            '<i class="fas fa-edit me-2"></i>Edit Exam',
                            ['action' => 'edit', $exam->id],
                            ['class' => 'btn btn-warning text-dark fw-bold', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-plus me-2"></i>Add New Exam',
                            ['action' => 'add'],
                            ['class' => 'btn btn-outline-success', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-list me-2"></i>All Exams',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-secondary', 'escape' => false]
                        ) ?>
                        <?php if ($exam->hasValue('department')): ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-building me-2"></i>View Department',
                            ['controller' => 'Departments', 'action' => 'view', $exam->department->id],
                            ['class' => 'btn btn-outline-info', 'escape' => false]
                        ) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>