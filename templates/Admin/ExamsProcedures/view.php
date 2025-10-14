<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ExamsProcedure $examsProcedure
 */
?>
<div class="exams-procedures view content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-link me-2"></i><?php echo __('Exam-Procedure Association') ?></h3>
        <div class="btn-group">
            <?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $examsProcedure->id], ['class' => 'btn btn-primary']) ?>
            <?php echo $this->Html->link(__('List Associations'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php echo $this->Html->link(__('Add New'), ['action' => 'add'], ['class' => 'btn btn-outline-success']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Association Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th scope="row" style="width: 200px;"><?php echo __('ID') ?></th>
                            <td><?php echo $this->Number->format($examsProcedure->id) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Exam') ?></th>
                            <td>
                                <?php echo $examsProcedure->hasValue('exam') ? $this->Html->link($examsProcedure->exam->name, ['controller' => 'Exams', 'action' => 'view', $examsProcedure->exam->id]) : '-' ?>
                                <?php if ($examsProcedure->hasValue('exam') && $examsProcedure->exam->type): ?>
                                    <br><small class="text-muted"><?php echo h($examsProcedure->exam->type) ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Procedure') ?></th>
                            <td>
                                <?php echo $examsProcedure->hasValue('procedure') ? $this->Html->link($examsProcedure->procedure->name, ['controller' => 'Procedures', 'action' => 'view', $examsProcedure->procedure->id]) : '-' ?>
                                <?php if ($examsProcedure->hasValue('procedure') && $examsProcedure->procedure->type): ?>
                                    <br><small class="text-muted"><?php echo h($examsProcedure->procedure->type) ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Order Sequence') ?></th>
                            <td>
                                <?php if ($examsProcedure->order_sequence): ?>
                                    <span class="badge bg-info"><?php echo $this->Number->format($examsProcedure->order_sequence) ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Required') ?></th>
                            <td>
                                <?php if ($examsProcedure->is_required): ?>
                                    <span class="badge bg-danger">Required</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Optional</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Estimated Duration') ?></th>
                            <td><?php echo $examsProcedure->estimated_duration ? $this->Number->format($examsProcedure->estimated_duration) . ' minutes' : '-' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Contrast Required') ?></th>
                            <td>
                                <?php if ($examsProcedure->contrast_required): ?>
                                    <span class="badge bg-warning">Required</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Required</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Sedation Required') ?></th>
                            <td>
                                <?php if ($examsProcedure->sedation_required): ?>
                                    <span class="badge bg-info">Required</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Required</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Created') ?></th>
                            <td><?php echo h($examsProcedure->created) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Modified') ?></th>
                            <td><?php echo h($examsProcedure->modified) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if (!empty($examsProcedure->preparation_instructions)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Preparation Instructions</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo $this->Text->autoParagraph(h($examsProcedure->preparation_instructions)); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($examsProcedure->post_procedure_care)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-heart me-2"></i>Post-Procedure Care</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo $this->Text->autoParagraph(h($examsProcedure->post_procedure_care)); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($examsProcedure->notes)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo $this->Text->autoParagraph(h($examsProcedure->notes)); ?></p>
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
                        <?php echo $this->Html->link(__('Edit Association'), ['action' => 'edit', $examsProcedure->id], ['class' => 'btn btn-primary']) ?>
                        <?php echo $this->Html->link(__('List Associations'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                        <?php echo $this->Html->link(__('Add New Association'), ['action' => 'add'], ['class' => 'btn btn-outline-success']) ?>
                        <?php echo $this->Form->postLink(__('Delete Association'), ['action' => 'delete', $examsProcedure->id], ['confirm' => __('Are you sure you want to delete this association?'), 'class' => 'btn btn-outline-danger']) ?>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Related Items</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($examsProcedure->hasValue('exam')): ?>
                            <?php echo $this->Html->link(__('View Exam'), ['controller' => 'Exams', 'action' => 'view', $examsProcedure->exam->id], ['class' => 'btn btn-sm btn-outline-info']) ?>
                        <?php endif; ?>
                        <?php if ($examsProcedure->hasValue('procedure')): ?>
                            <?php echo $this->Html->link(__('View Procedure'), ['controller' => 'Procedures', 'action' => 'view', $examsProcedure->procedure->id], ['class' => 'btn btn-sm btn-outline-info']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>