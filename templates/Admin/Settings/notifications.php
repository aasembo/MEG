<?php
/**
 * @var \App\View\AppView $this
 * @var array $notificationSettings
 * @var int $hospitalId
 */

$this->assign('title', 'Notification Settings');

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

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-dark text-warning p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 2rem;">
                                <i class="fas fa-bell"></i>
                            </div>
                        </div>
                        <div>
                            <h2 class="mb-2 fw-bold text-white">
                                Notification Settings
                            </h2>
                            <p class="mb-0 text-white-50 fs-5">
                                <i class="fas fa-envelope me-2"></i>Configure email and SMS notification providers
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-left me-2"></i>Back to Settings',
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-warning btn-lg', 'escape' => false]
                    ); ?>
                </div>
            </div>
        </div>
    </div>

    <?php echo $this->Form->create(null, ['url' => ['action' => 'notifications']]); ?>
    
    <!-- Notification Info -->
    <div class="alert alert-info border-0 shadow mb-4" role="alert">
        <div class="d-flex align-items-center">
            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                <i class="fas fa-info-circle"></i>
            </div>
            <div>
                <h6 class="mb-1 fw-bold">Notification Configuration</h6>
                <p class="mb-0">Configure email and SMS providers to enable system notifications for your hospital staff and patients.</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Email Configuration -->
        <div class="col-lg-6">
            <div class="card border-0 shadow h-100 border-start border-primary border-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-envelope text-primary me-2"></i>Email Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-server me-1 text-info"></i>SMTP Host
                        </label>
                        <?php echo $this->Form->control('email.host', [
                            'type' => 'text',
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => 'smtp.gmail.com',
                            'value' => getSettingValue($notificationSettings, 'email.host', ''),
                            'div' => false
                        ]); ?>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>Your email provider's SMTP server
                        </div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-plug me-1 text-success"></i>Port
                            </label>
                            <?php echo $this->Form->control('email.port', [
                                'type' => 'number',
                                'class' => 'form-control',
                                'label' => false,
                                'value' => getSettingValue($notificationSettings, 'email.port', 587),
                                'min' => 1,
                                'max' => 65535,
                                'div' => false
                            ]); ?>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-shield-alt me-1 text-warning"></i>Encryption
                            </label>
                            <?php echo $this->Form->control('email.encryption', [
                                'type' => 'select',
                                'options' => [
                                    'tls' => 'TLS (Recommended)',
                                    'ssl' => 'SSL',
                                    'none' => 'None'
                                ],
                                'class' => 'form-select',
                                'label' => false,
                                'value' => getSettingValue($notificationSettings, 'email.encryption', 'tls'),
                                'div' => false
                            ]); ?>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user me-1 text-primary"></i>Username
                        </label>
                        <?php echo $this->Form->control('email.username', [
                            'type' => 'email',
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => 'your-email@domain.com',
                            'value' => getSettingValue($notificationSettings, 'email.username', ''),
                            'div' => false
                        ]); ?>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-key me-1 text-danger"></i>Password
                        </label>
                        <?php echo $this->Form->control('email.password', [
                            'type' => 'password',
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => getSettingValue($notificationSettings, 'email.password') ? '••••••••••••' : 'Enter email password',
                            'div' => false
                        ]); ?>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>Password is stored encrypted
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-paper-plane me-1 text-success"></i>From Name
                        </label>
                        <?php echo $this->Form->control('email.from_name', [
                            'type' => 'text',
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => 'Hospital Notification System',
                            'value' => getSettingValue($notificationSettings, 'email.from_name', ''),
                            'div' => false
                        ]); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SMS Configuration -->
        <div class="col-lg-6">
            <div class="card border-0 shadow h-100 border-start border-success border-4">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-sms text-success me-2"></i>SMS Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-building me-1 text-info"></i>SMS Provider
                        </label>
                        <?php echo $this->Form->control('sms.provider', [
                            'type' => 'select',
                            'options' => [
                                'twilio' => 'Twilio (Recommended)',
                                'nexmo' => 'Vonage (Nexmo)',
                                'aws_sns' => 'AWS SNS',
                                'custom' => 'Custom Provider'
                            ],
                            'class' => 'form-select',
                            'label' => false,
                            'value' => getSettingValue($notificationSettings, 'sms.provider', 'twilio'),
                            'div' => false
                        ]); ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-key me-1 text-warning"></i>API Key / Account SID
                        </label>
                        <?php echo $this->Form->control('sms.api_key', [
                            'type' => 'password',
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => getSettingValue($notificationSettings, 'sms.api_key') ? '••••••••••••' : 'Enter API key',
                            'div' => false
                        ]); ?>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>API key is stored encrypted
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-lock me-1 text-danger"></i>API Secret / Auth Token
                        </label>
                        <?php echo $this->Form->control('sms.api_secret', [
                            'type' => 'password',
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => getSettingValue($notificationSettings, 'sms.api_secret') ? '••••••••••••' : 'Enter API secret',
                            'div' => false
                        ]); ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-phone me-1 text-primary"></i>From Number
                        </label>
                        <?php echo $this->Form->control('sms.from_number', [
                            'type' => 'tel',
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => '+1234567890',
                            'value' => getSettingValue($notificationSettings, 'sms.from_number', ''),
                            'div' => false
                        ]); ?>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>Your SMS provider's phone number
                        </div>
                    </div>
                    
                    <div class="form-check form-switch">
                        <?php echo $this->Form->control('sms.enabled', [
                            'type' => 'checkbox',
                            'class' => 'form-check-input',
                            'label' => false,
                            'checked' => getSettingValue($notificationSettings, 'sms.enabled', false),
                            'div' => false
                        ]); ?>
                        <label class="form-check-label fw-semibold">
                            <i class="fas fa-toggle-on me-1 text-success"></i>Enable SMS Notifications
                        </label>
                        <div class="form-text">SMS charges may apply from your provider</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Preferences -->
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-cogs text-info me-2"></i>Notification Preferences
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <div class="form-check form-switch mb-2">
                                    <?php echo $this->Form->control('email.case_notifications', [
                                        'type' => 'checkbox',
                                        'class' => 'form-check-input',
                                        'label' => false,
                                        'checked' => getSettingValue($notificationSettings, 'email.case_notifications', true),
                                        'div' => false
                                    ]); ?>
                                    <label class="form-check-label fw-semibold">
                                        <i class="fas fa-briefcase-medical me-1 text-primary"></i>Case Updates
                                    </label>
                                </div>
                                <div class="form-text">Email notifications for case status changes</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-warning bg-opacity-10 rounded">
                                <div class="form-check form-switch mb-2">
                                    <?php echo $this->Form->control('email.system_alerts', [
                                        'type' => 'checkbox',
                                        'class' => 'form-check-input',
                                        'label' => false,
                                        'checked' => getSettingValue($notificationSettings, 'email.system_alerts', true),
                                        'div' => false
                                    ]); ?>
                                    <label class="form-check-label fw-semibold">
                                        <i class="fas fa-exclamation-triangle me-1 text-warning"></i>System Alerts
                                    </label>
                                </div>
                                <div class="form-text">Critical system notifications and alerts</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <div class="form-check form-switch mb-2">
                                    <?php echo $this->Form->control('email.reminders', [
                                        'type' => 'checkbox',
                                        'class' => 'form-check-input',
                                        'label' => false,
                                        'checked' => getSettingValue($notificationSettings, 'email.reminders', false),
                                        'div' => false
                                    ]); ?>
                                    <label class="form-check-label fw-semibold">
                                        <i class="fas fa-clock me-1 text-success"></i>Appointment Reminders
                                    </label>
                                </div>
                                <div class="form-text">Email reminders for upcoming appointments</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="card border-0 shadow mt-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1 fw-bold">Ready to save your notification settings?</h6>
                    <p class="mb-0 text-muted">Changes will be applied immediately to your hospital's notification system.</p>
                </div>
                <div>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times me-2"></i>Cancel',
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-secondary me-3', 'escape' => false]
                    ); ?>
                    <?php echo $this->Form->button(
                        '<i class="fas fa-save me-2"></i>Save Notification Settings',
                        ['type' => 'submit', 'class' => 'btn btn-primary btn-lg', 'escapeTitle' => false]
                    ); ?>
                </div>
            </div>
        </div>
    </div>

    <?php echo $this->Form->end(); ?>
</div>