<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Exam $exam
 * @var \Cake\Collection\CollectionInterface|string[] $departments
 * @var \Cake\Collection\CollectionInterface|string[] $modalities
 */
?>
<?php $this->assign('title', 'Edit Exam'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-edit me-2"></i>Edit Exam
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-file-medical me-2"></i>Update exam: <?php echo h($exam->name) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-eye me-1"></i>View',
                            ['action' => 'view', $exam->id],
                            ['class' => 'btn btn-outline-warning', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-arrow-left me-1"></i>Back',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-warning', 'escape' => false]
                        ) ?>
                    </div>
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
                            <div class="d-flex gap-2 justify-content-end">
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-times me-2"></i>Cancel',
                                    ['action' => 'index'],
                                    ['class' => 'btn btn-outline-secondary', 'escape' => false]
                                ) ?>
                                <?php echo $this->Form->button(
                                    '<i class="fas fa-save me-2"></i>Update Exam',
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
            <!-- Exam Details -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle me-2 text-warning"></i>Exam Details
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Exam ID:</strong><br>
                        <span class="text-muted">#<?php echo h($exam->id) ?></span>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Created:</strong><br>
                        <span class="text-muted"><?php echo $exam->created->format('F j, Y \a\t g:i A') ?></span>
                    </div>
                    
                    <div class="mb-3 pb-3 border-bottom">
                        <strong class="text-dark">Last Modified:</strong><br>
                        <span class="text-muted"><?php echo $exam->modified->format('F j, Y \a\t g:i A') ?></span>
                    </div>
                    
                    <div>
                        <strong class="text-dark">Current Status:</strong><br>
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Active
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Current Settings -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-cogs me-2 text-warning"></i>Current Settings
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-primary mb-1"><?php echo $exam->duration_minutes ?? 'N/A' ?></h5>
                                <small class="text-muted">Minutes</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="<?php echo ($exam->preparation_required || $exam->contrast_required) ? 'text-warning' : 'text-success' ?> mb-1">
                                <?php echo ($exam->preparation_required || $exam->contrast_required) ? 'Special' : 'Standard' ?>
                            </h5>
                            <small class="text-muted">Preparation</small>
                        </div>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="row">
                        <div class="col-6 text-center">
                            <?php if ($exam->preparation_required): ?>
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-clipboard-list me-1"></i>Prep Required
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>No Prep
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-6 text-center">
                            <?php if ($exam->contrast_required): ?>
                                <span class="badge bg-danger">
                                    <i class="fas fa-syringe me-1"></i>Contrast
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>No Contrast
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="d-grid gap-2">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-eye me-2"></i>View Exam',
                            ['action' => 'view', $exam->id],
                            ['class' => 'btn btn-outline-info', 'escape' => false]
                        ) ?>
                        
                        <?php echo $this->Html->link(
                            '<i class="fas fa-plus me-2"></i>Add New Exam',
                            ['action' => 'add'],
                            ['class' => 'btn btn-outline-success', 'escape' => false]
                        ) ?>
                        
                        <?php if ($exam->hasValue('department')): ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-building me-2"></i>View Department',
                            ['controller' => 'Departments', 'action' => 'view', $exam->department->id],
                            ['class' => 'btn btn-outline-primary', 'escape' => false]
                        ) ?>
                        <?php endif; ?>
                        
                        <?php echo $this->Form->postLink(
                            '<i class="fas fa-trash me-2"></i>Delete Exam',
                            ['action' => 'delete', $exam->id],
                            [
                                'class' => 'btn btn-outline-danger',
                                'escape' => false,
                                'confirm' => 'Are you sure you want to delete this exam? This action cannot be undone.'
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>