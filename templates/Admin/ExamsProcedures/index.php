<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ExamsProcedure> $examsProcedures
 */
?>
<?php $this->assign('title', 'Exam-Procedure Associations'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-link me-2"></i>Exam-Procedure Associations
                    </h2>
                    <p class="mb-0 text-white-50">
                        <i class="fas fa-sitemap me-2"></i>Manage relationships between exams and procedures
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-plus me-2"></i>Add Association',
                        ['action' => 'add'],
                        ['class' => 'btn btn-warning btn-lg text-dark fw-bold', 'escape' => false]
                    ) ?>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-download me-2"></i>Export',
                        ['action' => 'export'],
                        ['class' => 'btn btn-outline-warning ms-2', 'escape' => false]
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
                    <i class="fas fa-link text-warning fs-1 mb-3"></i>
                    <h3 class="text-warning mb-2"><?php echo $examsProcedures->count() ?></h3>
                    <p class="text-muted mb-0">Total Associations</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-contrast text-warning fs-1 mb-3"></i>
                    <h3 class="text-warning mb-2">
                        <?php 
                        $contrastCount = 0;
                        foreach ($examsProcedures as $ep) {
                            if ($ep->contrast_required) $contrastCount++;
                        }
                        echo $contrastCount;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Contrast Required</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-bed text-danger fs-1 mb-3"></i>
                    <h3 class="text-danger mb-2">
                        <?php 
                        $sedationCount = 0;
                        foreach ($examsProcedures as $ep) {
                            if ($ep->sedation_required) $sedationCount++;
                        }
                        echo $sedationCount;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Sedation Required</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="fas fa-notes-medical text-info fs-1 mb-3"></i>
                    <h3 class="text-info mb-2">
                        <?php 
                        $notesCount = 0;
                        foreach ($examsProcedures as $ep) {
                            if (!empty($ep->notes)) $notesCount++;
                        }
                        echo $notesCount;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">With Notes</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-filter me-2 text-warning"></i>Search & Filter Associations
                </h5>
                <?php if ($this->request->getQuery('search') || $this->request->getQuery('exam_id') || $this->request->getQuery('procedure_id') || $this->request->getQuery('contrast_required') !== null || $this->request->getQuery('sedation_required') !== null): ?>
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
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1 text-warning"></i>Search
                    </label>
                    <?php echo $this->Form->control('search', [
                        'type' => 'text',
                        'class' => 'form-control',
                        'placeholder' => 'Search exams or procedures...',
                        'label' => false,
                        'value' => $this->request->getQuery('search'),
                        'div' => false
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-stethoscope me-1 text-primary"></i>Exam
                    </label>
                    <?php echo $this->Form->control('exam_id', [
                        'type' => 'select',
                        'options' => ['' => 'All Exams'] + $exams,
                        'class' => 'form-select',
                        'label' => false,
                        'value' => $this->request->getQuery('exam_id'),
                        'div' => false
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-procedures me-1 text-success"></i>Procedure
                    </label>
                    <?php echo $this->Form->control('procedure_id', [
                        'type' => 'select',
                        'options' => ['' => 'All Procedures'] + $procedures,
                        'class' => 'form-select',
                        'label' => false,
                        'value' => $this->request->getQuery('procedure_id'),
                        'div' => false
                    ]) ?>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-contrast me-1 text-warning"></i>Contrast
                    </label>
                    <?php echo $this->Form->control('contrast_required', [
                        'type' => 'select',
                        'options' => [
                            '' => 'All Types',
                            '1' => 'Required',
                            '0' => 'Not Required'
                        ],
                        'class' => 'form-select',
                        'label' => false,
                        'value' => $this->request->getQuery('contrast_required'),
                        'div' => false
                    ]) ?>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <?php echo $this->Form->button(
                            '<i class="fas fa-search"></i>',
                            ['type' => 'submit', 'class' => 'btn btn-warning text-dark fw-bold', 'escapeTitle' => false, 'title' => 'Apply Filters']
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
                <i class="fas fa-link me-2"></i>
                <?php echo $this->Paginator->counter(__('{{count}} Associations Found')) ?>
            </span>
            <?php if ($this->request->getQuery('search')): ?>
                <span class="badge bg-info me-2">
                    <i class="fas fa-search me-1"></i>
                    Search: "<?php echo h($this->request->getQuery('search')) ?>"
                </span>
            <?php endif; ?>
            <?php if ($this->request->getQuery('exam_id')): ?>
                <span class="badge bg-primary me-2">
                    <i class="fas fa-stethoscope me-1"></i>
                    Exam: <?php echo isset($exams[$this->request->getQuery('exam_id')]) ? h($exams[$this->request->getQuery('exam_id')]) : 'Unknown' ?>
                </span>
            <?php endif; ?>
            <?php if ($this->request->getQuery('procedure_id')): ?>
                <span class="badge bg-success me-2">
                    <i class="fas fa-procedures me-1"></i>
                    Procedure: <?php echo isset($procedures[$this->request->getQuery('procedure_id')]) ? h($procedures[$this->request->getQuery('procedure_id')]) : 'Unknown' ?>
                </span>
            <?php endif; ?>
            <?php if ($this->request->getQuery('contrast_required') !== null && $this->request->getQuery('contrast_required') !== ''): ?>
                <span class="badge bg-warning text-dark me-2">
                    <i class="fas fa-contrast me-1"></i>
                    Contrast: <?php echo $this->request->getQuery('contrast_required') ? 'Required' : 'Not Required' ?>
                </span>
            <?php endif; ?>
            <?php if ($this->request->getQuery('sedation_required') !== null && $this->request->getQuery('sedation_required') !== ''): ?>
                <span class="badge bg-danger me-2">
                    <i class="fas fa-bed me-1"></i>
                    Sedation: <?php echo $this->request->getQuery('sedation_required') ? 'Required' : 'Not Required' ?>
                </span>
            <?php endif; ?>
        </div>
        <?php if (!$examsProcedures->isEmpty()): ?>
        <div>
            <small class="text-muted fw-semibold">
                <i class="fas fa-list me-1"></i><?php echo $this->Paginator->counter(__('Page {{page}} of {{pages}}')); ?>
            </small>
        </div>
        <?php endif; ?>
    </div>

    <!-- Associations Table -->
    <?php if (!$examsProcedures->isEmpty()): ?>
    <div class="card border-0 shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4 fw-semibold text-uppercase small" style="width: 80px;">
                                <?php echo $this->Paginator->sort('ExamsProcedures.id', 'ID', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 200px;">
                                <?php echo $this->Paginator->sort('Exams.name', 'Exam', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 200px;">
                                <?php echo $this->Paginator->sort('Procedures.name', 'Procedure', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small text-center" style="width: 120px;">
                                <?php echo $this->Paginator->sort('ExamsProcedures.contrast_required', 'Contrast', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small text-center" style="width: 120px;">
                                <?php echo $this->Paginator->sort('ExamsProcedures.sedation_required', 'Sedation', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small" style="width: 140px;">
                                <?php echo $this->Paginator->sort('ExamsProcedures.created', 'Created', [
                                    'class' => 'text-decoration-none text-dark'
                                ]) ?>
                            </th>
                            <th class="border-0 fw-semibold text-uppercase small text-center pe-4" style="width: 120px;">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($examsProcedures as $examsProcedure): ?>
                        <tr>
                            <td class="ps-4">
                                <span class="badge bg-light text-dark fw-bold">
                                    #<?php echo $this->Number->format($examsProcedure->id) ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="fw-semibold text-dark">
                                        <i class="fas fa-stethoscope me-2 text-primary"></i>
                                        <?php echo $examsProcedure->hasValue('exam') ? h($examsProcedure->exam->name) : '-' ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="fw-semibold text-dark">
                                        <i class="fas fa-procedures me-2 text-success"></i>
                                        <?php echo $examsProcedure->hasValue('procedure') ? h($examsProcedure->procedure->name) : '-' ?>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($examsProcedure->contrast_required): ?>
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Required
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary text-white">
                                        <i class="fas fa-times me-1"></i>Not Required
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($examsProcedure->sedation_required): ?>
                                    <span class="badge bg-danger text-white">
                                        <i class="fas fa-bed me-1"></i>Required
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success text-white">
                                        <i class="fas fa-check me-1"></i>Not Required
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="text-muted small">
                                    <i class="fas fa-calendar me-1"></i>
                                    <?php echo $examsProcedure->created->format('M j, Y') ?>
                                    <br>
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo $examsProcedure->created->format('g:i A') ?>
                                </div>
                            </td>
                            <td class="text-center pe-4">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        ['action' => 'view', $examsProcedure->id],
                                        [
                                            'class' => 'btn btn-outline-info',
                                            'escape' => false,
                                            'title' => 'View Details',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ) ?>
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-edit"></i>',
                                        ['action' => 'edit', $examsProcedure->id],
                                        [
                                            'class' => 'btn btn-outline-warning',
                                            'escape' => false,
                                            'title' => 'Edit Association',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ) ?>
                                    <?php echo $this->Form->postLink(
                                        '<i class="fas fa-trash"></i>',
                                        ['action' => 'delete', $examsProcedure->id],
                                        [
                                            'confirm' => __('Are you sure you want to delete this association?'),
                                            'class' => 'btn btn-outline-danger',
                                            'escape' => false,
                                            'title' => 'Delete Association',
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
    </div>

    <!-- Pagination -->
    <?php if ($this->Paginator->hasNext() || $this->Paginator->hasPrev()): ?>
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div>
            <small class="text-muted">
                Showing <?php echo $this->Paginator->counter('{{start}} to {{end}} of {{count}} associations'); ?>
            </small>
        </div>
        <nav aria-label="Associations pagination">
            <ul class="pagination pagination-sm mb-0">
                <?php echo $this->Paginator->first('<i class="fas fa-angle-double-left"></i>', [
                    'class' => 'page-link',
                    'escape' => false
                ]); ?>
                <?php echo $this->Paginator->prev('<i class="fas fa-angle-left"></i>', [
                    'class' => 'page-link',
                    'escape' => false
                ]); ?>
                <?php echo $this->Paginator->numbers([
                    'class' => 'page-link'
                ]); ?>
                <?php echo $this->Paginator->next('<i class="fas fa-angle-right"></i>', [
                    'class' => 'page-link',
                    'escape' => false
                ]); ?>
                <?php echo $this->Paginator->last('<i class="fas fa-angle-double-right"></i>', [
                    'class' => 'page-link',
                    'escape' => false
                ]); ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Empty State -->
    <div class="card border-0 shadow">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="fas fa-link fa-4x text-muted"></i>
            </div>
            <h4 class="text-muted mb-3">No Exam-Procedure Associations Found</h4>
            <p class="text-muted mb-4 lead">
                <?php if ($this->request->getQuery('search') || $this->request->getQuery('exam_id') || $this->request->getQuery('procedure_id') || $this->request->getQuery('contrast_required') !== null || $this->request->getQuery('sedation_required') !== null): ?>
                    No associations match your current filters.<br>
                    Try adjusting your search criteria or clearing the filters.
                <?php else: ?>
                    No associations between exams and procedures have been created yet.<br>
                    Start by creating your first association to link exams with their required procedures.
                <?php endif; ?>
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <?php if ($this->request->getQuery('search') || $this->request->getQuery('exam_id') || $this->request->getQuery('procedure_id') || $this->request->getQuery('contrast_required') !== null || $this->request->getQuery('sedation_required') !== null): ?>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times-circle me-2"></i>Clear Filters',
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-secondary', 'escape' => false]
                    ) ?>
                <?php endif; ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-plus me-2"></i>Create First Association',
                    ['action' => 'add'],
                    ['class' => 'btn btn-warning text-dark fw-bold', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Initialize Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>