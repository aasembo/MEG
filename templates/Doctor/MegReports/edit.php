<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ReportSlide $slide
 * @var \App\Model\Entity\Report $report
 * @var array|null $slideConfig
 * @var string $slideType
 * @var array $slideTypes
 * @var array $examProceduresList
 */
$this->assign('title', 'Edit Slide');

// Use slide's stored layout_columns, fallback to config, then default to 1
$layoutColumns = $slide->layout_columns ?? $slideConfig['columns'] ?? 1;
$slideTitle = $slideConfig['title'] ?? 'Custom Slide';
?>

<style>
.slide-type-info {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.slide-type-info .type-name {
    font-size: 18px;
    font-weight: bold;
    text-transform: capitalize;
}
.slide-type-info .type-layout {
    font-size: 13px;
    opacity: 0.9;
}
.column-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}
.column-section h5 {
    color: #dc3545;
    border-bottom: 2px solid #dc3545;
    padding-bottom: 10px;
    margin-bottom: 20px;
}
.upload-zone {
    border: 3px dashed #dc3545;
    border-radius: 10px;
    padding: 30px 20px;
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
.current-image-preview {
    max-width: 100%;
    max-height: 200px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.preview-panel {
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    position: sticky;
    top: 20px;
}
.preview-header {
    background: #333;
    color: white;
    padding: 15px 20px;
    font-weight: bold;
}
.preview-slide {
    aspect-ratio: 16/9;
    background: white;
    padding: 20px;
    display: flex;
    flex-direction: column;
}
.preview-slide h2 {
    font-size: 18px;
    color: #333;
    margin-bottom: 10px;
}
.preview-two-columns {
    display: flex;
    gap: 15px;
    flex: 1;
}
.preview-column {
    flex: 1;
    display: flex;
    flex-direction: column;
}
.preview-column-header {
    font-size: 12px;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
    padding-bottom: 3px;
    border-bottom: 2px solid #dc3545;
}
.preview-column img {
    max-width: 100%;
    max-height: 150px;
    object-fit: contain;
    margin: auto 0;
}
.preview-single img {
    max-width: 100%;
    max-height: 200px;
    object-fit: contain;
    margin: 10px auto;
}
.legend-editor {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-top: 15px;
}
.legend-item-row {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
}
.legend-color-input {
    width: 50px;
    height: 35px;
    padding: 2px;
    border: 1px solid #ced4da;
    border-radius: 4px;
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
                    
                    <!-- Slide Type Info -->
                    <div class="slide-type-info">
                        <div class="type-name">
                            <i class="fas fa-layer-group me-2"></i>
                            <?php echo h(str_replace('_', ' ', $slideType)) ?>
                        </div>
                        <div class="type-layout">
                            <i class="fas fa-columns me-1"></i>
                            <?php echo $layoutColumns === 2 ? 'Two Column Layout' : 'Single Column Layout' ?>
                            <?php if ($slideConfig): ?>
                                | <?php echo h(ucfirst(str_replace('_', ' ', $slideConfig['layout'] ?? 'standard'))) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Slide Title -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Slide Title</label>
                        <?php echo $this->Form->control('title', [
                            'class' => 'form-control form-control-lg',
                            'placeholder' => $slideConfig['title'] ?? 'Enter slide title',
                            'label' => false,
                            'id' => 'slideTitle',
                            'value' => $slide->title ?: ($slideConfig['title'] ?? '')
                        ]) ?>
                    </div>
                    
                    <?php if ($slideConfig && !empty($slideConfig['subtitle'])): ?>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Subtitle</label>
                        <?php echo $this->Form->control('subtitle', [
                            'class' => 'form-control',
                            'placeholder' => $slideConfig['subtitle'] ?? 'Enter subtitle',
                            'label' => false,
                            'id' => 'slideSubtitle',
                            'value' => $slide->subtitle ?: ($slideConfig['subtitle'] ?? '')
                        ]) ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($layoutColumns === 2): ?>
                        <!-- Two Column Layout -->
                        <div class="row">
                            <!-- Column 1 -->
                            <div class="col-md-6">
                                <div class="column-section">
                                    <h5><i class="fas fa-columns me-2"></i>Column 1</h5>
                                    
                                    <?php if (!empty($slideConfig['col1']['header'])): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Column Header</label>
                                        <?php echo $this->Form->control('col1_header', [
                                            'class' => 'form-control',
                                            'placeholder' => $slideConfig['col1']['header'],
                                            'label' => false,
                                            'value' => $slide->col1_header ?? $slideConfig['col1']['header']
                                        ]) ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $col1Type = $slideConfig['col1']['type'] ?? $slide->col1_type ?? 'image';
                                    if ($col1Type === 'image' || $col1Type === 'composite_image'): ?>
                                        <?php if ($slide->col1_image_url ?? $slide->col1_image_path): ?>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Current Image:</label>
                                                <div class="text-center">
                                                    <img src="<?php echo h($slide->col1_image_url ?? $slide->col1_image_path) ?>" 
                                                         alt="Column 1 Image" class="current-image-preview" id="col1CurrentImage">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="upload-zone" id="col1UploadZone" data-target="col1_image_file">
                                            <i class="fas fa-image fa-2x text-danger mb-2"></i>
                                            <div>Upload New Image</div>
                                            <small class="text-muted">Drag & drop or click</small>
                                        </div>
                                        <?php echo $this->Form->file('col1_image_file', [
                                            'id' => 'col1_image_file',
                                            'accept' => 'image/*',
                                            'style' => 'display: none;'
                                        ]) ?>
                                        <div id="col1PreviewContainer" class="mt-2 text-center" style="display: none;">
                                            <img id="col1Preview" src="" class="current-image-preview" alt="Preview">
                                            <button type="button" class="btn btn-sm btn-danger mt-2 remove-preview" data-target="col1">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        </div>
                                    <?php elseif ($col1Type === 'text'): ?>
                                        <?php echo $this->Form->textarea('col1_content', [
                                            'class' => 'form-control',
                                            'rows' => 6,
                                            'placeholder' => 'Enter column 1 content',
                                            'label' => false,
                                            'id' => 'col1Content',
                                            'value' => $slide->col1_content ?? ''
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Column 2 -->
                            <div class="col-md-6">
                                <div class="column-section">
                                    <h5><i class="fas fa-columns me-2"></i>Column 2</h5>
                                    
                                    <?php if (!empty($slideConfig['col2']['header'])): ?>
                                    <div class="mb-3">
                                        <label class="form-label">Column Header</label>
                                        <?php echo $this->Form->control('col2_header', [
                                            'class' => 'form-control',
                                            'placeholder' => $slideConfig['col2']['header'],
                                            'label' => false,
                                            'value' => $slide->col2_header ?? $slideConfig['col2']['header']
                                        ]) ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    $col2Type = $slideConfig['col2']['type'] ?? $slide->col2_type ?? 'image';
                                    if ($col2Type === 'image' || $col2Type === 'composite_image'): ?>
                                        <?php if ($slide->col2_image_url ?? $slide->col2_image_path): ?>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Current Image:</label>
                                                <div class="text-center">
                                                    <img src="<?php echo h($slide->col2_image_url ?? $slide->col2_image_path) ?>" 
                                                         alt="Column 2 Image" class="current-image-preview" id="col2CurrentImage">
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="upload-zone" id="col2UploadZone" data-target="col2_image_file">
                                            <i class="fas fa-image fa-2x text-danger mb-2"></i>
                                            <div>Upload New Image</div>
                                            <small class="text-muted">Drag & drop or click</small>
                                        </div>
                                        <?php echo $this->Form->file('col2_image_file', [
                                            'id' => 'col2_image_file',
                                            'accept' => 'image/*',
                                            'style' => 'display: none;'
                                        ]) ?>
                                        <div id="col2PreviewContainer" class="mt-2 text-center" style="display: none;">
                                            <img id="col2Preview" src="" class="current-image-preview" alt="Preview">
                                            <button type="button" class="btn btn-sm btn-danger mt-2 remove-preview" data-target="col2">
                                                <i class="fas fa-times"></i> Remove
                                            </button>
                                        </div>
                                    <?php elseif ($col2Type === 'text'): ?>
                                        <?php echo $this->Form->textarea('col2_content', [
                                            'class' => 'form-control',
                                            'rows' => 6,
                                            'placeholder' => 'Enter column 2 content',
                                            'label' => false,
                                            'id' => 'col2Content',
                                            'value' => $slide->col2_content ?? ''
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Single Column Layout -->
                        <div class="column-section">
                            <h5><i class="fas fa-image me-2"></i>Slide Content</h5>
                            
                            <?php if (($slideConfig['col1']['type'] ?? 'image') === 'image' || ($slideConfig['layout'] ?? '') !== 'text_only'): ?>
                                <?php if ($slide->col1_image_url ?? $slide->image_url ?? $slide->file_path): ?>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Current Image:</label>
                                        <div class="text-center">
                                            <img src="<?php echo h($slide->col1_image_url ?? $slide->image_url ?? $slide->file_path) ?>" 
                                                 alt="Slide Image" class="current-image-preview" id="currentImage">
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="upload-zone" id="imageUploadZone" data-target="image_file">
                                    <i class="fas fa-image fa-2x text-danger mb-2"></i>
                                    <div>Upload New Image</div>
                                    <small class="text-muted">Drag & drop or click to select</small>
                                </div>
                                <?php echo $this->Form->file('image_file', [
                                    'id' => 'image_file',
                                    'accept' => 'image/*',
                                    'style' => 'display: none;'
                                ]) ?>
                                <div id="imagePreviewContainer" class="mt-2 text-center" style="display: none;">
                                    <img id="imagePreview" src="" class="current-image-preview" alt="Preview">
                                    <button type="button" class="btn btn-sm btn-danger mt-2 remove-preview" data-target="image">
                                        <i class="fas fa-times"></i> Remove
                                    </button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (($slideConfig['col1']['type'] ?? '') === 'text' || ($slideConfig['layout'] ?? '') === 'text_only'): ?>
                                <div class="mt-3">
                                    <label class="form-label fw-bold">Text Content</label>
                                    <?php echo $this->Form->textarea('col1_content', [
                                        'class' => 'form-control',
                                        'rows' => 8,
                                        'placeholder' => 'Enter slide text content',
                                        'label' => false,
                                        'id' => 'col1Content',
                                        'value' => $slide->col1_content ?? $slide->description ?? ''
                                    ]) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (($slideConfig['layout'] ?? '') === 'image_with_legend'): ?>
                        <div class="legend-editor">
                            <h6 class="fw-bold mb-3"><i class="fas fa-palette me-2"></i>Legend Items</h6>
                            <div id="legendItems">
                                <?php 
                                $legendItems = $slide->getLegendItems();
                                if (empty($legendItems)) {
                                    $legendItems = [['color' => '#ff0000', 'label' => '']];
                                }
                                foreach ($legendItems as $i => $item): 
                                ?>
                                <div class="legend-item-row">
                                    <input type="color" name="legend_items[<?php echo $i ?>][color]" 
                                           value="<?php echo h($item['color'] ?? '#ff0000') ?>" 
                                           class="legend-color-input">
                                    <input type="text" name="legend_items[<?php echo $i ?>][label]" 
                                           value="<?php echo h($item['label'] ?? '') ?>" 
                                           class="form-control" placeholder="Legend label">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-legend">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addLegendItem">
                                <i class="fas fa-plus me-1"></i>Add Legend Item
                            </button>
                        </div>
                    <?php endif; ?>
                    
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
            <div class="preview-panel">
                <div class="preview-header">
                    <i class="fas fa-eye me-2"></i>Live Preview
                </div>
                <div class="preview-slide" id="slidePreview">
                    <h2 id="previewTitle"><?php echo h($slide->title ?: ($slideConfig['title'] ?? 'Slide Title')) ?></h2>
                    <h4 id="previewSubtitle" class="text-muted" style="font-size: 12px; margin-top: -5px;">• <?php echo h($slide->subtitle ?: ($slideConfig['subtitle'] ?? '')) ?></h4>
                    
                    <?php if ($layoutColumns === 2): ?>
                        <?php 
                        $pptLayouts = unserialize(PPT_LAYOUTS);
                        $layout = $slideConfig['layout'] ?? 'two_column_images';
                        $layoutConfig = $pptLayouts[$layout] ?? [];
                        $col1WidthPercent = $layoutConfig['col1_width_percent'] ?? 50;
                        $col2WidthPercent = $layoutConfig['col2_width_percent'] ?? 50;
                        ?>
                        <div class="preview-two-columns">
                            <div class="preview-column" style="flex: <?php echo $col1WidthPercent ?>;">
                                <div class="preview-column-header" id="previewCol1Header">
                                    <?php echo h($slide->col1_header ?? $slideConfig['col1']['header'] ?? 'Column 1') ?>
                                </div>
                                <div id="previewCol1Content">
                                    <?php if ($slide->col1_image_url ?? $slide->col1_image_path): ?>
                                        <img src="<?php echo h($slide->col1_image_url ?? $slide->col1_image_path) ?>" alt="Column 1">
                                    <?php elseif ($slide->col1_content): ?>
                                        <p style="font-size: 11px;"><?php echo nl2br(h($slide->col1_content)) ?></p>
                                    <?php else: ?>
                                        <div class="text-center text-muted" style="margin-top: 30px;">
                                            <i class="fas fa-image fa-2x"></i>
                                            <div style="font-size: 10px;">Column 1</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="preview-column" style="flex: <?php echo $col2WidthPercent ?>;">
                                <div class="preview-column-header" id="previewCol2Header">
                                    <?php echo h($slide->col2_header ?? $slideConfig['col2']['header'] ?? 'Column 2') ?>
                                </div>
                                <div id="previewCol2Content">
                                    <?php if ($slide->col2_image_url ?? $slide->col2_image_path): ?>
                                        <img src="<?php echo h($slide->col2_image_url ?? $slide->col2_image_path) ?>" alt="Column 2">
                                    <?php elseif ($slide->col2_content): ?>
                                        <p style="font-size: 11px;"><?php echo nl2br(h($slide->col2_content)) ?></p>
                                    <?php else: ?>
                                        <div class="text-center text-muted" style="margin-top: 30px;">
                                            <i class="fas fa-image fa-2x"></i>
                                            <div style="font-size: 10px;">Column 2</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="preview-single" id="previewContent">
                            <?php if ($slide->col1_image_url ?? $slide->image_url ?? $slide->file_path): ?>
                                <img src="<?php echo h($slide->col1_image_url ?? $slide->image_url ?? $slide->file_path) ?>" alt="Slide Image">
                            <?php elseif ($slide->col1_content): ?>
                                <p><?php echo nl2br(h($slide->col1_content)) ?></p>
                            <?php else: ?>
                                <div class="text-center text-muted" style="margin-top: 50px;">
                                    <i class="fas fa-image fa-3x"></i>
                                    <div class="mt-2">No content yet</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php echo $this->Form->end() ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    
    // Live preview updates for title
    $('#slideTitle').on('input keyup change paste', function() {
        var val = $(this).val();
        $('#previewTitle').text(val || 'Slide Title');
    });
    
    // Live preview updates for subtitle
    $('#slideSubtitle').on('input keyup change paste', function() {
        var val = $(this).val();
        if (val && val.trim() !== '') {
            $('#previewSubtitle').text('• ' + val).show();
        } else {
            $('#previewSubtitle').hide();
        }
    });
    
    // Column headers
    $('input[name="col1_header"], textarea[name="col1_header"]').on('input keyup change paste', function() {
        $('#previewCol1Header').text($(this).val() || 'Column 1');
    });
    
    $('input[name="col2_header"], textarea[name="col2_header"]').on('input keyup change paste', function() {
        $('#previewCol2Header').text($(this).val() || 'Column 2');
    });
    $('.upload-zone').click(function() {
        const targetId = $(this).data('target');
        $('#' + targetId).click();
    });
    
    $('.upload-zone').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    }).on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    }).on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        const targetId = $(this).data('target');
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            $('#' + targetId)[0].files = files;
            handleFileSelect(targetId, files[0]);
        }
    });
    
    $('#col1_image_file, #col2_image_file, #image_file').change(function() {
        if (this.files && this.files[0]) {
            handleFileSelect(this.id, this.files[0]);
        }
    });
    
    function handleFileSelect(inputId, file) {
        if (!file.type.match('image.*')) {
            alert('Please select an image file.');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            if (inputId === 'col1_image_file') {
                $('#col1Preview').attr('src', e.target.result);
                $('#col1PreviewContainer').show();
                updatePreviewImage('col1', e.target.result);
            } else if (inputId === 'col2_image_file') {
                $('#col2Preview').attr('src', e.target.result);
                $('#col2PreviewContainer').show();
                updatePreviewImage('col2', e.target.result);
            } else {
                $('#imagePreview').attr('src', e.target.result);
                $('#imagePreviewContainer').show();
                updatePreviewImage('single', e.target.result);
            }
        };
        reader.readAsDataURL(file);
    }
    
    function updatePreviewImage(column, src) {
        if (column === 'col1') {
            $('#previewCol1Content').html('<img src="' + src + '" alt="Column 1">');
        } else if (column === 'col2') {
            $('#previewCol2Content').html('<img src="' + src + '" alt="Column 2">');
        } else {
            $('#previewContent').html('<img src="' + src + '" alt="Slide Image">');
        }
    }
    
    $('.remove-preview').click(function() {
        const target = $(this).data('target');
        if (target === 'col1') {
            $('#col1_image_file').val('');
            $('#col1PreviewContainer').hide();
        } else if (target === 'col2') {
            $('#col2_image_file').val('');
            $('#col2PreviewContainer').hide();
        } else {
            $('#image_file').val('');
            $('#imagePreviewContainer').hide();
        }
    });
    
    $('#col1Content').on('input', function() {
        const text = $(this).val();
        if (text) {
            $('#previewCol1Content, #previewContent').html('<p style="font-size: 11px;">' + text.replace(/\n/g, '<br>') + '</p>');
        }
    });
    $('#col2Content').on('input', function() {
        const text = $(this).val();
        if (text) {
            $('#previewCol2Content').html('<p style="font-size: 11px;">' + text.replace(/\n/g, '<br>') + '</p>');
        }
    });
    
    let legendIndex = <?php echo count($legendItems ?? [1]); ?>;
    
    $('#addLegendItem').click(function() {
        const html = '<div class="legend-item-row">' +
            '<input type="color" name="legend_items[' + legendIndex + '][color]" value="#ff0000" class="legend-color-input">' +
            '<input type="text" name="legend_items[' + legendIndex + '][label]" class="form-control" placeholder="Legend label">' +
            '<button type="button" class="btn btn-outline-danger btn-sm remove-legend"><i class="fas fa-times"></i></button>' +
            '</div>';
        $('#legendItems').append(html);
        legendIndex++;
    });
    
    $(document).on('click', '.remove-legend', function() {
        $(this).closest('.legend-item-row').remove();
    });
});
</script>
