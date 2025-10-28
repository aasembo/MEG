<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MedicalCase> $cases
 */

$this->setLayout('scientist');
$this->assign('title', 'My Assigned Cases');

use App\Constants\SiteConstants;
?>

<div class="cases index content">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-1">
                <i class="fas fa-briefcase-medical me-2 text-primary"></i>My Assigned Cases
            </h1>
            <p class="text-muted mb-0">
                <i class="fas fa-hospital me-1"></i><?php echo h($currentHospital->name) ?>
                <span class="ms-3 text-info">
                    <i class="fas fa-info-circle me-1"></i>Cases assigned to me
                </span>
            </p>
        </div>
    </div>

    <!-- Status Badges -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex gap-2 flex-wrap">
                <?php 
                // Scientists don't have draft status - their workflow starts with 'assigned'
                $statusLabels = array(
                    SiteConstants::CASE_STATUS_ASSIGNED => array('label' => 'Assigned', 'icon' => 'user-check', 'color' => 'primary'),
                    SiteConstants::CASE_STATUS_IN_PROGRESS => array('label' => 'In Progress', 'icon' => 'spinner', 'color' => 'warning'),
                    SiteConstants::CASE_STATUS_COMPLETED => array('label' => 'Completed', 'icon' => 'check-circle', 'color' => 'success'),
                    SiteConstants::CASE_STATUS_CANCELLED => array('label' => 'Cancelled', 'icon' => 'times-circle', 'color' => 'danger')
                );
                foreach ($statusLabels as $statusKey => $statusData): 
                    $count = isset($statusCounts[$statusKey]) ? $statusCounts[$statusKey] : 0;
                ?>
                <a href="<?php echo $this->Url->build(array('action' => 'index', '?' => array('status' => $statusKey))) ?>" 
                   class="badge bg-<?php echo $statusData['color'] ?> text-decoration-none px-3 py-2">
                    <i class="fas fa-<?php echo $statusData['icon'] ?> me-1"></i><?php echo $statusData['label'] ?>
                    <span class="badge bg-light text-dark ms-1"><?php echo $count ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filters
            </h5>
            <?php 
            $search = $this->request->getQuery('search');
            $status = $this->request->getQuery('status');
            $priority = $this->request->getQuery('priority');
            if ($search || $status || $priority): 
            ?>
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
                        'options' => array(
                            'all' => 'All Statuses',
                            SiteConstants::CASE_STATUS_ASSIGNED => 'Assigned',
                            SiteConstants::CASE_STATUS_IN_PROGRESS => 'In Progress',
                            SiteConstants::CASE_STATUS_COMPLETED => 'Completed',
                            SiteConstants::CASE_STATUS_CANCELLED => 'Cancelled'
                        ),
                        'value' => $status ? $status : 'all',
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
                        'options' => array(
                            'all' => 'All Priorities',
                            SiteConstants::PRIORITY_HIGH => 'High',
                            SiteConstants::PRIORITY_MEDIUM => 'Medium',
                            SiteConstants::PRIORITY_LOW => 'Low'
                        ),
                        'value' => $priority ? $priority : 'all',
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
                        'placeholder' => 'Case ID or patient name...'
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
                            <th class="border-0" style="width: 120px;">
                                <?php echo $this->Paginator->sort('status', 'Status'); ?>
                            </th>
                            <th class="border-0" style="width: 100px;">
                                <?php echo $this->Paginator->sort('priority', 'Priority'); ?>
                            </th>
                            <th class="border-0" style="width: 150px;">
                                <?php echo $this->Paginator->sort('date', 'Case Date'); ?>
                            </th>
                            <th class="border-0" style="width: 150px;">
                                <?php echo $this->Paginator->sort('created', 'Created'); ?>
                            </th>
                            <th class="border-0 text-center" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cases as $case): ?>
                        <tr class="case-row">
                            <!-- Case ID -->
                            <td class="ps-4 fw-bold text-primary">
                                #<?php echo h($case->id) ?>
                            </td>

                            <!-- Patient -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                        <i class="fas fa-user-injured text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">
                                            <?php echo h($case->patient_user ? ($case->patient_user->first_name . ' ' . $case->patient_user->last_name) : 'N/A') ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo h($case->patient_user ? $case->patient_user->email : '') ?>
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td>
                                <?php 
                                // Get scientist-specific status
                                $roleStatus = $case->scientist_status ?? 'draft';
                                $statusClasses = array(
                                    'draft' => 'secondary',
                                    'assigned' => 'primary',
                                    'in_progress' => 'warning',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                );
                                $statusClass = isset($statusClasses[$roleStatus]) ? $statusClasses[$roleStatus] : 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $statusClass ?>">
                                    <?php echo h($case->getStatusLabelForRole('scientist')) ?>
                                </span>
                            </td>

                            <!-- Priority -->
                            <td>
                                <?php 
                                $priorityClasses = array(
                                    SiteConstants::PRIORITY_HIGH => 'danger',
                                    SiteConstants::PRIORITY_MEDIUM => 'warning',
                                    SiteConstants::PRIORITY_LOW => 'info'
                                );
                                $priorityClass = isset($priorityClasses[$case->priority]) ? $priorityClasses[$case->priority] : 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $priorityClass ?>">
                                    <?php echo h(ucfirst($case->priority)) ?>
                                </span>
                            </td>

                            <!-- Case Date -->
                            <td>
                                <i class="fas fa-calendar-alt text-muted me-1"></i>
                                <?php echo $case->date ? $case->date->format('M d, Y') : 'N/A' ?>
                            </td>

                            <!-- Created -->
                            <td>
                                <i class="fas fa-clock text-muted me-1"></i>
                                <?php echo $case->created ? $case->created->format('M d, Y') : 'N/A' ?>
                            </td>

                            <!-- Actions -->
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        array('action' => 'view', $case->id),
                                        array(
                                            'class' => 'btn btn-outline-primary',
                                            'escape' => false,
                                            'title' => 'View Case',
                                            'data-bs-toggle' => 'tooltip'
                                        )
                                    ); ?>
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
    <div class="d-flex justify-content-center mt-4">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php
                echo $this->Paginator->prev('«', array('tag' => 'li', 'class' => 'page-item'));
                echo $this->Paginator->numbers(array('tag' => 'li', 'currentClass' => 'active', 'class' => 'page-item'));
                echo $this->Paginator->next('»', array('tag' => 'li', 'class' => 'page-item'));
                ?>
            </ul>
        </nav>
    </div>

    <?php else: ?>
    <!-- Empty State -->
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <div class="mb-4">
                <i class="fas fa-inbox fa-4x text-muted"></i>
            </div>
            <h4 class="text-muted mb-2">No Cases Found</h4>
            <p class="text-muted mb-4">
                <?php if ($search || $status || $priority): ?>
                    No cases match your current filters. Try adjusting your search criteria.
                <?php else: ?>
                    You don't have any assigned cases yet.
                <?php endif; ?>
            </p>
            <?php if ($search || $status || $priority): ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-times me-2"></i>Clear All Filters',
                    array('action' => 'index'),
                    array('class' => 'btn btn-primary', 'escape' => false)
                ); ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
