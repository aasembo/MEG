<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Sedation> $sedations
 */
?>
<div class="sedations index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-bed me-2"></i><?php echo __('Sedations') ?></h3>
        <?php echo $this->Html->link(__('Add Sedation'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $this->Paginator->sort('id') ?></th>
                            <th><?php echo $this->Paginator->sort('level') ?></th>
                            <th><?php echo $this->Paginator->sort('type') ?></th>
                            <th><?php echo $this->Paginator->sort('monitoring_required', 'Monitoring') ?></th>
                            <th><?php echo $this->Paginator->sort('recovery_time', 'Recovery (min)') ?></th>
                            <th><?php echo $this->Paginator->sort('risk_category', 'Risk') ?></th>
                            <th><?php echo $this->Paginator->sort('created', 'Created') ?></th>
                            <th class="actions"><?php echo __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sedations as $sedation): ?>
                        <tr>
                            <td><?php echo $this->Number->format($sedation->id) ?></td>
                            <td><strong><?php echo h($sedation->level) ?></strong></td>
                            <td><?php echo h($sedation->type) ?: '-' ?></td>
                            <td>
                                <?php if ($sedation->monitoring_required): ?>
                                    <span class="badge bg-warning">Required</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Required</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $sedation->recovery_time ? $this->Number->format($sedation->recovery_time) . ' min' : '-' ?></td>
                            <td>
                                <?php if ($sedation->risk_category): ?>
                                    <span class="badge bg-<?php echo $sedation->risk_category === 'high' ? 'danger' : ($sedation->risk_category === 'medium' ? 'warning' : 'success') ?>">
                                        <?php echo h($sedation->risk_category) ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?php echo h($sedation->created) ?></td>
                            <td class="actions">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php echo $this->Html->link('<i class="fas fa-eye"></i>', ['action' => 'view', $sedation->id], ['class' => 'btn btn-outline-info btn-sm', 'escape' => false, 'title' => 'View']) ?>
                                    <?php echo $this->Html->link('<i class="fas fa-edit"></i>', ['action' => 'edit', $sedation->id], ['class' => 'btn btn-outline-warning btn-sm', 'escape' => false, 'title' => 'Edit']) ?>
                                    <?php echo $this->Form->postLink('<i class="fas fa-trash"></i>', ['action' => 'delete', $sedation->id], ['confirm' => __('Are you sure you want to delete # {0}?', $sedation->id), 'class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Delete']) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="paginator mt-3">
        <ul class="pagination justify-content-center">
            <?php echo $this->Paginator->first('<< ' . __('first'), ['class' => 'page-link']) ?>
            <?php echo $this->Paginator->prev('< ' . __('previous'), ['class' => 'page-link']) ?>
            <?php echo $this->Paginator->numbers(['class' => 'page-link']) ?>
            <?php echo $this->Paginator->next(__('next') . ' >', ['class' => 'page-link']) ?>
            <?php echo $this->Paginator->last(__('last') . ' >>', ['class' => 'page-link']) ?>
        </ul>
        <p class="text-center text-muted">
            <?php echo $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
        </p>
    </div>
</div>