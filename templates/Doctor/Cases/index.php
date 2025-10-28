<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MedicalCase> $cases
 */

$this->setLayout('doctor');
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
                // Doctors do not have draft status - they start with 'assigned' when case is assigned to them
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
                            SiteConstants::PRIORITY_HIGH => 'High Priority',
                            SiteConstants::PRIORITY_MEDIUM => 'Medium Priority',
                            SiteConstants::PRIORITY_LOW => 'Low Priority'
                        ),
                        'value' => $priority ? $priority : 'all',
                        'label' => false,
                        'class' => 'form-select',
                        'empty' => false
                    )); ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1 text-info"></i>Search
                    </label>
                    <?php echo $this->Form->control('search', array(
                        'type' => 'text',
                        'value' => $search,
                        'label' => false,
                        'placeholder' => 'Search by case ID or patient name',
                        'class' => 'form-control'
                    )); ?>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <?php echo $this->Form->button('<i class="fas fa-search me-1"></i>Filter', array(
                        'type' => 'submit',
                        'class' => 'btn btn-primary w-100',
                        'escapeTitle' => false
                    )); ?>
                </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>

    <!-- Cases Table -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>Cases List
            </h5>
            <span class="badge bg-primary">
                <?php echo $this->Paginator->counter('{{count}} Cases') ?>
            </span>
        </div>
        <div class="card-body p-0">
            <?php if (!$cases->isEmpty()): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo $this->Paginator->sort('id', 'ID') ?></th>
                                <th><?php echo $this->Paginator->sort('PatientUsers.first_name', 'Patient') ?></th>
                                <th>Exam/Procedure</th>
                                <th>Department</th>
                                <th><?php echo $this->Paginator->sort('priority', 'Priority') ?></th>
                                <th><?php echo $this->Paginator->sort('doctor_status', 'My Status') ?></th>
                                <th><?php echo $this->Paginator->sort('status', 'Global Status') ?></th>
                                <th><?php echo $this->Paginator->sort('created', 'Created') ?></th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cases as $case): ?>
                            <tr>
                                <td>
                                    <strong class="text-primary">#<?php echo h($case->id) ?></strong>
                                </td>
                                <td>
                                    <?php 
                                    if ($case->patient_user) {
                                        echo h($case->patient_user->first_name . ' ' . $case->patient_user->last_name);
                                    } else {
                                        echo '<span class="text-muted">N/A</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if (!empty($case->cases_exams_procedures)) {
                                        $examProc = $case->cases_exams_procedures[0];
                                        if ($examProc->exams_procedure && $examProc->exams_procedure->exam) {
                                            echo '<i class="fas fa-x-ray me-1 text-info"></i>' . h($examProc->exams_procedure->exam->name);
                                        } elseif ($examProc->exams_procedure && $examProc->exams_procedure->procedure) {
                                            echo '<i class="fas fa-procedures me-1 text-success"></i>' . h($examProc->exams_procedure->procedure->name);
                                        }
                                    } else {
                                        echo '<span class="text-muted">N/A</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php echo $case->department ? h($case->department->name) : '<span class="text-muted">N/A</span>' ?>
                                </td>
                                <td>
                                    <?php echo $this->Status->priorityBadge($case->priority) ?>
                                </td>
                                <td>
                                    <?php 
                                    $doctorStatus = $case->doctor_status ? $case->doctor_status : 'assigned';
                                    echo '<span class="badge bg-' . $this->Status->colorClass($doctorStatus) . '">' . ucfirst(str_replace('_', ' ', $doctorStatus)) . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <?php echo $this->Status->globalBadge($case) ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i><?php echo $case->created ? $case->created->format('M d, Y') : 'N/A' ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php echo $this->Html->link(
                                            '<i class="fas fa-eye"></i>',
                                            array('action' => 'view', $case->id),
                                            array('class' => 'btn btn-outline-primary', 'escape' => false, 'title' => 'View Case')
                                        ); ?>
                                        <?php echo $this->Html->link(
                                            '<i class="fas fa-edit"></i>',
                                            array('action' => 'edit', $case->id),
                                            array('class' => 'btn btn-outline-secondary', 'escape' => false, 'title' => 'Edit Case')
                                        ); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No cases found matching your criteria.</p>
                    <?php if ($search || $status || $priority): ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-times me-1"></i>Clear Filters',
                            array('action' => 'index'),
                            array('class' => 'btn btn-outline-primary', 'escape' => false)
                        ); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!$cases->isEmpty()): ?>
        <div class="card-footer">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0 text-muted small">
                        <?php echo $this->Paginator->counter('Showing {{start}} to {{end}} of {{count}} cases') ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm justify-content-end mb-0">
                            <?php echo $this->Paginator->first('<i class="fas fa-angle-double-left"></i>', array('escape' => false)) ?>
                            <?php echo $this->Paginator->prev('<i class="fas fa-angle-left"></i>', array('escape' => false)) ?>
                            <?php echo $this->Paginator->numbers() ?>
                            <?php echo $this->Paginator->next('<i class="fas fa-angle-right"></i>', array('escape' => false)) ?>
                            <?php echo $this->Paginator->last('<i class="fas fa-angle-double-right"></i>', array('escape' => false)) ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.badge {
    font-size: 0.875em;
}

.table td {
    vertical-align: middle;
}

.btn-group-sm>.btn {
    padding: 0.25rem 0.5rem;
}

.pagination {
    --bs-pagination-active-bg: #0d6efd;
    --bs-pagination-active-border-color: #0d6efd;
}
</style>
