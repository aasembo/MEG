<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Log\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Document Analysis Service
 * 
 * Analyzes uploaded documents to auto-detect document type and suggest procedure linking
 * using OCR (Optical Character Recognition) and NLP (Natural Language Processing)
 */
class DocumentAnalysisService
{
    private $enabled;

    // Document type keywords for NLP classification
    private $documentTypeKeywords = [
        'report' => ['report', 'findings', 'impression', 'conclusion', 'summary', 'results', 'analysis', 'examination'],
        'image' => ['image', 'scan', 'x-ray', 'xray', 'mri', 'ct scan', 'ultrasound', 'radiograph'],
        'dicom' => ['dicom', 'dcm', 'medical imaging', 'pacs'],
        'consent' => ['consent', 'authorization', 'permission', 'agree', 'acknowledge', 'signature'],
        'lab_result' => ['lab', 'laboratory', 'blood test', 'urinalysis', 'culture', 'specimen', 'test result', 'hemoglobin', 'white blood', 'platelets', 'hematology', 'chemistry', 'blood count'],
        'prescription' => ['prescription', 'medication', 'drug', 'dosage', 'pharmacy', 'refill', 'rx'],
        'referral' => ['referral', 'refer', 'consultation', 'specialist', 'appointment'],
        'pathology' => ['pathology', 'biopsy', 'tissue', 'microscopic', 'histology', 'cytology'],
        'radiology' => ['radiology', 'radiological', 'radiologist', 'imaging study', 'contrast'],
        'discharge_summary' => ['discharge', 'summary', 'hospital course', 'admission', 'patient was'],
        'other' => ['document', 'file', 'medical', 'patient', 'healthcare']
    ];

    // Procedure keywords for NLP matching
    private $procedureKeywords = [
        'mri' => ['mri', 'magnetic resonance', 'imaging'],
        'ct' => ['ct', 'computed tomography', 'cat scan'],
        'xray' => ['x-ray', 'xray', 'radiograph'],
        'ultrasound' => ['ultrasound', 'sonography', 'echo'],
        'endoscopy' => ['endoscopy', 'endoscopic', 'colonoscopy', 'gastroscopy'],
        'biopsy' => ['biopsy', 'tissue sample'],
        'blood' => ['blood test', 'blood work', 'hematology'],
        'ecg' => ['ecg', 'ekg', 'electrocardiogram', 'cardiac'],
    ];

    public function __construct()
    {
        // Check if Tesseract is available
        $this->enabled = $this->checkTesseractAvailable();
    }

    /**
     * Check if Tesseract OCR is available
     */
    private function checkTesseractAvailable(): bool
    {
        try {
            exec('tesseract --version 2>&1', $output, $returnCode);
            return $returnCode === 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Analyze document to detect type and suggest procedure linking
     *
     * @param array $fileData Uploaded file data from form
     * @param array $availableProcedures List of procedures assigned to the case
     * @return array Analysis results with detected_type and suggested_procedure_id
     */
    public function analyzeDocument(array $fileData, array $availableProcedures = []): array
    {
        try {
            // Get file information
            $fileName = $fileData['name'] ?? '';
            $fileType = $fileData['type'] ?? '';
            $tmpName = $fileData['tmp_name'] ?? '';

            if (empty($tmpName) || !file_exists($tmpName)) {
                return ['success' => false, 'error' => 'File not found'];
            }

            // Determine analysis method based on file type
            $isImage = $this->isImageFile($fileType);
            $isPdf = $this->isPdfFile($fileType);

            $extractedText = '';

            if ($isImage && $this->enabled) {
                $extractedText = $this->extractTextFromImage($tmpName);
            } elseif ($isPdf) {
                $extractedText = $this->extractTextFromPdf($tmpName);
            } else {
                // Text file or unsupported format
                $extractedText = @file_get_contents($tmpName) ?: '';
            }

            // If no text extracted, fall back to filename analysis
            if (empty(trim($extractedText))) {
                return $this->analyzeByFilename($fileName, $availableProcedures);
            }

            // Analyze extracted text using NLP
            return $this->analyzeTextWithNLP($extractedText, $fileName, $availableProcedures);

        } catch (\Exception $e) {
            Log::error('Document Analysis Error: ' . $e->getMessage());
            // Fallback to filename analysis
            return $this->analyzeByFilename($fileData['name'] ?? '', $availableProcedures);
        }
    }

    /**
     * Extract text from image using Tesseract OCR
     */
    private function extractTextFromImage(string $filePath): string
    {
        try {
            $ocr = new TesseractOCR($filePath);
            $ocr->lang('eng');
            $text = $ocr->run();
            
            Log::info('OCR extracted ' . strlen($text) . ' characters from image');
            return $text;
        } catch (\Exception $e) {
            Log::error('OCR Error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Extract text from PDF using PDF parser
     */
    private function extractTextFromPdf(string $filePath): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();
            
            Log::info('PDF parser extracted ' . strlen($text) . ' characters from PDF');
            return $text;
        } catch (\Exception $e) {
            Log::error('PDF Parse Error: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Analyze extracted text using NLP to detect document type and procedure
     */
    private function analyzeTextWithNLP(string $text, string $fileName, array $procedures): array
    {
        // Normalize text for analysis
        $normalizedText = strtolower(trim($text));
        
        // Detect document type using keyword matching
        $detectedType = $this->detectDocumentType($normalizedText, $fileName);
        
        // Suggest procedure based on text content
        $suggestedProcedure = $this->suggestProcedure($normalizedText, $procedures);
        
        // Calculate confidence based on keyword matches
        $confidence = $this->calculateConfidence($normalizedText, $detectedType);
        
        // Generate a description from extracted text
        $description = $this->generateDescription($text, $detectedType);
        
        Log::info('NLP Analysis: Type=' . $detectedType . ', Confidence=' . $confidence);
        
        return [
            'success' => true,
            'detected_type' => $detectedType,
            'suggested_procedure_id' => $suggestedProcedure,
            'suggested_description' => $description,
            'confidence' => $confidence,
            'method' => $this->enabled ? 'OCR + NLP' : 'PDF Parser + NLP',
            'text_length' => strlen($text)
        ];
    }

    /**
     * Detect document type using keyword-based NLP
     */
    private function detectDocumentType(string $text, string $fileName): string
    {
        $scores = [];
        
        // Score each document type based on keyword matches
        foreach ($this->documentTypeKeywords as $type => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                // Count occurrences of each keyword
                $count = substr_count($text, strtolower($keyword));
                $score += $count;
                
                // Bonus points if keyword is in filename
                if (stripos($fileName, $keyword) !== false) {
                    $score += 5;
                }
            }
            $scores[$type] = $score;
        }
        
        // Get type with highest score
        arsort($scores);
        $topType = array_key_first($scores);
        
        // If no matches found, default to 'other'
        if ($scores[$topType] === 0) {
            return 'other';
        }
        
        return $topType;
    }

    /**
     * Suggest procedure based on text content
     */
    private function suggestProcedure(string $text, array $procedures): ?int
    {
        if (empty($procedures)) {
            return null;
        }
        
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($procedures as $procedureId => $procedureName) {
            $score = 0;
            $normalizedProcedure = strtolower($procedureName);
            
            // Check if procedure name appears in text
            if (stripos($text, $procedureName) !== false) {
                $score += 10;
            }
            
            // Check for procedure-specific keywords
            foreach ($this->procedureKeywords as $category => $keywords) {
                foreach ($keywords as $keyword) {
                    if (stripos($normalizedProcedure, $keyword) !== false) {
                        // This procedure is related to this keyword category
                        // Check if keyword appears in text
                        $count = substr_count($text, strtolower($keyword));
                        $score += $count * 2;
                    }
                }
            }
            
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestMatch = $procedureId;
            }
        }
        
        // Only return match if confidence is reasonable
        return $bestScore > 0 ? $bestMatch : null;
    }

    /**
     * Calculate confidence score for document type detection
     */
    private function calculateConfidence(string $text, string $detectedType): float
    {
        if (!isset($this->documentTypeKeywords[$detectedType])) {
            return 0.3;
        }
        
        $keywords = $this->documentTypeKeywords[$detectedType];
        $matchCount = 0;
        $totalKeywords = count($keywords);
        
        foreach ($keywords as $keyword) {
            if (stripos($text, $keyword) !== false) {
                $matchCount++;
            }
        }
        
        // Calculate confidence (0.0 to 1.0)
        $baseConfidence = $matchCount / $totalKeywords;
        
        // Boost confidence if multiple keywords found
        if ($matchCount > 3) {
            $baseConfidence = min(1.0, $baseConfidence + 0.2);
        }
        
        // Ensure minimum confidence
        return max(0.4, min(1.0, $baseConfidence));
    }

    /**
     * Analyze document based on filename patterns (fallback method)
     */
    private function analyzeByFilename(string $fileName, array $procedures): array
    {
        $fileName = strtolower($fileName);
        $detectedType = 'other';
        $confidence = 0.5;

        // Pattern matching for document types
        $patterns = [
            'report' => ['report', 'medical_report', 'exam_report'],
            'image' => ['scan', 'xray', 'x-ray', 'mri', 'ct', 'image', 'img'],
            'dicom' => ['dicom', 'dcm'],
            'consent' => ['consent', 'authorization', 'agreement'],
            'lab_result' => ['lab', 'blood', 'test_result', 'laboratory'],
            'prescription' => ['prescription', 'rx', 'medication'],
            'referral' => ['referral', 'refer'],
            'pathology' => ['pathology', 'biopsy', 'histology'],
            'radiology' => ['radiology', 'rad_report'],
            'discharge_summary' => ['discharge', 'summary']
        ];

        foreach ($patterns as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($fileName, $keyword) !== false) {
                    $detectedType = $type;
                    $confidence = 0.7;
                    break 2;
                }
            }
        }

        // Try to match with procedures
        $suggestedProcedure = null;
        foreach ($procedures as $procId => $procName) {
            $procNameLower = strtolower($procName);
            // Extract key terms from procedure name
            $terms = explode(' ', $procNameLower);
            foreach ($terms as $term) {
                if (strlen($term) > 3 && strpos($fileName, $term) !== false) {
                    $suggestedProcedure = $procId;
                    break 2;
                }
            }
        }

        return [
            'success' => true,
            'detected_type' => $detectedType,
            'suggested_procedure_id' => $suggestedProcedure,
            'confidence' => $confidence,
            'method' => 'filename_pattern',
            'explanation' => 'Analyzed based on filename patterns'
        ];
    }

    /**
     * Generate a description/summary from extracted text
     * 
     * @param string $text Extracted text from document
     * @param string $detectedType Detected document type
     * @return string Generated description
     */
    private function generateDescription(string $text, string $detectedType): string
    {
        // Clean and prepare text
        $text = trim($text);
        
        if (empty($text)) {
            return '';
        }
        
        // Split into sentences
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        if (empty($sentences)) {
            // If no sentence boundaries, take first 200 characters
            return mb_substr($text, 0, 200) . (mb_strlen($text) > 200 ? '...' : '');
        }
        
        // Find the most relevant sentences based on document type
        $relevantSentences = [];
        $keyPhrases = $this->getKeyPhrasesForType($detectedType);
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (empty($sentence) || strlen($sentence) < 10) {
                continue;
            }
            
            // Score sentence based on key phrases
            $score = 0;
            $lowerSentence = strtolower($sentence);
            
            foreach ($keyPhrases as $phrase) {
                if (stripos($lowerSentence, $phrase) !== false) {
                    $score += 1;
                }
            }
            
            // Add sentences with scores or first few sentences
            if ($score > 0 || count($relevantSentences) < 2) {
                $relevantSentences[] = [
                    'text' => $sentence,
                    'score' => $score
                ];
            }
        }
        
        // Sort by score (highest first)
        usort($relevantSentences, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        // Take top 2-3 sentences
        $description = '';
        $maxLength = 300;
        $sentenceCount = 0;
        
        foreach ($relevantSentences as $item) {
            if ($sentenceCount >= 3) {
                break;
            }
            
            $sentence = $item['text'];
            if (strlen($description) + strlen($sentence) > $maxLength) {
                break;
            }
            
            $description .= $sentence . ' ';
            $sentenceCount++;
        }
        
        $description = trim($description);
        
        // If still empty, use first 200 characters
        if (empty($description)) {
            $description = mb_substr($text, 0, 200);
            if (mb_strlen($text) > 200) {
                // Try to break at sentence or word boundary
                $lastPeriod = strrpos($description, '.');
                $lastSpace = strrpos($description, ' ');
                if ($lastPeriod !== false && $lastPeriod > 100) {
                    $description = mb_substr($description, 0, $lastPeriod + 1);
                } elseif ($lastSpace !== false && $lastSpace > 100) {
                    $description = mb_substr($description, 0, $lastSpace) . '...';
                } else {
                    $description .= '...';
                }
            }
        }
        
        return $description;
    }

    /**
     * Get key phrases for a document type to help identify relevant content
     * 
     * @param string $docType Document type
     * @return array Key phrases
     */
    private function getKeyPhrasesForType(string $docType): array
    {
        $phrases = [
            'report' => ['findings', 'impression', 'conclusion', 'results', 'diagnosis', 'examination', 'clinical'],
            'lab_result' => ['results', 'test', 'normal', 'abnormal', 'range', 'value', 'specimen'],
            'pathology' => ['diagnosis', 'specimen', 'microscopic', 'gross', 'impression', 'tissue'],
            'radiology' => ['findings', 'impression', 'technique', 'comparison', 'indication'],
            'discharge_summary' => ['admitted', 'discharged', 'diagnosis', 'hospital course', 'medications', 'follow-up'],
            'prescription' => ['medication', 'dosage', 'instructions', 'refill', 'pharmacy'],
            'consent' => ['consent', 'agree', 'understand', 'risks', 'benefits', 'procedure'],
            'referral' => ['referred', 'consultation', 'specialist', 'evaluation', 'appointment'],
        ];
        
        return $phrases[$docType] ?? ['patient', 'medical', 'treatment', 'diagnosis', 'findings'];
    }

    /**
     * Check if file is an image
     */
    private function isImageFile(string $mimeType): bool
    {
        return in_array($mimeType, [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/bmp',
            'image/webp'
        ]);
    }

    /**
     * Check if file is a PDF
     */
    private function isPdfFile(string $mimeType): bool
    {
        return $mimeType === 'application/pdf';
    }

    /**
     * Check if service is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
