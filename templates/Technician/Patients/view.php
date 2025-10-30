<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Patient $patient
 */

$this->assign('title', 'Patient Details');
?>
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-user-injured me-2"></i><?php echo h($patient->user->first_name . ' ' . $patient->user->last_name) ?>
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-hospital me-2"></i><?php echo h($currentHospital->name) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit me-1"></i>Edit Patient',
                            ['action' => 'edit', $patient->id],
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
        <!-- Patient Information -->
        <div class="col-md-8">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-user-circle me-2 text-primary"></i>Patient Information
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Full Name:</div>
                        <div class="col-sm-8 text-dark"><?php echo h($patient->user->first_name . ' ' . $patient->user->last_name) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Username:</div>
                        <div class="col-sm-8">
                            <span class="badge rounded-pill bg-secondary">
                                <i class="fas fa-user-circle me-1"></i><?php echo h($patient->user->username) ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Email:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-envelope me-1 text-primary"></i>
                            <a href="mailto:<?php echo h($patient->user->email) ?>" class="text-decoration-none">
                                <?php echo h($patient->user->email) ?>
                            </a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Phone:</div>
                        <div class="col-sm-8">
                            <?php if ($patient->phone): ?>
                                <i class="fas fa-phone me-1 text-success"></i><?php echo h($patient->phone) ?>
                            <?php else: ?>
                                <span class="text-muted">Not provided</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Gender:</div>
                        <div class="col-sm-8">
                            <?php if ($patient->gender): ?>
                                <?php
                                $genderConfig = match($patient->gender) {
                                    'M' => ['class' => 'primary', 'icon' => 'mars', 'text' => 'Male'],
                                    'F' => ['class' => 'danger', 'icon' => 'venus', 'text' => 'Female'],
                                    'O' => ['class' => 'warning', 'icon' => 'transgender', 'text' => 'Other'],
                                    default => ['class' => 'secondary', 'icon' => 'user', 'text' => h($patient->gender)]
                                };
                                $badgeClass = 'badge rounded-pill bg-' . $genderConfig['class'];
                                $badgeClass .= ($genderConfig['class'] === 'warning') ? ' text-dark' : ' text-white';
                                ?>
                                <span class="<?php echo $badgeClass; ?>">
                                    <i class="fas fa-<?php echo $genderConfig['icon'] ?> me-1"></i><?php echo $genderConfig['text'] ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Not provided</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Date of Birth:</div>
                        <div class="col-sm-8">
                            <?php if ($patient->dob): ?>
                                <i class="fas fa-birthday-cake me-1 text-warning"></i>
                                <?php echo $patient->dob->format('F j, Y') ?>
                                <span class="badge bg-light text-dark ms-2">
                                    <?php echo $this->DateTime->formatAge($patient->dob) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Not provided</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Medical Record #:</div>
                        <div class="col-sm-8">
                            <?php if ($patient->medical_record_number): ?>
                                <span class="badge bg-info text-white">
                                    <i class="fas fa-file-medical me-1"></i><?php echo h($patient->medical_record_number) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Not assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Financial Record #:</div>
                        <div class="col-sm-8">
                            <?php if ($patient->financial_record_number): ?>
                                <span class="badge bg-success text-white">
                                    <i class="fas fa-dollar-sign me-1"></i><?php echo h($patient->financial_record_number) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Not assigned</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($patient->address): ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Address:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-map-marker-alt me-1 text-danger"></i>
                            <?php echo nl2br(h($patient->address)) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Emergency Contact:</div>
                        <div class="col-sm-8">
                            <?php if ($patient->emergency_contact_name): ?>
                                <div><i class="fas fa-user-shield me-1 text-warning"></i><?php echo h($patient->emergency_contact_name) ?></div>
                                <?php if ($patient->emergency_contact_phone): ?>
                                    <div class="text-muted small mt-1">
                                        <i class="fas fa-phone me-1"></i><?php echo h($patient->emergency_contact_phone) ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Not provided</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Status:</div>
                        <div class="col-sm-8">
                            <?php
                            $statusConfig = match($patient->user->status) {
                                'active' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Active'],
                                'inactive' => ['class' => 'secondary', 'icon' => 'minus-circle', 'text' => 'Inactive'],
                                'suspended' => ['class' => 'danger', 'icon' => 'ban', 'text' => 'Suspended'],
                                default => ['class' => 'secondary', 'icon' => 'circle', 'text' => ucfirst($patient->user->status)]
                            };
                            $statusBadge = 'badge rounded-pill bg-' . $statusConfig['class'] . ' text-white';
                            ?>
                            <span class="<?php echo $statusBadge; ?>">
                                <i class="fas fa-<?php echo $statusConfig['icon'] ?> me-1"></i><?php echo $statusConfig['text'] ?>
                            </span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-semibold text-muted">Registered:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-calendar-plus me-1 text-info"></i>
                            <?php echo $patient->created->format('F d, Y \a\t g:i A') ?>
                            <div class="text-muted small"><?php echo $patient->created->timeAgoInWords() ?></div>
                        </div>
                    </div>
                    <?php if ($patient->modified != $patient->created): ?>
                    <div class="row mb-0">
                        <div class="col-sm-4 fw-semibold text-muted">Last Updated:</div>
                        <div class="col-sm-8">
                            <i class="fas fa-clock me-1 text-muted"></i>
                            <?php echo $patient->modified->format('F d, Y \a\t g:i A') ?>
                            <div class="text-muted small"><?php echo $patient->modified->timeAgoInWords() ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Stats & Actions -->
        <div class="col-md-4">
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Quick Stats
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Total Cases:</span>
                        <span class="badge rounded-pill bg-primary fs-6"><?php echo $casesCount ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <span class="text-muted">Account Status:</span>
                        <span class="badge rounded-pill bg-<?php echo $statusConfig['class'] ?> text-white">
                            <?php echo $statusConfig['text'] ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Registered:</span>
                        <span class="text-dark small fw-semibold">
                            <?php echo $patient->user->created->format('M d, Y') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bolt me-2 text-warning"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body bg-white">
                    <div class="d-grid gap-2">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-plus-circle me-2"></i>Create New Case', 
                            ['controller' => 'Cases', 'action' => 'add', '?' => ['patient_id' => $patient->user_id]], 
                            ['class' => 'btn btn-success', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-folder-open me-2"></i>View All Cases', 
                            ['controller' => 'Cases', 'action' => 'index', '?' => ['search' => $patient->user->username]], 
                            ['class' => 'btn btn-outline-primary', 'escape' => false]
                        ) ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit me-2"></i>Edit Patient', 
                            ['action' => 'edit', $patient->id], 
                            ['class' => 'btn btn-outline-secondary', 'escape' => false]
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
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-file-medical me-2 text-primary"></i>Recent Cases
                        </h5>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-external-link-alt me-1"></i>View All', 
                            ['controller' => 'Cases', 'action' => 'index', '?' => ['search' => $patient->user->username]], 
                            ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false]
                        ) ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 ps-4 fw-semibold text-uppercase small">Case ID</th>
                                    <th class="border-0 fw-semibold text-uppercase small">Status</th>
                                    <th class="border-0 fw-semibold text-uppercase small">Priority</th>
                                    <th class="border-0 fw-semibold text-uppercase small">Current User</th>
                                    <th class="border-0 fw-semibold text-uppercase small">Created</th>
                                    <th class="border-0 text-center fw-semibold text-uppercase small">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentCases as $case): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-light text-primary border">
                                            <i class="fas fa-hashtag"></i><?php echo h($case->id) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusConfig = match($case->status) {
                                            'draft' => ['class' => 'secondary', 'text' => 'Draft'],
                                            'assigned' => ['class' => 'primary', 'text' => 'Assigned'],
                                            'in_progress' => ['class' => 'warning', 'text' => 'In Progress'],
                                            'review' => ['class' => 'info', 'text' => 'Review'],
                                            'completed' => ['class' => 'success', 'text' => 'Completed'],
                                            'cancelled' => ['class' => 'danger', 'text' => 'Cancelled'],
                                            default => ['class' => 'secondary', 'text' => ucfirst($case->status)]
                                        };
                                        $badge = 'badge rounded-pill bg-' . $statusConfig['class'];
                                        $badge .= ($statusConfig['class'] === 'warning') ? ' text-dark' : ' text-white';
                                        ?>
                                        <span class="<?php echo $badge ?>"><?php echo $statusConfig['text'] ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $priorityConfig = match($case->priority) {
                                            'low' => ['class' => 'success', 'icon' => 'arrow-down', 'text' => 'Low'],
                                            'medium' => ['class' => 'warning', 'icon' => 'minus', 'text' => 'Medium'],
                                            'high' => ['class' => 'danger', 'icon' => 'arrow-up', 'text' => 'High'],
                                            'urgent' => ['class' => 'danger', 'icon' => 'exclamation-triangle', 'text' => 'Urgent'],
                                            default => ['class' => 'secondary', 'icon' => 'circle', 'text' => ucfirst($case->priority)]
                                        };
                                        $priorityBadge = 'badge rounded-pill bg-' . $priorityConfig['class'];
                                        $priorityBadge .= ($priorityConfig['class'] === 'warning') ? ' text-dark' : ' text-white';
                                        ?>
                                        <span class="<?php echo $priorityBadge ?>">
                                            <i class="fas fa-<?php echo $priorityConfig['icon'] ?> me-1"></i><?php echo $priorityConfig['text'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($case->current_user): ?>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle me-2 text-primary"></i>
                                                <span class="text-dark"><?php echo h($case->current_user->first_name . ' ' . $case->current_user->last_name) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Unassigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <i class="fas fa-calendar me-1 text-muted"></i>
                                            <?php echo $case->created->format('M d, Y') ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-clock me-1"></i><?php echo $case->created->timeAgoInWords() ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $this->Html->link(
                                            '<i class="fas fa-eye"></i>', 
                                            ['controller' => 'Cases', 'action' => 'view', $case->id], 
                                            [
                                                'class' => 'btn btn-sm btn-outline-primary', 
                                                'escape' => false, 
                                                'title' => 'View Case',
                                                'data-bs-toggle' => 'tooltip'
                                            ]
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>