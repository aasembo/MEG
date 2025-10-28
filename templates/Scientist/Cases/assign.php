<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 */

$this->setLayout('scientist');
$this->assign('title', 'Assign Case #' . $case->id . ' to Doctor');
?>

<div class="cases assign content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo __('Assign Case #'); ?><?php echo h($case->id); ?></h2>
        <div>
            <?php echo $this->Html->link(
                '<i class="fas fa-eye me-1"></i>' . __('View Case'),
                ['action' => 'view', $case->id],
                ['class' => 'btn btn-info', 'escape' => false]
            ); ?>
            <?php echo $this->Html->link(
                '<i class="fas fa-arrow-left me-1"></i>' . __('Back to Cases'),
                ['action' => 'index'],
                ['class' => 'btn btn-outline-secondary', 'escape' => false]
            ); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
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
                <div class="alert alert-warning border-warning" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-triangle fa-2x me-3 mt-1"></i>
                        <div class="flex-grow-1">
                            <h5 class="alert-heading mb-2">
                                <i class="fas fa-user-check me-1"></i> Case Already Assigned to Doctor
                            </h5>
                            <p class="mb-2">
                                This case is currently assigned to 
                                <strong><?php echo h($currentAssignment->assigned_to_user->first_name . ' ' . $currentAssignment->assigned_to_user->last_name); ?></strong>
                                <?php if (isset($currentAssignment->assigned_to_user->role) && $currentAssignment->assigned_to_user->role): ?>
                                    <span class="badge bg-info ms-2">
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
                                        <span class="badge bg-secondary ms-1">
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
                            <div class="alert alert-light border mt-3 mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Reassignment:</strong> Selecting a new doctor below will reassign this case and update the assignment history.
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <?php 
                        // Check if there's a valid doctor assignment (not just any assignment)
                        $hasValidAssignment = !empty($case->case_assignments) && 
                                             isset($case->case_assignments[0]->assigned_to_user) && 
                                             $case->case_assignments[0]->assigned_to_user &&
                                             isset($case->case_assignments[0]->assigned_to_user->role) &&
                                             $case->case_assignments[0]->assigned_to_user->role &&
                                             $case->case_assignments[0]->assigned_to_user->role->type === 'doctor';
                        ?>
                        <?php if ($hasValidAssignment): ?>
                            <i class="fas fa-exchange-alt me-1"></i> Reassign Case to Doctor
                        <?php else: ?>
                            <i class="fas fa-user-plus me-1"></i> Assign Case to Doctor
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Case Information Summary -->
                    <div class="bg-light p-3 rounded mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-2">Case Details</h6>
                                <p class="mb-1"><strong>Patient:</strong> 
                                    <?php if ($case->patient_user): ?>
                                        <?php echo h($case->patient_user->first_name . ' ' . $case->patient_user->last_name); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No patient assigned</span>
                                    <?php endif; ?>
                                </p>
                                <p class="mb-1"><strong>Priority:</strong> 
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
                                </p>
                                <p class="mb-0"><strong>Current Status:</strong> 
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
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-2">Timeline</h6>
                                <p class="mb-1"><strong>Created:</strong> <?php echo $case->created->format('M j, Y \a\t g:i A'); ?></p>
                                <p class="mb-1"><strong>Last Modified:</strong> <?php echo $case->modified->format('M j, Y \a\t g:i A'); ?></p>
                                <p class="mb-0"><strong>Case Date:</strong> 
                                    <?php echo $case->date ? $case->date->format('F j, Y') : '<span class="text-muted">Not set</span>'; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php echo $this->Form->create($case, ['class' => 'needs-validation', 'novalidate' => true]); ?>
                    
                    <div class="row mb-3">
                        <div class="col-12">
                            <?php echo $this->Form->control('assigned_to', [
                                'type' => 'select',
                                'options' => $doctors,
                                'empty' => 'Select a doctor...',
                                'label' => ($hasValidAssignment ? 'Reassign to Doctor' : 'Assign to Doctor'),
                                'class' => 'form-select',
                                'required' => true,
                                'help' => 'Choose the doctor who will handle this case'
                            ]); ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <?php echo $this->Form->control('notes', [
                                'type' => 'textarea',
                                'label' => ($hasValidAssignment ? 'Reassignment Notes' : 'Assignment Notes'),
                                'class' => 'form-control',
                                'rows' => 4,
                                'placeholder' => ($hasValidAssignment 
                                    ? 'Add reason for reassignment or any special instructions...' 
                                    : 'Add any special instructions or notes for the assigned doctor...'),
                                'help' => 'Optional notes about the assignment, special requirements, or priority instructions'
                            ]); ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <?php echo $this->Form->button(
                            ($hasValidAssignment ? __('Reassign Case') : __('Assign Case')), 
                            [
                                'type' => 'submit',
                                'class' => 'btn btn-primary px-4'
                            ]
                        ); ?>
                        
                        <?php echo $this->Html->link(
                            __('Cancel'),
                            ['action' => 'view', $case->id],
                            ['class' => 'btn btn-outline-secondary px-4']
                        ); ?>
                    </div>

                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-1"></i> Assignment Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Assignment Process</h6>
                        <ol class="small text-muted mb-0">
                            <li>Select the appropriate doctor</li>
                            <li>Add relevant notes if needed</li>
                            <li>Case status will change to "Assigned"</li>
                            <li>Doctor will be notified</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <h6>Doctor Selection</h6>
                        <p class="small text-muted">
                            Only doctors from your hospital are shown. Consider their specialization and current workload.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6>After Assignment</h6>
                        <p class="small text-muted">
                            The assigned doctor will take over the case and update its progress through the workflow.
                        </p>
                    </div>
                </div>
            </div>

            <?php if (!empty($doctors)): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-users me-1"></i> Available Doctors</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p class="text-muted mb-2"><?php echo count($doctors); ?> doctors available for assignment:</p>
                        <?php $count = 0; ?>
                        <?php foreach ($doctors as $id => $name): ?>
                            <?php if ($count >= 5) break; ?>
                            <div class="border-bottom py-1">
                                <span class="text-decoration-none">
                                    <?php echo h($name); ?>
                                </span>
                            </div>
                            <?php $count++; ?>
                        <?php endforeach; ?>
                        
                        <?php if (count($doctors) > 5): ?>
                            <div class="text-muted mt-2">
                                And <?php echo count($doctors) - 5; ?> more doctors...
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card mt-3">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                    <h6>No Doctors Available</h6>
                    <p class="small text-muted mb-0">
                        No doctors are available for assignment in your hospital. Please contact your administrator.
                    </p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
