<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Sedation $sedation
 */
?>
<div class="sedations add content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-plus me-2"></i><?php echo __('Add Sedation') ?></h3>
        <?php echo $this->Html->link(__('List Sedations'), ['action' => 'index'], ['class' => 'btn btn-outline-primary']) ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php echo $this->Form->create($sedation, ['class' => 'needs-validation', 'novalidate' => true]) ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('level', [
                            'class' => 'form-control',
                            'required' => true,
                            'label' => ['class' => 'form-label'],
                            'placeholder' => 'Enter sedation level',
                            'help' => 'e.g., minimal, moderate, deep'
                        ]) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('type', [
                            'class' => 'form-control',
                            'label' => ['class' => 'form-label'],
                            'placeholder' => 'Enter sedation type',
                            'help' => 'e.g., conscious, IV, inhalation'
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('risk_category', [
                            'type' => 'select',
                            'options' => [
                                'low' => 'Low Risk',
                                'medium' => 'Medium Risk',
                                'high' => 'High Risk'
                            ],
                            'empty' => 'Select Risk Category',
                            'class' => 'form-select',
                            'label' => ['class' => 'form-label']
                        ]) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('recovery_time', [
                            'type' => 'number',
                            'class' => 'form-control',
                            'label' => ['class' => 'form-label'],
                            'placeholder' => 'Recovery time in minutes',
                            'min' => 0
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="form-check mt-4">
                            <?php echo $this->Form->control('monitoring_required', [
                                'type' => 'checkbox',
                                'class' => 'form-check-input',
                                'label' => [
                                    'text' => 'Monitoring Required',
                                    'class' => 'form-check-label'
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="form-check mt-4">
                            <?php echo $this->Form->control('pre_medication_required', [
                                'type' => 'checkbox',
                                'class' => 'form-check-input',
                                'label' => [
                                    'text' => 'Pre-medication Required',
                                    'class' => 'form-check-label'
                                ]
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <?php echo $this->Form->control('description', [
                    'type' => 'textarea',
                    'class' => 'form-control',
                    'label' => ['class' => 'form-label'],
                    'rows' => 4,
                    'placeholder' => 'Enter detailed description of sedation protocol'
                ]) ?>
            </div>

            <div class="mb-3">
                <?php echo $this->Form->control('medications', [
                    'type' => 'textarea',
                    'class' => 'form-control',
                    'label' => ['class' => 'form-label'],
                    'rows' => 3,
                    'placeholder' => 'List medications used (one per line or comma-separated)'
                ]) ?>
            </div>

            <div class="mb-3">
                <?php echo $this->Form->control('contraindications', [
                    'type' => 'textarea',
                    'class' => 'form-control',
                    'label' => ['class' => 'form-label'],
                    'rows' => 3,
                    'placeholder' => 'List contraindications and precautions'
                ]) ?>
            </div>

            <div class="mb-3">
                <?php echo $this->Form->control('notes', [
                    'type' => 'textarea',
                    'class' => 'form-control',
                    'label' => ['class' => 'form-label'],
                    'rows' => 3,
                    'placeholder' => 'Additional notes or special instructions'
                ]) ?>
            </div>

            <div class="d-flex gap-2">
                <?php echo $this->Form->button(__('Save Sedation'), ['class' => 'btn btn-primary']) ?>
                <?php echo $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
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