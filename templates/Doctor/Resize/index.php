<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Resize Images');
?>

<style>
.upload-zone {
    border: 3px dashed #dc3545;
    border-radius: 15px;
    padding: 60px 20px;
    text-align: center;
    background: #fff;
    transition: all 0.3s ease;
    cursor: pointer;
}
.upload-zone:hover {
    background: #fff5f5;
    border-color: #c82333;
}
.upload-zone.dragover {
    background: #ffe5e8;
    border-color: #c82333;
    transform: scale(1.02);
}
.image-preview {
    position: relative;
    display: inline-block;
    margin: 10px;
}
.image-preview img {
    max-width: 150px;
    max-height: 150px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.image-preview .remove-btn {
    position: absolute;
    top: -10px;
    right: -10px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    font-size: 14px;
    line-height: 30px;
}
.image-preview .remove-btn:hover {
    background: #bb2d3b;
}
.size-preset {
    cursor: pointer;
    padding: 15px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    transition: all 0.3s ease;
}
.size-preset:hover {
    border-color: #dc3545;
    background: #fff5f5;
}
.size-preset.active {
    border-color: #dc3545;
    background: #ffe5e8;
    font-weight: bold;
}
</style>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-danger text-white p-4">
            <h2 class="mb-0 fw-bold">
                <i class="fas fa-crop-alt me-2"></i>Resize Images
            </h2>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Instructions:</strong>
                <ul class="mb-0 mt-2">
                    <li>Upload one or multiple images</li>
                    <li>Select a preset size or enter custom dimensions</li>
                    <li>Click "Resize & Download" to get your resized images</li>
                    <li>Multiple images will be downloaded as a ZIP file</li>
                    <li>Images maintain their aspect ratio during resize</li>
                </ul>
            </div>
        </div>
    </div>

    <?php echo $this->Form->create(null, [
        'url' => ['action' => 'process'],
        'type' => 'file',
        'id' => 'resizeForm'
    ]); ?>
    
    <!-- Size Selection -->
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light border-0 py-3">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-ruler-combined me-2 text-danger"></i>Select Size
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="size-preset active" data-width="760" data-height="450">
                        <div class="text-center">
                            <i class="fas fa-desktop fa-2x text-danger mb-2"></i>
                            <h6 class="mb-0">Default</h6>
                            <small class="text-muted">760 × 450</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="size-preset" data-width="1920" data-height="1080">
                        <div class="text-center">
                            <i class="fas fa-tv fa-2x text-danger mb-2"></i>
                            <h6 class="mb-0">Full HD</h6>
                            <small class="text-muted">1920 × 1080</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="size-preset" data-width="1280" data-height="720">
                        <div class="text-center">
                            <i class="fas fa-film fa-2x text-danger mb-2"></i>
                            <h6 class="mb-0">HD</h6>
                            <small class="text-muted">1280 × 720</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="size-preset" data-width="800" data-height="600">
                        <div class="text-center">
                            <i class="fas fa-image fa-2x text-danger mb-2"></i>
                            <h6 class="mb-0">Standard</h6>
                            <small class="text-muted">800 × 600</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Width (px)</label>
                    <input type="number" name="width" id="widthInput" class="form-control form-control-lg" value="760" min="1" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Height (px)</label>
                    <input type="number" name="height" id="heightInput" class="form-control form-control-lg" value="450" min="1" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Zone -->
    <div class="card border-0 shadow mb-4">
        <div class="card-header bg-light border-0 py-3">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-images me-2 text-danger"></i>Upload Images
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="upload-zone" id="uploadZone">
                <i class="fas fa-cloud-upload-alt fa-4x text-danger mb-3"></i>
                <h4>Drag & Drop Images Here</h4>
                <p class="text-muted mb-3">or</p>
                <button type="button" class="btn btn-danger btn-lg" id="selectFilesBtn">
                    <i class="fas fa-folder-open me-2"></i>Select Images
                </button>
                <p class="text-muted mt-3 mb-0">
                    <small>Supported formats: JPG, PNG, GIF</small>
                </p>
            </div>
            
            <input type="file" name="images[]" id="imageInput" multiple accept="image/*" style="display: none;">
            
            <!-- Preview Container -->
            <div id="imagePreviews" class="mt-4" style="display: none;">
                <h5 class="mb-3">
                    <i class="fas fa-images me-2"></i>Selected Images (<span id="imageCount">0</span>)
                </h5>
                <div id="previewContainer" class="text-center"></div>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="text-center mb-4">
        <button type="submit" class="btn btn-danger btn-lg px-5" id="submitBtn" disabled>
            <i class="fas fa-crop-alt me-2"></i>Resize & Download
        </button>
    </div>

    <?php echo $this->Form->end(); ?>
</div>

<meta name="csrf-token" content="<?php echo $this->request->getAttribute('csrfToken'); ?>">

<?php $this->start('script'); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let selectedFiles = [];
    const uploadZone = $('#uploadZone');
    const imageInput = $('#imageInput');
    const previewContainer = $('#previewContainer');
    const imagePreviews = $('#imagePreviews');
    const submitBtn = $('#submitBtn');
    const imageCountSpan = $('#imageCount');
    const widthInput = $('#widthInput');
    const heightInput = $('#heightInput');

    // Size preset clicks
    $('.size-preset').off('click').on('click', function() {
        $('.size-preset').removeClass('active');
        $(this).addClass('active');
        widthInput.val($(this).data('width'));
        heightInput.val($(this).data('height'));
    });

    // Select files button
    $('#selectFilesBtn').off('click').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        imageInput[0].click();
    });
    
    // Upload zone click
    uploadZone.off('click').on('click', function(e) {
        if (e.target !== this && !$(e.target).is('i, h4, p, small')) return;
        e.preventDefault();
        e.stopPropagation();
        imageInput[0].click();
    });

    // File input change
    imageInput.off('change').on('change', function() {
        handleFiles(this.files);
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
        handleFiles(files);
    });

    function handleFiles(files) {
        if (files.length === 0) return;

        Array.from(files).forEach(file => {
            if (file.type.startsWith('image/')) {
                selectedFiles.push(file);
            }
        });

        updatePreviews();
    }

    function updatePreviews() {
        previewContainer.empty();
        
        if (selectedFiles.length === 0) {
            imagePreviews.hide();
            submitBtn.prop('disabled', true);
            return;
        }

        imagePreviews.show();
        submitBtn.prop('disabled', false);
        imageCountSpan.text(selectedFiles.length);

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = $(`
                    <div class="image-preview" data-index="${index}">
                        <button type="button" class="remove-btn">
                            <i class="fas fa-times"></i>
                        </button>
                        <img src="${e.target.result}" alt="Preview ${index + 1}">
                        <div class="text-center mt-2">
                            <small class="text-muted">${file.name}</small>
                        </div>
                    </div>
                `);
                
                preview.find('.remove-btn').click(function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    selectedFiles.splice(index, 1);
                    updatePreviews();
                });
                
                previewContainer.append(preview);
            };
            reader.readAsDataURL(file);
        });
    }

    // Form submit
    $('#resizeForm').submit(function(e) {
        if (selectedFiles.length === 0) {
            e.preventDefault();
            alert('Please select at least one image.');
            return false;
        }

        // Create a new DataTransfer to assign files to the input
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });
        imageInput[0].files = dataTransfer.files;

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
        
        // Reset button state after download completes (since file download doesn't trigger AJAX callbacks)
        setTimeout(function() {
            submitBtn.prop('disabled', false).html('<i class="fas fa-crop-alt me-2"></i>Resize & Download');
        }, 3000);
        
        // Allow form to submit normally for file download
        return true;
    });
});
</script>
<?php $this->end(); ?>
