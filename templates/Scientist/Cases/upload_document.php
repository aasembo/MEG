<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\MedicalCase $case
 */

$this->assign('title', 'Upload Document for Case #' . $case->id);
?>

<div class="cases upload-document content">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-upload me-2"></i>Upload Document
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Case Information -->
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">
                            <i class="fas fa-info-circle me-1"></i>Case Information
                        </h6>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Case ID:</strong> #<?php echo h($case->id) ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Patient:</strong> <?php echo h($case->patient_user ? ($case->patient_user->first_name . ' ' . $case->patient_user->last_name) : 'N/A') ?>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Form -->
                    <?php echo $this->Form->create(null, array('type' => 'file', 'class' => 'needs-validation')); ?>
                    
                    <div class="mb-3">
                        <label for="document-file" class="form-label fw-semibold required">
                            <i class="fas fa-file me-1 text-primary"></i>Select Document
                        </label>
                        <?php echo $this->Form->control('document_file', array(
                            'type' => 'file',
                            'label' => false,
                            'class' => 'form-control form-control-lg',
                            'id' => 'document-file',
                            'required' => true,
                            'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png,.txt'
                        )); ?>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>Supported formats: PDF, DOC, DOCX, JPG, PNG, TXT (Max: 10MB)
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="document-type" class="form-label fw-semibold required">
                            <i class="fas fa-tag me-1 text-success"></i>Document Type
                        </label>
                        <?php echo $this->Form->control('document_type', array(
                            'type' => 'select',
                            'options' => $documentTypes,
                            'label' => false,
                            'class' => 'form-select form-select-lg',
                            'id' => 'document-type',
                            'required' => true
                        )); ?>
                    </div>

                    <div class="mb-4">
                        <label for="document-name" class="form-label fw-semibold">
                            <i class="fas fa-signature me-1 text-info"></i>Document Name (Optional)
                        </label>
                        <?php echo $this->Form->control('document_name', array(
                            'type' => 'text',
                            'label' => false,
                            'class' => 'form-control',
                            'id' => 'document-name',
                            'placeholder' => 'e.g., Patient Lab Results - Blood Test'
                        )); ?>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>If not provided, the original filename will be used
                        </div>
                    </div>

                    <!-- File Preview -->
                    <div id="filePreview" class="mb-4" style="display: none;">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-eye me-1"></i>Selected File
                                </h6>
                                <div id="fileInfo"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-times me-1"></i>Cancel',
                            array('action' => 'view', $case->id),
                            array('class' => 'btn btn-outline-secondary', 'escape' => false)
                        ); ?>
                        
                        <?php echo $this->Form->button(
                            '<i class="fas fa-upload me-1"></i>Upload Document',
                            array('type' => 'submit', 'class' => 'btn btn-primary btn-lg', 'escapeTitle' => false, 'id' => 'submitBtn')
                        ); ?>
                    </div>

                    <?php echo $this->Form->end(); ?>
                </div>
            </div>

            <!-- Help Text -->
            <div class="alert alert-light mt-3">
                <h6 class="alert-heading">
                    <i class="fas fa-question-circle me-1"></i>Upload Guidelines
                </h6>
                <ul class="mb-0 small">
                    <li><strong>File Size:</strong> Maximum file size is 10MB</li>
                    <li><strong>Supported Formats:</strong> PDF, Word Documents (DOC/DOCX), Images (JPG/PNG), Text files</li>
                    <li><strong>Security:</strong> All documents are encrypted and securely stored</li>
                    <li><strong>Access:</strong> Documents are only accessible to authorized personnel</li>
                    <li><strong>Document Name:</strong> Use descriptive names to easily identify documents later</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// File preview and validation
document.getElementById('document-file').addEventListener('change', function(e) {
    var file = e.target.files[0];
    var preview = document.getElementById('filePreview');
    var fileInfo = document.getElementById('fileInfo');
    var submitBtn = document.getElementById('submitBtn');
    
    if (file) {
        // Check file size (10MB = 10 * 1024 * 1024 bytes)
        var maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('File size exceeds 10MB limit. Please select a smaller file.');
            e.target.value = '';
            preview.style.display = 'none';
            submitBtn.disabled = true;
            return;
        }
        
        // Show file info
        var fileSize = (file.size / 1024).toFixed(2);
        var fileSizeUnit = 'KB';
        if (fileSize > 1024) {
            fileSize = (fileSize / 1024).toFixed(2);
            fileSizeUnit = 'MB';
        }
        
        fileInfo.innerHTML = '<div class="row">' +
            '<div class="col-md-6"><strong>Name:</strong> ' + file.name + '</div>' +
            '<div class="col-md-3"><strong>Size:</strong> ' + fileSize + ' ' + fileSizeUnit + '</div>' +
            '<div class="col-md-3"><strong>Type:</strong> ' + file.type + '</div>' +
            '</div>';
        
        preview.style.display = 'block';
        submitBtn.disabled = false;
    } else {
        preview.style.display = 'none';
        submitBtn.disabled = false;
    }
});

// Form validation
(function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                // Show loading state
                var submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Uploading...';
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>
