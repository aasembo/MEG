<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Modality> $modalities
 */
?>
<div class="modalities index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-x-ray me-2"></i><?php echo __('Modalities') ?></h3>
        <?php echo $this->Html->link(__('Add Modality'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Modalities List
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($modalities->toArray())): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $this->Paginator->sort('id') ?></th>
                            <th><?php echo $this->Paginator->sort('name') ?></th>
                            <th><?php echo $this->Paginator->sort('description') ?></th>
                            <th><?php echo $this->Paginator->sort('created') ?></th>
                            <th class="actions"><?php echo __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modalities as $modality): ?>
                        <tr>
                            <td><?php echo $this->Number->format($modality->id) ?></td>
                            <td><strong><?php echo h($modality->name) ?></strong></td>
                            <td><?php echo h($modality->description) ?: '<em class="text-muted">No description</em>' ?></td>
                            <td><?php echo h($modality->created->format('M j, Y g:i A')) ?></td>
                            <td class="actions">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php echo $this->Html->link('<i class="fas fa-eye"></i>', ['action' => 'view', $modality->id], ['class' => 'btn btn-outline-info btn-sm', 'escape' => false, 'title' => 'View']) ?>
                                    <?php echo $this->Html->link('<i class="fas fa-edit"></i>', ['action' => 'edit', $modality->id], ['class' => 'btn btn-outline-warning btn-sm', 'escape' => false, 'title' => 'Edit']) ?>
                                    <?php echo $this->Form->postLink('<i class="fas fa-trash"></i>', ['action' => 'delete', $modality->id], ['confirm' => __('Are you sure you want to delete # {0}?', $modality->id), 'class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Delete']) ?>
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
                    <i class="fas fa-x-ray fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Modalities Found</h4>
                    <p class="text-muted mb-4">You haven't created any imaging modalities yet. Modalities define the types of medical imaging equipment available.</p>
                </div>
                <div>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-plus me-2"></i>Add Your First Modality', 
                        ['action' => 'add'], 
                        ['class' => 'btn btn-primary btn-lg', 'escape' => false]
                    ) ?>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Examples: CT Scanner, MRI, X-Ray, Ultrasound, etc.
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php echo $this->element('admin_pagination', ['items' => $modalities, 'itemType' => 'modalities']) ?>
    </div>

</div>