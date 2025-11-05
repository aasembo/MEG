<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Modality $modality
 * @var \Cake\Collection\CollectionInterface|string[] $hospitals
 */
?>
<?php $this->assign('title', 'Add Modality'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-plus-circle me-2"></i>Add New Modality
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-camera me-2"></i>Create a new imaging modality
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-left me-2"></i>Back to Modalities',
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-warning', 'escape' => false]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-camera me-2 text-warning"></i>Modality Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <?php echo $this->Form->create($modality, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    
                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <?php echo $this->Form->control('name', [
                                'label' => 'Modality Name *',
                                'class' => 'form-control' . ($modality->hasErrors('name') ? ' is-invalid' : ''),
                                'required' => true,
                                'placeholder' => 'e.g., CT Scanner, MRI, X-Ray, Ultrasound'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <?php echo $this->Form->control('description', [
                                'label' => 'Description',
                                'type' => 'textarea',
                                'class' => 'form-control' . ($modality->hasErrors('description') ? ' is-invalid' : ''),
                                'rows' => 4,
                                'required' => false,
                                'placeholder' => 'Describe the modality specifications, capabilities, and technical details...'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info border-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> This modality will be available for exam assignments and scheduling once created.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-times me-2"></i>Cancel',
                                    ['action' => 'index'],
                                    ['class' => 'btn btn-outline-secondary', 'escape' => false]
                                ) ?>
                                <?php echo $this->Form->button(
                                    '<i class="fas fa-plus me-2"></i>Create Modality',
                                    [
                                        'class' => 'btn btn-warning text-dark fw-bold',
                                        'type' => 'submit',
                                        'escapeTitle' => false
                                    ]
                                ) ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php echo $this->Form->end() ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Modality Types Guide -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>Common Modalities
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-info me-2">Imaging</span>
                            <strong class="text-dark">Diagnostic Imaging</strong>
                        </div>
                        <small class="text-muted">CT Scanner, MRI, X-Ray, Mammography</small>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success me-2">Ultrasound</span>
                            <strong class="text-dark">Ultrasound Systems</strong>
                        </div>
                        <small class="text-muted">General US, Cardiac Echo, Doppler</small>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary me-2">Nuclear</span>
                            <strong class="text-dark">Nuclear Medicine</strong>
                        </div>
                        <small class="text-muted">PET Scan, SPECT, Gamma Camera</small>
                    </div>
                    
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary me-2">Special</span>
                            <strong class="text-dark">Specialized Equipment</strong>
                        </div>
                        <small class="text-muted">Fluoroscopy, Angiography, Bone Densitometry</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add form validation
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    }

    // Auto-focus on name field
    const nameField = document.querySelector('#name');
    if (nameField) {
        nameField.focus();
    }
});
</script>