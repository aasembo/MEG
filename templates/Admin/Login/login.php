<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="login-container d-flex align-items-center justify-content-center p-4">
    <div class="card shadow-sm border login-card">
        <div class="card-header login-card-header text-white text-center">
            <div class="mb-3">
                <i class="fas fa-shield-alt fa-3x shield-icon"></i>
            </div>
            <h2 class="mb-0 fw-bold">Admin Panel</h2>
            <p class="mb-0 opacity-75">
                <?php if (isset($hospitalName)): ?>
                    <?php echo h($hospitalName) ?> Administration
                <?php else: ?>
                    Hospital Administration
                <?php endif; ?>
            </p>
        </div>
        
        <div class="card-body p-4">
            <?php echo $this->CustomFlash->renderAll() ?>
            
            <?php echo $this->Form->create(null, [
                'class' => 'needs-validation',
                'novalidate' => true
            ]) ?>
            
            <div class="mb-3">
                <label for="email" class="form-label text-muted fw-semibold">
                    <i class="fas fa-envelope me-2"></i>Email Address
                </label>
                <?php echo $this->Form->control('email', [
                    'type' => 'email',
                    'class' => 'form-control form-control-lg border-2',
                    'placeholder' => 'Enter your email address',
                    'label' => false,
                    'required' => true,
                    'id' => 'email'
                ]) ?>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label text-muted fw-semibold">
                    <i class="fas fa-lock me-2"></i>Password
                </label>
                <?php echo $this->Form->control('password', [
                    'type' => 'password',
                    'class' => 'form-control form-control-lg border-2',
                    'placeholder' => 'Enter your password',
                    'label' => false,
                    'required' => true,
                    'id' => 'password'
                ]) ?>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </div>
            
            <?php echo $this->Form->end() ?>
        </div>
        
        <div class="card-footer bg-light text-center text-muted py-3 border-top">
            <small>
                <i class="fas fa-info-circle me-1"></i>
                Only users with 'super' role can access this panel
            </small>
        </div>
    </div>
</div>