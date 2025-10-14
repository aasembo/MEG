<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Patient $patient
 */

$this->setLayout('technician');
$this->assign('title', 'Patient Details');
?>
<div class="patients view content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-user me-2 text-secondary"></i><?php echo h($patient->user->first_name . ' ' . $patient->user->last_name) ?>
            </h1>
            <p class="text-muted mb-0">Patient Details - <?php echo h($currentHospital->name) ?></p>
        </div>
        <div class="btn-group" role="group">
            <?php echo $this->Html->link(
                '<i class="fas fa-edit me-1"></i>Edit Patient',
                ['action' => 'edit', $patient->id],
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
        <!-- Patient Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-circle"></i> Patient Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Full Name:</div>
                        <div class="col-sm-9"><?php echo h($patient->user->first_name . ' ' . $patient->user->last_name) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Username:</div>
                        <div class="col-sm-9"><?php echo h($patient->user->username) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Email:</div>
                        <div class="col-sm-9"><?php echo h($patient->user->email) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Phone:</div>
                        <div class="col-sm-9"><?php echo h($patient->phone ?: 'Not provided') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Gender:</div>
                        <div class="col-sm-9">
                            <?php if ($patient->gender): ?>
                                <?php
                                $genderText = match($patient->gender) {
                                    'M' => 'Male',
                                    'F' => 'Female',
                                    'O' => 'Other',
                                    default => h($patient->gender)
                                };
                                ?>
                                <span class="badge bg-light text-dark"><?php echo $genderText ?></span>
                            <?php else: ?>
                                Not provided
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Date of Birth:</div>
                        <div class="col-sm-9">
                            <?php if ($patient->dob): ?>
                                <?php echo $patient->dob->format('F j, Y') ?>
                                <small class="text-muted">
                                    (<?php echo $this->DateTime->formatAge($patient->dob) ?>)
                                </small>
                            <?php else: ?>
                                Not provided
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Medical Record #:</div>
                        <div class="col-sm-9"><?php echo h($patient->medical_record_number ?: 'Not assigned') ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Financial Record #:</div>
                        <div class="col-sm-9"><?php echo h($patient->financial_record_number ?: 'Not assigned') ?></div>
                    </div>
                    <?php if ($patient->address): ?>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Address:</div>
                        <div class="col-sm-9"><?php echo nl2br(h($patient->address)) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Emergency Contact:</div>
                        <div class="col-sm-9">
                            <?php if ($patient->emergency_contact_name): ?>
                                <?php echo h($patient->emergency_contact_name) ?>
                                <?php if ($patient->emergency_contact_phone): ?>
                                    <br><small class="text-muted"><?php echo h($patient->emergency_contact_phone) ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                Not provided
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Status:</div>
                        <div class="col-sm-9">
                            <?php
                            $statusClass = match($patient->user->status) {
                                'active' => 'success',
                                'inactive' => 'secondary',
                                'suspended' => 'danger',
                                default => 'secondary'
                            };
                            ?>
                            <span class="badge bg-<?php echo $statusClass ?>"><?php echo h(ucfirst($patient->user->status)) ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Registered:</div>
                        <div class="col-sm-9">
                            <?php echo $patient->created->format('F d, Y \a\t g:i A') ?>
                            <small class="text-muted">(<?php echo $patient->created->timeAgoInWords() ?>)</small>
                        </div>
                    </div>
                    <?php if ($patient->modified != $patient->created): ?>
                    <div class="row mb-3">
                        <div class="col-sm-3 fw-bold">Last Updated:</div>
                        <div class="col-sm-9">
                            <?php echo $patient->modified->format('F d, Y \a\t g:i A') ?>
                            <small class="text-muted">(<?php echo $patient->modified->timeAgoInWords() ?>)</small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line"></i> Quick Stats
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Total Cases:</span>
                        <span class="badge bg-primary"><?php echo $casesCount ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Account Status:</span>
                        <span class="badge bg-<?php echo $statusClass ?>"><?php echo h(ucfirst($patient->user->status)) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Registered:</span>
                        <span class="text-muted small">
                            <?php echo $patient->user->created->format('M d, Y') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php echo $this->Html->link(__('Create New Case'), 
                            ['controller' => 'Cases', 'action' => 'add', '?' => ['patient_id' => $patient->user_id]], 
                            ['class' => 'btn btn-success btn-sm']
                        ) ?>
                        <?php echo $this->Html->link(__('View All Cases'), 
                            ['controller' => 'Cases', 'action' => 'index', '?' => ['search' => $patient->username]], 
                            ['class' => 'btn btn-outline-primary btn-sm']
                        ) ?>
                        <?php echo $this->Html->link(__('Edit Patient'), 
                            ['action' => 'edit', $patient->id], 
                            ['class' => 'btn btn-outline-secondary btn-sm']
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Cases -->
    <?php if (!empty($recentCases)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-medical"></i> Recent Cases
                    </h5>
                    <?php echo $this->Html->link(__('View All'), 
                        ['controller' => 'Cases', 'action' => 'index', '?' => ['search' => $patient->username]], 
                        ['class' => 'btn btn-sm btn-outline-primary']
                    ) ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Case ID</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Current User</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentCases as $case): ?>
                                <tr>
                                    <td><strong>#<?php echo h($case->id) ?></strong></td>
                                    <td>
                                        <?php
                                        $statusClass = match($case->status) {
                                            'draft' => 'secondary',
                                            'assigned' => 'primary',
                                            'in_progress' => 'warning',
                                            'review' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass ?>"><?php echo h(ucfirst($case->status)) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $priorityClass = match($case->priority) {
                                            'low' => 'success',
                                            'medium' => 'warning',
                                            'high' => 'danger',
                                            'urgent' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $priorityClass ?>"><?php echo h(ucfirst($case->priority)) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($case->current_user): ?>
                                            <?php echo h($case->current_user->first_name . ' ' . $case->current_user->last_name) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $case->created->format('M d, Y') ?>
                                        <br><small class="text-muted"><?php echo $case->created->timeAgoInWords() ?></small>
                                    </td>
                                    <td>
                                        <?php echo $this->Html->link('<i class="fas fa-eye"></i>', 
                                            ['controller' => 'Cases', 'action' => 'view', $case->id], 
                                            ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'title' => 'View Case']
                                        ) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>