<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 */

// Determine whether there is an existing valid doctor assignment (case assignments are sorted by timestamp desc)
$hasValidAssignment = !empty($case->case_assignments) &&
                     isset($case->case_assignments[0]->assigned_to_user) &&
                     $case->case_assignments[0]->assigned_to_user &&
                     isset($case->case_assignments[0]->assigned_to_user->role) &&
                     $case->case_assignments[0]->assigned_to_user->role &&
                     strtolower($case->case_assignments[0]->assigned_to_user->role->type) === 'doctor';

$this->assign('title', ($hasValidAssignment ? 'Change Assignment for Case #' : 'Promote Case #') . $case->id . ' to Doctor');
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-success text-white p-4">
            <div class="row align-items-center">
                    <div class="col-md-8">
                    <?php if ($hasValidAssignment): ?>
                        <h2 class="mb-2 fw-bold">
                            <i class="fas fa-exchange-alt me-2"></i>Change Assignment
                        </h2>
                    <?php else: ?>
                        <h2 class="mb-2 fw-bold">
                            <i class="fas fa-level-up-alt me-2"></i>Promote Case to Doctor
                        </h2>
                    <?php endif; ?>
                    <p class="mb-0">
                        <i class="fas fa-file-medical me-2"></i>Case #<?php echo h($case->id); ?>
                        <?php if ($case->patient_user): ?>
                            - <?php echo $this->PatientMask->displayName($case->patient_user); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-eye me-1"></i>View Case',
                            ['action' => 'view', $case->id],
                            ['class' => 'btn btn-light', 'escape' => false]
                        ); ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-arrow-left me-1"></i>Back',
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-light', 'escape' => false]
                        ); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Case Information Card -->
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light border-0 py-3">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-file-medical me-2 text-success"></i>Case Information
            </h5>
        </div>
        <div class="card-body bg-white">
            <div class="row">
                <div class="col-md-3">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="fw-semibold text-muted">Patient:</td>
                            <td>
                                <?php if ($case->patient_user): ?>
                                    <?php echo $this->PatientMask->displayName($case->patient_user); ?>
                                    <br><small class="text-muted">
                                        DOB: <?php echo $this->PatientMask->displayDob($case->patient_user); ?>
                                    </small>
                                <?php else: ?>
                                    <span class="text-muted">No patient assigned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-3">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="fw-semibold text-muted">Priority:</td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($case->priority) {
                                        'urgent' => 'danger',
                                        'high' => 'warning',
                                        'medium' => 'info',
                                        'low' => 'secondary',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo h(ucfirst($case->priority)); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-3">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="fw-semibold text-muted">Case Date:</td>
                            <td><?php echo $case->date ? $case->date->format('M j, Y') : '<span class="text-muted">Not set</span>'; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-3">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="fw-semibold text-muted">Status:</td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($case->status) {
                                        'draft' => 'secondary',
                                        'assigned' => 'info',
                                        'in_progress' => 'warning',
                                        'review' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo h(ucfirst(str_replace('_', ' ', $case->status))); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Current Assignment Alert (if exists) -->
    <?php if (!empty($case->case_assignments)): ?>
        <?php $currentAssignment = $case->case_assignments[0]; ?>
        <?php 
        // Only show alert if currently assigned to a DOCTOR (not scientist)
        $isDoctorAssigned = isset($currentAssignment->assigned_to_user) && 
                           $currentAssignment->assigned_to_user &&
                           isset($currentAssignment->assigned_to_user->role) &&
                           $currentAssignment->assigned_to_user->role &&
                           $currentAssignment->assigned_to_user->role->type === 'doctor';
        ?>
        <?php if ($isDoctorAssigned): ?>
        <div class="card border-0 shadow mb-4">
            <div class="card-body bg-warning-subtle">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1 text-warning"></i>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold text-dark mb-2">
                            <i class="fas fa-user-check me-1"></i>Case Already Assigned to Doctor
                        </h5>
                        <p class="mb-2">
                            This case is currently assigned to 
                            <strong><?php echo h($currentAssignment->assigned_to_user->first_name . ' ' . $currentAssignment->assigned_to_user->last_name); ?></strong>
                            <?php if (isset($currentAssignment->assigned_to_user->role) && $currentAssignment->assigned_to_user->role): ?>
                                <span class="badge rounded-pill bg-info text-white ms-2">
                                    <?php echo h($this->Role->label($currentAssignment->assigned_to_user->role->type)); ?>
                                </span>
                            <?php endif; ?>
                        </p>
                        <hr class="my-2">
                        <div class="small">
                            <p class="mb-1">
                                <i class="fas fa-calendar me-1"></i>
                                <strong>Assigned on:</strong> <?php echo $currentAssignment->timestamp->format('F j, Y \a\t g:i A'); ?>
                            </p>
                            <?php if (isset($currentAssignment->user) && $currentAssignment->user): ?>
                            <p class="mb-1">
                                <i class="fas fa-user me-1"></i>
                                <strong>Assigned by:</strong> <?php echo h($currentAssignment->user->first_name . ' ' . $currentAssignment->user->last_name); ?>
                                <?php if (isset($currentAssignment->user->role) && $currentAssignment->user->role): ?>
                                    <span class="badge rounded-pill bg-secondary ms-1">
                                        <?php echo h($this->Role->label($currentAssignment->user->role->type)); ?>
                                    </span>
                                <?php endif; ?>
                            </p>
                            <?php endif; ?>
                            <?php if (!empty($currentAssignment->notes)): ?>
                                <p class="mb-0">
                                    <i class="fas fa-comment me-1"></i>
                                    <strong>Notes:</strong> <?php echo h($currentAssignment->notes); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="alert alert-info border-0 mt-3 mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Reassignment:</strong> Selecting a new doctor below will reassign this case and update the assignment history.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="row">
        <!-- Assignment Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-header bg-light border-0 py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <?php if ($hasValidAssignment): ?>
                            <i class="fas fa-exchange-alt me-2 text-success"></i>Change Assignment
                        <?php else: ?>
                            <i class="fas fa-user-md me-2 text-success"></i>Promote Case to Doctor
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <?php echo $this->Form->create($case, ['novalidate' => true]); ?>
                    
                    <div class="alert alert-success border-0 mb-4">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Promotion Process:</strong> Select a doctor from your hospital to take medical responsibility for this case. 
                        The doctor will be notified and the case will be elevated for medical review and diagnosis.
                    </div>
                    
                    <div class="mb-4">
                        <?php echo $this->Form->control('assigned_to', [
                            'type' => 'select',
                            'options' => $doctors,
                            'empty' => 'Select a doctor...',
                            'label' => [
                                'text' => ($hasValidAssignment ? 'Change Assignment' : 'Promote to Doctor'),
                                'class' => 'form-label fw-semibold'
                            ],
                            'class' => 'form-select',
                            'required' => true,
                            'help' => 'Choose the doctor who will provide medical oversight for this case'
                        ]); ?>
                    </div>

                    <div class="mb-4">
                        <?php echo $this->Form->control('notes', [
                            'type' => 'textarea',
                            'label' => [
                                'text' => ($hasValidAssignment ? 'Change Assignment Notes' : 'Promotion Notes'),
                                'class' => 'form-label fw-semibold'
                            ],
                            'class' => 'form-control',
                            'rows' => 4,
                            'placeholder' => ($hasValidAssignment 
                                ? 'Add reason for the change in assignment or any special instructions...' 
                                : 'Add any findings, recommendations, or special instructions for the doctor...'),
                            'help' => 'Include your scientific analysis, findings, or any medical concerns that require doctor attention'
                        ]); ?>
                    </div>
                </div>
                <div class="card-footer bg-light border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-times me-1"></i>Cancel',
                            ['action' => 'view', $case->id],
                            ['class' => 'btn btn-outline-secondary', 'escape' => false]
                        ); ?>
                        
                        <?php echo $this->Form->button(
                            ($hasValidAssignment ? '<i class="fas fa-user-md me-1"></i>Change Doctor' : '<i class="fas fa-level-up-alt me-1"></i>Promote to Doctor'), 
                            ['type' => 'submit', 'class' => 'btn btn-success', 'escapeTitle' => false]
                        ); ?>
                    </div>
                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>

        <!-- Sidebar - Guidelines and Available Doctors -->
        <div class="col-lg-4">
            <!-- Promotion Guidelines -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle me-2 text-success"></i>Promotion Guidelines
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="mb-3">
                        <h6 class="fw-semibold">Promotion Process</h6>
                        <ol class="small text-muted mb-0">
                            <li>Complete your scientific analysis</li>
                            <li>Select the appropriate doctor</li>
                            <li>Include your findings and recommendations</li>
                            <li>Case will be elevated for medical review</li>
                            <li>Doctor will be notified</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-semibold">Doctor Selection</h6>
                        <p class="small text-muted mb-0">
                            Only qualified doctors from your hospital are shown. Consider their specialization and expertise relevant to this case.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-semibold">Scientific Handoff</h6>
                        <p class="small text-muted mb-0">
                            Include your analysis, observations, and any concerns that require medical attention. This helps the doctor make informed decisions.
                        </p>
                    </div>

                    <div class="mb-0">
                        <h6 class="fw-semibold">After Promotion</h6>
                        <p class="small text-muted mb-0">
                            The assigned doctor will take medical responsibility and guide the case through diagnosis and treatment planning.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Available Doctors -->
            <?php if (!empty($doctors)): ?>
            <div class="card border-0 shadow">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-md me-2 text-success"></i>Available Doctors
                    </h6>
                </div>
                <div class="card-body bg-white" style="max-height: 400px; overflow-y: auto;">
                    <div class="alert alert-success border-0 mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong><?php echo count($doctors); ?> doctors</strong> available for case promotion
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($doctors as $id => $name): ?>
                            <div class="list-group-item border-0 px-0 py-2">
                                <button type="button" 
                                        onclick="selectDoctor(<?php echo $id; ?>)" 
                                        class="btn btn-link text-start p-0 text-decoration-none w-100">
                                    <i class="fas fa-user-md me-2 text-success"></i>
                                    <?php echo h($name); ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card border-0 shadow">
                <div class="card-body bg-white text-center">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                    <h6 class="fw-bold">No Doctors Available</h6>
                    <p class="small text-muted mb-0">
                        No doctors are available for case promotion in your hospital. Please contact your administrator.
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function selectDoctor(doctorId) {
    const selectElement = document.getElementById('assigned-to');
    if (selectElement) {
        selectElement.value = doctorId;
        selectElement.dispatchEvent(new Event('change'));
        
        // Visual feedback
        selectElement.classList.add('border-success');
        setTimeout(() => {
            selectElement.classList.remove('border-success');
        }, 2000);
    }
}
</script>
