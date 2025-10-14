<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Procedure> $procedures
 */
?>
<div class="procedures index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-procedures me-2"></i><?php echo __('Procedures') ?></h3>
        <?php echo $this->Html->link(__('Add Procedure'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Procedures List
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($procedures->toArray())): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $this->Paginator->sort('id') ?></th>
                            <th><?php echo $this->Paginator->sort('name') ?></th>
                            <th><?php echo $this->Paginator->sort('type') ?></th>
                            <th><?php echo $this->Paginator->sort('department.name', 'Department') ?></th>
                            <th><?php echo $this->Paginator->sort('sedation.level', 'Sedation') ?></th>
                            <th><?php echo $this->Paginator->sort('risk_level', 'Risk') ?></th>
                            <th><?php echo $this->Paginator->sort('consent_required', 'Consent') ?></th>
                            <th class="actions"><?php echo __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($procedures as $procedure): ?>
                        <tr>
                            <td><?php echo $this->Number->format($procedure->id) ?></td>
                            <td><strong><?php echo h($procedure->name) ?></strong></td>
                            <td><?php echo h($procedure->type) ?: '-' ?></td>
                            <td><?php echo $procedure->hasValue('department') ? h($procedure->department->name) : '-' ?></td>
                            <td><?php echo $procedure->hasValue('sedation') ? h($procedure->sedation->level) : '-' ?></td>
                            <td>
                                <?php if ($procedure->risk_level): ?>
                                    <span class="badge bg-<?php echo $procedure->risk_level === 'high' ? 'danger' : ($procedure->risk_level === 'medium' ? 'warning' : 'success') ?>">
                                        <?php echo h($procedure->risk_level) ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($procedure->consent_required): ?>
                                    <span class="badge bg-warning">Required</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Required</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php echo $this->Html->link('<i class="fas fa-eye"></i>', ['action' => 'view', $procedure->id], ['class' => 'btn btn-outline-info btn-sm', 'escape' => false, 'title' => 'View']) ?>
                                    <?php echo $this->Html->link('<i class="fas fa-edit"></i>', ['action' => 'edit', $procedure->id], ['class' => 'btn btn-outline-warning btn-sm', 'escape' => false, 'title' => 'Edit']) ?>
                                    <?php echo $this->Form->postLink('<i class="fas fa-trash"></i>', ['action' => 'delete', $procedure->id], ['confirm' => __('Are you sure you want to delete # {0}?', $procedure->id), 'class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Delete']) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-procedures fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Procedures Found</h4>
                    <p class="text-muted mb-4">You haven't created any medical procedures yet. Procedures define the specific medical services offered by your facility.</p>
                </div>
                <div>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-plus me-2"></i>Add Your First Procedure', 
                        ['action' => 'add'], 
                        ['class' => 'btn btn-primary btn-lg', 'escape' => false]
                    ) ?>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Examples: Blood Test, X-Ray Imaging, MRI Scan, Surgery Prep, etc.
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php echo $this->element('admin_pagination', ['items' => $procedures, 'itemType' => 'procedures']) ?>
    </div>

</div>