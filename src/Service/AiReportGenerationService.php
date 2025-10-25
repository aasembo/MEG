<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Log\Log;
use Cake\Core\Configure;

/**
 * AI Report Generation Service
 * 
 * Generates report structures using OpenAI with COMPLIANCE-SAFE metadata only
 * NO PHI/PII is ever sent to OpenAI
 * 
 * Includes fallback for when OpenAI is disabled
 */
class AiReportGenerationService
{
    private bool $enabled;
    private ?string $apiKey;
    private string $model;
    private float $temperature;

    public function __construct()
    {
        $this->enabled = (bool) env('OPENAI_REPORT_ENABLED');
        $this->apiKey = env('OPENAI_API_KEY');
        $this->model = env('OPENAI_REPORT_MODEL');
        $this->temperature = (float) env('OPENAI_REPORT_TEMPERATURE');
    }

    /**
     * Check if AI report generation is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->apiKey);
    }

    /**
     * Generate report structure using OpenAI
     * IMPORTANT: Only sends de-identified metadata - NO PHI/PII
     *
     * @param array $caseMetadata De-identified case metadata
     * @return array Report structure
     */
    public function generateReportStructure(array $caseMetadata): array
    {
        if (!$this->isEnabled()) {
            return $this->generateReportLocal($caseMetadata);
        }

        try {
            // Validate no PHI/PII in metadata
            $this->validateMetadata($caseMetadata);

            // Generate report structure from OpenAI
            $prompt = $this->buildPrompt($caseMetadata);
            $response = $this->callOpenAI($prompt);

            // Parse and structure the response
            $structure = $this->parseOpenAIResponse($response);

            Log::write('info', 'AI report structure generated successfully');
            
            return $structure;

        } catch (\Exception $e) {
            Log::error('AI report generation failed: ' . $e->getMessage());
            
            // Fallback to local generation
            return $this->generateReportLocal($caseMetadata);
        }
    }

    /**
     * Determine report type from procedures
     *
     * @param array $procedures List of procedure names
     * @return string Report type (MEG, EEG, etc.)
     */
    public function determineReportType(array $procedures): string
    {
        $procedureStr = strtolower(implode(' ', $procedures));

        // Check for specific keywords
        if (str_contains($procedureStr, 'meg') || 
            str_contains($procedureStr, 'magnetoencephalography')) {
            return 'MEG';
        } elseif (str_contains($procedureStr, 'eeg') || 
                  str_contains($procedureStr, 'electroencephalography')) {
            return 'EEG';
        } elseif (str_contains($procedureStr, 'mri')) {
            return 'MRI';
        } elseif (str_contains($procedureStr, 'ct') || 
                  str_contains($procedureStr, 'computed tomography')) {
            return 'CT';
        } else {
            return 'CLINICAL';
        }
    }

    /**
     * Generate report name
     *
     * @param array $metadata Case metadata
     * @return string Professional report name
     */
    public function generateReportName(array $metadata): string
    {
        $reportType = $metadata['report_type'] ?? 'Clinical';
        
        $names = [
            'MEG' => 'Magnetoencephalography Clinical Report',
            'EEG' => 'Electroencephalography Report',
            'MRI' => 'Magnetic Resonance Imaging Report',
            'CT' => 'Computed Tomography Report',
            'CLINICAL' => 'Clinical Assessment Report'
        ];

        return $names[$reportType] ?? 'Medical Report';
    }

    /**
     * Fallback report generation without OpenAI
     * Uses template-based approach
     *
     * @param array $caseMetadata Case metadata
     * @return array Report structure
     */
    public function generateReportLocal(array $caseMetadata): array
    {
        $reportType = $caseMetadata['report_type'] ?? 'CLINICAL';
        $procedures = $caseMetadata['procedures'] ?? [];

        // Build structure based on report type
        $structure = [
            'report_name' => $this->generateReportName($caseMetadata),
            'report_type' => $reportType,
            'sections' => $this->getDefaultSections($reportType, $procedures),
            'formatting' => [
                'font' => 'Times New Roman',
                'title_size' => '16pt',
                'section_size' => '12pt',
                'body_size' => '11pt'
            ]
        ];

        return $structure;
    }

    /**
     * Build prompt for OpenAI
     * Only includes de-identified metadata
     *
     * @param array $metadata De-identified metadata
     * @return string Prompt
     */
    private function buildPrompt(array $metadata): string
    {
        $reportType = $metadata['report_type'] ?? 'CLINICAL';
        $procedures = implode(', ', $metadata['procedures'] ?? []);
        $documentCount = $metadata['document_count'] ?? 0;
        $ageCategory = $metadata['age_category'] ?? 'adult';

        $prompt = "Generate a professional medical report structure for:\n\n";
        $prompt .= "Report Type: {$reportType}\n";
        $prompt .= "Procedures Performed: {$procedures}\n";
        $prompt .= "Patient Category: {$ageCategory}\n";
        $prompt .= "Documents Attached: {$documentCount}\n\n";
        $prompt .= "Return a JSON structure with this exact format:\n";
        $prompt .= "{\n";
        $prompt .= "  \"report_name\": \"Report Title\",\n";
        $prompt .= "  \"report_type\": \"{$reportType}\",\n";
        $prompt .= "  \"sections\": [\n";
        $prompt .= "    {\n";
        $prompt .= "      \"title\": \"Section Name\",\n";
        $prompt .= "      \"required\": true,\n";
        $prompt .= "      \"content_type\": \"symptoms_and_history\" (optional, use for: symptoms_and_history, procedure_list, procedure_findings, document_summaries, conclusions),\n";
        $prompt .= "      \"subsections\": [\"Subsection 1\", \"Subsection 2\"] (optional, array of strings)\n";
        $prompt .= "    }\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"formatting\": {\n";
        $prompt .= "    \"font\": \"Times New Roman\",\n";
        $prompt .= "    \"title_size\": \"16pt\",\n";
        $prompt .= "    \"section_size\": \"12pt\"\n";
        $prompt .= "  }\n";
        $prompt .= "}\n\n";
        $prompt .= "Include sections appropriate for a {$reportType} report. DO NOT include any patient data, only structure.";

        return $prompt;
    }

    /**
     * Call OpenAI API
     *
     * @param string $prompt Prompt to send
     * @return string Raw response
     */
    private function callOpenAI(string $prompt): string
    {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a medical report formatting assistant. Generate report structures only, never include patient data. Return valid JSON only.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $this->temperature,
            'max_tokens' => 2000
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('OpenAI API error: HTTP ' . $httpCode);
        }

        $result = json_decode($response, true);
        
        if (empty($result['choices'][0]['message']['content'])) {
            throw new \Exception('Empty response from OpenAI');
        }

        return $result['choices'][0]['message']['content'];
    }

    /**
     * Parse OpenAI response into structured format
     *
     * @param string $response Raw response from OpenAI
     * @return array Structured report format
     */
    private function parseOpenAIResponse(string $response): array
    {
        // Extract JSON from response (may have markdown formatting)
        $response = preg_replace('/^```json\s*/m', '', $response);
        $response = preg_replace('/\s*```$/m', '', $response);
        $response = trim($response);

        $structure = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Failed to parse OpenAI response as JSON');
        }

        // Validate required fields
        if (empty($structure['sections'])) {
            throw new \Exception('Invalid report structure from OpenAI');
        }

        // Normalize the structure to match expected format
        $structure['sections'] = $this->normalizeSections($structure['sections']);

        return $structure;
    }

    /**
     * Normalize section structure from AI response
     * Handles different formats AI might return
     *
     * @param array $sections Raw sections from AI
     * @return array Normalized sections
     */
    private function normalizeSections(array $sections): array
    {
        $normalized = [];

        foreach ($sections as $section) {
            // Handle both 'title' and 'section_title'
            $title = $section['title'] ?? $section['section_title'] ?? 'Untitled Section';
            
            $normalizedSection = [
                'title' => $title,
                'required' => $section['required'] ?? false,
                'content_type' => $section['content_type'] ?? null,
            ];

            // Handle subsections if present
            if (!empty($section['subsections'])) {
                $normalizedSection['subsections'] = $this->normalizeSubsections($section['subsections']);
            }

            // Handle fields if present
            if (!empty($section['fields'])) {
                $normalizedSection['fields'] = $section['fields'];
            }

            // Handle content_items if present
            if (!empty($section['content_items'])) {
                $normalizedSection['content_items'] = $section['content_items'];
            }

            $normalized[] = $normalizedSection;
        }

        return $normalized;
    }

    /**
     * Normalize subsection structure
     *
     * @param array $subsections Raw subsections from AI
     * @return array Normalized subsections (array of strings or arrays)
     */
    private function normalizeSubsections(array $subsections): array
    {
        $normalized = [];

        foreach ($subsections as $subsection) {
            if (is_string($subsection)) {
                // Already a string title
                $normalized[] = $subsection;
            } elseif (is_array($subsection)) {
                // Extract title from array format
                $title = $subsection['title'] ?? $subsection['subsection_title'] ?? 'Untitled';
                $normalized[] = [
                    'title' => $title,
                    'content_items' => $subsection['content_items'] ?? []
                ];
            }
        }

        return $normalized;
    }

    /**
     * Get default sections based on report type
     *
     * @param string $reportType Type of report
     * @param array $procedures List of procedures
     * @return array Section definitions
     */
    private function getDefaultSections(string $reportType, array $procedures): array
    {
        $sections = [
            [
                'title' => 'Patient Demographics',
                'fields' => ['name', 'dob', 'mrn', 'study_date'],
                'required' => true
            ],
            [
                'title' => 'Clinical Indication',
                'content_type' => 'symptoms_and_history',
                'required' => true
            ],
            [
                'title' => 'Procedures Performed',
                'content_type' => 'procedure_list',
                'required' => true
            ]
        ];

        // Add report-type specific sections
        if ($reportType === 'MEG') {
            $sections[] = [
                'title' => 'Technical Methodology',
                'subsections' => [
                    'Equipment Specifications',
                    'Data Acquisition',
                    'Analysis Methods'
                ]
            ];
        }

        // Add sections for each procedure
        if (!empty($procedures)) {
            $sections[] = [
                'title' => 'Findings by Procedure',
                'content_type' => 'procedure_findings',
                'procedures' => $procedures
            ];
        }

        // Common ending sections
        $sections[] = [
            'title' => 'Attached Documentation',
            'content_type' => 'document_summaries'
        ];

        $sections[] = [
            'title' => 'Clinical Interpretation',
            'content_type' => 'conclusions'
        ];

        $sections[] = [
            'title' => 'Physician Signature',
            'fields' => ['physician_name', 'credentials', 'date'],
            'required' => true
        ];

        return $sections;
    }

    /**
     * Validate metadata doesn't contain PHI/PII
     * Throws exception if PHI/PII detected
     *
     * @param array $metadata Metadata to validate
     * @throws \Exception if PHI/PII detected
     */
    private function validateMetadata(array $metadata): void
    {
        // List of keys that should NOT be present
        $prohibitedKeys = [
            'patient_name', 'name', 'first_name', 'last_name',
            'mrn', 'medical_record_number',
            'ssn', 'social_security',
            'dob', 'date_of_birth', 'birthday',
            'address', 'phone', 'email',
            'doctor_name', 'physician_name',
            'hospital_name', 'facility_name'
        ];

        foreach ($prohibitedKeys as $key) {
            if (isset($metadata[$key])) {
                throw new \Exception('PHI/PII detected in metadata: ' . $key);
            }
        }

        // Check for specific age (only categories allowed)
        if (isset($metadata['age']) && is_numeric($metadata['age'])) {
            throw new \Exception('Specific age detected - only age categories allowed');
        }
    }
}
