<?php
/**
 * @var \App\View\AppView $this
 */
$this->setLayout('login');

// Determine role from the current prefix
$prefix = $this->request->getParam('prefix');
$role = strtolower($prefix);
$roleTitle = ucfirst($role);

// Role-specific configurations
$roleConfig = [
    'doctor' => [
        'icon' => 'fas fa-user-md',
        'color' => 'primary',
        'description' => 'Access patient management and medical tools'
    ],
    'scientist' => [
        'icon' => 'fas fa-microscope', 
        'color' => 'info',
        'description' => 'Access research data and analysis tools'
    ],
    'technician' => [
        'icon' => 'fas fa-tools',
        'color' => 'secondary', 
        'description' => 'Access equipment management and technical tools'
    ]
];

$config = $roleConfig[$role] ?? $roleConfig['doctor'];
?>

<div class="login-container d-flex align-items-center justify-content-center p-4">
    <div class="card shadow-sm border login-card">
        <div class="card-header login-card-header text-white text-center" style="background: var(--bs-<?php echo $config['color'] ?>);">
            <div class="login-header-content">
                <i class="<?php echo $config['icon'] ?> login-icon mb-2"></i>
                <h4 class="mb-0"><?php echo $roleTitle ?> Login</h4>
                <small class="opacity-75"><?php echo $config['description'] ?></small>
            </div>
        </div>
        <div class="card-body p-4">
            <?php echo $this->Flash->render() ?>
            
            <?php echo $this->Form->create(null, [
                'class' => 'login-form',
                'novalidate' => true
            ]) ?>
            
            <div class="mb-3">
                <?php echo $this->Form->control('email', [
                    'label' => [
                        'text' => '<i class="fas fa-envelope me-2"></i>Email',
                        'escape' => false,
                        'class' => 'form-label'
                    ],
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Enter your email',
                    'required' => true,
                    'type' => 'email'
                ]) ?>
            </div>
            
            <div class="mb-4">
                <?php echo $this->Form->control('password', [
                    'label' => [
                        'text' => '<i class="fas fa-lock me-2"></i>Password',
                        'escape' => false,
                        'class' => 'form-label'
                    ],
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Enter your password',
                    'required' => true
                ]) ?>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-<?php echo $config['color'] ?> btn-lg btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Login as <?php echo $roleTitle ?>
                </button>
            </div>
            
            <?php echo $this->Form->end() ?>
        </div>
        <div class="card-footer text-center bg-light">
            <small class="text-muted">
                <i class="fas fa-arrow-left me-1"></i>
                <?php echo $this->Html->link('Back to Main Site', ['controller' => 'Pages', 'action' => 'home', 'prefix' => false], ['class' => 'text-decoration-none']) ?>
            </small>
        </div>
    </div>
</div>