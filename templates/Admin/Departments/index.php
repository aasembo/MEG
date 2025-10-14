<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Department> $departments
 */
?>
<div class="departments index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-building me-2"></i><?php echo __('Departments') ?></h3>
        <?php echo $this->Html->link(__('Add Department'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Departments List
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($departments->toArray())): ?>
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
                        <?php foreach ($departments as $department): ?>
                        <tr>
                            <td><?php echo $this->Number->format($department->id) ?></td>
                            <td><strong><?php echo h($department->name) ?></strong></td>
                            <td><?php echo h($department->description) ?: '<em class="text-muted">No description</em>' ?></td>
                            <td><?php echo h($department->created->format('M j, Y g:i A')) ?></td>
                            <td class="actions">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php echo $this->Html->link('<i class="fas fa-eye"></i>', ['action' => 'view', $department->id], ['class' => 'btn btn-outline-info btn-sm', 'escape' => false, 'title' => 'View']) ?>
                                    <?php echo $this->Html->link('<i class="fas fa-edit"></i>', ['action' => 'edit', $department->id], ['class' => 'btn btn-outline-warning btn-sm', 'escape' => false, 'title' => 'Edit']) ?>
                                    <?php echo $this->Form->postLink('<i class="fas fa-trash"></i>', ['action' => 'delete', $department->id], ['confirm' => __('Are you sure you want to delete # {0}?', $department->id), 'class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Delete']) ?>
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
                    <i class="fas fa-building fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Departments Found</h4>
                    <p class="text-muted mb-4">You haven't created any departments yet. Departments help organize your medical services and staff.</p>
                </div>
                <div>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-plus me-2"></i>Add Your First Department', 
                        ['action' => 'add'], 
                        ['class' => 'btn btn-primary btn-lg', 'escape' => false]
                    ) ?>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Departments can include categories like Cardiology, Radiology, Emergency, etc.
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php echo $this->element('admin_pagination', ['items' => $departments, 'itemType' => 'departments']) ?>
    </div>

</div>