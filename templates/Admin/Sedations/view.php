<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Sedation $sedation
 */
?>
<div class="sedations view content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-bed me-2"></i><?php echo h($sedation->level) ?></h3>
        <div class="btn-group">
            <?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $sedation->id], ['class' => 'btn btn-primary']) ?>
            <?php echo $this->Html->link(__('List Sedations'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php echo $this->Html->link(__('Add New'), ['action' => 'add'], ['class' => 'btn btn-outline-success']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Sedation Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th scope="row" style="width: 200px;"><?php echo __('ID') ?></th>
                            <td><?php echo $this->Number->format($sedation->id) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Level') ?></th>
                            <td><?php echo h($sedation->level) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Type') ?></th>
                            <td><?php echo h($sedation->type) ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Risk Category') ?></th>
                            <td>
                                <?php if ($sedation->risk_category): ?>
                                    <span class="badge bg-<?php echo $sedation->risk_category === 'high' ? 'danger' : ($sedation->risk_category === 'medium' ? 'warning' : 'success') ?>">
                                        <?php echo h($sedation->risk_category) ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Recovery Time') ?></th>
                            <td><?php echo $sedation->recovery_time ? $this->Number->format($sedation->recovery_time) . ' minutes' : '-' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Monitoring Required') ?></th>
                            <td>
                                <?php if ($sedation->monitoring_required): ?>
                                    <span class="badge bg-warning">Required</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Required</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Pre-medication Required') ?></th>
                            <td>
                                <?php if ($sedation->pre_medication_required): ?>
                                    <span class="badge bg-info">Required</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Required</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Created') ?></th>
                            <td><?php echo h($sedation->created) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Modified') ?></th>
                            <td><?php echo h($sedation->modified) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if (!empty($sedation->description)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-text me-2"></i>Description</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo $this->Text->autoParagraph(h($sedation->description)); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($sedation->medications)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-pills me-2"></i>Medications</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo $this->Text->autoParagraph(h($sedation->medications)); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($sedation->contraindications)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Contraindications</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo $this->Text->autoParagraph(h($sedation->contraindications)); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($sedation->notes)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo $this->Text->autoParagraph(h($sedation->notes)); ?></p>
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
                        <?php echo $this->Html->link(__('Edit Sedation'), ['action' => 'edit', $sedation->id], ['class' => 'btn btn-primary']) ?>
                        <?php echo $this->Html->link(__('List Sedations'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                        <?php echo $this->Html->link(__('Add New Sedation'), ['action' => 'add'], ['class' => 'btn btn-outline-success']) ?>
                        <?php echo $this->Form->postLink(__('Delete Sedation'), ['action' => 'delete', $sedation->id], ['confirm' => __('Are you sure you want to delete # {0}?', $sedation->id), 'class' => 'btn btn-outline-danger']) ?>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Related Procedures</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        View procedures that use this sedation level.
                    </p>
                    <?php echo $this->Html->link(__('View Procedures'), ['controller' => 'Procedures', 'action' => 'index'], ['class' => 'btn btn-sm btn-outline-info']) ?>
                </div>
            </div>
        </div>
    </div>
</div>