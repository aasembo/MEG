<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Sedation> $sedations
 */
?>
<?php $this->assign('title', 'Sedations Management'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-bed me-2"></i>Sedations Management
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-syringe me-2"></i>Anesthesia & Sedation Levels
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-plus me-2"></i>Add Sedation',
                        ['action' => 'add'],
                        ['class' => 'btn btn-warning btn-lg text-dark fw-bold', 'escape' => false]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light py-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-filter me-2 text-warning"></i>Search & Filter Sedations
                    </h5>
                </div>
                <div class="col-md-4 text-md-end">
                    <?php if ($this->request->getQuery('search') || $this->request->getQuery('sort')): ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-times-circle me-1"></i>Clear All Filters',
                            ['action' => 'index'],
                            ['class' => 'btn btn-sm btn-outline-secondary rounded-pill', 'escape' => false]
                        ); ?>
                    <?php else: ?>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <?php echo $sedations->count() ?> sedation(s) found
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body bg-white">
            <?php echo $this->Form->create(null, ['type' => 'get', 'class' => 'row g-3']) ?>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1 text-warning"></i>Search Sedations
                    </label>
                    <?php echo $this->Form->control('search', [
                        'type' => 'text',
                        'class' => 'form-control',
                        'placeholder' => 'Search by level, type, medications...',
                        'label' => false,
                        'value' => $this->request->getQuery('search'),
                        'div' => false
                    ]) ?>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-sort me-1 text-primary"></i>Sort By
                    </label>
                    <?php echo $this->Form->control('sort', [
                        'type' => 'select',
                        'options' => [
                            '' => 'Default Order',
                            'level' => 'Level (A-Z)',
                            'level DESC' => 'Level (Z-A)',
                            'created' => 'Oldest First',
                            'created DESC' => 'Newest First'
                        ],
                        'class' => 'form-select',
                        'label' => false,
                        'value' => $this->request->getQuery('sort'),
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
                <i class="fas fa-bed me-2"></i>
                <?php echo $this->Paginator->counter(__('{{count}} Sedations Found')) ?>
            </span>
            <?php if ($this->request->getQuery('search')): ?>
                <span class="badge bg-info me-2">
                    <i class="fas fa-search me-1"></i>
                    Search: "<?php echo h($this->request->getQuery('search')) ?>"
                </span>
            <?php endif; ?>
            <?php if ($this->request->getQuery('sort')): ?>
                <span class="badge bg-secondary me-2">
                    <i class="fas fa-sort me-1"></i>
                    <?php 
                    $sortLabels = [
                        'level' => 'Level (A-Z)',
                        'level DESC' => 'Level (Z-A)', 
                        'created' => 'Oldest First',
                        'created DESC' => 'Newest First'
                    ];
                    echo $sortLabels[$this->request->getQuery('sort')] ?? $this->request->getQuery('sort');
                    ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="text-muted small">
            <i class="fas fa-info-circle me-1"></i>
            Page <?php echo $this->Paginator->counter('{{page}} of {{pages}}') ?>
        </div>
    </div>
    <!-- Sedations List -->
    <?php if (!empty($sedations->toArray())): ?>
    <div class="card border-0 shadow">
        <div class="card-body bg-white p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4 fw-semibold text-uppercase small" style="width: 80px;">
                                #
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 200px;">
                                <?php echo $this->Paginator->sort('Sedations.level', 'Sedation Level', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 150px;">
                                <?php echo $this->Paginator->sort('Sedations.type', 'Type', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 120px;">
                                Risk Category
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 120px;">
                                Recovery Time
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 150px;">
                                <?php echo $this->Paginator->sort('Sedations.created', 'Created', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 text-center fw-semibold text-uppercase small" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sedations as $sedation): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="bg-dark text-warning rounded d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="fas fa-bed"></i>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <?php echo $this->Html->link(
                                            '<span class="fw-semibold">' . h($sedation->level) . '</span>',
                                            ['action' => 'view', $sedation->id],
                                            ['escape' => false, 'class' => 'text-decoration-none text-dark']
                                        ); ?>
                                        <div class="text-muted small">
                                            <i class="fas fa-hashtag me-1"></i>Sedation ID: <?php echo $sedation->id ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($sedation->type)): ?>
                                    <span class="text-dark"><?php echo h($sedation->type) ?></span>
                                <?php else: ?>
                                    <em class="text-muted">Not specified</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($sedation->risk_category): ?>
                                    <span class="badge bg-<?php echo $sedation->risk_category === 'high' ? 'danger' : ($sedation->risk_category === 'medium' ? 'warning text-dark' : 'success') ?>">
                                        <?php echo h(ucfirst($sedation->risk_category)) ?>
                                    </span>
                                <?php else: ?>
                                    <em class="text-muted">Not specified</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($sedation->recovery_time): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <i class="fas fa-clock me-1"></i><?php echo h($sedation->recovery_time) ?> min
                                    </span>
                                <?php else: ?>
                                    <em class="text-muted">Not specified</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-dark"><?php echo h($sedation->created->format('M j, Y')) ?></div>
                                <small class="text-muted"><?php echo h($sedation->created->format('g:i A')) ?></small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        ['action' => 'view', $sedation->id],
                                        [
                                            'class' => 'btn btn-outline-info btn-sm',
                                            'escape' => false,
                                            'title' => 'View Sedation',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ) ?>
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-edit"></i>',
                                        ['action' => 'edit', $sedation->id],
                                        [
                                            'class' => 'btn btn-outline-warning btn-sm',
                                            'escape' => false,
                                            'title' => 'Edit Sedation',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ) ?>
                                    <?php echo $this->Form->postLink(
                                        '<i class="fas fa-trash"></i>',
                                        ['action' => 'delete', $sedation->id],
                                        [
                                            'confirm' => __('Are you sure you want to delete "{0}"?', $sedation->level),
                                            'class' => 'btn btn-outline-danger btn-sm',
                                            'escape' => false,
                                            'title' => 'Delete Sedation',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-light">
            <div class="row align-items-center g-3">
                <div class="col-md-6 col-12">
                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1"></i>
                        <?php echo $this->Paginator->counter('Showing {{start}} to {{end}} of {{count}} sedations'); ?>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <nav aria-label="Sedations pagination">
                        <ul class="pagination pagination-sm mb-0 justify-content-md-end justify-content-center">
                            <?php 
                            // Build query parameters for pagination links
                            $queryParams = array_filter([
                                'search' => $this->request->getQuery('search'),
                                'sort' => $this->request->getQuery('sort')
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
            <div class="mb-4">
                <i class="fas fa-bed fa-4x text-warning mb-3"></i>
                <h4 class="text-dark mb-2">No Sedations Found</h4>
                <p class="text-muted mb-4">
                    <?php if ($this->request->getQuery('search')): ?>
                        No sedations match your search criteria. Try adjusting your search terms.
                    <?php else: ?>
                        You haven't created any sedation levels yet. Sedations define the comfort and consciousness levels for medical procedures.
                    <?php endif; ?>
                </p>
            </div>
            <div>
                <?php echo $this->Html->link(
                    '<i class="fas fa-plus me-2"></i>Add Your First Sedation', 
                    ['action' => 'add'], 
                    ['class' => 'btn btn-warning text-dark fw-bold btn-lg', 'escape' => false]
                ) ?>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Examples: Local Anesthesia, Conscious Sedation, General Anesthesia, Deep Sedation
                </small>
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