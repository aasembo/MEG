<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Report> $reports
 */
?>
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-primary text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-0 fw-bold">
                        <i class="fas fa-file-alt me-2"></i>Reports Management
                    </h2>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($reports->toArray())): ?>
    <!-- Reports Table -->
    <div class="card border-0 shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4 fw-semibold text-uppercase small"><?= __('Case ID') ?></th>
                            <th class="border-0 fw-semibold text-uppercase small"><?= __('Patient') ?></th>
                            <th class="border-0 fw-semibold text-uppercase small"><?= __('Hospital') ?></th>
                            <th class="border-0 fw-semibold text-uppercase small"><?= __('Status') ?></th>
                            <th class="border-0 fw-semibold text-uppercase small"><?= __('Confidence Score') ?></th>
                            <th class="border-0 fw-semibold text-uppercase small"><?= __('Created') ?></th>
                            <th class="border-0 text-center fw-semibold text-uppercase small"><?= __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $report): ?>
                        <tr>
                            <td class="ps-4">
                                <strong class="text-primary"><?= $this->Html->link('#' . $report->case_id, ['controller' => 'Cases', 'action' => 'view', $report->case_id], ['class' => 'text-decoration-none']) ?></strong>
                            </td>
                            <td>
                                <?php if (isset($report->case->patient_user)): ?>
                                    <div class="fw-semibold text-dark">
                                        <?= h($report->case->patient_user->first_name . ' ' . $report->case->patient_user->last_name) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?= $report->has('hospital') ? h($report->hospital->name) : '' ?></td>
                            <td>
                                <span class="badge rounded-pill bg-<?= $report->status === 'approved' ? 'success' : ($report->status === 'reviewed' ? 'info' : ($report->status === 'rejected' ? 'danger' : 'warning')) ?>">
                                    <?= h(ucfirst($report->status)) ?>
                                </span>
                            </td>
                            <td><?= $report->confidence_score ? h($report->confidence_score) . '%' : '<span class="text-muted">N/A</span>' ?></td>
                            <td>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i><?= h($report->created->format('M d, Y')) ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?= $this->Html->link('<i class="fas fa-eye"></i>', ['action' => 'view', $report->id], ['escape' => false, 'class' => 'btn btn-outline-info', 'title' => __('View'), 'data-bs-toggle' => 'tooltip']) ?>
                                    <?= $this->Html->link('<i class="fas fa-download"></i>', ['action' => 'preview', $report->id], ['escape' => false, 'class' => 'btn btn-outline-success', 'title' => __('Download'), 'data-bs-toggle' => 'tooltip']) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Empty State -->
    <div class="card shadow">
        <div class="card-body text-center py-5">
            <i class="fas fa-file-alt fa-4x text-secondary mb-4 opacity-25"></i>
            <h5 class="text-muted mb-2">No reports found</h5>
            <p class="text-muted mb-4">
                Reports are automatically generated from cases. Start by viewing cases or creating a new one.
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <?php echo $this->Html->link(
                    '<i class="fas fa-briefcase-medical me-2"></i>View Cases',
                    ['controller' => 'Cases', 'action' => 'index'],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ); ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-plus-circle me-2"></i>Create New Case',
                    ['controller' => 'Cases', 'action' => 'add'],
                    ['class' => 'btn btn-outline-primary', 'escape' => false]
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