<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Exam $exam
 * @var \Cake\Collection\CollectionInterface|string[] $hospitals
 * @var \Cake\Collection\CollectionInterface|string[] $modalities
 * @var \Cake\Collection\CollectionInterface|string[] $departments
 */
?>
<div class="exams form content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-plus me-2"></i><?php echo __('Add Exam') ?></h3>
        <?php echo $this->Html->link(__('List Exams'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php echo $this->Form->create($exam, ['class' => 'needs-validation', 'novalidate' => true]) ?>
            <fieldset>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <?php echo $this->Form->control('name', [
                                'class' => 'form-control',
                                'label' => ['class' => 'form-label'],
                                'placeholder' => 'Enter exam name'
                            ]); ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <?php echo $this->Form->control('modality_id', [
                                'type' => 'select',
                                'options' => $modalities,
                                'empty' => 'Select Modality',
                                'class' => 'form-select',
                                'label' => ['class' => 'form-label']
                            ]); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <?php echo $this->Form->control('department_id', [
                                'type' => 'select',
                                'options' => $departments,
                                'empty' => 'Select Department',
                                'class' => 'form-select',
                                'label' => ['class' => 'form-label']
                            ]); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <?php echo $this->Form->control('duration', [
                                'type' => 'number',
                                'class' => 'form-control',
                                'label' => ['class' => 'form-label', 'text' => 'Duration (minutes)'],
                                'placeholder' => 'e.g., 30'
                            ]); ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <?php echo $this->Form->control('cost', [
                                'type' => 'number',
                                'step' => '0.01',
                                'class' => 'form-control',
                                'label' => ['class' => 'form-label', 'text' => 'Cost ($)'],
                                'placeholder' => 'e.g., 150.00'
                            ]); ?>
                        </div>
                    </div>
                </div>
            </fieldset>
            <div class="d-flex gap-2">
                <?php echo $this->Form->button(__('Save Exam'), ['class' => 'btn btn-primary']) ?>
                <?php echo $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
            </div>
            <?php echo $this->Form->end() ?>
        </div>
    </div>
</div>