<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MedicalCase> $cases
 */

$this->assign('title', 'Case Management');
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-briefcase-medical me-2"></i>Case Management
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-hospital me-2"></i><?php echo h($currentHospital->name) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-plus-circle me-2"></i>Create New Case',
                        ['action' => 'add'],
                        ['class' => 'btn btn-light btn-lg', 'escape' => false]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-filter me-2 text-primary"></i>Filter Cases
                </h5>
                <?php if ($search || $status !== 'all' || $priority !== 'all'): ?>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times-circle me-1"></i>Clear All Filters',
                        ['action' => 'index'],
                        ['class' => 'btn btn-sm btn-outline-secondary rounded-pill', 'escape' => false]
                    ); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body bg-white">
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
            <span class="badge bg-primary text-white fs-6 px-3 py-2">
                <?php 
                $totalCount = $this->Paginator->counter('{{count}}');
                echo '<i class="fas fa-file-medical me-2"></i>' . $totalCount . ' ' . ($totalCount == 1 ? 'Case' : 'Cases') . ' Found';
                ?>
            </span>
        </div>
        <?php if (!empty($cases->toArray())): ?>
        <div>
            <small class="text-muted fw-semibold">
                <i class="fas fa-list me-1"></i><?php echo $this->Paginator->counter(__('Page {{page}} of {{pages}}')); ?>
            </small>
        </div>
        <?php endif; ?>
    </div>

    <!-- Cases Table -->
    <?php if (!empty($cases->toArray())): ?>
    <div class="card border-0 shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4 fw-semibold text-uppercase small" style="width: 80px;">
                                <?php echo $this->Paginator->sort('id', 'Case ID', [
                                    'class' => 'text-decoration-none text-dark',
                                    '?' => compact('status', 'priority', 'search')
                                ]); ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 200px;">
                                <?php echo $this->Paginator->sort('patient_id', 'Patient', [
                                    'class' => 'text-decoration-none text-dark',
                                    '?' => compact('status', 'priority', 'search')
                                ]); ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 120px;">
                                <?php echo $this->Paginator->sort('status', 'Status', [
                                    'class' => 'text-decoration-none text-dark',
                                    '?' => compact('status', 'priority', 'search')
                                ]); ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 100px;">
                                <?php echo $this->Paginator->sort('priority', 'Priority', [
                                    'class' => 'text-decoration-none text-dark',
                                    '?' => compact('status', 'priority', 'search')
                                ]); ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 150px;">
                                <?php echo $this->Paginator->sort('date', 'Case Date', [
                                    'class' => 'text-decoration-none text-dark',
                                    '?' => compact('status', 'priority', 'search')
                                ]); ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small">
                                <?php echo $this->Paginator->sort('current_user_id', 'Assigned To', [
                                    'class' => 'text-decoration-none text-dark',
                                    '?' => compact('status', 'priority', 'search')
                                ]); ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 150px;">
                                <?php echo $this->Paginator->sort('created', 'Created', [
                                    'class' => 'text-decoration-none text-dark',
                                    '?' => compact('status', 'priority', 'search')
                                ]); ?>
                            </th>
                            <th class="border-0 text-center fw-semibold text-uppercase small" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cases as $case): ?>
                        <tr>
                            <!-- Case ID -->
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded d-inline-flex align-items-center justify-content-center me-2 p-2" style="width: 36px; height: 36px;">
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
                                    <div>
                                        <div class="fw-semibold text-dark">
                                            <?php echo $this->PatientMask->displayField($case->patient_user, 'name', ['icon' => false]); ?>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-id-card me-1"></i>MRN: <?php echo $this->PatientMask->displayMrn($case->patient_user); ?>
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
                                // Get main case status (only: in_progress, completed, cancelled)
                                $caseStatus = $case->status ?? 'in_progress';
                                $statusConfig = match($caseStatus) {
                                    'in_progress' => ['class' => 'warning', 'icon' => 'spinner', 'label' => 'In Progress'],
                                    'completed' => ['class' => 'success', 'icon' => 'check-circle', 'label' => 'Completed'],
                                    'cancelled' => ['class' => 'danger', 'icon' => 'times-circle', 'label' => 'Cancelled'],
                                    default => ['class' => 'secondary', 'icon' => 'circle', 'label' => ucfirst(str_replace('_', ' ', $caseStatus))]
                                };
                                ?>
                                <?php
                                $badgeClass = 'badge rounded-pill bg-' . $statusConfig['class'];
                                $badgeClass .= ($statusConfig['class'] === 'warning') ? ' text-dark' : ' text-white';
                                ?>
                                <span class="<?php echo $badgeClass; ?>">
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
                                <?php
                                $priorityBadge = 'badge rounded-pill bg-' . $priorityConfig['class'];
                                $priorityBadge .= ($priorityConfig['class'] === 'warning') ? ' text-dark' : ' text-white';
                                ?>
                                <span class="<?php echo $priorityBadge; ?>">
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
                                    <div>
                                        <div class="fw-semibold text-dark">
                                            <i class="fas fa-user-circle me-1 text-primary"></i>
                                            <?php if ($case->current_user_id === $user->id): ?>
                                                <span class="text-success">You</span>
                                            <?php else: ?>
                                                <?php echo h($case->current_user->first_name . ' ' . $case->current_user->last_name) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-muted small">
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
                                    // Check main case status for permissions (not role-based)
                                    $mainStatus = $case->status ?? 'in_progress';
                                    if (!in_array($mainStatus, ['completed', 'cancelled'])): 
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
                                    <?php if (!in_array($mainStatus, ['completed', 'cancelled'])): ?>
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
        <div class="card-footer bg-light border-top">
            <div class="row align-items-center g-3">
                <div class="col-md-6 col-12">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo $this->Paginator->counter('Showing {{start}} to {{end}} of {{count}} cases'); ?>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <nav aria-label="Cases pagination">
                        <ul class="pagination pagination-sm mb-0 justify-content-md-end justify-content-center">
                            <?php 
                            // First page button
                            echo $this->Paginator->first(
                                '<i class="fas fa-angle-double-left"></i>', 
                                [
                                    'escape' => false,
                                    'class' => 'page-link',
                                    'url' => ['?' => compact('status', 'priority', 'search')],
                                    'templates' => [
                                        'first' => '<li class="page-item">{{text}}</li>',
                                        'firstDisabled' => '<li class="page-item disabled"><span class="page-link">{{text}}</span></li>'
                                    ]
                                ]
                            );
                            
                            // Previous button
                            echo $this->Paginator->prev(
                                '<i class="fas fa-chevron-left"></i>', 
                                [
                                    'escape' => false,
                                    'class' => 'page-link',
                                    'url' => ['?' => compact('status', 'priority', 'search')],
                                    'templates' => [
                                        'prevActive' => '<li class="page-item">{{text}}</li>',
                                        'prevDisabled' => '<li class="page-item disabled"><span class="page-link">{{text}}</span></li>'
                                    ]
                                ]
                            );
                            
                            // Page numbers
                            echo $this->Paginator->numbers([
                                'modulus' => 3,
                                'class' => 'page-link',
                                'url' => ['?' => compact('status', 'priority', 'search')],
                                'before' => '',
                                'after' => '',
                                'templates' => [
                                    'number' => '<li class="page-item"><a class="page-link" href="{{url}}">{{text}}</a></li>',
                                    'current' => '<li class="page-item active" aria-current="page"><span class="page-link">{{text}}</span></li>',
                                    'ellipsis' => '<li class="page-item disabled"><span class="page-link">...</span></li>'
                                ]
                            ]);
                            
                            // Next button
                            echo $this->Paginator->next(
                                '<i class="fas fa-chevron-right"></i>', 
                                [
                                    'escape' => false,
                                    'class' => 'page-link',
                                    'url' => ['?' => compact('status', 'priority', 'search')],
                                    'templates' => [
                                        'nextActive' => '<li class="page-item">{{text}}</li>',
                                        'nextDisabled' => '<li class="page-item disabled"><span class="page-link">{{text}}</span></li>'
                                    ]
                                ]
                            );
                            
                            // Last page button
                            echo $this->Paginator->last(
                                '<i class="fas fa-angle-double-right"></i>', 
                                [
                                    'escape' => false,
                                    'class' => 'page-link',
                                    'url' => ['?' => compact('status', 'priority', 'search')],
                                    'templates' => [
                                        'last' => '<li class="page-item">{{text}}</li>',
                                        'lastDisabled' => '<li class="page-item disabled"><span class="page-link">{{text}}</span></li>'
                                    ]
                                ]
                            );
                            ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Empty State -->
    <div class="card shadow">
        <div class="card-body text-center py-5">
            <i class="fas fa-folder-open fa-4x text-secondary mb-4 opacity-25"></i>
            <h5 class="text-muted mb-2">No cases found</h5>
            <p class="text-muted mb-4">
                <?php if ($search || $status !== 'all' || $priority !== 'all'): ?>
                    Try adjusting your filters or clearing them to see all cases.
                <?php else: ?>
                    Get started by creating your first case with patient details.
                <?php endif; ?>
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <?php if ($search || $status !== 'all' || $priority !== 'all'): ?>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times-circle me-2"></i>Clear Filters',
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-secondary', 'escape' => false]
                    ); ?>
                <?php endif; ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-plus-circle me-2"></i>Create New Case',
                    ['action' => 'add'],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ); ?>
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

