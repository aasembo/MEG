<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Exam $exam
 * @var \Cake\Collection\CollectionInterface|string[] $hospitals
 * @var \Cake\Collection\CollectionInterface|string[] $modalities
 * @var \Cake\Collection\CollectionInterface|string[] $departments
 */
?>
<?php $this->assign('title', 'Add Exam'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-plus-circle me-2"></i>Add New Exam
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-file-medical me-2"></i>Create a new medical exam
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-left me-2"></i>Back to Exams',
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
                        <i class="fas fa-file-medical me-2 text-warning"></i>Exam Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <?php echo $this->Form->create($exam, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    
                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('name', [
                                'label' => 'Exam Name *',
                                'class' => 'form-control' . ($exam->hasErrors('name') ? ' is-invalid' : ''),
                                'required' => true,
                                'placeholder' => 'e.g., Chest X-Ray, MRI Brain, CT Abdomen'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('department_id', [
                                'label' => 'Department *',
                                'type' => 'select',
                                'options' => $departments,
                                'empty' => 'Select a department...',
                                'class' => 'form-select' . ($exam->hasErrors('department_id') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('modality_id', [
                                'label' => 'Modality',
                                'type' => 'select',
                                'options' => $modalities,
                                'empty' => 'Select a modality...',
                                'class' => 'form-select' . ($exam->hasErrors('modality_id') ? ' is-invalid' : ''),
                                'required' => false
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <?php echo $this->Form->control('duration_minutes', [
                                'label' => 'Duration (Minutes)',
                                'type' => 'number',
                                'class' => 'form-control' . ($exam->hasErrors('duration_minutes') ? ' is-invalid' : ''),
                                'min' => 1,
                                'max' => 480,
                                'placeholder' => 'e.g., 30'
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-4 pt-2">
                                <div class="form-check">
                                    <?php echo $this->Form->control('preparation_required', [
                                        'type' => 'checkbox',
                                        'class' => 'form-check-input',
                                        'label' => [
                                            'text' => 'Preparation Required',
                                            'class' => 'form-check-label fw-semibold'
                                        ]
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mt-4 pt-2">
                                <div class="form-check">
                                    <?php echo $this->Form->control('contrast_required', [
                                        'type' => 'checkbox',
                                        'class' => 'form-check-input',
                                        'label' => [
                                            'text' => 'Contrast Required',
                                            'class' => 'form-check-label fw-semibold'
                                        ]
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <?php echo $this->Form->control('description', [
                                'label' => 'Description',
                                'type' => 'textarea',
                                'class' => 'form-control' . ($exam->hasErrors('description') ? ' is-invalid' : ''),
                                'rows' => 3,
                                'required' => false,
                                'placeholder' => 'Describe the exam purpose, what it shows, and any relevant details...'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <?php echo $this->Form->control('preparation_instructions', [
                                'label' => 'Preparation Instructions',
                                'type' => 'textarea',
                                'class' => 'form-control' . ($exam->hasErrors('preparation_instructions') ? ' is-invalid' : ''),
                                'rows' => 3,
                                'required' => false,
                                'placeholder' => 'Instructions for patients on how to prepare for this exam...'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info border-0">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> The exam will be automatically linked to your hospital and will be available for patient scheduling.
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
                                    '<i class="fas fa-plus me-2"></i>Create Exam',
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
            <!-- Exam Types Guide -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-file-medical me-2 text-warning"></i>Common Exam Types
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-info me-2">Imaging</span>
                            <strong class="text-dark">Diagnostic Imaging</strong>
                        </div>
                        <small class="text-muted">X-Ray, CT Scan, MRI, Ultrasound, Mammography</small>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success me-2">Lab</span>
                            <strong class="text-dark">Laboratory Tests</strong>
                        </div>
                        <small class="text-muted">Blood Work, Urine Analysis, Cultures, Pathology</small>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-primary me-2">Cardiac</span>
                            <strong class="text-dark">Heart Studies</strong>
                        </div>
                        <small class="text-muted">ECG, Echocardiogram, Stress Test, Cardiac Catheterization</small>
                    </div>
                    
                    <div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary me-2">Endoscopy</span>
                            <strong class="text-dark">Endoscopic Procedures</strong>
                        </div>
                        <small class="text-muted">Colonoscopy, Gastroscopy, Bronchoscopy, Arthroscopy</small>
                    </div>
                </div>
            </div>
            
            <!-- Guidelines -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>Exam Setup Guidelines
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Use clear, descriptive names</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Specify preparation requirements</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Include accurate duration estimates</small>
                        </li>
                        <li>
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Link to appropriate department</small>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- After Creation -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bolt me-2 text-warning"></i>After Creation
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <small class="text-muted mb-3 d-block">Once the exam is created, you can:</small>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Schedule patient appointments</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Link to procedures and protocols</small>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Generate exam reports</small>
                        </li>
                        <li class="mb-0">
                            <i class="fas fa-check text-success me-2"></i>
                            <small>Track exam statistics</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>