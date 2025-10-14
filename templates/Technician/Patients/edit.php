<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Patient $patient
 */

$this->setLayout('technician');
$this->assign('title', 'Edit Patient');
?>
<div class="patients edit content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-user-edit me-2 text-secondary"></i>Edit Patient
            </h1>
            <p class="text-muted mb-0">Update patient information for <?php echo h($patient->user->first_name . ' ' . $patient->user->last_name) ?></p>
        </div>
        <div class="btn-group" role="group">
            <?php echo $this->Html->link(
                '<i class="fas fa-eye me-1"></i>View Patient',
                ['action' => 'view', $patient->id],
                ['class' => 'btn btn-primary', 'escape' => false]
            ) ?>
            <?php echo $this->Html->link(
                '<i class="fas fa-arrow-left me-1"></i>Back to Patients',
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
                        <i class="fas fa-user-edit me-2"></i>Edit Patient Information
                    </h5>
                </div>
                <div class="card-body">
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
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Note:</strong> Changing the patient status will affect their ability to access the system. 
                        Setting status to 'inactive' or 'suspended' will prevent login.
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 text-end">
                    <?php echo $this->Form->button(__('Cancel'), [
                        'type' => 'button',
                        'class' => 'btn btn-outline-secondary me-2',
                        'onclick' => 'window.location.href="' . $this->Url->build(['action' => 'view', $patient->id]) . '"'
                    ]) ?>
                    <?php echo $this->Form->button(__('Update Patient'), [
                        'class' => 'btn btn-primary',
                        'type' => 'submit'
                    ]) ?>
                </div>
            </div>

            <?php echo $this->Form->end() ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
        <!-- Patient Overview -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-user-circle me-2"></i>Patient Overview
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-id-card me-2 text-primary"></i>
                        <strong>Patient ID:</strong>
                    </div>
                    <small class="text-muted">#<?php echo $patient->id ?></small>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-calendar-plus me-2 text-success"></i>
                        <strong>Registered:</strong>
                    </div>
                    <small class="text-muted"><?php echo $patient->created->format('M j, Y') ?></small>
                </div>
                
                <?php if ($patient->modified > $patient->created): ?>
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-edit me-2 text-warning"></i>
                        <strong>Last Updated:</strong>
                    </div>
                    <small class="text-muted"><?php echo $patient->modified->format('M j, Y g:i A') ?></small>
                </div>
                <?php endif; ?>
                
                <div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-hospital me-2 text-info"></i>
                        <strong>Hospital:</strong>
                    </div>
                    <small class="text-muted"><?php echo h($patient->hospital->name) ?></small>
                </div>
            </div>
        </div>
        
        <!-- Edit Guidelines -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Update Guidelines
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-warning me-2">!</span>
                        <strong>Required Fields</strong>
                    </div>
                    <small class="text-muted">First name, last name, and email must be provided.</small>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-info me-2">i</span>
                        <strong>Username Changes</strong>
                    </div>
                    <small class="text-muted">Username cannot be changed after creation for security.</small>
                </div>
                
                <div>
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-success me-2">✓</span>
                        <strong>Audit Trail</strong>
                    </div>
                    <small class="text-muted">All changes are logged for security purposes.</small>
                </div>
            </div>
        </div>
        
        <!-- Patient Actions -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-tools me-2"></i>Patient Management
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-eye me-1"></i>View Patient Profile',
                        ['action' => 'view', $patient->id],
                        ['class' => 'btn btn-outline-secondary btn-sm', 'escape' => false]
                    ) ?>
                    
                    <?php echo $this->Html->link(
                        '<i class="fas fa-file-medical me-1"></i>Create New Case',
                        ['controller' => 'Cases', 'action' => 'add', '?' => ['patient_id' => $patient->user_id]],
                        ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
                    ) ?>
                    
                    <?php if ($patient->user && $patient->user->status === 'active'): ?>
                    <?php echo $this->Form->postLink(
                        '<i class="fas fa-user-times me-1"></i>Deactivate Patient',
                        ['action' => 'delete', $patient->id],
                        [
                            'class' => 'btn btn-outline-danger btn-sm',
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
