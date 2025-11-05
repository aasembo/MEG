<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Report;
use Cake\Core\Configure;

/**
 * Report Export Service
 * Handles exporting reports to multiple formats (PDF, DOCX, RTF, HTML, TXT)
 */
class ReportExportService
{
    /**
     * Export report to specified format
     *
     * @param \App\Model\Entity\Report $report Report entity
     * @param array $reportData Decoded report_data JSON
     * @param string $format Export format (pdf, docx, rtf, html, txt)
     * @return array ['content' => file content, 'filename' => filename, 'mimeType' => MIME type]
     * @throws \Exception When format is not supported
     */
    public function export(Report $report, array $reportData, string $format = 'pdf'): array
    {
        $format = strtolower($format);
        
        switch ($format) {
            case 'pdf':
                return $this->exportToPdf($report, $reportData);
            case 'docx':
                return $this->exportToDocx($report, $reportData);
            case 'rtf':
                return $this->exportToRtf($report, $reportData);
            case 'html':
                return $this->exportToHtml($report, $reportData);
            case 'txt':
                return $this->exportToTxt($report, $reportData);
            default:
                throw new \Exception("Unsupported export format: {$format}");
        }
    }

    /**
     * Export to PDF using DomPDF
     */
    protected function exportToPdf(Report $report, array $reportData): array
    {
        // Check if DomPDF is available
        if (!class_exists('\Dompdf\Dompdf')) {
            throw new \Exception('DomPDF library is not installed. Run: composer require dompdf/dompdf');
        }

        $html = $this->generateHtmlContent($report, $reportData, true);
        
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = $this->generateFilename($report, 'pdf');
        
        return [
            'content' => $dompdf->output(),
            'filename' => $filename,
            'mimeType' => 'application/pdf'
        ];
    }

    /**
     * Export to DOCX using PHPWord
     */
    protected function exportToDocx(Report $report, array $reportData): array
    {
        // Check if PHPWord is available
        if (!class_exists('\PhpOffice\PhpWord\PhpWord')) {
            throw new \Exception('PHPWord library is not installed. Run: composer require phpoffice/phpword');
        }

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        
        // Set document properties
        $properties = $phpWord->getDocInfo();
        $properties->setCreator('MEG Diagnostic System');
        $properties->setTitle('MEG Diagnostic Report - Case ' . $report->case_id);
        
        $section = $phpWord->addSection([
            'marginTop' => 1440,    // 1 inch
            'marginRight' => 1440,  // 1 inch  
            'marginBottom' => 1440, // 1 inch
            'marginLeft' => 1440,   // 1 inch
        ]);
        
        // Check if we have the new single-content structure
        if (isset($reportData['content']) && !isset($reportData['patient_information'])) {
            // New single-content structure - use the same HTML generation as PDF
            $this->generateWordFromHtml($section, $report, $reportData);
        } else {
            // Legacy multi-section structure
            // Title
            $section->addText(
                'MEG DIAGNOSTIC REPORT',
                ['bold' => true, 'size' => 18, 'color' => '1F497D', 'name' => 'Times New Roman'],
                ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
            );
            $section->addTextBreak(2);
            
            // Case Information
            $this->addDocxTable($section, [
                ['Case ID:', $report->case_id],
                ['Hospital:', $report->hospital->name ?? ''],
                ['Report Date:', $report->created->format('F d, Y')],
                ['Status:', ucfirst($report->status)],
            ]);
            $section->addTextBreak(1);
            
            // Report Sections
            $sections = [
                'Patient Information' => $reportData['patient_information'] ?? '',
                'Clinical Indication' => $reportData['clinical_indication'] ?? '',
                'Procedure Performed' => $reportData['procedure_performed'] ?? '',
                'Technical Parameters' => $reportData['technical_parameters'] ?? '',
                'Findings' => $reportData['findings'] ?? '',
                'Conclusion' => $reportData['conclusion'] ?? '',
                'Recommendations' => $reportData['recommendations'] ?? '',
            ];
            
            foreach ($sections as $title => $content) {
                if (!empty($content)) {
                    $section->addText(
                        strtoupper($title),
                        ['bold' => true, 'size' => 14, 'color' => '1F497D', 'name' => 'Times New Roman']
                    );
                    $section->addTextBreak(1);
                    
                    // Convert HTML content to Word formatting
                    $this->convertHtmlToWord($section, $content);
                    $section->addTextBreak(1);
                }
            }
            
            // Confidence Score
            if ($report->confidence_score) {
                $section->addText(
                    'Confidence Score: ' . $report->confidence_score . '%',
                    ['bold' => true, 'size' => 11, 'name' => 'Times New Roman']
                );
            }
        }
        
        // Save to temporary location
        $tempFile = tempnam(sys_get_temp_dir(), 'report_') . '.docx';
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        
        $content = file_get_contents($tempFile);
        unlink($tempFile);
        
        $filename = $this->generateFilename($report, 'docx');
        
        return [
            'content' => $content,
            'filename' => $filename,
            'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
    }
    
    /**
     * Generate Word document using the same HTML as PDF
     */
    protected function generateWordFromHtml($section, Report $report, array $reportData): void
    {
        // Instead of HTML conversion, build the Word document directly using the same structure as PDF
        $this->generateWordDocumentDirectly($section, $report, $reportData);
    }
    
    /**
     * Generate Word document directly matching PDF structure
     */
    protected function generateWordDocumentDirectly($section, Report $report, array $reportData): void
    {
        // Check if we have the new single-content structure or old multi-section structure
        if (isset($reportData['content']) && !isset($reportData['patient_information'])) {
            // New single-content structure - use the rich text content
            $this->processRichContentForWord($section, $reportData['content']);
        } else {
            // Legacy multi-section structure - build exactly like PDF
            $this->buildLegacyWordStructure($section, $report, $reportData);
        }
    }
    
    /**
     * Process rich content for Word document
     */
    protected function processRichContentForWord($section, string $content): void
    {
        // Clean HTML and convert to Word using existing methods
        $cleanContent = $this->cleanHtmlForWord($content);
        $this->convertPdfHtmlToWord($section, $cleanContent);
    }
    
    /**
     * Build legacy Word structure matching PDF exactly
     */
    protected function buildLegacyWordStructure($section, Report $report, array $reportData): void
    {
        // Header - exact match to PDF
        $section->addText('MEG DIAGNOSTIC REPORT', [
            'bold' => true,
            'size' => 24,
            'color' => '1F497D',
            'name' => 'Times New Roman',
            'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'spaceAfter' => 300,
            'borderBottomSize' => 18,
            'borderBottomColor' => '1F497D'
        ]);
        
        $section->addTextBreak();
        
        // Info table - exact match to PDF
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => 'dddddd',
            'cellMargin' => 80,
            'width' => 5000,
            'unit' => \PhpOffice\PhpWord\Style\Table::WIDTH_PERCENT
        ]);
        
        $infoData = [
            ['Case ID:', $report->case_id],
            ['Hospital:', $report->hospital->name ?? ''],
            ['Report Date:', $report->created->format('F d, Y')],
            ['Status:', ucfirst($report->status)]
        ];
        
        foreach ($infoData as [$label, $value]) {
            $table->addRow();
            $table->addCell(1500, ['bgColor' => 'f0f0f0'])->addText($label, [
                'bold' => true,
                'name' => 'Times New Roman',
                'size' => 12
            ]);
            $table->addCell(3500)->addText($value, [
                'name' => 'Times New Roman',
                'size' => 12
            ]);
        }
        
        $section->addTextBreak(2);
        
        // Sections - exact match to PDF structure
        $sections = [
            'PATIENT INFORMATION' => $reportData['patient_information'] ?? '',
            'CLINICAL INDICATION' => $reportData['clinical_indication'] ?? '',
            'PROCEDURE PERFORMED' => $reportData['procedure_performed'] ?? '',
            'TECHNICAL PARAMETERS' => $reportData['technical_parameters'] ?? '',
            'FINDINGS' => $reportData['findings'] ?? '',
            'CONCLUSION' => $reportData['conclusion'] ?? '',
            'RECOMMENDATIONS' => $reportData['recommendations'] ?? '',
        ];
        
        foreach ($sections as $title => $content) {
            if (!empty($content)) {
                // Section heading - exact match to PDF
                $section->addText($title, [
                    'bold' => true,
                    'size' => 14,
                    'color' => '1F497D',
                    'name' => 'Times New Roman'
                ], [
                    'spaceBefore' => 240,
                    'spaceAfter' => 120,
                    'borderBottomSize' => 12,
                    'borderBottomColor' => '1F497D'
                ]);
                
                // Section content with proper formatting
                $this->addFormattedWordContent($section, $content);
                $section->addTextBreak();
            }
        }
        
        // Confidence score
        if ($report->confidence_score) {
            $section->addTextBreak();
            $table = $section->addTable(['borderSize' => 0]);
            $table->addRow();
            $cell = $table->addCell(null, ['bgColor' => 'e7f3ff']);
            $cell->addText('Confidence Score: ' . $report->confidence_score . '%', [
                'bold' => true,
                'name' => 'Times New Roman',
                'size' => 12
            ]);
        }
        
        // Footer - exact match to PDF
        $section->addTextBreak(2);
        $section->addText('Report ID: ' . $report->id . ' | Generated: ' . date('F d, Y g:i A'), [
            'name' => 'Times New Roman',
            'size' => 9,
            'color' => '666666'
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'borderTopSize' => 6,
            'borderTopColor' => 'cccccc',
            'spaceBefore' => 300
        ]);
    }
    
    /**
     * Add formatted content to Word section
     */
    protected function addFormattedWordContent($section, string $content): void
    {
        // Simple but effective approach - clean HTML and add as paragraphs
        $content = strip_tags($content, '<p><br><strong><b><em><i><u>');
        
        // Handle paragraphs
        $paragraphs = preg_split('/<p[^>]*>|<\/p>/', $content, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (!empty($paragraph)) {
                // Handle line breaks
                $lines = preg_split('/<br[^>]*>/', $paragraph);
                
                foreach ($lines as $line) {
                    $line = trim(strip_tags($line));
                    if (!empty($line)) {
                        $section->addText($line, [
                            'name' => 'Times New Roman',
                            'size' => 12
                        ], [
                            'spaceAfter' => 120,
                            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH,
                            'indentation' => ['left' => 0, 'right' => 0]
                        ]);
                    }
                }
            }
        }
        
        // If no paragraphs found, add as simple text
        if (empty($paragraphs)) {
            $cleanContent = trim(strip_tags($content));
            if (!empty($cleanContent)) {
                $section->addText($cleanContent, [
                    'name' => 'Times New Roman',
                    'size' => 12
                ], [
                    'spaceAfter' => 120,
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH
                ]);
            }
        }
    }
    
    /**
     * Convert PDF HTML to Word format maintaining exact structure
     */
    protected function convertPdfHtmlToWord($section, string $htmlContent): void
    {
        // Create DOM document
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        
        // Wrap content for proper parsing
        $wrappedContent = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $htmlContent . '</body></html>';
        $dom->loadHTML($wrappedContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        
        // Process the body
        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            $this->processPdfStructureForWord($section, $body);
        }
    }
    
    /**
     * Process PDF structure for Word conversion
     */
    protected function processPdfStructureForWord($section, \DOMElement $body): void
    {
        foreach ($body->childNodes as $node) {
            $this->processPdfNode($section, $node);
        }
    }
    
    /**
     * Process PDF DOM nodes for Word
     */
    protected function processPdfNode($section, \DOMNode $node): void
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $text = trim($node->textContent);
            if (!empty($text)) {
                $section->addText($text, [
                    'name' => 'Times New Roman',
                    'size' => 12
                ], ['spaceAfter' => 80]);
            }
        } elseif ($node->nodeType === XML_ELEMENT_NODE) {
            $this->processPdfElement($section, $node);
        }
    }
    
    /**
     * Process PDF HTML elements for Word
     */
    protected function processPdfElement($section, \DOMElement $element): void
    {
        $tagName = strtolower($element->tagName);
        $text = trim($element->textContent);
        $class = $element->getAttribute('class');
        $style = $element->getAttribute('style');
        
        switch ($tagName) {
            case 'div':
                if ($class === 'report-content') {
                    // Main content div - process children
                    foreach ($element->childNodes as $child) {
                        $this->processPdfNode($section, $child);
                    }
                } else {
                    // Regular div - process children
                    foreach ($element->childNodes as $child) {
                        $this->processPdfNode($section, $child);
                    }
                }
                break;
                
            case 'h1':
                if (!empty($text)) {
                    $section->addText($text, [
                        'bold' => true,
                        'size' => 14,
                        'color' => '000080',
                        'name' => 'Times New Roman',
                        'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE
                    ], [
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                        'spaceAfter' => 240
                    ]);
                }
                break;
                
            case 'h2':
                if (!empty($text)) {
                    $section->addText($text, [
                        'bold' => true,
                        'size' => 12,
                        'name' => 'Times New Roman'
                    ], [
                        'spaceBefore' => 120,
                        'spaceAfter' => 120
                    ]);
                }
                break;
                
            case 'h3':
            case 'h4':
                if (!empty($text)) {
                    $section->addText($text, [
                        'bold' => true,
                        'size' => 12,
                        'name' => 'Times New Roman'
                    ], [
                        'spaceBefore' => 80,
                        'spaceAfter' => 80
                    ]);
                }
                break;
                
            case 'p':
                $this->processPdfParagraph($section, $element);
                break;
                
            case 'ul':
            case 'ol':
                $this->processPdfList($section, $element, $tagName === 'ol');
                break;
                
            case 'table':
                $this->processPdfTable($section, $element);
                break;
                
            case 'br':
                $section->addTextBreak();
                break;
                
            case 'strong':
            case 'b':
                if (!empty($text)) {
                    $section->addText($text, [
                        'bold' => true,
                        'name' => 'Times New Roman',
                        'size' => 12
                    ], ['spaceAfter' => 80]);
                }
                break;
                
            case 'em':
            case 'i':
                if (!empty($text)) {
                    $section->addText($text, [
                        'italic' => true,
                        'name' => 'Times New Roman',
                        'size' => 12
                    ], ['spaceAfter' => 80]);
                }
                break;
                
            default:
                // Process children for unknown elements
                foreach ($element->childNodes as $child) {
                    $this->processPdfNode($section, $child);
                }
                break;
        }
    }
    
    /**
     * Process PDF paragraph for Word
     */
    protected function processPdfParagraph($section, \DOMElement $element): void
    {
        $style = $element->getAttribute('style');
        $text = trim($element->textContent);
        
        if (empty($text)) {
            return;
        }
        
        // Determine alignment from style
        $alignment = \PhpOffice\PhpWord\SimpleType\Jc::LEFT;
        if (strpos($style, 'text-align: center') !== false) {
            $alignment = \PhpOffice\PhpWord\SimpleType\Jc::CENTER;
        } elseif (strpos($style, 'text-align: justify') !== false) {
            $alignment = \PhpOffice\PhpWord\SimpleType\Jc::BOTH;
        } elseif (strpos($style, 'text-align: right') !== false) {
            $alignment = \PhpOffice\PhpWord\SimpleType\Jc::RIGHT;
        }
        
        // Check for mixed formatting
        $hasFormatting = $this->hasMixedFormatting($element);
        
        if ($hasFormatting) {
            $this->processPdfParagraphWithFormatting($section, $element, $alignment);
        } else {
            // Simple paragraph
            $fontStyle = [
                'name' => 'Times New Roman',
                'size' => 12
            ];
            
            // Check for special formatting
            if (strpos($text, 'without sedation') !== false) {
                $fontStyle['bold'] = true;
            }
            
            $section->addText($text, $fontStyle, [
                'alignment' => $alignment,
                'spaceAfter' => 120
            ]);
        }
    }
    
    /**
     * Check if element has mixed formatting
     */
    protected function hasMixedFormatting(\DOMElement $element): bool
    {
        foreach ($element->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tagName = strtolower($child->tagName);
                if (in_array($tagName, ['strong', 'b', 'em', 'i', 'u', 'span'])) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Process PDF paragraph with mixed formatting
     */
    protected function processPdfParagraphWithFormatting($section, \DOMElement $element, string $alignment): void
    {
        $textRun = $section->addTextRun([
            'alignment' => $alignment,
            'spaceAfter' => 120
        ]);
        
        foreach ($element->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $child->textContent;
                if (!empty(trim($text))) {
                    $textRun->addText($text, [
                        'name' => 'Times New Roman',
                        'size' => 12
                    ]);
                }
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $this->addPdfInlineElement($textRun, $child);
            }
        }
    }
    
    /**
     * Add PDF inline element to text run
     */
    protected function addPdfInlineElement($textRun, \DOMElement $element): void
    {
        $tagName = strtolower($element->tagName);
        $text = trim($element->textContent);
        
        if (empty($text)) {
            return;
        }
        
        $fontStyle = [
            'name' => 'Times New Roman',
            'size' => 12
        ];
        
        switch ($tagName) {
            case 'strong':
            case 'b':
                $fontStyle['bold'] = true;
                break;
            case 'em':
            case 'i':
                $fontStyle['italic'] = true;
                break;
            case 'u':
                $fontStyle['underline'] = \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE;
                break;
            case 'span':
                // Extract styling from span
                $spanStyle = $element->getAttribute('style');
                if (strpos($spanStyle, 'font-weight: bold') !== false) {
                    $fontStyle['bold'] = true;
                }
                if (strpos($spanStyle, 'font-style: italic') !== false) {
                    $fontStyle['italic'] = true;
                }
                break;
        }
        
        $textRun->addText($text, $fontStyle);
    }
    
    /**
     * Process PDF list for Word
     */
    protected function processPdfList($section, \DOMElement $element, bool $isOrdered): void
    {
        $listItems = $element->getElementsByTagName('li');
        
        foreach ($listItems as $index => $item) {
            $text = trim($item->textContent);
            if (!empty($text)) {
                $prefix = $isOrdered ? ($index + 1) . '. ' : '• ';
                
                $section->addText($prefix . $text, [
                    'name' => 'Times New Roman',
                    'size' => 12
                ], [
                    'indentation' => ['left' => 360, 'hanging' => 180],
                    'spaceAfter' => 80
                ]);
            }
        }
        
        $section->addTextBreak();
    }
    
    /**
     * Process PDF table for Word
     */
    protected function processPdfTable($section, \DOMElement $element): void
    {
        $style = $element->getAttribute('style');
        
        // Check if it's a borderless signature table
        if (strpos($style, 'border: none') !== false || strpos($style, 'border:none') !== false) {
            $this->processPdfSignatureTable($section, $element);
            return;
        }
        
        // Regular table
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80
        ]);
        
        $rows = $element->getElementsByTagName('tr');
        
        foreach ($rows as $rowIndex => $row) {
            $table->addRow();
            $cells = $row->getElementsByTagName('td');
            if ($cells->length === 0) {
                $cells = $row->getElementsByTagName('th');
            }
            
            foreach ($cells as $cell) {
                $cellText = trim($cell->textContent);
                $isHeader = $cell->tagName === 'th' || $rowIndex === 0;
                
                $cellElement = $table->addCell(null, [
                    'bgColor' => $isHeader ? 'f8f9fa' : 'ffffff'
                ]);
                
                $cellElement->addText($cellText, [
                    'bold' => $isHeader,
                    'name' => 'Times New Roman',
                    'size' => 11
                ]);
            }
        }
        
        $section->addTextBreak();
    }
    
    /**
     * Process PDF signature table for Word
     */
    protected function processPdfSignatureTable($section, \DOMElement $element): void
    {
        $section->addTextBreak(2);
        
        $table = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
            'width' => 5000,
            'unit' => 'pct'
        ]);
        
        $rows = $element->getElementsByTagName('tr');
        
        foreach ($rows as $row) {
            $table->addRow();
            $cells = $row->getElementsByTagName('td');
            
            foreach ($cells as $cell) {
                $cellText = trim($cell->textContent);
                $cellElement = $table->addCell(2500, ['borderSize' => 0]);
                
                if (!empty($cellText)) {
                    $cellElement->addText($cellText, [
                        'name' => 'Times New Roman',
                        'size' => 12
                    ]);
                }
            }
        }
    }
    
    /**
     * Generate Word document with the same layout as PDF report
     */
    protected function generateWordDocumentLayout($section, Report $report, array $reportData): void
    {
        // Get the HTML content from the report
        $htmlContent = $reportData['content'] ?? '';
        
        if (empty($htmlContent)) {
            $section->addText('No content available', [
                'name' => 'Times New Roman',
                'size' => 12
            ]);
            return;
        }
        
        // Instead of parsing HTML, generate the exact PDF structure
        $this->generateExactPdfLayout($section, $htmlContent, $report);
    }
    
    /**
     * Generate exact PDF layout in Word format
     */
    protected function generateExactPdfLayout($section, string $htmlContent, Report $report): void
    {
        // Extract plain text content for processing
        $plainText = strip_tags($htmlContent);
        $plainText = html_entity_decode($plainText, ENT_QUOTES, 'UTF-8');
        
        // Split into lines for processing
        $lines = explode("\n", $plainText);
        $lines = array_map('trim', $lines);
        $lines = array_filter($lines); // Remove empty lines
        
        $currentSection = '';
        $inSignatureBlock = false;
        
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            // Detect and format different sections
            if ($this->isMainTitle($line)) {
                $this->addMainTitle($section, $line);
            } elseif ($this->isPatientInfoField($line)) {
                $this->addPatientInfoField($section, $line);
            } elseif ($this->isSectionHeader($line)) {
                $this->addSectionHeader($section, $line);
                $currentSection = $line;
            } elseif ($this->isCenteredText($line)) {
                $this->addCenteredText($section, $line);
            } elseif ($this->isListItem($line)) {
                $this->addListItem($section, $line);
            } elseif ($this->isSignatureLine($line)) {
                $this->addSignatureLine($section, $line);
                $inSignatureBlock = true;
            } elseif ($inSignatureBlock && $this->isSignatureField($line)) {
                $this->addSignatureField($section, $line);
            } else {
                // Regular paragraph
                $this->addRegularParagraph($section, $line, $currentSection);
            }
        }
        
        // Add signature block if not already added
        if (!$inSignatureBlock) {
            $this->addSignatureBlock($section);
        }
    }
    
    /**
     * Check if line is main title
     */
    protected function isMainTitle(string $line): bool
    {
        return stripos($line, 'Magnetoencephalography Report') !== false || 
               stripos($line, 'MEG Report') !== false ||
               (stripos($line, 'MEG') !== false && strlen($line) < 100);
    }
    
    /**
     * Check if line is patient info field
     */
    protected function isPatientInfoField(string $line): bool
    {
        $fields = ['Name:', 'Date of Birth:', 'MRN', 'Date of Study:', 'Referring Physician:', 'MEG ID:', 'Patient History:', 'Medication:'];
        foreach ($fields as $field) {
            if (stripos($line, $field) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if line is section header
     */
    protected function isSectionHeader(string $line): bool
    {
        $headers = ['MEG RECORDINGS:', 'AI Recommendations:', 'MSI Technical Note:', 'TECHNICAL DESCRIPTION OF PROCEDURES:'];
        foreach ($headers as $header) {
            if (stripos($line, $header) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if line should be centered
     */
    protected function isCenteredText(string $line): bool
    {
        return stripos($line, 'without sedation') !== false ||
               stripos($line, 'performed without') !== false;
    }
    
    /**
     * Check if line is a list item
     */
    protected function isListItem(string $line): bool
    {
        return strpos($line, '•') === 0 || 
               preg_match('/^\d+\./', $line) ||
               stripos($line, 'Localization of') !== false ||
               stripos($line, 'Comprehensive Functional') !== false;
    }
    
    /**
     * Check if line is signature line
     */
    protected function isSignatureLine(string $line): bool
    {
        return stripos($line, 'Signature:') !== false ||
               stripos($line, 'Date:') !== false ||
               stripos($line, 'Reviewed by:') !== false;
    }
    
    /**
     * Check if line is signature field
     */
    protected function isSignatureField(string $line): bool
    {
        return strpos($line, '___') !== false || 
               (strlen($line) < 50 && stripos($line, 'MD') !== false);
    }
    
    /**
     * Add main title
     */
    protected function addMainTitle($section, string $title): void
    {
        $section->addText(
            $title,
            [
                'bold' => true,
                'size' => 14,
                'color' => '000080',
                'name' => 'Times New Roman',
                'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE
            ],
            [
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                'spaceAfter' => 240
            ]
        );
    }
    
    /**
     * Add patient info field
     */
    protected function addPatientInfoField($section, string $line): void
    {
        // Split on common separators
        if (strpos($line, ':') !== false) {
            $parts = explode(':', $line, 2);
            $label = trim($parts[0]) . ':';
            $value = isset($parts[1]) ? trim($parts[1]) : '';
            
            if (!empty($value)) {
                $textRun = $section->addTextRun(['spaceAfter' => 0]);
                $textRun->addText($label . ' ', [
                    'bold' => true,
                    'name' => 'Times New Roman',
                    'size' => 12
                ]);
                $textRun->addText($value, [
                    'name' => 'Times New Roman',
                    'size' => 12
                ]);
            } else {
                $section->addText($label, [
                    'bold' => true,
                    'name' => 'Times New Roman',
                    'size' => 12
                ], ['spaceAfter' => 0]);
            }
        } else {
            $section->addText($line, [
                'name' => 'Times New Roman',
                'size' => 12
            ], ['spaceAfter' => 0]);
        }
    }
    
    /**
     * Add section header
     */
    protected function addSectionHeader($section, string $header): void
    {
        $section->addTextBreak();
        $section->addText($header, [
            'bold' => true,
            'name' => 'Times New Roman',
            'size' => 12
        ], ['spaceAfter' => 120]);
    }
    
    /**
     * Add centered text
     */
    protected function addCenteredText($section, string $text): void
    {
        $section->addText($text, [
            'bold' => true,
            'name' => 'Times New Roman',
            'size' => 12
        ], [
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
            'spaceAfter' => 120
        ]);
    }
    
    /**
     * Add list item
     */
    protected function addListItem($section, string $item): void
    {
        // Clean up the item text
        $cleanItem = $item;
        if (strpos($cleanItem, '•') === 0) {
            $cleanItem = trim(substr($cleanItem, 1));
        } elseif (preg_match('/^\d+\.(.*)/', $cleanItem, $matches)) {
            $cleanItem = trim($matches[1]);
        }
        
        $section->addText($cleanItem, [
            'name' => 'Times New Roman',
            'size' => 12
        ], [
            'indentation' => ['left' => 0],
            'spaceAfter' => 0
        ]);
    }
    
    /**
     * Add regular paragraph
     */
    protected function addRegularParagraph($section, string $text, string $currentSection): void
    {
        // Determine alignment based on content and section
        $alignment = \PhpOffice\PhpWord\SimpleType\Jc::LEFT;
        
        // Check for justified text (long paragraphs)
        if (strlen($text) > 100 && stripos($currentSection, 'AI Recommendations') !== false) {
            $alignment = \PhpOffice\PhpWord\SimpleType\Jc::BOTH;
        }
        
        // Check for indented technical content
        $indentation = null;
        if (stripos($text, 'Neuromag') !== false || stripos($text, 'Elekta') !== false) {
            $indentation = ['left' => 360];
        } elseif (stripos($text, 'international 10-20') !== false) {
            $indentation = ['left' => 720];
        } elseif (stripos($text, 'judged by statistical') !== false) {
            $indentation = ['left' => 720];
        }
        
        $paragraphStyle = [
            'alignment' => $alignment,
            'spaceAfter' => 120
        ];
        
        if ($indentation) {
            $paragraphStyle['indentation'] = $indentation;
        }
        
        $section->addText($text, [
            'name' => 'Times New Roman',
            'size' => 12
        ], $paragraphStyle);
    }
    
    /**
     * Add signature line
     */
    protected function addSignatureLine($section, string $line): void
    {
        $section->addTextBreak(2);
        $section->addText($line, [
            'name' => 'Times New Roman',
            'size' => 12
        ], ['spaceAfter' => 120]);
    }
    
    /**
     * Add signature field
     */
    protected function addSignatureField($section, string $field): void
    {
        $section->addText($field, [
            'name' => 'Times New Roman',
            'size' => 12
        ], ['spaceAfter' => 60]);
    }
    
    /**
     * Add signature block
     */
    protected function addSignatureBlock($section): void
    {
        $section->addTextBreak(3);
        
        // Create signature table
        $table = $section->addTable([
            'borderSize' => 0,
            'cellMargin' => 0,
            'width' => 5000,
            'unit' => 'pct'
        ]);
        
        // First row - signature lines
        $table->addRow();
        $cell1 = $table->addCell(2500, ['borderSize' => 0]);
        $cell1->addText('Signature: ________________________', [
            'name' => 'Times New Roman',
            'size' => 12
        ]);
        
        $cell2 = $table->addCell(2500, ['borderSize' => 0]);
        $cell2->addText('Date: _______________', [
            'name' => 'Times New Roman',
            'size' => 12
        ]);
        
        // Second row - names
        $table->addRow();
        $cell3 = $table->addCell(2500, ['borderSize' => 0]);
        $cell3->addText('Dr. [Name], MD', [
            'name' => 'Times New Roman',
            'size' => 12
        ]);
        
        $cell4 = $table->addCell(2500, ['borderSize' => 0]);
        $cell4->addText('', [
            'name' => 'Times New Roman',
            'size' => 12
        ]);
        
        // Third row - title
        $table->addRow();
        $cell5 = $table->addCell(5000, ['borderSize' => 0]);
        $cell5->addText('MEG Technologist', [
            'name' => 'Times New Roman',
            'size' => 12
        ]);
    }
    
    /**
     * Convert HTML content to Word format while preserving structure
     */
    protected function convertHtmlContentToWord($section, string $htmlContent): void
    {
        // Create DOM document to parse HTML
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        
        // Clean and prepare HTML
        $cleanHtml = $this->prepareHtmlForParsing($htmlContent);
        $dom->loadHTML($cleanHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        
        // Process the document body
        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            $this->processBodyContent($section, $body);
        }
    }
    
    /**
     * Prepare HTML for parsing
     */
    protected function prepareHtmlForParsing(string $html): string
    {
        // Add proper HTML structure
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>';
        
        // Clean up common HTML issues
        $html = str_replace(['&nbsp;'], [' '], $html);
        $html = preg_replace('/\s+/', ' ', $html);
        
        return $html;
    }
    
    /**
     * Process body content and convert to Word elements
     */
    protected function processBodyContent($section, \DOMElement $body): void
    {
        foreach ($body->childNodes as $node) {
            $this->processNode($section, $node);
        }
    }
    
    /**
     * Process individual DOM nodes
     */
    protected function processNode($section, \DOMNode $node): void
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $text = trim($node->textContent);
            if (!empty($text)) {
                $section->addText($text, [
                    'name' => 'Times New Roman',
                    'size' => 11
                ], ['spaceAfter' => 60]);
            }
        } elseif ($node->nodeType === XML_ELEMENT_NODE) {
            $this->processElement($section, $node);
        }
    }
    
    /**
     * Process HTML elements and convert to Word format
     */
    protected function processElement($section, \DOMElement $element): void
    {
        $tagName = strtolower($element->tagName);
        
        switch ($tagName) {
            case 'div':
                $this->processDiv($section, $element);
                break;
                
            case 'h1':
                $this->processHeading($section, $element, 16, true, true);
                break;
                
            case 'h2':
                $this->processHeading($section, $element, 14, true, false);
                break;
                
            case 'h3':
                $this->processHeading($section, $element, 13, true, false);
                break;
                
            case 'h4':
                $this->processHeading($section, $element, 12, true, false);
                break;
                
            case 'p':
                $this->processParagraph($section, $element);
                break;
                
            case 'ul':
            case 'ol':
                $this->processList($section, $element, $tagName === 'ol');
                break;
                
            case 'table':
                $this->processTable($section, $element);
                break;
                
            case 'br':
                $section->addTextBreak();
                break;
                
            case 'strong':
            case 'b':
                $this->addTextWithFormatting($section, $element, ['bold' => true]);
                break;
                
            case 'em':
            case 'i':
                $this->addTextWithFormatting($section, $element, ['italic' => true]);
                break;
                
            case 'u':
                $this->addTextWithFormatting($section, $element, ['underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE]);
                break;
                
            default:
                // Process children for unknown elements
                foreach ($element->childNodes as $child) {
                    $this->processNode($section, $child);
                }
                break;
        }
    }
    
    /**
     * Process div elements
     */
    protected function processDiv($section, \DOMElement $element): void
    {
        // Check for special div classes or styles
        $class = $element->getAttribute('class');
        $style = $element->getAttribute('style');
        
        // Process children
        foreach ($element->childNodes as $child) {
            $this->processNode($section, $child);
        }
    }
    
    /**
     * Process heading elements
     */
    protected function processHeading($section, \DOMElement $element, int $size, bool $bold, bool $underline): void
    {
        $text = trim($element->textContent);
        if (empty($text)) {
            return;
        }
        
        $fontStyle = [
            'name' => 'Times New Roman',
            'size' => $size,
            'bold' => $bold,
            'color' => '000080' // Navy blue for headings
        ];
        
        if ($underline) {
            $fontStyle['underline'] = \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE;
        }
        
        $paragraphStyle = [
            'alignment' => $size >= 16 ? \PhpOffice\PhpWord\SimpleType\Jc::CENTER : \PhpOffice\PhpWord\SimpleType\Jc::LEFT,
            'spaceBefore' => $size >= 16 ? 0 : 120,
            'spaceAfter' => 240
        ];
        
        $section->addText($text, $fontStyle, $paragraphStyle);
    }
    
    /**
     * Process paragraph elements
     */
    protected function processParagraph($section, \DOMElement $element): void
    {
        $hasInlineFormatting = $this->hasInlineFormatting($element);
        
        if ($hasInlineFormatting) {
            $this->processParagraphWithMixedFormatting($section, $element);
        } else {
            $text = trim($element->textContent);
            if (!empty($text)) {
                // Get alignment from style
                $style = $element->getAttribute('style');
                $alignment = $this->getAlignmentFromStyle($style);
                
                // Check if it's a special centered paragraph (like "without sedation")
                $isCentered = strpos($style, 'text-align: center') !== false || 
                             strpos($style, 'text-align:center') !== false;
                $isBold = strpos($text, 'without sedation') !== false || 
                         strpos($text, 'performed') !== false;
                
                $fontStyle = [
                    'name' => 'Times New Roman',
                    'size' => 11
                ];
                
                if ($isBold) {
                    $fontStyle['bold'] = true;
                }
                
                $paragraphStyle = [
                    'alignment' => $alignment,
                    'spaceAfter' => 120
                ];
                
                $section->addText($text, $fontStyle, $paragraphStyle);
            }
        }
    }
    
    /**
     * Check if paragraph has inline formatting
     */
    protected function hasInlineFormatting(\DOMElement $element): bool
    {
        foreach ($element->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tagName = strtolower($child->tagName);
                if (in_array($tagName, ['strong', 'b', 'em', 'i', 'u', 'span'])) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Process paragraph with mixed formatting
     */
    protected function processParagraphWithMixedFormatting($section, \DOMElement $element): void
    {
        $style = $element->getAttribute('style');
        $alignment = $this->getAlignmentFromStyle($style);
        
        $textRun = $section->addTextRun([
            'alignment' => $alignment,
            'spaceAfter' => 120
        ]);
        
        foreach ($element->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $child->textContent;
                if (!empty(trim($text))) {
                    $textRun->addText($text, [
                        'name' => 'Times New Roman',
                        'size' => 11
                    ]);
                }
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $this->addInlineElementToTextRun($textRun, $child);
            }
        }
    }
    
    /**
     * Add inline element to text run
     */
    protected function addInlineElementToTextRun($textRun, \DOMElement $element): void
    {
        $tagName = strtolower($element->tagName);
        $text = trim($element->textContent);
        
        if (empty($text)) {
            return;
        }
        
        $fontStyle = [
            'name' => 'Times New Roman',
            'size' => 11
        ];
        
        switch ($tagName) {
            case 'strong':
            case 'b':
                $fontStyle['bold'] = true;
                break;
            case 'em':
            case 'i':
                $fontStyle['italic'] = true;
                break;
            case 'u':
                $fontStyle['underline'] = \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE;
                break;
            case 'span':
                // Extract style information
                $spanStyle = $element->getAttribute('style');
                if (strpos($spanStyle, 'font-weight: bold') !== false || strpos($spanStyle, 'font-weight:bold') !== false) {
                    $fontStyle['bold'] = true;
                }
                if (strpos($spanStyle, 'font-style: italic') !== false || strpos($spanStyle, 'font-style:italic') !== false) {
                    $fontStyle['italic'] = true;
                }
                // Extract color
                if (preg_match('/color:\s*(#[0-9a-f]{6}|#[0-9a-f]{3}|rgb\([^)]+\))/i', $spanStyle, $matches)) {
                    $color = $this->convertColorToHex($matches[1]);
                    if ($color) {
                        $fontStyle['color'] = $color;
                    }
                }
                break;
        }
        
        $textRun->addText($text, $fontStyle);
    }
    
    /**
     * Add text with specific formatting
     */
    protected function addTextWithFormatting($section, \DOMElement $element, array $additionalFormatting): void
    {
        $text = trim($element->textContent);
        if (!empty($text)) {
            $fontStyle = array_merge([
                'name' => 'Times New Roman',
                'size' => 11
            ], $additionalFormatting);
            
            $section->addText($text, $fontStyle, ['spaceAfter' => 60]);
        }
    }
    
    /**
     * Process list elements
     */
    protected function processList($section, \DOMElement $element, bool $isOrdered): void
    {
        $listItems = $element->getElementsByTagName('li');
        
        foreach ($listItems as $index => $item) {
            $text = trim($item->textContent);
            if (!empty($text)) {
                $prefix = $isOrdered ? ($index + 1) . '. ' : '• ';
                
                $section->addText(
                    $prefix . $text,
                    [
                        'name' => 'Times New Roman',
                        'size' => 11
                    ],
                    [
                        'indentation' => [
                            'left' => 360,
                            'hanging' => 180
                        ],
                        'spaceAfter' => 60
                    ]
                );
            }
        }
        
        $section->addTextBreak();
    }
    
    /**
     * Process table elements
     */
    protected function processTable($section, \DOMElement $element): void
    {
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 80
        ]);
        
        $rows = $element->getElementsByTagName('tr');
        
        foreach ($rows as $rowIndex => $row) {
            $table->addRow();
            $cells = $row->getElementsByTagName('td');
            if ($cells->length === 0) {
                $cells = $row->getElementsByTagName('th');
            }
            
            foreach ($cells as $cell) {
                $cellText = trim($cell->textContent);
                $isHeader = $cell->tagName === 'th' || $rowIndex === 0;
                
                $cellElement = $table->addCell(null, [
                    'bgColor' => $isHeader ? 'f8f9fa' : 'ffffff'
                ]);
                
                $cellElement->addText($cellText, [
                    'bold' => $isHeader,
                    'name' => 'Times New Roman',
                    'size' => 10
                ]);
            }
        }
        
        $section->addTextBreak();
    }

    /**
     * Convert HTML content to Word formatting
     */
    protected function convertHtmlToWord($section, string $htmlContent): void
    {
        // Clean and prepare HTML content
        $htmlContent = $this->cleanHtmlForWord($htmlContent);
        
        // Parse HTML and convert to Word elements
        $dom = new \DOMDocument();
        
        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        
        // Add HTML5 doctype and encoding
        $htmlContent = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $htmlContent . '</body></html>';
        $dom->loadHTML($htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        libxml_clear_errors();
        
        // Process the body content
        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            $this->processWordNode($section, $body);
        }
    }
    
    /**
     * Clean HTML content for better Word conversion while preserving formatting
     */
    protected function cleanHtmlForWord(string $html): string
    {
        // Remove PHP-specific content that shouldn't be in final output
        $html = preg_replace('/<\?php.*?\?>/s', '', $html);
        $html = preg_replace('/<\?=.*?\?>/s', '', $html);
        
        // Fix self-closing tags that should be properly closed for XML parsing
        $html = preg_replace('/<br\s*\/?>/', '<br/>', $html);
        $html = preg_replace('/<hr\s*\/?>/', '<hr/>', $html);
        $html = preg_replace('/<img([^>]*?)\/?>/', '<img$1/>', $html);
        
        // Ensure list items are properly nested and closed
        $html = preg_replace('/<li([^>]*)>\s*<br\s*\/?>\s*/', '<li$1>', $html);
        $html = preg_replace('/\s*<br\s*\/?>\s*<\/li>/', '</li>', $html);
        
        // Fix unclosed list items - ensure every <li> has a closing </li>
        $html = preg_replace('/<li([^>]*)>([^<]*?)(?=<li|<\/ul|<\/ol|$)/s', '<li$1>$2</li>', $html);
        
        // Clean up HTML entities and whitespace issues that cause Word formatting problems
        $html = str_replace('&nbsp;', ' ', $html);
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove excessive whitespace but preserve line breaks in content
        $html = preg_replace('/[ \t]+/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);
        
        // Clean up problematic HTML patterns that cause Word formatting issues
        $html = preg_replace('/<br\s*\/?>\s*<br\s*\/?>/', '<br/>', $html);
        
        // Remove empty paragraphs and divs but preserve those with styling
        $html = preg_replace('/<p[^>]*>\s*<\/p>/', '', $html);
        $html = preg_replace('/<div[^>]*>\s*<\/div>/', '', $html);
        
        // Fix nested formatting issues
        $html = preg_replace('/<(strong|b)>\s*<\/\1>/', '', $html);
        $html = preg_replace('/<(em|i)>\s*<\/\1>/', '', $html);
        
        // Clean up excessive spaces but preserve intentional formatting
        $html = preg_replace('/(&nbsp;){2,}/', ' ', $html);
        
        // Ensure proper nesting - fix any unclosed tags that might cause XML parsing errors
        $html = $this->fixHtmlNesting($html);
        
        return trim($html);
    }
    
    /**
     * Fix HTML nesting issues that cause XML parsing errors
     */
    protected function fixHtmlNesting(string $html): string
    {
        // Create a simple DOM to validate and fix structure
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        
        // Try to load and fix the HTML
        $wrappedHtml = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>';
        
        if ($dom->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            // Extract the body content which should now be properly formatted
            $body = $dom->getElementsByTagName('body')->item(0);
            if ($body) {
                $html = '';
                foreach ($body->childNodes as $node) {
                    $html .= $dom->saveHTML($node);
                }
            }
        }
        
        libxml_clear_errors();
        return $html;
    }

    /**
     * Process DOM nodes for Word document with better structure handling
     */
    protected function processWordNode($section, \DOMNode $node): void
    {
        foreach ($node->childNodes as $child) {
            switch ($child->nodeType) {
                case XML_TEXT_NODE:
                    $text = $child->textContent;
                    // Clean up whitespace but preserve meaningful text
                    $text = preg_replace('/\s+/', ' ', $text);
                    $text = trim($text);
                    
                    // Only add substantial text content (not just whitespace)
                    if (!empty($text) && strlen($text) > 0) {
                        $this->addWordText($section, $text);
                    }
                    break;
                    
                case XML_ELEMENT_NODE:
                    $this->processWordElement($section, $child);
                    break;
            }
        }
    }

    /**
     * Process individual HTML elements for Word conversion with enhanced formatting
     */
    protected function processWordElement($section, \DOMElement $element): void
    {
        $tagName = strtolower($element->tagName);
        $style = $element->getAttribute('style');
        $class = $element->getAttribute('class');
        
        switch ($tagName) {
            case 'div':
                // Handle main content divs with style preservation
                if (strpos($style, 'page-break-inside: avoid') !== false) {
                    // Signature block - add extra spacing before
                    $section->addTextBreak(2);
                    $this->processWordNode($section, $element);
                } elseif (strpos($style, 'font-family') !== false || strpos($style, 'max-width') !== false) {
                    // Main document container - process children
                    $this->processWordNode($section, $element);
                } else {
                    // Regular div - process children without extra formatting
                    $this->processWordNode($section, $element);
                }
                break;
                
            case 'span':
                // Handle span elements with style preservation
                $this->processStyledSpan($section, $element);
                break;
                
            case 'h1':
                $text = trim($element->textContent);
                if (!empty($text)) {
                    $fontStyle = $this->extractFontStyleFromElement($element, [
                        'bold' => true, 
                        'size' => 16, 
                        'color' => '1F497D', 
                        'name' => 'Times New Roman'
                    ]);
                    
                    $section->addText(
                        $text,
                        $fontStyle,
                        [
                            'alignment' => $this->getAlignmentFromStyle($style),
                            'spaceBefore' => 0,
                            'spaceAfter' => 240
                        ]
                    );
                }
                break;
                
            case 'h2':
            case 'h3':
            case 'h4':
                $text = trim($element->textContent);
                if (!empty($text)) {
                    $size = $tagName === 'h2' ? 14 : ($tagName === 'h3' ? 13 : 12);
                    $fontStyle = $this->extractFontStyleFromElement($element, [
                        'bold' => true, 
                        'size' => $size, 
                        'color' => '1F497D', 
                        'name' => 'Times New Roman'
                    ]);
                    
                    $section->addText(
                        $text,
                        $fontStyle,
                        [
                            'alignment' => $this->getAlignmentFromStyle($style),
                            'spaceBefore' => 120,
                            'spaceAfter' => 60
                        ]
                    );
                }
                break;
                
            case 'p':
                $this->processWordParagraph($section, $element);
                break;
                
            case 'ul':
            case 'ol':
                $this->processWordList($section, $element, $tagName === 'ol');
                break;
                
            case 'table':
                $this->processWordTable($section, $element);
                break;
                
            case 'br':
                $section->addTextBreak();
                break;
                
            case 'strong':
            case 'b':
                $text = trim($element->textContent);
                if (!empty($text)) {
                    $fontStyle = $this->extractFontStyleFromElement($element, ['bold' => true]);
                    $this->addWordText($section, $text, $fontStyle);
                }
                break;
                
            case 'em':
            case 'i':
                $text = trim($element->textContent);
                if (!empty($text)) {
                    $fontStyle = $this->extractFontStyleFromElement($element, ['italic' => true]);
                    $this->addWordText($section, $text, $fontStyle);
                }
                break;
                
            case 'u':
                $text = trim($element->textContent);
                if (!empty($text)) {
                    $fontStyle = $this->extractFontStyleFromElement($element, [
                        'underline' => \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE
                    ]);
                    $this->addWordText($section, $text, $fontStyle);
                }
                break;
                
            default:
                // For other elements, process their children
                $this->processWordNode($section, $element);
                break;
        }
    }
    
    /**
     * Process styled span elements with font formatting
     */
    protected function processStyledSpan($section, \DOMElement $element): void
    {
        $text = trim($element->textContent);
        if (!empty($text)) {
            $fontStyle = $this->extractFontStyleFromElement($element);
            $this->addWordText($section, $text, $fontStyle);
        }
    }
    
    /**
     * Extract font style properties from HTML element
     */
    protected function extractFontStyleFromElement(\DOMElement $element, array $defaultStyle = []): array
    {
        $style = $element->getAttribute('style');
        $fontStyle = array_merge([
            'name' => 'Times New Roman',
            'size' => 11
        ], $defaultStyle);
        
        // Parse inline styles
        if (!empty($style)) {
            // Font family
            if (preg_match('/font-family:\s*([^;]+)/i', $style, $matches)) {
                $fontFamily = trim($matches[1], '\'"');
                $fontStyle['name'] = $this->normalizeFontFamily($fontFamily);
            }
            
            // Font size
            if (preg_match('/font-size:\s*(\d+)px/i', $style, $matches)) {
                $fontStyle['size'] = (int)$matches[1];
            } elseif (preg_match('/font-size:\s*(\d+)pt/i', $style, $matches)) {
                $fontStyle['size'] = (int)$matches[1];
            } elseif (preg_match('/font-size:\s*(\d+\.?\d*)em/i', $style, $matches)) {
                $fontStyle['size'] = (int)((float)$matches[1] * 11); // Convert em to points
            }
            
            // Font weight
            if (preg_match('/font-weight:\s*(bold|700|800|900)/i', $style)) {
                $fontStyle['bold'] = true;
            }
            
            // Font style
            if (preg_match('/font-style:\s*italic/i', $style)) {
                $fontStyle['italic'] = true;
            }
            
            // Text decoration
            if (preg_match('/text-decoration:\s*underline/i', $style)) {
                $fontStyle['underline'] = \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE;
            }
            
            // Color
            if (preg_match('/color:\s*(#[0-9a-f]{6}|#[0-9a-f]{3}|rgb\([^)]+\))/i', $style, $matches)) {
                $color = $this->convertColorToHex($matches[1]);
                if ($color) {
                    $fontStyle['color'] = $color;
                }
            }
            
            // Background color
            if (preg_match('/background-color:\s*(#[0-9a-f]{6}|#[0-9a-f]{3}|rgb\([^)]+\))/i', $style, $matches)) {
                $backgroundColor = $this->convertColorToHex($matches[1]);
                if ($backgroundColor) {
                    $fontStyle['bgColor'] = $backgroundColor;
                }
            }
        }
        
        return $fontStyle;
    }
    
    /**
     * Normalize font family names for Word compatibility
     */
    protected function normalizeFontFamily(string $fontFamily): string
    {
        // Remove quotes and clean up
        $fontFamily = trim($fontFamily, '\'"');
        
        // Common font mappings
        $fontMappings = [
            'Arial' => 'Arial',
            'Times' => 'Times New Roman',
            'Times New Roman' => 'Times New Roman',
            'Helvetica' => 'Arial',
            'Courier' => 'Courier New',
            'Courier New' => 'Courier New',
            'Georgia' => 'Georgia',
            'Verdana' => 'Verdana',
            'Calibri' => 'Calibri',
            'serif' => 'Times New Roman',
            'sans-serif' => 'Arial',
            'monospace' => 'Courier New'
        ];
        
        // Extract first font from font stack
        $fonts = explode(',', $fontFamily);
        $firstFont = trim($fonts[0], '\'"');
        
        return $fontMappings[$firstFont] ?? 'Times New Roman';
    }
    
    /**
     * Convert CSS color to hex format for Word
     */
    protected function convertColorToHex(string $color): ?string
    {
        $color = trim($color);
        
        // Already hex color
        if (preg_match('/^#([0-9a-f]{6})$/i', $color, $matches)) {
            return strtoupper($matches[1]);
        }
        
        // Short hex color
        if (preg_match('/^#([0-9a-f]{3})$/i', $color, $matches)) {
            $hex = $matches[1];
            return strtoupper($hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2]);
        }
        
        // RGB color
        if (preg_match('/rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)/i', $color, $matches)) {
            $r = (int)$matches[1];
            $g = (int)$matches[2];
            $b = (int)$matches[3];
            return sprintf('%02X%02X%02X', $r, $g, $b);
        }
        
        // Named colors
        $namedColors = [
            'black' => '000000',
            'white' => 'FFFFFF',
            'red' => 'FF0000',
            'green' => '008000',
            'blue' => '0000FF',
            'yellow' => 'FFFF00',
            'cyan' => '00FFFF',
            'magenta' => 'FF00FF',
            'gray' => '808080',
            'grey' => '808080',
            'darkgray' => '404040',
            'darkgrey' => '404040',
            'lightgray' => 'C0C0C0',
            'lightgrey' => 'C0C0C0'
        ];
        
        $colorLower = strtolower($color);
        return $namedColors[$colorLower] ?? null;
    }
    
    /**
     * Get text alignment from CSS style
     */
    protected function getAlignmentFromStyle(string $style): string
    {
        if (strpos($style, 'text-align: center') !== false || strpos($style, 'text-align:center') !== false) {
            return \PhpOffice\PhpWord\SimpleType\Jc::CENTER;
        } elseif (strpos($style, 'text-align: justify') !== false || strpos($style, 'text-align:justify') !== false) {
            return \PhpOffice\PhpWord\SimpleType\Jc::BOTH;
        } elseif (strpos($style, 'text-align: right') !== false || strpos($style, 'text-align:right') !== false) {
            return \PhpOffice\PhpWord\SimpleType\Jc::RIGHT;
        }
        
        return \PhpOffice\PhpWord\SimpleType\Jc::LEFT;
    }

    /**
     * Process paragraph elements with proper formatting and style preservation
     */
    protected function processWordParagraph($section, \DOMElement $element): void
    {
        $style = $element->getAttribute('style');
        $textContent = '';
        $hasFormatting = false;
        
        // Extract text and check for inline formatting
        foreach ($element->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $textContent .= $child->textContent;
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $childTag = strtolower($child->tagName);
                if (in_array($childTag, ['strong', 'b', 'em', 'i', 'u', 'span'])) {
                    $hasFormatting = true;
                }
                $textContent .= $child->textContent;
            }
        }
        
        // Clean up text content - remove excessive spaces and HTML artifacts
        $textContent = preg_replace('/\s+/', ' ', $textContent);
        $textContent = trim($textContent);
        
        if (empty($textContent)) {
            return;
        }
        
        // Determine paragraph style from CSS
        $paragraphStyle = [
            'spaceBefore' => 0,
            'spaceAfter' => 120,
            'alignment' => $this->getAlignmentFromStyle($style)
        ];
        
        // Extract additional paragraph styling
        if (preg_match('/margin-top:\s*(\d+)px/i', $style, $matches)) {
            $paragraphStyle['spaceBefore'] = (int)$matches[1] * 10; // Convert px to twips (rough conversion)
        }
        
        if (preg_match('/margin-bottom:\s*(\d+)px/i', $style, $matches)) {
            $paragraphStyle['spaceAfter'] = (int)$matches[1] * 10; // Convert px to twips
        }
        
        // Basic font styling from paragraph
        $paragraphFontStyle = $this->extractFontStyleFromElement($element, [
            'name' => 'Times New Roman',
            'size' => 11
        ]);
        
        if ($hasFormatting) {
            // For paragraphs with mixed formatting, process each child separately
            $this->processWordParagraphWithFormatting($section, $element, $paragraphStyle);
        } else {
            // Simple paragraph with unified styling
            $section->addText($textContent, $paragraphFontStyle, $paragraphStyle);
        }
    }

    /**
     * Process paragraph with mixed formatting (bold, italic, colors, etc.)
     */
    protected function processWordParagraphWithFormatting($section, \DOMElement $element, array $paragraphStyle): void
    {
        $textRun = $section->addTextRun($paragraphStyle);
        
        foreach ($element->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $child->textContent;
                // Clean up whitespace and HTML artifacts
                $text = preg_replace('/\s+/', ' ', $text);
                if (!empty(trim($text))) {
                    // Use parent element styling for text nodes
                    $fontStyle = $this->extractFontStyleFromElement($element, [
                        'name' => 'Times New Roman',
                        'size' => 11
                    ]);
                    $textRun->addText($text, $fontStyle);
                }
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $this->processInlineElement($textRun, $child, $element);
            }
        }
    }
    
    /**
     * Process inline elements within text runs
     */
    protected function processInlineElement($textRun, \DOMElement $element, \DOMElement $parentElement): void
    {
        $childTag = strtolower($element->tagName);
        $text = $element->textContent;
        
        // Clean up text content thoroughly
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        if (empty($text)) {
            return;
        }
        
        // Start with parent styling and add element-specific styles
        $fontStyle = $this->extractFontStyleFromElement($parentElement, [
            'name' => 'Times New Roman',
            'size' => 11
        ]);
        
        // Apply element-specific formatting
        $elementStyle = $this->extractFontStyleFromElement($element, $fontStyle);
        
        // Add tag-specific formatting
        switch ($childTag) {
            case 'strong':
            case 'b':
                $elementStyle['bold'] = true;
                break;
            case 'em':
            case 'i':
                $elementStyle['italic'] = true;
                break;
            case 'u':
                $elementStyle['underline'] = \PhpOffice\PhpWord\Style\Font::UNDERLINE_SINGLE;
                break;
            case 'span':
                // Span elements already handled by extractFontStyleFromElement
                break;
        }
        
        $textRun->addText($text, $elementStyle);
    }

    /**
     * Process lists with proper indentation and numbering
     */
    protected function processWordList($section, \DOMElement $listElement, bool $isOrdered = false): void
    {
        $items = $listElement->getElementsByTagName('li');
        
        if ($items->length === 0) {
            return;
        }
        
        $section->addTextBreak();
        
        foreach ($items as $index => $item) {
            // Extract text content and handle nested formatting
            $text = $this->extractCleanTextFromElement($item);
            
            if (!empty($text)) {
                $prefix = $isOrdered ? ($index + 1) . '. ' : '• ';
                
                // Check if the list item has special formatting (like bold titles)
                $hasStrongChild = $item->getElementsByTagName('strong')->length > 0 || 
                                 $item->getElementsByTagName('b')->length > 0;
                
                if ($hasStrongChild) {
                    // Handle mixed formatting in list items
                    $this->processListItemWithFormatting($section, $item, $prefix);
                } else {
                    // Simple list item
                    $section->addText(
                        $prefix . $text,
                        [
                            'name' => 'Times New Roman',
                            'size' => 11
                        ],
                        [
                            'indentation' => [
                                'left' => 720,     // 0.5 inch
                                'hanging' => 360   // 0.25 inch hanging
                            ],
                            'spaceAfter' => 60
                        ]
                    );
                }
            }
        }
        
        $section->addTextBreak();
    }

    /**
     * Process list item with mixed formatting and colors
     */
    protected function processListItemWithFormatting($section, \DOMElement $item, string $prefix): void
    {
        $textRun = $section->addTextRun([
            'indentation' => [
                'left' => 720,     // 0.5 inch
                'hanging' => 360   // 0.25 inch hanging
            ],
            'spaceAfter' => 60
        ]);
        
        // Add the bullet/number prefix with default styling
        $textRun->addText($prefix, [
            'name' => 'Times New Roman',
            'size' => 11
        ]);
        
        // Process the content with formatting
        foreach ($item->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $child->textContent;
                $text = preg_replace('/\s+/', ' ', $text);
                if (!empty(trim($text))) {
                    $fontStyle = $this->extractFontStyleFromElement($item, [
                        'name' => 'Times New Roman',
                        'size' => 11
                    ]);
                    $textRun->addText($text, $fontStyle);
                }
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $this->processInlineElement($textRun, $child, $item);
            }
        }
    }

    /**
     * Add formatted text to Word document with enhanced styling
     */
    protected function addWordText($section, string $text, array $additionalFontStyle = []): void
    {
        if (empty(trim($text))) {
            return;
        }
        
        $fontStyle = array_merge([
            'name' => 'Times New Roman',
            'size' => 11
        ], $additionalFontStyle);
        
        $section->addText(
            $text,
            $fontStyle,
            [
                'spaceBefore' => 0,
                'spaceAfter' => 60
            ]
        );
    }

    /**
     * Process HTML table and convert to Word table with formatting preservation
     */
    protected function processWordTable($section, \DOMElement $tableElement): void
    {
        $style = $tableElement->getAttribute('style');
        
        // Check if this is a signature table (borderless table for layout)
        $isSignatureTable = strpos($style, 'border: none') !== false || 
                           strpos($style, 'border:none') !== false;
        
        if ($isSignatureTable) {
            // Handle signature table as formatted text rather than a bordered table
            $this->processSignatureTable($section, $tableElement);
            return;
        }
        
        // Regular data table with borders and formatting
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin' => 80,
            'width' => 5000,
            'unit' => 'pct'
        ]);
        
        $rows = $tableElement->getElementsByTagName('tr');
        
        foreach ($rows as $rowIndex => $row) {
            $table->addRow();
            $cells = $row->getElementsByTagName('td');
            if ($cells->length === 0) {
                $cells = $row->getElementsByTagName('th');
            }
            
            foreach ($cells as $cell) {
                $isHeader = $cell->tagName === 'th' || $rowIndex === 0;
                
                // Extract cell styling
                $cellStyle = ['bgColor' => $isHeader ? 'f8f9fa' : 'ffffff'];
                
                // Check for background color in cell style
                $cellStyleAttr = $cell->getAttribute('style');
                if (preg_match('/background-color:\s*(#[0-9a-f]{6}|#[0-9a-f]{3}|rgb\([^)]+\))/i', $cellStyleAttr, $matches)) {
                    $bgColor = $this->convertColorToHex($matches[1]);
                    if ($bgColor) {
                        $cellStyle['bgColor'] = $bgColor;
                    }
                }
                
                $cellElement = $table->addCell(null, $cellStyle);
                
                // Process cell content with formatting
                $this->processCellContent($cellElement, $cell, $isHeader);
            }
        }
        
        $section->addTextBreak();
    }
    
    /**
     * Process table cell content with formatting preservation
     */
    protected function processCellContent($cellElement, \DOMElement $cell, bool $isHeader): void
    {
        // Check if cell has mixed formatting
        $hasFormatting = false;
        foreach ($cell->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $childTag = strtolower($child->tagName);
                if (in_array($childTag, ['strong', 'b', 'em', 'i', 'u', 'span'])) {
                    $hasFormatting = true;
                    break;
                }
            }
        }
        
        if ($hasFormatting) {
            // Process with mixed formatting
            $textRun = $cellElement->addTextRun();
            
            foreach ($cell->childNodes as $child) {
                if ($child->nodeType === XML_TEXT_NODE) {
                    $text = trim($child->textContent);
                    if (!empty($text)) {
                        $fontStyle = $this->extractFontStyleFromElement($cell, [
                            'bold' => $isHeader,
                            'name' => 'Times New Roman',
                            'size' => 10
                        ]);
                        $textRun->addText($text, $fontStyle);
                    }
                } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                    $this->processInlineElement($textRun, $child, $cell);
                }
            }
        } else {
            // Simple cell content
            $cellText = trim($cell->textContent);
            $fontStyle = $this->extractFontStyleFromElement($cell, [
                'bold' => $isHeader,
                'name' => 'Times New Roman',
                'size' => 10
            ]);
            
            $cellElement->addText($cellText, $fontStyle);
        }
    }

    /**
     * Process signature table as formatted text layout
     */
    protected function processSignatureTable($section, \DOMElement $tableElement): void
    {
        $rows = $tableElement->getElementsByTagName('tr');
        
        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            
            if ($cells->length === 2) {
                // Two-column layout - process as side-by-side text
                $leftCell = $cells->item(0);
                $rightCell = $cells->item(1);
                
                $leftStyle = $leftCell->getAttribute('style');
                $rightStyle = $rightCell->getAttribute('style');
                
                // Create a text run for the row
                $textRun = $section->addTextRun([
                    'spaceBefore' => 120,
                    'spaceAfter' => 60
                ]);
                
                // Process left cell content
                $leftContent = $this->extractCleanTextFromElement($leftCell);
                if (!empty($leftContent)) {
                    $textRun->addText(
                        $leftContent,
                        [
                            'name' => 'Times New Roman',
                            'size' => 11
                        ]
                    );
                }
                
                // Add spacing between columns
                $textRun->addText(str_repeat(' ', 20), [
                    'name' => 'Times New Roman',
                    'size' => 11
                ]);
                
                // Process right cell content
                $rightContent = $this->extractCleanTextFromElement($rightCell);
                if (!empty($rightContent)) {
                    $textRun->addText(
                        $rightContent,
                        [
                            'name' => 'Times New Roman',
                            'size' => 11
                        ]
                    );
                }
            } else {
                // Single column or other layout - process normally
                foreach ($cells as $cell) {
                    $cellContent = $this->extractCleanTextFromElement($cell);
                    if (!empty($cellContent)) {
                        $section->addText(
                            $cellContent,
                            [
                                'name' => 'Times New Roman',
                                'size' => 11
                            ],
                            [
                                'spaceBefore' => 60,
                                'spaceAfter' => 60
                            ]
                        );
                    }
                }
            }
        }
        
        $section->addTextBreak();
    }

    /**
     * Extract clean text content from DOM element
     */
    protected function extractCleanTextFromElement(\DOMElement $element): string
    {
        $text = '';
        
        foreach ($element->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text .= $child->textContent;
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $childTag = strtolower($child->tagName);
                $childText = $child->textContent;
                
                // Handle formatting elements
                if (in_array($childTag, ['strong', 'b'])) {
                    $text .= $childText; // Note: formatting will be lost, but text preserved
                } elseif ($childTag === 'br') {
                    $text .= "\n";
                } else {
                    $text .= $childText;
                }
            }
        }
        
        // Clean up the text
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }

    /**
     * Add table to Word document
     */
    protected function addDocxTable($section, array $data): void
    {
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '999999']);
        
        foreach ($data as $row) {
            $table->addRow();
            $table->addCell(3000)->addText($row[0], ['bold' => true]);
            $table->addCell(6000)->addText($row[1]);
        }
    }

    /**
     * Export to RTF
     */
    protected function exportToRtf(Report $report, array $reportData): array
    {
        $rtf = "{\\rtf1\\ansi\\deff0\n";
        $rtf .= "{\\fonttbl{\\f0 Times New Roman;}}\n";
        $rtf .= "{\\colortbl;\\red31\\green73\\blue125;}\n";
        
        // Check if we have the new single-content structure
        if (isset($reportData['content']) && !isset($reportData['patient_information'])) {
            // New single-content structure - convert HTML to RTF-safe text
            $cleanContent = strip_tags($reportData['content']);
            $cleanContent = html_entity_decode($cleanContent, ENT_QUOTES, 'UTF-8');
            $cleanContent = str_replace(['\\', '{', '}'], ['\\\\', '\\{', '\\}'], $cleanContent);
            $rtf .= $cleanContent . "\\par\n";
        } else {
            // Legacy multi-section structure
            $rtf .= "\\viewkind4\\uc1\\pard\\sa200\\sl276\\slmult1\\qc\\cf1\\b\\fs36 MEG DIAGNOSTIC REPORT\\b0\\cf0\\fs22\\par\n";
            $rtf .= "\\qc\\par\n";
            
            // Case Information
            $rtf .= "\\pard\\sa200\\sl276\\slmult1\\b Case ID:\\b0  {$report->case_id}\\par\n";
            $rtf .= "\\b Hospital:\\b0  " . ($report->hospital->name ?? '') . "\\par\n";
            $rtf .= "\\b Report Date:\\b0  " . $report->created->format('F d, Y') . "\\par\n";
            $rtf .= "\\b Status:\\b0  " . ucfirst($report->status) . "\\par\n";
            $rtf .= "\\par\n";
            
            // Report Sections
            $sections = [
                'PATIENT INFORMATION' => $reportData['patient_information'] ?? '',
                'CLINICAL INDICATION' => $reportData['clinical_indication'] ?? '',
                'PROCEDURE PERFORMED' => $reportData['procedure_performed'] ?? '',
                'TECHNICAL PARAMETERS' => $reportData['technical_parameters'] ?? '',
                'FINDINGS' => $reportData['findings'] ?? '',
                'CONCLUSION' => $reportData['conclusion'] ?? '',
                'RECOMMENDATIONS' => $reportData['recommendations'] ?? '',
            ];
            
            foreach ($sections as $title => $content) {
                if (!empty($content)) {
                    $rtf .= "\\cf1\\b\\fs28 {$title}\\cf0\\b0\\fs22\\par\n";
                    $cleanContent = strip_tags($content);
                    $cleanContent = str_replace(['\\', '{', '}'], ['\\\\', '\\{', '\\}'], $cleanContent);
                    $rtf .= $cleanContent . "\\par\\par\n";
                }
            }
            
            if ($report->confidence_score) {
                $rtf .= "\\b Confidence Score:\\b0  {$report->confidence_score}%\\par\n";
            }
        }
        
        $rtf .= "}";
        
        $filename = $this->generateFilename($report, 'rtf');
        
        return [
            'content' => $rtf,
            'filename' => $filename,
            'mimeType' => 'application/rtf'
        ];
    }

    /**
     * Export to HTML
     */
    protected function exportToHtml(Report $report, array $reportData): array
    {
        $html = $this->generateHtmlContent($report, $reportData, false);
        $filename = $this->generateFilename($report, 'html');
        
        return [
            'content' => $html,
            'filename' => $filename,
            'mimeType' => 'text/html'
        ];
    }

    /**
     * Export to plain text
     */
    protected function exportToTxt(Report $report, array $reportData): array
    {
        // Check if we have the new single-content structure
        if (isset($reportData['content']) && !isset($reportData['patient_information'])) {
            // New single-content structure - convert HTML to plain text
            $text = "MEG DIAGNOSTIC REPORT\n";
            $text .= str_repeat("=", 80) . "\n\n";
            
            $text .= "Case ID: {$report->case_id}\n";
            $text .= "Hospital: " . ($report->hospital->name ?? '') . "\n";
            $text .= "Report Date: " . $report->created->format('F d, Y') . "\n";
            $text .= "Status: " . ucfirst($report->status) . "\n\n";
            
            // Convert HTML content to plain text
            $cleanContent = strip_tags($reportData['content']);
            $cleanContent = html_entity_decode($cleanContent, ENT_QUOTES, 'UTF-8');
            $text .= $cleanContent . "\n\n";
            
            if ($report->confidence_score) {
                $text .= "Confidence Score: {$report->confidence_score}%\n";
            }
        } else {
            // Legacy multi-section structure
            $text = "MEG DIAGNOSTIC REPORT\n";
            $text .= str_repeat("=", 80) . "\n\n";
            
            $text .= "Case ID: {$report->case_id}\n";
            $text .= "Hospital: " . ($report->hospital->name ?? '') . "\n";
            $text .= "Report Date: " . $report->created->format('F d, Y') . "\n";
            $text .= "Status: " . ucfirst($report->status) . "\n\n";
            
            $sections = [
                'PATIENT INFORMATION' => $reportData['patient_information'] ?? '',
                'CLINICAL INDICATION' => $reportData['clinical_indication'] ?? '',
                'PROCEDURE PERFORMED' => $reportData['procedure_performed'] ?? '',
                'TECHNICAL PARAMETERS' => $reportData['technical_parameters'] ?? '',
                'FINDINGS' => $reportData['findings'] ?? '',
                'CONCLUSION' => $reportData['conclusion'] ?? '',
                'RECOMMENDATIONS' => $reportData['recommendations'] ?? '',
            ];
            
            foreach ($sections as $title => $content) {
                if (!empty($content)) {
                    $text .= "{$title}\n";
                    $text .= str_repeat("-", 80) . "\n";
                    $cleanContent = strip_tags($content);
                    $text .= $cleanContent . "\n\n";
                }
            }
            
            if ($report->confidence_score) {
                $text .= "Confidence Score: {$report->confidence_score}%\n";
            }
        }
        
        $text .= "\n" . str_repeat("=", 80) . "\n";
        $text .= "Report ID: {$report->id}\n";
        $text .= "Generated: " . date('F d, Y g:i A') . "\n";
        
        $filename = $this->generateFilename($report, 'txt');
        
        return [
            'content' => $text,
            'filename' => $filename,
            'mimeType' => 'text/plain'
        ];
    }

    /**
     * Generate HTML content for report
     */
    protected function generateHtmlContent(Report $report, array $reportData, bool $forPdf = false): string
    {
        $styles = $forPdf ? $this->getPdfStyles() : $this->getHtmlStyles();
        
        $html = "<!DOCTYPE html>\n<html>\n<head>\n";
        $html .= "<meta charset='UTF-8'>\n";
        $html .= "<title>MEG Report - Case {$report->case_id}</title>\n";
        $html .= "<style>{$styles}</style>\n";
        $html .= "</head>\n<body>\n";
        
        // Check if we have the new single-content structure or old multi-section structure
        if (isset($reportData['content']) && !isset($reportData['patient_information'])) {
            // New single-content structure - use the content directly
            $html .= "<div class='report-content'>\n";
            $html .= $reportData['content'];
            $html .= "\n</div>\n";
        } else {
            // Legacy multi-section structure
            $html .= "<div class='header'>\n";
            $html .= "<h1>MEG DIAGNOSTIC REPORT</h1>\n";
            $html .= "</div>\n";
            
            $html .= "<table class='info-table'>\n";
            $html .= "<tr><td><strong>Case ID:</strong></td><td>{$report->case_id}</td></tr>\n";
            $html .= "<tr><td><strong>Hospital:</strong></td><td>" . ($report->hospital->name ?? '') . "</td></tr>\n";
            $html .= "<tr><td><strong>Report Date:</strong></td><td>" . $report->created->format('F d, Y') . "</td></tr>\n";
            $html .= "<tr><td><strong>Status:</strong></td><td>" . ucfirst($report->status) . "</td></tr>\n";
            $html .= "</table>\n";
            
            $sections = [
                'PATIENT INFORMATION' => $reportData['patient_information'] ?? '',
                'CLINICAL INDICATION' => $reportData['clinical_indication'] ?? '',
                'PROCEDURE PERFORMED' => $reportData['procedure_performed'] ?? '',
                'TECHNICAL PARAMETERS' => $reportData['technical_parameters'] ?? '',
                'FINDINGS' => $reportData['findings'] ?? '',
                'CONCLUSION' => $reportData['conclusion'] ?? '',
                'RECOMMENDATIONS' => $reportData['recommendations'] ?? '',
            ];
            
            foreach ($sections as $title => $content) {
                if (!empty($content)) {
                    $html .= "<div class='section'>\n";
                    $html .= "<h2>{$title}</h2>\n";
                    $html .= "<div class='content'>{$content}</div>\n";
                    $html .= "</div>\n";
                }
            }
            
            if ($report->confidence_score) {
                $html .= "<div class='confidence'>\n";
                $html .= "<strong>Confidence Score:</strong> {$report->confidence_score}%\n";
                $html .= "</div>\n";
            }
            
            $html .= "<div class='footer'>\n";
            $html .= "<p>Report ID: {$report->id} | Generated: " . date('F d, Y g:i A') . "</p>\n";
            $html .= "</div>\n";
        }
        
        $html .= "</body>\n</html>";
        
        return $html;
    }

    /**
     * Get CSS styles for PDF
     */
    protected function getPdfStyles(): string
    {
        return "
            body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.6; margin: 20px; color: #000; }
            .report-content { font-family: 'Times New Roman', serif; line-height: 1.6; }
            .report-content h1, .report-content h2, .report-content h3, .report-content h4, .report-content h5, .report-content h6 { 
                color: #000; page-break-after: avoid; margin-top: 1.5em; margin-bottom: 0.5em; 
            }
            .report-content p { margin-bottom: 1em; text-align: justify; }
            .report-content ul, .report-content ol { margin-bottom: 1em; padding-left: 2em; }
            .report-content li { margin-bottom: 0.5em; }
            .report-content table { width: 100%; border-collapse: collapse; margin: 1em 0; }
            .report-content table td, .report-content table th { border: 1px solid #000; padding: 8px; text-align: left; }
            .report-content table th { background-color: #f0f0f0; font-weight: bold; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #1F497D; padding-bottom: 10px; }
            h1 { color: #1F497D; font-size: 24pt; margin: 0; }
            h2 { color: #1F497D; font-size: 14pt; border-bottom: 2px solid #1F497D; padding-bottom: 5px; margin-top: 20px; }
            .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
            .info-table td { padding: 5px; border: 1px solid #ddd; }
            .info-table td:first-child { width: 150px; background-color: #f0f0f0; font-weight: bold; }
            .section { margin-bottom: 20px; }
            .content { padding: 10px; }
            .confidence { background-color: #e7f3ff; padding: 10px; margin-top: 20px; border-radius: 5px; }
            .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 9pt; color: #666; text-align: center; }
        ";
    }

    /**
     * Get CSS styles for HTML export
     */
    protected function getHtmlStyles(): string
    {
        return $this->getPdfStyles() . "
            body { max-width: 800px; margin: 40px auto; padding: 20px; background-color: #f5f5f5; }
            .header, .section, .info-table, .confidence { background-color: white; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
            .info-table { padding: 15px; }
        ";
    }

    /**
     * Generate filename for export
     */
    protected function generateFilename(Report $report, string $extension): string
    {
        $date = $report->created->format('Y-m-d');
        $caseId = $report->case_id;
        return "MEG_Report_Case_{$caseId}_{$date}.{$extension}";
    }
}
