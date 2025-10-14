<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Exam> $exams
 */
?>
<div class="exams index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-file-medical me-2"></i><?php echo __('Exams') ?></h3>
        <?php echo $this->Html->link(__('Add Exam'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Exams List
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($exams->toArray())): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $this->Paginator->sort('id') ?></th>
                            <th><?php echo $this->Paginator->sort('name') ?></th>
                            <th><?php echo $this->Paginator->sort('modality.name', 'Modality') ?></th>
                            <th><?php echo $this->Paginator->sort('department.name', 'Department') ?></th>
                            <th><?php echo $this->Paginator->sort('duration') ?></th>
                            <th><?php echo $this->Paginator->sort('cost') ?></th>
                            <th><?php echo $this->Paginator->sort('created') ?></th>
                            <th class="actions"><?php echo __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exams as $exam): ?>
                        <tr>
                            <td><?php echo $this->Number->format($exam->id) ?></td>
                            <td><strong><?php echo h($exam->name) ?></strong></td>
                            <td><?php echo $exam->hasValue('modality') ? h($exam->modality->name) : '-' ?></td>
                            <td><?php echo $exam->hasValue('department') ? h($exam->department->name) : '-' ?></td>
                            <td><?php echo $exam->duration ? h($exam->duration) . ' min' : '-' ?></td>
                            <td><?php echo $exam->cost ? '$' . number_format($exam->cost, 2) : '-' ?></td>
                            <td><?php echo h($exam->created->format('M j, Y g:i A')) ?></td>
                            <td class="actions">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php echo $this->Html->link('<i class="fas fa-eye"></i>', ['action' => 'view', $exam->id], ['class' => 'btn btn-outline-info btn-sm', 'escape' => false, 'title' => 'View']) ?>
                                    <?php echo $this->Html->link('<i class="fas fa-edit"></i>', ['action' => 'edit', $exam->id], ['class' => 'btn btn-outline-warning btn-sm', 'escape' => false, 'title' => 'Edit']) ?>
                                    <?php echo $this->Form->postLink('<i class="fas fa-trash"></i>', ['action' => 'delete', $exam->id], ['confirm' => __('Are you sure you want to delete # {0}?', $exam->id), 'class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Delete']) ?>
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
                    <i class="fas fa-file-medical fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Exams Found</h4>
                    <p class="text-muted mb-4">You haven't created any exams yet. Exams are diagnostic tests performed using medical equipment and procedures.</p>
                </div>
                <div>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-plus me-2"></i>Add Your First Exam', 
                        ['action' => 'add'], 
                        ['class' => 'btn btn-primary btn-lg', 'escape' => false]
                    ) ?>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Examples: Chest X-Ray, Blood Panel, ECG, Ultrasound, etc.
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php echo $this->element('admin_pagination', ['items' => $exams, 'itemType' => 'exams']) ?>
    </div>
</div>