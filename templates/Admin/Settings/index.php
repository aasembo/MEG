<?php
/**
 * @var \App\View\AppView $this
 * @var array $categories
 * @var array $settingsCounts
 * @var int $hospitalId
 */
?>
<?php $this->assign('title', 'System Settings'); ?>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2rem;">
                                <i class="fas fa-cogs"></i>
                            </div>
                        </div>
                        <div>
                            <h2 class="mb-2 fw-bold text-white">
                                System Settings
                            </h2>
                            <p class="mb-0 text-white-50 fs-5">
                                <i class="fas fa-hospital me-2"></i>Manage hospital-specific configuration settings
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-inline-block bg-white bg-opacity-25 rounded-pill px-4 py-2">
                        <i class="fas fa-database me-2"></i>
                        <strong>Hospital ID: <?php echo h($hospitalId); ?></strong>
                    </div>
                    <div class="mt-2">
                        <small><i class="fas fa-info-circle me-1"></i>Hospital-specific overrides</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Categories Cards -->
    <div class="row g-4 mb-4">
        <?php
        $categoryInfo = [
            'ai' => [
                'title' => 'AI Configuration',
                'icon' => 'brain',
                'color' => 'primary',
                'description' => 'Configure AI provider settings (OpenAI, Gemini) and manage AI budgets'
            ],
            'notifications' => [
                'title' => 'Notifications',
                'icon' => 'bell',
                'color' => 'warning',
                'description' => 'Configure email, SMS, and push notification settings'
            ]
        ];
        
        // Filter categories to only show ai and notifications for admin
        $allowedCategories = ['ai', 'notifications'];
        $filteredCategories = array_intersect($categories, $allowedCategories);
        ?>

        <?php foreach ($filteredCategories as $category): ?>
            <?php
            $info = isset($categoryInfo[$category]) ? $categoryInfo[$category] : [
                'title' => ucfirst($category),
                'icon' => 'cog',
                'color' => 'secondary',
                'description' => 'Manage ' . $category . ' settings'
            ];
            $count = isset($settingsCounts[$category]) ? $settingsCounts[$category] : 0;
            ?>
            <div class="col-lg-6">
                <div class="card border-0 shadow h-100 border-start border-<?php echo h($info['color']); ?> border-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="bg-<?php echo h($info['color']); ?> bg-opacity-10 text-<?php echo h($info['color']); ?> rounded d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; font-size: 1.75rem;">
                                    <i class="fas fa-<?php echo h($info['icon']); ?>"></i>
                                </div>
                                <h4 class="fw-bold mb-2"><?php echo h($info['title']); ?></h4>
                                <p class="text-muted mb-3"><?php echo h($info['description']); ?></p>
                            </div>
                            <span class="badge bg-<?php echo h($info['color']); ?> bg-opacity-10 text-<?php echo h($info['color']); ?> fs-6">
                                <?php echo h($count); ?> settings
                            </span>
                        </div>
                        <div class="mt-3">
                            <?php echo $this->Html->link(
                                '<i class="fas fa-arrow-right me-2"></i>Configure Settings',
                                ['action' => $category],
                                ['class' => 'btn btn-' . $info['color'] . ' btn-sm', 'escape' => false]
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- Quick Actions -->
        <div class="col-lg-8">
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-bolt text-warning me-2"></i>Quick Configuration Links
                        </h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="<?php echo $this->Url->build(['action' => 'ai']); ?>" class="list-group-item list-group-item-action p-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-brain"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 fw-bold">AI Provider Configuration</h6>
                                            <p class="mb-0 text-muted small">Manage OpenAI and Google Gemini API credentials and settings</p>
                                        </div>
                                        <div class="ms-3">
                                            <i class="fas fa-chevron-right text-muted"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        
                        <a href="<?php echo $this->Url->build(['action' => 'notifications']); ?>" class="list-group-item list-group-item-action p-4">
                            <div class="d-flex align-items-center">
                                <div class="bg-warning bg-opacity-10 text-warning rounded d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 fw-bold">Notification Settings</h6>
                                            <p class="mb-0 text-muted small">Set up email and SMS notification providers</p>
                                        </div>
                                        <div class="ms-3">
                                            <i class="fas fa-chevron-right text-muted"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Settings Info Sidebar -->
        <div class="col-lg-4">
            <!-- Hospital Context -->
            <div class="card border-0 shadow mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-hospital text-success me-2"></i>Hospital Context
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i class="fas fa-database fa-lg"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Hospital ID: <?php echo h($hospitalId); ?></h6>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                            Active Configuration
                        </span>
                    </div>
                    <hr>
                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            <strong>Important:</strong> These settings are specific to your hospital and override system defaults.
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- Settings Overview -->
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-chart-pie text-info me-2"></i>Settings Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center g-3">
                        <div class="col-6">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <div class="h4 mb-1 text-primary"><?php echo isset($settingsCounts['ai']) ? $settingsCounts['ai'] : 0; ?></div>
                                <div class="small text-muted">AI Settings</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-warning bg-opacity-10 rounded">
                                <div class="h4 mb-1 text-warning"><?php echo isset($settingsCounts['notifications']) ? $settingsCounts['notifications'] : 0; ?></div>
                                <div class="small text-muted">Notifications</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-secondary bg-opacity-10 rounded">
                                <div class="h4 mb-1 text-secondary"><?php echo array_sum($settingsCounts ?? []); ?></div>
                                <div class="small text-muted">Total Settings</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
