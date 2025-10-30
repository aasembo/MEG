<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Patient> $patients
 */

$this->assign('title', 'Patient Management');
?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-user-injured me-2"></i>Patient Management
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-hospital me-2"></i><?php echo h($currentHospital->name) ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-user-plus me-2"></i>Add New Patient',
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
                    <i class="fas fa-filter me-2 text-primary"></i>Filter Patients
                </h5>
                <?php if ($search || $status !== 'all'): ?>
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
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1 text-success"></i>Search
                    </label>
                    <?php echo $this->Form->control('search', [
                        'type' => 'text',
                        'value' => $search,
                        'label' => false,
                        'class' => 'form-control',
                        'placeholder' => 'Name, username, email, or MRN...'
                    ]); ?>
                </div>
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
                echo '<i class="fas fa-users me-2"></i>' . $totalCount . ' ' . ($totalCount == 1 ? 'Patient' : 'Patients') . ' Found';
                ?>
            </span>
        </div>
        <?php if (!empty($patients->toArray())): ?>
        <div>
            <small class="text-muted fw-semibold">
                <i class="fas fa-list me-1"></i><?php echo $this->Paginator->counter(__('Page {{page}} of {{pages}}')); ?>
            </small>
        </div>
        <?php endif; ?>
    </div>

    <!-- Patients Table -->
    <?php if (!empty($patients->toArray())): ?>
    <div class="card border-0 shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4 fw-semibold text-uppercase small" style="width: 80px;">
                                ID
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 250px;">
                                <?php echo $this->Paginator->sort('Users.last_name', 'Patient', [
                                    'class' => 'text-decoration-none text-dark',
                                    '?' => compact('status', 'search')
                                ]); ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 180px;">
                                <?php echo $this->Paginator->sort('Users.username', 'Username', [
                                    'class' => 'text-decoration-none text-dark',
                                    '?' => compact('status', 'search')
                                ]); ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small">
                                <?php echo $this->Paginator->sort('Users.email', 'Contact', [
                                    'class' => 'text-decoration-none text-dark',
                                    '?' => compact('status', 'search')
                                ]); ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 100px;">
                                Gender
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 120px;">
                                <?php echo $this->Paginator->sort('Users.status', 'Status', [
                                    'class' => 'text-decoration-none text-dark',
                                    '?' => compact('status', 'search')
                                ]); ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 150px;">
                                <?php echo $this->Paginator->sort('Patients.created', 'Registered', [
                                    'class' => 'text-decoration-none text-dark',
                                    '?' => compact('status', 'search')
                                ]); ?>
                            </th>
                            <th class="border-0 text-center fw-semibold text-uppercase small" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                        <tr>
                            <!-- Patient Icon -->
                            <td class="ps-4">
                                <?php 
                                $genderIcon = match($patient->gender ?? '') {
                                    'M' => 'fa-mars',
                                    'F' => 'fa-venus',
                                    'O' => 'fa-transgender',
                                    default => 'fa-user'
                                };
                                $genderColorClass = match($patient->gender ?? '') {
                                    'M' => 'bg-primary',
                                    'F' => 'bg-danger',
                                    'O' => 'bg-warning',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <div class="<?php echo $genderColorClass; ?> text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="fas <?php echo $genderIcon; ?>"></i>
                                </div>
                            </td>
                            
                            <!-- Patient Name & MRN -->
                            <td>
                                <div>
                                    <?php echo $this->Html->link(
                                        '<span class="fw-semibold">' . h($patient->user->first_name . ' ' . $patient->user->last_name) . '</span>',
                                        ['action' => 'view', $patient->id],
                                        ['escape' => false, 'class' => 'text-decoration-none text-primary']
                                    ); ?>
                                    <?php if ($patient->medical_record_number): ?>
                                        <div class="text-muted small">
                                            <i class="fas fa-file-medical me-1"></i>MRN: <?php echo h($patient->medical_record_number) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <!-- Username -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle me-2 text-primary"></i>
                                    <span class="fw-semibold text-dark"><?php echo h($patient->user->username) ?></span>
                                </div>
                            </td>
                            
                            <!-- Contact -->
                            <td>
                                <div>
                                    <div class="text-dark small">
                                        <i class="fas fa-envelope me-1 text-muted"></i><?php echo h($patient->user->email) ?>
                                    </div>
                                    <?php if ($patient->phone): ?>
                                        <div class="text-muted small">
                                            <i class="fas fa-phone me-1"></i><?php echo h($patient->phone) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($patient->dob): ?>
                                        <div class="text-muted small">
                                            <i class="fas fa-birthday-cake me-1"></i><?php echo $patient->dob->format('M d, Y') ?> 
                                            <span>(<?php echo $patient->dob->age ?> yrs)</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <!-- Gender -->
                            <td>
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
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Status -->
                            <td>
                                <?php
                                $statusConfig = match($patient->user->status) {
                                    'active' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Active'],
                                    'inactive' => ['class' => 'secondary', 'icon' => 'minus-circle', 'text' => 'Inactive'],
                                    'suspended' => ['class' => 'danger', 'icon' => 'ban', 'text' => 'Suspended'],
                                    default => ['class' => 'secondary', 'icon' => 'circle', 'text' => ucfirst($patient->user->status)]
                                };
                                $statusBadge = 'badge rounded-pill bg-' . $statusConfig['class'];
                                $statusBadge .= ($statusConfig['class'] === 'warning') ? ' text-dark' : ' text-white';
                                ?>
                                <span class="<?php echo $statusBadge; ?>">
                                    <i class="fas fa-<?php echo $statusConfig['icon'] ?> me-1"></i><?php echo $statusConfig['text'] ?>
                                </span>
                            </td>
                            
                            <!-- Registered -->
                            <td>
                                <div class="text-muted small">
                                    <div><i class="fas fa-calendar me-1"></i><?php echo $patient->created->format('M d, Y') ?></div>
                                    <div><i class="fas fa-clock me-1"></i><?php echo $patient->created->timeAgoInWords() ?></div>
                                </div>
                            </td>
                            
                            <!-- Actions -->
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        ['action' => 'view', $patient->id],
                                        [
                                            'escape' => false,
                                            'class' => 'btn btn-outline-info',
                                            'title' => 'View Patient',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ); ?>
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-edit"></i>',
                                        ['action' => 'edit', $patient->id],
                                        [
                                            'escape' => false,
                                            'class' => 'btn btn-outline-primary',
                                            'title' => 'Edit Patient',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ); ?>
                                    <?php if ($patient->user->status === 'active'): ?>
                                        <?php echo $this->Form->postLink(
                                            '<i class="fas fa-user-times"></i>',
                                            ['action' => 'delete', $patient->id],
                                            [
                                                'escape' => false,
                                                'class' => 'btn btn-outline-danger',
                                                'title' => 'Deactivate Patient',
                                                'data-bs-toggle' => 'tooltip',
                                                'confirm' => __('Are you sure you want to deactivate {0}?', $patient->user->first_name . ' ' . $patient->user->last_name)
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
        <div class="card-footer bg-light">
            <div class="row align-items-center g-3">
                <div class="col-md-6 col-12">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo $this->Paginator->counter('Showing {{start}} to {{end}} of {{count}} patients'); ?>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <nav aria-label="Patients pagination">
                        <ul class="pagination pagination-sm mb-0 justify-content-md-end justify-content-center">
                            <?php 
                            // First page button
                            echo $this->Paginator->first(
                                '<i class="fas fa-angle-double-left"></i>', 
                                [
                                    'escape' => false,
                                    'class' => 'page-link',
                                    'url' => ['?' => compact('status', 'search')],
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
                                    'url' => ['?' => compact('status', 'search')],
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
                                'url' => ['?' => compact('status', 'search')],
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
                                    'url' => ['?' => compact('status', 'search')],
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
                                    'url' => ['?' => compact('status', 'search')],
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
            <i class="fas fa-user-injured fa-4x text-secondary mb-4 opacity-25"></i>
            <h5 class="text-muted mb-2">No patients found</h5>
            <p class="text-muted mb-4">
                <?php if ($search || $status !== 'all'): ?>
                    No patients match your current filters. Try adjusting your search criteria.
                <?php else: ?>
                    Get started by registering your first patient.
                <?php endif; ?>
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <?php if ($search || $status !== 'all'): ?>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times-circle me-2"></i>Clear Filters',
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-secondary', 'escape' => false]
                    ); ?>
                <?php endif; ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-user-plus me-2"></i>Add New Patient',
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
