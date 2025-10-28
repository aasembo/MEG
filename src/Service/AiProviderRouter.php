<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\TableRegistry;
use Cake\Log\Log;
use App\Service\ServiceUsageLogger;

/**
 * AI Provider Router Service
 * 
 * Intelligently routes AI requests to the appropriate provider (OpenAI or Google Gemini)
 * based on hospital configuration, budget limits, and availability.
 * 
 * Features:
 * - Provider selection based on hospital settings
 * - Budget monitoring and enforcement
 * - Cost calculation per provider
 * - Usage logging and statistics
 * - Automatic fallback to alternative providers
 */
class AiProviderRouter
{
    public const PROVIDER_OPENAI = 'openai';
    public const PROVIDER_GEMINI = 'gemini';
    public const PROVIDER_FALLBACK = 'fallback';
    
    // Pricing per 1K tokens (input + output averaged)
    private const PRICING = [
        self::PROVIDER_OPENAI => 0.03,    // $0.03 per 1K tokens
        self::PROVIDER_GEMINI => 0.00125, // $0.00125 per 1K tokens (96% cheaper!)
    ];

    private $settingsTable;
    private $serviceUsageLogsTable;

    public function __construct()
    {
        $this->settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $this->serviceUsageLogsTable = TableRegistry::getTableLocator()->get('ServiceUsageLogs');
    }

    /**
     * Determine which AI provider to use for a hospital
     * 
     * @param int $hospitalId Hospital ID
     * @return string Provider name: 'openai', 'gemini', or 'fallback'
     */
    public function determineProvider(int $hospitalId): string
    {
        // Get default provider preference
        $defaultProvider = $this->getSetting($hospitalId, 'ai', 'default_provider', self::PROVIDER_GEMINI);
        
        // Check if preferred provider is enabled
        if ($this->isProviderEnabled($hospitalId, $defaultProvider)) {
            // Check budget limits
            if ($this->isWithinBudget($hospitalId, $defaultProvider)) {
                Log::debug('Using preferred provider', [
                    'hospital_id' => $hospitalId,
                    'provider' => $defaultProvider
                ]);
                return $defaultProvider;
            }
            Log::warning('Preferred provider over budget', [
                'hospital_id' => $hospitalId,
                'provider' => $defaultProvider
            ]);
        }
        
        // Try alternative providers
        $providers = [self::PROVIDER_GEMINI, self::PROVIDER_OPENAI];
        foreach ($providers as $provider) {
            if ($provider === $defaultProvider) {
                continue; // Already checked
            }
            
            if ($this->isProviderEnabled($hospitalId, $provider) && 
                $this->isWithinBudget($hospitalId, $provider)) {
                Log::info('Using fallback provider', [
                    'hospital_id' => $hospitalId,
                    'provider' => $provider,
                    'reason' => 'preferred_unavailable'
                ]);
                return $provider;
            }
        }
        
        // No AI providers available
        Log::warning('No AI providers available, using fallback mode', [
            'hospital_id' => $hospitalId
        ]);
        return self::PROVIDER_FALLBACK;
    }

    /**
     * Get configuration for a specific provider
     * 
     * @param int $hospitalId Hospital ID
     * @param string $provider Provider name
     * @return array Configuration array
     */
    public function getProviderConfig(int $hospitalId, string $provider): array
    {
        if ($provider === self::PROVIDER_FALLBACK) {
            return [
                'enabled' => false,
                'api_key' => '',
                'model' => '',
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ];
        }

        $prefix = "ai.{$provider}";
        
        $apiKey = $this->getSetting($hospitalId, 'ai', "{$provider}.api_key", '');
        
        // Debug log to check API key retrieval (log only first/last 4 chars for security)
        Log::debug('Provider config retrieved', [
            'hospital_id' => $hospitalId,
            'provider' => $provider,
            'api_key_length' => strlen($apiKey),
            'api_key_preview' => !empty($apiKey) ? substr($apiKey, 0, 4) . '...' . substr($apiKey, -4) : 'empty',
        ]);
        
        return [
            'enabled' => (bool) $this->getSetting($hospitalId, 'ai', "{$provider}.enabled", false),
            'api_key' => $apiKey,
            'model' => $this->getSetting($hospitalId, 'ai', "{$provider}.model", $this->getDefaultModel($provider)),
            'temperature' => (float) $this->getSetting($hospitalId, 'ai', "{$provider}.temperature", 0.7),
            'max_tokens' => (int) $this->getSetting($hospitalId, 'ai', "{$provider}.max_tokens", 2000),
        ];
    }

    /**
     * Check if a provider is enabled for a hospital
     * 
     * @param int $hospitalId Hospital ID
     * @param string $provider Provider name
     * @return bool True if enabled
     */
    public function isProviderEnabled(int $hospitalId, string $provider): bool
    {
        if ($provider === self::PROVIDER_FALLBACK) {
            return true; // Fallback is always available
        }

        $enabled = $this->getSetting($hospitalId, 'ai', "{$provider}.enabled", false);
        $apiKey = $this->getSetting($hospitalId, 'ai', "{$provider}.api_key", '');
        
        return (bool) $enabled && !empty($apiKey);
    }

    /**
     * Check if hospital is within budget for a provider
     * 
     * @param int $hospitalId Hospital ID
     * @param string $provider Provider name
     * @return bool True if within budget
     */
    public function isWithinBudget(int $hospitalId, string $provider): bool
    {
        if ($provider === self::PROVIDER_FALLBACK) {
            return true; // Fallback has no cost
        }

        $monthlyLimit = (float) $this->getSetting($hospitalId, 'ai', 'budget.monthly_limit', 0);
        
        if ($monthlyLimit <= 0) {
            return true; // No budget limit set
        }

        // Get current month's usage
        $currentUsage = $this->getCurrentMonthUsage($hospitalId);
        
        return $currentUsage < $monthlyLimit;
    }

    /**
     * Calculate cost for a given number of tokens
     * 
     * @param string $provider Provider name
     * @param int $tokens Number of tokens
     * @return float Cost in dollars
     */
    public function calculateCost(string $provider, int $tokens): float
    {
        if (!isset(self::PRICING[$provider])) {
            return 0.0;
        }

        return ($tokens / 1000) * self::PRICING[$provider];
    }

    /**
     * Log AI service usage
     * 
     * @param int $hospitalId Hospital ID
     * @param string $provider Provider used
     * @param string $serviceName Service name (e.g., 'report_generation', 'case_recommendations')
     * @param int $tokensUsed Number of tokens used
     * @param int|null $userId User ID who triggered the request
     * @param int|null $relatedId Related record ID (case_id, document_id, etc.)
     * @return bool Success
     */
    public function logUsage(
        int $hospitalId, 
        string $provider, 
        string $serviceName, 
        int $tokensUsed,
        ?int $userId = null,
        ?int $relatedId = null
    ): bool {
        try {
            $cost = $this->calculateCost($provider, $tokensUsed);
            $unitCost = $tokensUsed > 0 ? $cost / $tokensUsed : 0;
            
            // Use the universal ServiceUsageLogger
            $logger = new ServiceUsageLogger();
            
            $logId = $logger->quickLog(
                $hospitalId,
                ServiceUsageLogger::TYPE_AI,
                $provider, // 'openai' or 'gemini'
                $serviceName,   // 'case_recommendation', 'report_generation', etc.
                true,      // success
                [
                    'user_id' => $userId,
                    'related_id' => $relatedId,
                    'units_consumed' => $tokensUsed,
                    'unit_cost' => $unitCost,
                    'total_cost_usd' => $cost,
                    'metadata' => "AI service: {$serviceName}"
                ]
            );

            if ($logId > 0) {
                Log::debug('AI usage logged via ServiceUsageLogger', [
                    'log_id' => $logId,
                    'hospital_id' => $hospitalId,
                    'provider' => $provider,
                    'service' => $serviceName,
                    'tokens' => $tokensUsed,
                    'cost' => $cost
                ]);
                return true;
            }

            Log::error('Failed to log AI usage via ServiceUsageLogger');
            return false;
            
        } catch (\Exception $e) {
            Log::error('Exception logging AI usage', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get usage statistics for a hospital
     * 
     * @param int $hospitalId Hospital ID
     * @param string|null $provider Filter by provider (optional)
     * @param int $days Number of days to look back (default 30)
     * @return array Statistics
     */
    public function getUsageStats(int $hospitalId, ?string $provider = null, int $days = 30): array
    {
        try {
            $startDate = new \DateTime("-{$days} days");
            
            $query = $this->serviceUsageLogsTable->find()
                ->where([
                    'hospital_id' => $hospitalId,
                    'created >=' => $startDate
                ]);

            if ($provider) {
                $query->where(['provider' => $provider]);
            }

            $results = $query->all();

            $stats = [
                'total_requests' => $results->count(),
                'total_tokens' => 0,
                'total_cost' => 0.0,
                'by_provider' => [],
                'by_service' => [],
            ];

            foreach ($results as $log) {
                $stats['total_tokens'] += $log->tokens_used;
                $stats['total_cost'] += $log->cost;

                // By provider
                if (!isset($stats['by_provider'][$log->provider])) {
                    $stats['by_provider'][$log->provider] = [
                        'requests' => 0,
                        'tokens' => 0,
                        'cost' => 0.0,
                    ];
                }
                $stats['by_provider'][$log->provider]['requests']++;
                $stats['by_provider'][$log->provider]['tokens'] += $log->tokens_used;
                $stats['by_provider'][$log->provider]['cost'] += $log->cost;

                // By service
                if (!isset($stats['by_service'][$log->service_name])) {
                    $stats['by_service'][$log->service_name] = [
                        'requests' => 0,
                        'tokens' => 0,
                        'cost' => 0.0,
                    ];
                }
                $stats['by_service'][$log->service_name]['requests']++;
                $stats['by_service'][$log->service_name]['tokens'] += $log->tokens_used;
                $stats['by_service'][$log->service_name]['cost'] += $log->cost;
            }

            return $stats;
        } catch (\Exception $e) {
            Log::error('Error getting usage stats', [
                'error' => $e->getMessage()
            ]);
            return [
                'total_requests' => 0,
                'total_tokens' => 0,
                'total_cost' => 0.0,
                'by_provider' => [],
                'by_service' => [],
            ];
        }
    }

    /**
     * Get cost comparison between providers for a given token count
     * 
     * @param int $tokens Number of tokens
     * @return array Cost comparison
     */
    public function getCostComparison(int $tokens): array
    {
        $comparison = [];
        
        foreach (self::PRICING as $provider => $pricePerK) {
            $cost = $this->calculateCost($provider, $tokens);
            $comparison[$provider] = [
                'cost' => $cost,
                'price_per_1k' => $pricePerK,
            ];
        }

        // Calculate savings
        if (isset($comparison[self::PROVIDER_OPENAI]) && isset($comparison[self::PROVIDER_GEMINI])) {
            $savings = $comparison[self::PROVIDER_OPENAI]['cost'] - $comparison[self::PROVIDER_GEMINI]['cost'];
            $savingsPercent = ($savings / $comparison[self::PROVIDER_OPENAI]['cost']) * 100;
            
            $comparison['savings'] = [
                'amount' => $savings,
                'percent' => round($savingsPercent, 2),
            ];
        }

        return $comparison;
    }

    /**
     * Get current month's total usage cost for a hospital
     * 
     * @param int $hospitalId Hospital ID
     * @return float Total cost
     */
    private function getCurrentMonthUsage(int $hospitalId): float
    {
        try {
            $startOfMonth = new \DateTime('first day of this month 00:00:00');
            
            $result = $this->serviceUsageLogsTable->find()
                ->where([
                    'hospital_id' => $hospitalId,
                    'created >=' => $startOfMonth
                ])
                ->select(['total_cost' => 'SUM(cost)'])
                ->first();

            return (float) ($result->total_cost ?? 0.0);
        } catch (\Exception $e) {
            Log::error('Error getting current month usage', [
                'error' => $e->getMessage()
            ]);
            return 0.0;
        }
    }

    /**
     * Get a setting value with fallback
     * 
     * @param int $hospitalId Hospital ID
     * @param string $category Setting category
     * @param string $name Setting name
     * @param mixed $default Default value
     * @return mixed Setting value
     */
    private function getSetting(int $hospitalId, string $category, string $name, $default = null)
    {
        try {
            // Use SettingsTable's getSetting method which handles decryption
            $key = "{$category}.{$name}";
            return $this->settingsTable->getSetting($hospitalId, $key, $default, true);
        } catch (\Exception $e) {
            Log::error('Error getting setting', [
                'hospital_id' => $hospitalId,
                'category' => $category,
                'name' => $name,
                'error' => $e->getMessage()
            ]);
            return $default;
        }
    }

    /**
     * Get default model for a provider
     * 
     * @param string $provider Provider name
     * @return string Default model name
     */
    private function getDefaultModel(string $provider): string
    {
        $defaults = [
            self::PROVIDER_OPENAI => 'gpt-4',
            self::PROVIDER_GEMINI => 'gemini-pro',
        ];

        return $defaults[$provider] ?? '';
    }
}
