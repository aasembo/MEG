<?php
declare(strict_types=1);

namespace App\Controller\Trait;

use App\Lib\S3DocumentService;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;

/**
 * PPT Download Trait
 * 
 * Shared PowerPoint generation functionality for MEG reports.
 * Used by Doctor, Admin, Technician, and Scientist controllers.
 */
trait PptDownloadTrait
{
    /**
     * Download PowerPoint presentation (read-only access)
     * This method does not require case assignment - for roles that have broader access
     *
     * @param int|null $reportId Report ID
     * @return \Cake\Http\Response
     * @throws \Cake\Http\Exception\NotFoundException When report not found.
     */
    protected function downloadPptReadOnly($reportId = null): Response
    {
        // Increase execution time for large reports with many S3 images
        set_time_limit(300);
        
        $Reports = $this->fetchTable('Reports');
        $report = $Reports->get($reportId, ['contain' => ['Cases']]);
        
        // Get all slides for this report
        $ReportSlides = $this->fetchTable('ReportSlides');
        $slides = $ReportSlides->find()
            ->where(['report_id' => $reportId])
            ->order(['slide_order' => 'ASC'])
            ->all();
        
        // Calculate current hash based on slides content
        $currentHash = $this->calculateReportHashForPpt($slides);
        
        // Generate expected filename
        $caseId = str_pad((string)$report->case_id, 6, 'X', STR_PAD_LEFT);
        $filename = 'MEG_Report_CASE_' . $caseId . '.pptx';
        
        // Check if we have a cached PPT that matches current content
        $s3Service = new S3DocumentService();
        if (!empty($report->ppt_download_url) && $report->ppt_hash === $currentHash) {
            // Cache hit - download from S3 and serve with proper filename
            $downloadUrl = $s3Service->getDownloadUrl($report->ppt_download_url);
            $tmpFile = TMP . 'ppt_cache_' . uniqid() . '.pptx';
            
            $context = stream_context_create([
                'http' => ['timeout' => 60],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
            ]);
            $fileContent = @file_get_contents($downloadUrl, false, $context);
            
            if ($fileContent !== false) {
                file_put_contents($tmpFile, $fileContent);
                
                $response = $this->response->withFile($tmpFile, [
                    'download' => true,
                    'name' => $filename
                ]);
                
                register_shutdown_function(function() use ($tmpFile) {
                    if (file_exists($tmpFile)) {
                        unlink($tmpFile);
                    }
                });
                
                return $response;
            }
            // If cache download failed, fall through to regenerate
        }
        
        // Generate PPT using shared method
        return $this->generateAndServePpt($slides, $report, $filename, $currentHash, $s3Service);
    }
    
    /**
     * Generate and serve PowerPoint file
     *
     * @param \Cake\ORM\ResultSet $slides Collection of report slides
     * @param \App\Model\Entity\Report $report Report entity
     * @param string $filename Download filename
     * @param string $currentHash Content hash for caching
     * @param \App\Lib\S3DocumentService $s3Service S3 service
     * @return \Cake\Http\Response
     */
    protected function generateAndServePpt($slides, $report, string $filename, string $currentHash, S3DocumentService $s3Service): Response
    {
        // Generate URLs for slide images
        foreach ($slides as $slide) {
            if ($slide->file_path) {
                $slide->image_url = $s3Service->getDownloadUrl($slide->file_path);
            }
            if ($slide->col1_image_path) {
                $slide->col1_image_url = $s3Service->getDownloadUrl($slide->col1_image_path);
            }
            if ($slide->col2_image_path) {
                $slide->col2_image_url = $s3Service->getDownloadUrl($slide->col2_image_path);
            }
            // Handle col3-col5 for multi-image slides
            foreach ([3, 4, 5] as $colNum) {
                $pathField = "col{$colNum}_image_path";
                if ($slide->{$pathField}) {
                    $slide->{"col{$colNum}_image_url"} = $s3Service->getDownloadUrl($slide->{$pathField});
                }
            }
        }
        
        // Create PowerPoint presentation
        $presentation = new \PhpOffice\PhpPresentation\PhpPresentation();
        $presentation->removeSlideByIndex(0);
        
        // Set slide layout to 16:9 widescreen
        $presentation->getLayout()->setDocumentLayout(
            \PhpOffice\PhpPresentation\DocumentLayout::LAYOUT_SCREEN_16X9,
            true
        );
        
        // Load PPT styles from configuration
        $pptStyles = unserialize(PPT_STYLES);
        
        // Get slide dimensions from config
        $slideWidth = $pptStyles['slide']['width'] ?? 960;
        $slideHeight = $pptStyles['slide']['height'] ?? 540;
        $margin = 15;
        $topMargin = 10;
        
        // Track temp files for cleanup
        $tempFiles = [];
        
        foreach ($slides as $index => $slide) {
            $pptSlide = $presentation->createSlide();
            
            // Set slide background to white
            $background = $pptSlide->getBackground();
            if ($background) {
                $background->setColor(new \PhpOffice\PhpPresentation\Style\Color('FFFFFFFF'));
            }
            
            $slideTitle = $slide->title ?: $slide->description ?: '';
            $layoutColumns = $slide->layout_columns ?? 1;
            
            // First slide (cover) gets special formatting
            if ($index === 0) {
                $this->renderCoverSlide($pptSlide, $slide, $pptStyles, $slideWidth, $slideHeight, $margin);
            } else {
                $tempFiles = array_merge(
                    $tempFiles,
                    $this->renderContentSlide($pptSlide, $slide, $pptStyles, $slideWidth, $slideHeight, $margin, $topMargin, $s3Service)
                );
            }
        }
        
        // Generate PowerPoint file
        $writer = new \PhpOffice\PhpPresentation\Writer\PowerPoint2007($presentation);
        $tmpFile = TMP . 'ppt_' . uniqid() . '.pptx';
        $writer->save($tmpFile);
        
        // Clean up temp image files
        foreach ($tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
        
        // Upload generated PPT to S3 for caching
        $s3Path = 'generated-ppts/report_' . $report->id . '_' . $currentHash . '.pptx';
        try {
            $uploadResult = $s3Service->uploadLocalFile(
                $tmpFile, 
                $s3Path, 
                'application/vnd.openxmlformats-officedocument.presentationml.presentation'
            );
            if ($uploadResult['success']) {
                $Reports = $this->fetchTable('Reports');
                $report->ppt_download_url = $s3Path;
                $report->ppt_hash = $currentHash;
                $report->ppt_generated_at = new \Cake\I18n\DateTime();
                $Reports->save($report);
            }
        } catch (\Exception $e) {
            \Cake\Log\Log::error('Failed to cache PPT to S3: ' . $e->getMessage());
        }
        
        // Send file to browser
        $response = $this->response->withFile($tmpFile, [
            'download' => true,
            'name' => $filename
        ]);
        
        register_shutdown_function(function() use ($tmpFile) {
            if (file_exists($tmpFile)) {
                unlink($tmpFile);
            }
        });
        
        return $response;
    }
    
    /**
     * Render cover slide
     */
    protected function renderCoverSlide($pptSlide, $slide, array $pptStyles, int $slideWidth, int $slideHeight, int $margin): void
    {
        $coverStyles = $pptStyles['cover'] ?? [];
        $coverHeadingSize = $coverStyles['heading_font_size'] ?? 24;
        $coverContentSize = $coverStyles['content_font_size'] ?? 14;
        $patientNameBold = $coverStyles['patient_name_bold'] ?? true;
        
        $slideTitle = $slide->title ?: $slide->description ?: '';
        $lines = explode("\n", $slide->description ?: $slideTitle);
        $heading = array_shift($lines);
        $content = implode("\n", array_slice($lines, 2));
        
        // Add heading - centered
        $headingShape = $pptSlide->createRichTextShape();
        $headingShape->setHeight(50);
        $headingShape->setWidth($slideWidth - ($margin * 2));
        $headingShape->setOffsetX($margin);
        $headingShape->setOffsetY(60);
        $headingShape->getActiveParagraph()->getAlignment()
            ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_CENTER);
        
        $headingRun = $headingShape->createTextRun($heading);
        $headingRun->getFont()
            ->setName('Calibri')
            ->setSize($coverHeadingSize)
            ->setBold(true)
            ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
        
        // Add content below heading
        if (!empty($content)) {
            $textShape = $pptSlide->createRichTextShape();
            $textShape->setHeight($slideHeight - 140);
            $textShape->setWidth($slideWidth - ($margin * 2));
            $textShape->setOffsetX($margin);
            $textShape->setOffsetY(120);
            $textShape->getActiveParagraph()->getAlignment()
                ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_CENTER);
            
            $contentLines = explode("\n", $content);
            foreach ($contentLines as $lineIdx => $line) {
                if ($lineIdx > 0) {
                    $textShape->createBreak();
                }
                
                if (strpos($line, 'Name:') !== false && $patientNameBold) {
                    $parts = explode(':', $line, 2);
                    $labelRun = $textShape->createTextRun($parts[0] . ': ');
                    $labelRun->getFont()
                        ->setName('Calibri')
                        ->setSize($coverContentSize)
                        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                    
                    if (isset($parts[1])) {
                        $valueRun = $textShape->createTextRun(trim($parts[1]));
                        $valueRun->getFont()
                            ->setName('Calibri')
                            ->setSize($coverContentSize)
                            ->setBold(true)
                            ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                    }
                } else {
                    $textRun = $textShape->createTextRun($line);
                    $textRun->getFont()
                        ->setName('Calibri')
                        ->setSize($coverContentSize)
                        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                }
            }
        }
    }
    
    /**
     * Render content slide
     *
     * @return array List of temp files to clean up
     */
    protected function renderContentSlide($pptSlide, $slide, array $pptStyles, int $slideWidth, int $slideHeight, int $margin, int $topMargin, S3DocumentService $s3Service): array
    {
        $tempFiles = [];
        $currentY = $topMargin;
        $slideTitle = $slide->title ?: $slide->description ?: '';
        $layoutColumns = $slide->layout_columns ?? 1;
        
        // Get slide config early - we need it for fallbacks
        $slideTypes = unserialize(PPT_REPORT_PAGES);
        $slideType = $slide->slide_type ?? 'custom';
        $slideConfig = $slideTypes[$slideType] ?? null;
        
        // Apply config fallbacks for slides created before template updates
        if ($slideConfig) {
            // Fallback layout_columns from config
            if (empty($slide->layout_columns) && isset($slideConfig['columns'])) {
                $layoutColumns = $slideConfig['columns'];
            }
            // Fallback subtitle from config
            if (empty($slide->subtitle) && !empty($slideConfig['subtitle'])) {
                $slide->subtitle = $slideConfig['subtitle'];
            }
            // Fallback col1_content from config (header_text or default_content)
            if (empty($slide->col1_content)) {
                if (isset($slideConfig['header_text']['content'])) {
                    $headerContent = $slideConfig['header_text']['content'];
                    $slide->col1_content = is_array($headerContent) ? implode("\n", $headerContent) : $headerContent;
                } elseif (isset($slideConfig['col1']['default_content'])) {
                    $slide->col1_content = $slideConfig['col1']['default_content'];
                } elseif (isset($slideConfig['default_sections'])) {
                    $slide->col1_content = json_encode($slideConfig['default_sections']);
                }
            }
            // Fallback col1_header / col2_header from config
            if (empty($slide->col1_header) && !empty($slideConfig['col1']['header'])) {
                $slide->col1_header = $slideConfig['col1']['header'];
            }
            if (empty($slide->col2_header) && !empty($slideConfig['col2']['header'])) {
                $slide->col2_header = $slideConfig['col2']['header'];
            }
            // Fallback legend_data from config
            if (empty($slide->legend_data) && isset($slideConfig['legend']['items'])) {
                $slide->legend_data = json_encode($slideConfig['legend']['items']);
            }
        }
        
        // Add title
        if (!empty($slideTitle)) {
            $titleFontSize = $pptStyles['title']['font_size'] ?? 24;
            $titleMarginBottom = $pptStyles['title']['margin_bottom'] ?? 8;
            $titleFontFamily = $pptStyles['title']['font_family'] ?? 'Calibri';
            
            $contentWidth = $slideWidth - ($margin * 2);
            $charsPerLine = (int)($contentWidth / ($titleFontSize * 0.5));
            $titleLines = max(1, ceil(strlen($slideTitle) / $charsPerLine));
            $titleLineHeight = $titleFontSize + 8;
            $titleHeight = (int)min($titleLines * $titleLineHeight, 70);
            
            $titleShape = $pptSlide->createRichTextShape();
            $titleShape->setHeight($titleHeight);
            $titleShape->setWidth($contentWidth);
            $titleShape->setOffsetX($margin);
            $titleShape->setOffsetY($currentY);
            
            $titleRun = $titleShape->createTextRun($slideTitle);
            $titleRun->getFont()
                ->setName($titleFontFamily)
                ->setSize($titleFontSize)
                ->setBold($pptStyles['title']['font_bold'] ?? true)
                ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF' . ($pptStyles['title']['font_color'] ?? '000000')));
            
            $currentY += $titleHeight + $titleMarginBottom;
        }
        
        // Add subtitle if present
        if (!empty($slide->subtitle)) {
            $subtitleFontSize = $pptStyles['subtitle']['font_size'] ?? 20;
            $subtitleMarginBottom = $pptStyles['subtitle']['margin_bottom'] ?? 8;
            $subtitleFontFamily = $pptStyles['subtitle']['font_family'] ?? 'Calibri';
            
            $subtitleText = '• ' . $slide->subtitle;
            $subtitleCharsPerLine = (int)(($slideWidth - ($margin * 2)) / ($subtitleFontSize * 0.5));
            $subtitleLines = max(1, ceil(strlen($subtitleText) / $subtitleCharsPerLine));
            $subtitleLineHeight = $subtitleFontSize + 6;
            $subtitleHeight = (int)min($subtitleLines * $subtitleLineHeight, 60);
            
            $subtitleShape = $pptSlide->createRichTextShape();
            $subtitleShape->setHeight($subtitleHeight);
            $subtitleShape->setWidth($slideWidth - ($margin * 2));
            $subtitleShape->setOffsetX($margin);
            $subtitleShape->setOffsetY($currentY);
            
            $subtitleRun = $subtitleShape->createTextRun($subtitleText);
            $subtitleRun->getFont()
                ->setName($subtitleFontFamily)
                ->setSize($subtitleFontSize)
                ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF' . ($pptStyles['subtitle']['font_color'] ?? '000000')));
            
            $currentY += $subtitleHeight + $subtitleMarginBottom;
        }
        
        // Calculate available content area
        $contentStartY = (int)$currentY;
        $contentEndY = (int)($slideHeight - 15);
        $availableHeight = (int)($contentEndY - $contentStartY);
        
        // Handle two-column layout
        if ($layoutColumns === 2) {
            $tempFiles = array_merge($tempFiles, $this->renderTwoColumnLayout(
                $pptSlide, $slide, $pptStyles, $slideWidth, $margin, $contentStartY, $availableHeight, $slideConfig, $s3Service
            ));
        } elseif ($slide->slide_type === 'eeg_meg_discharge' || ($slideConfig['layout'] ?? '') === 'multi_image_with_titles') {
            $tempFiles = array_merge($tempFiles, $this->renderMultiImageLayout(
                $pptSlide, $slide, $pptStyles, $slideWidth, $slideHeight, $contentStartY, $slideConfig, $s3Service
            ));
        } else {
            $tempFiles = array_merge($tempFiles, $this->renderSingleColumnLayout(
                $pptSlide, $slide, $pptStyles, $slideWidth, $margin, $contentStartY, $availableHeight
            ));
        }
        
        // Add legend if present
        $legendItems = $slide->getLegendItems();
        if (!empty($legendItems)) {
            $this->renderLegend($pptSlide, $legendItems, $margin, $slideHeight);
        }
        
        return $tempFiles;
    }
    
    /**
     * Render two-column layout
     */
    protected function renderTwoColumnLayout($pptSlide, $slide, array $pptStyles, int $slideWidth, int $margin, int $contentStartY, int $availableHeight, ?array $slideConfig, S3DocumentService $s3Service): array
    {
        $tempFiles = [];
        $contentWidth = $slideWidth - ($margin * 2);
        $columnGap = 15;
        $layout = $slideConfig['layout'] ?? 'two_column_images';
        
        // Get layout configuration for column widths
        $pptLayouts = unserialize(PPT_LAYOUTS);
        $layoutConfig = $pptLayouts[$layout] ?? [];
        
        $col1WidthPercent = $layoutConfig['col1_width_percent'] ?? 50;
        $col2WidthPercent = $layoutConfig['col2_width_percent'] ?? 50;
        
        $col1Width = (int)(($contentWidth - $columnGap) * $col1WidthPercent / 100);
        $col2Width = (int)(($contentWidth - $columnGap) * $col2WidthPercent / 100);
        
        $col1X = $margin;
        $col2X = $margin + $col1Width + $columnGap;
        
        // For text_header_two_images layout, render col1_content as full-width header text
        $isTextHeaderLayout = ($layout === 'text_header_two_images');
        $hasImages = !empty($slide->col1_image_url) || !empty($slide->col2_image_url);
        $textHeaderHeight = 0;
        
        // Show header text for text_header_two_images layout - always show content, not just when images exist
        if ($isTextHeaderLayout && !empty($slide->col1_content)) {
            // Render header text spanning full width
            $textFontSize = $pptStyles['content']['font_size'] ?? 14;
            
            // Split content by newlines for multiple bullet points
            $contentLines = preg_split('/\r\n|\r|\n/', $slide->col1_content);
            $textHeaderHeight = max(70, count($contentLines) * 22); // Adjust height based on line count
            
            $headerTextShape = $pptSlide->createRichTextShape();
            $headerTextShape->setHeight($textHeaderHeight);
            $headerTextShape->setWidth($contentWidth);
            $headerTextShape->setOffsetX($margin);
            $headerTextShape->setOffsetY($contentStartY);
            
            // Render each line as a bullet point
            $isFirst = true;
            foreach ($contentLines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                if (!$isFirst) {
                    $headerTextShape->createBreak();
                }
                $isFirst = false;
                
                $textRun = $headerTextShape->createTextRun('• ' . strip_tags($line));
                $textRun->getFont()
                    ->setName('Calibri')
                    ->setSize($textFontSize)
                    ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
            }
            
            $contentStartY += $textHeaderHeight + 10;
            $availableHeight -= $textHeaderHeight + 10;
        }
        
        // Calculate header height
        $col1HeaderText = strip_tags($slide->col1_header ?? '');
        $col2HeaderText = strip_tags($slide->col2_header ?? '');
        $columnHeaderMarginBottom = $pptStyles['column_header']['margin_bottom'] ?? 10;
        $columnHeaderFontSize = $pptStyles['column_header']['font_size'] ?? 14;
        
        $hasHeaders = !empty($col1HeaderText) || !empty($col2HeaderText);
        $headerHeight = 0;
        
        if ($hasHeaders) {
            $charsPerLineHeader = (int)($col1Width / ($columnHeaderFontSize * 0.5));
            $col1Lines = !empty($col1HeaderText) ? (int)max(1, ceil(strlen($col1HeaderText) / $charsPerLineHeader)) : 0;
            $col2Lines = !empty($col2HeaderText) ? (int)max(1, ceil(strlen($col2HeaderText) / $charsPerLineHeader)) : 0;
            $maxHeaderLines = (int)max($col1Lines, $col2Lines);
            $headerLineHeight = $columnHeaderFontSize + 6;
            $headerHeight = (int)min(80, $maxHeaderLines * $headerLineHeight + 5);
        }
        
        // Render column headers
        if (!empty($col1HeaderText)) {
            $this->renderColumnHeader($pptSlide, $col1HeaderText, $col1X, $col1Width, $contentStartY, $headerHeight, $pptStyles);
        }
        if (!empty($col2HeaderText)) {
            $this->renderColumnHeader($pptSlide, $col2HeaderText, $col2X, $col2Width, $contentStartY, $headerHeight, $pptStyles);
        }
        
        // Image area starts after headers
        if ($hasHeaders) {
            $imageStartY = (int)($contentStartY + $headerHeight + $columnHeaderMarginBottom);
            $imageMaxHeight = (int)($availableHeight - $headerHeight - $columnHeaderMarginBottom);
        } else {
            $imageStartY = (int)$contentStartY;
            $imageMaxHeight = (int)$availableHeight;
        }
        
        // Render column 1 content
        if (!empty($slide->col1_image_url)) {
            $tempFile = $this->renderColumnImage($pptSlide, $slide->col1_image_url, $col1X, $col1Width, $imageStartY, $imageMaxHeight);
            if ($tempFile) $tempFiles[] = $tempFile;
        } elseif (!empty($slide->col1_content) && !$isTextHeaderLayout) {
            // Only render col1_content here if not already shown as header text
            $this->renderColumnText($pptSlide, $slide->col1_content, $col1X, $col1Width, $imageStartY, $imageMaxHeight, $pptStyles, $layout);
        }
        
        // Check if this slide has stacked images (e.g., functional_mapping_motor)
        $isStacked = !empty($slideConfig['stacked_images']) && !empty($slideConfig['stacked_columns']);
        
        if ($isStacked) {
            // Render stacked images: col2 on top, col3 on bottom, both in the col2 area
            $stackGap = 8;
            $halfHeight = (int)(($imageMaxHeight - $stackGap) / 2);
            
            // Top image (col2)
            if (!empty($slide->col2_image_url)) {
                $tempFile = $this->renderColumnImage($pptSlide, $slide->col2_image_url, $col2X, $col2Width, $imageStartY, $halfHeight);
                if ($tempFile) $tempFiles[] = $tempFile;
            }
            
            // Bottom image (col3)
            $bottomY = $imageStartY + $halfHeight + $stackGap;
            if (!empty($slide->col3_image_url)) {
                $tempFile = $this->renderColumnImage($pptSlide, $slide->col3_image_url, $col2X, $col2Width, $bottomY, $halfHeight);
                if ($tempFile) $tempFiles[] = $tempFile;
            }
        } else {
            // Render column 2 content (standard)
            if (!empty($slide->col2_image_url)) {
                $tempFile = $this->renderColumnImage($pptSlide, $slide->col2_image_url, $col2X, $col2Width, $imageStartY, $imageMaxHeight);
                if ($tempFile) $tempFiles[] = $tempFile;
            } elseif (!empty($slide->col2_content)) {
                $this->renderColumnText($pptSlide, $slide->col2_content, $col2X, $col2Width, $imageStartY, $imageMaxHeight, $pptStyles, $layout);
            }
        }
        
        return $tempFiles;
    }
    
    /**
     * Render multi-image layout
     */
    protected function renderMultiImageLayout($pptSlide, $slide, array $pptStyles, int $slideWidth, int $slideHeight, int $contentStartY, ?array $slideConfig, S3DocumentService $s3Service): array
    {
        $tempFiles = [];
        $maxImages = $slideConfig['max_images'] ?? 5;
        $defaultTitles = $slideConfig['default_image_titles'] ?? [];
        $imageColumns = ['col1', 'col2', 'col3', 'col4', 'col5'];
        
        // Collect all images
        $imagesToRender = [];
        foreach (range(0, $maxImages - 1) as $i) {
            $colName = $imageColumns[$i];
            $imagePathField = "{$colName}_image_path";
            $headerField = "{$colName}_header";
            
            if (!empty($slide->{$imagePathField})) {
                $imageUrl = $s3Service->getDownloadUrl($slide->{$imagePathField});
                $title = $slide->{$headerField} ?? $defaultTitles[$i] ?? 'Discharge ' . ($i + 1);
                $imagesToRender[] = [
                    'url' => $imageUrl,
                    'title' => $title,
                ];
            }
        }
        
        if (empty($imagesToRender)) {
            return $tempFiles;
        }
        
        $imageCount = count($imagesToRender);
        $totalWidth = $slideWidth;
        $columnWidth = (int)($totalWidth / $imageCount);
        
        $multiImageTitleStyles = $pptStyles['multi_image_title'] ?? [];
        $titleHeight = 20;
        $titleFontSize = $multiImageTitleStyles['font_size'] ?? 14;
        $titleFontBold = $multiImageTitleStyles['font_bold'] ?? true;
        
        $imageAreaStartY = $contentStartY + $titleHeight + 3;
        $imageAreaHeight = $slideHeight - $imageAreaStartY;
        
        foreach ($imagesToRender as $index => $imageData) {
            $columnX = (int)($index * $columnWidth);
            
            // Add title
            if (!empty($imageData['title'])) {
                $titleShape = $pptSlide->createRichTextShape();
                $titleShape->setHeight($titleHeight);
                $titleShape->setWidth($columnWidth);
                $titleShape->setOffsetX($columnX);
                $titleShape->setOffsetY($contentStartY);
                $titleShape->getActiveParagraph()->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_CENTER);
                
                $titleRun = $titleShape->createTextRun($imageData['title']);
                $titleRun->getFont()
                    ->setName('Calibri')
                    ->setSize($titleFontSize)
                    ->setBold($titleFontBold)
                    ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
            }
            
            // Render image
            $tempImage = $this->downloadTempImageForPpt($imageData['url']);
            if ($tempImage && file_exists($tempImage)) {
                $tempFiles[] = $tempImage;
                list($imgW, $imgH) = getimagesize($tempImage);
                
                $scale = min($columnWidth / $imgW, $imageAreaHeight / $imgH);
                $scaledW = (int)($imgW * $scale);
                $scaledH = (int)($imgH * $scale);
                
                $imgX = $columnX + (int)(($columnWidth - $scaledW) / 2);
                $imgY = $imageAreaStartY;
                
                $shape = $pptSlide->createDrawingShape();
                $shape->setPath($tempImage);
                $shape->setWidth($scaledW);
                $shape->setHeight($scaledH);
                $shape->setOffsetX($imgX);
                $shape->setOffsetY($imgY);
            }
        }
        
        return $tempFiles;
    }
    
    /**
     * Render single column layout
     */
    protected function renderSingleColumnLayout($pptSlide, $slide, array $pptStyles, int $slideWidth, int $margin, int $contentStartY, int $availableHeight): array
    {
        $tempFiles = [];
        $contentWidth = $slideWidth - ($margin * 2);
        
        $imageUrl = $slide->col1_image_url ?? $slide->image_url ?? null;
        if (!empty($imageUrl)) {
            $tempImage = $this->downloadTempImageForPpt($imageUrl);
            if ($tempImage && file_exists($tempImage)) {
                $tempFiles[] = $tempImage;
                list($imgW, $imgH) = getimagesize($tempImage);
                
                $maxImgHeight = (int)$availableHeight;
                $scale = min($contentWidth / $imgW, $maxImgHeight / $imgH);
                $scaledW = (int)($imgW * $scale);
                $scaledH = (int)($imgH * $scale);
                
                $imgX = $margin + (int)(($contentWidth - $scaledW) / 2);
                $imgY = $contentStartY + (int)(($availableHeight - $scaledH) / 2);
                
                $shape = $pptSlide->createDrawingShape();
                $shape->setPath($tempImage);
                $shape->setWidth($scaledW);
                $shape->setHeight($scaledH);
                $shape->setOffsetX($imgX);
                $shape->setOffsetY($imgY);
            }
        }
        
        // Add text content if present
        if (!empty($slide->col1_content)) {
            $textY = (int)(!empty($imageUrl) ? $contentStartY + $availableHeight - 80 : $contentStartY);
            $textHeight = (int)(!empty($imageUrl) ? 75 : $availableHeight);
            
            $textShape = $pptSlide->createRichTextShape();
            $textShape->setHeight($textHeight);
            $textShape->setWidth($contentWidth);
            $textShape->setOffsetX($margin);
            $textShape->setOffsetY($textY);
            
            $bulletStyles = $pptStyles['structured_bullets'] ?? [];
            $structuredFontSize = $bulletStyles['font_size'] ?? 14;
            
            $this->addStructuredContentToShapeForPpt($textShape, $slide->col1_content, $structuredFontSize);
        }
        
        return $tempFiles;
    }
    
    /**
     * Render column header
     */
    protected function renderColumnHeader($pptSlide, string $headerText, int $x, int $width, int $y, int $height, array $pptStyles): void
    {
        $fontFamily = $pptStyles['column_header']['font_family'] ?? 'Calibri';
        $fontSize = $pptStyles['column_header']['font_size'] ?? 14;
        $fontBold = $pptStyles['column_header']['font_bold'] ?? false;
        $fontColor = $pptStyles['column_header']['font_color'] ?? '000000';
        
        $headerShape = $pptSlide->createRichTextShape();
        $headerShape->setHeight($height);
        $headerShape->setWidth($width);
        $headerShape->setOffsetX($x);
        $headerShape->setOffsetY($y);
        $headerShape->getActiveParagraph()->getAlignment()
            ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_LEFT);
        
        $headerRun = $headerShape->createTextRun($headerText);
        $headerRun->getFont()
            ->setName($fontFamily)
            ->setSize($fontSize)
            ->setBold($fontBold)
            ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF' . $fontColor));
    }
    
    /**
     * Render column image
     */
    protected function renderColumnImage($pptSlide, string $imageUrl, int $x, int $width, int $y, int $maxHeight): ?string
    {
        $tempImage = $this->downloadTempImageForPpt($imageUrl);
        if ($tempImage && file_exists($tempImage)) {
            list($imgW, $imgH) = getimagesize($tempImage);
            
            $scale = min($width / $imgW, $maxHeight / $imgH);
            $scaledW = (int)($imgW * $scale);
            $scaledH = (int)($imgH * $scale);
            
            $imgX = $x + (int)(($width - $scaledW) / 2);
            $imgY = $y + (int)(($maxHeight - $scaledH) / 2);
            
            $shape = $pptSlide->createDrawingShape();
            $shape->setPath($tempImage);
            $shape->setWidth($scaledW);
            $shape->setHeight($scaledH);
            $shape->setOffsetX($imgX);
            $shape->setOffsetY($imgY);
            
            return $tempImage;
        }
        
        return null;
    }
    
    /**
     * Render column text
     */
    protected function renderColumnText($pptSlide, string $content, int $x, int $width, int $y, int $height, array $pptStyles, string $layout): void
    {
        $contentFontSize = ($layout === 'text_and_image') 
            ? ($pptStyles['text_and_image_content']['font_size'] ?? 14)
            : ($pptStyles['content']['font_size'] ?? 14);
        
        $textShape = $pptSlide->createRichTextShape();
        $textShape->setHeight($height);
        $textShape->setWidth($width);
        $textShape->setOffsetX($x);
        $textShape->setOffsetY($y);
        
        $this->addStructuredContentToShapeForPpt($textShape, $content, $contentFontSize);
    }
    
    /**
     * Render legend
     */
    protected function renderLegend($pptSlide, array $legendItems, int $margin, int $slideHeight): void
    {
        $legendY = $slideHeight - 22;
        $legendX = $margin;
        $legendItemWidth = 120;
        
        foreach ($legendItems as $item) {
            if (!empty($item['label'])) {
                $legendShape = $pptSlide->createRichTextShape();
                $legendShape->setHeight(18);
                $legendShape->setWidth($legendItemWidth);
                $legendShape->setOffsetX($legendX);
                $legendShape->setOffsetY($legendY);
                
                $legendRun = $legendShape->createTextRun('■ ' . $item['label']);
                $legendRun->getFont()
                    ->setSize(9)
                    ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF' . ltrim($item['color'] ?? '000000', '#')));
                
                $legendX += $legendItemWidth;
            }
        }
    }
    
    /**
     * Download image from URL to temporary file
     */
    protected function downloadTempImageForPpt(string $imageUrl): ?string
    {
        if (empty($imageUrl)) {
            return null;
        }
        
        try {
            if (strpos($imageUrl, 'http') === 0) {
                $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $tempImage = TMP . 'ppt_img_' . uniqid() . '.' . $ext;
                
                $context = stream_context_create([
                    'http' => ['timeout' => 60],
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
                ]);
                $imageContent = @file_get_contents($imageUrl, false, $context);
                if ($imageContent !== false) {
                    file_put_contents($tempImage, $imageContent);
                    return $tempImage;
                }
            } else {
                $localPath = WWW_ROOT . ltrim($imageUrl, '/');
                if (file_exists($localPath)) {
                    return $localPath;
                }
            }
        } catch (\Exception $e) {
            \Cake\Log\Log::error('Failed to download image for PPT: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Add structured bullets content to PPT shape
     */
    protected function addStructuredContentToShapeForPpt($textShape, string $content, int $fontSize = 14): void
    {
        if (empty($content)) {
            return;
        }
        
        $pptStyles = unserialize(PPT_STYLES);
        $bulletStyles = $pptStyles['structured_bullets'] ?? [];
        $lineSpacing = $bulletStyles['line_spacing'] ?? 100;
        $headingFontSize = $bulletStyles['heading_font_size'] ?? ($fontSize + 2);
        
        $decoded = json_decode($content, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $isFirst = true;
            
            foreach ($decoded as $section) {
                $heading = $section['heading'] ?? '';
                
                if ($heading) {
                    if (!$isFirst) {
                        $textShape->createBreak();
                    }
                    $paragraph = $textShape->createParagraph();
                    $paragraph->setLineSpacing($lineSpacing);
                    $paragraph->getAlignment()->setMarginLeft(0);
                    $headingRun = $paragraph->createTextRun($heading);
                    $headingRun->getFont()
                        ->setSize($headingFontSize)
                        ->setBold(true)
                        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                    $isFirst = false;
                }
                
                foreach ($section['items'] ?? [] as $item) {
                    $title = $item['title'] ?? '';
                    
                    if ($title) {
                        $paragraph = $textShape->createParagraph();
                        $paragraph->setLineSpacing($lineSpacing);
                        $paragraph->getAlignment()->setMarginLeft(20);
                        $titleRun = $paragraph->createTextRun('• ' . $title);
                        $titleRun->getFont()
                            ->setSize($fontSize)
                            ->setBold(false)
                            ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
                    }
                    
                    foreach ($item['subitems'] ?? [] as $subitem) {
                        $paragraph = $textShape->createParagraph();
                        $paragraph->setLineSpacing($lineSpacing);
                        $paragraph->getAlignment()->setMarginLeft(40);
                        $subitemRun = $paragraph->createTextRun('○ ' . $subitem);
                        $subitemRun->getFont()
                            ->setSize($fontSize - 1)
                            ->setBold(false)
                            ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF333333'));
                    }
                }
            }
        } else {
            $textRun = $textShape->createTextRun($content);
            $textRun->getFont()
                ->setSize($fontSize)
                ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
        }
    }
    
    /**
     * Calculate hash for report slides to detect changes
     */
    protected function calculateReportHashForPpt($slides): string
    {
        $hashData = [];
        
        foreach ($slides as $slide) {
            $slideData = [
                'id' => $slide->id,
                'slide_type' => $slide->slide_type,
                'title' => $slide->title,
                'description' => $slide->description,
                'subtitle' => $slide->subtitle,
                'col1_content' => $slide->col1_content,
                'col2_content' => $slide->col2_content,
                'col1_header' => $slide->col1_header,
                'col2_header' => $slide->col2_header,
                'col3_header' => $slide->col3_header,
                'col4_header' => $slide->col4_header,
                'col5_header' => $slide->col5_header,
                'col1_image_path' => $slide->col1_image_path,
                'col2_image_path' => $slide->col2_image_path,
                'col3_image_path' => $slide->col3_image_path,
                'col4_image_path' => $slide->col4_image_path,
                'col5_image_path' => $slide->col5_image_path,
                'file_path' => $slide->file_path,
                'legend_items' => $slide->legend_items,
                'modified' => $slide->modified ? $slide->modified->format('Y-m-d H:i:s') : null,
            ];
            $hashData[] = $slideData;
        }
        
        return hash('sha256', json_encode($hashData));
    }
}
