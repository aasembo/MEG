<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Exam> $exams
 */
?>
<?php $this->assign('title', 'Exams Management'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-file-medical me-2"></i>Exams Management
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-stethoscope me-2"></i>Medical Examination & Testing
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-plus me-2"></i>Add Exam',
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
                    <i class="fas fa-file-medical text-warning fs-1 mb-3"></i>
                    <h3 class="text-warning mb-2"><?php echo $exams->count() ?></h3>
                    <p class="text-muted mb-0">Total Exams</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-desktop text-info fs-1 mb-3"></i>
                    <h3 class="text-info mb-2">
                        <?php 
                        $modalityCount = 0;
                        $modalities = [];
                        foreach ($exams as $exam) {
                            if ($exam->hasValue('modality') && !in_array($exam->modality->id, $modalities)) {
                                $modalities[] = $exam->modality->id;
                                $modalityCount++;
                            }
                        }
                        echo $modalityCount;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Active Modalities</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle text-success fs-1 mb-3"></i>
                    <h3 class="text-success mb-2">
                        <?php 
                        $prepRequired = 0;
                        foreach ($exams as $exam) {
                            if ($exam->preparation_required) {
                                $prepRequired++;
                            }
                        }
                        echo $prepRequired;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Require Preparation</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt text-secondary fs-1 mb-3"></i>
                    <h3 class="text-secondary mb-2">
                        <?php 
                        $recent = 0;
                        $thirtyDaysAgo = new DateTime('-30 days');
                        foreach ($exams as $exam) {
                            if ($exam->created >= $thirtyDaysAgo) {
                                $recent++;
                            }
                        }
                        echo $recent;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Recent Additions</p>
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
                        <i class="fas fa-filter me-2 text-warning"></i>Search & Filter Exams
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
                            <?php echo $exams->count() ?> exam(s) found
                        </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body bg-white">
            <?php echo $this->Form->create(null, ['type' => 'get', 'class' => 'row g-3']) ?>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1 text-warning"></i>Search Exams
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
                            'duration_minutes' => 'Duration (Short to Long)',
                            'duration_minutes DESC' => 'Duration (Long to Short)',
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
                <i class="fas fa-file-medical me-2"></i>
                <?php echo $this->Paginator->counter(__('{{count}} Exams Found')) ?>
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
                        'duration_minutes' => 'Duration (Short to Long)',
                        'duration_minutes DESC' => 'Duration (Long to Short)', 
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

    <!-- Exams List -->
    <?php if (!empty($exams->toArray())): ?>
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
                                <?php echo $this->Paginator->sort('Exams.name', 'Exam Name', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 180px;">
                                <?php echo $this->Paginator->sort('Departments.name', 'Department', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 150px;">
                                <?php echo $this->Paginator->sort('Modalities.name', 'Modality', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 120px;">
                                <?php echo $this->Paginator->sort('Exams.duration_minutes', 'Duration', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 150px;">
                                <?php echo $this->Paginator->sort('Exams.created', 'Created', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 text-center fw-semibold text-uppercase small" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exams as $exam): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="bg-dark text-warning rounded d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="fas fa-file-medical"></i>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <?php echo $this->Html->link(
                                            '<span class="fw-semibold">' . h($exam->name) . '</span>',
                                            ['action' => 'view', $exam->id],
                                            ['escape' => false, 'class' => 'text-decoration-none text-dark']
                                        ); ?>
                                        <div class="text-muted small">
                                            <i class="fas fa-hashtag me-1"></i>Exam ID: <?php echo $exam->id ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($exam->hasValue('department')): ?>
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-building me-1 text-warning"></i>' . h($exam->department->name),
                                        ['controller' => 'Departments', 'action' => 'view', $exam->department->id],
                                        ['escape' => false, 'class' => 'text-decoration-none text-dark fw-semibold']
                                    ); ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($exam->hasValue('modality')): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info">
                                        <i class="fas fa-desktop me-1"></i><?php echo h($exam->modality->name) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($exam->duration_minutes)): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <i class="fas fa-clock me-1"></i><?php echo h($exam->duration_minutes) ?> min
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-dark fw-semibold"><?php echo h($exam->created->format('M j, Y')) ?></div>
                                <div class="text-muted small"><?php echo h($exam->created->format('g:i A')) ?></div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        ['action' => 'view', $exam->id],
                                        ['class' => 'btn btn-outline-info btn-sm', 'escape' => false, 'title' => 'View Exam']
                                    ) ?>
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-edit"></i>',
                                        ['action' => 'edit', $exam->id],
                                        ['class' => 'btn btn-outline-warning btn-sm', 'escape' => false, 'title' => 'Edit Exam']
                                    ) ?>
                                    <?php echo $this->Form->postLink(
                                        '<i class="fas fa-trash"></i>',
                                        ['action' => 'delete', $exam->id],
                                        [
                                            'confirm' => __('Are you sure you want to delete exam "{0}"?', $exam->name),
                                            'class' => 'btn btn-outline-danger btn-sm',
                                            'escape' => false,
                                            'title' => 'Delete Exam'
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
        
        <!-- Pagination -->
        <?php echo $this->element('admin_pagination', ['items' => $exams, 'itemType' => 'exams']) ?>
    </div>
    
    <?php else: ?>
    <!-- Empty State -->
    <div class="card border-0 shadow">
        <div class="card-body bg-white text-center py-5">
            <div class="mb-4">
                <i class="fas fa-file-medical fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Exams Found</h4>
                <p class="text-muted mb-4">You haven't created any exams yet. Exams are diagnostic tests performed using medical equipment and procedures.</p>
            </div>
            <div>
                <?php echo $this->Html->link(
                    '<i class="fas fa-plus me-2"></i>Add Your First Exam', 
                    ['action' => 'add'], 
                    ['class' => 'btn btn-warning btn-lg text-dark fw-bold', 'escape' => false]
                ) ?>
            </div>
            <div class="mt-3">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Examples: Chest X-Ray, Blood Panel, ECG, Ultrasound, CT Scan, MRI
                </small>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>