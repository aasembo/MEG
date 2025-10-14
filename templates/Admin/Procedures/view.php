<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Procedure $procedure
 */
?>
<div class="procedures view content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-procedures me-2"></i><?php echo h($procedure->name) ?></h3>
        <div class="btn-group">
            <?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $procedure->id], ['class' => 'btn btn-primary']) ?>
            <?php echo $this->Html->link(__('List Procedures'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php echo $this->Html->link(__('Add New'), ['action' => 'add'], ['class' => 'btn btn-outline-success']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Procedure Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th scope="row" style="width: 200px;"><?php echo __('ID') ?></th>
                            <td><?php echo $this->Number->format($procedure->id) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Name') ?></th>
                            <td><?php echo h($procedure->name) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Type') ?></th>
                            <td><?php echo h($procedure->type) ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Department') ?></th>
                            <td><?php echo $procedure->hasValue('department') ? $this->Html->link($procedure->department->name, ['controller' => 'Departments', 'action' => 'view', $procedure->department->id]) : '-' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Sedation') ?></th>
                            <td><?php echo $procedure->hasValue('sedation') ? $this->Html->link($procedure->sedation->level, ['controller' => 'Sedations', 'action' => 'view', $procedure->sedation->id]) : '-' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Risk Level') ?></th>
                            <td>
                                <?php if ($procedure->risk_level): ?>
                                    <span class="badge bg-<?php echo $procedure->risk_level === 'high' ? 'danger' : ($procedure->risk_level === 'medium' ? 'warning' : 'success') ?>">
                                        <?php echo h($procedure->risk_level) ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Consent Required') ?></th>
                            <td>
                                <?php if ($procedure->consent_required): ?>
                                    <span class="badge bg-warning">Required</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Required</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Duration') ?></th>
                            <td><?php echo $procedure->duration_minutes ? $this->Number->format($procedure->duration_minutes) . ' minutes' : '-' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Cost') ?></th>
                            <td><?php echo $procedure->cost ? '$' . $this->Number->format($procedure->cost) : '-' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Created') ?></th>
                            <td><?php echo h($procedure->created) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Modified') ?></th>
                            <td><?php echo h($procedure->modified) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if (!empty($procedure->description)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-text me-2"></i>Description</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo $this->Text->autoParagraph(h($procedure->description)); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($procedure->notes)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo $this->Text->autoParagraph(h($procedure->notes)); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php echo $this->Html->link(__('Edit Procedure'), ['action' => 'edit', $procedure->id], ['class' => 'btn btn-primary']) ?>
                        <?php echo $this->Html->link(__('List Procedures'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                        <?php echo $this->Html->link(__('Add New Procedure'), ['action' => 'add'], ['class' => 'btn btn-outline-success']) ?>
                        <?php echo $this->Form->postLink(__('Delete Procedure'), ['action' => 'delete', $procedure->id], ['confirm' => __('Are you sure you want to delete # {0}?', $procedure->id), 'class' => 'btn btn-outline-danger']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>