<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Report $report
 */
$this->assign('title', 'MEG Report - Case #' . $report->case->id);
?>

<style>
    body {
        background: #f8f9fa;
    }
    
    /* Slides Grid Layout */
    .slides-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
        padding: 0;
    }
    
    /* Slide Card */
    .slide-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s;
        cursor: move;
        position: relative;
    }
    
    .slide-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-4px);
        border-color: #dc3545;
    }
    
    .slide-card.sortable-ghost {
        opacity: 0.4;
    }
    
    .slide-card.sortable-drag {
        opacity: 0.8;
        box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    }
    
    /* Slide Number Badge */
    .slide-number {
        position: absolute;
        top: 12px;
        left: 12px;
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        z-index: 10;
        box-shadow: 0 2px 6px rgba(220, 53, 69, 0.4);
    }
    
    /* Slide Image Container - 16:9 Aspect Ratio */
    .slide-image-container {
        width: 100%;
        padding-top: 56.25%; /* 16:9 aspect ratio */
        position: relative;
        background: #f8f9fa;
        overflow: hidden;
    }
    
    .slide-image-container img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: white;
    }
    
    /* Slide Actions */
    .slide-actions {
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        border-top: 1px solid #e0e0e0;
    }
    
    .slide-actions small {
        font-size: 12px;
        color: #6c757d;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .slide-actions .btn-danger {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 6px;
    }
    
    /* Add Slide Card */
    .add-slide-card {
        background: white;
        border: 2px dashed #dc3545;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 250px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .add-slide-card:hover {
        border-color: #c82333;
        background: #fff5f5;
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);
    }
    
    .add-slide-card i {
        font-size: 48px;
        color: #dc3545;
        margin-bottom: 10px;
    }
    
    .add-slide-card span {
        font-size: 16px;
        color: #dc3545;
        font-weight: 600;
    }
    
    /* Empty State */
    .empty-state-card {
        background: white;
        border: 2px dashed #dc3545;
        border-radius: 12px;
        padding: 60px 40px;
        text-align: center;
    }
    
    .empty-state-card i {
        font-size: 64px;
        color: #dc3545;
        margin-bottom: 20px;
    }
    
    .empty-state-card h4 {
        color: #212529;
        margin-bottom: 12px;
    }
    
    .empty-state-card p {
        color: #6c757d;
        margin-bottom: 24px;
    }
        color: #5f6368;
        margin-right: 8px;
        font-weight: 500;
    }
    
    /* Main Editor Area */
    .editor-main {
        display: flex;
        height: calc(100vh - 120px);
        margin: 0;
        padding: 0;
        overflow: hidden;
    }
    
    /* Thumbnail Sidebar */
    .thumbnail-sidebar {
        width: 200px;
        background: #f8f9fa;
        border-right: 1px solid #e0e0e0;
        overflow-y: auto;
        padding: 12px 8px;
        flex-shrink: 0;
    }
    
    .thumbnail-item {
        background: white;
        border: 2px solid #e0e0e0;
        border-radius: 4px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }
    
    .thumbnail-item:hover {
        border-color: #1a73e8;
        box-shadow: 0 2px 8px rgba(26, 115, 232, 0.2);
    }
    
    .thumbnail-item.active {
        border-color: #1a73e8;
        border-width: 3px;
        box-shadow: 0 2px 12px rgba(26, 115, 232, 0.3);
    }
    
    .thumbnail-number {
        position: absolute;
        top: 4px;
        left: 4px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        z-index: 1;
    }
    
    .thumbnail-image {
        width: 100%;
        height: 120px;
        object-fit: cover;
        display: block;
    }
    
    .thumbnail-drag-handle {
        position: absolute;
        bottom: 4px;
        right: 4px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 4px;
        border-radius: 4px;
        font-size: 10px;
        cursor: move;
    }
    
    .add-slide-btn {
        width: 100%;
        padding: 12px;
        background: white;
        border: 2px dashed #dadce0;
        border-radius: 4px;
        color: #1a73e8;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .add-slide-btn:hover {
        background: #e8f0fe;
        border-color: #1a73e8;
    }
    
    /* Content Area */
    .content-area {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        background: #f1f3f4;
    }
    
    .content-wrapper {
        max-width: 1000px;
        margin: 0 auto;
    }
    
    /* Slide Canvas - Document Style */
    .slide-canvas {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 24px;
        overflow: hidden;
        transition: box-shadow 0.3s;
        scroll-margin-top: 24px;
    }
    
    .slide-canvas:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    }
    
    .slide-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e0e0e0;
    .slide-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .slide-actions {
        display: flex;
        gap: 8px;
    }
    
    .slide-body {
        padding: 24px;
    }
    
    /* Simple Slide Container */
    .simple-slide-container {
        background: white;
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    /* Text Section */
    .text-section {
        margin-bottom: 20px;
    }
    
    .text-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #202124;
        margin-bottom: 8px;
    }
    
    .text-label i {
        color: #5f6368;
        margin-right: 6px;
    }
    
    .slide-text-input {
        width: 100%;
        min-height: 80px;
        padding: 12px 16px;
        border: 1px solid #dadce0;
        border-radius: 4px;
        font-size: 14px;
        line-height: 1.6;
        font-family: 'Arial', sans-serif;
        resize: vertical;
        transition: all 0.2s;
    }
    
    .slide-text-input:focus {
        outline: none;
        border-color: #1a73e8;
        box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
    }
    
    .slide-text-input::placeholder {
        color: #9aa0a6;
    }
    
    /* Image Display */
    .slide-image-display {
        margin: 24px 0;
        text-align: center;
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
    }
    
    .slide-image-display img {
        max-width: 100%;
        height: auto;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    /* Upload Zone - Modern Design */
    .upload-zone {
        background: white;
        border: 2px dashed #dadce0;
        border-radius: 12px;
        padding: 48px 24px;
        text-align: center;
        transition: all 0.3s;
        cursor: pointer;
        margin-bottom: 24px;
    }
    
    .upload-zone:hover {
        border-color: #1a73e8;
        background: #f8f9fa;
    }
    
    .upload-zone.dragover {
        border-color: #1a73e8;
        background: #e8f0fe;
    }
    
    .upload-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        background: #e8f0fe;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1a73e8;
        font-size: 28px;
    }
    
    /* Floating Action Buttons */
    .fab-container {
        position: fixed;
        bottom: 32px;
        right: 32px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        z-index: 1000;
    }
    
    .fab {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #1a73e8;
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }
    
    .fab:hover {
        background: #1557b0;
        box-shadow: 0 6px 16px rgba(0,0,0,0.4);
        transform: scale(1.1);
    }
    
    .fab.success {
        background: #34a853;
    }
    
    .fab.success:hover {
        background: #2d8e47;
    }
    
    /* Auto-save Indicator */
    .autosave-indicator {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #5f6368;
        font-size: 13px;
        padding: 4px 12px;
        border-radius: 16px;
        background: #f1f3f4;
    }
    
    .autosave-indicator.saving {
        color: #ea8600;
    }
    
    .autosave-indicator.saved {
        color: #34a853;
    }
    
    .pulse {
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    /* Drag Handle */
    .drag-handle {
        cursor: move;
        color: #9aa0a6;
        transition: color 0.2s;
    }
    
    .drag-handle:hover {
        color: #1a73e8;
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 24px;
        color: #5f6368;
    }
    
    .empty-state-icon {
        font-size: 72px;
        color: #dadce0;
        margin-bottom: 24px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .toolbar-section {
            padding: 0 8px;
        }
        
        .editor-main {
            padding: 0 12px;
        }
        
        .fab-container {
            bottom: 16px;
            right: 16px;
        }
    }
</style>

<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="card border-0 shadow mb-4">
        <div class="card-body bg-danger text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="mb-2 fw-bold">
                        <i class="fas fa-file-powerpoint me-2"></i>MEG Report - Case #<?php echo h($report->case->id); ?>
                    </h2>
                    <p class="mb-0">
                        <i class="fas fa-user-injured me-2"></i>
                        <?php echo h($report->case->patient_user->first_name . ' ' . $report->case->patient_user->last_name); ?>
                        <span class="mx-2">•</span>
                        <i class="fas fa-images me-2"></i>
                        <?php echo count($report->report_images); ?> Slide<?php echo count($report->report_images) !== 1 ? 's' : ''; ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="btn-group" role="group">
                        <?php if (!empty($report->report_images)): ?>
                            <button class="btn btn-light" onclick="downloadReport()">
                                <i class="fas fa-download me-1"></i> Download PPT
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-outline-light" onclick="window.history.back()">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions Card -->
    <?php if (count($report->report_images) > 0): ?>
    <div class="alert alert-info border-0 shadow-sm mb-4">
        <div class="d-flex">
            <div class="me-3">
                <i class="fas fa-info-circle fa-2x"></i>
            </div>
            <div>
                <h6 class="mb-1 fw-bold">How to manage your slides:</h6>
                <ul class="mb-0 ps-3">
                    <li><strong>Drag & Drop:</strong> Click and drag slides to reorder them</li>
                    <li><strong>Add Slides:</strong> Click the "Add Slide" button or the dashed card</li>
                    <li><strong>Delete:</strong> Click the red delete button on any slide</li>
                    <li><strong>Download:</strong> Click "Download PPT" when ready to export</li>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="card border-0 shadow">
        <div class="card-header bg-light py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="fas fa-layer-group me-2 text-danger"></i>Presentation Slides
                </h5>
                <button class="btn btn-danger btn-sm" onclick="document.getElementById('imageUpload').click()">
                    <i class="fas fa-plus me-1"></i>Add Slide
                </button>
            </div>
        </div>
        <div class="card-body bg-white p-4">
            <!-- Upload Form (hidden) -->
            <?php echo $this->Form->create(null, [
                'url' => ['action' => 'uploadSlideImage', $report->id],
                'type' => 'file',
                'id' => 'uploadForm',
                'style' => 'display: none;'
            ]); ?>
            <?php echo $this->Form->control('image', [
                'type' => 'file',
                'label' => false,
                'id' => 'imageUpload',
                'accept' => 'image/jpeg,image/png,image/gif',
                'onchange' => 'handleFileSelect(this)'
            ]); ?>
            <?php echo $this->Form->end(); ?>

            <!-- Slides Grid -->
            <?php if (empty($report->report_images)): ?>
                <div class="empty-state-card">
                    <i class="fas fa-images"></i>
                    <h4>No Slides Yet</h4>
                    <p class="mb-4">Start building your presentation by adding your first slide</p>
                    <button class="btn btn-danger btn-lg" onclick="document.getElementById('imageUpload').click()">
                        <i class="fas fa-plus me-2"></i>Add First Slide
                    </button>
                </div>
            <?php else: ?>
                <div class="slides-grid" id="slides-container">
            <?php foreach ($report->report_images as $index => $image): ?>
                <div class="slide-card" data-image-id="<?php echo $image->id; ?>">
                    <div class="slide-number"><?php echo $index + 1; ?></div>
                    <div class="slide-image-container">
                        <img src="<?php echo $this->Url->build(['controller' => 'Reports', 'action' => 'getSlideImage', $image->id]); ?>" 
                             alt="Slide <?php echo $index + 1; ?>">
                    </div>
                    <div class="slide-actions">
                        <small class="text-muted"><?php echo h($image->original_filename); ?></small>
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $image->id; ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
            
                    <!-- Add Slide Card -->
                    <div class="add-slide-card" onclick="document.getElementById('imageUpload').click()">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Slide</span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Sortable.js for drag and drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeSortable();
    updateSlideNumbers();
});

// Initialize Sortable for drag and drop
function initializeSortable() {
    const slidesContainer = document.getElementById('slides-container');
    
    if (slidesContainer) {
        // Count only slide cards, not the add-slide-card
        const slideCards = slidesContainer.querySelectorAll('.slide-card');
        
        if (slideCards.length > 0) {
            new Sortable(slidesContainer, {
                animation: 300,
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                filter: '.add-slide-card',  // Prevent dragging the add card
                preventOnFilter: false,      // Allow clicks on add card
                handle: '.slide-card',       // Only drag by the card itself
                onEnd: function(evt) {
                    updateSlideNumbers();
                    updateSlideOrder();
                }
            });
        }
    }
}

function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        document.getElementById('uploadForm').submit();
    }
}

function updateSlideOrder() {
    const slideCards = document.querySelectorAll('.slide-card');
    const orderData = {};
    
    slideCards.forEach((card, index) => {
        const imageId = card.dataset.imageId;
        orderData[index] = imageId;
    });
    
    const formData = new FormData();
    // Send each order as separate form field: slide_order[0]=1, slide_order[1]=2, etc.
    Object.keys(orderData).forEach(order => {
        formData.append(`slide_order[${order}]`, orderData[order]);
    });
    
    fetch('<?php echo $this->Url->build(['action' => 'editMegReport', $report->id]); ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-Token': '<?php echo $this->request->getAttribute('csrfToken'); ?>'
        },
        body: formData
    })
    .then(response => {
        if (response.ok) {
            console.log('Slide order updated successfully');
        }
        return response.text();
    })
    .catch(error => {
        console.error('Error updating slide order:', error);
    });
}

function updateSlideNumbers() {
    const slideCards = document.querySelectorAll('.slide-card');
    slideCards.forEach((card, index) => {
        const slideNumber = card.querySelector('.slide-number');
        if (slideNumber) {
            slideNumber.textContent = index + 1;
        }
    });
}

function confirmDelete(imageId) {
    if (confirm('Are you sure you want to delete this slide? This action cannot be undone.')) {
        deleteSlide(imageId);
    }
}

function deleteSlide(imageId) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?php echo $this->Url->build(['action' => 'deleteSlideImage', '_TOKEN_']); ?>'.replace('_TOKEN_', imageId);
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_csrfToken';
    csrfInput.value = '<?php echo $this->request->getAttribute('csrfToken'); ?>';
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
}

function downloadReport() {
    window.location.href = '<?php echo $this->Url->build(['action' => 'downloadMegReport', $report->id]); ?>';
}
</script>
