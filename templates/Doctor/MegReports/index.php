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

/**
 * Helper function to format structured bullets content
 * Matches PPT output styling with proper line heights and indentation
 * @param string $content JSON string or plain text
 * @param bool $truncate Whether to truncate the output
 * @param int $maxLength Max length for truncated output
 * @return string Formatted HTML
 */
function formatStructuredContent($content, $truncate = false, $maxLength = 100) {
    if (empty($content)) return '';
    
    // Check if it's JSON (structured bullets)
    $decoded = json_decode($content, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $html = '<div class="structured-bullets-content">';
        $isFirst = true;
        
        foreach ($decoded as $section) {
            $heading = h($section['heading'] ?? '');
            
            // Section heading - bold, 1.5 line height, flush left (matches PPT)
            if ($heading) {
                $marginTop = $isFirst ? '0' : '12px';
                $html .= '<div style="font-weight: bold; line-height: 1.5; margin-top: ' . $marginTop . '; margin-bottom: 4px;">' . $heading . '</div>';
                $isFirst = false;
            }
            
            // Items with bullet points
            foreach ($section['items'] ?? [] as $item) {
                $title = h($item['title'] ?? '');
                
                // Item - normal weight, 1.4 line height, indented (matches PPT)
                if ($title) {
                    $html .= '<div style="line-height: 1.4; margin-left: 20px; margin-bottom: 2px;">• ' . $title . '</div>';
                }
                
                // Subitems - slightly smaller, lighter color, more indented (matches PPT)
                foreach ($item['subitems'] ?? [] as $subitem) {
                    $html .= '<div style="line-height: 1.3; margin-left: 40px; margin-bottom: 2px; font-size: 0.95em; color: #333;">○ ' . h($subitem) . '</div>';
                }
            }
        }
        $html .= '</div>';
        
        if ($truncate && strlen(strip_tags($html)) > $maxLength) {
            // Return truncated plain text version
            $plainText = '';
            foreach ($decoded as $section) {
                $plainText .= ($section['heading'] ?? '') . ' ';
                foreach ($section['items'] ?? [] as $item) {
                    $plainText .= '• ' . ($item['title'] ?? '') . ' ';
                    foreach ($item['subitems'] ?? [] as $subitem) {
                        $plainText .= '○ ' . $subitem . ' ';
                    }
                }
            }
            return h(substr($plainText, 0, $maxLength)) . '...';
        }
        return $html;
    }
    
    // Not JSON, return as plain text
    if ($truncate) {
        return h(substr($content, 0, $maxLength)) . (strlen($content) > $maxLength ? '...' : '');
    }
    return nl2br(h($content));
}

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
                
                // Apply config fallbacks for slides created before template updates
                if ($slideConfig) {
                    if (empty($slide->layout_columns) && isset($slideConfig['columns'])) {
                        $layoutColumns = $slideConfig['columns'];
                    }
                    if (empty($slide->subtitle) && !empty($slideConfig['subtitle'])) {
                        $slide->subtitle = $slideConfig['subtitle'];
                    }
                    if (empty($slide->col1_content)) {
                        if (isset($slideConfig['header_text']['content'])) {
                            $hc = $slideConfig['header_text']['content'];
                            $slide->col1_content = is_array($hc) ? implode("\n", $hc) : $hc;
                        } elseif (isset($slideConfig['col1']['default_content'])) {
                            $slide->col1_content = $slideConfig['col1']['default_content'];
                        }
                    }
                    if (empty($slide->col1_header) && !empty($slideConfig['col1']['header'])) {
                        $slide->col1_header = $slideConfig['col1']['header'];
                    }
                    if (empty($slide->col2_header) && !empty($slideConfig['col2']['header'])) {
                        $slide->col2_header = $slideConfig['col2']['header'];
                    }
                }
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
                            <!-- Cover slide with centered content - matches PPT output -->
                            <?php echo formatCoverSlideContent($slide) ?>
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
                            
                            // For text_header_two_images layout, always show col1_content as header text
                            $isTextHeaderLayout = ($layout === 'text_header_two_images');
                            ?>
                            
                            <?php if ($isTextHeaderLayout && !empty($slide->col1_content)): ?>
                                <!-- Header text for text_header_two_images layout -->
                                <div class="slide-header-text" style="margin-bottom: 15px; font-size: 14px; line-height: 1.6;">
                                    <?php 
                                    // Format as bullet points
                                    $lines = preg_split('/\r\n|\r|\n/', $slide->col1_content);
                                    foreach ($lines as $line):
                                        $line = trim($line);
                                        if (empty($line)) continue;
                                    ?>
                                        <div style="margin-bottom: 4px;">• <?php echo h(strip_tags($line)) ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
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
                                        <?php elseif (!empty($slide->col1_content) && !$isTextHeaderLayout): ?>
                                            <div class="column-text"><?php echo formatStructuredContent($slide->col1_content) ?></div>
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
                                            <div class="column-text"><?php echo formatStructuredContent($slide->col2_content) ?></div>
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
                        <?php elseif (($slideConfig['layout'] ?? '') === 'multi_image_with_titles'): ?>
                            <!-- Multi-image layout -->
                            <?php if (!empty($slide->title)): ?>
                                <h2><?php echo h($slide->title) ?></h2>
                            <?php endif; ?>
                            
                            <?php 
                            $maxImages = $slideConfig['max_images'] ?? 5;
                            $defaultTitles = $slideConfig['default_image_titles'] ?? [];
                            $imageColumns = ['col1', 'col2', 'col3', 'col4', 'col5'];
                            $hasImages = false;
                            
                            // First pass - count actual images to calculate widths
                            $imageCount = 0;
                            for ($i = 0; $i < $maxImages; $i++) {
                                $colName = $imageColumns[$i];
                                $imagePathField = $colName . '_image_path';
                                $imageUrlField = $colName . '_image_url';
                                if (!empty($slide->{$imageUrlField}) || !empty($slide->{$imagePathField})) {
                                    $imageCount++;
                                }
                            }
                            $columnWidthPercent = $imageCount > 0 ? (100 / $imageCount) : 20;
                            ?>
                            <div class="multi-image-grid" style="display: flex; flex-wrap: nowrap; gap: 5px; justify-content: center; height: 340px; align-items: flex-start;">
                                <?php for ($i = 0; $i < $maxImages; $i++): 
                                    $colName = $imageColumns[$i];
                                    $imagePathField = $colName . '_image_path';
                                    $imageUrlField = $colName . '_image_url';
                                    $headerField = $colName . '_header';
                                    $defaultTitle = $defaultTitles[$i] ?? 'Discharge ' . ($i + 1);
                                    $imageUrl = $slide->{$imageUrlField} ?? null;
                                    if (!empty($imageUrl) || !empty($slide->{$imagePathField})):
                                        $hasImages = true;
                                ?>
                                    <div class="multi-image-item" style="text-align: center; flex: 1; max-width: <?php echo $columnWidthPercent ?>%; display: flex; flex-direction: column;">
                                        <div class="image-title" style="font-weight: bold; font-size: 12px; margin-bottom: 5px; padding: 0 2px;">
                                            <?= h($slide->{$headerField} ?? $defaultTitle) ?>
                                        </div>
                                        <div style="flex: 1; display: flex; align-items: flex-start; justify-content: center;">
                                            <img src="<?= h($imageUrl ?? $slide->{$imagePathField}) ?>" 
                                                 alt="<?= h($slide->{$headerField} ?? $defaultTitle) ?>" 
                                                 style="max-width: 100%; max-height: 310px; object-fit: contain;" />
                                        </div>
                                    </div>
                                <?php endif; endfor; ?>
                                
                                <?php if (!$hasImages): ?>
                                    <div class="text-muted text-center py-3" style="width: 100%;">
                                        <i class="fas fa-images fa-2x mb-2"></i>
                                        <div>No images uploaded yet</div>
                                    </div>
                                <?php endif; ?>
                            </div>
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
                                    <?php echo formatStructuredContent($slide->col1_content) ?>
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

        <!-- Reorder Info Banner -->
        <div class="reorder-info-banner">
            <i class="fas fa-info-circle me-2"></i>Drag and drop thumbnails to reorder slides. The cover page cannot be moved.
        </div>

        <!-- Thumbnail Strip -->
        <div class="thumbnail-strip" id="thumbnailStrip">
            <?php foreach ($slideArray as $index => $slide): ?>
                <?php 
                $slideConfig = $slide->getSlideConfig();
                $slideType = $slide->slide_type ?? 'custom';
                $layoutColumns = $slide->layout_columns ?? 1;
                $isCoverSlide = ($slide->slide_order === 1);
                ?>
                <div class="thumbnail <?php echo $index === 0 ? 'active' : '' ?> <?php echo $isCoverSlide ? 'cover-slide' : '' ?>" 
                     onclick="goToSlide(<?php echo $index ?>)" 
                     title="<?php echo h(str_replace('_', ' ', ucfirst($slideType))) ?>"
                     data-slide-id="<?php echo $slide->id ?>"
                     data-is-cover="<?php echo $isCoverSlide ? '1' : '0' ?>">
                    <div class="reorder-handle"><i class="fas fa-grip-vertical me-1"></i>Drag</div>
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
                        <?php elseif (($slideConfig['layout'] ?? '') === 'multi_image_with_titles'): ?>
                            <!-- Multi-image thumbnail -->
                            <div style="font-size: 7px; margin-bottom: 2px; text-align: center; font-weight: bold;">
                                <?php echo h(substr($slide->title ?? '', 0, 25)) ?><?php echo strlen($slide->title ?? '') > 25 ? '...' : '' ?>
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 2px; justify-content: center;">
                                <?php 
                                $imageColumns = ['col1', 'col2', 'col3', 'col4', 'col5'];
                                $imageCount = 0;
                                foreach ($imageColumns as $colName):
                                    $imageUrl = $slide->{$colName . '_image_url'} ?? $slide->{$colName . '_image_path'} ?? null;
                                    if (!empty($imageUrl)): 
                                        $imageCount++;
                                ?>
                                    <img src="<?= h($imageUrl) ?>" alt="<?= $colName ?>" style="max-width: 25px; max-height: 20px; object-fit: cover; border-radius: 2px;" />
                                <?php endif; endforeach; ?>
                                <?php if ($imageCount === 0): ?>
                                    <div style="font-size: 6px; color: #6c757d;"><i class="fas fa-images"></i> No images</div>
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
                                    <?php echo formatStructuredContent($slide->col1_content, true, 100) ?>
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
<meta name="csrf-token" content="<?php echo $this->request->getAttribute('csrfToken'); ?>">
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
