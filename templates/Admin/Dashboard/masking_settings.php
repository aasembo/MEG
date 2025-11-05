<?php
/**
 * Patient Data Masking Settings Template
 * 
 * @var \App\View\AppView $this
 * @var array $status
 */
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shield-alt me-2"></i>
                        Patient Data Masking Settings
                    </h3>
                </div>
                <div class="card-body">
                    
                    <!-- Current Status -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card <?php echo $status['enabled'] ? 'border-success' : 'border-warning'; ?>">
                                <div class="card-body text-center">
                                    <i class="fas <?php echo $status['enabled'] ? 'fa-shield-alt text-success' : 'fa-shield-slash text-warning'; ?> fa-3x mb-3"></i>
                                    <h4>Current Status</h4>
                                    <h2 class="<?php echo $status['enabled'] ? 'text-success' : 'text-warning'; ?>">
                                        <?php echo ucfirst($status['status']); ?>
                                    </h2>
                                    <?php if ($status['warning']): ?>
                                        <div class="alert alert-warning mt-3">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <?php echo h($status['warning']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-info-circle text-info"></i>
                                        Configuration Details
                                    </h5>
                                    <ul class="list-unstyled">
                                        <li><strong>Config Key:</strong> <code><?php echo h($status['config_key']); ?></code></li>
                                        <li><strong>Default State:</strong> <?php echo h($status['default_state']); ?></li>
                                        <li><strong>Last Modified:</strong> <?php echo h($status['last_modified']); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Control Panel -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Masking Controls</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php echo $this->Form->create(null, [
                                                'type' => 'post',
                                                'id' => 'enable-masking-form',
                                                'class' => 'masking-control-form'
                                            ]); ?>
                                            <?php echo $this->Form->hidden('action', ['value' => 'enable']); ?>
                                            
                                            <button type="submit" 
                                                    class="btn btn-success btn-lg w-100 <?php echo $status['enabled'] ? 'disabled' : ''; ?>"
                                                    <?php echo $status['enabled'] ? 'disabled' : ''; ?>>
                                                <i class="fas fa-shield-alt me-2"></i>
                                                Enable Masking
                                            </button>
                                            
                                            <div class="text-muted mt-2">
                                                <small>
                                                    <i class="fas fa-info-circle"></i>
                                                    Enables role-based patient data masking for enhanced privacy and security.
                                                </small>
                                            </div>
                                            
                                            <?php echo $this->Form->end(); ?>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <?php echo $this->Form->create(null, [
                                                'type' => 'post',
                                                'id' => 'disable-masking-form',
                                                'class' => 'masking-control-form'
                                            ]); ?>
                                            <?php echo $this->Form->hidden('action', ['value' => 'disable']); ?>
                                            
                                            <button type="submit" 
                                                    class="btn btn-warning btn-lg w-100 <?php echo !$status['enabled'] ? 'disabled' : ''; ?>"
                                                    <?php echo !$status['enabled'] ? 'disabled' : ''; ?>
                                                    onclick="return confirm('Are you sure you want to disable patient data masking? This will make sensitive information visible to all users.');">
                                                <i class="fas fa-shield-slash me-2"></i>
                                                Disable Masking
                                            </button>
                                            
                                            <div class="text-muted mt-2">
                                                <small>
                                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                                    <strong>Warning:</strong> Disabling masking will expose sensitive patient information.
                                                </small>
                                            </div>
                                            
                                            <?php echo $this->Form->end(); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Information Panel -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-secondary">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-question-circle text-info"></i>
                                        About Patient Data Masking
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p>Patient data masking is a security feature that protects sensitive patient information by displaying different levels of detail based on user roles:</p>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><strong>When Enabled:</strong></h6>
                                            <ul>
                                                <li><strong>Super Admin/Administrator:</strong> Can see most patient information</li>
                                                <li><strong>Doctor:</strong> Sees patient identifiers and basic information</li>
                                                <li><strong>Nurse/Technician:</strong> Sees limited patient information</li>
                                                <li><strong>Scientist:</strong> Sees anonymized patient identifiers only</li>
                                            </ul>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <h6><strong>When Disabled:</strong></h6>
                                            <ul>
                                                <li>All users see complete, unmasked patient information</li>
                                                <li>Sensitive data like full names, addresses, and contact info are visible</li>
                                                <li>Increased privacy risk but easier for administrative tasks</li>
                                                <li>All access is still logged for audit purposes</li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-info mt-3">
                                        <h6><strong>Security Recommendation:</strong></h6>
                                        <p class="mb-0">
                                            Keep masking enabled in production environments to comply with privacy regulations 
                                            and protect patient confidentiality. Only disable temporarily for administrative 
                                            tasks when necessary.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Back to Dashboard -->
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <?php echo $this->Html->link(
                                '<i class="fas fa-arrow-left me-2"></i>Back to Dashboard',
                                ['action' => 'index'],
                                ['class' => 'btn btn-outline-secondary', 'escape' => false]
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh status every 30 seconds
setInterval(function() {
    fetch('<?php echo $this->Url->build(['action' => 'maskingStatus']); ?>')
        .then(response => response.json())
        .then(data => {
            // Update UI if status has changed
            console.log('Masking status:', data);
        })
        .catch(error => {
            console.error('Error checking masking status:', error);
        });
}, 30000);

// Add loading state to forms
document.querySelectorAll('.masking-control-form').forEach(form => {
    form.addEventListener('submit', function() {
        const button = this.querySelector('button[type="submit"]');
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    });
});
</script>