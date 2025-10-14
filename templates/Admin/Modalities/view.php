<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Modality $modality
 */
?>
<div class="modalities view content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-camera me-2"></i><?php echo h($modality->name) ?></h3>
        <div class="btn-group">
            <?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $modality->id], ['class' => 'btn btn-primary']) ?>
            <?php echo $this->Html->link(__('List Modalities'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php echo $this->Html->link(__('Add New'), ['action' => 'add'], ['class' => 'btn btn-outline-success']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Modality Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th scope="row" style="width: 200px;"><?php echo __('ID') ?></th>
                            <td><?php echo $this->Number->format($modality->id) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Name') ?></th>
                            <td><?php echo h($modality->name) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Hospital') ?></th>
                            <td><?php echo $modality->hasValue('hospital') ? $this->Html->link($modality->hospital->name, ['controller' => 'Hospitals', 'action' => 'view', $modality->hospital->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Created') ?></th>
                            <td><?php echo h($modality->created) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Modified') ?></th>
                            <td><?php echo h($modality->modified) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if (!empty($modality->description)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-text me-2"></i>Description</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo $this->Text->autoParagraph(h($modality->description)); ?></p>
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
                        <?php echo $this->Html->link(__('Edit Modality'), ['action' => 'edit', $modality->id], ['class' => 'btn btn-primary']) ?>
                        <?php echo $this->Html->link(__('List Modalities'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                        <?php echo $this->Html->link(__('Add New Modality'), ['action' => 'add'], ['class' => 'btn btn-outline-success']) ?>
                        <?php echo $this->Form->postLink(__('Delete Modality'), ['action' => 'delete', $modality->id], ['confirm' => __('Are you sure you want to delete # {0}?', $modality->id), 'class' => 'btn btn-outline-danger']) ?>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Related Data</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        This modality can be used for various medical examinations and procedures.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>