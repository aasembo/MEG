<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ExamsProcedure $examsProcedure
 * @var \Cake\Collection\CollectionInterface|string[] $exams
 * @var \Cake\Collection\CollectionInterface|string[] $procedures
 */
?>
<div class="exams-procedures edit content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-edit me-2"></i><?php echo __('Edit Exam-Procedure Association') ?></h3>
        <div class="btn-group">
            <?php echo $this->Html->link(__('View'), ['action' => 'view', $examsProcedure->id], ['class' => 'btn btn-outline-info']) ?>
            <?php echo $this->Html->link(__('List Associations'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php echo $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $examsProcedure->id],
                ['confirm' => __('Are you sure you want to delete this association?'), 'class' => 'btn btn-outline-danger']
            ) ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php echo $this->Form->create($examsProcedure, ['class' => 'needs-validation', 'novalidate' => true]) ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('exam_id', [
                            'options' => $exams,
                            'empty' => 'Select Exam',
                            'class' => 'form-select',
                            'required' => true,
                            'label' => ['class' => 'form-label']
                        ]) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('procedure_id', [
                            'options' => $procedures,
                            'empty' => 'Select Procedure',
                            'class' => 'form-select',
                            'required' => true,
                            'label' => ['class' => 'form-label']
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <?php echo $this->Form->control('order_sequence', [
                            'type' => 'number',
                            'class' => 'form-control',
                            'label' => ['class' => 'form-label'],
                            'placeholder' => 'Order sequence',
                            'min' => 1
                        ]) ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <?php echo $this->Form->control('estimated_duration', [
                            'type' => 'number',
                            'class' => 'form-control',
                            'label' => ['class' => 'form-label'],
                            'placeholder' => 'Duration in minutes',
                            'min' => 1
                        ]) ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <div class="form-check mt-4">
                            <?php echo $this->Form->control('is_required', [
                                'type' => 'checkbox',
                                'class' => 'form-check-input',
                                'label' => [
                                    'text' => 'Required Procedure',
                                    'class' => 'form-check-label'
                                ]
                            ]) ?>
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
                    'placeholder' => 'Special preparation instructions for this procedure in this exam'
                ]) ?>
            </div>

            <div class="mb-3">
                <?php echo $this->Form->control('post_procedure_care', [
                    'type' => 'textarea',
                    'class' => 'form-control',
                    'label' => ['class' => 'form-label'],
                    'rows' => 3,
                    'placeholder' => 'Post-procedure care instructions'
                ]) ?>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
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
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('sedation_required', [
                            'type' => 'checkbox',
                            'class' => 'form-check-input',
                            'label' => [
                                'text' => 'Sedation Required',
                                'class' => 'form-check-label'
                            ]
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <?php echo $this->Form->control('notes', [
                    'type' => 'textarea',
                    'class' => 'form-control',
                    'label' => ['class' => 'form-label'],
                    'rows' => 3,
                    'placeholder' => 'Additional notes specific to this exam-procedure combination'
                ]) ?>
            </div>

            <div class="d-flex gap-2">
                <?php echo $this->Form->button(__('Update Association'), ['class' => 'btn btn-primary']) ?>
                <?php echo $this->Html->link(__('Cancel'), ['action' => 'view', $examsProcedure->id], ['class' => 'btn btn-secondary']) ?>
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