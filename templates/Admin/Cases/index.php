<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MedicalCase> $cases
 */

$this->setLayout('admin');
$this->assign('title', 'Cases Overview');
?>

<div class="cases index content">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-1">
                <i class="fas fa-briefcase-medical me-2 text-primary"></i>Cases Overview
            </h1>
            <p class="text-muted mb-0">
                <i class="fas fa-hospital me-1"></i><?php echo h($currentHospital->name) ?>
                <span class="ms-3 text-info">
                    <i class="fas fa-info-circle me-1"></i>Read-only access - View all cases for your hospital
                </span>
            </p>
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
                    array('action' => 'index'),
                    array('class' => 'btn btn-sm btn-outline-secondary', 'escape' => false)
                ); ?>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php echo $this->Form->create(null, array('type' => 'get', 'class' => 'row g-3')); ?>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-info-circle me-1 text-primary"></i>Status
                    </label>
                    <?php echo $this->Form->control('status', array(
                        'type' => 'select',
                        'options' => $statusOptions,
                        'value' => $status,
                        'label' => false,
                        'class' => 'form-select',
                        'empty' => false
                    )); ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-exclamation-circle me-1 text-warning"></i>Priority
                    </label>
                    <?php echo $this->Form->control('priority', array(
                        'type' => 'select',
                        'options' => $priorityOptions,
                        'value' => $priority,
                        'label' => false,
                        'class' => 'form-select',
                        'empty' => false
                    )); ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1 text-success"></i>Search
                    </label>
                    <?php echo $this->Form->control('search', array(
                        'type' => 'text',
                        'value' => $search,
                        'label' => false,
                        'class' => 'form-control',
                        'placeholder' => 'Case ID, patient name, or technician name...'
                    )); ?>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <?php echo $this->Form->button(
                            '<i class="fas fa-search me-1"></i>' . __('Apply'),
                            array('type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false)
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
                            <th class="border-0" style="width: 150px;">
                                Technician
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
                            <th class="border-0 text-center" style="width: 100px;">Actions</th>
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
                                        array('action' => 'view', $case->id),
                                        array('escape' => false, 'class' => 'text-decoration-none text-primary')
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

                            <!-- Technician (Creator) -->
                            <td>
                                <?php if (isset($case->user)): ?>
                                    <div class="text-dark">
                                        <i class="fas fa-user-md me-1 text-muted"></i><?php echo h($case->user->first_name . ' ' . $case->user->last_name); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Status -->
                            <td>
                                <?php 
                                $statusClass = 'secondary';
                                $statusIcon = 'file';
                                $statusLabel = $case->getStatusLabel();
                                
                                if ($case->status === 'draft') {
                                    $statusClass = 'secondary';
                                    $statusIcon = 'file';
                                } elseif ($case->status === 'assigned') {
                                    $statusClass = 'info';
                                    $statusIcon = 'user-check';
                                } elseif ($case->status === 'in_progress') {
                                    $statusClass = 'warning';
                                    $statusIcon = 'spinner';
                                } elseif ($case->status === 'review') {
                                    $statusClass = 'primary';
                                    $statusIcon = 'search';
                                } elseif ($case->status === 'completed') {
                                    $statusClass = 'success';
                                    $statusIcon = 'check-circle';
                                } elseif ($case->status === 'cancelled') {
                                    $statusClass = 'danger';
                                    $statusIcon = 'times-circle';
                                }
                                ?>
                                <span class="badge badge-status badge-status-<?php echo $statusClass ?>">
                                    <i class="fas fa-<?php echo $statusIcon ?> me-1"></i><?php echo h($statusLabel) ?>
                                </span>
                            </td>
                            
                            <!-- Priority -->
                            <td>
                                <?php 
                                $priorityClass = 'secondary';
                                $priorityIcon = 'minus';
                                $priorityLabel = $case->getPriorityLabel();
                                
                                if ($case->priority === 'urgent') {
                                    $priorityClass = 'danger';
                                    $priorityIcon = 'exclamation-triangle';
                                } elseif ($case->priority === 'high') {
                                    $priorityClass = 'danger';
                                    $priorityIcon = 'arrow-up';
                                } elseif ($case->priority === 'medium') {
                                    $priorityClass = 'warning';
                                    $priorityIcon = 'minus';
                                } elseif ($case->priority === 'low') {
                                    $priorityClass = 'success';
                                    $priorityIcon = 'arrow-down';
                                }
                                ?>
                                <span class="badge badge-priority badge-priority-<?php echo $priorityClass ?>">
                                    <i class="fas fa-<?php echo $priorityIcon ?> me-1"></i><?php echo h($priorityLabel) ?>
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
                                <?php if (isset($case->current_user)): ?>
                                    <div class="assigned-info">
                                        <div class="fw-semibold">
                                            <i class="fas fa-user-circle me-1 text-primary"></i><?php echo h($case->current_user->first_name . ' ' . $case->current_user->last_name) ?>
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
                                    <div><i class="fas fa-clock me-1"></i><?php echo $case->created->format('M d, Y') ?></div>
                                    <div><i class="fas fa-clock me-1"></i><?php echo $case->created->format('h:i A') ?></div>
                                </div>
                            </td>
                            
                            <!-- Actions (View Only) -->
                            <td class="text-center">
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-eye"></i> View',
                                    array('action' => 'view', $case->id),
                                    array(
                                        'escape' => false,
                                        'class' => 'btn btn-sm btn-outline-info',
                                        'title' => 'View Case Details'
                                    )
                                ); ?>
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
                            <?php echo $this->Paginator->first('<i class="fas fa-angle-double-left"></i>', array('escape' => false)); ?>
                            <?php echo $this->Paginator->prev('<i class="fas fa-angle-left"></i> Prev', array('escape' => false)); ?>
                            <?php echo $this->Paginator->numbers(); ?>
                            <?php echo $this->Paginator->next('Next <i class="fas fa-angle-right"></i>', array('escape' => false)); ?>
                            <?php echo $this->Paginator->last('<i class="fas fa-angle-double-right"></i>', array('escape' => false)); ?>
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
            <div class="mb-4">
                <i class="fas fa-folder-open fa-4x text-muted"></i>
            </div>
            <h4 class="text-muted mb-3">No Cases Found</h4>
            <p class="text-muted">
                <?php if ($search || $status !== 'all' || $priority !== 'all'): ?>
                    No cases match your current filters. Try adjusting your search criteria.
                <?php else: ?>
                    There are no cases for your hospital yet.
                <?php endif; ?>
            </p>
            <?php if ($search || $status !== 'all' || $priority !== 'all'): ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-times me-2"></i>Clear Filters',
                    array('action' => 'index'),
                    array('class' => 'btn btn-outline-secondary', 'escape' => false)
                ); ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.case-id-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 6px;
    background-color: #e7f3ff;
    color: #0d6efd;
}

.badge-status {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 6px;
}

.badge-status-secondary {
    background-color: #6c757d;
    color: white;
}

.badge-status-info {
    background-color: #0dcaf0;
    color: #000;
}

.badge-status-warning {
    background-color: #ffc107;
    color: #000;
}

.badge-status-primary {
    background-color: #0d6efd;
    color: white;
}

.badge-status-success {
    background-color: #198754;
    color: white;
}

.badge-status-danger {
    background-color: #dc3545;
    color: white;
}

.badge-priority {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 6px;
}

.badge-priority-danger {
    background-color: #dc3545;
    color: white;
}

.badge-priority-warning {
    background-color: #ffc107;
    color: #000;
}

.badge-priority-success {
    background-color: #198754;
    color: white;
}

.badge-priority-secondary {
    background-color: #6c757d;
    color: white;
}

.case-row:hover {
    background-color: #f8f9fa;
}
</style>
