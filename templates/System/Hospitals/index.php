<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Hospital> $hospitals
 */
?>
<?php $this->assign('title', 'Hospital Management'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-hospital me-2 text-primary"></i>Hospital Management
        </h1>
        <p class="text-muted mb-0">Manage hospital accounts and settings</p>
    </div>
    <div>
        <?php echo $this->Html->link(
            '<i class="fas fa-plus me-2"></i>Add Hospital',
            ['action' => 'add'],
            ['class' => 'btn btn-primary', 'escape' => false]
        ) ?>
    </div>
</div>

<!-- Filter Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <?php echo $this->Html->link('', ['action' => 'index'], ['class' => 'text-decoration-none']) ?>
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Total Hospitals</h6>
                        <h3 class="mb-0"><?php echo $totalCount ?></h3>
                    </div>
                    <div class="fs-1 opacity-50">
                        <i class="fas fa-hospital"></i>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <?php echo $this->Html->link('', ['action' => 'index', '?' => ['status' => 'active']], ['class' => 'text-decoration-none']) ?>
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Active</h6>
                        <h3 class="mb-0"><?php echo $activeCount ?></h3>
                    </div>
                    <div class="fs-1 opacity-50">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <?php echo $this->Html->link('', ['action' => 'index', '?' => ['status' => 'inactive']], ['class' => 'text-decoration-none']) ?>
        <div class="card border-0 shadow-sm bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Inactive</h6>
                        <h3 class="mb-0"><?php echo $inactiveCount ?></h3>
                    </div>
                    <div class="fs-1 opacity-50">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2 text-white-50">Last Updated</h6>
                        <small class="mb-0">Just now</small>
                    </div>
                    <div class="fs-1 opacity-50">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <?php echo $this->Form->create(null, ['type' => 'get', 'class' => 'row g-3']) ?>
            <div class="col-md-4">
                <?php echo $this->Form->control('search', [
                    'type' => 'text',
                    'class' => 'form-control',
                    'placeholder' => 'Search hospitals, subdomains...',
                    'label' => false,
                    'value' => $this->request->getQuery('search'),
                    'div' => false,
                    'templates' => [
                        'inputContainer' => '<div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            {{content}}
                        </div>',
                        'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>'
                    ]
                ]) ?>
            </div>
            <div class="col-md-3">
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
                    'div' => false,
                    'templates' => [
                        'inputContainer' => '{{content}}',
                        'select' => '<select name="{{name}}" {{attrs}}>{{content}}</select>'
                    ]
                ]) ?>
            </div>
            <div class="col-md-5">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times me-2"></i>Clear',
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-secondary', 'escape' => false]
                    ) ?>
                    <button type="button" class="btn btn-outline-primary ms-auto" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt me-2"></i>Refresh
                    </button>
                </div>
            </div>
        <?php echo $this->Form->end() ?>
    </div>
</div>

<!-- Hospitals Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fas fa-list me-2"></i>Hospitals List
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if (!$hospitals->isEmpty()): ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col"><?php echo $this->Paginator->sort('name', 'Hospital Name') ?></th>
                            <th scope="col"><?php echo $this->Paginator->sort('subdomain', 'Subdomain') ?></th>
                            <th scope="col"><?php echo $this->Paginator->sort('status', 'Status') ?></th>
                            <th scope="col">Users</th>
                            <th scope="col"><?php echo $this->Paginator->sort('created', 'Created') ?></th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hospitals as $hospital): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-hospital"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?php echo h($hospital->name) ?></h6>
                                        <small class="text-muted">ID: <?php echo $hospital->id ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?php echo h($hospital->subdomain) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($hospital->status === 'active'): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Active
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-pause-circle me-1"></i>Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $userCount = count($hospital->users ?? []);
                                if ($userCount > 0): 
                                ?>
                                    <?php echo $this->Html->link(
                                        '<span class="badge bg-info">' . $userCount . ' users</span>',
                                        ['controller' => 'Users', 'action' => 'index', '?' => ['hospital_id' => $hospital->id]],
                                        ['escape' => false, 'title' => 'View users in this hospital']
                                    ) ?>
                                <?php else: ?>
                                    <span class="text-muted">No users</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span title="<?php echo $hospital->created->format('Y-m-d H:i:s') ?>">
                                    <?php echo $hospital->created->format('M j, Y') ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        ['action' => 'view', $hospital->id],
                                        [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'escape' => false,
                                            'title' => 'View Details'
                                        ]
                                    ) ?>
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-edit"></i>',
                                        ['action' => 'edit', $hospital->id],
                                        [
                                            'class' => 'btn btn-sm btn-outline-secondary',
                                            'escape' => false,
                                            'title' => 'Edit Hospital'
                                        ]
                                    ) ?>
                                    <?php echo $this->Form->postLink(
                                        ($hospital->status === 'active') ? '<i class="fas fa-pause"></i>' : '<i class="fas fa-play"></i>',
                                        ['action' => 'toggleStatus', $hospital->id],
                                        [
                                            'class' => 'btn btn-sm ' . (($hospital->status === 'active') ? 'btn-outline-warning' : 'btn-outline-success'),
                                            'escape' => false,
                                            'title' => ($hospital->status === 'active') ? 'Deactivate' : 'Activate',
                                            'confirm' => 'Are you sure you want to ' . (($hospital->status === 'active') ? 'deactivate' : 'activate') . ' this hospital?'
                                        ]
                                    ) ?>
                                    <?php if (count($hospital->users ?? []) === 0): ?>
                                        <?php echo $this->Form->postLink(
                                            '<i class="fas fa-trash"></i>',
                                            ['action' => 'delete', $hospital->id],
                                            [
                                                'class' => 'btn btn-sm btn-outline-danger',
                                                'escape' => false,
                                                'title' => 'Delete Hospital',
                                                'confirm' => 'Are you sure you want to delete this hospital? This action cannot be undone.'
                                            ]
                                        ) ?>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-outline-danger" disabled title="Cannot delete hospital with users">
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
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-hospital text-muted mb-3" style="font-size: 4rem;"></i>
                <h5 class="text-muted">No hospitals found</h5>
                <p class="text-muted">
                    <?php if ($this->request->getQuery('search') || $this->request->getQuery('status')): ?>
                        Try adjusting your search criteria or 
                        <?php echo $this->Html->link('clear filters', ['action' => 'index'], ['class' => 'text-primary']) ?>
                    <?php else: ?>
                        Get started by adding your first hospital
                    <?php endif; ?>
                </p>
                <?php if (!$this->request->getQuery('search') && !$this->request->getQuery('status')): ?>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-plus me-2"></i>Add First Hospital',
                        ['action' => 'add'],
                        ['class' => 'btn btn-primary', 'escape' => false]
                    ) ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if (!$hospitals->isEmpty()): ?>
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    <?php echo $this->Paginator->counter(__('Showing {{start}} to {{end}} of {{count}} hospitals')) ?>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php echo $this->Paginator->first('<i class="fas fa-angle-double-left"></i>', ['escape' => false]) ?>
                        <?php echo $this->Paginator->prev('<i class="fas fa-angle-left"></i>', ['escape' => false]) ?>
                        <?php echo $this->Paginator->numbers() ?>
                        <?php echo $this->Paginator->next('<i class="fas fa-angle-right"></i>', ['escape' => false]) ?>
                        <?php echo $this->Paginator->last('<i class="fas fa-angle-double-right"></i>', ['escape' => false]) ?>
                    </ul>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>