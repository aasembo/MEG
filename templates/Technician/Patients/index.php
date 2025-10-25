<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Patient> $patients
 */

$this->setLayout('technician');
$this->assign('title', 'Patient Management');
?>

<div class="patients index content">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-1">
                <i class="fas fa-users me-2 text-primary"></i>Patient Management
            </h1>
            <p class="text-muted mb-0">
                <i class="fas fa-hospital me-1"></i><?php echo h($currentHospital->name) ?>
            </p>
        </div>
        <div class="col-md-4 text-end">
            <?php echo $this->Html->link(
                '<i class="fas fa-user-plus me-2"></i>Add New Patient',
                ['action' => 'add'],
                ['class' => 'btn btn-primary', 'escape' => false]
            ) ?>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filters
            </h5>
            <?php if ($search || $status !== 'all'): ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-times me-1"></i>Clear Filters',
                    ['action' => 'index'],
                    ['class' => 'btn btn-sm btn-outline-secondary', 'escape' => false]
                ); ?>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php echo $this->Form->create(null, ['type' => 'get', 'class' => 'row g-3']); ?>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-search me-1 text-success"></i>Search
                    </label>
                    <?php echo $this->Form->control('search', [
                        'type' => 'text',
                        'value' => $search,
                        'label' => false,
                        'class' => 'form-control',
                        'placeholder' => 'Name, username, email, or MRN...'
                    ]); ?>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">
                        <i class="fas fa-info-circle me-1 text-primary"></i>Status
                    </label>
                    <?php echo $this->Form->control('status', [
                        'type' => 'select',
                        'options' => $statusOptions,
                        'value' => $status,
                        'label' => false,
                        'class' => 'form-select',
                        'empty' => false
                    ]); ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <?php echo $this->Form->button(
                            '<i class="fas fa-search me-1"></i>' . __('Apply'),
                            ['type' => 'submit', 'class' => 'btn btn-primary', 'escapeTitle' => false]
                        ); ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-redo me-1"></i>' . __('Reset'),
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-secondary', 'escape' => false]
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
                echo '<i class="fas fa-users me-1"></i>' . $totalCount . ' ' . ($totalCount == 1 ? 'Patient' : 'Patients');
                ?>
            </span>
        </div>
        <?php if (!empty($patients->toArray())): ?>
        <div>
            <small class="text-muted">
                <?php echo $this->Paginator->counter(__('Page {{page}} of {{pages}}')); ?>
            </small>
        </div>
        <?php endif; ?>
    </div>

    <!-- Patients Table -->
    <?php if (!empty($patients->toArray())): ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4" style="width: 80px;">
                                <i class="fas fa-id-badge"></i>
                            </th>
                            <th class="border-0" style="width: 250px;">
                                <?php echo $this->Paginator->sort('Users.last_name', 'Patient'); ?>
                            </th>
                            <th class="border-0" style="width: 180px;">
                                <?php echo $this->Paginator->sort('Users.username', 'Username'); ?>
                            </th>
                            <th class="border-0">
                                <?php echo $this->Paginator->sort('Users.email', 'Contact'); ?>
                            </th>
                            <th class="border-0" style="width: 100px;">
                                Gender
                            </th>
                            <th class="border-0" style="width: 120px;">
                                <?php echo $this->Paginator->sort('Users.status', 'Status'); ?>
                            </th>
                            <th class="border-0" style="width: 150px;">
                                <?php echo $this->Paginator->sort('Patients.created', 'Registered'); ?>
                            </th>
                            <th class="border-0 text-center" style="width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $patient): ?>
                        <tr class="patient-row">
                            <!-- Patient Icon -->
                            <td class="ps-4">
                                <div class="patient-icon-wrapper d-flex align-items-center justify-content-center">
                                    <?php 
                                    $genderIcon = match($patient->gender ?? '') {
                                        'M' => 'fa-mars',
                                        'F' => 'fa-venus',
                                        'O' => 'fa-transgender',
                                        default => 'fa-user'
                                    };
                                    $genderColor = match($patient->gender ?? '') {
                                        'M' => 'linear-gradient(135deg, #4e73df 0%, #224abe 100%)',
                                        'F' => 'linear-gradient(135deg, #e74a3b 0%, #c92a1e 100%)',
                                        'O' => 'linear-gradient(135deg, #f6c23e 0%, #dda20a 100%)',
                                        default => 'linear-gradient(135deg, #858796 0%, #60616f 100%)'
                                    };
                                    ?>
                                    <div class="patient-icon me-2" style="background: <?php echo $genderColor; ?>">
                                        <i class="fas <?php echo $genderIcon; ?>"></i>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Patient Name & MRN -->
                            <td>
                                <div class="patient-info">
                                    <?php echo $this->Html->link(
                                        '<strong>' . h($patient->user->first_name . ' ' . $patient->user->last_name) . '</strong>',
                                        ['action' => 'view', $patient->id],
                                        ['escape' => false, 'class' => 'text-decoration-none text-dark']
                                    ); ?>
                                    <?php if ($patient->medical_record_number): ?>
                                        <div class="text-muted small">
                                            <i class="fas fa-file-medical me-1"></i>MRN: <?php echo h($patient->medical_record_number) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <!-- Username -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle me-2 text-primary"></i>
                                    <span class="fw-semibold"><?php echo h($patient->user->username) ?></span>
                                </div>
                            </td>
                            
                            <!-- Contact -->
                            <td>
                                <div class="contact-info">
                                    <div class="text-dark small">
                                        <i class="fas fa-envelope me-1 text-muted"></i><?php echo h($patient->user->email) ?>
                                    </div>
                                    <?php if ($patient->phone): ?>
                                        <div class="text-muted small">
                                            <i class="fas fa-phone me-1"></i><?php echo h($patient->phone) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($patient->dob): ?>
                                        <div class="text-muted small">
                                            <i class="fas fa-birthday-cake me-1"></i><?php echo $patient->dob->format('M d, Y') ?> 
                                            <span class="text-muted">(<?php echo $patient->dob->age ?> yrs)</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <!-- Gender -->
                            <td>
                                <?php if ($patient->gender): ?>
                                    <?php
                                    $genderConfig = match($patient->gender) {
                                        'M' => ['class' => 'primary', 'icon' => 'mars', 'text' => 'Male'],
                                        'F' => ['class' => 'danger', 'icon' => 'venus', 'text' => 'Female'],
                                        'O' => ['class' => 'warning', 'icon' => 'transgender', 'text' => 'Other'],
                                        default => ['class' => 'secondary', 'icon' => 'user', 'text' => h($patient->gender)]
                                    };
                                    ?>
                                    <span class="badge badge-gender badge-gender-<?php echo $genderConfig['class'] ?>">
                                        <i class="fas fa-<?php echo $genderConfig['icon'] ?> me-1"></i><?php echo $genderConfig['text'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Status -->
                            <td>
                                <?php
                                $statusConfig = match($patient->user->status) {
                                    'active' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Active'],
                                    'inactive' => ['class' => 'secondary', 'icon' => 'minus-circle', 'text' => 'Inactive'],
                                    'suspended' => ['class' => 'danger', 'icon' => 'ban', 'text' => 'Suspended'],
                                    default => ['class' => 'secondary', 'icon' => 'circle', 'text' => ucfirst($patient->user->status)]
                                };
                                ?>
                                <span class="badge badge-status badge-status-<?php echo $statusConfig['class'] ?>">
                                    <i class="fas fa-<?php echo $statusConfig['icon'] ?> me-1"></i><?php echo $statusConfig['text'] ?>
                                </span>
                            </td>
                            
                            <!-- Registered -->
                            <td>
                                <div class="text-muted small">
                                    <div><i class="fas fa-calendar me-1"></i><?php echo $patient->created->format('M d, Y') ?></div>
                                    <div><i class="fas fa-clock me-1"></i><?php echo $patient->created->timeAgoInWords() ?></div>
                                </div>
                            </td>
                            
                            <!-- Actions -->
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-eye"></i>',
                                        ['action' => 'view', $patient->id],
                                        [
                                            'escape' => false,
                                            'class' => 'btn btn-outline-info',
                                            'title' => 'View Patient',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ); ?>
                                    <?php echo $this->Html->link(
                                        '<i class="fas fa-edit"></i>',
                                        ['action' => 'edit', $patient->id],
                                        [
                                            'escape' => false,
                                            'class' => 'btn btn-outline-primary',
                                            'title' => 'Edit Patient',
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ); ?>
                                    <?php if ($patient->user->status === 'active'): ?>
                                        <?php echo $this->Form->postLink(
                                            '<i class="fas fa-user-times"></i>',
                                            ['action' => 'delete', $patient->id],
                                            [
                                                'escape' => false,
                                                'class' => 'btn btn-outline-danger',
                                                'title' => 'Deactivate Patient',
                                                'data-bs-toggle' => 'tooltip',
                                                'confirm' => __('Are you sure you want to deactivate {0}?', $patient->user->first_name . ' ' . $patient->user->last_name)
                                            ]
                                        ); ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Pagination -->
        <div class="card-footer bg-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">
                        <?php echo $this->Paginator->counter('Showing {{start}} to {{end}} of {{count}} patients'); ?>
                    </small>
                </div>
                <div class="col-md-6">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-end">
                            <?php echo $this->Paginator->first('<i class="fas fa-angle-double-left"></i>', ['escape' => false]); ?>
                            <?php echo $this->Paginator->prev('<i class="fas fa-angle-left"></i> Prev', ['escape' => false]); ?>
                            <?php echo $this->Paginator->numbers(); ?>
                            <?php echo $this->Paginator->next('Next <i class="fas fa-angle-right"></i>', ['escape' => false]); ?>
                            <?php echo $this->Paginator->last('<i class="fas fa-angle-double-right"></i>', ['escape' => false]); ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Empty State -->
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <div class="empty-state">
                <i class="fas fa-users fa-4x text-muted mb-4" style="opacity: 0.3;"></i>
                <h5 class="text-muted mb-2">No patients found</h5>
                <p class="text-muted mb-4">
                    <?php if ($search || $status !== 'all'): ?>
                        No patients match your current filters. Try adjusting your search criteria or <?php echo $this->Html->link('clear all filters', ['action' => 'index'], ['class' => 'text-primary']); ?>.
                    <?php else: ?>
                        No patients have been registered for this hospital yet.
                    <?php endif; ?>
                </p>
                <?php echo $this->Html->link(
                    '<i class="fas fa-user-plus me-2"></i>Add First Patient',
                    ['action' => 'add'],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Patient Icon */
.patient-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    color: white;
    font-size: 14px;
}

/* Gender Badge Styles */
.badge-gender {
    padding: 6px 12px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 6px;
}

.badge-gender-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    color: white;
}

.badge-gender-danger {
    background: linear-gradient(135deg, #e74a3b 0%, #c92a1e 100%);
    color: white;
}

.badge-gender-warning {
    background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
    color: white;
}

.badge-gender-secondary {
    background: linear-gradient(135deg, #858796 0%, #60616f 100%);
    color: white;
}

/* Status Badge Styles */
.badge-status {
    padding: 6px 12px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-status-success {
    background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
    color: white;
}

.badge-status-secondary {
    background: linear-gradient(135deg, #858796 0%, #60616f 100%);
    color: white;
}

.badge-status-danger {
    background: linear-gradient(135deg, #e74a3b 0%, #c92a1e 100%);
    color: white;
}

/* Table Row Hover Effect */
.patient-row {
    transition: all 0.2s ease;
}

.patient-row:hover {
    background-color: #f8f9fc;
    transform: translateX(2px);
}

.patient-row:hover .patient-icon {
    transform: scale(1.05);
}

/* Patient Info */
.patient-info strong {
    color: #5a5c69;
}

/* Contact Info */
.contact-info {
    line-height: 1.6;
}

/* Button Group Styling */
.btn-group-sm .btn {
    padding: 0.375rem 0.75rem;
}

.btn-outline-info:hover {
    background-color: #36b9cc;
    border-color: #36b9cc;
}

.btn-outline-primary:hover {
    background-color: #4e73df;
    border-color: #4e73df;
}

.btn-outline-danger:hover {
    background-color: #e74a3b;
    border-color: #e74a3b;
}

/* Pagination Styling */
.pagination-sm .page-link {
    border-radius: 6px;
    margin: 0 2px;
}

.pagination-sm .page-item.active .page-link {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    border-color: #4e73df;
}

/* Empty State */
.empty-state {
    padding: 2rem;
}

/* Card Shadow */
.card.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
