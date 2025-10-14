<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Exam $exam
 * @var \Cake\Collection\CollectionInterface|string[] $departments
 * @var \Cake\Collection\CollectionInterface|string[] $modalities
 */
?>
<div class="exams edit content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-edit me-2"></i><?php echo __('Edit Exam') ?></h3>
        <div class="btn-group">
            <?php echo $this->Html->link(__('View'), ['action' => 'view', $exam->id], ['class' => 'btn btn-outline-info']) ?>
            <?php echo $this->Html->link(__('List Exams'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php echo $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $exam->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $exam->id), 'class' => 'btn btn-outline-danger']
            ) ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php echo $this->Form->create($exam, ['class' => 'needs-validation', 'novalidate' => true]) ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('name', [
                            'class' => 'form-control',
                            'required' => true,
                            'label' => ['class' => 'form-label'],
                            'placeholder' => 'Enter exam name'
                        ]) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('type', [
                            'class' => 'form-control',
                            'label' => ['class' => 'form-label'],
                            'placeholder' => 'Enter exam type'
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('department_id', [
                            'options' => $departments,
                            'empty' => 'Select Department',
                            'class' => 'form-select',
                            'label' => ['class' => 'form-label']
                        ]) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('modality_id', [
                            'options' => $modalities,
                            'empty' => 'Select Modality',
                            'class' => 'form-select',
                            'label' => ['class' => 'form-label']
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <?php echo $this->Form->control('description', [
                    'type' => 'textarea',
                    'class' => 'form-control',
                    'label' => ['class' => 'form-label'],
                    'rows' => 4,
                    'placeholder' => 'Enter exam description'
                ]) ?>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('duration_minutes', [
                            'type' => 'number',
                            'class' => 'form-control',
                            'label' => ['class' => 'form-label'],
                            'placeholder' => 'Duration in minutes',
                            'min' => 1
                        ]) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <?php echo $this->Form->control('preparation_required', [
                                        'type' => 'checkbox',
                                        'class' => 'form-check-input',
                                        'label' => [
                                            'text' => 'Preparation Required',
                                            'class' => 'form-check-label'
                                        ]
                                    ]) ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mt-4">
                                    <?php echo $this->Form->control('contrast_required', [
                                        'type' => 'checkbox',
                                        'class' => 'form-check-input',
                                        'label' => [
                                            'text' => 'Contrast Required',
                                            'class' => 'form-check-label'
                                        ]
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <?php echo $this->Form->control('preparation_instructions', [
                    'type' => 'textarea',
                    'class' => 'form-control',
                    'label' => ['class' => 'form-label'],
                    'rows' => 3,
                    'placeholder' => 'Enter preparation instructions for patients'
                ]) ?>
            </div>

            <div class="d-flex gap-2">
                <?php echo $this->Form->button(__('Update Exam'), ['class' => 'btn btn-primary']) ?>
                <?php echo $this->Html->link(__('Cancel'), ['action' => 'view', $exam->id], ['class' => 'btn btn-secondary']) ?>
            </div>
            
            <?php echo $this->Form->end() ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bootstrap form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});
</script>