<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Sedation $sedation
 */
?>
<?php $this->assign('title', 'Sedation Details'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-bed me-2"></i><?php echo h($sedation->level) ?>
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-hashtag me-2"></i>Sedation ID: <?php echo h($sedation->id) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit me-1"></i>Edit Sedation',
                            ['action' => 'edit', $sedation->id],
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
        <!-- Sedation Information -->
        <div class="col-md-8">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bed me-2 text-warning"></i>Sedation Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Sedation Level:</div>
                        <div class="col-sm-8 text-dark fw-bold"><?php echo h($sedation->level) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Sedation ID:</div>
                        <div class="col-sm-8">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-hashtag"></i><?php echo h($sedation->id) ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Type:</div>
                        <div class="col-sm-8">
                            <?php if (!empty($sedation->type)): ?>
                                <span class="text-dark"><?php echo h($sedation->type) ?></span>
                            <?php else: ?>
                                <span class="text-muted">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Risk Category:</div>
                        <div class="col-sm-8">
                            <?php if ($sedation->risk_category): ?>
                                <span class="badge bg-<?php echo $sedation->risk_category === 'high' ? 'danger' : ($sedation->risk_category === 'medium' ? 'warning text-dark' : 'success') ?>">
                                    <?php echo h(ucfirst($sedation->risk_category)) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Recovery Time:</div>
                        <div class="col-sm-8">
                            <?php if ($sedation->recovery_time): ?>
                                <i class="fas fa-clock me-1 text-info"></i>
                                <span class="text-dark"><?php echo h($sedation->recovery_time) ?> minutes</span>
                            <?php else: ?>
                                <span class="text-muted">Not specified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Monitoring Required:</div>
                        <div class="col-sm-8">
                            <?php if ($sedation->monitoring_required): ?>
                                <span class="badge bg-warning text-dark">Required</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Not Required</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Pre-medication Required:</div>
                        <div class="col-sm-8">
                            <?php if ($sedation->pre_medication_required): ?>
                                <span class="badge bg-info">Required</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Not Required</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Created:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-calendar-plus me-1 text-success"></i>
                            <span class="text-dark"><?php echo h($sedation->created->format('F j, Y')) ?></span>
                            <small class="text-muted ms-2"><?php echo h($sedation->created->format('g:i A')) ?></small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Last Modified:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-edit me-1 text-info"></i>
                            <span class="text-dark"><?php echo h($sedation->modified->format('F j, Y')) ?></span>
                            <small class="text-muted ms-2"><?php echo h($sedation->modified->format('g:i A')) ?></small>
                        </div>
                    </div>
                    
                    <?php if (!empty($sedation->description)): ?>
                    <div class="row">
                        <div class="col-sm-4 fw-semibold text-muted">Description:</div>
                        <div class="col-sm-8">
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <p class="mb-0 text-dark"><?php echo $this->Text->autoParagraph(h($sedation->description)) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($sedation->medications)): ?>
            <!-- Medications -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-pills me-2 text-success"></i>Medications
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="card bg-light border-0">
                        <div class="card-body p-3">
                            <p class="mb-0 text-dark"><?php echo $this->Text->autoParagraph(h($sedation->medications)) ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($sedation->contraindications)): ?>
            <!-- Contraindications -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>Contraindications
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="alert alert-warning border-0">
                        <div class="text-dark"><?php echo $this->Text->autoParagraph(h($sedation->contraindications)) ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($sedation->notes)): ?>
            <!-- Additional Notes -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-sticky-note me-2 text-info"></i>Additional Notes
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="card bg-light border-0">
                        <div class="card-body p-3">
                            <p class="mb-0 text-dark"><?php echo $this->Text->autoParagraph(h($sedation->notes)) ?></p>
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
                        <i class="fas fa-chart-bar me-2 text-info"></i>Sedation Statistics
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                <i class="fas fa-procedures text-primary fs-1 mb-2"></i>
                                <h4 class="text-primary mb-1">0</h4>
                                <small class="text-muted">Procedures</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <i class="fas fa-calendar text-success fs-1 mb-2"></i>
                                <h4 class="text-success mb-1">
                                    <?php 
                                    $recent = 0;
                                    $thirtyDaysAgo = new DateTime('-30 days');
                                    if ($sedation->created >= $thirtyDaysAgo) {
                                        $recent = 1;
                                    }
                                    echo $recent;
                                    ?>
                                </h4>
                                <small class="text-muted">Recent</small>
                            </div>
                        </div>
                    </div>
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
                            '<i class="fas fa-edit me-2"></i>Edit Sedation',
                            ['action' => 'edit', $sedation->id],
                            ['class' => 'btn btn-warning text-dark fw-bold', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-list me-2"></i>All Sedations',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-secondary', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-plus me-2"></i>Add New Sedation',
                            ['action' => 'add'],
                            ['class' => 'btn btn-outline-success', 'escape' => false]
                        ) ?>
                        <?php echo $this->Form->postLink(
                            '<i class="fas fa-trash me-2"></i>Delete Sedation',
                            ['action' => 'delete', $sedation->id],
                            [
                                'class' => 'btn btn-outline-danger',
                                'escape' => false,
                                'confirm' => 'Are you sure you want to delete this sedation level? This action cannot be undone.'
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>