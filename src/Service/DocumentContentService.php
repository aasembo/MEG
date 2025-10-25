<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Log\Log;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Document Content Extraction Service
 * 
 * Extracts text content from documents using OCR and NLP
 * IMPORTANT: All processing done LOCALLY - no content sent to external APIs
 */
class DocumentContentService
{
    /**
     * Extract text content from a document
     *
     * @param object $document Document entity with file_path and file_type
     * @return array ['success' => bool, 'text' => string, 'metadata' => array, 'analysis' => array, 'error' => string]
     */
    public function extractContent($document): array
    {
        try {
            $filePath = $this->getDocumentPath($document->file_path);
            
            Log::debug('DocumentContentService: Attempting to extract from: ' . $filePath);
            Log::debug('DocumentContentService: File type: ' . $document->file_type);
            
            if (!file_exists($filePath)) {
                Log::error('DocumentContentService: File not found: ' . $filePath);
                return [
                    'success' => false,
                    'text' => '',
                    'metadata' => [],
                    'analysis' => [],
                    'error' => 'File not found: ' . $filePath
                ];
            }

            $fileType = strtolower($document->file_type);
            
            // Route to appropriate extraction method based on file type
            $result = null;
            if (str_contains($fileType, 'pdf')) {
                Log::debug('DocumentContentService: Routing to PDF extraction');
                $result = $this->extractFromPdf($filePath);
            } elseif (str_contains($fileType, 'image') || 
                      in_array($fileType, ['image/jpeg', 'image/png', 'image/tiff'])) {
                Log::debug('DocumentContentService: Routing to Image/OCR extraction');
                $result = $this->extractFromImage($filePath);
            } elseif (str_contains($fileType, 'text')) {
                Log::debug('DocumentContentService: Routing to Text extraction');
                $result = $this->extractFromText($filePath);
            } else {
                Log::warning('DocumentContentService: Unsupported file type: ' . $fileType);
                return [
                    'success' => false,
                    'text' => '',
                    'metadata' => ['file_type' => $fileType],
                    'analysis' => [],
                    'error' => 'Unsupported file type: ' . $fileType
                ];
            }
            
            Log::debug('DocumentContentService: Extraction result - Success: ' . 
                      ($result['success'] ? 'YES' : 'NO') . 
                      ', Text length: ' . (isset($result['text']) ? strlen($result['text']) : 0));
            
            // If extraction succeeded, analyze the content
            if ($result['success'] && !empty($result['text'])) {
                $result['analysis'] = $this->analyzeContent($result['text']);
            } else {
                $result['analysis'] = [];
            }
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Document content extraction failed: ' . $e->getMessage());
            return [
                'success' => false,
                'text' => '',
                'metadata' => [],
                'analysis' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract text from PDF files
     *
     * @param string $filePath Path to PDF file
     * @return array Extraction result
     */
    private function extractFromPdf(string $filePath): array
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);
            
            $text = $pdf->getText();
            $metadata = [
                'pages' => $pdf->getPages() ? count($pdf->getPages()) : 0,
                'file_type' => 'pdf',
                'extraction_method' => 'pdf_parser'
            ];

            return [
                'success' => true,
                'text' => $text,
                'metadata' => $metadata,
                'error' => ''
            ];
        } catch (\Exception $e) {
            Log::error('PDF extraction failed: ' . $e->getMessage());
            return [
                'success' => false,
                'text' => '',
                'metadata' => ['file_type' => 'pdf'],
                'error' => 'PDF extraction failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Extract text from image files using OCR
     *
     * @param string $filePath Path to image file
     * @return array Extraction result
     */
    private function extractFromImage(string $filePath): array
    {
        try {
            // Check if Tesseract OCR is available
            $tesseractPath = env('TESSERACT_PATH', 'tesseract');
            
            // Create temporary output file
            $outputFile = tempnam(sys_get_temp_dir(), 'ocr_');
            
            // Enhanced Tesseract command with better OCR options
            // --psm 3 = Fully automatic page segmentation, but no OSD (Orientation and Script Detection)
            // --oem 3 = Default, based on what is available (LSTM + Legacy)
            // -c preserve_interword_spaces=1 = Better preserve formatting
            $command = escapeshellcmd($tesseractPath) . ' ' . 
                       escapeshellarg($filePath) . ' ' . 
                       escapeshellarg($outputFile) . ' ' .
                       '--psm 3 --oem 3 ' .
                       '-c preserve_interword_spaces=1 ' .
                       '2>&1';
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                // Try fallback with simpler options
                Log::warning('OCR with advanced options failed, trying fallback');
                $command = escapeshellcmd($tesseractPath) . ' ' . 
                           escapeshellarg($filePath) . ' ' . 
                           escapeshellarg($outputFile) . ' 2>&1';
                exec($command, $output, $returnCode);
                
                if ($returnCode !== 0) {
                    throw new \Exception('OCR failed: ' . implode("\n", $output));
                }
            }

            // Read extracted text
            $textFile = $outputFile . '.txt';
            $text = file_exists($textFile) ? file_get_contents($textFile) : '';
            
            // Clean up temporary files
            @unlink($outputFile);
            @unlink($textFile);
            
            // Get image metadata
            $imageInfo = @getimagesize($filePath);
            $metadata = [
                'file_type' => 'image',
                'extraction_method' => 'tesseract_ocr',
                'ocr_enhanced' => true
            ];
            
            if ($imageInfo) {
                $metadata['image_width'] = $imageInfo[0];
                $metadata['image_height'] = $imageInfo[1];
                $metadata['image_type'] = image_type_to_mime_type($imageInfo[2]);
            }

            return [
                'success' => true,
                'text' => $text,
                'metadata' => $metadata,
                'error' => ''
            ];
        } catch (\Exception $e) {
            Log::error('Image OCR extraction failed: ' . $e->getMessage());
            
            // Return empty content if OCR fails
            return [
                'success' => false,
                'text' => '',
                'metadata' => ['file_type' => 'image'],
                'error' => 'OCR extraction failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Extract text from plain text files
     *
     * @param string $filePath Path to text file
     * @return array Extraction result
     */
    private function extractFromText(string $filePath): array
    {
        try {
            $text = file_get_contents($filePath);
            
            return [
                'success' => true,
                'text' => $text,
                'metadata' => [
                    'file_type' => 'text',
                    'extraction_method' => 'file_get_contents'
                ],
                'error' => ''
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'text' => '',
                'metadata' => ['file_type' => 'text'],
                'error' => 'Text extraction failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Analyze document content using NLP
     * Identifies medical terms, findings, measurements
     *
     * @param string $content Document text content
     * @return array ['findings' => array, 'terms' => array, 'summary' => string]
     */
    public function analyzeContent(string $content): array
    {
        if (empty($content)) {
            return [
                'findings' => [],
                'terms' => [],
                'summary' => ''
            ];
        }

        // Extract medical terms and findings
        $findings = $this->extractFindings($content);
        $terms = $this->extractMedicalTerms($content);
        $summary = $this->generateSummary($content);

        return [
            'findings' => $findings,
            'terms' => $terms,
            'summary' => $summary
        ];
    }

    /**
     * Extract key findings from content
     *
     * @param string $content Document content
     * @return array List of findings
     */
    private function extractFindings(string $content): array
    {
        $findings = [];
        
        // Common medical finding patterns
        $patterns = [
            '/(?:found|identified|observed|noted|showed|demonstrated|revealed):?\s+([^.]+)/i',
            '/(?:conclusion|impression|diagnosis):?\s+([^.]+)/i',
            '/(?:results?|findings?):?\s+([^.]+)/i',
        ];

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $matches);
            if (!empty($matches[1])) {
                $findings = array_merge($findings, $matches[1]);
            }
        }

        return array_unique(array_map('trim', $findings));
    }

    /**
     * Extract medical terminology from content
     *
     * @param string $content Document content
     * @return array List of medical terms
     */
    private function extractMedicalTerms(string $content): array
    {
        // Common MEG/neurology terms
        $medicalTerms = [
            'magnetoencephalography', 'meg', 'epileptiform', 'interictal', 'ictal',
            'somatosensory', 'motor', 'auditory', 'visual', 'language',
            'cortex', 'cortical', 'hemisphere', 'frontal', 'temporal', 'parietal', 'occipital',
            'dipole', 'localization', 'source', 'activity', 'rhythms',
            'seizure', 'spike', 'wave', 'discharge', 'abnormality',
            'eeg', 'mri', 'fmri', 'electroencephalography',
            'neuromag', 'sensor', 'gradiometer', 'magnetometer'
        ];

        $foundTerms = [];
        $contentLower = strtolower($content);

        foreach ($medicalTerms as $term) {
            if (str_contains($contentLower, $term)) {
                $foundTerms[] = $term;
            }
        }

        return array_unique($foundTerms);
    }

    /**
     * Generate a brief summary of document content
     *
     * @param string $content Document content
     * @return string Summary (max 500 chars)
     */
    private function generateSummary(string $content): string
    {
        // Get first few sentences
        $sentences = preg_split('/[.!?]+/', $content, 4);
        $summary = implode('. ', array_slice($sentences, 0, 3));
        
        // Truncate if too long
        if (strlen($summary) > 500) {
            $summary = substr($summary, 0, 497) . '...';
        }

        return trim($summary);
    }

    /**
     * Get actual file path from stored path
     * Handles both S3 and local storage
     *
     * @param string|null $storedPath Path stored in database
     * @return string Actual file path
     */
    private function getDocumentPath(?string $storedPath): string
    {
        // Handle null or empty path
        if (empty($storedPath)) {
            throw new \InvalidArgumentException('Document path cannot be empty');
        }
        
        // If S3 path, download to temp location
        if (str_starts_with($storedPath, 's3://') || str_starts_with($storedPath, 'https://')) {
            return $this->downloadFromS3($storedPath);
        }

        // Local storage path
        // Check if storedPath already starts with 'uploads/' to avoid duplication
        if (str_starts_with($storedPath, 'uploads/') || str_starts_with($storedPath, 'uploads' . DS)) {
            // Path already includes uploads directory
            return WWW_ROOT . $storedPath;
        }
        
        // Path doesn't include uploads directory
        $uploadsPath = env('UPLOAD_PATH', WWW_ROOT . 'uploads' . DS);
        return $uploadsPath . $storedPath;
    }

    /**
     * Download file from S3 to temporary location for processing
     *
     * @param string $s3Path S3 path or URL
     * @return string Local temporary file path
     */
    private function downloadFromS3(string $s3Path): string
    {
        try {
            $s3Service = new \App\Lib\S3DocumentService();
            
            // Get download URL
            $downloadUrl = str_starts_with($s3Path, 'https://') 
                ? $s3Path 
                : $s3Service->getDownloadUrl($s3Path);

            // Download to temp file
            $tempFile = tempnam(sys_get_temp_dir(), 'doc_');
            $content = file_get_contents($downloadUrl);
            file_put_contents($tempFile, $content);

            return $tempFile;
        } catch (\Exception $e) {
            Log::error('S3 download failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
