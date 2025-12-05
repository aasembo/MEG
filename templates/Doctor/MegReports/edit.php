<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ReportSlide $slide
 * @var \App\Model\Entity\Report $report
 */
$this->assign('title', 'Edit Slide');

// Use title and description fields directly, fallback to html_content extraction for backwards compatibility
$title = $slide->title ?? '';
$content = $slide->description ?? '';

// Fallback: Extract from html_content if title/description are empty
if (empty($title) && empty($content) && $slide->html_content) {
    if (preg_match('/<h3>(.*?)<\/h3>/s', $slide->html_content, $matches)) {
        $title = html_entity_decode(strip_tags($matches[1]));
    }
    if (preg_match('/<p>(.*?)<\/p>/s', $slide->html_content, $matches)) {
        $content = html_entity_decode(strip_tags(str_replace('<br />', "\n", $matches[1])));
    }
}
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
.current-image {
    max-width: 300px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-danger text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-edit me-2"></i>Edit Slide
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-folder-open me-2"></i>Case: <?php echo h($report->case->patient_user->name ?? 'N/A') ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?php echo $this->Html->link(
                        '<i class="fas fa-arrow-left me-2"></i>Back to Slides',
                        ['action' => 'index', $slide->report_id],
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
                        'id' => 'slideTitle',
                        'value' => $title
                    ]) ?>
                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i>For internal use only - not displayed on presentation</small>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Link to Exam/Procedure <span class="text-muted">(Optional)</span></label>
                    <?php if ($slide->cases_exams_procedure_id): ?>
                        <div class="form-control bg-light" style="cursor: not-allowed;">
                            <?php echo h($examProceduresList[$slide->cases_exams_procedure_id] ?? 'Not Linked') ?>
                        </div>
                        <?php echo $this->Form->hidden('cases_exams_procedure_id', [
                            'value' => $slide->cases_exams_procedure_id
                        ]) ?>
                        <small class="text-muted">
                            <i class="fas fa-lock me-1"></i>Exam/Procedure link cannot be changed after creation.
                            New images will be saved to this exam's documents.
                        </small>
                    <?php else: ?>
                        <div class="form-control bg-light" style="cursor: not-allowed;">
                            Not Linked
                        </div>
                        <?php echo $this->Form->hidden('cases_exams_procedure_id', [
                            'value' => null
                        ]) ?>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>This slide is not linked to any exam/procedure.
                        </small>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Slide Content/Description</label>
                    <?php echo $this->Form->textarea('content', [
                        'class' => 'form-control',
                        'rows' => 8,
                        'placeholder' => 'Enter slide content or description',
                        'label' => false,
                        'id' => 'slideContent',
                        'value' => $content
                    ]) ?>
                    <small class="text-muted"><i class="fas fa-file-powerpoint me-1 text-success"></i>This content will be displayed on the PowerPoint presentation</small>
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Slide Image</label>
                    
                    <?php if ($slide->file_path): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <label class="fw-semibold mb-2 text-dark"><i class="fas fa-image me-2 text-danger"></i>Current Image:</label>
                            <div class="mb-3">
                                <img src="<?php echo h($slide->image_url ?? $slide->file_path) ?>" alt="Current Slide Image" class="current-image img-thumbnail" id="currentImage">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="keepCurrentImage" checked>
                                <label class="form-check-label fw-semibold" for="keepCurrentImage">
                                    <i class="fas fa-check-circle me-1 text-success"></i>Keep current image
                                </label>
                            </div>
                        </div>
                        
                        <div id="newImageUpload" style="display: none;">
                    <?php else: ?>
                        <div id="newImageUpload">
                    <?php endif; ?>
                    
                            <div class="upload-zone" id="uploadZone">
                                <i class="fas fa-image fa-3x text-danger mb-3"></i>
                                <h5>Drag & Drop New Image Here</h5>
                                <p class="text-muted mb-3">or</p>
                                <button type="button" class="btn btn-danger" id="selectImageBtn">
                                    <i class="fas fa-folder-open me-2"></i>Select New Image
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
                                    <i class="fas fa-times me-1"></i>Remove New Image
                                </button>
                            </div>
                        </div>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <?php echo $this->Form->button('<i class="fas fa-save me-2"></i>Update Slide', [
                        'class' => 'btn btn-danger btn-lg fw-bold',
                        'escapeTitle' => false
                    ]) ?>
                    <?php echo $this->Html->link(
                        '<i class="fas fa-times me-2"></i>Cancel',
                        ['action' => 'index', $slide->report_id],
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
                    <div id="previewContent" class="preview-content"><?php echo h($content) ?></div>
                    <?php if ($slide->file_path): ?>
                        <div id="previewImageContainer">
                            <img id="previewImage" src="<?php echo h($slide->image_url ?? $slide->file_path) ?>" alt="Slide Image" class="preview-image">
                        </div>
                    <?php else: ?>
                        <div id="previewImageContainer" style="display: none;">
                            <img id="previewImage" src="" alt="Slide Image" class="preview-image">
                        </div>
                    <?php endif; ?>
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
    const keepCurrentImage = $('#keepCurrentImage');
    const currentImage = $('#currentImage');
    const newImageUpload = $('#newImageUpload');
    
    const slideTitle = $('#slideTitle');
    const slideContent = $('#slideContent');
    
    const previewContent = $('#previewContent');
    const previewImageContainer = $('#previewImageContainer');
    const previewImage = $('#previewImage');
    
    // Show/hide new image upload
    if (keepCurrentImage.length) {
        keepCurrentImage.change(function() {
            if (this.checked) {
                newImageUpload.slideUp();
                if (currentImage.length) {
                    previewImage.attr('src', currentImage.attr('src'));
                    previewImageContainer.show();
                }
            } else {
                newImageUpload.slideDown();
            }
        });
    }
    
    // Update preview on input
    function updatePreview() {
        const contentText = slideContent.val().trim();
        
        if (contentText) {
            previewContent.text(contentText).show();
        } else {
            previewContent.hide();
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
            previewImage.attr('src', e.target.result);
            previewImageContainer.show();
            
            if (keepCurrentImage.length) {
                keepCurrentImage.prop('checked', false);
            }
        };
        reader.readAsDataURL(file);
    }
    
    removeImageBtn.click(function() {
        imageInput.val('');
        imagePreview.attr('src', '');
        imagePreviewContainer.hide();
        
        // Restore current image in preview if it exists
        if (currentImage.length && keepCurrentImage.prop('checked')) {
            previewImage.attr('src', currentImage.attr('src'));
            previewImageContainer.show();
        } else {
            previewImageContainer.hide();
        }
    });
});
</script>
<?php $this->end(); ?>
