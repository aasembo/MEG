<?php
/**
 * @var \App\View\AppView $this
 * @var array $aiSettings
 * @var int $hospitalId
 */

$this->assign('title', 'AI Provider Configuration');
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
                    <i class="fas fa-brain text-primary"></i> AI Provider Configuration
                </h3>
                <?php echo $this->Html->link(
                    '<i class="fas fa-arrow-left"></i> Back to Settings',
                    array('action' => 'index'),
                    array('class' => 'btn btn-outline-secondary', 'escape' => false)
                ); ?>
            </div>

            <?php echo $this->Form->create(null, array('url' => array('action' => 'ai'))); ?>
            
            <!-- Important Notice -->
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Important:</strong> Only one AI provider can be active at a time. Enabling one provider will automatically disable the other.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            
            <!-- General AI Settings -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-toggle-on"></i> General AI Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Default AI Provider</label>
                                <?php echo $this->Form->select(
                                    'general.default_provider',
                                    array('openai' => 'OpenAI', 'gemini' => 'Google Gemini'),
                                    array(
                                        'class' => 'form-select',
                                        'value' => getSettingValue($aiSettings, 'default_provider', 'openai'),
                                        'readonly' => 'readonly',
                                        'disabled' => true
                                    )
                                ); ?>
                                <?php echo $this->Form->hidden('general.default_provider', array(
                                    'value' => getSettingValue($aiSettings, 'default_provider', 'openai')
                                )); ?>
                                <small class="form-text text-muted">Automatically set based on enabled provider</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Max Retries</label>
                                <?php echo $this->Form->number(
                                    'general.max_retries',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($aiSettings, 'max_retries', 3),
                                        'min' => 0,
                                        'max' => 10
                                    )
                                ); ?>
                                <small class="form-text text-muted">Number of retry attempts on API failure</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Request Timeout (seconds)</label>
                                <?php echo $this->Form->number(
                                    'general.timeout_seconds',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($aiSettings, 'timeout_seconds', 30),
                                        'min' => 5,
                                        'max' => 300
                                    )
                                ); ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4 pt-2">
                                <?php echo $this->Form->checkbox(
                                    'general.fallback_enabled',
                                    array(
                                        'class' => 'form-check-input',
                                        'checked' => getSettingValue($aiSettings, 'fallback_enabled', true)
                                    )
                                ); ?>
                                <label class="form-check-label">
                                    Enable automatic fallback to secondary provider on failure
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OpenAI Configuration -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: #10a37f; color: white;">
                    <h5 class="mb-0"><i class="fas fa-robot"></i> OpenAI Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> OpenAI provides powerful models like GPT-4 but is more expensive. Best for complex analysis requiring high accuracy.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">API Key <span class="text-danger">*</span></label>
                                <?php echo $this->Form->password(
                                    'openai.api_key',
                                    array(
                                        'class' => 'form-control',
                                        'placeholder' => 'sk-...',
                                        'value' => getSettingValue($aiSettings, 'openai.api_key', '') ? '••••••••••••' : ''
                                    )
                                ); ?>
                                <small class="form-text text-muted">Your OpenAI API key (stored encrypted)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Model</label>
                                <?php echo $this->Form->select(
                                    'openai.model',
                                    array(
                                        'gpt-4' => 'GPT-4 (Most powerful)',
                                        'gpt-4-turbo' => 'GPT-4 Turbo (Faster, cheaper)',
                                        'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Fast, economical)'
                                    ),
                                    array(
                                        'class' => 'form-select',
                                        'value' => getSettingValue($aiSettings, 'openai.model', 'gpt-4')
                                    )
                                ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Temperature</label>
                                <?php echo $this->Form->number(
                                    'openai.temperature',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($aiSettings, 'openai.temperature', 0.7),
                                        'step' => 0.1,
                                        'min' => 0,
                                        'max' => 2
                                    )
                                ); ?>
                                <small class="form-text text-muted">0 = deterministic, 2 = creative</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Max Tokens</label>
                                <?php echo $this->Form->number(
                                    'openai.max_tokens',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($aiSettings, 'openai.max_tokens', 2000),
                                        'min' => 100,
                                        'max' => 8000
                                    )
                                ); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4 pt-2">
                                <?php echo $this->Form->checkbox(
                                    'openai.enabled',
                                    array(
                                        'class' => 'form-check-input ai-provider-toggle',
                                        'id' => 'openai-enabled',
                                        'data-provider' => 'openai',
                                        'checked' => getSettingValue($aiSettings, 'openai.enabled', true)
                                    )
                                ); ?>
                                <label class="form-check-label" for="openai-enabled">
                                    <strong>Enable OpenAI</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Gemini Configuration -->
            <div class="card mb-4">
                <div class="card-header" style="background-color: #4285f4; color: white;">
                    <h5 class="mb-0"><i class="fas fa-gem"></i> Google Gemini Configuration</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-dollar-sign"></i> Google Gemini is approximately 24x cheaper than GPT-4 while maintaining good quality. Recommended for cost-effective operations.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">API Key <span class="text-danger">*</span></label>
                                <?php echo $this->Form->password(
                                    'gemini.api_key',
                                    array(
                                        'class' => 'form-control',
                                        'placeholder' => 'AIza...',
                                        'value' => getSettingValue($aiSettings, 'gemini.api_key', '') ? '••••••••••••' : ''
                                    )
                                ); ?>
                                <small class="form-text text-muted">Your Google Gemini API key (stored encrypted)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Model</label>
                                <?php echo $this->Form->select(
                                    'gemini.model',
                                    array(
                                        'gemini-2.5-flash' => 'Gemini 2.5 Flash (Fastest)',
                                        'gemini-2.5-pro' => 'Gemini Pro (Standard)'
                                    ),
                                    array(
                                        'class' => 'form-select',
                                        'value' => getSettingValue($aiSettings, 'gemini.model', 'gemini-2.5-flash')
                                    )
                                ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Temperature</label>
                                <?php echo $this->Form->number(
                                    'gemini.temperature',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($aiSettings, 'gemini.temperature', 0.7),
                                        'step' => 0.1,
                                        'min' => 0,
                                        'max' => 2
                                    )
                                ); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Max Tokens</label>
                                <?php echo $this->Form->number(
                                    'gemini.max_tokens',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($aiSettings, 'gemini.max_tokens', 2000),
                                        'min' => 100,
                                        'max' => 8000
                                    )
                                ); ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check mt-4 pt-2">
                                <?php echo $this->Form->checkbox(
                                    'gemini.enabled',
                                    array(
                                        'class' => 'form-check-input ai-provider-toggle',
                                        'id' => 'gemini-enabled',
                                        'data-provider' => 'gemini',
                                        'checked' => getSettingValue($aiSettings, 'gemini.enabled', true)
                                    )
                                ); ?>
                                <label class="form-check-label" for="gemini-enabled">
                                    <strong>Enable Gemini</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget Management -->
            <div class="card mb-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-dollar-sign"></i> Budget Management</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Monthly Budget Limit (USD)</label>
                                <?php echo $this->Form->number(
                                    'budget.monthly_limit',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($aiSettings, 'budget.monthly_limit', 1000),
                                        'step' => 0.01,
                                        'min' => 0
                                    )
                                ); ?>
                                <small class="form-text text-muted">Maximum AI spending per month for this hospital</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Alert Threshold (%)</label>
                                <?php echo $this->Form->number(
                                    'budget.alert_threshold',
                                    array(
                                        'class' => 'form-control',
                                        'value' => getSettingValue($aiSettings, 'budget.alert_threshold', 80),
                                        'min' => 0,
                                        'max' => 100
                                    )
                                ); ?>
                                <small class="form-text text-muted">Send alert when spending reaches this percentage</small>
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
                                '<i class="fas fa-save"></i> Save AI Settings',
                                array('class' => 'btn btn-primary', 'type' => 'submit', 'escapeTitle' => false)
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
    const openaiCheckbox = document.getElementById('openai-enabled');
    const geminiCheckbox = document.getElementById('gemini-enabled');
    const defaultProviderSelect = document.querySelector('select[name="general[default_provider]"]');
    const defaultProviderHidden = document.querySelector('input[name="general[default_provider]"]');
    
    if (!openaiCheckbox || !geminiCheckbox) {
        return;
    }
    
    // Function to update default provider based on enabled provider
    function updateDefaultProvider(enabledProvider) {
        if (defaultProviderSelect) {
            defaultProviderSelect.value = enabledProvider;
        }
        if (defaultProviderHidden) {
            defaultProviderHidden.value = enabledProvider;
        }
    }
    
    // Function to handle provider toggle
    function handleProviderToggle(enabledCheckbox, disabledCheckbox, enabledProvider) {
        if (enabledCheckbox.checked) {
            // Show confirmation if the other provider is already enabled
            if (disabledCheckbox.checked) {
                const otherProvider = disabledCheckbox.dataset.provider;
                const otherProviderName = otherProvider.charAt(0).toUpperCase() + otherProvider.slice(1);
                const currentProviderName = enabledProvider.charAt(0).toUpperCase() + enabledProvider.slice(1);
                
                if (confirm(`Enabling ${currentProviderName} will automatically disable ${otherProviderName}. Continue?`)) {
                    disabledCheckbox.checked = false;
                    updateDefaultProvider(enabledProvider);
                } else {
                    // User cancelled, revert the change
                    enabledCheckbox.checked = false;
                }
            } else {
                // No conflict, just update default provider
                updateDefaultProvider(enabledProvider);
            }
        }
    }
    
    // OpenAI checkbox change handler
    openaiCheckbox.addEventListener('change', function() {
        handleProviderToggle(openaiCheckbox, geminiCheckbox, 'openai');
    });
    
    // Gemini checkbox change handler
    geminiCheckbox.addEventListener('change', function() {
        handleProviderToggle(geminiCheckbox, openaiCheckbox, 'gemini');
    });
    
    // Form submission validation
    const form = openaiCheckbox.closest('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (openaiCheckbox.checked && geminiCheckbox.checked) {
                e.preventDefault();
                alert('Only one AI provider can be enabled at a time. Please disable one provider before submitting.');
                return false;
            }
            
            // Check if at least one provider is enabled
            if (!openaiCheckbox.checked && !geminiCheckbox.checked) {
                if (!confirm('No AI provider is enabled. AI features will not work. Continue anyway?')) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
    
    // Initialize: ensure only one is checked on page load
    if (openaiCheckbox.checked && geminiCheckbox.checked) {
        // Default to OpenAI if both are somehow checked
        geminiCheckbox.checked = false;
        updateDefaultProvider('openai');
    } else if (openaiCheckbox.checked) {
        updateDefaultProvider('openai');
    } else if (geminiCheckbox.checked) {
        updateDefaultProvider('gemini');
    }
});
</script>
