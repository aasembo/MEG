<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ExamsProcedure $examsProcedure
 */
?>
<?php $this->assign('title', 'Exam-Procedure Association Details'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-link me-2"></i>Exam-Procedure Association
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-hashtag me-2"></i>Association ID: <?php echo h($examsProcedure->id) ?>
                        <?php if ($examsProcedure->hasValue('exam') && $examsProcedure->hasValue('procedure')): ?>
                            <span class="mx-2">•</span>
                            <i class="fas fa-stethoscope me-1"></i><?php echo h($examsProcedure->exam->name) ?>
                            <i class="fas fa-arrow-right mx-2"></i>
                            <i class="fas fa-procedures me-1"></i><?php echo h($examsProcedure->procedure->name) ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit me-1"></i>Edit Association',
                            ['action' => 'edit', $examsProcedure->id],
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
        <!-- Association Information -->
        <div class="col-md-8">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-link me-2 text-warning"></i>Association Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Association ID:</div>
                        <div class="col-sm-8">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-hashtag"></i><?php echo h($examsProcedure->id) ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Exam:</div>
                        <div class="col-sm-8">
                            <?php if ($examsProcedure->hasValue('exam')): ?>
                                <i class="fas fa-stethoscope me-1 text-primary"></i>
                                <?php echo $this->Html->link(
                                    h($examsProcedure->exam->name),
                                    ['controller' => 'Exams', 'action' => 'view', $examsProcedure->exam->id],
                                    ['class' => 'text-decoration-none text-dark fw-bold']
                                ) ?>
                            <?php else: ?>
                                <span class="text-muted">No exam associated</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Procedure:</div>
                        <div class="col-sm-8">
                            <?php if ($examsProcedure->hasValue('procedure')): ?>
                                <i class="fas fa-procedures me-1 text-success"></i>
                                <?php echo $this->Html->link(
                                    h($examsProcedure->procedure->name),
                                    ['controller' => 'Procedures', 'action' => 'view', $examsProcedure->procedure->id],
                                    ['class' => 'text-decoration-none text-dark fw-bold']
                                ) ?>
                            <?php else: ?>
                                <span class="text-muted">No procedure associated</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Contrast Required:</div>
                        <div class="col-sm-8">
                            <?php if ($examsProcedure->contrast_required): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Required
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary text-white">
                                    <i class="fas fa-times me-1"></i>Not Required
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Sedation Required:</div>
                        <div class="col-sm-8">
                            <?php if ($examsProcedure->sedation_required): ?>
                                <span class="badge bg-danger text-white">
                                    <i class="fas fa-bed me-1"></i>Required
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success text-white">
                                    <i class="fas fa-check me-1"></i>Not Required
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Created:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-calendar-plus me-1 text-success"></i>
                            <span class="text-dark"><?php echo h($examsProcedure->created->format('F j, Y')) ?></span>
                            <small class="text-muted ms-2"><?php echo h($examsProcedure->created->format('g:i A')) ?></small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Last Modified:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-edit me-1 text-info"></i>
                            <span class="text-dark"><?php echo h($examsProcedure->modified->format('F j, Y')) ?></span>
                            <small class="text-muted ms-2"><?php echo h($examsProcedure->modified->format('g:i A')) ?></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preparation Instructions -->
            <?php if (!empty($examsProcedure->preparation_instructions)): ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-clipboard-list me-2 text-info"></i>Preparation Instructions
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="card bg-light border-0">
                        <div class="card-body p-3">
                            <p class="mb-0 text-dark"><?php echo $this->Text->autoParagraph(h($examsProcedure->preparation_instructions)); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Post-Procedure Care -->
            <?php if (!empty($examsProcedure->post_procedure_care)): ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-heart me-2 text-danger"></i>Post-Procedure Care
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="card bg-light border-0">
                        <div class="card-body p-3">
                            <p class="mb-0 text-dark"><?php echo $this->Text->autoParagraph(h($examsProcedure->post_procedure_care)); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Notes -->
            <?php if (!empty($examsProcedure->notes)): ?>
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-sticky-note me-2 text-secondary"></i>Additional Notes
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="card bg-light border-0">
                        <div class="card-body p-3">
                            <p class="mb-0 text-dark"><?php echo $this->Text->autoParagraph(h($examsProcedure->notes)); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-md-4">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-bar me-2 text-info"></i>Association Statistics
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-warning bg-opacity-10 rounded">
                                <i class="fas fa-contrast text-warning fs-1 mb-2"></i>
                                <h4 class="text-warning mb-1"><?php echo $examsProcedure->contrast_required ? 'Yes' : 'No' ?></h4>
                                <small class="text-muted">Contrast</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                                <i class="fas fa-bed text-danger fs-1 mb-2"></i>
                                <h4 class="text-danger mb-1"><?php echo $examsProcedure->sedation_required ? 'Yes' : 'No' ?></h4>
                                <small class="text-muted">Sedation</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-info bg-opacity-10 rounded">
                                <i class="fas fa-clipboard-list text-info fs-1 mb-2"></i>
                                <h4 class="text-info mb-1"><?php echo !empty($examsProcedure->preparation_instructions) ? 'Yes' : 'No' ?></h4>
                                <small class="text-muted">Prep Guide</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <i class="fas fa-heart text-success fs-1 mb-2"></i>
                                <h4 class="text-success mb-1"><?php echo !empty($examsProcedure->post_procedure_care) ? 'Yes' : 'No' ?></h4>
                                <small class="text-muted">Post-Care</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="d-grid gap-2">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit me-2"></i>Edit Association',
                            ['action' => 'edit', $examsProcedure->id],
                            ['class' => 'btn btn-warning text-dark fw-bold', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-plus me-2"></i>Add New Association',
                            ['action' => 'add'],
                            ['class' => 'btn btn-outline-success', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-list me-2"></i>All Associations',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-secondary', 'escape' => false]
                        ) ?>
                        <?php echo $this->Form->postLink(
                            '<i class="fas fa-trash me-2"></i>Delete Association',
                            ['action' => 'delete', $examsProcedure->id],
                            [
                                'confirm' => __('Are you sure you want to delete this association?'),
                                'class' => 'btn btn-outline-danger',
                                'escape' => false
                            ]
                        ) ?>
                    </div>
                </div>
            </div>

            <!-- Related Items -->
            <?php if ($examsProcedure->hasValue('exam') || $examsProcedure->hasValue('procedure')): ?>
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-external-link-alt me-2 text-primary"></i>Related Items
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="d-grid gap-2">
                        <?php if ($examsProcedure->hasValue('exam')): ?>
                            <?php echo $this->Html->link(
                                '<i class="fas fa-stethoscope me-2"></i>View Exam Details',
                                ['controller' => 'Exams', 'action' => 'view', $examsProcedure->exam->id],
                                ['class' => 'btn btn-outline-primary', 'escape' => false]
                            ) ?>
                        <?php endif; ?>
                        <?php if ($examsProcedure->hasValue('procedure')): ?>
                            <?php echo $this->Html->link(
                                '<i class="fas fa-procedures me-2"></i>View Procedure Details',
                                ['controller' => 'Procedures', 'action' => 'view', $examsProcedure->procedure->id],
                                ['class' => 'btn btn-outline-success', 'escape' => false]
                            ) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>