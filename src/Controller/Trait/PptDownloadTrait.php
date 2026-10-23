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
        $report = $Reports->get($reportId, contain: ['Cases']);
        
        // Get all slides for this report
        $ReportSlides = $this->fetchTable('ReportSlides');
        $slides = $ReportSlides->find()
            ->where(['report_id' => $reportId])
            ->orderBy(['slide_order' => 'ASC'])
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
            
            if ($slide->slide_type === 'cover_page') {
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

        // PhpPresentation 1.2.0 triggers PHP 8.5 "null as array offset" deprecations while writing
        // (HashTable::add); in debug mode they are printed into the response and corrupt the download.
        // Fixed upstream on master - remove this once composer has a phppresentation release after 1.2.0.
        $errorLevel = error_reporting(error_reporting() & ~E_DEPRECATED);
        try {
            $writer->save($tmpFile);
        } finally {
            error_reporting($errorLevel);
        }
        
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
        
        // Doctor-defined grid layout (the only content slide type)
        if ($slide->isCustomLayout()) {
            $tempFiles = array_merge($tempFiles, $this->renderGridLayout(
                $pptSlide, $slide, $pptStyles, $slideWidth, $margin, $contentStartY, $availableHeight, $s3Service
            ));
        }
        
        return $tempFiles;
    }
    
    /**
     * Render doctor-defined grid layout: rows of columns, each column with an optional
     * header and one or more stacked boxes (image / text / bullets / heading)
     */
    protected function renderGridLayout($pptSlide, $slide, array $pptStyles, int $slideWidth, int $margin, int $contentStartY, int $availableHeight, S3DocumentService $s3Service): array
    {
        $tempFiles = [];
        $rows = array_values(array_filter($slide->getLayoutData()['rows'], fn($row) => !empty($row['columns'])));
        if (empty($rows)) {
            return $tempFiles;
        }
        
        $contentWidth = $slideWidth - ($margin * 2);
        $columnGap = 15;
        $rowGap = 10;
        $stackGap = 8;
        $headerFontSize = $pptStyles['column_header']['font_size'] ?? 14;
        $headerMarginBottom = $pptStyles['column_header']['margin_bottom'] ?? 8;
        $headerLineHeight = $headerFontSize + 6;
        
        // Header text block height for a given width (max 3 lines)
        $headerHeightFor = function (string $text, int $width) use ($headerFontSize, $headerLineHeight): int {
            $charsPerLine = max(1, (int)($width / ($headerFontSize * 0.5)));
            $lines = min(3, (int)ceil(strlen($text) / $charsPerLine));
            return $lines * $headerLineHeight + 5;
        };
        
        $totalRowWeight = array_sum(array_map(fn($row) => max(0.1, (float)($row['height'] ?? 1)), $rows));
        $rowsHeight = $availableHeight - $rowGap * (count($rows) - 1);
        $rowY = $contentStartY;
        
        foreach ($rows as $row) {
            $columns = $row['columns'];
            $rowHeight = (int)($rowsHeight * max(0.1, (float)($row['height'] ?? 1)) / $totalRowWeight);
            $totalColumnWeight = array_sum(array_map(fn($column) => max(0.1, (float)($column['width'] ?? 1)), $columns));
            $columnsWidth = $contentWidth - $columnGap * (count($columns) - 1);
            
            $columnWidths = [];
            foreach ($columns as $c => $column) {
                $columnWidths[$c] = (int)($columnsWidth * max(0.1, (float)($column['width'] ?? 1)) / $totalColumnWeight);
            }
            
            // One column-header band per row so column bodies stay top-aligned
            $headerHeight = 0;
            foreach ($columns as $c => $column) {
                if (!empty($column['header'])) {
                    $headerHeight = max($headerHeight, $headerHeightFor((string)$column['header'], $columnWidths[$c]));
                }
            }
            $headerHeight = (int)min($rowHeight / 3, $headerHeight);
            $bodyY = $headerHeight > 0 ? $rowY + $headerHeight + $headerMarginBottom : $rowY;
            $bodyHeight = (int)max(20, $rowHeight - ($bodyY - $rowY));
            
            $columnX = $margin;
            foreach ($columns as $c => $column) {
                $columnWidth = $columnWidths[$c];
                
                if (!empty($column['header'])) {
                    $this->renderColumnHeader($pptSlide, (string)$column['header'], $columnX, $columnWidth, (int)$rowY, $headerHeight, $pptStyles);
                }
                
                // Stacked boxes share the column body equally
                $boxes = $column['boxes'] ?? [];
                $boxCount = max(1, count($boxes));
                $boxHeight = (int)(($bodyHeight - $stackGap * ($boxCount - 1)) / $boxCount);
                $boxY = $bodyY;
                
                foreach ($boxes as $box) {
                    $contentY = $boxY;
                    $contentHeight = $boxHeight;
                    
                    if (!empty($box['header'])) {
                        $boxHeaderHeight = (int)min($boxHeight / 3, $headerHeightFor((string)$box['header'], $columnWidth));
                        $this->renderColumnHeader($pptSlide, (string)$box['header'], $columnX, $columnWidth, (int)$boxY, $boxHeaderHeight, $pptStyles);
                        $contentY += $boxHeaderHeight;
                        $contentHeight -= $boxHeaderHeight;
                    }
                    
                    if (($box['type'] ?? 'image') === 'image') {
                        if (!empty($box['image_path'])) {
                            $tempFile = $this->renderColumnImage($pptSlide, $s3Service->getDownloadUrl($box['image_path']), $columnX, $columnWidth, (int)$contentY, (int)max(10, $contentHeight));
                            if ($tempFile) $tempFiles[] = $tempFile;
                        }
                    } elseif (!empty($box['text'])) {
                        $this->renderGridCellText($pptSlide, $box, $columnX, $columnWidth, (int)$contentY, (int)max(10, $contentHeight), $pptStyles);
                    }
                    
                    $boxY += $boxHeight + $stackGap;
                }
                
                $columnX += $columnWidth + $columnGap;
            }
            
            $rowY += $rowHeight + $rowGap;
        }
        
        return $tempFiles;
    }
    
    /**
     * Render grid cell text - one paragraph per line, prefixed with a bullet for 'bullets' cells,
     * bold and larger for 'heading' cells
     */
    protected function renderGridCellText($pptSlide, array $cell, int $x, int $width, int $y, int $height, array $pptStyles): void
    {
        $fontFamily = $pptStyles['content']['font_family'] ?? 'Calibri';
        $fontSize = $pptStyles['content']['font_size'] ?? 14;
        $isBullets = ($cell['type'] ?? '') === 'bullets';
        $isHeading = ($cell['type'] ?? '') === 'heading';
        if ($isHeading) {
            $fontSize = $pptStyles['subtitle']['font_size'] ?? 20;
        }
        
        $textShape = $pptSlide->createRichTextShape();
        $textShape->setHeight($height);
        $textShape->setWidth($width);
        $textShape->setOffsetX($x);
        $textShape->setOffsetY($y);
        
        $isFirst = true;
        foreach (preg_split('/\r\n|\r|\n/', (string)$cell['text']) as $line) {
            $line = trim($line);
            if ($line === '' && $isBullets) {
                continue;
            }
            
            $paragraph = $isFirst ? $textShape->getActiveParagraph() : $textShape->createParagraph();
            $isFirst = false;
            
            $textRun = $paragraph->createTextRun($isBullets ? '• ' . $line : $line);
            $textRun->getFont()
                ->setName($fontFamily)
                ->setSize($fontSize)
                ->setBold($isHeading)
                ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF000000'));
        }
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
     * Calculate hash for report slides to detect changes
     */
    protected function calculateReportHashForPpt($slides): string
    {
        $hashData = [];
        
        foreach ($slides as $slide) {
            $slideData = [
                'id' => $slide->id,
                'slide_order' => $slide->slide_order,
                'slide_type' => $slide->slide_type,
                'title' => $slide->title,
                'description' => $slide->description,
                'subtitle' => $slide->subtitle,
                'layout_data' => $slide->layout_data,
                'modified' => $slide->modified ? $slide->modified->format('Y-m-d H:i:s') : null,
            ];
            $hashData[] = $slideData;
        }
        
        return hash('sha256', json_encode($hashData));
    }
}
