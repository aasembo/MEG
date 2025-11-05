<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Modality> $modalities
 */
?>
<?php $this->assign('title', 'Modalities Management'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-stethoscope me-2"></i>Modalities Management
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-x-ray me-2"></i>Medical Imaging Equipment & Devices
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-plus me-2"></i>Add Modality',
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
                        <i class="fas fa-filter me-2 text-warning"></i>Search & Filter Modalities
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
                            <?php echo $modalities->count() ?> modality(ies) found
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body bg-white">
            <?php echo $this->Form->create(null, ['type' => 'get', 'class' => 'row g-3']) ?>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1 text-warning"></i>Search Modalities
                    </label>
                    <?php echo $this->Form->control('search', [
                        'type' => 'text',
                        'class' => 'form-control',
                        'placeholder' => 'Search by name, description...',
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
                            'name' => 'Name (A-Z)',
                            'name DESC' => 'Name (Z-A)',
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
                <i class="fas fa-stethoscope me-2"></i>
                <?php echo $this->Paginator->counter(__('{{count}} Modalities Found')) ?>
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
                        'name' => 'Name (A-Z)',
                        'name DESC' => 'Name (Z-A)', 
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
    <!-- Modalities List -->
    <?php if (!empty($modalities->toArray())): ?>
    <div class="card border-0 shadow">
        <div class="card-body bg-white p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4 fw-semibold text-uppercase small" style="width: 80px;">
                                #
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 250px;">
                                <?php echo $this->Paginator->sort('Modalities.name', 'Modality Name', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 300px;">
                                <?php echo $this->Paginator->sort('Modalities.description', 'Description', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 150px;">
                                <?php echo $this->Paginator->sort('Modalities.created', 'Created', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 text-center fw-semibold text-uppercase small" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($modalities as $modality): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="bg-dark text-warning rounded d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="fas fa-stethoscope"></i>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <?php echo $this->Html->link(
                                            '<span class="fw-semibold">' . h($modality->name) . '</span>',
                                            ['action' => 'view', $modality->id],
                                            ['escape' => false, 'class' => 'text-decoration-none text-dark']
                                        ); ?>
                                        <div class="text-muted small">
                                            <i class="fas fa-hashtag me-1"></i>Modality ID: <?php echo $modality->id ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($modality->description)): ?>
                                    <span class="text-muted"><?php echo h($modality->description) ?></span>
                                <?php else: ?>
                                    <em class="text-muted">No description provided</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-dark"><?php echo h($modality->created->format('M j, Y')) ?></div>
                                <small class="text-muted"><?php echo h($modality->created->format('g:i A')) ?></small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        ['action' => 'view', $modality->id],
                                        [
                                            'class' => 'btn btn-outline-info btn-sm',
                                            'escape' => false,
                                            'title' => 'View Modality',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ) ?>
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-edit"></i>',
                                        ['action' => 'edit', $modality->id],
                                        [
                                            'class' => 'btn btn-outline-warning btn-sm',
                                            'escape' => false,
                                            'title' => 'Edit Modality',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ) ?>
                                    <?php echo $this->Form->postLink(
                                        '<i class="fas fa-trash"></i>',
                                        ['action' => 'delete', $modality->id],
                                        [
                                            'confirm' => __('Are you sure you want to delete "{0}"?', $modality->name),
                                            'class' => 'btn btn-outline-danger btn-sm',
                                            'escape' => false,
                                            'title' => 'Delete Modality',
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
                        <?php echo $this->Paginator->counter('Showing {{start}} to {{end}} of {{count}} modalities'); ?>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <nav aria-label="Modalities pagination">
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
                <i class="fas fa-stethoscope fa-4x text-warning mb-3"></i>
                <h4 class="text-dark mb-2">No Modalities Found</h4>
                <p class="text-muted mb-4">
                    <?php if ($this->request->getQuery('search')): ?>
                        No modalities match your search criteria. Try adjusting your search terms.
                    <?php else: ?>
                        You haven't created any modalities yet. Modalities define the types of medical imaging equipment available.
                    <?php endif; ?>
                </p>
            </div>
            <div>
                <?php echo $this->Html->link(
                    '<i class="fas fa-plus me-2"></i>Add Your First Modality', 
                    ['action' => 'add'], 
                    ['class' => 'btn btn-warning text-dark fw-bold btn-lg', 'escape' => false]
                ) ?>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Examples: CT Scanner, MRI Machine, X-Ray Unit, Ultrasound, PET Scanner
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