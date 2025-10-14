<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 */

$this->setLayout('technician');
$this->assign('title', 'Assign Case #' . $case->id);
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
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-plus me-1"></i> Case Assignment</h5>
                </div>
                <div class="card-body">
                    <!-- Case Information Summary -->
                    <div class="bg-light p-3 rounded mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-2">Case Details</h6>
                                <p class="mb-1"><strong>Patient:</strong> 
                                    <?php if ($case->patient_users): ?>
                                        <?php echo h($case->patient_users->first_name . ' ' . $case->patient_users->last_name); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No patient assigned</span>
                                    <?php endif; ?>
                                </p>
                                <p class="mb-1"><strong>Priority:</strong> 
                                    <span class="<?php echo h($case->getPriorityColorClass()); ?>">
                                        <?php echo h($case->getPriorityLabel()); ?>
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
                                        <?php echo h($case->getStatusLabel()); ?>
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
                                'options' => $scientists,
                                'empty' => 'Select a scientist...',
                                'label' => 'Assign to Scientist',
                                'class' => 'form-select',
                                'required' => true,
                                'help' => 'Choose the scientist who will handle this case'
                            ]); ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <?php echo $this->Form->control('notes', [
                                'type' => 'textarea',
                                'label' => 'Assignment Notes',
                                'class' => 'form-control',
                                'rows' => 4,
                                'placeholder' => 'Add any special instructions or notes for the assigned scientist...',
                                'help' => 'Optional notes about the assignment, special requirements, or priority instructions'
                            ]); ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <?php echo $this->Form->button(__('Assign Case'), [
                            'type' => 'submit',
                            'class' => 'btn btn-primary px-4'
                        ]); ?>
                        
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
                            <li>Select the appropriate scientist</li>
                            <li>Add relevant notes if needed</li>
                            <li>Case status will change to "Assigned"</li>
                            <li>Scientist will be notified</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <h6>Scientist Selection</h6>
                        <p class="small text-muted">
                            Only scientists from your hospital are shown. Consider their specialization and current workload.
                        </p>
                    </div>

                    <div class="mb-3">
                        <h6>After Assignment</h6>
                        <p class="small text-muted">
                            The assigned scientist will take over the case and update its progress through the workflow.
                        </p>
                    </div>
                </div>
            </div>

            <?php if (!empty($scientists)): ?>
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-users me-1"></i> Available Scientists</h6>
                </div>
                <div class="card-body">
                    <div class="small">
                        <p class="text-muted mb-2"><?php echo count($scientists); ?> scientists available for assignment:</p>
                        <?php $count = 0; ?>
                        <?php foreach ($scientists as $id => $name): ?>
                            <?php if ($count >= 5) break; ?>
                            <div class="border-bottom py-1">
                                <a href="#" onclick="selectScientist(<?php echo $id; ?>)" class="text-decoration-none">
                                    <?php echo h($name); ?>
                                </a>
                            </div>
                            <?php $count++; ?>
                        <?php endforeach; ?>
                        
                        <?php if (count($scientists) > 5): ?>
                            <div class="text-muted mt-2">
                                And <?php echo count($scientists) - 5; ?> more scientists...
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="card mt-3">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                    <h6>No Scientists Available</h6>
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
    document.getElementById('assigned-to').value = scientistId;
    document.getElementById('assigned-to').dispatchEvent(new Event('change'));
}

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
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.form-control:focus,
.form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>