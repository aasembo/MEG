<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Modality $modality
 */
?>
<?php $this->assign('title', 'Modality Details'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-camera me-2"></i><?php echo h($modality->name) ?>
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-hashtag me-2"></i>Modality ID: <?php echo h($modality->id) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit me-1"></i>Edit Modality',
                            ['action' => 'edit', $modality->id],
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
        <!-- Modality Information -->
        <div class="col-md-8">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-camera me-2 text-warning"></i>Modality Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Modality Name:</div>
                        <div class="col-sm-8 text-dark fw-bold"><?php echo h($modality->name) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Modality ID:</div>
                        <div class="col-sm-8">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-hashtag"></i><?php echo h($modality->id) ?>
                            </span>
                        </div>
                    </div>
                    <?php if ($modality->hasValue('hospital')): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Hospital:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-hospital me-1 text-warning"></i>
                            <?php echo $this->Html->link(
                                h($modality->hospital->name),
                                ['controller' => 'Hospitals', 'action' => 'view', $modality->hospital->id],
                                ['class' => 'text-decoration-none text-dark fw-bold']
                            ) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Created:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-calendar-plus me-1 text-success"></i>
                            <span class="text-dark"><?php echo h($modality->created->format('F j, Y')) ?></span>
                            <small class="text-muted ms-2"><?php echo h($modality->created->format('g:i A')) ?></small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Last Modified:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-edit me-1 text-info"></i>
                            <span class="text-dark"><?php echo h($modality->modified->format('F j, Y')) ?></span>
                            <small class="text-muted ms-2"><?php echo h($modality->modified->format('g:i A')) ?></small>
                        </div>
                    </div>
                    
                    <?php if (!empty($modality->description)): ?>
                    <div class="row">
                        <div class="col-sm-4 fw-semibold text-muted">Description:</div>
                        <div class="col-sm-8">
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <p class="mb-0 text-dark"><?php echo $this->Text->autoParagraph(h($modality->description)) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Statistics Sidebar -->
        <div class="col-md-4">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-bar me-2 text-info"></i>Modality Statistics
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                <i class="fas fa-file-medical text-primary fs-1 mb-2"></i>
                                <h4 class="text-primary mb-1"><?php echo count($modality->exams ?? []) ?></h4>
                                <small class="text-muted">Exams</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <i class="fas fa-calendar text-success fs-1 mb-2"></i>
                                <h4 class="text-success mb-1">
                                    <?php 
                                    $recent = 0;
                                    $thirtyDaysAgo = new DateTime('-30 days');
                                    if ($modality->created >= $thirtyDaysAgo) {
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
                            '<i class="fas fa-edit me-2"></i>Edit Modality',
                            ['action' => 'edit', $modality->id],
                            ['class' => 'btn btn-warning text-dark fw-bold', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-list me-2"></i>All Modalities',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-secondary', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-plus me-2"></i>Add New Modality',
                            ['action' => 'add'],
                            ['class' => 'btn btn-outline-success', 'escape' => false]
                        ) ?>
                        <?php echo $this->Form->postLink(
                            '<i class="fas fa-trash me-2"></i>Delete Modality',
                            ['action' => 'delete', $modality->id],
                            [
                                'class' => 'btn btn-outline-danger',
                                'escape' => false,
                                'confirm' => 'Are you sure you want to delete this modality? This action cannot be undone.'
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Exams -->
    <?php if (!empty($modality->exams)): ?>
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-file-medical me-2 text-primary"></i>Related Exams (<?php echo count($modality->exams) ?>)
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
                        <?php foreach ($modality->exams as $exam): ?>
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