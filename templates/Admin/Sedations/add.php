<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Sedation $sedation
 */
?>

<div class="container-fluid">
    <!-- Header Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-warning py-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="fas fa-plus me-2"></i>Add New Sedation Level
                    </h4>
                    <p class="mb-0 text-light">Create a new sedation level configuration</p>
                </div>
                <div>
                    <?php echo  $this->Html->link('<i class="fas fa-list me-1"></i>List Sedations', 
                        ['action' => 'index'], 
                        ['class' => 'btn btn-outline-light btn-sm', 'escape' => false]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-dark"><i class="fas fa-bed me-2"></i>Sedation Level Information</h6>
                </div>
                <div class="card-body">
                    <?php echo  $this->Form->create($sedation, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    
                    <!-- Basic Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <?php echo  $this->Form->control('level', [
                                    'class' => 'form-control',
                                    'required' => true,
                                    'label' => ['class' => 'form-label fw-semibold text-dark'],
                                    'placeholder' => 'e.g., Minimal, Moderate, Deep',
                                    'maxlength' => 50
                                ]) ?>
                                <div class="form-text">Enter the sedation level name</div>
                                <div class="invalid-feedback">
                                    Please provide a valid sedation level.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <?php echo  $this->Form->control('type', [
                                    'class' => 'form-control',
                                    'label' => ['class' => 'form-label fw-semibold text-dark'],
                                    'placeholder' => 'e.g., IV, Inhalation, Oral',
                                    'maxlength' => 50
                                ]) ?>
                                <div class="form-text">Specify the method of administration</div>
                            </div>
                        </div>
                    </div>

                    <!-- Risk and Recovery -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <?php echo  $this->Form->control('risk_category', [
                                    'type' => 'select',
                                    'options' => [
                                        'low' => 'Low Risk',
                                        'medium' => 'Medium Risk',
                                        'high' => 'High Risk'
                                    ],
                                    'empty' => 'Select Risk Category',
                                    'class' => 'form-select',
                                    'label' => ['class' => 'form-label fw-semibold text-dark']
                                ]) ?>
                                <div class="form-text">Patient risk assessment level</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <?php echo  $this->Form->control('recovery_time', [
                                    'type' => 'number',
                                    'class' => 'form-control',
                                    'label' => ['class' => 'form-label fw-semibold text-dark'],
                                    'placeholder' => 'Minutes',
                                    'min' => 0,
                                    'max' => 1440
                                ]) ?>
                                <div class="form-text">Expected recovery time in minutes</div>
                            </div>
                        </div>
                    </div>

                    <!-- Requirements -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Monitoring Requirements</label>
                                <div class="form-check mt-2">
                                    <?php echo  $this->Form->control('monitoring_required', [
                                        'type' => 'checkbox',
                                        'class' => 'form-check-input',
                                        'label' => [
                                            'text' => 'Continuous monitoring required',
                                            'class' => 'form-check-label text-dark'
                                        ]
                                    ]) ?>
                                </div>
                                <div class="form-text">Check if patient requires continuous monitoring</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Pre-medication</label>
                                <div class="form-check mt-2">
                                    <?php echo  $this->Form->control('pre_medication_required', [
                                        'type' => 'checkbox',
                                        'class' => 'form-check-input',
                                        'label' => [
                                            'text' => 'Pre-medication required',
                                            'class' => 'form-check-label text-dark'
                                        ]
                                    ]) ?>
                                </div>
                                <div class="form-text">Check if pre-medication is necessary</div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Information -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <?php echo  $this->Form->control('description', [
                                    'type' => 'textarea',
                                    'class' => 'form-control',
                                    'label' => ['class' => 'form-label fw-semibold text-dark'],
                                    'rows' => 4,
                                    'placeholder' => 'Describe the sedation protocol, procedures, and patient care requirements...',
                                    'maxlength' => 1000
                                ]) ?>
                                <div class="form-text">Detailed description of the sedation protocol (max 1000 characters)</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <?php echo  $this->Form->control('medications', [
                                    'type' => 'textarea',
                                    'class' => 'form-control',
                                    'label' => ['class' => 'form-label fw-semibold text-dark'],
                                    'rows' => 3,
                                    'placeholder' => 'List medications used (e.g., Propofol, Midazolam, Fentanyl)',
                                    'maxlength' => 500
                                ]) ?>
                                <div class="form-text">Medications typically used for this sedation level</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <?php echo  $this->Form->control('contraindications', [
                                    'type' => 'textarea',
                                    'class' => 'form-control',
                                    'label' => ['class' => 'form-label fw-semibold text-dark'],
                                    'rows' => 3,
                                    'placeholder' => 'List contraindications and precautions...',
                                    'maxlength' => 500
                                ]) ?>
                                <div class="form-text">Medical contraindications and precautions</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <?php echo  $this->Form->control('notes', [
                                    'type' => 'textarea',
                                    'class' => 'form-control',
                                    'label' => ['class' => 'form-label fw-semibold text-dark'],
                                    'rows' => 3,
                                    'placeholder' => 'Additional notes, special instructions, or considerations...',
                                    'maxlength' => 500
                                ]) ?>
                                <div class="form-text">Additional notes or special instructions</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <?php echo  $this->Form->button('<i class="fas fa-save me-1"></i>Save Sedation Level', [
                            'class' => 'btn btn-warning text-dark fw-semibold',
                            'escape' => false,
                            'type' => 'submit'
                        ]) ?>
                        <?php echo  $this->Html->link('<i class="fas fa-times me-1"></i>Cancel', 
                            ['action' => 'index'], 
                            ['class' => 'btn btn-secondary', 'escape' => false]) ?>
                    </div>
                    
                    <?php echo  $this->Form->end() ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- Guidelines -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-dark"><i class="fas fa-info-circle me-2"></i>Sedation Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="small text-muted">
                        <div class="mb-3">
                            <strong class="text-dark d-block mb-1">Sedation Level</strong>
                            Use standard terminology (Minimal, Moderate, Deep, General).
                        </div>
                        <div class="mb-3">
                            <strong class="text-dark d-block mb-1">Risk Category</strong>
                            Assess based on patient safety requirements and monitoring needs.
                        </div>
                        <div class="mb-3">
                            <strong class="text-dark d-block mb-1">Recovery Time</strong>
                            Include time for patient to reach discharge readiness.
                        </div>
                        <div class="alert alert-info py-2 px-3 small mb-0">
                            <i class="fas fa-lightbulb me-1"></i>
                            Follow institutional sedation protocols and guidelines.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Common Sedation Levels -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-dark"><i class="fas fa-list-ul me-2"></i>Common Sedation Levels</h6>
                </div>
                <div class="card-body">
                    <div class="small text-muted">
                        <div class="mb-2">
                            <strong class="text-dark">Minimal:</strong> Anxiolysis, normal response to verbal stimulation
                        </div>
                        <div class="mb-2">
                            <strong class="text-dark">Moderate:</strong> Conscious sedation, purposeful response
                        </div>
                        <div class="mb-2">
                            <strong class="text-dark">Deep:</strong> Depressed consciousness, not easily aroused
                        </div>
                        <div class="mb-0">
                            <strong class="text-dark">General:</strong> Complete loss of consciousness
                        </div>
                    </div>
                </div>
            </div>
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

    // Auto-focus on first input
    const firstInput = document.querySelector('input[name="level"]');
    if (firstInput) {
        firstInput.focus();
    }

    // Character counters for text areas
    const textAreas = document.querySelectorAll('textarea[maxlength]');
    textAreas.forEach(textarea => {
        const maxLength = parseInt(textarea.getAttribute('maxlength'));
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        textarea.parentNode.appendChild(counter);
        
        function updateCounter() {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = `${remaining} characters remaining`;
            counter.className = remaining < 50 ? 'form-text text-end text-warning' : 'form-text text-end text-muted';
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
});
</script>