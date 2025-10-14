<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Department $department
 */
?>
<div class="departments view content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-building me-2"></i><?php echo h($department->name) ?></h3>
        <div>
            <?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $department->id], ['class' => 'btn btn-warning']) ?>
            <?php echo $this->Html->link(__('List Departments'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Department Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th style="width: 30%;"><?php echo __('ID') ?></th>
                            <td><?php echo $this->Number->format($department->id) ?></td>
                        </tr>
                        <tr>
                            <th><?php echo __('Name') ?></th>
                            <td><?php echo h($department->name) ?></td>
                        </tr>
                        <tr>
                            <th><?php echo __('Hospital') ?></th>
                            <td><?php echo $department->hasValue('hospital') ? $this->Html->link($department->hospital->name, ['controller' => 'Hospitals', 'action' => 'view', $department->hospital->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?php echo __('Created') ?></th>
                            <td><?php echo h($department->created->format('F j, Y g:i A')) ?></td>
                        </tr>
                        <tr>
                            <th><?php echo __('Modified') ?></th>
                            <td><?php echo h($department->modified->format('F j, Y g:i A')) ?></td>
                        </tr>
                    </table>
                    
                    <?php if (!empty($department->description)): ?>
                    <div class="mt-4">
                        <h6><i class="fas fa-align-left me-2"></i>Description</h6>
                        <div class="card bg-light">
                            <div class="card-body">
                                <?php echo $this->Text->autoParagraph(h($department->description)); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-2">
                                    <h4 class="mb-0"><?php echo count($department->exams ?? []) ?></h4>
                                    <small>Exams</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-success text-white">
                                <div class="card-body py-2">
                                    <h4 class="mb-0"><?php echo count($department->procedures ?? []) ?></h4>
                                    <small>Procedures</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($department->exams)): ?>
    <div class="card shadow-sm mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-file-medical me-2"></i>Related Exams</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th><?php echo __('ID') ?></th>
                            <th><?php echo __('Name') ?></th>
                            <th><?php echo __('Duration') ?></th>
                            <th><?php echo __('Cost') ?></th>
                            <th class="actions"><?php echo __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($department->exams as $exam): ?>
                        <tr>
                            <td><?php echo h($exam->id) ?></td>
                            <td><?php echo h($exam->name) ?></td>
                            <td><?php echo $exam->duration ? h($exam->duration) . ' min' : '-' ?></td>
                            <td><?php echo $exam->cost ? '$' . number_format($exam->cost, 2) : '-' ?></td>
                            <td class="actions">
                                <?php echo $this->Html->link(__('View'), ['controller' => 'Exams', 'action' => 'view', $exam->id], ['class' => 'btn btn-sm btn-outline-info']) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($department->procedures)): ?>
    <div class="card shadow-sm mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-procedures me-2"></i>Related Procedures</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th><?php echo __('ID') ?></th>
                            <th><?php echo __('Name') ?></th>
                            <th><?php echo __('Type') ?></th>
                            <th><?php echo __('Risk Level') ?></th>
                            <th class="actions"><?php echo __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($department->procedures as $procedure): ?>
                        <tr>
                            <td><?php echo h($procedure->id) ?></td>
                            <td><?php echo h($procedure->name) ?></td>
                            <td><?php echo h($procedure->type) ?: '-' ?></td>
                            <td>
                                <?php if ($procedure->risk_level): ?>
                                    <span class="badge bg-<?php echo $procedure->risk_level === 'high' ? 'danger' : ($procedure->risk_level === 'medium' ? 'warning' : 'success') ?>">
                                        <?php echo h($procedure->risk_level) ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <?php echo $this->Html->link(__('View'), ['controller' => 'Procedures', 'action' => 'view', $procedure->id], ['class' => 'btn btn-sm btn-outline-info']) ?>
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