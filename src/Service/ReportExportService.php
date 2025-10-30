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
            // New single-content structure - parse HTML and convert to Word formatting
            $this->convertHtmlToWord($section, $reportData['content']);
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
     * Clean HTML content for better Word conversion
     */
    protected function cleanHtmlForWord(string $html): string
    {
        // Remove PHP-specific content that shouldn't be in final output
        $html = preg_replace('/<\?php.*?\?>/s', '', $html);
        $html = preg_replace('/<\?=.*?\?>/s', '', $html);
        
        // Clean up HTML entities and whitespace issues that cause Word formatting problems
        $html = str_replace('&nbsp;', ' ', $html);
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove excessive whitespace and line breaks that cause indentation issues
        $html = preg_replace('/\s+/', ' ', $html);
        $html = preg_replace('/>\s+</', '><', $html);
        
        // Clean up problematic HTML patterns that cause Word formatting issues
        $html = preg_replace('/<br\s*\/?>\s*<br\s*\/?>/', '<br>', $html);
        
        // Remove empty paragraphs and divs
        $html = preg_replace('/<p[^>]*>\s*<\/p>/', '', $html);
        $html = preg_replace('/<div[^>]*>\s*<\/div>/', '', $html);
        
        // Clean up HTML indentation patterns that show up in Word
        $html = preg_replace('/(&nbsp;){2,}/', ' ', $html);
        $html = preg_replace('/\s{2,}/', ' ', $html);
        
        return trim($html);
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
     * Process individual HTML elements for Word conversion
     */
    protected function processWordElement($section, \DOMElement $element): void
    {
        $tagName = strtolower($element->tagName);
        
        switch ($tagName) {
            case 'div':
                // Handle main content divs
                $style = $element->getAttribute('style');
                $class = $element->getAttribute('class');
                
                // Check if this is a signature block or special formatting
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
                
            case 'h1':
                $text = trim($element->textContent);
                if (!empty($text)) {
                    $section->addText(
                        $text,
                        [
                            'bold' => true, 
                            'size' => 16, 
                            'color' => '1F497D', 
                            'name' => 'Times New Roman'
                        ],
                        [
                            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
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
                    $section->addText(
                        $text,
                        [
                            'bold' => true, 
                            'size' => $size, 
                            'color' => '1F497D', 
                            'name' => 'Times New Roman'
                        ],
                        [
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
                    $this->addWordText($section, $text, ['bold' => true]);
                }
                break;
                
            case 'em':
            case 'i':
                $text = trim($element->textContent);
                if (!empty($text)) {
                    $this->addWordText($section, $text, ['italic' => true]);
                }
                break;
                
            default:
                // For other elements, process their children
                $this->processWordNode($section, $element);
                break;
        }
    }

    /**
     * Process paragraph elements with proper formatting
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
                if (in_array($childTag, ['strong', 'b', 'em', 'i', 'u'])) {
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
            'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT
        ];
        
        if (strpos($style, 'text-align: center') !== false) {
            $paragraphStyle['alignment'] = \PhpOffice\PhpWord\SimpleType\Jc::CENTER;
        } elseif (strpos($style, 'text-align: justify') !== false) {
            $paragraphStyle['alignment'] = \PhpOffice\PhpWord\SimpleType\Jc::BOTH;
        } elseif (strpos($style, 'text-align: right') !== false) {
            $paragraphStyle['alignment'] = \PhpOffice\PhpWord\SimpleType\Jc::RIGHT;
        }
        
        // Remove any problematic indentation from HTML
        // Don't apply HTML padding/margin as Word indentation as it causes formatting issues
        
        // Font styling
        $fontStyle = [
            'name' => 'Times New Roman',
            'size' => 11
        ];
        
        if ($hasFormatting) {
            // For paragraphs with mixed formatting, process each child separately
            $this->processWordParagraphWithFormatting($section, $element, $paragraphStyle);
        } else {
            // Simple paragraph - clean the text thoroughly
            $section->addText($textContent, $fontStyle, $paragraphStyle);
        }
    }

    /**
     * Process paragraph with mixed formatting (bold, italic, etc.)
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
                    $textRun->addText($text, [
                        'name' => 'Times New Roman',
                        'size' => 11
                    ]);
                }
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $childTag = strtolower($child->tagName);
                $text = $child->textContent;
                
                // Clean up text content thoroughly
                $text = preg_replace('/\s+/', ' ', $text);
                $text = trim($text);
                
                if (!empty($text)) {
                    $fontStyle = [
                        'name' => 'Times New Roman',
                        'size' => 11
                    ];
                    
                    switch ($childTag) {
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
                    }
                    
                    $textRun->addText($text, $fontStyle);
                }
            }
        }
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
     * Process list item with mixed formatting
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
        
        // Add the bullet/number prefix
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
                    $textRun->addText($text, [
                        'name' => 'Times New Roman',
                        'size' => 11
                    ]);
                }
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                $childTag = strtolower($child->tagName);
                $childText = $child->textContent;
                $childText = preg_replace('/\s+/', ' ', $childText);
                $childText = trim($childText);
                
                if (!empty($childText)) {
                    $fontStyle = [
                        'name' => 'Times New Roman',
                        'size' => 11
                    ];
                    
                    switch ($childTag) {
                        case 'strong':
                        case 'b':
                            $fontStyle['bold'] = true;
                            break;
                        case 'em':
                        case 'i':
                            $fontStyle['italic'] = true;
                            break;
                        case 'br':
                            $textRun->addTextBreak();
                            continue 2;
                    }
                    
                    $textRun->addText($childText, $fontStyle);
                }
            }
        }
    }

    /**
     * Add formatted text to Word document
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
     * Process HTML table and convert to Word table
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
        
        // Regular data table with borders
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
                $cellText = trim($cell->textContent);
                $isHeader = $cell->tagName === 'th' || $rowIndex === 0;
                
                $cellElement = $table->addCell(null, ['bgColor' => $isHeader ? 'f8f9fa' : 'ffffff']);
                $cellElement->addText(
                    $cellText,
                    [
                        'bold' => $isHeader,
                        'name' => 'Times New Roman',
                        'size' => 10
                    ]
                );
            }
        }
        
        $section->addTextBreak();
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
