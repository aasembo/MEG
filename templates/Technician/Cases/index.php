<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MedicalCase> $cases
 */

$this->setLayout('technician');
$this->assign('title', 'Case Management');
?>

<div class="cases index content">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-1">
                <i class="fas fa-briefcase-medical me-2 text-primary"></i>Case Management
            </h1>
            <p class="text-muted mb-0">
                <i class="fas fa-hospital me-1"></i><?php echo h($currentHospital->name) ?>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <?php echo $this->Html->link(
                '<i class="fas fa-plus me-2"></i>New Case',
                ['action' => 'add'],
                ['class' => 'btn btn-primary', 'escape' => false]
            ) ?>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filters
            </h5>
            <?php if ($search || $status !== 'all' || $priority !== 'all'): ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-times me-1"></i>Clear Filters',
                    ['action' => 'index'],
                    ['class' => 'btn btn-sm btn-outline-secondary', 'escape' => false]
                ); ?>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php echo $this->Form->create(null, ['type' => 'get', 'class' => 'row g-3']); ?>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-info-circle me-1 text-primary"></i>Status
                    </label>
                    <?php echo $this->Form->control('status', [
                        'type' => 'select',
                        'options' => $statusOptions,
                        'value' => $status,
                        'label' => false,
                        'class' => 'form-select',
                        'empty' => false
                    ]); ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-exclamation-circle me-1 text-warning"></i>Priority
                    </label>
                    <?php echo $this->Form->control('priority', [
                        'type' => 'select',
                        'options' => $priorityOptions,
                        'value' => $priority,
                        'label' => false,
                        'class' => 'form-select',
                        'empty' => false
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1 text-success"></i>Search
                    </label>
                    <?php echo $this->Form->control('search', [
                        'type' => 'text',
                        'value' => $search,
                        'label' => false,
                        'class' => 'form-control',
                        'placeholder' => 'Case ID or patient name...'
                    ]); ?>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <?php echo $this->Form->button(
                            '<i class="fas fa-search me-1"></i>' . __('Apply'),
                            ['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false]
                        ); ?>
                    </div>
                </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-3 py-2">
                <?php 
                $totalCount = $this->Paginator->counter('{{count}}');
                echo '<i class="fas fa-file-medical me-1"></i>' . $totalCount . ' ' . ($totalCount == 1 ? 'Case' : 'Cases');
                ?>
            </span>
        </div>
        <?php if (!empty($cases->toArray())): ?>
        <div>
            <small class="text-muted">
                <?php echo $this->Paginator->counter(__('Page {{page}} of {{pages}}')); ?>
            </small>
        </div>
        <?php endif; ?>
    </div>

    <!-- Cases Table -->
    <?php if (!empty($cases->toArray())): ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4" style="width: 80px;">
                                <?php echo $this->Paginator->sort('id', 'Case ID'); ?>
                            </th>
                            <th class="border-0" style="width: 200px;">
                                <?php echo $this->Paginator->sort('patient_id', 'Patient'); ?>
                            </th>
                            <th class="border-0" style="width: 120px;">
                                <?php echo $this->Paginator->sort('status', 'Status'); ?>
                            </th>
                            <th class="border-0" style="width: 100px;">
                                <?php echo $this->Paginator->sort('priority', 'Priority'); ?>
                            </th>
                            <th class="border-0" style="width: 150px;">
                                <?php echo $this->Paginator->sort('date', 'Case Date'); ?>
                            </th>
                            <th class="border-0">
                                <?php echo $this->Paginator->sort('current_user_id', 'Assigned To'); ?>
                            </th>
                            <th class="border-0" style="width: 150px;">
                                <?php echo $this->Paginator->sort('created', 'Created'); ?>
                            </th>
                            <th class="border-0 text-center" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cases as $case): ?>
                        <tr class="case-row">
                            <!-- Case ID -->
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="case-id-icon me-2">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                    <?php echo $this->Html->link(
                                        '<span class="fw-semibold">#' . h($case->id) . '</span>',
                                        ['action' => 'view', $case->id],
                                        ['escape' => false, 'class' => 'text-decoration-none text-primary']
                                    ); ?>
                                </div>
                            </td>
                            
                            <!-- Patient -->
                            <td>
                                <?php if ($case->patient_id && isset($case->patient_user)): ?>
                                    <div class="patient-info">
                                        <div class="fw-semibold text-dark">
                                            <?php echo h($case->patient_user->first_name . ' ' . $case->patient_user->last_name); ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-id-card me-1"></i>ID: <?php echo h($case->patient_user->id) ?>
                                        </div>
                                    </div>
                                <?php elseif ($case->patient_id): ?>
                                    <span class="text-muted">Patient ID: <?php echo h($case->patient_id); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">
                                        <i class="fas fa-user-slash me-1"></i>No patient assigned
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Status -->
                            <td>
                                <?php 
                                // Get technician-specific status
                                $roleStatus = $case->technician_status ?? 'draft';
                                $statusConfig = match($roleStatus) {
                                    'draft' => ['class' => 'secondary', 'icon' => 'file', 'label' => $case->getStatusLabelForRole('technician')],
                                    'assigned' => ['class' => 'info', 'icon' => 'user-check', 'label' => $case->getStatusLabelForRole('technician')],
                                    'in_progress' => ['class' => 'warning', 'icon' => 'spinner', 'label' => $case->getStatusLabelForRole('technician')],
                                    'review' => ['class' => 'primary', 'icon' => 'search', 'label' => $case->getStatusLabelForRole('technician')],
                                    'completed' => ['class' => 'success', 'icon' => 'check-circle', 'label' => $case->getStatusLabelForRole('technician')],
                                    'cancelled' => ['class' => 'danger', 'icon' => 'times-circle', 'label' => $case->getStatusLabelForRole('technician')],
                                    default => ['class' => 'secondary', 'icon' => 'circle', 'label' => $case->getStatusLabelForRole('technician')]
                                };
                                ?>
                                <span class="badge badge-status badge-status-<?php echo $statusConfig['class'] ?>">
                                    <i class="fas fa-<?php echo $statusConfig['icon'] ?> me-1"></i><?php echo h($statusConfig['label']) ?>
                                </span>
                            </td>
                            
                            <!-- Priority -->
                            <td>
                                <?php 
                                $priorityConfig = match($case->priority) {
                                    'urgent' => ['class' => 'danger', 'icon' => 'exclamation-triangle', 'label' => $case->getPriorityLabel()],
                                    'high' => ['class' => 'danger', 'icon' => 'arrow-up', 'label' => $case->getPriorityLabel()],
                                    'medium' => ['class' => 'warning', 'icon' => 'minus', 'label' => $case->getPriorityLabel()],
                                    'low' => ['class' => 'success', 'icon' => 'arrow-down', 'label' => $case->getPriorityLabel()],
                                    default => ['class' => 'secondary', 'icon' => 'minus', 'label' => $case->getPriorityLabel()]
                                };
                                ?>
                                <span class="badge badge-priority badge-priority-<?php echo $priorityConfig['class'] ?>">
                                    <i class="fas fa-<?php echo $priorityConfig['icon'] ?> me-1"></i><?php echo h($priorityConfig['label']) ?>
                                </span>
                            </td>
                            
                            <!-- Case Date -->
                            <td>
                                <?php if ($case->date): ?>
                                    <div class="text-dark">
                                        <i class="fas fa-calendar me-1 text-muted"></i><?php echo $case->date->format('M d, Y') ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Not set</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Assigned To -->
                            <td>
                                <?php if ($case->current_user): ?>
                                    <div class="assigned-info">
                                        <div class="fw-semibold">
                                            <i class="fas fa-user-circle me-1 text-primary"></i>
                                            <?php if ($case->current_user_id === $user->id): ?>
                                                <span class="text-success">You</span>
                                            <?php else: ?>
                                                <?php echo h($case->current_user->first_name . ' ' . $case->current_user->last_name) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            <i class="fas fa-envelope me-1"></i><?php echo h($case->current_user->email) ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">
                                        <i class="fas fa-user-slash me-1"></i>Unassigned
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Created -->
                            <td>
                                <div class="text-muted small">
                                    <div>
                                        <i class="fas fa-user me-1"></i>
                                        <?php if ($case->user_id === $user->id): ?>
                                            <span class="text-primary">You</span>
                                        <?php else: ?>
                                            <?php echo h($case->user->first_name . ' ' . $case->user->last_name) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div><i class="fas fa-clock me-1"></i><?php echo $case->created->format('M d, Y') ?></div>
                                </div>
                            </td>
                            
                            <!-- Actions -->
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        ['action' => 'view', $case->id],
                                        [
                                            'escape' => false,
                                            'class' => 'btn btn-outline-info',
                                            'title' => 'View Case',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ); ?>
                                    <?php 
                                    // Check technician's role-specific status for permissions
                                    $technicianStatus = $case->technician_status ?? 'draft';
                                    if (!in_array($technicianStatus, ['completed', 'cancelled'])): 
                                    ?>
                                        <?php echo $this->Html->link(
                                            '<i class="fas fa-edit"></i>',
                                            ['action' => 'edit', $case->id],
                                            [
                                                'escape' => false,
                                                'class' => 'btn btn-outline-primary',
                                                'title' => 'Edit Case',
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ); ?>
                                    <?php endif; ?>
                                    <?php if (!in_array($technicianStatus, ['completed', 'cancelled'])): ?>
                                        <?php echo $this->Html->link(
                                            '<i class="fas fa-user-plus"></i>',
                                            ['action' => 'assign', $case->id],
                                            [
                                                'escape' => false,
                                                'class' => 'btn btn-outline-success',
                                                'title' => 'Assign Case',
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="card-footer bg-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">
                        <?php echo $this->Paginator->counter('Showing {{start}} to {{end}} of {{count}} cases'); ?>
                    </small>
                </div>
                <div class="col-md-6">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-end">
                            <?php echo $this->Paginator->first('<i class="fas fa-angle-double-left"></i>', ['escape' => false]); ?>
                            <?php echo $this->Paginator->prev('<i class="fas fa-angle-left"></i> Prev', ['escape' => false]); ?>
                            <?php echo $this->Paginator->numbers(); ?>
                            <?php echo $this->Paginator->next('Next <i class="fas fa-angle-right"></i>', ['escape' => false]); ?>
                            <?php echo $this->Paginator->last('<i class="fas fa-angle-double-right"></i>', ['escape' => false]); ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Empty State -->
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state">
                <i class="fas fa-folder-open fa-4x text-muted mb-4" style="opacity: 0.3;"></i>
                <h5 class="text-muted mb-2">No cases found</h5>
                <p class="text-muted mb-4">
                    <?php if ($search || $status !== 'all' || $priority !== 'all'): ?>
                        Try adjusting your filters or <?php echo $this->Html->link('clear all filters', ['action' => 'index'], ['class' => 'text-primary']); ?>.
                    <?php else: ?>
                        Start by creating your first case.
                    <?php endif; ?>
                </p>
                <?php if (!$search && $status === 'all' && $priority === 'all'): ?>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-plus me-2"></i>Create First Case',
                        ['action' => 'add'],
                        ['class' => 'btn btn-primary', 'escape' => false]
                    ); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Case ID Icon */
.case-id-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    color: white;
    font-size: 14px;
}

/* Status Badge Styles */
.badge-status {
    padding: 6px 12px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-status-secondary {
    background: linear-gradient(135deg, #858796 0%, #60616f 100%);
    color: white;
}

.badge-status-info {
    background: linear-gradient(135deg, #36b9cc 0%, #2c9faf 100%);
    color: white;
}

.badge-status-warning {
    background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
    color: white;
}

.badge-status-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    color: white;
}

.badge-status-success {
    background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
    color: white;
}

.badge-status-danger {
    background: linear-gradient(135deg, #e74a3b 0%, #c92a1e 100%);
    color: white;
}

/* Priority Badge Styles */
.badge-priority {
    padding: 6px 12px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 6px;
}

.badge-priority-danger {
    background: linear-gradient(135deg, #e74a3b 0%, #c92a1e 100%);
    color: white;
}

.badge-priority-warning {
    background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
    color: white;
}

.badge-priority-success {
    background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
    color: white;
}

/* Table Row Hover Effect */
.case-row {
    transition: all 0.2s ease;
}

.case-row:hover {
    background-color: #f8f9fc;
    transform: translateX(2px);
}

.case-row:hover .case-id-icon {
    transform: scale(1.05);
}

/* Patient Info */
.patient-info .fw-semibold {
    color: #5a5c69;
}

.patient-info .text-muted {
    font-size: 0.8rem;
}

/* Assigned Info */
.assigned-info .fw-semibold {
    color: #5a5c69;
}

/* Button Group Styling */
.btn-group-sm .btn {
    padding: 0.375rem 0.75rem;
}

.btn-outline-info:hover {
    background-color: #36b9cc;
    border-color: #36b9cc;
}

.btn-outline-primary:hover {
    background-color: #4e73df;
    border-color: #4e73df;
}

.btn-outline-success:hover {
    background-color: #1cc88a;
    border-color: #1cc88a;
}

/* Pagination Styling */
.pagination-sm .page-link {
    border-radius: 6px;
    margin: 0 2px;
}

.pagination-sm .page-item.active .page-link {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    border-color: #4e73df;
}

/* Empty State */
.empty-state {
    padding: 2rem;
}

/* Card Shadow */
.card.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

