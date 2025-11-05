<?php
/**
 * @var \App\View\AppView $this
 */
$this->setLayout('login');

// Admin-specific configuration with distinct color theme
$roleConfig = [
    'icon' => 'fas fa-shield-alt',
    'color' => 'dark', // Dark theme for admin
    'accent_color' => 'warning', // Gold accent for buttons and highlights
    'description' => 'Secure administrative access and system management'
];
?>

<div class="container-fluid vh-100 d-flex align-items-center justify-content-center bg-light">
    <div class="row justify-content-center w-100">
        <div class="col-12 col-md-6 col-lg-4">
            <!-- Login Card -->
            <div class="card border-0 shadow-lg">
                <!-- Header -->
                <div class="card-body bg-<?php echo $roleConfig['color'] ?> text-white p-4 text-center">
                    <div class="mb-3">
                        <i class="<?php echo $roleConfig['icon'] ?> fa-3x mb-3 text-<?php echo $roleConfig['accent_color'] ?>"></i>
                        <h2 class="mb-2 fw-bold">Administrator Portal</h2>
                        <p class="mb-0 opacity-75"><?php echo $roleConfig['description'] ?></p>
                        <?php if (isset($hospitalName)): ?>
                            <p class="mb-0 mt-2 opacity-90">
                                <i class="fas fa-hospital me-1"></i><?php echo h($hospitalName) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Login Form -->
                <div class="card-body p-4">
                    <?php echo $this->Flash->render() ?>
                    
                    <?php echo $this->Form->create(null, [
                        'class' => 'needs-validation',
                        'novalidate' => true
                    ]) ?>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">
                            <i class="fas fa-envelope me-2 text-<?php echo $roleConfig['accent_color'] ?>"></i>Email Address
                        </label>
                        <?php echo $this->Form->control('email', [
                            'type' => 'email',
                            'label' => false,
                            'class' => 'form-control form-control-lg border-2',
                            'placeholder' => 'Enter your administrator email',
                            'required' => true,
                            'autocomplete' => 'email',
                            'id' => 'email'
                        ]) ?>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold">
                            <i class="fas fa-lock me-2 text-<?php echo $roleConfig['accent_color'] ?>"></i>Password
                        </label>
                        <?php echo $this->Form->control('password', [
                            'type' => 'password',
                            'label' => false,
                            'class' => 'form-control form-control-lg border-2',
                            'placeholder' => 'Enter your secure password',
                            'required' => true,
                            'autocomplete' => 'current-password',
                            'id' => 'password'
                        ]) ?>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-<?php echo $roleConfig['accent_color'] ?> btn-lg fw-bold py-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Access Administrator Portal
                        </button>
                    </div>
                    
                    <?php echo $this->Form->end() ?>

                    <!-- Okta Login Option -->
                    <?php if (isset($oktaEnabled) && $oktaEnabled): ?>
                        <div class="text-center mt-3">
                            <div class="d-flex align-items-center mb-3">
                                <hr class="flex-grow-1">
                                <span class="px-3 text-muted small">OR</span>
                                <hr class="flex-grow-1">
                            </div>
                            <?php echo $this->Html->link(
                                '<i class="fab fa-okta me-2"></i>Sign in with Okta',
                                ['action' => 'login'],
                                [
                                    'class' => 'btn btn-outline-dark btn-lg w-100 fw-bold',
                                    'escape' => false
                                ]
                            ) ?>
                        </div>
                    <?php endif; ?>
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

            <!-- Security Notice Card -->
            <div class="text-center mt-4">
                <div class="card border-0 bg-white shadow-sm">
                    <div class="card-body py-3">
                        <small class="text-muted">
                            <i class="fas fa-user-shield me-1 text-<?php echo $roleConfig['accent_color'] ?>"></i>
                            Administrator access only • Requires 'administrator' role privileges
                        </small>
                    </div>
                </div>
            </div>

            <!-- Additional Security Info -->
            <div class="text-center mt-2">
                <div class="card border-0 bg-<?php echo $roleConfig['color'] ?> text-white shadow-sm">
                    <div class="card-body py-2">
                        <small class="opacity-75">
                            <i class="fas fa-shield-alt me-1"></i>
                            Secure login powered by MEG Patient Tracking System
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>