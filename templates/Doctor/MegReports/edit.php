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
.upload-zone-sm {
    padding: 15px 10px;
    border-width: 2px;
}
.upload-zone-sm i {
    font-size: 1rem;
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
/* Structured Bullets Editor Styles */
.structured-bullets-editor {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    background: #fafafa;
    max-height: 500px;
    overflow-y: auto;
}
.summary-section {
    border-left: 3px solid #dc3545;
    padding-left: 15px;
    background: white;
    border-radius: 0 8px 8px 0;
    padding: 15px;
}
.section-item {
    border-left: 2px solid #6c757d;
}
.section-items .btn-outline-secondary {
    border-style: dashed;
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
                        '<i class="fas fa-sync-alt me-2"></i>Reset to Template',
                        ['action' => 'resetSlide', $slide->id],
                        ['class' => 'btn btn-warning me-2', 'escape' => false, 'confirm' => 'This will reset this slide to its default template values. Any custom text content will be lost. Continue?']
                    ) ?>
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
                    
                    <?php if (($slideConfig['layout'] ?? '') === 'text_header_two_images'): ?>
                        <!-- Text Header Two Images Layout (e.g., functional_mapping_language) -->
                        
                        <!-- Header Text Section -->
                        <div class="column-section mb-4">
                            <h5><i class="fas fa-list me-2"></i>Header Text (Bullet Points)</h5>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-info-circle me-1"></i>Enter each bullet point on a new line. Bullets (•) are added automatically.
                            </p>
                            <?php 
                            $headerContent = $slide->col1_content ?? '';
                            if (empty($headerContent) && isset($slideConfig['header_text']['content'])) {
                                $defaultContent = $slideConfig['header_text']['content'];
                                if (is_array($defaultContent)) {
                                    $headerContent = implode("\n", $defaultContent);
                                } else {
                                    $headerContent = $defaultContent;
                                }
                            }
                            ?>
                            <?php echo $this->Form->textarea('col1_content', [
                                'class' => 'form-control',
                                'rows' => 5,
                                'placeholder' => "First bullet point text here\nSecond bullet point text here",
                                'label' => false,
                                'id' => 'headerTextContent',
                                'value' => $headerContent
                            ]) ?>
                            
                            <!-- Live Bullet Preview -->
                            <div class="mt-3 p-3 bg-light border rounded" id="bulletPreviewContainer">
                                <small class="text-muted d-block mb-2"><i class="fas fa-eye me-1"></i>Preview:</small>
                                <div id="bulletPreview" style="font-size: 13px; line-height: 1.6;">
                                    <?php 
                                    if (!empty($headerContent)) {
                                        $lines = preg_split('/\r\n|\r|\n/', $headerContent);
                                        foreach ($lines as $line) {
                                            $line = trim($line);
                                            if (!empty($line)) {
                                                echo '<div>• ' . h(strip_tags($line)) . '</div>';
                                            }
                                        }
                                    } else {
                                        echo '<div class="text-muted fst-italic">Enter text above to see bullet preview</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Two Image Columns -->
                        <div class="row">
                            <!-- Left Image -->
                            <div class="col-md-6">
                                <div class="column-section">
                                    <h5><i class="fas fa-image me-2"></i>Left Image</h5>
                                    <?php if ($slide->col1_image_url ?? $slide->col1_image_path): ?>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Current Image:</label>
                                            <div class="text-center">
                                                <img src="<?php echo h($slide->col1_image_url ?? $slide->col1_image_path) ?>" 
                                                     alt="Left Image" class="current-image-preview" id="col1CurrentImage">
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
                                </div>
                            </div>
                            
                            <!-- Right Image -->
                            <div class="col-md-6">
                                <div class="column-section">
                                    <h5><i class="fas fa-image me-2"></i>Right Image</h5>
                                    <?php if ($slide->col2_image_url ?? $slide->col2_image_path): ?>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">Current Image:</label>
                                            <div class="text-center">
                                                <img src="<?php echo h($slide->col2_image_url ?? $slide->col2_image_path) ?>" 
                                                     alt="Right Image" class="current-image-preview" id="col2CurrentImage">
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
                                </div>
                            </div>
                        </div>
                    <?php elseif ($layoutColumns === 2): ?>
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
                                        <?php 
                                        $col1ContentValue = $slide->col1_content ?? '';
                                        if (empty($col1ContentValue) && isset($slideConfig['col1']['default_content'])) {
                                            $col1ContentValue = $slideConfig['col1']['default_content'];
                                        }
                                        ?>
                                        <?php echo $this->Form->textarea('col1_content', [
                                            'class' => 'form-control',
                                            'rows' => 6,
                                            'placeholder' => 'Enter column 1 content',
                                            'label' => false,
                                            'id' => 'col1Content',
                                            'value' => $col1ContentValue
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Column 2 -->
                            <div class="col-md-6">
                                <div class="column-section">
                                    <?php 
                                    $isStackedSlide = !empty($slideConfig['stacked_images']);
                                    $col2Type = $slideConfig['col2']['type'] ?? $slide->col2_type ?? 'image';
                                    ?>
                                    <h5><i class="fas fa-columns me-2"></i><?php echo $isStackedSlide ? 'Images (Stacked)' : 'Column 2' ?></h5>
                                    
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
                                    
                                    <?php if ($isStackedSlide): ?>
                                        <!-- Stacked Images: Top (col2) and Bottom (col3) -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold"><i class="fas fa-arrow-up me-1"></i>Top Image (<?php echo h($slideConfig['col2']['description'] ?? 'Top') ?>)</label>
                                            <?php if ($slide->col2_image_url ?? $slide->col2_image_path): ?>
                                                <div class="mb-2 text-center">
                                                    <img src="<?php echo h($slide->col2_image_url ?? $slide->col2_image_path) ?>" 
                                                         alt="Top Image" class="current-image-preview" id="col2CurrentImage">
                                                </div>
                                            <?php endif; ?>
                                            <div class="upload-zone" id="col2UploadZone" data-target="col2_image_file">
                                                <i class="fas fa-image fa-2x text-danger mb-2"></i>
                                                <div>Upload Top Image</div>
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
                                        </div>
                                        
                                        <hr class="my-3">
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold"><i class="fas fa-arrow-down me-1"></i>Bottom Image (<?php echo h($slideConfig['col3']['description'] ?? 'Bottom') ?>)</label>
                                            <?php if ($slide->col3_image_url ?? $slide->col3_image_path): ?>
                                                <div class="mb-2 text-center">
                                                    <img src="<?php echo h($slide->col3_image_url ?? $slide->col3_image_path) ?>" 
                                                         alt="Bottom Image" class="current-image-preview" id="col3CurrentImage">
                                                </div>
                                            <?php endif; ?>
                                            <div class="upload-zone" id="col3UploadZone" data-target="col3_image_file">
                                                <i class="fas fa-image fa-2x text-danger mb-2"></i>
                                                <div>Upload Bottom Image</div>
                                                <small class="text-muted">Drag & drop or click</small>
                                            </div>
                                            <?php echo $this->Form->file('col3_image_file', [
                                                'id' => 'col3_image_file',
                                                'accept' => 'image/*',
                                                'style' => 'display: none;'
                                            ]) ?>
                                            <div id="col3PreviewContainer" class="mt-2 text-center" style="display: none;">
                                                <img id="col3Preview" src="" class="current-image-preview" alt="Preview">
                                                <button type="button" class="btn btn-sm btn-danger mt-2 remove-preview" data-target="col3">
                                                    <i class="fas fa-times"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    <?php elseif ($col2Type === 'image' || $col2Type === 'composite_image'): ?>
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
                                        <?php 
                                        $col2ContentValue = $slide->col2_content ?? '';
                                        if (empty($col2ContentValue) && isset($slideConfig['col2']['default_content'])) {
                                            $col2ContentValue = $slideConfig['col2']['default_content'];
                                        }
                                        ?>
                                        <?php echo $this->Form->textarea('col2_content', [
                                            'class' => 'form-control',
                                            'rows' => 6,
                                            'placeholder' => 'Enter column 2 content',
                                            'label' => false,
                                            'id' => 'col2Content',
                                            'value' => $col2ContentValue
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php elseif (($slideConfig['layout'] ?? '') === 'multi_image_with_titles'): ?>
                        <!-- Multi-Image Layout (up to 5 images with titles) -->
                        <div class="column-section">
                            <h5><i class="fas fa-images me-2"></i>Multiple Images</h5>
                            <p class="text-muted mb-3"><?= h($slideConfig['col1']['description'] ?? 'Upload up to 5 images') ?></p>
                            
                            <?php 
                            $maxImages = $slideConfig['max_images'] ?? 5;
                            $defaultTitles = $slideConfig['default_image_titles'] ?? [];
                            $imageColumns = ['col1', 'col2', 'col3', 'col4', 'col5'];
                            ?>
                            
                            <div class="row g-3">
                                <?php for ($i = 0; $i < $maxImages; $i++): 
                                    $colName = $imageColumns[$i];
                                    $imagePathField = $colName . '_image_path';
                                    $imageUrlField = $colName . '_image_url';
                                    $headerField = $colName . '_header';
                                    $fileInputId = $colName . '_image_file';
                                    $defaultTitle = $defaultTitles[$i] ?? 'Discharge ' . ($i + 1);
                                ?>
                                <div class="col-md-4 col-lg-4">
                                    <div class="card h-100">
                                        <div class="card-header bg-light py-2">
                                            <span class="badge bg-danger me-2"><?= $i + 1 ?></span>
                                            <small class="text-muted">Image <?= $i + 1 ?></small>
                                        </div>
                                        <div class="card-body p-2">
                                            <div class="mb-2">
                                                <input type="text" 
                                                       name="<?= $headerField ?>" 
                                                       class="form-control form-control-sm"
                                                       value="<?= h($slide->{$headerField} ?? $defaultTitle) ?>"
                                                       placeholder="Image title">
                                            </div>
                                            
                                            <?php if ($slide->{$imageUrlField} ?? $slide->{$imagePathField}): ?>
                                                <div class="mb-2 text-center">
                                                    <img src="<?= h($slide->{$imageUrlField} ?? $slide->{$imagePathField}) ?>" 
                                                         alt="Image <?= $i + 1 ?>" 
                                                         class="img-fluid rounded" 
                                                         style="max-height: 120px;"
                                                         id="<?= $colName ?>CurrentImage">
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="upload-zone upload-zone-sm" id="<?= $colName ?>UploadZone" data-target="<?= $fileInputId ?>">
                                                <i class="fas fa-image text-danger mb-1"></i>
                                                <div><small><?= ($slide->{$imagePathField}) ? 'Replace' : 'Upload' ?></small></div>
                                            </div>
                                            <?= $this->Form->file($fileInputId, [
                                                'id' => $fileInputId,
                                                'accept' => 'image/*',
                                                'style' => 'display: none;'
                                            ]) ?>
                                            <div id="<?= $colName ?>PreviewContainer" class="mt-2 text-center" style="display: none;">
                                                <img id="<?= $colName ?>Preview" src="" class="img-fluid rounded" style="max-height: 100px;" alt="Preview">
                                                <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-preview" data-target="<?= $colName ?>">
                                                    <i class="fas fa-times"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endfor; ?>
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
                                    
                                    <?php if (($slideConfig['col1']['format'] ?? '') === 'structured_bullets' && !empty($slideConfig['default_sections'])): ?>
                                        <?php 
                                        // Parse existing content or use defaults
                                        $existingSections = [];
                                        if (!empty($slide->col1_content)) {
                                            $decoded = json_decode($slide->col1_content, true);
                                            if (is_array($decoded)) {
                                                $existingSections = $decoded;
                                            }
                                        }
                                        // If no existing data, use defaults
                                        if (empty($existingSections)) {
                                            $existingSections = $slideConfig['default_sections'];
                                        }
                                        ?>
                                        <!-- Structured Bullets Editor for Summary Slide -->
                                        <div class="structured-bullets-editor" id="structuredBulletsEditor">
                                            <?php foreach ($existingSections as $sectionIndex => $section): ?>
                                            <div class="summary-section mb-4" data-section="<?= $sectionIndex ?>">
                                                <div class="section-heading mb-3">
                                                    <label class="form-label small text-muted">Section Heading</label>
                                                    <div class="input-group">
                                                        <input type="text" 
                                                               name="structured_sections[<?= $sectionIndex ?>][heading]" 
                                                               class="form-control form-control-sm fw-bold"
                                                               value="<?= h($section['heading'] ?? '') ?>"
                                                               placeholder="Section heading">
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-section-btn">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <div class="section-items ms-3">
                                                    <?php foreach ($section['items'] ?? [] as $itemIndex => $item): ?>
                                                    <div class="section-item mb-3 p-2 bg-light rounded" data-item="<?= $itemIndex ?>">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <label class="form-label small text-muted mb-0">Item Title</label>
                                                            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item-btn">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                        <input type="text" 
                                                               name="structured_sections[<?= $sectionIndex ?>][items][<?= $itemIndex ?>][title]" 
                                                               class="form-control form-control-sm mb-2"
                                                               value="<?= h($item['title'] ?? '') ?>"
                                                               placeholder="Item title">
                                                        
                                                        <label class="form-label small text-muted">Sub-items (one per line)</label>
                                                        <textarea name="structured_sections[<?= $sectionIndex ?>][items][<?= $itemIndex ?>][subitems_text]" 
                                                                  class="form-control form-control-sm" 
                                                                  rows="3"
                                                                  placeholder="Enter sub-items, one per line"><?= h(implode("\n", $item['subitems'] ?? [])) ?></textarea>
                                                    </div>
                                                    <?php endforeach; ?>
                                                    
                                                    <button type="button" class="btn btn-sm btn-outline-secondary add-item-btn" data-section="<?= $sectionIndex ?>">
                                                        <i class="fas fa-plus me-1"></i>Add Item
                                                    </button>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                            
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="addSectionBtn">
                                                <i class="fas fa-plus me-1"></i>Add Section
                                            </button>
                                        </div>
                                        
                                        <!-- Hidden field to store JSON data -->
                                        <?= $this->Form->hidden('col1_content', ['id' => 'col1ContentJson']) ?>
                                        <?= $this->Form->hidden('content_format', ['value' => 'structured_bullets']) ?>
                                        
                                    <?php else: ?>
                                        <?php echo $this->Form->textarea('col1_content', [
                                            'class' => 'form-control',
                                            'rows' => 8,
                                            'placeholder' => 'Enter slide text content',
                                            'label' => false,
                                            'id' => 'col1Content',
                                            'value' => $slide->col1_content ?? $slide->description ?? ''
                                        ]) ?>
                                    <?php endif; ?>
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
                    
                    <?php if (($slideConfig['layout'] ?? '') === 'text_header_two_images'): ?>
                        <!-- Text Header Two Images Layout Preview -->
                        <?php 
                        $headerContent = $slide->col1_content ?? '';
                        if (empty($headerContent) && isset($slideConfig['header_text']['content'])) {
                            $defaultContent = $slideConfig['header_text']['content'];
                            if (is_array($defaultContent)) {
                                $headerContent = implode("\n", $defaultContent);
                            } else {
                                $headerContent = $defaultContent;
                            }
                        }
                        ?>
                        <!-- Header Text Bullets (Full Width) -->
                        <div id="previewHeaderText" class="mb-2" style="font-size: 11px; line-height: 1.5; text-align: left; padding: 5px 10px; background: #f8f9fa; border-radius: 4px;">
                            <?php 
                            if (!empty($headerContent)) {
                                $lines = preg_split('/\r\n|\r|\n/', $headerContent);
                                foreach ($lines as $line) {
                                    $line = trim($line);
                                    if (!empty($line)) {
                                        echo '<div>• ' . h(strip_tags($line)) . '</div>';
                                    }
                                }
                            } else {
                                echo '<div class="text-muted fst-italic">Header text will appear here</div>';
                            }
                            ?>
                        </div>
                        
                        <!-- Two Image Columns -->
                        <div class="preview-two-columns">
                            <div class="preview-column" style="flex: 50;">
                                <div id="previewCol1Content">
                                    <?php if ($slide->col1_image_url ?? $slide->col1_image_path): ?>
                                        <img src="<?php echo h($slide->col1_image_url ?? $slide->col1_image_path) ?>" alt="Left Image">
                                    <?php else: ?>
                                        <div class="text-center text-muted" style="margin-top: 20px;">
                                            <i class="fas fa-image fa-2x"></i>
                                            <div style="font-size: 10px;">Left Image</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="preview-column" style="flex: 50;">
                                <div id="previewCol2Content">
                                    <?php if ($slide->col2_image_url ?? $slide->col2_image_path): ?>
                                        <img src="<?php echo h($slide->col2_image_url ?? $slide->col2_image_path) ?>" alt="Right Image">
                                    <?php else: ?>
                                        <div class="text-center text-muted" style="margin-top: 20px;">
                                            <i class="fas fa-image fa-2x"></i>
                                            <div style="font-size: 10px;">Right Image</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($layoutColumns === 2): ?>
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
                                    <?php 
                                    $isStackedPreview = !empty($slideConfig['stacked_images']);
                                    if ($isStackedPreview): ?>
                                        <!-- Stacked images preview -->
                                        <div style="display: flex; flex-direction: column; gap: 4px; height: 100%;">
                                            <div style="flex: 1; display: flex; align-items: center; justify-content: center;" data-stacked-top>
                                                <?php if ($slide->col2_image_url ?? $slide->col2_image_path): ?>
                                                    <img src="<?php echo h($slide->col2_image_url ?? $slide->col2_image_path) ?>" alt="Top" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                <?php else: ?>
                                                    <div class="text-muted" style="font-size: 9px;"><i class="fas fa-image"></i> Top</div>
                                                <?php endif; ?>
                                            </div>
                                            <div style="flex: 1; display: flex; align-items: center; justify-content: center;" data-stacked-bottom>
                                                <?php if ($slide->col3_image_url ?? $slide->col3_image_path): ?>
                                                    <img src="<?php echo h($slide->col3_image_url ?? $slide->col3_image_path) ?>" alt="Bottom" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                <?php else: ?>
                                                    <div class="text-muted" style="font-size: 9px;"><i class="fas fa-image"></i> Bottom</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php elseif ($slide->col2_image_url ?? $slide->col2_image_path): ?>
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
    
    // Live preview for header text bullet points
    $('#headerTextContent').on('input keyup change paste', function() {
        var val = $(this).val();
        var $preview = $('#bulletPreview');
        var $slidePreview = $('#previewHeaderText');
        
        var html = '';
        if (val && val.trim() !== '') {
            var lines = val.split(/\r?\n/);
            lines.forEach(function(line) {
                line = line.trim();
                if (line) {
                    // Escape HTML but preserve the line
                    var escaped = $('<div>').text(line).html();
                    html += '<div>• ' + escaped + '</div>';
                }
            });
        }
        
        // Update form preview (bullet preview section)
        if ($preview.length) {
            $preview.html(html || '<div class="text-muted fst-italic">Enter text above to see bullet preview</div>');
        }
        
        // Update slide preview (right side)
        if ($slidePreview.length) {
            $slidePreview.html(html || '<div class="text-muted fst-italic">Header text will appear here</div>');
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
    
    $('#col1_image_file, #col2_image_file, #col3_image_file, #col4_image_file, #col5_image_file, #image_file').change(function() {
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
            // Handle col1-col5 image files
            const colMatch = inputId.match(/^(col[1-5])_image_file$/);
            if (colMatch) {
                const colName = colMatch[1];
                $('#' + colName + 'Preview').attr('src', e.target.result);
                $('#' + colName + 'PreviewContainer').show();
                // Update current image if it exists
                if ($('#' + colName + 'CurrentImage').length) {
                    $('#' + colName + 'CurrentImage').attr('src', e.target.result);
                }
                updatePreviewImage(colName, e.target.result);
            } else if (inputId === 'image_file') {
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
            // Check if stacked layout - update only the top image portion
            var stackedContainer = $('#previewCol2Content').find('[data-stacked-top]');
            if (stackedContainer.length) {
                stackedContainer.html('<img src="' + src + '" alt="Top" style="max-width:100%;max-height:100%;object-fit:contain;">');
            } else {
                $('#previewCol2Content').html('<img src="' + src + '" alt="Column 2">');
            }
        } else if (column === 'col3') {
            // Update bottom image in stacked layout
            var stackedBottom = $('#previewCol2Content').find('[data-stacked-bottom]');
            if (stackedBottom.length) {
                stackedBottom.html('<img src="' + src + '" alt="Bottom" style="max-width:100%;max-height:100%;object-fit:contain;">');
            }
        } else {
            $('#previewContent').html('<img src="' + src + '" alt="Slide Image">');
        }
    }
    
    $('.remove-preview').click(function() {
        const target = $(this).data('target');
        // Handle col1-col5 
        if (target.match(/^col[1-5]$/)) {
            $('#' + target + '_image_file').val('');
            $('#' + target + 'PreviewContainer').hide();
        } else if (target === 'image') {
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
    
    // Structured Bullets Editor for Summary Slide
    const structuredEditor = document.getElementById('structuredBulletsEditor');
    if (structuredEditor) {
        let sectionCounter = structuredEditor.querySelectorAll('.summary-section').length;
        let itemCounters = {};
        
        // Initialize item counters
        structuredEditor.querySelectorAll('.summary-section').forEach((section, sIndex) => {
            itemCounters[sIndex] = section.querySelectorAll('.section-item').length;
        });
        
        // Add new section
        document.getElementById('addSectionBtn')?.addEventListener('click', function() {
            const sectionHtml = `
                <div class="summary-section mb-4" data-section="${sectionCounter}">
                    <div class="section-heading mb-3">
                        <label class="form-label small text-muted">Section Heading</label>
                        <div class="input-group">
                            <input type="text" 
                                   name="structured_sections[${sectionCounter}][heading]" 
                                   class="form-control form-control-sm fw-bold"
                                   placeholder="Section heading">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-section-btn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="section-items ms-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary add-item-btn" data-section="${sectionCounter}">
                            <i class="fas fa-plus me-1"></i>Add Item
                        </button>
                    </div>
                </div>
            `;
            this.insertAdjacentHTML('beforebegin', sectionHtml);
            itemCounters[sectionCounter] = 0;
            sectionCounter++;
        });
        
        // Add new item to section
        structuredEditor.addEventListener('click', function(e) {
            if (e.target.closest('.add-item-btn')) {
                const btn = e.target.closest('.add-item-btn');
                const sectionIndex = btn.dataset.section;
                const itemIndex = itemCounters[sectionIndex] || 0;
                
                const itemHtml = `
                    <div class="section-item mb-3 p-2 bg-light rounded" data-item="${itemIndex}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <label class="form-label small text-muted mb-0">Item Title</label>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-item-btn">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <input type="text" 
                               name="structured_sections[${sectionIndex}][items][${itemIndex}][title]" 
                               class="form-control form-control-sm mb-2"
                               placeholder="Item title">
                        <label class="form-label small text-muted">Sub-items (one per line)</label>
                        <textarea name="structured_sections[${sectionIndex}][items][${itemIndex}][subitems_text]" 
                                  class="form-control form-control-sm" 
                                  rows="3"
                                  placeholder="Enter sub-items, one per line"></textarea>
                    </div>
                `;
                btn.insertAdjacentHTML('beforebegin', itemHtml);
                itemCounters[sectionIndex] = itemIndex + 1;
            }
            
            // Remove section
            if (e.target.closest('.remove-section-btn')) {
                e.target.closest('.summary-section').remove();
            }
            
            // Remove item
            if (e.target.closest('.remove-item-btn')) {
                e.target.closest('.section-item').remove();
            }
        });
        
        // Before form submit, convert structured data to JSON
        document.querySelector('.slide-form')?.addEventListener('submit', function(e) {
            const jsonField = document.getElementById('col1ContentJson');
            if (jsonField && structuredEditor) {
                const sections = [];
                structuredEditor.querySelectorAll('.summary-section').forEach(section => {
                    const headingInput = section.querySelector('input[name*="[heading]"]');
                    const sectionData = {
                        heading: headingInput ? headingInput.value : '',
                        items: []
                    };
                    
                    section.querySelectorAll('.section-item').forEach(item => {
                        const titleInput = item.querySelector('input[name*="[title]"]');
                        const subitemsTextarea = item.querySelector('textarea[name*="[subitems_text]"]');
                        
                        const subitems = subitemsTextarea ? 
                            subitemsTextarea.value.split('\n').filter(line => line.trim() !== '') : [];
                        
                        sectionData.items.push({
                            title: titleInput ? titleInput.value : '',
                            subitems: subitems
                        });
                    });
                    
                    sections.push(sectionData);
                });
                
                jsonField.value = JSON.stringify(sections);
            }
        });
        
        // Live preview update function for structured bullets (matches PPT output)
        function updateStructuredPreview() {
            const previewContent = document.getElementById('previewContent');
            if (!previewContent || !structuredEditor) return;
            
            let html = '<div style="text-align: left; font-size: 10px; overflow: auto; max-height: 100%; padding: 10px;">';
            let isFirst = true;
            
            structuredEditor.querySelectorAll('.summary-section').forEach(section => {
                const headingInput = section.querySelector('input[name*="[heading]"]');
                const heading = headingInput ? headingInput.value : '';
                
                // Section heading - bold, 1.5 line height (matches PPT)
                if (heading) {
                    const marginTop = isFirst ? '0' : '10px';
                    html += '<div style="font-weight: bold; line-height: 1.5; margin-top: ' + marginTop + '; margin-bottom: 4px;">' + $('<div>').text(heading).html() + '</div>';
                    isFirst = false;
                }
                
                section.querySelectorAll('.section-item').forEach(item => {
                    const titleInput = item.querySelector('input[name*="[title]"]');
                    const subitemsTextarea = item.querySelector('textarea[name*="[subitems_text]"]');
                    
                    const title = titleInput ? titleInput.value : '';
                    const subitems = subitemsTextarea ? 
                        subitemsTextarea.value.split('\n').filter(line => line.trim() !== '') : [];
                    
                    // Item - 1.4 line height, indented with bullet (matches PPT)
                    if (title) {
                        html += '<div style="line-height: 1.4; margin-left: 15px; margin-bottom: 2px;">• ' + $('<div>').text(title).html() + '</div>';
                    }
                    
                    // Subitems - 1.3 line height, more indented, smaller (matches PPT)
                    subitems.forEach(subitem => {
                        const truncated = subitem.length > 60 ? subitem.substring(0, 60) + '...' : subitem;
                        html += '<div style="line-height: 1.3; margin-left: 30px; font-size: 0.95em; color: #333; margin-bottom: 1px;">○ ' + $('<div>').text(truncated).html() + '</div>';
                    });
                });
            });
            
            html += '</div>';
            previewContent.innerHTML = html;
        }
        
        // Attach live preview listeners to structured editor
        structuredEditor.addEventListener('input', updateStructuredPreview);
        structuredEditor.addEventListener('click', function(e) {
            // Delay to allow DOM changes from add/remove operations
            setTimeout(updateStructuredPreview, 100);
        });
        
        // Initial preview update
        updateStructuredPreview();
    }
});
</script>
