<?php
/**
 * @var \App\View\AppView $this
 * @var array $notificationSettings
 * @var int $hospitalId
 */

$this->assign('title', 'Notification Settings');
$this->layout = 'admin';

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
                    <i class="fas fa-bell text-warning"></i> Notification Settings
                </h3>
                <?php echo $this->Html->link(
                    '<i class="fas fa-arrow-left"></i> Back to Settings',
                    array('action' => 'index'),
                    array('class' => 'btn btn-outline-secondary', 'escape' => false)
                ); ?>
            </div>

            <?php echo $this->Form->create(null, array('url' => array('action' => 'notifications'))); ?>
            
            <!-- Email Configuration -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-envelope"></i> Email Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email Provider</label>
                                <?php echo $this->Form->select(
                                    'email.provider',
                                    array(
                                        'smtp' => 'SMTP Server',
                                        'sendgrid' => 'SendGrid',
                                        'mailgun' => 'Mailgun',
                                        'ses' => 'Amazon SES'
                                    ),
                                    array(
                                        'class' => 'form-select',
                                        'id' => 'emailProvider',
                                        'value' => getSettingValue($notificationSettings, 'email.provider', 'smtp')
                                    )
                                ); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4 pt-2">
                                <?php echo $this->Form->checkbox(
                                    'email.enabled',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($notificationSettings, 'email.enabled', true)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    <strong>Enable Email Notifications</strong>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="smtpSettings" class="smtp-settings">
                        <hr>
                        <h6>SMTP Settings</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">SMTP Host</label>
                                    <?php echo $this->Form->text(
                                        'email.host',
                                        array(
                                            'class' => 'form-control',
                                            'value' => getSettingValue($notificationSettings, 'email.host', ''),
                                            'placeholder' => 'smtp.example.com'
                                        )
                                    ); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Port</label>
                                    <?php echo $this->Form->number(
                                        'email.port',
                                        array(
                                            'class' => 'form-control',
                                            'value' => getSettingValue($notificationSettings, 'email.port', 587),
                                            'min' => 1,
                                            'max' => 65535
                                        )
                                    ); ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Encryption</label>
                                    <?php echo $this->Form->select(
                                        'email.encryption',
                                        array('tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'),
                                        array(
                                            'class' => 'form-select',
                                            'value' => getSettingValue($notificationSettings, 'email.encryption', 'tls')
                                        )
                                    ); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <?php echo $this->Form->text(
                                        'email.username',
                                        array(
                                            'class' => 'form-control',
                                            'value' => getSettingValue($notificationSettings, 'email.username', ''),
                                            'autocomplete' => 'off'
                                        )
                                    ); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <?php echo $this->Form->password(
                                        'email.password',
                                        array(
                                            'class' => 'form-control',
                                            'value' => getSettingValue($notificationSettings, 'email.password', '') ? '••••••••••••' : '',
                                            'autocomplete' => 'new-password'
                                        )
                                    ); ?>
                                    <small class="form-text text-muted">Stored encrypted</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="apiSettings" class="api-settings" style="display: none;">
                        <hr>
                        <h6>API Settings</h6>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">API Key</label>
                                    <?php echo $this->Form->password(
                                        'email.api_key',
                                        array(
                                            'class' => 'form-control',
                                            'value' => getSettingValue($notificationSettings, 'email.api_key', '') ? '••••••••••••' : '',
                                            'placeholder' => 'Enter your API key'
                                        )
                                    ); ?>
                                    <small class="form-text text-muted">Stored encrypted</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">From Name</label>
                                <?php echo $this->Form->text(
                                    'email.from_name',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($notificationSettings, 'email.from_name', 'MEG System'),
                                        'placeholder' => 'Hospital Name'
                                    )
                                ); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">From Email</label>
                                <?php echo $this->Form->email(
                                    'email.from_email',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($notificationSettings, 'email.from_email', 'noreply@example.com'),
                                        'placeholder' => 'noreply@hospital.com'
                                    )
                                ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SMS Configuration -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-sms"></i> SMS Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">SMS Provider</label>
                                <?php echo $this->Form->select(
                                    'sms.provider',
                                    array(
                                        'twilio' => 'Twilio',
                                        'nexmo' => 'Vonage (Nexmo)',
                                        'sns' => 'Amazon SNS'
                                    ),
                                    array(
                                        'class' => 'form-select',
                                        'value' => getSettingValue($notificationSettings, 'sms.provider', 'twilio')
                                    )
                                ); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4 pt-2">
                                <?php echo $this->Form->checkbox(
                                    'sms.enabled',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($notificationSettings, 'sms.enabled', false)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    <strong>Enable SMS Notifications</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Account SID / API Key</label>
                                <?php echo $this->Form->text(
                                    'sms.api_key',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($notificationSettings, 'sms.api_key', ''),
                                        'placeholder' => 'Your Account SID or API Key'
                                    )
                                ); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Auth Token / API Secret</label>
                                <?php echo $this->Form->password(
                                    'sms.api_secret',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($notificationSettings, 'sms.api_secret', '') ? '••••••••••••' : '',
                                        'placeholder' => 'Your Auth Token or API Secret'
                                    )
                                ); ?>
                                <small class="form-text text-muted">Stored encrypted</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sender Phone Number</label>
                                <?php echo $this->Form->tel(
                                    'sms.from_number',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($notificationSettings, 'sms.from_number', ''),
                                        'placeholder' => '+1234567890'
                                    )
                                ); ?>
                                <small class="form-text text-muted">Include country code (e.g., +1 for US)</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-sliders-h"></i> Notification Preferences</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Send Notifications For:</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <?php echo $this->Form->checkbox(
                                    'notify_new_case',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($notificationSettings, 'notify_new_case', true)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    New Case Assignments
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <?php echo $this->Form->checkbox(
                                    'notify_case_status',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($notificationSettings, 'notify_case_status', true)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    Case Status Changes
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <?php echo $this->Form->checkbox(
                                    'notify_ai_report',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($notificationSettings, 'notify_ai_report', true)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    AI Report Completion
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <?php echo $this->Form->checkbox(
                                    'notify_comments',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($notificationSettings, 'notify_comments', true)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    New Comments
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <?php echo $this->Form->checkbox(
                                    'notify_budget_alert',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($notificationSettings, 'notify_budget_alert', true)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    Budget Alerts
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mb-3">
                                <?php echo $this->Form->checkbox(
                                    'notify_system_alerts',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($notificationSettings, 'notify_system_alerts', true)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    System Alerts
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
                                '<i class="fas fa-save"></i> Save Notification Settings',
                                array('class' => 'btn btn-warning', 'type' => 'submit', 'escapeTitle' => false)
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const providerSelect = document.getElementById('emailProvider');
    const smtpSettings = document.getElementById('smtpSettings');
    const apiSettings = document.getElementById('apiSettings');
    
    function toggleEmailSettings() {
        const provider = providerSelect.value;
        
        if (provider === 'smtp') {
            smtpSettings.style.display = 'block';
            apiSettings.style.display = 'none';
        } else {
            smtpSettings.style.display = 'none';
            apiSettings.style.display = 'block';
        }
    }
    
    providerSelect.addEventListener('change', toggleEmailSettings);
    toggleEmailSettings();
});
</script>
