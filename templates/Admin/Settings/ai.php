<?php
/**
 * @var \App\View\AppView $this
 * @var array $aiSettings
 * @var int $hospitalId
 */

$this->assign('title', 'AI Provider Configuration');

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
                                <i class="fas fa-brain"></i>
                            </div>
                        </div>
                        <div>
                            <h2 class="mb-2 fw-bold text-white">
                                AI Provider Configuration
                            </h2>
                            <p class="mb-0 text-white-50 fs-5">
                                <i class="fas fa-cogs me-2"></i>Configure OpenAI and Google Gemini AI providers
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

    <?php echo $this->Form->create(null, ['url' => ['action' => 'ai']]); ?>
    
    <!-- Important Notice -->
    <div class="alert alert-warning border-0 shadow mb-4" role="alert">
        <div class="d-flex align-items-center">
            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h6 class="mb-1 fw-bold">Provider Mutual Exclusivity</h6>
                <p class="mb-0">Only one AI provider can be active at a time. Enabling one provider will automatically disable the other.</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- General AI Settings -->
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-sliders-h text-primary me-2"></i>General AI Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-robot me-1 text-primary"></i>Default AI Provider
                            </label>
                            <?php echo $this->Form->select(
                                'general.default_provider',
                                ['openai' => 'OpenAI', 'gemini' => 'Google Gemini'],
                                [
                                    'class' => 'form-select',
                                    'value' => getSettingValue($aiSettings, 'default_provider', 'openai'),
                                    'readonly' => 'readonly',
                                    'disabled' => true
                                ]
                            ); ?>
                            <?php echo $this->Form->hidden('general.default_provider', [
                                'value' => getSettingValue($aiSettings, 'default_provider', 'openai')
                            ]); ?>
                            <div class="form-text">Automatically set based on enabled provider</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-redo me-1 text-success"></i>Max Retries
                            </label>
                            <?php echo $this->Form->number(
                                'general.max_retries',
                                [
                                    'class' => 'form-control',
                                    'value' => getSettingValue($aiSettings, 'max_retries', 3),
                                    'min' => 0,
                                    'max' => 10
                                ]
                            ); ?>
                            <div class="form-text">Number of retry attempts on API failure</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- OpenAI Configuration -->
        <div class="col-lg-6">
            <div class="card border-0 shadow h-100 border-start border-primary border-4">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-brain text-primary me-2"></i>OpenAI Configuration
                        </h5>
                        <div class="form-check form-switch">
                            <?php echo $this->Form->checkbox(
                                'openai.enabled',
                                [
                                    'class' => 'form-check-input ai-provider-toggle',
                                    'id' => 'openai-enabled',
                                    'data-provider' => 'openai',
                                    'checked' => getSettingValue($aiSettings, 'openai.enabled', true)
                                ]
                            ); ?>
                            <label class="form-check-label fw-semibold text-primary" for="openai-enabled">
                                <strong>Enable OpenAI</strong>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> OpenAI provides powerful models like GPT-4 but is more expensive. Best for complex analysis requiring high accuracy.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-key me-1 text-warning"></i>API Key <span class="text-danger">*</span>
                        </label>
                        <?php echo $this->Form->password(
                            'openai.api_key',
                            [
                                'class' => 'form-control',
                                'placeholder' => 'sk-...',
                                'value' => getSettingValue($aiSettings, 'openai.api_key', '') ? '••••••••••••' : ''
                            ]
                        ); ?>
                        <div class="form-text">Your OpenAI API key (stored encrypted)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-cog me-1 text-info"></i>Model
                        </label>
                        <?php echo $this->Form->select(
                            'openai.model',
                            [
                                'gpt-4' => 'GPT-4 (Most powerful)',
                                'gpt-4-turbo' => 'GPT-4 Turbo (Faster, cheaper)',
                                'gpt-3.5-turbo' => 'GPT-3.5 Turbo (Fast, economical)'
                            ],
                            [
                                'class' => 'form-select',
                                'value' => getSettingValue($aiSettings, 'openai.model', 'gpt-4')
                            ]
                        ); ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-thermometer-half me-1 text-danger"></i>Temperature
                            </label>
                            <?php echo $this->Form->number(
                                'openai.temperature',
                                [
                                    'class' => 'form-control',
                                    'value' => getSettingValue($aiSettings, 'openai.temperature', 0.7),
                                    'step' => 0.1,
                                    'min' => 0,
                                    'max' => 2
                                ]
                            ); ?>
                            <div class="form-text small">0 = deterministic, 2 = creative</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-hashtag me-1 text-secondary"></i>Max Tokens
                            </label>
                            <?php echo $this->Form->number(
                                'openai.max_tokens',
                                [
                                    'class' => 'form-control',
                                    'value' => getSettingValue($aiSettings, 'openai.max_tokens', 2000),
                                    'min' => 100,
                                    'max' => 8000
                                ]
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Google Gemini Configuration -->
        <div class="col-lg-6">
            <div class="card border-0 shadow h-100 border-start border-success border-4">
                <div class="card-header bg-light py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-gem text-success me-2"></i>Google Gemini Configuration
                        </h5>
                        <div class="form-check form-switch">
                            <?php echo $this->Form->checkbox(
                                'gemini.enabled',
                                [
                                    'class' => 'form-check-input ai-provider-toggle',
                                    'id' => 'gemini-enabled',
                                    'data-provider' => 'gemini',
                                    'checked' => getSettingValue($aiSettings, 'gemini.enabled', true)
                                ]
                            ); ?>
                            <label class="form-check-label fw-semibold text-success" for="gemini-enabled">
                                <strong>Enable Gemini</strong>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-success mb-3">
                        <i class="fas fa-dollar-sign"></i> Google Gemini is approximately 24x cheaper than GPT-4 while maintaining good quality. Recommended for cost-effective operations.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-key me-1 text-warning"></i>API Key <span class="text-danger">*</span>
                        </label>
                        <?php echo $this->Form->password(
                            'gemini.api_key',
                            [
                                'class' => 'form-control',
                                'placeholder' => 'AIza...',
                                'value' => getSettingValue($aiSettings, 'gemini.api_key', '') ? '••••••••••••' : ''
                            ]
                        ); ?>
                        <div class="form-text">Your Google Gemini API key (stored encrypted)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-cog me-1 text-info"></i>Model
                        </label>
                        <?php echo $this->Form->select(
                            'gemini.model',
                            [
                                'gemini-2.5-flash' => 'Gemini 2.5 Flash (Fastest)',
                                'gemini-2.5-pro' => 'Gemini Pro (Standard)'
                            ],
                            [
                                'class' => 'form-select',
                                'value' => getSettingValue($aiSettings, 'gemini.model', 'gemini-2.5-flash')
                            ]
                        ); ?>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-thermometer-half me-1 text-danger"></i>Temperature
                            </label>
                            <?php echo $this->Form->number(
                                'gemini.temperature',
                                [
                                    'class' => 'form-control',
                                    'value' => getSettingValue($aiSettings, 'gemini.temperature', 0.7),
                                    'step' => 0.1,
                                    'min' => 0,
                                    'max' => 2
                                ]
                            ); ?>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-hashtag me-1 text-secondary"></i>Max Tokens
                            </label>
                            <?php echo $this->Form->number(
                                'gemini.max_tokens',
                                [
                                    'class' => 'form-control',
                                    'value' => getSettingValue($aiSettings, 'gemini.max_tokens', 2000),
                                    'min' => 100,
                                    'max' => 8000
                                ]
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Management -->
        <div class="col-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-light py-3">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-dollar-sign text-success me-2"></i>Budget Management
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-calendar-alt me-1 text-primary"></i>Monthly Budget Limit (USD)
                            </label>
                            <?php echo $this->Form->number(
                                'budget.monthly_limit',
                                [
                                    'class' => 'form-control',
                                    'value' => getSettingValue($aiSettings, 'budget.monthly_limit', 1000),
                                    'step' => 0.01,
                                    'min' => 0
                                ]
                            ); ?>
                            <div class="form-text">Maximum AI spending per month for this hospital</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-bell me-1 text-warning"></i>Alert Threshold (%)
                            </label>
                            <?php echo $this->Form->number(
                                'budget.alert_threshold',
                                [
                                    'class' => 'form-control',
                                    'value' => getSettingValue($aiSettings, 'budget.alert_threshold', 80),
                                    'min' => 0,
                                    'max' => 100
                                ]
                            ); ?>
                            <div class="form-text">Send alert when spending reaches this percentage</div>
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
                    <h6 class="mb-1 fw-bold">Ready to save your AI configuration?</h6>
                    <p class="mb-0 text-muted">Changes will be applied immediately to your hospital's AI services.</p>
                </div>
                <div>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times me-2"></i>Cancel',
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-secondary me-3', 'escape' => false]
                    ); ?>
                    <?php echo $this->Form->button(
                        '<i class="fas fa-save me-2"></i>Save AI Settings',
                        ['class' => 'btn btn-primary btn-lg', 'type' => 'submit', 'escapeTitle' => false]
                    ); ?>
                </div>
            </div>
        </div>
    </div>

    <?php echo $this->Form->end(); ?>
</div>

<!-- JavaScript for Provider Mutual Exclusivity -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('AI Settings page loaded');
    
    const openaiCheckbox = document.getElementById('openai-enabled');
    const geminiCheckbox = document.getElementById('gemini-enabled');
    const defaultProviderSelect = document.querySelector('select[name="general[default_provider]"]');
    const defaultProviderHidden = document.querySelector('input[name="general[default_provider]"]');
    
    console.log('OpenAI checkbox found:', openaiCheckbox);
    console.log('Gemini checkbox found:', geminiCheckbox);
    
    if (!openaiCheckbox || !geminiCheckbox) {
        console.error('Could not find provider checkboxes');
        return;
    }
    
    // Function to update default provider based on enabled provider
    function updateDefaultProvider(enabledProvider) {
        console.log('Updating default provider to:', enabledProvider);
        if (defaultProviderSelect) {
            defaultProviderSelect.value = enabledProvider;
        }
        if (defaultProviderHidden) {
            defaultProviderHidden.value = enabledProvider;
        }
    }
    
    // Function to handle provider toggle
    function handleProviderToggle(enabledCheckbox, disabledCheckbox, enabledProvider) {
        console.log('Provider toggle triggered:', enabledProvider, 'enabled:', enabledCheckbox.checked);
        
        if (enabledCheckbox.checked) {
            // Show confirmation if the other provider is already enabled
            if (disabledCheckbox.checked) {
                const otherProvider = disabledCheckbox.dataset.provider;
                const otherProviderName = otherProvider.charAt(0).toUpperCase() + otherProvider.slice(1);
                const currentProviderName = enabledProvider.charAt(0).toUpperCase() + enabledProvider.slice(1);
                
                if (confirm(`Enabling ${currentProviderName} will automatically disable ${otherProviderName}. Continue?`)) {
                    disabledCheckbox.checked = false;
                    updateDefaultProvider(enabledProvider);
                    console.log('Provider switch confirmed');
                } else {
                    // User cancelled, revert the change
                    enabledCheckbox.checked = false;
                    console.log('Provider switch cancelled');
                }
            } else {
                // No conflict, just update default provider
                updateDefaultProvider(enabledProvider);
            }
        }
    }
    
    // OpenAI checkbox change handler
    openaiCheckbox.addEventListener('change', function() {
        console.log('OpenAI checkbox changed');
        handleProviderToggle(openaiCheckbox, geminiCheckbox, 'openai');
    });
    
    // Gemini checkbox change handler
    geminiCheckbox.addEventListener('change', function() {
        console.log('Gemini checkbox changed');
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
        console.log('Initialized with OpenAI only');
    } else if (openaiCheckbox.checked) {
        updateDefaultProvider('openai');
        console.log('Initialized with OpenAI enabled');
    } else if (geminiCheckbox.checked) {
        updateDefaultProvider('gemini');
        console.log('Initialized with Gemini enabled');
    }
});
</script>
