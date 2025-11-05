<?php $this->assign('title', 'Users Management'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-users me-2"></i>Users Management
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-shield-alt me-2"></i>Secure Healthcare Management Platform
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-user-plus me-2"></i>Add User',
                        ['action' => 'add'],
                        ['class' => 'btn btn-warning btn-lg text-dark fw-bold', 'escape' => false]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users text-warning fs-1 mb-3"></i>
                    <h3 class="text-warning mb-2"><?php echo $totalCount ?></h3>
                    <p class="text-muted mb-0">Total Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-check text-success fs-1 mb-3"></i>
                    <h3 class="text-success mb-2"><?php echo $activeCount ?></h3>
                    <p class="text-muted mb-0">Active Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-times text-danger fs-1 mb-3"></i>
                    <h3 class="text-danger mb-2"><?php echo $inactiveCount ?></h3>
                    <p class="text-muted mb-0">Inactive Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-clock text-info fs-1 mb-3"></i>
                    <h3 class="text-info mb-2"><?php echo isset($recentCount) ? $recentCount : '0' ?></h3>
                    <p class="text-muted mb-0">Recent (7 days)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-filter me-2 text-warning"></i>Search & Filter Users
                </h5>
                <?php if ($this->request->getQuery('search') || $this->request->getQuery('status') || $this->request->getQuery('role_id')): ?>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times-circle me-1"></i>Clear All Filters',
                        ['action' => 'index'],
                        ['class' => 'btn btn-sm btn-outline-secondary rounded-pill', 'escape' => false]
                    ); ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body bg-white">
            <?php echo $this->Form->create(null, ['type' => 'get', 'class' => 'row g-3']) ?>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1 text-warning"></i>Search Users
                    </label>
                    <?php echo $this->Form->control('search', [
                        'type' => 'text',
                        'class' => 'form-control',
                        'placeholder' => 'Search by name, email, username...',
                        'label' => false,
                        'value' => $this->request->getQuery('search'),
                        'div' => false
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-info-circle me-1 text-primary"></i>Status
                    </label>
                    <?php echo $this->Form->control('status', [
                        'type' => 'select',
                        'options' => [
                            '' => 'All Status',
                            'active' => 'Active',
                            'inactive' => 'Inactive'
                        ],
                        'class' => 'form-select',
                        'label' => false,
                        'value' => $this->request->getQuery('status'),
                        'div' => false
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-user-tag me-1 text-success"></i>Role
                    </label>
                    <?php echo $this->Form->control('role_id', [
                        'type' => 'select',
                        'options' => ['' => 'All Roles'] + $roles,
                        'class' => 'form-select',
                        'label' => false,
                        'value' => $this->request->getQuery('role_id'),
                        'div' => false
                    ]) ?>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <?php echo $this->Form->button(
                            '<i class="fas fa-search me-1"></i>' . __('Apply'),
                            ['type' => 'submit', 'class' => 'btn btn-warning text-dark fw-bold', 'escapeTitle' => false]
                        ); ?>
                    </div>
                </div>
            <?php echo $this->Form->end() ?>
        </div>
    </div>
    <!-- Results Summary -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                <i class="fas fa-users me-2"></i>
                <?php echo $this->Paginator->counter(__('{{count}} Users Found')) ?>
            </span>
            <?php if ($this->request->getQuery('search')): ?>
                <span class="badge bg-info me-2">
                    <i class="fas fa-search me-1"></i>
                    Search: "<?php echo h($this->request->getQuery('search')) ?>"
                </span>
            <?php endif; ?>
            <?php if ($this->request->getQuery('status')): ?>
                <span class="badge bg-secondary me-2">
                    <i class="fas fa-filter me-1"></i>
                    Status: <?php echo h(ucfirst($this->request->getQuery('status'))) ?>
                </span>
            <?php endif; ?>
            <?php if ($this->request->getQuery('role_id') && isset($roles[$this->request->getQuery('role_id')])): ?>
                <span class="badge bg-secondary me-2">
                    <i class="fas fa-user-tag me-1"></i>
                    Role: <?php echo h($roles[$this->request->getQuery('role_id')]) ?>
                </span>
            <?php endif; ?>
        </div>
        <?php if (!$users->isEmpty()): ?>
        <div>
            <small class="text-muted fw-semibold">
                <i class="fas fa-list me-1"></i><?php echo $this->Paginator->counter(__('Page {{page}} of {{pages}}')); ?>
            </small>
        </div>
        <?php endif; ?>
    </div>

    <!-- Users Table -->
    <?php if (!$users->isEmpty()): ?>
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
                                <?php echo $this->Paginator->sort('username', 'User', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 200px;">
                                <?php echo $this->Paginator->sort('email', 'Email', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 150px;">
                                <?php echo $this->Paginator->sort('role_id', 'Role', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 120px;">
                                <?php echo $this->Paginator->sort('status', 'Status', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 150px;">
                                <?php echo $this->Paginator->sort('created', 'Created', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 text-center fw-semibold text-uppercase small" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="bg-dark text-warning rounded d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <?php 
                                    $initials = '';
                                    if ($user->first_name) $initials .= strtoupper(substr($user->first_name, 0, 1));
                                    if ($user->last_name) $initials .= strtoupper(substr($user->last_name, 0, 1));
                                    if (!$initials) $initials = strtoupper(substr($user->username, 0, 2));
                                    echo h($initials);
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <?php echo $this->Html->link(
                                            '<span class="fw-semibold">' . h($user->first_name . ' ' . $user->last_name) . '</span>',
                                            ['action' => 'view', $user->id],
                                            ['escape' => false, 'class' => 'text-decoration-none text-dark']
                                        ); ?>
                                        <div class="text-muted small">
                                            <i class="fas fa-user-circle me-1"></i>@<?php echo h($user->username) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <div class="text-dark small">
                                        <i class="fas fa-envelope me-1 text-muted"></i><?php echo h($user->email) ?>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-clock me-1"></i>
                                        Last login: <?php echo $user->last_login ? $user->last_login->timeAgoInWords() : 'Never' ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($user->role): ?>
                                    <span class="badge rounded-pill <?php echo $this->Role->badgeClass($user->role->type); ?>">
                                        <i class="fas fa-<?php echo $user->role->type === 'administrator' ? 'user-shield' : 'user' ?> me-1"></i>
                                        <?php echo h($this->Role->label($user->role->type)); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-secondary">
                                        <i class="fas fa-question me-1"></i>No role
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user->status === 'active'): ?>
                                    <span class="badge rounded-pill bg-success text-white">
                                        <i class="fas fa-check-circle me-1"></i>Active
                                    </span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-danger text-white">
                                        <i class="fas fa-pause-circle me-1"></i>Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-muted small">
                                    <div><i class="fas fa-calendar me-1"></i><?php echo $user->created->format('M d, Y') ?></div>
                                    <div><i class="fas fa-clock me-1"></i><?php echo $user->created->timeAgoInWords() ?></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        ['action' => 'view', $user->id],
                                        [
                                            'escape' => false,
                                            'class' => 'btn btn-outline-info',
                                            'title' => 'View Details',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ); ?>
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-edit"></i>',
                                        ['action' => 'edit', $user->id],
                                        [
                                            'escape' => false,
                                            'class' => 'btn btn-outline-warning',
                                            'title' => 'Edit User',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ); ?>
                                    <?php
                                    $currentUser = $this->getRequest()->getAttribute('identity');
                                    $isCurrentUser = $currentUser && $currentUser->get('id') == $user->id;
                                    ?>
                                    <?php if (!$isCurrentUser): ?>
                                        <?php echo $this->Form->postLink(
                                            ($user->status === 'active') ? '<i class="fas fa-pause"></i>' : '<i class="fas fa-play"></i>',
                                            ['action' => 'toggleStatus', $user->id],
                                            [
                                                'escape' => false,
                                                'class' => 'btn ' . (($user->status === 'active') ? 'btn-outline-danger' : 'btn-outline-success'),
                                                'title' => ($user->status === 'active') ? 'Deactivate' : 'Activate',
                                                'data-bs-toggle' => 'tooltip',
                                                'confirm' => 'Are you sure you want to ' . (($user->status === 'active') ? 'deactivate' : 'activate') . ' this user?'
                                            ]
                                        ); ?>
                                        <?php echo $this->Form->postLink(
                                            '<i class="fas fa-trash"></i>',
                                            ['action' => 'delete', $user->id],
                                            [
                                                'escape' => false,
                                                'class' => 'btn btn-outline-danger',
                                                'title' => 'Delete User',
                                                'data-bs-toggle' => 'tooltip',
                                                'confirm' => 'Are you sure you want to delete this user? This action cannot be undone.'
                                            ]
                                        ); ?>
                                    <?php else: ?>
                                        <button class="btn btn-outline-secondary" disabled title="Cannot change own status" data-bs-toggle="tooltip">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary" disabled title="Cannot delete own account" data-bs-toggle="tooltip">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
                        <?php echo $this->Paginator->counter('Showing {{start}} to {{end}} of {{count}} users'); ?>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <nav aria-label="Users pagination">
                        <ul class="pagination pagination-sm mb-0 justify-content-md-end justify-content-center">
                            <?php 
                            // Build query parameters for pagination links
                            $queryParams = array_filter([
                                'search' => $this->request->getQuery('search'),
                                'status' => $this->request->getQuery('status'),
                                'role_id' => $this->request->getQuery('role_id')
                            ]);
                            
                            // First page button
                            echo $this->Paginator->first(
                                '<i class="fas fa-angle-double-left"></i>', 
                                [
                                    'escape' => false,
                                    'class' => 'page-link',
                                    'url' => ['?' => $queryParams],
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
                                    'url' => ['?' => $queryParams],
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
                                'url' => ['?' => $queryParams],
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
                                    'url' => ['?' => $queryParams],
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
                                    'url' => ['?' => $queryParams],
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
    <div class="card border-0 shadow">
        <div class="card-body text-center py-5">
            <i class="fas fa-users fa-4x text-muted mb-4 opacity-25"></i>
            <h5 class="text-dark mb-2">No users found</h5>
            <p class="text-muted mb-4">
                <?php if ($this->request->getQuery('search') || $this->request->getQuery('status') || $this->request->getQuery('role_id')): ?>
                    No users match your current filters. Try adjusting your search criteria.
                <?php else: ?>
                    Get started by adding your first user to the system.
                <?php endif; ?>
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <?php if ($this->request->getQuery('search') || $this->request->getQuery('status') || $this->request->getQuery('role_id')): ?>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times-circle me-2"></i>Clear Filters',
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-secondary', 'escape' => false]
                    ); ?>
                <?php endif; ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-user-plus me-2"></i>Add New User',
                    ['action' => 'add'],
                    ['class' => 'btn btn-warning text-dark fw-bold', 'escape' => false]
                ); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
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