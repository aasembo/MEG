<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Hospital $hospital
 */
?>
<?php $this->assign('title', 'Edit Hospital'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-edit me-2 text-primary"></i>Edit Hospital
        </h1>
        <p class="text-muted mb-0">Update hospital information</p>
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
                        <div class="form-text">
                            <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                            Changing subdomain will affect hospital URL access
                        </div>
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
                        <label class="form-label">Current URL</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">https://</span>
                            <input type="text" class="form-control bg-light" id="url-preview" readonly value="<?php echo h($hospital->subdomain) ?>.meg.www">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyUrl()">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <div class="form-text">Hospital's current access URL</div>
                    </div>
                </div>
                
                <!-- Hospital Statistics -->
                <?php if (!empty($hospital->users)): ?>
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="fas fa-users me-2"></i>Hospital Users
                            </h6>
                            <p class="mb-0">
                                This hospital currently has <strong><?php echo count($hospital->users) ?> users</strong> assigned. 
                                Deactivating this hospital will prevent user access but preserve all data.
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times me-2"></i>Cancel',
                        ['action' => 'index'],
                        ['class' => 'btn btn-secondary', 'escape' => false]
                    ) ?>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Update Hospital
                    </button>
                </div>
            </div>
            
            <?php echo $this->Form->end() ?>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Hospital Info -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Hospital Details
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Hospital ID</label>
                    <div class="fw-bold"><?php echo $hospital->id ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted small">Created</label>
                    <div class="fw-bold"><?php echo $hospital->created->format('M j, Y \a\t g:i A') ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted small">Last Modified</label>
                    <div class="fw-bold"><?php echo $hospital->modified->format('M j, Y \a\t g:i A') ?></div>
                </div>
                
                <div class="mb-0">
                    <label class="form-label text-muted small">Current Status</label>
                    <div>
                        <?php if ($hospital->status === 'active'): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i>Active
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning">
                                <i class="fas fa-pause-circle me-1"></i>Inactive
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-eye me-2"></i>View Details',
                        ['action' => 'view', $hospital->id],
                        ['class' => 'btn btn-outline-primary btn-sm', 'escape' => false]
                    ) ?>
                    
                    <?php if (!empty($hospital->users)): ?>
                        <?php echo $this->Html->link(
                            '<i class="fas fa-users me-2"></i>Manage Users',
                            ['controller' => 'Users', 'action' => 'index', '?' => ['hospital_id' => $hospital->id]],
                            ['class' => 'btn btn-outline-info btn-sm', 'escape' => false]
                        ) ?>
                    <?php endif; ?>
                    
                    <?php echo $this->Form->postLink(
                        ($hospital->status === 'active') ? '<i class="fas fa-pause me-2"></i>Deactivate' : '<i class="fas fa-play me-2"></i>Activate',
                        ['action' => 'toggleStatus', $hospital->id],
                        [
                            'class' => 'btn btn-outline-' . (($hospital->status === 'active') ? 'warning' : 'success') . ' btn-sm',
                            'escape' => false,
                            'confirm' => 'Are you sure you want to ' . (($hospital->status === 'active') ? 'deactivate' : 'activate') . ' this hospital?'
                        ]
                    ) ?>
                </div>
            </div>
        </div>
        
        <!-- Warning -->
        <?php if (!empty($hospital->users)): ?>
        <div class="card border-warning border-0 shadow-sm mt-3">
            <div class="card-header bg-warning bg-opacity-10">
                <h6 class="mb-0 text-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>Important Notice
                </h6>
            </div>
            <div class="card-body">
                <small class="text-muted">
                    This hospital has active users. Changes to status or subdomain may affect user access. 
                    Consider notifying users before making significant changes.
                </small>
            </div>
        </div>
        <?php endif; ?>
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

// Copy URL function
function copyUrl() {
    var urlPreview = document.getElementById('url-preview');
    var fullUrl = 'https://' + urlPreview.value;
    
    navigator.clipboard.writeText(fullUrl).then(function() {
        // Show success feedback
        var button = event.target.closest('button');
        var originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check text-success"></i>';
        setTimeout(function() {
            button.innerHTML = originalHtml;
        }, 1500);
    });
}
</script>