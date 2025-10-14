<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Exam $exam
 */
?>
<div class="exams view content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-stethoscope me-2"></i><?php echo h($exam->name) ?></h3>
        <div class="btn-group">
            <?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $exam->id], ['class' => 'btn btn-primary']) ?>
            <?php echo $this->Html->link(__('List Exams'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php echo $this->Html->link(__('Add New'), ['action' => 'add'], ['class' => 'btn btn-outline-success']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Exam Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th scope="row" style="width: 200px;"><?php echo __('ID') ?></th>
                            <td><?php echo $this->Number->format($exam->id) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Name') ?></th>
                            <td><?php echo h($exam->name) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Type') ?></th>
                            <td><?php echo h($exam->type) ?: '-' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Department') ?></th>
                            <td><?php echo $exam->hasValue('department') ? $this->Html->link($exam->department->name, ['controller' => 'Departments', 'action' => 'view', $exam->department->id]) : '-' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Modality') ?></th>
                            <td><?php echo $exam->hasValue('modality') ? $this->Html->link($exam->modality->name, ['controller' => 'Modalities', 'action' => 'view', $exam->modality->id]) : '-' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Duration') ?></th>
                            <td><?php echo $exam->duration_minutes ? $this->Number->format($exam->duration_minutes) . ' minutes' : '-' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Preparation Required') ?></th>
                            <td>
                                <?php if ($exam->preparation_required): ?>
                                    <span class="badge bg-warning">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Contrast Required') ?></th>
                            <td>
                                <?php if ($exam->contrast_required): ?>
                                    <span class="badge bg-danger">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Hospital') ?></th>
                            <td><?php echo $exam->hasValue('hospital') ? $this->Html->link($exam->hospital->name, ['controller' => 'Hospitals', 'action' => 'view', $exam->hospital->id]) : '' ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Created') ?></th>
                            <td><?php echo h($exam->created) ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo __('Modified') ?></th>
                            <td><?php echo h($exam->modified) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <?php if (!empty($exam->description)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-text me-2"></i>Description</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo $this->Text->autoParagraph(h($exam->description)); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($exam->preparation_instructions)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Preparation Instructions</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted"><?php echo $this->Text->autoParagraph(h($exam->preparation_instructions)); ?></p>
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
                        <?php echo $this->Html->link(__('Edit Exam'), ['action' => 'edit', $exam->id], ['class' => 'btn btn-primary']) ?>
                        <?php echo $this->Html->link(__('List Exams'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                        <?php echo $this->Html->link(__('Add New Exam'), ['action' => 'add'], ['class' => 'btn btn-outline-success']) ?>
                        <?php echo $this->Form->postLink(__('Delete Exam'), ['action' => 'delete', $exam->id], ['confirm' => __('Are you sure you want to delete # {0}?', $exam->id), 'class' => 'btn btn-outline-danger']) ?>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-link me-2"></i>Related Procedures</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        View associated procedures for this exam in the Exam-Procedures section.
                    </p>
                    <?php echo $this->Html->link(__('Manage Procedures'), ['controller' => 'ExamsProcedures', 'action' => 'index'], ['class' => 'btn btn-sm btn-outline-info']) ?>
                </div>
            </div>
        </div>
    </div>
</div>