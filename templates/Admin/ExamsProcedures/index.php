<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ExamsProcedure> $examsProcedures
 */
?>
<div class="exams-procedures index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-link me-2"></i><?php echo __('Exam-Procedure Associations') ?></h3>
        <?php echo $this->Html->link(__('Add Association'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Exam-Procedure Relationships
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($examsProcedures->toArray())): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?php echo $this->Paginator->sort('id') ?></th>
                            <th><?php echo $this->Paginator->sort('exam.name', 'Exam') ?></th>
                            <th><?php echo $this->Paginator->sort('procedure.name', 'Procedure') ?></th>
                            <th><?php echo $this->Paginator->sort('order_sequence', 'Order') ?></th>
                            <th><?php echo $this->Paginator->sort('is_required', 'Required') ?></th>
                            <th><?php echo $this->Paginator->sort('estimated_duration', 'Duration') ?></th>
                            <th><?php echo $this->Paginator->sort('created', 'Created') ?></th>
                            <th class="actions"><?php echo __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($examsProcedures as $examsProcedure): ?>
                        <tr>
                            <td><?php echo $this->Number->format($examsProcedure->id) ?></td>
                            <td>
                                <?php echo $examsProcedure->hasValue('exam') ? h($examsProcedure->exam->name) : '-' ?>
                                <?php if ($examsProcedure->hasValue('exam') && $examsProcedure->exam->type): ?>
                                    <br><small class="text-muted"><?php echo h($examsProcedure->exam->type) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $examsProcedure->hasValue('procedure') ? h($examsProcedure->procedure->name) : '-' ?>
                                <?php if ($examsProcedure->hasValue('procedure') && $examsProcedure->procedure->type): ?>
                                    <br><small class="text-muted"><?php echo h($examsProcedure->procedure->type) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($examsProcedure->order_sequence): ?>
                                    <span class="badge bg-info"><?php echo $this->Number->format($examsProcedure->order_sequence) ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($examsProcedure->is_required): ?>
                                    <span class="badge bg-danger">Required</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Optional</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $examsProcedure->estimated_duration ? $this->Number->format($examsProcedure->estimated_duration) . ' min' : '-' ?></td>
                            <td><?php echo h($examsProcedure->created) ?></td>
                            <td class="actions">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php echo $this->Html->link('<i class="fas fa-eye"></i>', ['action' => 'view', $examsProcedure->id], ['class' => 'btn btn-outline-info btn-sm', 'escape' => false, 'title' => 'View']) ?>
                                    <?php echo $this->Html->link('<i class="fas fa-edit"></i>', ['action' => 'edit', $examsProcedure->id], ['class' => 'btn btn-outline-warning btn-sm', 'escape' => false, 'title' => 'Edit']) ?>
                                    <?php echo $this->Form->postLink('<i class="fas fa-trash"></i>', ['action' => 'delete', $examsProcedure->id], ['confirm' => __('Are you sure you want to delete this association?'), 'class' => 'btn btn-outline-danger btn-sm', 'escape' => false, 'title' => 'Delete']) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-link fa-4x text-muted"></i>
                </div>
                <h5 class="text-muted mb-3">No Exam-Procedure Associations Found</h5>
                <p class="text-muted mb-4">
                    No associations between exams and procedures have been created yet.<br>
                    Start by creating your first association to link exams with their required procedures.
                </p>
                <?php echo $this->Html->link(
                    '<i class="fas fa-plus me-2"></i>Create First Association',
                    ['action' => 'add'],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ) ?>
            </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($examsProcedures->toArray())): ?>
        <?php echo $this->element('admin_pagination', ['itemType' => 'exam-procedure association']); ?>
        <?php endif; ?>
    </div>
</div>