<?php
/**
 * @var \App\View\AppView $this
 * @var array $securitySettings
 * @var int $hospitalId
 */

$this->assign('title', 'Security Policies');

// Helper function to get nested value with fallback
function getSettingValue($settings, $path, $default = '') {
    $keys = explode('.', $path);
    $value = $settings;
    
    foreach ($keys as $key) {
        if (isset($value[$key])) {
            $value = $value[$key];
        } else {
            return $default;
        }
    }
    
    return $value;
}
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>
                    <i class="fas fa-shield-alt text-success"></i> Security Policies
                </h3>
                <?php echo $this->Html->link(
                    '<i class="fas fa-arrow-left"></i> Back to Settings',
                    array('action' => 'index'),
                    array('class' => 'btn btn-outline-secondary', 'escape' => false)
                ); ?>
            </div>

            <?php echo $this->Form->create(null, array('url' => array('action' => 'security'))); ?>
            
            <!-- Session Management -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-clock"></i> Session Management</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Session Timeout (minutes)</label>
                                <?php echo $this->Form->number(
                                    'session_timeout',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($securitySettings, 'session_timeout', 30),
                                        'min' => 5,
                                        'max' => 480
                                    )
                                ); ?>
                                <small class="form-text text-muted">Automatically log out users after this period of inactivity</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Remember Me Duration (days)</label>
                                <?php echo $this->Form->number(
                                    'remember_me_days',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($securitySettings, 'remember_me_days', 30),
                                        'min' => 1,
                                        'max' => 365
                                    )
                                ); ?>
                                <small class="form-text text-muted">How long "Remember Me" stays active</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Policies -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-key"></i> Password Policies</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Minimum Password Length</label>
                                <?php echo $this->Form->number(
                                    'password_min_length',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($securitySettings, 'password_min_length', 8),
                                        'min' => 6,
                                        'max' => 32
                                    )
                                ); ?>
                                <small class="form-text text-muted">Minimum number of characters required</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password Expiry (days)</label>
                                <?php echo $this->Form->number(
                                    'password_expiry_days',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($securitySettings, 'password_expiry_days', 90),
                                        'min' => 0,
                                        'max' => 365
                                    )
                                ); ?>
                                <small class="form-text text-muted">Force password change after this many days (0 = never)</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <?php echo $this->Form->checkbox(
                                    'password_require_uppercase',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($securitySettings, 'password_require_uppercase', true)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    Require uppercase letters
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <?php echo $this->Form->checkbox(
                                    'password_require_numbers',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($securitySettings, 'password_require_numbers', true)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    Require numbers
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <?php echo $this->Form->checkbox(
                                    'password_require_special',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($securitySettings, 'password_require_special', true)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    Require special characters
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-mobile-alt"></i> Two-Factor Authentication</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <?php echo $this->Form->checkbox(
                                    'two_factor_enabled',
                                    array(
                                        'class' => 'form-check-input',
                                        'id' => 'twoFactorEnabled',
                                        'checked' => getSettingValue($securitySettings, 'two_factor_enabled', false)
                                    )
                                ); ?>
                                <label class="form-check-label" for="twoFactorEnabled">
                                    <strong>Enable Two-Factor Authentication</strong>
                                </label>
                                <br>
                                <small class="form-text text-muted">Require users to verify identity with a second factor</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">2FA Method</label>
                                <?php echo $this->Form->select(
                                    'two_factor_method',
                                    array(
                                        'totp' => 'Authenticator App (TOTP)',
                                        'sms' => 'SMS Code',
                                        'email' => 'Email Code'
                                    ),
                                    array(
                                        'class' => 'form-select',
                                        'value' => getSettingValue($securitySettings, 'two_factor_method', 'totp')
                                    )
                                ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Login Security -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-lock"></i> Login Security</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Max Login Attempts</label>
                                <?php echo $this->Form->number(
                                    'max_login_attempts',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($securitySettings, 'max_login_attempts', 5),
                                        'min' => 1,
                                        'max' => 20
                                    )
                                ); ?>
                                <small class="form-text text-muted">Lock account after this many failed attempts</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Lockout Duration (minutes)</label>
                                <?php echo $this->Form->number(
                                    'lockout_duration',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($securitySettings, 'lockout_duration', 30),
                                        'min' => 5,
                                        'max' => 1440
                                    )
                                ); ?>
                                <small class="form-text text-muted">How long to lock the account after max attempts</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <?php echo $this->Form->checkbox(
                                    'require_email_verification',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($securitySettings, 'require_email_verification', true)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    Require email verification for new accounts
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <?php echo $this->Form->checkbox(
                                    'log_all_logins',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($securitySettings, 'log_all_logins', true)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    Log all login attempts (successful and failed)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IP Restrictions -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-network-wired"></i> IP Restrictions</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> Be careful with IP restrictions. You could lock yourself out!
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Allowed IP Addresses</label>
                                <?php echo $this->Form->textarea(
                                    'allowed_ips',
                                    array(
                                        'class' => 'form-control',
                                        'rows' => 4,
                                        'value' => getSettingValue($securitySettings, 'allowed_ips', ''),
                                        'placeholder' => "Enter one IP address or CIDR range per line:\n192.168.1.1\n10.0.0.0/24\n172.16.0.0/16"
                                    )
                                ); ?>
                                <small class="form-text text-muted">Leave empty to allow all IPs. Enter one IP or CIDR range per line.</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <?php echo $this->Form->checkbox(
                                    'ip_restrictions_enabled',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($securitySettings, 'ip_restrictions_enabled', false)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    <strong>Enable IP Restrictions</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <?php echo $this->Html->link(
                                '<i class="fas fa-arrow-left"></i> Cancel',
                                array('action' => 'index'),
                                array('class' => 'btn btn-outline-secondary', 'escape' => false)
                            ); ?>
                        </div>
                        <div>
                            <?php echo $this->Form->button(
                                '<i class="fas fa-save"></i> Save Security Settings',
                                array('class' => 'btn btn-success', 'type' => 'submit', 'escape' => false)
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>
