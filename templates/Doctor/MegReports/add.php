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
.slide-preview-body.two-col-custom {
    display: grid;
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

                        <?php if (($slideConfig['layout'] ?? '') === 'text_header_two_images'): ?>
                        <!-- Text Header Two Images Layout (e.g., functional_mapping_language) -->
                        
                        <!-- Header Text (Bullet Points) -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-list me-2 text-danger"></i>Header Text (Bullet Points)
                            </label>
                            <p class="text-muted small mb-2">
                                <i class="fas fa-info-circle me-1"></i>Enter each bullet point on a new line. Bullets (•) are added automatically.
                            </p>
                            <?php 
                            $defaultHeaderText = '';
                            if (isset($slideConfig['header_text']['content'])) {
                                $hc = $slideConfig['header_text']['content'];
                                $defaultHeaderText = is_array($hc) ? implode("\n", $hc) : $hc;
                            }
                            ?>
                            <?= $this->Form->textarea('col1_content', [
                                'class' => 'form-control',
                                'rows' => 5,
                                'placeholder' => "First bullet point text here\nSecond bullet point text here",
                                'id' => 'headerTextContent',
                                'value' => $slide->col1_content ?? $defaultHeaderText
                            ]) ?>
                            <?= $this->Form->hidden('col1_type', ['value' => 'text']) ?>
                            
                            <!-- Bullet Preview -->
                            <div class="mt-3 p-3 bg-light border rounded">
                                <small class="text-muted d-block mb-2"><i class="fas fa-eye me-1"></i>Preview:</small>
                                <div id="addBulletPreview" style="font-size: 13px; line-height: 1.6;">
                                    <?php 
                                    if (!empty($defaultHeaderText)) {
                                        $lines = preg_split('/\r\n|\r|\n/', $defaultHeaderText);
                                        foreach ($lines as $line) {
                                            $line = trim($line);
                                            if (!empty($line)) {
                                                echo '<div>• ' . h(strip_tags($line)) . '</div>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Two Image Columns -->
                        <div class="row">
                            <!-- Left Image -->
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-image me-2 text-danger"></i>Left Image
                                    </label>
                                    <div class="upload-dropzone" id="col1Dropzone">
                                        <i class="fas fa-cloud-upload-alt fa-2x text-danger mb-2"></i>
                                        <p class="mb-0">Drop image here or click to upload</p>
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
                                </div>
                            </div>
                            
                            <!-- Right Image -->
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="fas fa-image me-2 text-danger"></i>Right Image
                                    </label>
                                    <?= $this->Form->hidden('col2_type', ['value' => 'image']) ?>
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
                                </div>
                            </div>
                        </div>
                        
                        <?php else: ?>

                        <?php if (($slideConfig['layout'] ?? '') === 'multi_image_with_titles'): ?>
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
                                    $headerField = $colName . '_header';
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
                                                       value="<?= h($defaultTitle) ?>"
                                                       placeholder="Image title">
                                            </div>
                                            
                                            <div class="upload-dropzone" id="<?= $colName ?>Dropzone" style="padding: 10px; min-height: auto;">
                                                <i class="fas fa-cloud-upload-alt text-danger mb-1"></i>
                                                <p class="mb-0" style="font-size: 12px;">Upload</p>
                                            </div>
                                            <?= $this->Form->file($colName . '_image', [
                                                'id' => $colName . 'ImageInput',
                                                'accept' => 'image/*',
                                                'class' => 'd-none'
                                            ]) ?>
                                            <div id="<?= $colName ?>ImagePreview" class="mt-2 text-center d-none">
                                                <img id="<?= $colName ?>PreviewImg" src="" class="img-fluid rounded" style="max-height: 100px;">
                                                <br>
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-1" id="<?= $colName ?>RemoveImg">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <?php elseif (($slideConfig['columns'] ?? 1) == 2): ?>
                        <!-- Two Column Layout -->
                        <div class="row">
                            <!-- Column 1 -->
                            <div class="col-md-6">
                                <div class="column-section">
                                    <h5><i class="fas fa-columns me-2"></i>Column 1</h5>
                                    
                                    <?php $col1Type = $slideConfig['col1']['type'] ?? 'text'; ?>

                                    <?php if (!empty($slideConfig['col1']['header'])): ?>
                                    <div class="mb-3">
                                        <label class="form-label small">Column Header</label>
                                        <?= $this->Form->textarea('col1_header', [
                                            'class' => 'form-control form-control-sm',
                                            'rows' => 2,
                                            'id' => 'col1Header',
                                            'value' => strip_tags($slideConfig['col1']['header'] ?? '')
                                        ]) ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($col1Type === 'image' || $col1Type === 'composite_image'): ?>
                                        <?= $this->Form->hidden('col1_type', ['value' => 'image']) ?>
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
                                    <?php elseif ($col1Type === 'text'): ?>
                                        <?= $this->Form->hidden('col1_type', ['value' => 'text']) ?>

                                        <?php if (($slideConfig['col1']['format'] ?? '') === 'structured_bullets' && !empty($slideConfig['default_sections'])): ?>
                                            <!-- Structured Bullets Editor for Summary Slide -->
                                            <div class="structured-bullets-editor" id="structuredBulletsEditor">
                                                <?php foreach ($slideConfig['default_sections'] as $sectionIndex => $section): ?>
                                                <div class="summary-section mb-4" data-section="<?= $sectionIndex ?>">
                                                    <div class="section-heading mb-3">
                                                        <label class="form-label small text-muted">Section Heading</label>
                                                        <input type="text" 
                                                               name="structured_sections[<?= $sectionIndex ?>][heading]" 
                                                               class="form-control form-control-sm fw-bold"
                                                               value="<?= h($section['heading'] ?? '') ?>"
                                                               placeholder="Section heading (e.g., (I) Epileptiform discharges)">
                                                    </div>
                                                    
                                                    <div class="section-items ms-3">
                                                        <?php foreach ($section['items'] ?? [] as $itemIndex => $item): ?>
                                                        <div class="section-item mb-3 p-2 bg-light rounded" data-item="<?= $itemIndex ?>">
                                                            <label class="form-label small text-muted">Item Title</label>
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
                                            <?= $this->Form->textarea('col1_content', [
                                                'class' => 'form-control',
                                                'rows' => 6,
                                                'placeholder' => $slideConfig['col1']['placeholder'] ?? 'Enter text content',
                                                'id' => 'col1Content',
                                                'value' => $slide->col1_content ?? ($slideConfig['col1']['default_content'] ?? '')
                                            ]) ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Column 2 -->
                            <div class="col-md-6">
                                <div class="column-section">
                                    <?php 
                                    $isStackedSlide = !empty($slideConfig['stacked_images']);
                                    $col2Type = $slideConfig['col2']['type'] ?? 'text';
                                    ?>
                                    <h5><i class="fas fa-columns me-2"></i><?= $isStackedSlide ? 'Images (Stacked)' : 'Column 2' ?></h5>

                                    <?php if (!empty($slideConfig['col2']['header'])): ?>
                                    <div class="mb-3">
                                        <label class="form-label small">Column Header</label>
                                        <?= $this->Form->textarea('col2_header', [
                                            'class' => 'form-control form-control-sm',
                                            'rows' => 2,
                                            'id' => 'col2Header',
                                            'value' => strip_tags($slideConfig['col2']['header'] ?? '')
                                        ]) ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($isStackedSlide): ?>
                                        <!-- Stacked Images: Top (col2) and Bottom (col3) -->
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="fas fa-arrow-up me-1"></i>Top Image
                                                <small class="text-muted d-block"><?= h($slideConfig['col2']['description'] ?? 'Top image') ?></small>
                                            </label>
                                            <?= $this->Form->hidden('col2_type', ['value' => 'image']) ?>
                                            <div class="upload-dropzone" id="col2Dropzone">
                                                <i class="fas fa-cloud-upload-alt fa-2x text-danger mb-2"></i>
                                                <p class="mb-0">Drop top image here or click to upload</p>
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
                                        </div>
                                        
                                        <hr class="my-3">
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold">
                                                <i class="fas fa-arrow-down me-1"></i>Bottom Image
                                                <small class="text-muted d-block"><?= h($slideConfig['col3']['description'] ?? 'Bottom image') ?></small>
                                            </label>
                                            <?= $this->Form->hidden('col3_type', ['value' => 'image']) ?>
                                            <div class="upload-dropzone" id="col3Dropzone">
                                                <i class="fas fa-cloud-upload-alt fa-2x text-danger mb-2"></i>
                                                <p class="mb-0">Drop bottom image here or click to upload</p>
                                            </div>
                                            <?= $this->Form->file('col3_image', [
                                                'id' => 'col3ImageInput',
                                                'accept' => 'image/*',
                                                'class' => 'd-none'
                                            ]) ?>
                                            <div id="col3ImagePreview" class="mt-3 text-center d-none">
                                                <img id="col3PreviewImg" src="" class="image-preview-thumb">
                                                <br>
                                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="col3RemoveImg">
                                                    <i class="fas fa-times me-1"></i>Remove
                                                </button>
                                            </div>
                                        </div>
                                    <?php elseif ($col2Type === 'image' || $col2Type === 'composite_image'): ?>
                                        <?= $this->Form->hidden('col2_type', ['value' => 'image']) ?>
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
                                    <?php elseif ($col2Type === 'text'): ?>
                                        <?= $this->Form->hidden('col2_type', ['value' => 'text']) ?>
                                        <?= $this->Form->textarea('col2_content', [
                                            'class' => 'form-control',
                                            'rows' => 6,
                                            'placeholder' => 'Enter text content for column 2',
                                            'id' => 'col2Content',
                                            'value' => $slide->col2_content ?? ($slideConfig['col2']['default_content'] ?? '')
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?php else: ?>
                        <!-- Single Column Layout -->
                        <div class="column-section">
                            <h5><i class="fas fa-image me-2"></i>Slide Content</h5>
                            
                            <?php $col1Type = $slideConfig['col1']['type'] ?? 'text'; ?>

                            <?php if ($col1Type === 'image' || $col1Type === 'composite_image'): ?>
                                <?= $this->Form->hidden('col1_type', ['value' => 'image']) ?>
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
                            <?php endif; ?>
                            
                            <?php if ($col1Type === 'text' || ($slideConfig['layout'] ?? '') === 'text_only' || ($slideConfig['layout'] ?? '') === 'text_bullets'): ?>
                                <?= $this->Form->hidden('col1_type', ['value' => 'text']) ?>

                                <?php if (($slideConfig['col1']['format'] ?? '') === 'structured_bullets' && !empty($slideConfig['default_sections'])): ?>
                                    <!-- Structured Bullets Editor -->
                                    <div class="structured-bullets-editor" id="structuredBulletsEditor">
                                        <?php foreach ($slideConfig['default_sections'] as $sectionIndex => $section): ?>
                                        <div class="summary-section mb-4" data-section="<?= $sectionIndex ?>">
                                            <div class="section-heading mb-3">
                                                <label class="form-label small text-muted">Section Heading</label>
                                                <input type="text" 
                                                       name="structured_sections[<?= $sectionIndex ?>][heading]" 
                                                       class="form-control form-control-sm fw-bold"
                                                       value="<?= h($section['heading'] ?? '') ?>"
                                                       placeholder="Section heading">
                                            </div>
                                            
                                            <div class="section-items ms-3">
                                                <?php foreach ($section['items'] ?? [] as $itemIndex => $item): ?>
                                                <div class="section-item mb-3 p-2 bg-light rounded" data-item="<?= $itemIndex ?>">
                                                    <label class="form-label small text-muted">Item Title</label>
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
                                    
                                    <?= $this->Form->hidden('col1_content', ['id' => 'col1ContentJson']) ?>
                                    <?= $this->Form->hidden('content_format', ['value' => 'structured_bullets']) ?>
                                    
                                <?php else: ?>
                                    <?= $this->Form->textarea('col1_content', [
                                        'class' => 'form-control',
                                        'rows' => 6,
                                        'placeholder' => $slideConfig['col1']['placeholder'] ?? 'Enter text content',
                                        'id' => 'col1Content',
                                        'value' => $slide->col1_content ?? ($slideConfig['col1']['default_content'] ?? '')
                                    ]) ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?><!-- end multi_image / two-col / single-col -->
                        <?php endif; ?><!-- end text_header_two_images else -->

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
                                    
                                    <?php if (($slideConfig['layout'] ?? '') === 'text_header_two_images'): ?>
                                    <!-- Header text bullets preview -->
                                    <div id="previewHeaderBullets" style="font-size: 9px; line-height: 1.5; text-align: left; margin-top: 5px; padding: 5px; background: #f8f9fa; border-radius: 3px;">
                                        <?php 
                                        if (isset($slideConfig['header_text']['content'])) {
                                            $hc = $slideConfig['header_text']['content'];
                                            $lines = is_array($hc) ? $hc : preg_split('/\r\n|\r|\n/', $hc);
                                            foreach ($lines as $line) {
                                                $line = trim($line);
                                                if (!empty($line)) {
                                                    echo '<div>• ' . h(strip_tags($line)) . '</div>';
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                    <?php elseif (($slideConfig['columns'] ?? 1) == 2 && !empty($slideConfig['col1']['header'])): ?>
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
                                <div class="slide-preview-body <?= ($slideConfig['columns'] ?? 1) == 2 ? 'two-col-custom' : '' ?>" id="previewBody" style="<?= ($slideConfig['columns'] ?? 1) == 2 ? 'grid-template-columns: ' . ($col1WidthPercent ?? 50) . 'fr ' . ($col2WidthPercent ?? 50) . 'fr;' : '' ?>">
                                    <?php if (($slideConfig['layout'] ?? '') === 'text_header_two_images'): ?>
                                    <!-- Two image placeholders side by side -->
                                    <div class="preview-column" id="previewCol1" style="flex: 50;">
                                        <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                        <span class="text-muted">Left Image</span>
                                    </div>
                                    <div class="preview-column" id="previewCol2" style="flex: 50;">
                                        <i class="fas fa-image fa-2x text-muted mb-2"></i>
                                        <span class="text-muted">Right Image</span>
                                    </div>
                                    <?php else: ?>
                                    <div class="preview-column" id="previewCol1" style="flex: <?= $col1WidthPercent ?>;">
                                        <i class="fas fa-<?= ($slideConfig['col1']['type'] ?? 'text') === 'image' ? 'image' : 'font' ?> fa-2x text-muted mb-2"></i>
                                        <span class="text-muted"><?= ($slideConfig['columns'] ?? 1) == 2 ? 'Column 1' : 'Content' ?></span>
                                    </div>
                                    <?php if (($slideConfig['columns'] ?? 1) == 2): ?>
                                        <?php if (!empty($slideConfig['stacked_images'])): ?>
                                        <div class="preview-column" id="previewCol2" style="flex: <?= $col2WidthPercent ?>; display: flex; flex-direction: column; gap: 4px;">
                                            <div style="flex: 1; display: flex; align-items: center; justify-content: center;" data-stacked-top id="previewCol2Top">
                                                <div class="text-muted" style="font-size: 9px;"><i class="fas fa-image"></i> Top</div>
                                            </div>
                                            <div style="flex: 1; display: flex; align-items: center; justify-content: center;" data-stacked-bottom id="previewCol3Bottom">
                                                <div class="text-muted" style="font-size: 9px;"><i class="fas fa-image"></i> Bottom</div>
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <div class="preview-column" id="previewCol2" style="flex: <?= $col2WidthPercent ?>;">
                                            <i class="fas fa-<?= ($slideConfig['col2']['type'] ?? 'text') === 'image' ? 'image' : 'font' ?> fa-2x text-muted mb-2"></i>
                                            <span class="text-muted">Column 2</span>
                                        </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
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
    
    // Header Text (text_header_two_images layout) live preview
    const headerTextContent = document.getElementById('headerTextContent');
    const addBulletPreview = document.getElementById('addBulletPreview');
    const previewHeaderBullets = document.getElementById('previewHeaderBullets');
    
    if (headerTextContent) {
        headerTextContent.addEventListener('input', function() {
            const lines = this.value.split('\n').filter(l => l.trim() !== '');
            // Update inline bullet preview
            if (addBulletPreview) {
                if (lines.length > 0) {
                    addBulletPreview.innerHTML = lines.map(l => '<div>• ' + escapeHtml(l.trim()) + '</div>').join('');
                } else {
                    addBulletPreview.innerHTML = '<span class="text-muted">Bullet points will appear here...</span>';
                }
            }
            // Update slide preview
            if (previewHeaderBullets) {
                if (lines.length > 0) {
                    previewHeaderBullets.innerHTML = lines.map(l => '<div>• ' + escapeHtml(l.trim()) + '</div>').join('');
                } else {
                    previewHeaderBullets.innerHTML = '<span class="text-muted">Header text bullets</span>';
                }
            }
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
    setupImageUpload('col3');
    setupImageUpload('col4');
    setupImageUpload('col5');
    
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
                // Handle stacked preview reset
                if (col === 'col2') {
                    const stackedTop = document.getElementById('previewCol2Top');
                    if (stackedTop) {
                        stackedTop.innerHTML = '<div class="text-muted" style="font-size: 9px;"><i class="fas fa-image"></i> Top</div>';
                        return;
                    }
                }
                if (col === 'col3') {
                    const stackedBottom = document.getElementById('previewCol3Bottom');
                    if (stackedBottom) {
                        stackedBottom.innerHTML = '<div class="text-muted" style="font-size: 9px;"><i class="fas fa-image"></i> Bottom</div>';
                        return;
                    }
                }
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
                
                // Handle stacked preview updates
                if (col === 'col2') {
                    const stackedTop = document.getElementById('previewCol2Top');
                    if (stackedTop) {
                        stackedTop.innerHTML = '<img src="' + e.target.result + '" style="max-width: 100%; max-height: 120px; object-fit: contain;">';
                        return;
                    }
                }
                if (col === 'col3') {
                    const stackedBottom = document.getElementById('previewCol3Bottom');
                    if (stackedBottom) {
                        stackedBottom.innerHTML = '<img src="' + e.target.result + '" style="max-width: 100%; max-height: 120px; object-fit: contain;">';
                        return;
                    }
                }
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
        document.getElementById('slideForm')?.addEventListener('submit', function(e) {
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
            const previewCol1 = document.getElementById('previewCol1');
            if (!previewCol1 || !structuredEditor) return;
            
            let html = '<div style="text-align: left; font-size: 9px; overflow: auto; max-height: 100%; padding: 5px;">';
            let isFirst = true;
            
            structuredEditor.querySelectorAll('.summary-section').forEach(section => {
                const headingInput = section.querySelector('input[name*="[heading]"]');
                const heading = headingInput ? headingInput.value : '';
                
                // Section heading - bold, 1.5 line height (matches PPT)
                if (heading) {
                    const marginTop = isFirst ? '0' : '8px';
                    html += '<div style="font-weight: bold; line-height: 1.5; margin-top: ' + marginTop + '; margin-bottom: 3px;">' + escapeHtml(heading) + '</div>';
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
                        html += '<div style="line-height: 1.4; margin-left: 12px; margin-bottom: 2px;">• ' + escapeHtml(title) + '</div>';
                    }
                    
                    // Subitems - 1.3 line height, more indented, smaller (matches PPT)
                    subitems.forEach(subitem => {
                        html += '<div style="line-height: 1.3; margin-left: 24px; font-size: 0.95em; color: #333; margin-bottom: 1px;">○ ' + escapeHtml(subitem.substring(0, 50)) + (subitem.length > 50 ? '...' : '') + '</div>';
                    });
                });
            });
            
            html += '</div>';
            previewCol1.innerHTML = html;
            previewCol1.classList.add('has-content');
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
<?php $this->end(); ?>
