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

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
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