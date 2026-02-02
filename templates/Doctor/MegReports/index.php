<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ReportSlide> $slides
 * @var \App\Model\Entity\Report $report
 * @var int $reportId
 * @var array $slideTypes
 * @var array $slideCategories
 */
$this->assign('title', 'MEG Report Slides');
?>

<style>
.presentation-container {
    background: #f5f5f5;
    min-height: calc(100vh - 100px);
    display: flex;
    flex-direction: column;
}
.presentation-header {
    background: #dc3545;
    padding: 15px 30px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    z-index: 100;
}
.presentation-header h2 {
    font-size: 18px;
    margin: 0;
}
.slide-viewer {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    min-height: 600px;
    position: relative;
}
.slide-container {
    background: white;
    width: 960px;
    height: 540px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease;
}
.slide-container.active {
    opacity: 1;
    visibility: visible;
    z-index: 1;
}
.slide-content {
    width: 100%;
    height: 100%;
    padding: 40px;
    box-sizing: border-box;
    overflow: auto;
}
.slide-content.text-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}
.slide-content h2 {
    font-size: 24px;
    margin-bottom: 20px;
    font-weight: bold;
    color: #333;
}
.slide-content h3 {
    font-size: 18px;
    margin-bottom: 15px;
    font-weight: 600;
    color: #555;
}
.slide-content p {
    font-size: 16px;
    line-height: 1.6;
    margin-bottom: 20px;
}
.slide-image {
    max-width: 100%;
    max-height: 350px;
    display: block;
    margin: 10px auto;
}
/* Two-column layout styles */
.slide-two-columns {
    display: flex;
    gap: 30px;
    height: calc(100% - 60px);
}
.slide-column {
    flex: 1;
    display: flex;
    flex-direction: column;
}
.slide-column-header {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 2px solid #dc3545;
}
.slide-column img {
    max-width: 100%;
    max-height: 300px;
    object-fit: contain;
    margin: auto 0;
}
.slide-column-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
/* Legend styles */
.slide-legend {
    margin-top: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
}
.legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
    font-size: 13px;
}
.legend-color {
    width: 20px;
    height: 12px;
    margin-right: 8px;
    border-radius: 2px;
    border: 1px solid rgba(0,0,0,0.1);
}
/* Slide type badge */
.slide-type-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(220,53,69,0.9);
    color: white;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
    text-transform: capitalize;
}
.slide-actions-overlay {
    position: absolute;
    top: 10px;
    right: 10px;
    display: none;
    gap: 5px;
}
.slide-container:hover .slide-actions-overlay {
    display: flex;
}
.slide-number-badge {
    position: absolute;
    bottom: 20px;
    right: 30px;
    background: rgba(0,0,0,0.6);
    color: white;
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: bold;
}
.navigation-controls {
    background: white;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    border-top: 1px solid #dee2e6;
}
.nav-btn {
    background: #dc3545;
    border: none;
    color: white;
    padding: 12px 25px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s;
}
.nav-btn:hover:not(:disabled) {
    background: #c82333;
    transform: scale(1.05);
}
.nav-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}
.slide-counter {
    color: #333;
    font-size: 16px;
    min-width: 100px;
    text-align: center;
    font-weight: 600;
}
.thumbnail-strip {
    background: white;
    padding: 15px;
    display: flex;
    gap: 10px;
    overflow-x: auto;
    white-space: nowrap;
    border-top: 1px solid #dee2e6;
}
.thumbnail {
    width: 160px;
    height: 90px;
    background: white;
    border: 3px solid transparent;
    cursor: pointer;
    flex-shrink: 0;
    position: relative;
    transition: all 0.2s;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.thumbnail:hover {
    border-color: #dc3545;
}
.thumbnail.active {
    border-color: #dc3545;
    box-shadow: 0 0 10px rgba(220,53,69,0.5);
}
.thumbnail-content {
    flex: 1;
    padding: 5px;
    overflow: hidden;
    font-size: 8px;
    line-height: 1.2;
}
.thumbnail-content img {
    max-width: 100%;
    max-height: 60px;
    display: block;
    margin: 2px auto;
}
.thumbnail-two-cols {
    display: flex;
    gap: 3px;
}
.thumbnail-two-cols img {
    max-width: 48%;
    max-height: 50px;
}
.thumbnail-number {
    position: absolute;
    bottom: 3px;
    right: 3px;
    background: rgba(220,53,69,0.9);
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: bold;
}
.thumbnail-type {
    position: absolute;
    top: 2px;
    left: 2px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 1px 4px;
    border-radius: 2px;
    font-size: 7px;
    max-width: 80px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin: 40px;
}
</style>

<?php if ($slides->count() > 0): ?>
    <div class="presentation-container">
        <!-- Header -->
        <div class="presentation-header">
            <div>
                <h2>
                    <i class="fas fa-file-powerpoint me-2"></i>MEG Report - 
                    <?php 
                    if (isset($report->case->patient_user)) {
                        $patientUser = $report->case->patient_user;
                        $patientName = $this->PatientMask->displayName($patientUser);
                        echo h($patientName);
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </h2>
            </div>
            <div class="d-flex gap-2">
                <?php echo $this->Html->link(
                    '<i class="fas fa-plus-circle me-1"></i>Add Slide',
                    ['action' => 'add', '?' => ['report_id' => $reportId]],
                    ['class' => 'btn btn-sm btn-outline-light', 'escape' => false]
                ) ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-download me-1"></i>Download PPT',
                    ['action' => 'downloadPpt', $reportId],
                    ['class' => 'btn btn-sm btn-outline-light', 'escape' => false]
                ) ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-times me-1"></i>Close',
                    ['controller' => 'Cases', 'action' => 'view', $report->case_id],
                    ['class' => 'btn btn-sm btn-light', 'escape' => false]
                ) ?>
            </div>
        </div>

        <!-- Slide Viewer -->
        <div class="slide-viewer">
            <?php 
            $slideArray = $slides->toArray();
            ?>
            <?php foreach ($slideArray as $index => $slide): ?>
                <?php 
                // Get slide config for this slide type
                $slideConfig = $slide->getSlideConfig();
                $slideType = $slide->slide_type ?? 'custom';
                $layoutColumns = $slide->layout_columns ?? 1;
                ?>
                <div class="slide-container <?php echo $index === 0 ? 'active' : '' ?>" data-slide-index="<?php echo $index ?>" data-slide-id="<?php echo $slide->id ?>">
                    <!-- Slide type badge -->
                    <?php if ($slideConfig): ?>
                        <div class="slide-type-badge">
                            <?php echo h(str_replace('_', ' ', $slideType)) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="slide-actions-overlay">
                        <?php echo $this->Html->link(
                            '<i class="fas fa-edit"></i>',
                            ['action' => 'edit', $slide->id],
                            ['class' => 'btn btn-sm btn-warning', 'escape' => false, 'title' => 'Edit']
                        ) ?>
                        <?php if ($slide->slide_order !== 1): ?>
                            <?php echo $this->Form->postLink(
                                '<i class="fas fa-trash"></i>',
                                ['action' => 'delete', $slide->id],
                                ['class' => 'btn btn-sm btn-danger', 'escape' => false, 'title' => 'Delete', 'confirm' => 'Delete this slide?']
                            ) ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="slide-content <?php echo $slide->slide_order === 1 ? 'text-center' : '' ?>">
                        <?php if ($slide->slide_order === 1): ?>
                            <!-- Cover slide with centered content -->
                            <?php echo $slide->html_content ?>
                        <?php elseif ($layoutColumns === 2): ?>
                            <!-- Two-column layout -->
                            <?php if (!empty($slide->title)): ?>
                                <h2><?php echo h($slide->title) ?></h2>
                            <?php elseif (!empty($slide->description)): ?>
                                <h2><?php echo h($slide->description) ?></h2>
                            <?php endif; ?>
                            <?php if (!empty($slide->subtitle)): ?>
                                <h3><?php echo h($slide->subtitle) ?></h3>
                            <?php endif; ?>
                            <?php 
                            // Get column width percentages from layout config
                            $pptLayouts = unserialize(PPT_LAYOUTS);
                            $layout = $slideConfig['layout'] ?? 'two_column_images';
                            $layoutConfig = $pptLayouts[$layout] ?? [];
                            $col1WidthPercent = $layoutConfig['col1_width_percent'] ?? 50;
                            $col2WidthPercent = $layoutConfig['col2_width_percent'] ?? 50;
                            ?>
                            <div class="slide-two-columns">
                                <!-- Column 1 -->
                                <div class="slide-column" style="flex: <?php echo $col1WidthPercent ?>;">
                                    <?php if (!empty($slide->col1_header)): ?>
                                        <div class="slide-column-header"><?php echo h($slide->col1_header) ?></div>
                                    <?php endif; ?>
                                    <div class="slide-column-content">
                                        <?php if (!empty($slide->col1_image_url)): ?>
                                            <img src="<?php echo h($slide->col1_image_url) ?>" alt="Column 1 Image" />
                                        <?php elseif (!empty($slide->col1_image_path)): ?>
                                            <img src="<?php echo h($slide->col1_image_path) ?>" alt="Column 1 Image" />
                                        <?php elseif (!empty($slide->col1_content)): ?>
                                            <div class="column-text"><?php echo nl2br(h($slide->col1_content)) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <!-- Column 2 -->
                                <div class="slide-column" style="flex: <?php echo $col2WidthPercent ?>;">
                                    <?php if (!empty($slide->col2_header)): ?>
                                        <div class="slide-column-header"><?php echo h($slide->col2_header) ?></div>
                                    <?php endif; ?>
                                    <div class="slide-column-content">
                                        <?php if (!empty($slide->col2_image_url)): ?>
                                            <img src="<?php echo h($slide->col2_image_url) ?>" alt="Column 2 Image" />
                                        <?php elseif (!empty($slide->col2_image_path)): ?>
                                            <img src="<?php echo h($slide->col2_image_path) ?>" alt="Column 2 Image" />
                                        <?php elseif (!empty($slide->col2_content)): ?>
                                            <div class="column-text"><?php echo nl2br(h($slide->col2_content)) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php 
                            // Display legend if present
                            $legendItems = $slide->getLegendItems();
                            if (!empty($legendItems)): 
                            ?>
                                <div class="slide-legend">
                                    <?php foreach ($legendItems as $item): ?>
                                        <div class="legend-item">
                                            <div class="legend-color" style="background: <?php echo h($item['color'] ?? '#ccc') ?>;"></div>
                                            <span><?php echo h($item['label'] ?? '') ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Single column layout -->
                            <?php if (!empty($slide->title)): ?>
                                <h2><?php echo h($slide->title) ?></h2>
                            <?php elseif (!empty($slide->description)): ?>
                                <h2><?php echo h($slide->description) ?></h2>
                            <?php endif; ?>
                            <?php if (!empty($slide->subtitle)): ?>
                                <h3><?php echo h($slide->subtitle) ?></h3>
                            <?php endif; ?>
                            <?php if (!empty($slide->image_url)): ?>
                                <img src="<?php echo h($slide->image_url) ?>" alt="Slide Image" class="slide-image" />
                            <?php elseif (!empty($slide->col1_image_url)): ?>
                                <img src="<?php echo h($slide->col1_image_url) ?>" alt="Slide Image" class="slide-image" />
                            <?php elseif (!empty($slide->col1_image_path)): ?>
                                <img src="<?php echo h($slide->col1_image_path) ?>" alt="Slide Image" class="slide-image" />
                            <?php endif; ?>
                            <?php if (!empty($slide->col1_content)): ?>
                                <div class="slide-text-content" style="font-size: 16px; line-height: 1.8;">
                                    <?php echo nl2br(h($slide->col1_content)) ?>
                                </div>
                            <?php endif; ?>
                            <?php 
                            // Display legend if present
                            $legendItems = $slide->getLegendItems();
                            if (!empty($legendItems)): 
                            ?>
                                <div class="slide-legend">
                                    <?php foreach ($legendItems as $item): ?>
                                        <div class="legend-item">
                                            <div class="legend-color" style="background: <?php echo h($item['color'] ?? '#ccc') ?>;"></div>
                                            <span><?php echo h($item['label'] ?? '') ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="slide-number-badge">
                        <?php echo $index + 1 ?> / <?php echo $slides->count() ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Navigation Controls -->
        <div class="navigation-controls">
            <button class="nav-btn" id="prevBtn" onclick="previousSlide()">
                <i class="fas fa-chevron-left me-2"></i>Previous
            </button>
            <div class="slide-counter">
                <span id="currentSlide">1</span> / <span id="totalSlides"><?php echo $slides->count() ?></span>
            </div>
            <button class="nav-btn" id="nextBtn" onclick="nextSlide()">
                Next<i class="fas fa-chevron-right ms-2"></i>
            </button>
        </div>

        <!-- Thumbnail Strip -->
        <div class="thumbnail-strip">
            <?php foreach ($slideArray as $index => $slide): ?>
                <?php 
                $slideConfig = $slide->getSlideConfig();
                $slideType = $slide->slide_type ?? 'custom';
                $layoutColumns = $slide->layout_columns ?? 1;
                ?>
                <div class="thumbnail <?php echo $index === 0 ? 'active' : '' ?>" onclick="goToSlide(<?php echo $index ?>)" title="<?php echo h(str_replace('_', ' ', ucfirst($slideType))) ?>">
                    <?php if ($slideType !== 'custom'): ?>
                        <div class="thumbnail-type"><?php echo h(str_replace('_', ' ', $slideType)) ?></div>
                    <?php endif; ?>
                    <div class="thumbnail-content">
                        <?php if ($slide->slide_order === 1): ?>
                            <!-- Cover slide thumbnail -->
                            <div style="display: flex; align-items: center; justify-content: center; height: 60px; background: #f8f9fa; border: 1px dashed #dc3545; border-radius: 3px; margin: 2px;">
                                <div style="text-align: center;">
                                    <div style="font-size: 8px; font-weight: bold; color: #dc3545;">Cover Page</div>
                                    <div style="font-size: 6px; color: #6c757d; font-style: italic;">(Patient Info)</div>
                                </div>
                            </div>
                        <?php elseif ($layoutColumns === 2): ?>
                            <!-- Two-column thumbnail -->
                            <div style="font-size: 7px; margin-bottom: 2px; text-align: center; font-weight: bold;">
                                <?php echo h(substr($slide->title ?? $slide->description ?? '', 0, 25)) ?><?php echo strlen($slide->title ?? $slide->description ?? '') > 25 ? '...' : '' ?>
                            </div>
                            <div class="thumbnail-two-cols">
                                <?php if (!empty($slide->col1_image_url)): ?>
                                    <img src="<?php echo h($slide->col1_image_url) ?>" alt="Col 1" />
                                <?php elseif (!empty($slide->col1_image_path)): ?>
                                    <img src="<?php echo h($slide->col1_image_path) ?>" alt="Col 1" />
                                <?php else: ?>
                                    <div style="flex:1; background:#e9ecef; display:flex; align-items:center; justify-content:center; font-size:6px; color:#6c757d;">Col 1</div>
                                <?php endif; ?>
                                <?php if (!empty($slide->col2_image_url)): ?>
                                    <img src="<?php echo h($slide->col2_image_url) ?>" alt="Col 2" />
                                <?php elseif (!empty($slide->col2_image_path)): ?>
                                    <img src="<?php echo h($slide->col2_image_path) ?>" alt="Col 2" />
                                <?php else: ?>
                                    <div style="flex:1; background:#e9ecef; display:flex; align-items:center; justify-content:center; font-size:6px; color:#6c757d;">Col 2</div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <!-- Single column thumbnail -->
                            <?php if (!empty($slide->title)): ?>
                                <div style="font-size: 7px; margin-bottom: 2px;"><?php echo h(substr($slide->title, 0, 40)) ?><?php echo strlen($slide->title) > 40 ? '...' : '' ?></div>
                            <?php elseif (!empty($slide->description)): ?>
                                <div style="font-size: 7px; margin-bottom: 2px;"><?php echo h(substr($slide->description, 0, 40)) ?><?php echo strlen($slide->description) > 40 ? '...' : '' ?></div>
                            <?php endif; ?>
                            <?php if (!empty($slide->image_url)): ?>
                                <img src="<?php echo h($slide->image_url) ?>" alt="Slide preview" />
                            <?php elseif (!empty($slide->col1_image_url)): ?>
                                <img src="<?php echo h($slide->col1_image_url) ?>" alt="Slide preview" />
                            <?php elseif (!empty($slide->col1_image_path)): ?>
                                <img src="<?php echo h($slide->col1_image_path) ?>" alt="Slide preview" />
                            <?php elseif (!empty($slide->col1_content)): ?>
                                <div style="font-size: 6px; line-height: 1.2; color: #666; overflow: hidden; max-height: 50px;">
                                    <?php echo h(substr($slide->col1_content, 0, 100)) ?><?php echo strlen($slide->col1_content) > 100 ? '...' : '' ?>
                                </div>
                            <?php else: ?>
                                <div style="display:flex; align-items:center; justify-content:center; height:50px; background:#f8f9fa; border-radius:3px;">
                                    <span style="font-size:7px; color:#6c757d;">No content</span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="thumbnail-number"><?php echo $index + 1 ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <div class="empty-state">
        <i class="fas fa-file-powerpoint fa-4x text-muted mb-3"></i>
        <h3 class="text-muted mb-3">No Slides Yet</h3>
        <p class="text-muted mb-4">Start creating your MEG PowerPoint report by adding your first slide.</p>
        <?php echo $this->Html->link(
            '<i class="fas fa-plus-circle me-2"></i>Create First Slide',
            ['action' => 'add', '?' => ['report_id' => $reportId]],
            ['class' => 'btn btn-danger btn-lg', 'escape' => false]
        ) ?>
    </div>
<?php endif; ?>

<?php if ($slides->count() > 0): ?>
<?php $this->start('script'); ?>
<script>
let currentSlideIndex = 0;
const totalSlides = <?php echo $slides->count() ?>;

function showSlide(index) {
    console.log('=== showSlide called with index:', index);
    
    if (index < 0 || index >= totalSlides) {
        console.log('Invalid slide index:', index);
        return;
    }
    
    // Hide all slides
    const allSlides = document.querySelectorAll('.slide-container');
    console.log('Total slide elements found:', allSlides.length);
    
    allSlides.forEach((slide, i) => {
        const slideIndex = slide.getAttribute('data-slide-index');
        console.log(`Slide ${i}: data-slide-index="${slideIndex}", classList:`, slide.classList.value);
        slide.classList.remove('active');
    });
    
    // Remove active from all thumbnails
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    
    // Show current slide
    if (allSlides[index]) {
        console.log('Adding active to slide at index', index);
        allSlides[index].classList.add('active');
        console.log('After adding active, classList:', allSlides[index].classList.value);
        console.log('Computed styles:', {
            opacity: window.getComputedStyle(allSlides[index]).opacity,
            visibility: window.getComputedStyle(allSlides[index]).visibility,
            zIndex: window.getComputedStyle(allSlides[index]).zIndex
        });
    } else {
        console.log('Slide element not found at index', index);
    }
    
    // Highlight current thumbnail
    const thumbnails = document.querySelectorAll('.thumbnail');
    if (thumbnails[index]) {
        thumbnails[index].classList.add('active');
        // Scroll thumbnail into view
        thumbnails[index].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
    
    // Update counter
    document.getElementById('currentSlide').textContent = index + 1;
    
    // Update button states
    document.getElementById('prevBtn').disabled = (index === 0);
    document.getElementById('nextBtn').disabled = (index === totalSlides - 1);
    
    currentSlideIndex = index;
}

function nextSlide() {
    if (currentSlideIndex < totalSlides - 1) {
        showSlide(currentSlideIndex + 1);
    }
}

function previousSlide() {
    if (currentSlideIndex > 0) {
        showSlide(currentSlideIndex - 1);
    }
}

function goToSlide(index) {
    showSlide(index);
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    if (e.key === 'ArrowRight' || e.key === ' ') {
        e.preventDefault();
        nextSlide();
    } else if (e.key === 'ArrowLeft') {
        e.preventDefault();
        previousSlide();
    } else if (e.key === 'Home') {
        e.preventDefault();
        goToSlide(0);
    } else if (e.key === 'End') {
        e.preventDefault();
        goToSlide(totalSlides - 1);
    }
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing slides');
    showSlide(0);
});

// Also try immediate initialization
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(function() {
        showSlide(0);
    }, 100);
}
</script>
<?php $this->end(); ?>
<?php endif; ?>

<meta name="csrf-token" content="<?php echo $this->request->getAttribute('csrfToken'); ?>">
