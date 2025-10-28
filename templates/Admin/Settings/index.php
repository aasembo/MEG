<?php
/**
 * @var \App\View\AppView $this
 * @var array $categories
 * @var array $settingsCounts
 * @var int $hospitalId
 */

$this->assign('title', 'System Settings');
$this->layout = 'admin';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i> System Settings
                    </h3>
                    <p class="text-muted mb-0">Manage hospital-specific configuration settings</p>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Hospital ID:</strong> <?php echo h($hospitalId); ?> - These settings are specific to your hospital and override system defaults.
                    </div>

                    <div class="row">
                        <?php
                        $categoryInfo = array(
                            'ai' => array(
                                'title' => 'AI Configuration',
                                'icon' => 'brain',
                                'color' => 'primary',
                                'description' => 'Configure AI provider settings (OpenAI, Gemini) and manage AI budgets'
                            ),
                            'notifications' => array(
                                'title' => 'Notifications',
                                'icon' => 'bell',
                                'color' => 'warning',
                                'description' => 'Configure email, SMS, and push notification settings'
                            )
                        );
                        
                        // Filter categories to only show ai and notifications for admin
                        $allowedCategories = array('ai', 'notifications');
                        $filteredCategories = array_intersect($categories, $allowedCategories);
                        ?>

                        <?php foreach ($filteredCategories as $category): ?>
                            <?php
                            $info = isset($categoryInfo[$category]) ? $categoryInfo[$category] : array(
                                'title' => ucfirst($category),
                                'icon' => 'cog',
                                'color' => 'secondary',
                                'description' => 'Manage ' . $category . ' settings'
                            );
                            $count = isset($settingsCounts[$category]) ? $settingsCounts[$category] : 0;
                            ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="icon-circle bg-<?php echo h($info['color']); ?> text-white">
                                                <i class="fas fa-<?php echo h($info['icon']); ?>"></i>
                                            </div>
                                            <span class="badge bg-secondary"><?php echo h($count); ?> settings</span>
                                        </div>
                                        <h5 class="card-title"><?php echo h($info['title']); ?></h5>
                                        <p class="card-text text-muted small"><?php echo h($info['description']); ?></p>
                                        <?php echo $this->Html->link(
                                            'Configure',
                                            array('action' => $category),
                                            array('class' => 'btn btn-outline-' . $info['color'] . ' btn-sm w-100')
                                        ); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-4">
                        <h5>Quick Links</h5>
                        <div class="list-group">
                            <a href="<?php echo $this->Url->build(array('action' => 'ai')); ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-brain text-primary"></i>
                                <strong>AI Provider Configuration</strong>
                                <span class="float-end"><i class="fas fa-chevron-right"></i></span>
                                <br>
                                <small class="text-muted">Manage OpenAI and Google Gemini API credentials and settings</small>
                            </a>
                            <a href="<?php echo $this->Url->build(array('action' => 'notifications')); ?>" class="list-group-item list-group-item-action">
                                <i class="fas fa-bell text-warning"></i>
                                <strong>Notification Settings</strong>
                                <span class="float-end"><i class="fas fa-chevron-right"></i></span>
                                <br>
                                <small class="text-muted">Set up email and SMS notification providers</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.opacity-50 {
    opacity: 0.5;
}

.list-group-item {
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
}

.list-group-item:hover {
    border-left-color: #007bff;
    padding-left: 1.5rem;
}
</style>
