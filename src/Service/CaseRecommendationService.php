<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Http\Client;
use Cake\Log\Log;
use App\Service\AiProviderRouter;
use App\Service\ServiceUsageLogger;

/**
 * Case Recommendation Service
 * 
 * Uses AI (OpenAI or Google Gemini) to analyze patient symptoms and recommend:
 * - Exams and procedures
 * - Department
 * - Sedation level
 * - Priority level
 * - Case notes
 */
class CaseRecommendationService
{
    private ?int $hospitalId;
    private ?int $userId;
    private AiProviderRouter $aiRouter;
    private string $provider;
    private bool $enabled;
    private string $apiKey;
    private string $model;
    private int $maxTokens;
    private float $temperature;

    public function __construct(?int $hospitalId = null, ?int $userId = null)
    {
        $this->hospitalId = $hospitalId ?? 1;
        $this->userId = $userId;
        
        // Initialize AI Provider Router
        $this->aiRouter = new AiProviderRouter();
        
        // Determine which provider to use for this hospital
        $this->provider = $this->aiRouter->determineProvider($this->hospitalId);
        
        // Get provider configuration from database
        $config = $this->aiRouter->getProviderConfig($this->hospitalId, $this->provider);
        
        $this->enabled = $config['enabled'];
        $this->apiKey = $config['api_key'];
        $this->model = $config['model'];
        $this->maxTokens = (int)$config['max_tokens']; // Use configured value (no artificial cap)
        $this->temperature = $config['temperature'];
        
        // Log initialization status
        Log::debug('CaseRecommendationService initialized', [
            'hospital_id' => $this->hospitalId,
            'provider' => $this->provider,
            'enabled' => $this->enabled,
            'model' => $this->model
        ]);
    }

    /**
     * Check if AI integration is enabled
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get active AI provider name
     * 
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
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

            // Estimate tokens for the prompt (rough estimate)
            $estimatedPromptTokens = (int)(strlen($prompt) / 4);
            $estimatedTotalTokens = $estimatedPromptTokens + $this->maxTokens;

            $response = $this->callAiProvider($prompt);
            
            if ($response && isset($response['choices'][0]['message']['content'])) {
                $content = $response['choices'][0]['message']['content'];
                $recommendations = $this->parseRecommendations($content, $availableExamsProcedures, $availableDepartments, $availableSedations);
                
                return $recommendations;
            }

            return $this->getFallbackRecommendations();
        } catch (\Exception $e) {
            Log::error('AI API Error in case recommendations', [
                'provider' => $this->provider,
                'error' => $e->getMessage()
            ]);
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
     * Call appropriate AI provider based on configuration
     * 
     * @param string $prompt
     * @param int|null $caseId Related case ID (optional)
     * @return array|null
     */
    private function callAiProvider(string $prompt, ?int $caseId = null): ?array
    {
        // Initialize ServiceUsageLogger
        $logger = new ServiceUsageLogger();
        
        // Start logging
        $logId = $logger->startLog(
            $this->hospitalId,
            ServiceUsageLogger::TYPE_AI,
            $this->provider,
            ServiceUsageLogger::ACTION_AI_CASE_RECOMMENDATION,
            [
                'user_id' => $this->userId,
                'related_id' => $caseId,
                'request_data' => [
                    'model' => $this->model,
                    'prompt_length' => strlen($prompt),
                    'max_tokens' => $this->maxTokens,
                    'temperature' => $this->temperature
                ]
            ]
        );
        
        try {
            // Make the API call
            if ($this->provider === 'openai') {
                $response = $this->callOpenAI($prompt);
            } elseif ($this->provider === 'gemini') {
                $response = $this->callGemini($prompt);
            } else {
                $response = null;
            }
            
            // If successful, complete the log
            if ($response) {
                // Extract token count from response
                $tokens = $this->extractTokenCount($response);
                $cost = $this->aiRouter->calculateCost($this->provider, $tokens);
                $unitCost = $tokens > 0 ? $cost / $tokens : 0;
                
                $logger->completeLog($logId, [
                    'response_data' => [
                        'has_content' => isset($response['choices'][0]['message']['content']),
                        'token_count' => $tokens
                    ],
                    'units_consumed' => $tokens,
                    'unit_cost' => $unitCost,
                    'total_cost_usd' => $cost
                ]);
                
                Log::debug('AI case recommendation completed', [
                    'log_id' => $logId,
                    'provider' => $this->provider,
                    'tokens' => $tokens,
                    'cost' => $cost
                ]);
            } else {
                // Log as failed
                $logger->failLog($logId, 'NO_RESPONSE', 'AI provider returned null response');
            }
            
            return $response;
            
        } catch (\Exception $e) {
            // Log the failure
            $logger->failLog($logId, 'API_ERROR', $e->getMessage(), [
                'response_data' => ['error' => $e->getMessage()]
            ]);
            
            Log::error('AI API Error in case recommendations', [
                'log_id' => $logId,
                'provider' => $this->provider,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }
    
    /**
     * Extract token count from API response
     * 
     * @param array $response API response
     * @return int Token count
     */
    private function extractTokenCount(array $response): int
    {
        // OpenAI format
        if (isset($response['usage']['total_tokens'])) {
            return (int)$response['usage']['total_tokens'];
        }
        
        // Gemini format (converted to OpenAI format in callGemini)
        if (isset($response['usageMetadata']['totalTokenCount'])) {
            return (int)$response['usageMetadata']['totalTokenCount'];
        }
        
        // Fallback: estimate based on response length
        if (isset($response['choices'][0]['message']['content'])) {
            $content = $response['choices'][0]['message']['content'];
            return (int)(strlen($content) / 4);
        }
        
        return 0;
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
        
        try {
            $response = $http->post('https://api.openai.com/v1/chat/completions', json_encode([
                'model' => $this->model,
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
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ]
            ]);

            if ($response->isOk()) {
                return $response->getJson();
            }

            Log::error('OpenAI API request failed', [
                'status_code' => $response->getStatusCode(),
                'body' => $response->getStringBody()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('OpenAI API exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Call Google Gemini API
     * 
     * @param string $prompt
     * @return array|null
     */
    private function callGemini(string $prompt): ?array
    {
        $http = new Client();
        
        try {
            // Follow Google's official API format
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->model . ':generateContent';
            
            Log::debug('Calling Gemini API', [
                'url' => $url,
                'model' => $this->model,
                'max_output_tokens' => $this->maxTokens,
                'api_key_length' => strlen($this->apiKey),
                'api_key_preview' => substr($this->apiKey, 0, 4) . '...' . substr($this->apiKey, -4),
            ]);
            
            // Prepare request body following Google's format
            $requestBody = [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => 'You are a medical assistant that provides case recommendations in JSON format.']
                        ]
                    ],
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $this->temperature,
                    'maxOutputTokens' => $this->maxTokens,
                ]
            ];
            
            $response = $http->post($url, json_encode($requestBody), [
                'headers' => [
                    'x-goog-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ]
            ]);

            if ($response->isOk()) {
                $data = $response->getJson();
                
                Log::debug('Gemini API response received', [
                    'has_candidates' => isset($data['candidates']),
                    'candidate_count' => isset($data['candidates']) ? count($data['candidates']) : 0,
                    'finish_reason' => $data['candidates'][0]['finishReason'] ?? 'unknown',
                    'usage_metadata' => $data['usageMetadata'] ?? []
                ]);
                
                // Check if we have a valid response with content
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $text = $data['candidates'][0]['content']['parts'][0]['text'];
                    
                    // Convert Gemini response format to OpenAI format for consistency
                    // Include usage metadata for token tracking
                    return [
                        'choices' => [
                            [
                                'message' => [
                                    'content' => $text
                                ]
                            ]
                        ],
                        'usageMetadata' => $data['usageMetadata'] ?? []
                    ];
                }
                
                // Check for incomplete response (MAX_TOKENS)
                if (isset($data['candidates'][0]['finishReason']) && 
                    $data['candidates'][0]['finishReason'] === 'MAX_TOKENS') {
                    Log::warning('Gemini response incomplete - MAX_TOKENS reached', [
                        'max_tokens' => $this->maxTokens,
                        'tokens_used' => $data['usageMetadata']['totalTokenCount'] ?? 'unknown'
                    ]);
                }
                
                // Log the full response for debugging
                Log::error('Gemini API response missing content', [
                    'full_response' => $data
                ]);
            }

            Log::error('Gemini API request failed', [
                'status_code' => $response->getStatusCode(),
                'body' => $response->getStringBody()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Gemini API exception', ['error' => $e->getMessage()]);
            return null;
        }
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
            Log::error('Failed to parse AI JSON response', [
                'provider' => $this->provider,
                'content' => $content
            ]);
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
