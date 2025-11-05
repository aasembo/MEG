<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Department $department
 */
?>
<?php $this->assign('title', 'Department Details'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-building me-2"></i><?php echo h($department->name) ?>
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-hashtag me-2"></i>Department ID: <?php echo h($department->id) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit me-1"></i>Edit Department',
                            ['action' => 'edit', $department->id],
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
        <!-- Department Information -->
        <div class="col-md-8">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-building me-2 text-warning"></i>Department Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Department Name:</div>
                        <div class="col-sm-8 text-dark fw-bold"><?php echo h($department->name) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Department ID:</div>
                        <div class="col-sm-8">
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-hashtag"></i><?php echo h($department->id) ?>
                            </span>
                        </div>
                    </div>
                    <?php if ($department->hasValue('hospital')): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Hospital:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-hospital me-1 text-warning"></i>
                            <?php echo $this->Html->link(
                                h($department->hospital->name),
                                ['controller' => 'Hospitals', 'action' => 'view', $department->hospital->id],
                                ['class' => 'text-decoration-none text-dark fw-bold']
                            ) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Created:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-calendar-plus me-1 text-success"></i>
                            <span class="text-dark"><?php echo h($department->created->format('F j, Y')) ?></span>
                            <small class="text-muted ms-2"><?php echo h($department->created->format('g:i A')) ?></small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Last Modified:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-edit me-1 text-info"></i>
                            <span class="text-dark"><?php echo h($department->modified->format('F j, Y')) ?></span>
                            <small class="text-muted ms-2"><?php echo h($department->modified->format('g:i A')) ?></small>
                        </div>
                    </div>
                    
                    <?php if (!empty($department->description)): ?>
                    <div class="row">
                        <div class="col-sm-4 fw-semibold text-muted">Description:</div>
                        <div class="col-sm-8">
                            <div class="card bg-light border-0">
                                <div class="card-body p-3">
                                    <p class="mb-0 text-dark"><?php echo h($department->description) ?></p>
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
                        <i class="fas fa-chart-bar me-2 text-info"></i>Department Statistics
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                <i class="fas fa-file-medical text-primary fs-1 mb-2"></i>
                                <h4 class="text-primary mb-1"><?php echo count($department->exams ?? []) ?></h4>
                                <small class="text-muted">Exams</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <i class="fas fa-procedures text-success fs-1 mb-2"></i>
                                <h4 class="text-success mb-1"><?php echo count($department->procedures ?? []) ?></h4>
                                <small class="text-muted">Procedures</small>
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
                            '<i class="fas fa-edit me-2"></i>Edit Department',
                            ['action' => 'edit', $department->id],
                            ['class' => 'btn btn-warning text-dark fw-bold', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-list me-2"></i>All Departments',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-secondary', 'escape' => false]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Exams -->
    <?php if (!empty($department->exams)): ?>
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-file-medical me-2 text-primary"></i>Related Exams (<?php echo count($department->exams) ?>)
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
                        <?php foreach ($department->exams as $exam): ?>
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
                                <?php if ($exam->duration): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <i class="fas fa-clock me-1"></i><?php echo h($exam->duration) ?> min
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

    <!-- Related Procedures -->
    <?php if (!empty($department->procedures)): ?>
    <div class="card border-0 shadow">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-procedures me-2 text-success"></i>Related Procedures (<?php echo count($department->procedures) ?>)
            </h5>
        </div>
        <div class="card-body bg-white p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4 fw-semibold text-uppercase small">ID</th>
                            <th class="border-0 fw-semibold text-uppercase small">Procedure Name</th>
                            <th class="border-0 fw-semibold text-uppercase small">Type</th>
                            <th class="border-0 fw-semibold text-uppercase small">Risk Level</th>
                            <th class="border-0 text-center fw-semibold text-uppercase small">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($department->procedures as $procedure): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-hashtag"></i><?php echo h($procedure->id) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo h($procedure->name) ?></div>
                            </td>
                            <td>
                                <?php if ($procedure->type): ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                        <?php echo h($procedure->type) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($procedure->risk_level): ?>
                                    <span class="badge bg-<?php echo $procedure->risk_level === 'high' ? 'danger' : ($procedure->risk_level === 'medium' ? 'warning text-dark' : 'success') ?>">
                                        <i class="fas fa-exclamation-triangle me-1"></i><?php echo ucfirst(h($procedure->risk_level)) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-eye"></i>',
                                    ['controller' => 'Procedures', 'action' => 'view', $procedure->id],
                                    ['class' => 'btn btn-outline-success btn-sm', 'escape' => false, 'title' => 'View Procedure']
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