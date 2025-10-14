<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Department $department
 * @var \Cake\Collection\CollectionInterface|string[] $hospitals
 */
?>
<div class="departments form content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-plus me-2"></i><?php echo __('Add Department') ?></h3>
        <?php echo $this->Html->link(__('List Departments'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php echo $this->Form->create($department, ['class' => 'needs-validation', 'novalidate' => true]) ?>
            <fieldset>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <?php echo $this->Form->control('name', [
                                'class' => 'form-control',
                                'label' => ['class' => 'form-label'],
                                'placeholder' => 'Enter department name'
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <?php echo $this->Form->control('description', [
                        'type' => 'textarea',
                        'class' => 'form-control',
                        'label' => ['class' => 'form-label'],
                        'rows' => 3,
                        'placeholder' => 'Enter department description (optional)'
                    ]); ?>
                </div>
            </fieldset>
            <div class="d-flex gap-2">
                <?php echo $this->Form->button(__('Save Department'), ['class' => 'btn btn-primary']) ?>
                <?php echo $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
            </div>
            <?php echo $this->Form->end() ?>
        </div>
    </div>
</div>