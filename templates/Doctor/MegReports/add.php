<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ReportSlide $slide
 * @var \App\Model\Entity\Report $report
 * @var int $reportId
 */
$this->assign('title', 'Add Slide');
?>

<style>
.preview-title {
    color: #333;
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
}
.preview-content {
    color: #666;
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 20px;
    white-space: pre-wrap;
}
.preview-image {
    max-width: 750px;
    max-height: 450px;
    width: 100%;
    height: auto;
    border-radius: 8px;
    margin-top: 20px;
}
.upload-zone {
    border: 3px dashed #dc3545;
    border-radius: 10px;
    padding: 40px 20px;
    text-align: center;
    background: #fff5f5;
    transition: all 0.3s ease;
    cursor: pointer;
}
.upload-zone:hover {
    background: #ffe5e8;
    border-color: #c82333;
}
.upload-zone.dragover {
    background: #ffe5e8;
    border-color: #c82333;
    transform: scale(1.02);
}
</style>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-danger text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-plus-circle me-2"></i>Add New Slide
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-folder-open me-2"></i>Case: <?php echo h($report->case->patient_user->name ?? 'N/A') ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-left me-2"></i>Back to Slides',
                        ['action' => 'index', $reportId],
                        ['class' => 'btn btn-light', 'escape' => false]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <?php echo $this->Form->create($slide, [
        'type' => 'file',
        'class' => 'slide-form',
        'id' => 'slideForm'
    ]) ?>
    
    <div class="row">
        <!-- Form Section -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-edit me-2 text-danger"></i>Slide Content
                    </h5>
                </div>
                <div class="card-body p-4">
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Slide Title</label>
                    <?php echo $this->Form->control('title', [
                        'class' => 'form-control form-control-lg',
                        'placeholder' => 'Enter slide title (for internal use)',
                        'label' => false,
                        'id' => 'slideTitle'
                    ]) ?>
                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i>For internal use only - not displayed on presentation</small>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Link to Exam/Procedure <span class="text-muted">(Optional)</span></label>
                    <?php echo $this->Form->control('cases_exams_procedure_id', [
                        'type' => 'select',
                        'options' => $examProceduresList,
                        'empty' => '-- Not Linked --',
                        'class' => 'form-select',
                        'label' => false
                    ]) ?>
                    <small class="text-muted">
                        <i class="fas fa-link me-1"></i>Link this slide to a specific exam/procedure. 
                        Original images will be saved to the linked exam's documents.
                    </small>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Slide Content/Description</label>
                    <?php echo $this->Form->textarea('content', [
                        'class' => 'form-control',
                        'rows' => 8,
                        'placeholder' => 'Enter slide content or description',
                        'label' => false,
                        'id' => 'slideContent'
                    ]) ?>
                    <small class="text-muted"><i class="fas fa-file-powerpoint me-1 text-success"></i>This content will be displayed on the PowerPoint presentation</small>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Slide Image</label>
                    <div class="upload-zone" id="uploadZone">
                        <i class="fas fa-image fa-3x text-danger mb-3"></i>
                        <h5>Drag & Drop Image Here</h5>
                        <p class="text-muted mb-3">or</p>
                        <button type="button" class="btn btn-danger" id="selectImageBtn">
                            <i class="fas fa-folder-open me-2"></i>Select Image
                        </button>
                        <p class="text-muted mt-3 mb-0">
                            <small>Image will be resized to max 750×450 pixels</small><br>
                            <small>Supported formats: JPG, PNG, GIF</small>
                        </p>
                    </div>
                    <?php echo $this->Form->file('image_file', [
                        'id' => 'imageInput',
                        'accept' => 'image/*',
                        'style' => 'display: none;'
                    ]) ?>
                    <div id="imagePreviewContainer" class="mt-3" style="display: none;">
                        <img id="imagePreview" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px;">
                        <button type="button" class="btn btn-sm btn-danger mt-2" id="removeImageBtn">
                            <i class="fas fa-times me-1"></i>Remove Image
                        </button>
                    </div>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <?php echo $this->Form->button('<i class="fas fa-save me-2"></i>Save Slide', [
                        'class' => 'btn btn-danger btn-lg fw-bold',
                        'escapeTitle' => false
                    ]) ?>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times me-2"></i>Cancel',
                        ['action' => 'index', $reportId],
                        ['class' => 'btn btn-outline-danger btn-lg', 'escape' => false]
                    ) ?>
                </div>
                </div>
            </div>
        </div>
        
        <!-- Preview Section -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-eye me-2 text-danger"></i>Live Preview
                    </h5>
                </div>
                <div class="card-body p-4">
                
                <div id="slidePreview">
                    <div id="previewContent" class="preview-content" style="display: none;"></div>
                    <div id="previewImageContainer" style="display: none;">
                        <img id="previewImage" src="" alt="Slide Image" class="preview-image">
                    </div>
                    <div id="emptyPreview" class="text-center text-muted py-5">
                        <i class="fas fa-file-powerpoint fa-4x mb-3 opacity-50"></i>
                        <p class="fw-semibold">Enter content to see preview</p>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php echo $this->Form->end() ?>
</div>

<?php $this->start('script'); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const uploadZone = $('#uploadZone');
    const imageInput = $('#imageInput');
    const selectImageBtn = $('#selectImageBtn');
    const imagePreviewContainer = $('#imagePreviewContainer');
    const imagePreview = $('#imagePreview');
    const removeImageBtn = $('#removeImageBtn');
    
    const slideTitle = $('#slideTitle');
    const slideContent = $('#slideContent');
    
    const previewContent = $('#previewContent');
    const previewImageContainer = $('#previewImageContainer');
    const previewImage = $('#previewImage');
    const emptyPreview = $('#emptyPreview');
    
    // Update preview on input
    function updatePreview() {
        const hasContent = slideContent.val().trim() !== '';
        const hasImage = imagePreview.attr('src') !== '';
        
        if (hasContent || hasImage) {
            emptyPreview.hide();
            
            if (hasContent) {
                previewContent.text(slideContent.val()).show();
            } else {
                previewContent.hide();
            }
            
            if (hasImage) {
                previewImage.attr('src', imagePreview.attr('src'));
                previewImageContainer.show();
            } else {
                previewImageContainer.hide();
            }
        } else {
            emptyPreview.show();
            previewContent.hide();
            previewImageContainer.hide();
        }
    }
    
    slideContent.on('input', updatePreview);
    
    // Image upload handling
    selectImageBtn.click(function() {
        imageInput.click();
    });
    
    uploadZone.click(function(e) {
        if (e.target === this || $(e.target).closest('#selectImageBtn').length === 0) {
            imageInput.click();
        }
    });
    
    // Drag and drop
    uploadZone.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });
    
    uploadZone.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });
    
    uploadZone.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            imageInput[0].files = files;
            handleImageSelection(files[0]);
        }
    });
    
    imageInput.change(function() {
        if (this.files && this.files[0]) {
            handleImageSelection(this.files[0]);
        }
    });
    
    function handleImageSelection(file) {
        if (!file.type.match('image.*')) {
            alert('Please select an image file.');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            imagePreview.attr('src', e.target.result);
            imagePreviewContainer.show();
            updatePreview();
        };
        reader.readAsDataURL(file);
    }
    
    removeImageBtn.click(function() {
        imageInput.val('');
        imagePreview.attr('src', '');
        imagePreviewContainer.hide();
        updatePreview();
    });
});
</script>
<?php $this->end(); ?>
