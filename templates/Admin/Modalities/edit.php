<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Modality $modality
 */
?>
<div class="modalities edit content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-edit me-2"></i><?php echo __('Edit Modality') ?></h3>
        <div class="btn-group">
            <?php echo $this->Html->link(__('View'), ['action' => 'view', $modality->id], ['class' => 'btn btn-outline-info']) ?>
            <?php echo $this->Html->link(__('List Modalities'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php echo $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $modality->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $modality->id), 'class' => 'btn btn-outline-danger']
            ) ?>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php echo $this->Form->create($modality, ['class' => 'needs-validation', 'novalidate' => true]) ?>
            
            <div class="mb-3">
                <?php echo $this->Form->control('name', [
                    'class' => 'form-control',
                    'required' => true,
                    'label' => ['class' => 'form-label'],
                    'placeholder' => 'Enter modality name'
                ]) ?>
            </div>

            <div class="mb-3">
                <?php echo $this->Form->control('description', [
                    'type' => 'textarea',
                    'class' => 'form-control',
                    'label' => ['class' => 'form-label'],
                    'rows' => 4,
                    'placeholder' => 'Enter detailed description of the modality'
                ]) ?>
            </div>

            <div class="d-flex gap-2">
                <?php echo $this->Form->button(__('Update Modality'), ['class' => 'btn btn-primary']) ?>
                <?php echo $this->Html->link(__('Cancel'), ['action' => 'view', $modality->id], ['class' => 'btn btn-secondary']) ?>
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