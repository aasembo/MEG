<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ReportSlide $slide
 * @var \App\Model\Entity\Report $report
 * @var int $reportId
 * @var array $examProceduresList
 * @var string|null $slideType
 * @var array|null $slideConfig
 * @var array $slideTypes
 * @var array $slideCategories
 */
$this->assign('title', 'Add Slide');
$slideType = $slideType ?? null;
$slideConfig = $slideConfig ?? null;
$slideTypes = $slideTypes ?? [];
$slideCategories = $slideCategories ?? [];
?>

<style>
.slide-type-selection {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}
.slide-type-card {
    border: 2px solid #dee2e6;
    border-radius: 10px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}
.slide-type-card:hover {
    border-color: #dc3545;
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.15);
    transform: translateY(-2px);
}
.slide-type-icon {
    font-size: 24px;
    margin-bottom: 8px;
    color: #dc3545;
}
.slide-type-title {
    font-weight: bold;
    margin-bottom: 5px;
}
.slide-type-meta {
    font-size: 12px;
    color: #6c757d;
}
.slide-preview-container {
    background: #2d2d2d;
    padding: 20px;
    border-radius: 10px;
    min-height: 350px;
}
.slide-preview {
    background: white;
    aspect-ratio: 16/9;
    border-radius: 5px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    position: relative;
    overflow: hidden;
    max-width: 100%;
}
.slide-preview-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
}
.slide-preview-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: bold;
}
.slide-preview-header p {
    margin: 5px 0 0;
    font-size: 14px;
    color: #666;
}
.slide-preview-body {
    padding: 15px 20px;
    height: calc(100% - 80px);
}
.slide-preview-body.two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}
.preview-column {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    padding: 10px;
    text-align: center;
}
.preview-column.has-content {
    border-style: solid;
    border-color: #28a745;
}
.upload-dropzone {
    border: 3px dashed #dc3545;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    background: #fff5f5;
    cursor: pointer;
    transition: all 0.3s ease;
}
.upload-dropzone:hover,
.upload-dropzone.dragover {
    background: #ffe5e8;
    transform: scale(1.02);
}
.image-preview-thumb {
    max-width: 100%;
    max-height: 200px;
    border-radius: 5px;
    object-fit: contain;
}
.category-section {
    margin-bottom: 25px;
}
.category-title {
    font-size: 14px;
    font-weight: bold;
    color: #6c757d;
    margin-bottom: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
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
                        <i class="fas fa-folder-open me-2"></i>Case ID: <?= h($report->case_id ?? 'N/A') ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <?= $this->Html->link(
                        '<i class="fas fa-arrow-left me-2"></i>Back to Slides',
                        ['action' => 'index', $reportId],
                        ['class' => 'btn btn-light', 'escape' => false]
                    ) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$slideType): ?>
        <!-- Step 1: Select Slide Type -->
        <div class="card border-0 shadow">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0 fw-bold text-dark">
                    <span class="badge bg-danger me-2">1</span>Select Slide Type
                </h5>
            </div>
            <div class="card-body p-4">
                <?php foreach ($slideCategories as $categoryKey => $category): ?>
                    <div class="category-section">
                        <div class="category-title">
                            <i class="fas fa-folder me-2"></i><?= h($category['name']) ?>
                        </div>
                        <div class="slide-type-selection">
                            <?php foreach ($category['slides'] as $typeKey): ?>
                                <?php if (isset($slideTypes[$typeKey])): ?>
                                    <?php $config = $slideTypes[$typeKey]; ?>
                                    <?php if ($typeKey === 'cover_page') continue; ?>
                                    <a href="<?= $this->Url->build(['action' => 'add', '?' => ['report_id' => $reportId, 'slide_type' => $typeKey]]) ?>" 
                                       class="slide-type-card text-decoration-none text-dark">
                                        <div class="d-flex align-items-start">
                                            <div class="slide-type-icon me-3">
                                                <?php if (($config['columns'] ?? 1) == 2): ?>
                                                    <i class="fas fa-columns"></i>
                                                <?php elseif (($config['layout'] ?? '') === 'text_only' || ($config['layout'] ?? '') === 'text_bullets'): ?>
                                                    <i class="fas fa-align-left"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-image"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <div class="slide-type-title"><?= h($config['title'] ?? ucwords(str_replace('_', ' ', $typeKey))) ?></div>
                                                <div class="slide-type-meta">
                                                    <?= ($config['columns'] ?? 1) ?> column(s) • 
                                                    <?= h($config['layout'] ?? 'single_image') ?>
                                                    <?php if (!empty($config['subtitle'])): ?>
                                                        <br><small><?= h($config['subtitle']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Step 2: Configure Slide Content -->
        <?= $this->Form->create($slide, ['type' => 'file', 'id' => 'slideForm']) ?>
        <?= $this->Form->hidden('slide_type', ['value' => $slideType]) ?>
        
        <div class="row">
            <!-- Left Panel: Form Controls -->
            <div class="col-lg-5 mb-4">
                <div class="card border-0 shadow mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-dark">
                            <span class="badge bg-danger me-2">2</span>Slide Content
                            <a href="<?= $this->Url->build(['action' => 'add', '?' => ['report_id' => $reportId]]) ?>" 
                               class="btn btn-sm btn-outline-secondary float-end">
                                <i class="fas fa-undo me-1"></i>Change Type
                            </a>
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <!-- Slide Title -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Slide Title</label>
                            <?= $this->Form->control('title', [
                                'class' => 'form-control',
                                'placeholder' => 'Enter slide title',
                                'label' => false,
                                'id' => 'slideTitle',
                                'value' => $slide->title ?? ''
                            ]) ?>
                        </div>

                        <?php if (!empty($slideConfig['subtitle'])): ?>
                        <!-- Subtitle -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Subtitle</label>
                            <?= $this->Form->control('subtitle', [
                                'class' => 'form-control',
                                'placeholder' => 'Enter subtitle',
                                'label' => false,
                                'id' => 'slideSubtitle',
                                'value' => $slide->subtitle ?: ($slideConfig['subtitle'] ?? '')
                            ]) ?>
                        </div>
                        <?php endif; ?>

                        <!-- Column 1 -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-th-large me-2 text-danger"></i>
                                <?= ($slideConfig['columns'] ?? 1) == 2 ? 'Column 1' : 'Content' ?>
                            </label>

                            <?php $col1Type = $slideConfig['col1']['type'] ?? 'text'; ?>

                            <?php if ($col1Type === 'image' || $col1Type === 'composite_image'): ?>
                                <?= $this->Form->hidden('col1_type', ['value' => 'image']) ?>
                                
                                <?php if (!empty($slideConfig['col1']['header'])): ?>
                                <div class="mb-3">
                                    <label class="form-label small">Column 1 Header</label>
                                    <?= $this->Form->textarea('col1_header', [
                                        'class' => 'form-control form-control-sm',
                                        'rows' => 2,
                                        'id' => 'col1Header',
                                        'value' => strip_tags($slideConfig['col1']['header'] ?? '')
                                    ]) ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="upload-dropzone" id="col1Dropzone">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-danger mb-2"></i>
                                    <p class="mb-0">Drop image here or click to upload</p>
                                    <small class="text-muted"><?= h($slideConfig['col1']['description'] ?? 'Upload slide image') ?></small>
                                </div>
                                <?= $this->Form->file('col1_image', [
                                    'id' => 'col1ImageInput',
                                    'accept' => 'image/*',
                                    'class' => 'd-none'
                                ]) ?>
                                <div id="col1ImagePreview" class="mt-3 text-center d-none">
                                    <img id="col1PreviewImg" src="" class="image-preview-thumb">
                                    <br>
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="col1RemoveImg">
                                        <i class="fas fa-times me-1"></i>Remove
                                    </button>
                                </div>
                            <?php else: ?>
                                <?= $this->Form->hidden('col1_type', ['value' => 'text']) ?>
                                <?= $this->Form->textarea('col1_content', [
                                    'class' => 'form-control',
                                    'rows' => 6,
                                    'placeholder' => $slideConfig['col1']['placeholder'] ?? 'Enter text content',
                                    'id' => 'col1Content',
                                    'value' => $slide->col1_content ?? ($slideConfig['col1']['default_content'] ?? '')
                                ]) ?>
                            <?php endif; ?>
                        </div>

                        <?php if (($slideConfig['columns'] ?? 1) == 2): ?>
                        <!-- Column 2 -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-th-large me-2 text-danger"></i>Column 2
                            </label>

                            <?php $col2Type = $slideConfig['col2']['type'] ?? 'text'; ?>

                            <?php if ($col2Type === 'image' || $col2Type === 'composite_image'): ?>
                                <?= $this->Form->hidden('col2_type', ['value' => 'image']) ?>
                                
                                <?php if (!empty($slideConfig['col2']['header'])): ?>
                                <div class="mb-3">
                                    <label class="form-label small">Column 2 Header</label>
                                    <?= $this->Form->textarea('col2_header', [
                                        'class' => 'form-control form-control-sm',
                                        'rows' => 2,
                                        'id' => 'col2Header',
                                        'value' => strip_tags($slideConfig['col2']['header'] ?? '')
                                    ]) ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="upload-dropzone" id="col2Dropzone">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-danger mb-2"></i>
                                    <p class="mb-0">Drop image here or click to upload</p>
                                </div>
                                <?= $this->Form->file('col2_image', [
                                    'id' => 'col2ImageInput',
                                    'accept' => 'image/*',
                                    'class' => 'd-none'
                                ]) ?>
                                <div id="col2ImagePreview" class="mt-3 text-center d-none">
                                    <img id="col2PreviewImg" src="" class="image-preview-thumb">
                                    <br>
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="col2RemoveImg">
                                        <i class="fas fa-times me-1"></i>Remove
                                    </button>
                                </div>
                            <?php else: ?>
                                <?= $this->Form->hidden('col2_type', ['value' => 'text']) ?>
                                <?= $this->Form->textarea('col2_content', [
                                    'class' => 'form-control',
                                    'rows' => 6,
                                    'placeholder' => 'Enter text content for column 2',
                                    'id' => 'col2Content'
                                ]) ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($slideConfig['footer_text']) || !empty($slideConfig['footer_editable'])): ?>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Footer Text</label>
                            <?= $this->Form->control('footer_text', [
                                'class' => 'form-control',
                                'placeholder' => 'Enter footer text',
                                'label' => false,
                                'value' => $slideConfig['footer_text'] ?? ''
                            ]) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <?= $this->Form->button('<i class="fas fa-save me-2"></i>Save Slide', [
                        'class' => 'btn btn-danger btn-lg fw-bold',
                        'escapeTitle' => false
                    ]) ?>
                </div>
            </div>

            <!-- Right Panel: Live Preview -->
            <div class="col-lg-7 mb-4">
                <div class="card border-0 shadow">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-eye me-2 text-danger"></i>Live Preview
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="slide-preview-container">
                            <div class="slide-preview" id="slidePreview">
                                <div class="slide-preview-header">
                                    <h3 id="previewTitle"><?= h($slide->title ?? $slideConfig['title'] ?? 'Slide Title') ?></h3>
                                    <p id="previewSubtitle" style="<?= empty($slideConfig['subtitle']) ? 'display:none;' : '' ?>">• <?= h($slideConfig['subtitle'] ?? '') ?></p>
                                    <?php if (($slideConfig['columns'] ?? 1) == 2 && !empty($slideConfig['col1']['header'])): ?>
                                    <?php 
                                    $pptLayouts = unserialize(PPT_LAYOUTS);
                                    $layout = $slideConfig['layout'] ?? 'two_column_images';
                                    $layoutConfig = $pptLayouts[$layout] ?? [];
                                    $col1WidthPercent = $layoutConfig['col1_width_percent'] ?? 50;
                                    $col2WidthPercent = $layoutConfig['col2_width_percent'] ?? 50;
                                    ?>
                                    <div class="preview-headers" style="display: flex; gap: 10px; margin-top: 5px;">
                                        <small id="previewCol1Header" style="flex: <?= $col1WidthPercent ?>; font-size: 9px;"><?= h(strip_tags($slideConfig['col1']['header'] ?? '')) ?></small>
                                        <small id="previewCol2Header" style="flex: <?= $col2WidthPercent ?>; font-size: 9px;"><?= h(strip_tags($slideConfig['col2']['header'] ?? '')) ?></small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php 
                                if (!isset($col1WidthPercent)) {
                                    $pptLayouts = unserialize(PPT_LAYOUTS);
                                    $layout = $slideConfig['layout'] ?? 'two_column_images';
                                    $layoutConfig = $pptLayouts[$layout] ?? [];
                                    $col1WidthPercent = $layoutConfig['col1_width_percent'] ?? 50;
                                    $col2WidthPercent = $layoutConfig['col2_width_percent'] ?? 50;
                                }
                                ?>
                                <div class="slide-preview-body <?= ($slideConfig['columns'] ?? 1) == 2 ? 'two-col' : '' ?>" id="previewBody">
                                    <div class="preview-column" id="previewCol1" style="flex: <?= $col1WidthPercent ?>;">
                                        <i class="fas fa-<?= ($slideConfig['col1']['type'] ?? 'text') === 'image' ? 'image' : 'font' ?> fa-2x text-muted mb-2"></i>
                                        <span class="text-muted"><?= ($slideConfig['columns'] ?? 1) == 2 ? 'Column 1' : 'Content' ?></span>
                                    </div>
                                    <?php if (($slideConfig['columns'] ?? 1) == 2): ?>
                                    <div class="preview-column" id="previewCol2" style="flex: <?= $col2WidthPercent ?>;">
                                        <i class="fas fa-<?= ($slideConfig['col2']['type'] ?? 'text') === 'image' ? 'image' : 'font' ?> fa-2x text-muted mb-2"></i>
                                        <span class="text-muted">Column 2</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?= $this->Form->end() ?>
    <?php endif; ?>
</div>

<?php $this->start('script'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Title live preview
    const titleInput = document.getElementById('slideTitle');
    const previewTitle = document.getElementById('previewTitle');
    
    if (titleInput && previewTitle) {
        titleInput.addEventListener('input', function() {
            previewTitle.textContent = this.value || 'Slide Title';
        });
    }
    
    // Subtitle live preview
    const subtitleInput = document.getElementById('slideSubtitle');
    const previewSubtitle = document.getElementById('previewSubtitle');
    
    if (subtitleInput && previewSubtitle) {
        subtitleInput.addEventListener('input', function() {
            if (this.value && this.value.trim() !== '') {
                previewSubtitle.textContent = '• ' + this.value;
                previewSubtitle.style.display = 'block';
            } else {
                previewSubtitle.style.display = 'none';
            }
        });
    }
    
    // Column 1 Header live preview
    const col1Header = document.getElementById('col1Header');
    const previewCol1Header = document.getElementById('previewCol1Header');
    
    if (col1Header && previewCol1Header) {
        col1Header.addEventListener('input', function() {
            previewCol1Header.textContent = this.value || 'Column 1';
        });
    }
    
    // Column 2 Header live preview
    const col2Header = document.getElementById('col2Header');
    const previewCol2Header = document.getElementById('previewCol2Header');
    
    if (col2Header && previewCol2Header) {
        col2Header.addEventListener('input', function() {
            previewCol2Header.textContent = this.value || 'Column 2';
        });
    }
    
    // Column 1 Content live preview
    const col1Content = document.getElementById('col1Content');
    const previewCol1 = document.getElementById('previewCol1');
    
    if (col1Content && previewCol1) {
        col1Content.addEventListener('input', function() {
            if (this.value) {
                previewCol1.innerHTML = '<div style="text-align: left; white-space: pre-wrap; font-size: 11px; overflow: hidden;">' + escapeHtml(this.value.substring(0, 300)) + '</div>';
                previewCol1.classList.add('has-content');
            } else {
                previewCol1.innerHTML = '<i class="fas fa-font fa-2x text-muted mb-2"></i><span class="text-muted">Column 1</span>';
                previewCol1.classList.remove('has-content');
            }
        });
    }
    
    setupImageUpload('col1');
    setupImageUpload('col2');
    
    function setupImageUpload(col) {
        const dropzone = document.getElementById(col + 'Dropzone');
        const input = document.getElementById(col + 'ImageInput');
        const preview = document.getElementById(col + 'ImagePreview');
        const previewImg = document.getElementById(col + 'PreviewImg');
        const removeBtn = document.getElementById(col + 'RemoveImg');
        const previewCol = document.getElementById('preview' + col.charAt(0).toUpperCase() + col.slice(1));
        
        if (!dropzone || !input) return;
        
        dropzone.addEventListener('click', () => input.click());
        
        dropzone.addEventListener('dragover', e => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });
        
        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('dragover');
        });
        
        dropzone.addEventListener('drop', e => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                handleImageUpload(input.files[0]);
            }
        });
        
        input.addEventListener('change', function() {
            if (this.files.length) {
                handleImageUpload(this.files[0]);
            }
        });
        
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                input.value = '';
                preview.classList.add('d-none');
                dropzone.style.display = 'block';
                if (previewCol) {
                    previewCol.innerHTML = '<i class="fas fa-image fa-2x text-muted mb-2"></i><span class="text-muted">Upload image</span>';
                    previewCol.classList.remove('has-content');
                }
            });
        }
        
        function handleImageUpload(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('d-none');
                dropzone.style.display = 'none';
                
                if (previewCol) {
                    previewCol.innerHTML = '<img src="' + e.target.result + '" style="max-width: 100%; max-height: 100px; object-fit: contain;">';
                    previewCol.classList.add('has-content');
                }
            };
            reader.readAsDataURL(file);
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
<?php $this->end(); ?>
