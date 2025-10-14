<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 */

$this->setLayout('technician');
$this->assign('title', 'Create New Case');
?>
<div class="cases add content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-medical me-2 text-secondary"></i>Create New Case
            </h1>
            <p class="text-muted mb-0">Create a new medical case for <?php echo h($currentHospital->name) ?></p>
        </div>
        <div>
            <?php echo $this->Html->link(
                '<i class="fas fa-arrow-left me-2"></i>Back to Cases',
                ['action' => 'index'],
                ['class' => 'btn btn-secondary', 'escape' => false]
            ) ?>
        </div>
    </div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-file-medical-alt me-2"></i>Case Information
                </h5>
            </div>
            <div class="card-body">
            <?php echo $this->Form->create($case, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    
                    <!-- Patient and Basic Info -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php if (empty($patients)): ?>
                                <div class="mb-3">
                                    <label class="form-label">Patient *</label>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>No patients found!</strong><br>
                                        You need to register patients before creating cases.
                                        <div class="mt-2">
                                            <?php echo $this->Html->link(
                                                '<i class="fas fa-user-plus me-1"></i>Add New Patient',
                                                ['controller' => 'Patients', 'action' => 'add'],
                                                ['class' => 'btn btn-sm btn-primary', 'escape' => false]
                                            ) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php echo $this->Form->control('patient_id', [
                                    'type' => 'select',
                                    'options' => $patients,
                                    'empty' => 'Select a patient...',
                                    'label' => 'Patient *',
                                    'class' => 'form-select',
                                    'required' => true
                                ]) ?>
                                <div class="form-text">
                                    Don't see the patient you need? 
                                    <?php echo $this->Html->link(
                                        'Add new patient',
                                        ['controller' => 'Patients', 'action' => 'add'],
                                        ['class' => 'text-decoration-none']
                                    ) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('date', [
                                'type' => 'date',
                                'label' => 'Case Date *',
                                'class' => 'form-control',
                                'value' => date('Y-m-d'),
                                'required' => true
                            ]) ?>
                        </div>
                    </div>

                    <!-- Department and Sedation -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('department_id', [
                                'type' => 'select',
                                'options' => $departments,
                                'empty' => 'Select department...',
                                'label' => 'Department *',
                                'class' => 'form-select',
                                'required' => true
                            ]) ?>
                            <div class="form-text">Department handling this case</div>
                        </div>
                        <div class="col-md-6">
                            <?php echo $this->Form->control('sedation_id', [
                                'type' => 'select',
                                'options' => $sedations,
                                'empty' => 'No sedation required',
                                'label' => 'Sedation Level',
                                'class' => 'form-select'
                            ]) ?>
                            <div class="form-text">Optional: Required sedation level</div>
                        </div>
                    </div>

                    <!-- Priority and Status -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <?php echo $this->Form->control('priority', [
                                'type' => 'select',
                                'options' => $priorities,
                                'label' => 'Priority *',
                                'class' => 'form-select',
                                'value' => 'medium',
                                'required' => true
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" value="Draft" readonly>
                                <div class="form-text">New cases start as draft status</div>
                            </div>
                        </div>
                    </div>

                    <!-- Case Description -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <?php echo $this->Form->control('notes', [
                                'type' => 'textarea',
                                'label' => 'Case Notes',
                                'class' => 'form-control',
                                'rows' => 4,
                                'placeholder' => 'Enter case description, symptoms, initial observations...'
                            ]) ?>
                            <div class="form-text">Provide detailed information about the case</div>
                        </div>
                    </div>

                    <!-- Exam Procedures Section -->
                    <div class="card border-secondary mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-procedures me-2"></i>Exam Procedures
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label class="form-label">Select Procedures for this Case</label>
                                    <div id="exam-procedures-container">
                                        <?php if (!empty($examsProcedures)): ?>
                                            <div class="row">
                                                <?php foreach ($examsProcedures as $epId => $epName): ?>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-check">
                                                            <?php echo $this->Form->checkbox("exam_procedures.$epId", [
                                                                'class' => 'form-check-input',
                                                                'id' => "exam_procedure_$epId"
                                                            ]); ?>
                                                            <label class="form-check-label" for="exam_procedure_<?php echo $epId; ?>">
                                                                <?php echo h($epName); ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                No exam procedures available. Contact your administrator to set up procedures.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-text">Select all procedures that need to be performed for this case</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-12 text-end">
                            <?php echo $this->Form->button(__('Cancel'), [
                                'type' => 'button',
                                'class' => 'btn btn-outline-secondary me-2',
                                'onclick' => 'window.location.href="' . $this->Url->build(['action' => 'index']) . '"'
                            ]) ?>
                            <?php if (!empty($patients)): ?>
                                <?php echo $this->Form->button(__('Create Case'), [
                                    'type' => 'submit',
                                    'class' => 'btn btn-primary'
                                ]) ?>
                            <?php else: ?>
                                <button type="button" class="btn btn-secondary" disabled>
                                    Create Case (No Patients Available)
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php echo $this->Form->end() ?>
                </div>
            </div>
        </div>
    
        <div class="col-lg-4">
        <!-- Case Creation Help -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Case Creation Guide
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-primary me-2">1</span>
                        <strong>Patient & Department</strong>
                    </div>
                    <small class="text-muted">Select the patient and department for this case.</small>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-primary me-2">2</span>
                        <strong>Sedation Requirements</strong>
                    </div>
                    <small class="text-muted">Choose sedation level if procedures require it.</small>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-primary me-2">3</span>
                        <strong>Exam Procedures</strong>
                    </div>
                    <small class="text-muted">Select all procedures needed for this case.</small>
                </div>
                
                <div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-primary me-2">4</span>
                        <strong>Priority & Notes</strong>
                    </div>
                    <small class="text-muted">Set priority and add detailed case information.</small>
                </div>
            </div>
        </div>
        
        <!-- Available Modalities -->
        <?php if (!empty($modalities)): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-microscope me-2"></i>Available Modalities
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <?php foreach ($modalities as $modalityId => $modalityName): ?>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-circle text-success me-2" style="font-size: 0.5rem;"></i>
                            <?php echo h($modalityName); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-text">These modalities are available in your hospital</div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Priority Levels -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Priority Levels
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-danger me-2">Urgent</span>
                        <strong>Immediate Attention</strong>
                    </div>
                    <small class="text-muted">Critical cases requiring immediate processing.</small>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-warning me-2">High</span>
                        <strong>Within Hours</strong>
                    </div>
                    <small class="text-muted">Process within a few hours of creation.</small>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-info me-2">Medium</span>
                        <strong>Standard Processing</strong>
                    </div>
                    <small class="text-muted">Normal processing timeline applies.</small>
                </div>
                
                <div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-secondary me-2">Low</span>
                        <strong>When Convenient</strong>
                    </div>
                    <small class="text-muted">Non-urgent cases for routine processing.</small>
                </div>
            </div>
        </div>
        
        <?php if (empty($patients)): ?>
        <!-- No Patients Warning -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-warning">
                <h6 class="mb-0 text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i>No Patients Available
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Action Required:</strong></p>
                <small class="text-muted">You need to register patients before creating cases. Click the button below to add your first patient.</small>
                <div class="mt-3">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-user-plus me-1"></i>Add First Patient',
                        ['controller' => 'Patients', 'action' => 'add'],
                        ['class' => 'btn btn-warning btn-sm', 'escape' => false]
                    ) ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Dynamic procedure selection feedback
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('#exam-procedures-container input[type="checkbox"]');
    
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const selectedCount = document.querySelectorAll('#exam-procedures-container input[type="checkbox"]:checked').length;
            const feedback = document.getElementById('procedure-feedback');
            
            if (!feedback) {
                const feedbackDiv = document.createElement('div');
                feedbackDiv.id = 'procedure-feedback';
                feedbackDiv.className = 'alert alert-info mt-2';
                document.getElementById('exam-procedures-container').appendChild(feedbackDiv);
            }
            
            const feedbackEl = document.getElementById('procedure-feedback');
            if (selectedCount > 0) {
                feedbackEl.innerHTML = `<i class="fas fa-check me-2"></i>${selectedCount} procedure(s) selected for this case.`;
                feedbackEl.className = 'alert alert-success mt-2';
            } else {
                feedbackEl.innerHTML = '<i class="fas fa-info-circle me-2"></i>Select at least one procedure for this case.';
                feedbackEl.className = 'alert alert-info mt-2';
            }
        });
    });
});
</script>
