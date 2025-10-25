<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Http\Client;
use Cake\Log\Log;

/**
 * Case Recommendation Service
 * 
 * Uses OpenAI to analyze patient symptoms and recommend:
 * - Exams and procedures
 * - Department
 * - Sedation level
 * - Priority level
 * - Case notes
 */
class CaseRecommendationService
{
    private $openaiApiKey;
    private $openaiModel;
    private $maxTokens;
    private $temperature;
    private $enabled;

    public function __construct()
    {
        // Safely access nested array values with additional validation
        $this->openaiApiKey = env('OPENAI_API_KEY');
        $this->openaiModel = env('OPENAI_MODEL', 'gpt-3.5-turbo');
        $this->maxTokens = (int)env('OPENAI_MAX_TOKENS', 500);
        $this->temperature = (float)env('OPENAI_TEMPERATURE', 0.3);
        
        // Convert string 'true'/'false' to boolean
        $enabledValue = env('OPENAI_ENABLED', 'false');
        $this->enabled = filter_var($enabledValue, FILTER_VALIDATE_BOOLEAN) && !empty($this->openaiApiKey);
        
        // Log initialization status
        Log::debug('CaseRecommendationService initialized. Enabled: ' . ($this->enabled ? 'true' : 'false') . ', API Key present: ' . (!empty($this->openaiApiKey) ? 'yes' : 'no'));
    }

    /**
     * Check if OpenAI integration is enabled
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get case recommendations based on patient data and symptoms
     * 
     * @param array $patientData Patient information (age, gender, etc.)
     * @param string $symptoms Symptoms description
     * @param array $availableExamsProcedures List of available exams/procedures
     * @param array $availableDepartments List of available departments
     * @param array $availableSedations List of available sedation levels
     * @return array Recommendations including exams, department, sedation, priority, notes
     */
    public function getRecommendations(
        array $patientData,
        string $symptoms,
        array $availableExamsProcedures = [],
        array $availableDepartments = [],
        array $availableSedations = []
    ): array {
        if (!$this->enabled) {
            return $this->getFallbackRecommendations();
        }

        try {
            $prompt = $this->buildPrompt(
                $patientData,
                $symptoms,
                $availableExamsProcedures,
                $availableDepartments,
                $availableSedations
            );

            $response = $this->callOpenAI($prompt);
            
            if ($response && isset($response['choices'][0]['message']['content'])) {
                $content = $response['choices'][0]['message']['content'];
                return $this->parseRecommendations($content, $availableExamsProcedures, $availableDepartments, $availableSedations);
            }

            return $this->getFallbackRecommendations();
        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            return $this->getFallbackRecommendations();
        }
    }

    /**
     * Build prompt for OpenAI
     * 
     * @param array $patientData
     * @param string $symptoms
     * @param array $availableExamsProcedures
     * @param array $availableDepartments
     * @param array $availableSedations
     * @return string
     */
    private function buildPrompt(
        array $patientData,
        string $symptoms,
        array $availableExamsProcedures,
        array $availableDepartments,
        array $availableSedations
    ): string {
        $age = $patientData['age'] ?? 'unknown';
        $gender = $patientData['gender'] ?? 'unknown';
        
        $examsProceduresList = !empty($availableExamsProcedures) 
            ? "\n\nAvailable Exams/Procedures:\n" . implode("\n", array_map(function($id, $name) {
                return "- ID: $id, Name: $name";
            }, array_keys($availableExamsProcedures), $availableExamsProcedures))
            : '';

        $departmentsList = !empty($availableDepartments)
            ? "\n\nAvailable Departments:\n" . implode("\n", array_map(function($id, $name) {
                return "- ID: $id, Name: $name";
            }, array_keys($availableDepartments), $availableDepartments))
            : '';

        $sedationsList = !empty($availableSedations)
            ? "\n\nAvailable Sedation Levels:\n" . implode("\n", array_map(function($id, $name) {
                return "- ID: $id, Level: $name";
            }, array_keys($availableSedations), $availableSedations))
            : '';

        return <<<PROMPT
You are a medical assistant helping to analyze patient symptoms and recommend appropriate medical exams, procedures, and case management details.

Patient Information:
- Age: $age
- Gender: $gender

Symptoms:
$symptoms
$examsProceduresList
$departmentsList
$sedationsList

Based on this information, please provide recommendations in the following JSON format:

{
    "recommended_exam_procedure_ids": [list of IDs from available exams/procedures that are most relevant],
    "department_id": recommended department ID,
    "sedation_id": recommended sedation level ID,
    "priority": "low|medium|high|urgent",
    "notes": "Brief clinical notes explaining the recommendations and any important observations"
}

Consider the patient's age, gender, and symptoms when making recommendations. Prioritize the most relevant exams/procedures. If specific IDs are not available from the lists, provide your best medical reasoning in the notes field.

Respond ONLY with valid JSON, no additional text.
PROMPT;
    }

    /**
     * Call OpenAI API
     * 
     * @param string $prompt
     * @return array|null
     */
    private function callOpenAI(string $prompt): ?array
    {
        $http = new Client();
        
        $response = $http->post('https://api.openai.com/v1/chat/completions', json_encode([
            'model' => $this->openaiModel,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a medical assistant that provides case recommendations in JSON format.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
        ]), [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ]
        ]);

        if ($response->isOk()) {
            return $response->getJson();
        }

        Log::error('OpenAI API request failed: ' . $response->getStatusCode());
        return null;
    }

    /**
     * Parse OpenAI response into structured recommendations
     * 
     * @param string $content
     * @param array $availableExamsProcedures
     * @param array $availableDepartments
     * @param array $availableSedations
     * @return array
     */
    private function parseRecommendations(
        string $content,
        array $availableExamsProcedures,
        array $availableDepartments,
        array $availableSedations
    ): array {
        // Try to extract JSON from the response
        $content = trim($content);
        
        // Remove markdown code blocks if present
        $content = preg_replace('/^```json\s*/m', '', $content);
        $content = preg_replace('/\s*```$/m', '', $content);
        
        $data = json_decode($content, true);
        
        if (!$data) {
            Log::error('Failed to parse OpenAI JSON response: ' . $content);
            return $this->getFallbackRecommendations();
        }

        // Validate and filter recommended exam procedure IDs
        $recommendedExamProcedureIds = [];
        if (!empty($data['recommended_exam_procedure_ids']) && is_array($data['recommended_exam_procedure_ids'])) {
            foreach ($data['recommended_exam_procedure_ids'] as $id) {
                if (isset($availableExamsProcedures[$id])) {
                    $recommendedExamProcedureIds[] = (int)$id;
                }
            }
        }

        // Validate department ID
        $departmentId = null;
        if (!empty($data['department_id']) && isset($availableDepartments[$data['department_id']])) {
            $departmentId = (int)$data['department_id'];
        }

        // Validate sedation ID
        $sedationId = null;
        if (!empty($data['sedation_id']) && isset($availableSedations[$data['sedation_id']])) {
            $sedationId = (int)$data['sedation_id'];
        }

        // Validate priority
        $priority = 'medium';
        if (!empty($data['priority']) && in_array($data['priority'], ['low', 'medium', 'high', 'urgent'])) {
            $priority = $data['priority'];
        }

        return [
            'recommended_exam_procedure_ids' => $recommendedExamProcedureIds,
            'department_id' => $departmentId,
            'sedation_id' => $sedationId,
            'priority' => $priority,
            'notes' => $data['notes'] ?? '',
            'ai_generated' => true
        ];
    }

    /**
     * Get fallback recommendations when AI is disabled or fails
     * 
     * @return array
     */
    private function getFallbackRecommendations(): array
    {
        return [
            'recommended_exam_procedure_ids' => [],
            'department_id' => null,
            'sedation_id' => null,
            'priority' => 'medium',
            'notes' => '',
            'ai_generated' => false
        ];
    }
}
