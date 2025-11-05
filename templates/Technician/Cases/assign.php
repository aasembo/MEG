<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 * @var array $scientists
 * @var \App\Model\Entity\User $user
 */

$this->assign('title', 'Assign Case #' . $case->id);
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-user-plus me-2"></i>Assign Case
                    </h2>
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
                <i class="fas fa-file-medical me-2 text-primary"></i>Case Information
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
                                    <br><small class="text-muted">ID: <?php echo h($case->patient_user->id); ?></small>
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
                            <td><?php echo $this->Status->priorityBadge($case->priority); ?></td>
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
                                <div class="d-flex flex-column gap-1">
                                    <?php echo $this->Status->priorityBadge($case->priority); ?>
                                </div>
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
        <?php if (isset($currentAssignment->assigned_to_user) && $currentAssignment->assigned_to_user): ?>
        <div class="card border-0 shadow mb-4">
            <div class="card-body bg-warning-subtle">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1 text-warning"></i>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold text-dark mb-2">
                            <i class="fas fa-user-check me-1"></i>Case Already Assigned
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
                            <strong>Reassignment:</strong> Selecting a new scientist below will reassign this case and update the assignment history.
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
                        <?php 
                        $hasValidAssignment = !empty($case->case_assignments) && 
                                             isset($case->case_assignments[0]->assigned_to_user) && 
                                             $case->case_assignments[0]->assigned_to_user;
                        ?>
                        <?php if ($hasValidAssignment): ?>
                            <i class="fas fa-exchange-alt me-2 text-primary"></i>Reassign Case to Scientist
                        <?php else: ?>
                            <i class="fas fa-user-plus me-2 text-primary"></i>Assign Case to Scientist
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <?php echo $this->Form->create($case, ['novalidate' => true]); ?>
                    
                    <div class="alert alert-info border-0 mb-4">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Assignment Process:</strong> Select a scientist from your hospital to take responsibility for this case. 
                        The scientist will be notified and the case status will be updated.
                    </div>
                    
                    <div class="mb-4">
                        <?php echo $this->Form->control('assigned_to', [
                            'type' => 'select',
                            'options' => $scientists,
                            'empty' => 'Select a scientist...',
                            'label' => [
                                'text' => ($hasValidAssignment ? 'Reassign to Scientist' : 'Assign to Scientist'),
                                'class' => 'form-label fw-semibold'
                            ],
                            'class' => 'form-select',
                            'required' => true,
                            'help' => 'Choose the scientist who will handle this case'
                        ]); ?>
                    </div>

                    <div class="mb-4">
                        <?php echo $this->Form->control('notes', [
                            'type' => 'textarea',
                            'label' => [
                                'text' => ($hasValidAssignment ? 'Reassignment Notes' : 'Assignment Notes'),
                                'class' => 'form-label fw-semibold'
                            ],
                            'class' => 'form-control',
                            'rows' => 4,
                            'placeholder' => ($hasValidAssignment 
                                ? 'Add reason for reassignment or any special instructions...' 
                                : 'Add any special instructions or notes for the assigned scientist...'),
                            'help' => 'Optional notes about the assignment, special requirements, or priority instructions'
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
                            ($hasValidAssignment ? '<i class="fas fa-exchange-alt me-1"></i>Reassign Case' : '<i class="fas fa-user-plus me-1"></i>Assign Case'), 
                            ['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false]
                        ); ?>
                    </div>
                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>

        <!-- Sidebar - Guidelines and Available Scientists -->
        <div class="col-lg-4">
            <!-- Assignment Guidelines -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Assignment Guidelines
                    </h6>
                </div>
                <div class="card-body bg-white">
                    <div class="mb-3">
                        <h6 class="fw-semibold">Assignment Process</h6>
                        <ol class="small text-muted mb-0">
                            <li>Select the appropriate scientist</li>
                            <li>Add relevant notes if needed</li>
                            <li>Case status will change to "Assigned"</li>
                            <li>Scientist will be notified</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-semibold">Scientist Selection</h6>
                        <p class="small text-muted mb-0">
                            Only scientists from your hospital are shown. Consider their specialization and current workload.
                        </p>
                    </div>

                    <div class="mb-0">
                        <h6 class="fw-semibold">After Assignment</h6>
                        <p class="small text-muted mb-0">
                            The assigned scientist will take over the case and update its progress through the workflow.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Available Scientists -->
            <?php if (!empty($scientists)): ?>
            <div class="card border-0 shadow">
                <div class="card-header bg-light border-0 py-3">
                    <h6 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-users me-2 text-primary"></i>Available Scientists
                    </h6>
                </div>
                <div class="card-body bg-white" style="max-height: 400px; overflow-y: auto;">
                    <div class="alert alert-info border-0 mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong><?php echo count($scientists); ?> scientists</strong> available for assignment
                    </div>
                    <div class="list-group list-group-flush">
                        <?php foreach ($scientists as $id => $name): ?>
                            <div class="list-group-item border-0 px-0 py-2">
                                <button type="button" 
                                        onclick="selectScientist(<?php echo $id; ?>)" 
                                        class="btn btn-link text-start p-0 text-decoration-none w-100">
                                    <i class="fas fa-user-md me-2 text-primary"></i>
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
                    <h6 class="fw-bold">No Scientists Available</h6>
                    <p class="small text-muted mb-0">
                        No scientists are available for assignment in your hospital. Please contact your administrator.
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function selectScientist(scientistId) {
    const selectElement = document.getElementById('assigned-to');
    if (selectElement) {
        selectElement.value = scientistId;
        selectElement.dispatchEvent(new Event('change'));
        
        // Visual feedback
        selectElement.classList.add('border-success');
        setTimeout(() => {
            selectElement.classList.remove('border-success');
        }, 2000);
    }
}
</script>