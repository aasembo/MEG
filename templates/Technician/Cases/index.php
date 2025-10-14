<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\MedicalCase> $cases
 */

$this->setLayout('technician');
$this->assign('title', 'Case Management');
?>

<div class="cases index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-briefcase-medical me-2 text-secondary"></i>Case Management
            </h1>
            <p class="text-muted mb-0">Manage cases for <?php echo h($currentHospital->name) ?></p>
        </div>
        <div>
            <?php echo $this->Html->link(
                '<i class="fas fa-plus me-2"></i>New Case',
                ['action' => 'add'],
                ['class' => 'btn btn-primary', 'escape' => false]
            ) ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <?php echo $this->Form->create(null, ['type' => 'get', 'class' => 'row g-3']); ?>
                <div class="col-md-3">
                    <?php echo $this->Form->control('status', [
                        'type' => 'select',
                        'options' => $statusOptions,
                        'value' => $status,
                        'label' => 'Status',
                        'class' => 'form-select',
                        'empty' => false
                    ]); ?>
                </div>
                <div class="col-md-3">
                    <?php echo $this->Form->control('priority', [
                        'type' => 'select',
                        'options' => $priorityOptions,
                        'value' => $priority,
                        'label' => 'Priority',
                        'class' => 'form-select',
                        'empty' => false
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <?php echo $this->Form->control('search', [
                        'type' => 'text',
                        'value' => $search,
                        'label' => 'Search (ID, Patient Name)',
                        'class' => 'form-control',
                        'placeholder' => 'Enter case ID or patient name...'
                    ]); ?>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <?php echo $this->Form->button(__('Filter'), [
                            'type' => 'submit',
                            'class' => 'btn btn-outline-primary'
                        ]); ?>
                    </div>
                </div>
            <?php echo $this->Form->end(); ?>
            
            <div class="mt-3">
                <div class="form-check">
                    <?php 
                    $viewAllChecked = $viewAll ? 'checked' : '';
                    echo $this->Html->link(
                        '<input type="checkbox" class="form-check-input" ' . $viewAllChecked . '> <span class="form-check-label">View all hospital cases</span>',
                        ['?' => array_merge($this->request->getQueryParams(), ['view' => $viewAll ? null : 'all'])],
                        ['escape' => false, 'class' => 'text-decoration-none']
                    );
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="mb-3">
        <small class="text-muted">
            <?php 
            $totalCount = $this->Paginator->counter();
            echo $totalCount;
            if (!$viewAll) {
                echo ' (showing only your cases)';
            }
            ?>
        </small>
    </div>

    <!-- Cases Table -->
    <div class="card">
        <div class="card-body">
            <?php if (!empty($cases->toArray())): ?>
            <div class="table-responsive">
                <table class="table table-hover">
            <thead>
                <tr>
                    <th><?php echo $this->Paginator->sort('id', 'Case ID'); ?></th>
                    <th><?php echo $this->Paginator->sort('patient_id', 'Patient'); ?></th>
                    <th><?php echo $this->Paginator->sort('status', 'Status'); ?></th>
                    <th><?php echo $this->Paginator->sort('priority', 'Priority'); ?></th>
                    <th><?php echo $this->Paginator->sort('date', 'Date'); ?></th>
                    <th><?php echo $this->Paginator->sort('user_id', 'Created By'); ?></th>
                    <th><?php echo $this->Paginator->sort('current_user_id', 'Current User'); ?></th>
                    <th><?php echo $this->Paginator->sort('created', 'Created'); ?></th>
                    <th class="actions"><?php echo __('Actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cases as $case): ?>
                <tr>
                    <td>
                        <strong><?php echo $this->Html->link('#' . h($case->id), ['action' => 'view', $case->id]); ?></strong>
                    </td>
                    <td>
                        <?php if ($case->patient_id && isset($case->patient_user)): ?>
                            <strong><?php echo h($case->patient_user->first_name . ' ' . $case->patient_user->last_name); ?></strong>
                            <br><small class="text-muted">ID: <?php echo h($case->patient_user->id); ?></small>
                        <?php elseif ($case->patient_id): ?>
                            <span class="text-muted">Patient ID: <?php echo h($case->patient_id); ?> (not loaded)</span>
                        <?php else: ?>
                            <span class="text-muted">No patient assigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?php 
                            echo match($case->status) {
                                'draft' => 'secondary',
                                'assigned' => 'info',
                                'in_progress' => 'warning',
                                'review' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                default => 'secondary'
                            };
                        ?>">
                            <?php echo h($case->getStatusLabel()); ?>
                        </span>
                    </td>
                    <td>
                        <span class="<?php echo h($case->getPriorityColorClass()); ?>">
                            <i class="fas fa-<?php 
                                echo match($case->priority) {
                                    'urgent' => 'exclamation-triangle',
                                    'high' => 'arrow-up',
                                    'medium' => 'minus',
                                    'low' => 'arrow-down',
                                    default => 'minus'
                                };
                            ?>"></i>
                            <?php echo h($case->getPriorityLabel()); ?>
                        </span>
                    </td>
                    <td>
                        <?php echo $case->date ? $case->date->format('Y-m-d') : '<span class="text-muted">Not set</span>'; ?>
                    </td>
                    <td>
                        <?php echo h($case->user->first_name . ' ' . $case->user->last_name); ?>
                    </td>
                    <td>
                        <?php if ($case->current_user): ?>
                            <?php echo h($case->current_user->first_name . ' ' . $case->current_user->last_name); ?>
                        <?php else: ?>
                            <span class="text-muted">Unassigned</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo $case->created->format('M j, Y'); ?>
                        <br><small class="text-muted"><?php echo $case->created->format('g:i A'); ?></small>
                    </td>
                    <td class="actions">
                        <div class="btn-group btn-group-sm" role="group">
                            <?php echo $this->Html->link(
                                '<i class="fas fa-eye"></i>',
                                ['action' => 'view', $case->id],
                                ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false, 'title' => 'View']
                            ); ?>
                            
                            <?php if (in_array($case->status, ['draft', 'assigned'])): ?>
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-edit"></i>',
                                    ['action' => 'edit', $case->id],
                                    ['class' => 'btn btn-outline-secondary btn-sm', 'escape' => false, 'title' => 'Edit']
                                ); ?>
                            <?php endif; ?>
                            
                            <?php if (in_array($case->status, ['draft', 'assigned'])): ?>
                                <?php echo $this->Html->link(
                                    '<i class="fas fa-user-plus"></i>',
                                    ['action' => 'assign', $case->id],
                                    ['class' => 'btn btn-outline-info btn-sm', 'escape' => false, 'title' => 'Assign']
                                ); ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div>
            <?php echo $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')); ?>
        </div>
        <nav>
            <ul class="pagination mb-0">
                <?php echo $this->Paginator->first('<< ' . __('first')); ?>
                <?php echo $this->Paginator->prev('< ' . __('previous')); ?>
                <?php echo $this->Paginator->numbers(); ?>
                <?php echo $this->Paginator->next(__('next') . ' >'); ?>
                <?php echo $this->Paginator->last(__('last') . ' >>'); ?>
            </ul>
        </nav>
    </div>

    <?php else: ?>
    <div class="text-center py-5">
        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No cases found</h5>
        <p class="text-muted">
            <?php if ($search || $status !== 'all' || $priority !== 'all'): ?>
                Try adjusting your filters or <?php echo $this->Html->link('clear all filters', ['action' => 'index']); ?>.
            <?php else: ?>
                Start by creating your first case.
            <?php endif; ?>
        </p>
        <?php if (!$search && $status === 'all' && $priority === 'all'): ?>
            <?php echo $this->Html->link(
                '<i class="fas fa-plus me-1"></i>' . __('Create First Case'),
                ['action' => 'add'],
                ['class' => 'btn btn-primary mt-2', 'escape' => false]
            ); ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

