<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Hospital $hospital
 */
?>
<?php $this->assign('title', 'Add New Hospital'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-hospital-symbol me-2 text-primary"></i>Add New Hospital
        </h1>
        <p class="text-muted mb-0">Create a new hospital account</p>
    </div>
    <div>
        <?php echo $this->Html->link(
            '<i class="fas fa-arrow-left me-2"></i>Back to Hospitals',
            ['action' => 'index'],
            ['class' => 'btn btn-secondary', 'escape' => false]
        ) ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-hospital me-2"></i>Hospital Information
                </h5>
            </div>
            <div class="card-body">
                <?php echo $this->Form->create($hospital, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?php echo $this->Form->control('name', [
                            'type' => 'text',
                            'class' => 'form-control' . ($hospital->hasErrors('name') ? ' is-invalid' : ''),
                            'label' => [
                                'text' => 'Hospital Name <span class="text-danger">*</span>',
                                'escape' => false,
                                'class' => 'form-label'
                            ],
                            'required' => true,
                            'placeholder' => 'Enter hospital name',
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                            ]
                        ]) ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <?php echo $this->Form->control('subdomain', [
                            'type' => 'text',
                            'class' => 'form-control' . ($hospital->hasErrors('subdomain') ? ' is-invalid' : ''),
                            'label' => [
                                'text' => 'Subdomain <span class="text-danger">*</span>',
                                'escape' => false,
                                'class' => 'form-label'
                            ],
                            'required' => true,
                            'placeholder' => 'e.g., hospital-name',
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '<div class="input-group">{{content}}<span class="input-group-text">.meg.www</span></div>',
                                'input' => '<input type="{{type}}" name="{{name}}" {{attrs}}/>',
                                'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                            ]
                        ]) ?>
                        <div class="form-text">Used for hospital-specific URLs and identification</div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?php echo $this->Form->control('status', [
                            'type' => 'select',
                            'options' => [
                                'active' => 'Active',
                                'inactive' => 'Inactive'
                            ],
                            'class' => 'form-select' . ($hospital->hasErrors('status') ? ' is-invalid' : ''),
                            'label' => [
                                'text' => 'Status <span class="text-danger">*</span>',
                                'escape' => false,
                                'class' => 'form-label'
                            ],
                            'default' => 'active',
                            'required' => true,
                            'div' => false,
                            'templates' => [
                                'inputContainer' => '{{content}}',
                                'select' => '<select name="{{name}}" {{attrs}}>{{content}}</select>',
                                'error' => '<div class="invalid-feedback d-block">{{content}}</div>'
                            ]
                        ]) ?>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Preview URL</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">https://</span>
                            <input type="text" class="form-control bg-light" id="url-preview" readonly placeholder="subdomain.meg.www">
                        </div>
                        <div class="form-text">This will be the hospital's access URL</div>
                    </div>
                </div>
                
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times me-2"></i>Cancel',
                        ['action' => 'index'],
                        ['class' => 'btn btn-secondary', 'escape' => false]
                    ) ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Hospital
                    </button>
                </div>
            </div>
            
            <?php echo $this->Form->end() ?>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Hospital Guidelines -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Hospital Guidelines
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        <strong>Naming Convention</strong>
                    </div>
                    <small class="text-muted">Use clear, descriptive hospital names that are easily identifiable.</small>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-link text-info me-2"></i>
                        <strong>Subdomain Rules</strong>
                    </div>
                    <small class="text-muted">Only lowercase letters, numbers, and hyphens. Must be unique across all hospitals.</small>
                </div>
                
                <div class="mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-users text-primary me-2"></i>
                        <strong>User Management</strong>
                    </div>
                    <small class="text-muted">After creating the hospital, you can assign administrators and users.</small>
                </div>
                
                <div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-toggle-on text-warning me-2"></i>
                        <strong>Status Control</strong>
                    </div>
                    <small class="text-muted">Inactive hospitals cannot be accessed by users but data is preserved.</small>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-lightning-bolt me-2"></i>After Creation
                </h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item border-0 px-0">
                        <i class="fas fa-user-plus text-primary me-2"></i>
                        <small>Add hospital administrators</small>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <i class="fas fa-cog text-secondary me-2"></i>
                        <small>Configure hospital settings</small>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <i class="fas fa-users text-info me-2"></i>
                        <small>Invite staff members</small>
                    </div>
                    <div class="list-group-item border-0 px-0">
                        <i class="fas fa-chart-bar text-success me-2"></i>
                        <small>Monitor usage statistics</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation and URL preview
(function() {
    'use strict';
    
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
        
        // URL Preview functionality
        var subdomainInput = document.querySelector('input[name="subdomain"]');
        var urlPreview = document.getElementById('url-preview');
        
        if (subdomainInput && urlPreview) {
            function updatePreview() {
                var subdomain = subdomainInput.value.trim();
                if (subdomain) {
                    urlPreview.value = subdomain + '.meg.www';
                } else {
                    urlPreview.value = 'subdomain.meg.www';
                }
            }
            
            // Update preview on input
            subdomainInput.addEventListener('input', updatePreview);
            
            // Initial preview
            updatePreview();
            
            // Auto-format subdomain (lowercase, replace spaces with hyphens)
            subdomainInput.addEventListener('blur', function() {
                var value = this.value.toLowerCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^a-z0-9-]/g, '')
                    .replace(/-+/g, '-')
                    .replace(/^-|-$/g, '');
                this.value = value;
                updatePreview();
            });
        }
    }, false);
})();
</script>