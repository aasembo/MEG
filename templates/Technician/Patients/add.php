<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $newUser
 * @var \App\Model\Entity\Patient $patient
 */

$this->assign('title', 'Add New Patient');
?>
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-user-plus me-2"></i>Add New Patient
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-hospital me-2"></i>Register a new patient for <?php echo h($currentHospital->name) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-left me-2"></i>Back to Patients',
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-light', 'escape' => false]
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
                    <i class="fas fa-user-circle me-2 text-primary"></i>Patient Information
                </h5>
            </div>
            <div class="card-body bg-white">
            <?php echo $this->Form->create($patient, [
                'class' => 'needs-validation',
                'novalidate' => true,
                'context' => [
                    'entity' => $patient,
                    'table' => 'Patients'
                ]
            ]) ?>            <!-- User Information Section -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <?php echo $this->Form->control('first_name', [
                        'label' => 'First Name *',
                        'class' => 'form-control',
                        'required' => true,
                        'placeholder' => 'Enter first name'
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?php echo $this->Form->control('last_name', [
                        'label' => 'Last Name *',
                        'class' => 'form-control',
                        'required' => true,
                        'placeholder' => 'Enter last name'
                    ]) ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <?php echo $this->Form->control('username', [
                        'label' => 'Username',
                        'class' => 'form-control',
                        'placeholder' => 'Leave blank to auto-generate',
                        'help' => 'Leave blank to automatically generate from name'
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?php echo $this->Form->control('email', [
                        'label' => 'Email Address *',
                        'type' => 'email',
                        'class' => 'form-control',
                        'required' => true,
                        'placeholder' => 'Enter email address'
                    ]) ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <?php echo $this->Form->control('phone', [
                        'label' => 'Phone Number',
                        'class' => 'form-control',
                        'placeholder' => 'Enter phone number',
                        'help' => 'Patient primary phone number'
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <!-- Empty column or additional field can go here -->
                </div>
            </div>

            <!-- Patient Information Section -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <?php echo $this->Form->control('gender', [
                        'label' => 'Gender *',
                        'type' => 'select',
                        'options' => [
                            'M' => 'Male',
                            'F' => 'Female',
                            'O' => 'Other'
                        ],
                        'empty' => 'Select gender',
                        'class' => 'form-select',
                        'required' => true
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?php echo $this->Form->control('dob', [
                        'label' => 'Date of Birth *',
                        'type' => 'date',
                        'class' => 'form-control',
                        'required' => true
                    ]) ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <?php echo $this->Form->control('medical_record_number', [
                        'label' => 'Medical Record Number',
                        'class' => 'form-control',
                        'placeholder' => 'Enter medical record number',
                        'help' => 'Optional - used for external record keeping'
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?php echo $this->Form->control('financial_record_number', [
                        'label' => 'Financial Record Number',
                        'class' => 'form-control',
                        'placeholder' => 'Enter financial record number',
                        'help' => 'Optional - used for billing purposes'
                    ]) ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <?php echo $this->Form->control('address', [
                        'label' => 'Address',
                        'type' => 'textarea',
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => 'Enter full address'
                    ]) ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <?php echo $this->Form->control('emergency_contact_name', [
                        'label' => 'Emergency Contact Name',
                        'class' => 'form-control',
                        'placeholder' => 'Enter emergency contact name'
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?php echo $this->Form->control('emergency_contact_phone', [
                        'label' => 'Emergency Contact Phone',
                        'class' => 'form-control',
                        'placeholder' => 'Enter emergency contact phone'
                    ]) ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-12">
                    <?php echo $this->Form->control('notes', [
                        'label' => 'Additional Notes',
                        'type' => 'textarea',
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => 'Any additional notes about the patient...'
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info border-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> The patient will be automatically assigned to <?php echo h($currentHospital->name) ?> 
                        and given patient role access. Patients cannot login to the system but their information is available for case management.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="d-flex gap-2 justify-content-end">
                        <?php echo $this->Form->button(
                            '<i class="fas fa-times me-2"></i>Cancel',
                            [
                                'type' => 'button',
                                'class' => 'btn btn-outline-secondary',
                                'onclick' => 'window.location.href="' . $this->Url->build(['action' => 'index']) . '"',
                                'escapeTitle' => false
                            ]
                        ) ?>
                        <?php echo $this->Form->button(
                            '<i class="fas fa-user-plus me-2"></i>Create Patient',
                            [
                                'class' => 'btn btn-primary',
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
        <!-- Patient Registration Guide -->
        <div class="card border-0 shadow mb-4">
            <div class="card-header bg-light py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Patient Registration
                </h6>
            </div>
            <div class="card-body bg-white">
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge rounded-pill bg-success me-2">
                            <i class="fas fa-check"></i>
                        </span>
                        <strong class="text-dark">Required Fields</strong>
                    </div>
                    <small class="text-muted">First name, last name, email, gender, and date of birth are required for all patients.</small>
                </div>
                
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge rounded-pill bg-info me-2">
                            <i class="fas fa-info"></i>
                        </span>
                        <strong class="text-dark">Medical Records</strong>
                    </div>
                    <small class="text-muted">Medical and financial record numbers are optional but help with external record keeping.</small>
                </div>
                
                <div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge rounded-pill bg-warning text-dark me-2">
                            <i class="fas fa-exclamation"></i>
                        </span>
                        <strong class="text-dark">Patient Access</strong>
                    </div>
                    <small class="text-muted">Patients cannot login to the system. Their information is managed by hospital staff only.</small>
                </div>
            </div>
        </div>
        
        <!-- Patient Data Guide -->
        <div class="card border-0 shadow mb-4">
            <div class="card-header bg-light py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-database me-2 text-primary"></i>Data Management
                </h6>
            </div>
            <div class="card-body bg-white">
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge rounded-pill bg-primary me-2">1</span>
                        <strong class="text-dark">Basic Information</strong>
                    </div>
                    <small class="text-muted">Name, email, and contact details stored in user profile.</small>
                </div>
                
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge rounded-pill bg-primary me-2">2</span>
                        <strong class="text-dark">Medical Data</strong>
                    </div>
                    <small class="text-muted">Gender, age, DOB, and medical records stored separately for privacy.</small>
                </div>
                
                <div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge rounded-pill bg-primary me-2">3</span>
                        <strong class="text-dark">Hospital Assignment</strong>
                    </div>
                    <small class="text-muted">Patient automatically assigned to <?php echo h($currentHospital->name) ?>.</small>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card border-0 shadow">
            <div class="card-header bg-light py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-bolt me-2 text-warning"></i>After Registration
                </h6>
            </div>
            <div class="card-body bg-white">
                <small class="text-muted mb-3 d-block">Once the patient is created, you can:</small>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        <small>Create medical cases immediately</small>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        <small>Edit patient information</small>
                    </li>
                    <li class="mb-0">
                        <i class="fas fa-check text-success me-2"></i>
                        <small>View patient case history</small>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>

<script>
// Bootstrap form validation
(function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>