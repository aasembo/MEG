<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ReportSlide> $slides
 * @var \App\Model\Entity\Report $report
 * @var int $reportId
 * @var iterable<\App\Model\Entity\ReportTemplate> $reportTemplates
 */
$this->assign('title', 'MEG Report Slides');
$reportTemplates = $reportTemplates ?? [];

/**
 * Format cover slide content to match PPT output
 * Parses the description field the same way PPT does
 * @param object $slide Slide entity with description field
 * @return string Formatted HTML
 */
function formatCoverSlideContent($slide) {
    $description = $slide->description ?? '';
    if (empty($description)) {
        // Fallback to html_content if description is empty
        return $slide->html_content ?? '';
    }
    
    // Parse description the same way PPT does
    $lines = explode("\n", $description);
    $heading = array_shift($lines);
    $content = implode("\n", array_slice($lines, 2));
    
    $html = '<h2 style="font-size: 24px; font-weight: bold;">' . h($heading) . '</h2>';
    
    if (!empty($content)) {
        $html .= '<div style="font-size: 16px; margin-top: 20px;">';
        $contentLines = explode("\n", $content);
        foreach ($contentLines as $line) {
            if (strpos($line, 'Name:') !== false) {
                // Make patient name bold like PPT does
                $parts = explode(':', $line, 2);
                $html .= '<div>' . h($parts[0]) . ': ';
                if (isset($parts[1])) {
                    $html .= '<strong>' . h(trim($parts[1])) . '</strong>';
                }
                $html .= '</div>';
            } else {
                $html .= '<div>' . h($line) . '</div>';
            }
        }
        $html .= '</div>';
    }
    
    return $html;
}
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
/* Reorder Mode Styles */
.reorder-mode .thumbnail-strip {
    background: #fff3cd;
    border: 2px dashed #ffc107;
}
.reorder-mode .thumbnail {
    cursor: grab;
    transition: transform 0.2s, box-shadow 0.2s;
}
.reorder-mode .thumbnail:active {
    cursor: grabbing;
}
.reorder-mode .thumbnail.sortable-ghost {
    opacity: 0.4;
    background: #ffeeba;
}
.reorder-mode .thumbnail.sortable-drag {
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    transform: scale(1.05);
}
.reorder-mode .thumbnail.cover-slide {
    cursor: not-allowed;
    opacity: 0.6;
}
.reorder-mode .thumbnail:not(.cover-slide):hover {
    transform: scale(1.03);
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
}
.reorder-mode .reorder-handle {
    display: flex !important;
}
.reorder-handle {
    display: none;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(220, 53, 69, 0.9);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 10px;
    z-index: 10;
}
.reorder-mode .thumbnail .thumbnail-content {
    opacity: 0.7;
}
.reorder-info-banner {
    display: none;
    background: #fff3cd;
    color: #856404;
    padding: 10px 20px;
    text-align: center;
    font-size: 14px;
}
.reorder-mode .reorder-info-banner {
    display: block;
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
                <button type="button" id="reorderBtn" class="btn btn-sm btn-outline-light" onclick="toggleReorderMode()">
                    <i class="fas fa-sort me-1"></i>Reorder
                </button>
                <button type="button" id="saveOrderBtn" class="btn btn-sm btn-success" style="display: none;" onclick="saveSlideOrder()">
                    <i class="fas fa-check me-1"></i>Save Order
                </button>
                <button type="button" id="cancelOrderBtn" class="btn btn-sm btn-secondary" style="display: none;" onclick="cancelReorder()">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <?php echo $this->Html->link(
                    '<i class="fas fa-plus-circle me-1"></i>Add Slide',
                    ['action' => 'add', '?' => ['report_id' => $reportId]],
                    ['class' => 'btn btn-sm btn-outline-light', 'escape' => false, 'id' => 'addSlideBtn']
                ) ?>
                <button type="button" class="btn btn-sm btn-outline-light" data-bs-toggle="modal" data-bs-target="#reportTemplatesModal">
                    <i class="fas fa-copy me-1"></i>Templates
                </button>
                <?php echo $this->Html->link(
                    '<i class="fas fa-download me-1"></i>Download PPT',
                    ['action' => 'downloadPpt', $reportId],
                    ['class' => 'btn btn-sm btn-outline-light', 'escape' => false, 'id' => 'downloadPptBtn']
                ) ?>
                <?php echo $this->Html->link(
                    '<i class="fas fa-times me-1"></i>Close',
                    ['controller' => 'Cases', 'action' => 'view', $report->case_id],
                    ['class' => 'btn btn-sm btn-light', 'escape' => false]
                ) ?>
            </div>
        </div>

        <?php if ($slides->count() === 1): ?>
            <!-- New report (cover page only): offer report templates -->
            <div class="alert alert-warning border-0 rounded-0 mb-0 py-2 text-center" style="font-size: 14px;">
                <i class="fas fa-magic me-2"></i>
                <?php if (count($reportTemplates) > 0): ?>
                    <strong>New report:</strong> start from one of your report templates, or add slides one by one.
                    <button type="button" class="btn btn-sm btn-dark ms-2" data-bs-toggle="modal" data-bs-target="#reportTemplatesModal">
                        <i class="fas fa-copy me-1"></i>Start from Template
                    </button>
                <?php else: ?>
                    <strong>New report:</strong> use <em>Add Slide</em> to build your slides. Then use <em>Templates &rarr; Save</em> to reuse the same set of slides on your next report.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Slide Viewer -->
        <div class="slide-viewer">
            <?php 
            $slideArray = $slides->toArray();
            ?>
            <?php foreach ($slideArray as $index => $slide): ?>
                <div class="slide-container <?php echo $index === 0 ? 'active' : '' ?>" data-slide-index="<?php echo $index ?>" data-slide-id="<?php echo $slide->id ?>">
                    <!-- Slide type badge -->
                    <div class="slide-type-badge">
                        <?php echo $slide->isCustomLayout() ? 'custom layout' : h(str_replace('_', ' ', (string)$slide->slide_type)) ?>
                    </div>
                    
                    <div class="slide-actions-overlay">
                        <?php if ($slide->slide_type === 'cover_page'): ?>
                            <?php echo $this->Form->postLink(
                                '<i class="fas fa-sync-alt me-1"></i>Refresh from case',
                                ['action' => 'resetSlide', $slide->id],
                                ['class' => 'btn btn-sm btn-warning', 'escape' => false, 'title' => 'Update the cover page with the current case details']
                            ) ?>
                        <?php else: ?>
                            <?php echo $this->Html->link(
                                '<i class="fas fa-edit"></i>',
                                ['action' => 'edit', $slide->id],
                                ['class' => 'btn btn-sm btn-warning', 'escape' => false, 'title' => 'Edit']
                            ) ?>
                        <?php endif; ?>
                        <?php if ($slide->slide_order !== 1): ?>
                            <?php echo $this->Form->postLink(
                                '<i class="fas fa-trash"></i>',
                                ['action' => 'delete', $slide->id],
                                ['class' => 'btn btn-sm btn-danger', 'escape' => false, 'title' => 'Delete', 'confirm' => 'Delete this slide?']
                            ) ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="slide-content <?php echo $slide->slide_order === 1 ? 'text-center' : '' ?> <?php echo $slide->isCustomLayout() ? 'grid-slide' : '' ?>">
                        <?php if ($slide->slide_order === 1): ?>
                            <!-- Cover slide with centered content - matches PPT output -->
                            <?php echo formatCoverSlideContent($slide) ?>
                        <?php elseif ($slide->isCustomLayout()): ?>
                            <!-- Custom grid layout -->
                            <?php if (!empty($slide->title)): ?>
                                <h2><?php echo h($slide->title) ?></h2>
                            <?php endif; ?>
                            <?php if (!empty($slide->subtitle)): ?>
                                <h3>• <?php echo h($slide->subtitle) ?></h3>
                            <?php endif; ?>
                            <?php echo $this->element('MegReports/grid_layout', ['layout' => $slide->grid_layout]) ?>
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

        <!-- Reorder Info Banner -->
        <div class="reorder-info-banner">
            <i class="fas fa-info-circle me-2"></i>Drag and drop thumbnails to reorder slides. The cover page cannot be moved.
        </div>

        <!-- Thumbnail Strip -->
        <div class="thumbnail-strip" id="thumbnailStrip">
            <?php foreach ($slideArray as $index => $slide): ?>
                <?php
                $isCoverSlide = ($slide->slide_order === 1);
                $slideTypeLabel = $slide->isCustomLayout() ? 'custom layout' : str_replace('_', ' ', (string)$slide->slide_type);
                ?>
                <div class="thumbnail <?php echo $index === 0 ? 'active' : '' ?> <?php echo $isCoverSlide ? 'cover-slide' : '' ?>" 
                     onclick="goToSlide(<?php echo $index ?>)" 
                     title="<?php echo h(ucfirst($slideTypeLabel)) ?>"
                     data-slide-id="<?php echo $slide->id ?>"
                     data-is-cover="<?php echo $isCoverSlide ? '1' : '0' ?>">
                    <div class="reorder-handle"><i class="fas fa-grip-vertical me-1"></i>Drag</div>
                    <div class="thumbnail-type"><?php echo h($slideTypeLabel) ?></div>
                    <div class="thumbnail-content">
                        <?php if ($slide->slide_order === 1): ?>
                            <!-- Cover slide thumbnail -->
                            <div style="display: flex; align-items: center; justify-content: center; height: 60px; background: #f8f9fa; border: 1px dashed #dc3545; border-radius: 3px; margin: 2px;">
                                <div style="text-align: center;">
                                    <div style="font-size: 8px; font-weight: bold; color: #dc3545;">Cover Page</div>
                                    <div style="font-size: 6px; color: #6c757d; font-style: italic;">(Patient Info)</div>
                                </div>
                            </div>
                        <?php elseif ($slide->isCustomLayout()): ?>
                            <!-- Custom grid layout thumbnail -->
                            <div style="font-size: 7px; margin-bottom: 2px; text-align: center; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo h($slide->title ?? '') ?>
                            </div>
                            <div style="height: 52px;">
                                <?php echo $this->element('MegReports/grid_layout', ['layout' => $slide->grid_layout, 'compact' => true]) ?>
                            </div>
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
<meta name="csrf-token" content="<?php echo $this->request->getAttribute('csrfToken'); ?>">

<!-- Report Templates Modal -->
<div class="modal fade" id="reportTemplatesModal" tabindex="-1" aria-labelledby="reportTemplatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="reportTemplatesModalLabel">
                    <i class="fas fa-copy me-2"></i>Report Templates
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="fw-bold">Start from a template</h6>
                <p class="text-muted small mb-2">Adds the template's slides (layouts, titles, headers and text) after the current slides. Images are not included.</p>
                <?php if (count($reportTemplates) > 0): ?>
                    <ul class="list-group mb-4">
                        <?php foreach ($reportTemplates as $reportTemplate): ?>
                            <?php $templateSlides = $reportTemplate->getSlides(); ?>
                            <li class="list-group-item d-flex align-items-center gap-2">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?php echo h($reportTemplate->name) ?></div>
                                    <small class="text-muted">
                                        <?php echo count($templateSlides) ?> slide(s):
                                        <?php echo h(implode(', ', array_map(fn($ts) => ($ts['title'] ?? '') !== '' ? $ts['title'] : 'Untitled', array_slice($templateSlides, 0, 4)))) ?><?php echo count($templateSlides) > 4 ? ', ...' : '' ?>
                                    </small>
                                </div>
                                <?php echo $this->Form->create(null, ['url' => ['action' => 'applyReportTemplate', $reportId], 'class' => 'm-0']) ?>
                                    <?php echo $this->Form->hidden('template_id', ['value' => $reportTemplate->id]) ?>
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-plus me-1"></i>Use</button>
                                <?php echo $this->Form->end() ?>
                                <?php echo $this->Form->postLink(
                                    '<i class="fas fa-trash"></i>',
                                    ['action' => 'deleteReportTemplate', $reportTemplate->id, '?' => ['report_id' => $reportId]],
                                    ['escape' => false, 'class' => 'btn btn-sm btn-outline-secondary', 'title' => 'Delete template', 'confirm' => 'Delete report template "' . $reportTemplate->name . '"? Reports already made from it are not affected.']
                                ) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="alert alert-light border mb-4 small">You have no report templates yet. Save one below once this report's slides are set up.</div>
                <?php endif; ?>

                <h6 class="fw-bold">Save this report as a template</h6>
                <p class="text-muted small mb-2">Saves this report's custom layout slides, in order, without images or the patient cover page. Using an existing name replaces that template.</p>
                <?php echo $this->Form->create(null, ['url' => ['action' => 'saveReportTemplate', $reportId]]) ?>
                    <div class="input-group">
                        <input type="text" name="name" class="form-control" maxlength="150" required placeholder="Template name, e.g. Epilepsy pre-surgical">
                        <button type="submit" class="btn btn-dark"><i class="fas fa-save me-1"></i>Save</button>
                    </div>
                <?php echo $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

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

// ============================================
// Slide Reorder Functionality
// ============================================
let sortableInstance = null;
let originalOrder = [];

function toggleReorderMode() {
    const container = document.querySelector('.presentation-container');
    const thumbnailStrip = document.getElementById('thumbnailStrip');
    const reorderBtn = document.getElementById('reorderBtn');
    const saveOrderBtn = document.getElementById('saveOrderBtn');
    const cancelOrderBtn = document.getElementById('cancelOrderBtn');
    const infoBanner = document.querySelector('.reorder-info-banner');
    
    container.classList.add('reorder-mode');
    reorderBtn.style.display = 'none';
    saveOrderBtn.style.display = 'inline-block';
    cancelOrderBtn.style.display = 'inline-block';
    if (infoBanner) infoBanner.style.display = 'block';
    
    // Save original order
    originalOrder = [];
    thumbnailStrip.querySelectorAll('.thumbnail').forEach(thumb => {
        originalOrder.push(thumb.dataset.slideId);
    });
    
    // Initialize Sortable
    if (!sortableInstance) {
        sortableInstance = new Sortable(thumbnailStrip, {
            animation: 150,
            handle: '.thumbnail:not(.cover-slide)',
            draggable: '.thumbnail:not(.cover-slide)',
            ghostClass: 'sortable-ghost',
            dragClass: 'sortable-drag',
            filter: '.cover-slide',
            onStart: function(evt) {
                document.body.style.cursor = 'grabbing';
            },
            onEnd: function(evt) {
                document.body.style.cursor = '';
            }
        });
    }
    sortableInstance.option('disabled', false);
}

function cancelReorder() {
    const container = document.querySelector('.presentation-container');
    const thumbnailStrip = document.getElementById('thumbnailStrip');
    const reorderBtn = document.getElementById('reorderBtn');
    const saveOrderBtn = document.getElementById('saveOrderBtn');
    const cancelOrderBtn = document.getElementById('cancelOrderBtn');
    const infoBanner = document.querySelector('.reorder-info-banner');
    
    // Restore original order
    if (originalOrder.length > 0) {
        const thumbnails = Array.from(thumbnailStrip.querySelectorAll('.thumbnail'));
        originalOrder.forEach((slideId, index) => {
            const thumb = thumbnails.find(t => t.dataset.slideId === slideId);
            if (thumb && index < thumbnails.length) {
                thumbnailStrip.appendChild(thumb);
            }
        });
    }
    
    exitReorderMode();
}

function exitReorderMode() {
    const container = document.querySelector('.presentation-container');
    const reorderBtn = document.getElementById('reorderBtn');
    const saveOrderBtn = document.getElementById('saveOrderBtn');
    const cancelOrderBtn = document.getElementById('cancelOrderBtn');
    const infoBanner = document.querySelector('.reorder-info-banner');
    
    container.classList.remove('reorder-mode');
    reorderBtn.style.display = 'inline-block';
    saveOrderBtn.style.display = 'none';
    cancelOrderBtn.style.display = 'none';
    if (infoBanner) infoBanner.style.display = 'none';
    
    if (sortableInstance) {
        sortableInstance.option('disabled', true);
    }
}

function saveSlideOrder() {
    const thumbnailStrip = document.getElementById('thumbnailStrip');
    const thumbnails = thumbnailStrip.querySelectorAll('.thumbnail');
    const slideOrder = [];
    
    thumbnails.forEach((thumb, index) => {
        slideOrder.push({
            id: thumb.dataset.slideId,
            order: index + 1  // slide_order is 1-based
        });
    });
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    // Send AJAX request to save order
    fetch('<?php echo $this->Url->build(['controller' => 'MegReports', 'action' => 'reorder', 'prefix' => 'Doctor']) ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        },
        body: JSON.stringify({
            case_id: <?php echo $report->case_id ?>,
            slides: slideOrder
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message and reload
            alert('Slide order saved successfully!');
            window.location.reload();
        } else {
            alert('Error saving slide order: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving slide order. Please try again.');
    });
    
    exitReorderMode();
}

// Load Sortable.js dynamically if not already loaded
if (typeof Sortable === 'undefined') {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
    script.onload = function() {
        console.log('Sortable.js loaded');
    };
    document.head.appendChild(script);
}

</script>
<?php $this->end(); ?>
<?php endif; ?>
