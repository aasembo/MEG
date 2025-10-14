<?php
/**
 * @var \App\View\AppView $this
 * @var object $user
 * @var object|null $currentHospital
 */
$this->setLayout('scientist');
$this->assign('title', 'Doctor Dashboard');

$prefix = $this->request->getParam('prefix');
$role = strtolower($prefix);
$roleTitle = ucfirst($role);

// Role-specific configurations
$roleConfig = [
    'doctor' => [
        'icon' => 'fas fa-user-md',
        'color' => 'primary',
        'welcomeMessage' => 'Welcome to your medical dashboard',
        'features' => [
            'Patient Management' => 'fas fa-users',
            'Medical Records' => 'fas fa-file-medical',
            'Appointments' => 'fas fa-calendar-alt',
            'Prescriptions' => 'fas fa-pills'
        ]
    ],
    'scientist' => [
        'icon' => 'fas fa-microscope',
        'color' => 'info', 
        'welcomeMessage' => 'Welcome to your research dashboard',
        'features' => [
            'Research Studies' => 'fas fa-flask',
            'Data Analysis' => 'fas fa-chart-line',
            'Clinical Trials' => 'fas fa-vial',
            'Publications' => 'fas fa-book'
        ]
    ],
    'technician' => [
        'icon' => 'fas fa-tools',
        'color' => 'secondary',
        'welcomeMessage' => 'Welcome to your technical dashboard', 
        'features' => [
            'Equipment Status' => 'fas fa-cogs',
            'Maintenance' => 'fas fa-wrench',
            'Calibration' => 'fas fa-sliders-h',
            'Reports' => 'fas fa-clipboard-list'
        ]
    ]
];

$config = $roleConfig[$role] ?? $roleConfig['doctor'];
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Welcome Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="avatar-wrapper me-3">
                                    <div class="avatar bg-<?php echo $config['color'] ?> text-white">
                                        <i class="<?php echo $config['icon'] ?>"></i>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="mb-1">Welcome back, <?php echo h($user->name ?? $user->username) ?>!</h4>
                                    <p class="text-muted mb-0"><?php echo $config['welcomeMessage'] ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <?php if ($currentHospital): ?>
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-hospital me-1"></i><?php echo h($currentHospital->name) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary fs-6">
                                    <i class="fas fa-globe me-1"></i>System Wide
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-4 mb-4">
                <?php foreach ($config['features'] as $feature => $icon): ?>
                <div class="col-lg-3 col-md-6">
                    <div class="card h-100 feature-card">
                        <div class="card-body text-center">
                            <div class="feature-icon text-<?php echo $config['color'] ?> mb-3">
                                <i class="<?php echo $icon ?>"></i>
                            </div>
                            <h5 class="card-title"><?php echo $feature ?></h5>
                            <p class="card-text text-muted">Access <?php echo strtolower($feature) ?> tools and data</p>
                            <button class="btn btn-outline-<?php echo $config['color'] ?>" disabled>
                                Coming Soon
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Information Cards -->
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>System Status
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="status-item d-flex justify-content-between align-items-center mb-2">
                                <span>Platform Status</span>
                                <span class="badge bg-success">Online</span>
                            </div>
                            <div class="status-item d-flex justify-content-between align-items-center mb-2">
                                <span>Database</span>
                                <span class="badge bg-success">Connected</span>
                            </div>
                            <div class="status-item d-flex justify-content-between align-items-center">
                                <span>Services</span>
                                <span class="badge bg-success">Operational</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user me-2"></i>Profile Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Role:</strong> <?php echo h($roleTitle) ?></p>
                            <p><strong>Username:</strong> <?php echo h($user->username ?? 'N/A') ?></p>
                            <?php if ($currentHospital): ?>
                                <p><strong>Hospital:</strong> <?php echo h($currentHospital->name) ?></p>
                            <?php endif; ?>
                            <p><strong>Last Login:</strong> <?php echo $this->Time->format($user->modified ?? 'now', 'MMM d, yyyy HH:mm') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-wrapper .avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.feature-card {
    transition: transform 0.2s;
}

.feature-card:hover {
    transform: translateY(-2px);
}

.feature-icon {
    font-size: 2.5rem;
}

.status-item {
    padding: 0.25rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.status-item:last-child {
    border-bottom: none;
}
</style>