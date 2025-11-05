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

$config = $roleConfig[$role] ?? $roleConfig['technician'];
?>

<div class="container-fluid vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="row justify-content-center w-100">
        <div class="col-12 col-md-6 col-lg-4">
            <!-- Login Card -->
            <div class="card border-0 shadow">
                <!-- Header -->
                <div class="card-body bg-<?php echo $config['color'] ?> text-white p-4 text-center">
                    <div class="mb-3">
                        <i class="<?php echo $config['icon'] ?> fa-3x mb-3"></i>
                        <h2 class="mb-2 fw-bold"><?php echo $roleTitle ?> Portal</h2>
                        <p class="mb-0 opacity-75"><?php echo $config['description'] ?></p>
                    </div>
                </div>

                <!-- Login Form -->
                <div class="card-body p-4">
                    <?php echo $this->Flash->render() ?>
                    
                    <?php echo $this->Form->create(null, [
                        'novalidate' => true
                    ]) ?>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">
                            <i class="fas fa-envelope me-2 text-<?php echo $config['color'] ?>"></i>Email Address
                        </label>
                        <?php echo $this->Form->control('email', [
                            'label' => false,
                            'class' => 'form-control form-control-lg border-2',
                            'placeholder' => 'Enter your email address',
                            'required' => true,
                            'autocomplete' => 'email'
                        ]) ?>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold">
                            <i class="fas fa-lock me-2 text-<?php echo $config['color'] ?>"></i>Password
                        </label>
                        <?php echo $this->Form->control('password', [
                            'label' => false,
                            'class' => 'form-control form-control-lg border-2',
                            'placeholder' => 'Enter your password',
                            'required' => true,
                            'autocomplete' => 'current-password'
                        ]) ?>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-<?php echo $config['color'] ?> btn-lg fw-bold py-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In to <?php echo $roleTitle ?> Portal
                        </button>
                    </div>
                    
                    <?php echo $this->Form->end() ?>
                </div>

                <!-- Footer -->
                <div class="card-footer bg-light text-center border-top-0 py-3">
                    <small class="text-muted">
                        <i class="fas fa-arrow-left me-1"></i>
                        <?php echo $this->Html->link(
                            'Back to Main Site', 
                            ['controller' => 'Pages', 'action' => 'home', 'prefix' => false], 
                            ['class' => 'text-decoration-none text-secondary fw-bold']
                        ) ?>
                    </small>
                </div>
            </div>

            <!-- Additional Info Card -->
            <div class="text-center mt-4">
                <div class="card border-0 bg-white shadow-sm">
                    <div class="card-body py-3">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1 text-success"></i>
                            Secure login powered by MEG Patient Tracking System
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>