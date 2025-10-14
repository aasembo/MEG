<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Procedure $procedure
 * @var \Cake\Collection\CollectionInterface|string[] $departments
 * @var \Cake\Collection\CollectionInterface|string[] $sedations
 */
?>
<div class="procedures add content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-plus me-2"></i><?php echo __('Add Procedure') ?></h3>
        <?php echo $this->Html->link(__('List Procedures'), ['action' => 'index'], ['class' => 'btn btn-outline-primary']) ?>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php echo $this->Form->create($procedure, ['class' => 'needs-validation', 'novalidate' => true]) ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('name', [
                            'class' => 'form-control',
                            'required' => true,
                            'label' => ['class' => 'form-label'],
                            'placeholder' => 'Enter procedure name'
                        ]) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('type', [
                            'class' => 'form-control',
                            'label' => ['class' => 'form-label'],
                            'placeholder' => 'Enter procedure type',
                            'help' => 'e.g., diagnostic, therapeutic, surgical'
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
                        <?php echo $this->Form->control('sedation_id', [
                            'options' => $sedations,
                            'empty' => 'Select Sedation Level',
                            'class' => 'form-select',
                            'label' => ['class' => 'form-label']
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <?php echo $this->Form->control('risk_level', [
                            'type' => 'select',
                            'options' => [
                                'low' => 'Low Risk',
                                'medium' => 'Medium Risk',
                                'high' => 'High Risk'
                            ],
                            'empty' => 'Select Risk Level',
                            'class' => 'form-select',
                            'label' => ['class' => 'form-label']
                        ]) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <div class="form-check mt-4">
                            <?php echo $this->Form->control('consent_required', [
                                'type' => 'checkbox',
                                'class' => 'form-check-input',
                                'label' => [
                                    'text' => 'Consent Required',
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
                    'placeholder' => 'Enter procedure description'
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
                        <?php echo $this->Form->control('cost', [
                            'type' => 'number',
                            'step' => 0.01,
                            'class' => 'form-control',
                            'label' => ['class' => 'form-label'],
                            'placeholder' => 'Procedure cost'
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
                    'placeholder' => 'Additional notes or instructions'
                ]) ?>
            </div>

            <div class="d-flex gap-2">
                <?php echo $this->Form->button(__('Save Procedure'), ['class' => 'btn btn-primary']) ?>
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