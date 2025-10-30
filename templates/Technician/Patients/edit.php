<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Patient $patient
 */

$this->assign('title', 'Edit Patient');
?>
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-user-edit me-2"></i>Edit Patient
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>Update patient information for <?php echo h($patient->user->first_name . ' ' . $patient->user->last_name) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-eye me-1"></i>View',
                            ['action' => 'view', $patient->id],
                            ['class' => 'btn btn-light', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-arrow-left me-1"></i>Back',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-light', 'escape' => false]
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
                        <i class="fas fa-user-edit me-2 text-primary"></i>Edit Patient Information
                    </h5>
                </div>
                <div class="card-body bg-white">
            <?php echo $this->Form->create($patient, ['class' => 'needs-validation', 'novalidate' => true]) ?>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <?php echo $this->Form->control('first_name', [
                        'label' => 'First Name *',
                        'class' => 'form-control',
                        'required' => true,
                        'placeholder' => 'Enter first name',
                        'value' => $patient->user ? $patient->user->first_name : ''
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?php echo $this->Form->control('last_name', [
                        'label' => 'Last Name *',
                        'class' => 'form-control',
                        'required' => true,
                        'placeholder' => 'Enter last name',
                        'value' => $patient->user ? $patient->user->last_name : ''
                    ]) ?>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <?php echo $this->Form->control('username', [
                        'label' => 'Username *',
                        'class' => 'form-control',
                        'required' => true,
                        'readonly' => true,
                        'help' => 'Username cannot be changed after creation',
                        'value' => $patient->user ? $patient->user->username : ''
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?php echo $this->Form->control('email', [
                        'label' => 'Email Address *',
                        'type' => 'email',
                        'class' => 'form-control',
                        'required' => true,
                        'placeholder' => 'Enter email address',
                        'value' => $patient->user ? $patient->user->email : ''
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

            <div class="row mb-3">
                <div class="col-md-6">
                    <?php echo $this->Form->control('gender', [
                        'label' => 'Gender',
                        'type' => 'select',
                        'options' => [
                            'M' => 'Male',
                            'F' => 'Female',
                            'O' => 'Other'
                        ],
                        'empty' => 'Select gender',
                        'class' => 'form-select',
                        'value' => $patient->gender ?? ''
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?php echo $this->Form->control('dob', [
                        'label' => 'Date of Birth',
                        'type' => 'date',
                        'class' => 'form-control'
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
                <div class="col-md-6">
                    <?php echo $this->Form->control('status', [
                        'label' => 'Account Status',
                        'type' => 'select',
                        'options' => $statusOptions,
                        'class' => 'form-select',
                        'empty' => false,
                        'value' => $patient->user ? $patient->user->status : 'active'
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <!-- Status information or other field can go here -->
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
                        <strong>Note:</strong> Changes will be saved to the patient record. Patients cannot login to the system - their information is managed by hospital staff only.
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
                                'onclick' => 'window.location.href="' . $this->Url->build(['action' => 'view', $patient->id]) . '"',
                                'escapeTitle' => false
                            ]
                        ) ?>
                        <?php echo $this->Form->button(
                            '<i class="fas fa-save me-2"></i>Update Patient',
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
        <!-- Patient Overview -->
        <div class="card border-0 shadow mb-4">
            <div class="card-header bg-light py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-user-circle me-2 text-primary"></i>Patient Overview
                </h6>
            </div>
            <div class="card-body bg-white">
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-id-card me-2 text-primary"></i>
                        <strong class="text-dark">Patient ID:</strong>
                    </div>
                    <span class="badge bg-light text-dark border">#<?php echo $patient->id ?></span>
                </div>
                
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-calendar-plus me-2 text-success"></i>
                        <strong class="text-dark">Registered:</strong>
                    </div>
                    <small class="text-muted"><?php echo $patient->created->format('M j, Y') ?></small>
                </div>
                
                <?php if ($patient->modified > $patient->created): ?>
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-edit me-2 text-warning"></i>
                        <strong class="text-dark">Last Updated:</strong>
                    </div>
                    <small class="text-muted"><?php echo $patient->modified->format('M j, Y g:i A') ?></small>
                </div>
                <?php endif; ?>
                
                <div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-hospital me-2 text-info"></i>
                        <strong class="text-dark">Hospital:</strong>
                    </div>
                    <small class="text-muted"><?php echo h($patient->hospital->name) ?></small>
                </div>
            </div>
        </div>
        
        <!-- Edit Guidelines -->
        <div class="card border-0 shadow mb-4">
            <div class="card-header bg-light py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Update Guidelines
                </h6>
            </div>
            <div class="card-body bg-white">
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge rounded-pill bg-warning text-dark me-2">
                            <i class="fas fa-exclamation"></i>
                        </span>
                        <strong class="text-dark">Required Fields</strong>
                    </div>
                    <small class="text-muted">First name, last name, and email must be provided.</small>
                </div>
                
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge rounded-pill bg-info me-2">
                            <i class="fas fa-info"></i>
                        </span>
                        <strong class="text-dark">Username Changes</strong>
                    </div>
                    <small class="text-muted">Username cannot be changed after creation for security.</small>
                </div>
                
                <div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge rounded-pill bg-success me-2">
                            <i class="fas fa-check"></i>
                        </span>
                        <strong class="text-dark">Audit Trail</strong>
                    </div>
                    <small class="text-muted">All changes are logged for security purposes.</small>
                </div>
            </div>
        </div>
        
        <!-- Patient Actions -->
        <div class="card border-0 shadow">
            <div class="card-header bg-light py-3">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-tools me-2 text-warning"></i>Patient Management
                </h6>
            </div>
            <div class="card-body bg-white">
                <div class="d-grid gap-2">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-eye me-2"></i>View Patient Profile',
                        ['action' => 'view', $patient->id],
                        ['class' => 'btn btn-outline-secondary', 'escape' => false]
                    ) ?>
                    
                    <?php echo $this->Html->link(
                        '<i class="fas fa-file-medical me-2"></i>Create New Case',
                        ['controller' => 'Cases', 'action' => 'add', '?' => ['patient_id' => $patient->user_id]],
                        ['class' => 'btn btn-outline-primary', 'escape' => false]
                    ) ?>
                    
                    <?php if ($patient->user && $patient->user->status === 'active'): ?>
                    <?php echo $this->Form->postLink(
                        '<i class="fas fa-user-times me-2"></i>Deactivate Patient',
                        ['action' => 'delete', $patient->id],
                        [
                            'class' => 'btn btn-outline-danger',
                            'escape' => false,
                            'confirm' => 'Are you sure you want to deactivate this patient? This will hide them from active lists but preserve their case history.'
                        ]
                    ) ?>
                    <?php endif; ?>
                </div>
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