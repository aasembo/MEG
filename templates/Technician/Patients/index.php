<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Patient> $patients
 */

$this->setLayout('technician');
$this->assign('title', 'Patient Management');
?>
<div class="patients index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-users me-2 text-secondary"></i>Patient Management
            </h1>
            <p class="text-muted mb-0">Manage patients for <?php echo h($currentHospital->name) ?></p>
        </div>
        <div>
            <?php echo $this->Html->link(
                '<i class="fas fa-user-plus me-2"></i>Add New Patient',
                ['action' => 'add'],
                ['class' => 'btn btn-primary', 'escape' => false]
            ) ?>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <?php echo $this->Form->create(null, ['type' => 'get', 'class' => 'row g-3']) ?>
                <div class="col-md-4">
                    <?php echo $this->Form->control('search', [
                        'label' => 'Search Patients',
                        'placeholder' => 'Name, username, or email...',
                        'value' => $search,
                        'class' => 'form-control'
                    ]) ?>
                </div>
                <div class="col-md-3">
                    <?php echo $this->Form->control('status', [
                        'type' => 'select',
                        'options' => $statusOptions,
                        'value' => $status,
                        'class' => 'form-select',
                        'empty' => false
                    ]) ?>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <?php echo $this->Form->button(__('Filter'), ['class' => 'btn btn-outline-secondary me-2']) ?>
                    <?php echo $this->Html->link(__('Clear'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            <?php echo $this->Form->end() ?>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="card">
        <div class="card-body">
            <?php if (count($patients) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?php echo $this->Paginator->sort('Users.last_name', 'Name') ?></th>
                                <th><?php echo $this->Paginator->sort('Users.username', 'Username') ?></th>
                                <th><?php echo $this->Paginator->sort('Users.email', 'Email') ?></th>
                                <th>Gender</th>
                                <th>Date of Birth</th>
                                <th><?php echo $this->Paginator->sort('Users.status', 'Status') ?></th>
                                <th><?php echo $this->Paginator->sort('Patients.created', 'Created') ?></th>
                                <th class="actions"><?php echo __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td>
                                    <strong><?php echo h($patient->user->first_name . ' ' . $patient->user->last_name) ?></strong>
                                    <?php if ($patient->medical_record_number): ?>
                                        <br><small class="text-muted">MRN: <?php echo h($patient->medical_record_number) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo h($patient->user->username) ?></td>
                                <td>
                                    <?php echo h($patient->user->email) ?>
                                    <?php if ($patient->phone): ?>
                                        <br><small class="text-muted">Phone: <?php echo h($patient->phone) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($patient->gender): ?>
                                        <?php
                                        $genderText = match($patient->gender) {
                                            'M' => 'Male',
                                            'F' => 'Female',
                                            'O' => 'Other',
                                            default => h($patient->gender)
                                        };
                                        ?>
                                        <span class="badge bg-light text-dark"><?php echo $genderText ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($patient->dob): ?>
                                        <?php echo $patient->dob->format('M d, Y') ?>
                                        <br><small class="text-muted"><?php echo $patient->dob->diffForHumans() ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match($patient->user->status) {
                                        'active' => 'success',
                                        'inactive' => 'secondary',
                                        'suspended' => 'danger',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?php echo $statusClass ?>"><?php echo h(ucfirst($patient->user->status)) ?></span>
                                </td>
                                <td>
                                    <?php echo $patient->created->format('M d, Y') ?>
                                    <br><small class="text-muted"><?php echo $patient->created->timeAgoInWords() ?></small>
                                </td>
                                <td class="actions">
                                    <div class="btn-group" role="group">
                                        <?php echo $this->Html->link('<i class="fas fa-eye"></i>', ['action' => 'view', $patient->id], [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'escape' => false,
                                            'title' => 'View'
                                        ]) ?>
                                        <?php echo $this->Html->link('<i class="fas fa-edit"></i>', ['action' => 'edit', $patient->id], [
                                            'class' => 'btn btn-sm btn-outline-secondary',
                                            'escape' => false,
                                            'title' => 'Edit'
                                        ]) ?>
                                        <?php if ($patient->user->status === 'active'): ?>
                                        <?php echo $this->Form->postLink('<i class="fas fa-user-times"></i>', ['action' => 'delete', $patient->id], [
                                            'class' => 'btn btn-sm btn-outline-danger',
                                            'escape' => false,
                                            'title' => 'Deactivate',
                                            'confirm' => __('Are you sure you want to deactivate {0}?', $patient->user->first_name . ' ' . $patient->user->last_name)
                                        ]) ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <?php echo $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php echo $this->Paginator->first('<< ' . __('first')) ?>
                            <?php echo $this->Paginator->prev('< ' . __('previous')) ?>
                            <?php echo $this->Paginator->numbers() ?>
                            <?php echo $this->Paginator->next(__('next') . ' >') ?>
                            <?php echo $this->Paginator->last(__('last') . ' >>') ?>
                        </ul>
                    </nav>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users display-1 text-muted"></i>
                    <h4 class="mt-3">No Patients Found</h4>
                    <p class="text-muted">
                        <?php if ($search || $status !== 'all'): ?>
                            No patients match your current filters.
                        <?php else: ?>
                            No patients have been registered for this hospital yet.
                        <?php endif; ?>
                    </p>
                    <?php echo $this->Html->link(__('Add First Patient'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
