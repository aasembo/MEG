<?php
declare(strict_types=1);

namespace App\Service;

use Cake\ORM\TableRegistry;
use Cake\I18n\FrozenTime;
use Cake\Log\Log;

/**
 * Universal Service Usage Logger
 * 
 * Centralized logging system for all external service calls including:
 * - AI Services (OpenAI, Gemini)
 * - Payment Gateways (Stripe, PayPal)
 * - SMS Services (Twilio, AWS SNS)
 * - Email Services (SendGrid, AWS SES)
 * - Cloud Storage (AWS S3, Google Cloud Storage)
 * - Analytics Services
 * - Any other external API calls
 * 
 * Features:
 * - Automatic cost tracking
 * - Response time monitoring
 * - Error logging and alerting
 * - Usage analytics and reporting
 * - Budget enforcement support
 */
class ServiceUsageLogger
{
    private $serviceUsageLogsTable;
    private ?int $logId = null;
    private float $startTime;
    
    /**
     * Service type constants
     */
    public const TYPE_AI = 'ai';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_SMS = 'sms';
    public const TYPE_EMAIL = 'email';
    public const TYPE_STORAGE = 'storage';
    public const TYPE_ANALYTICS = 'analytics';
    public const TYPE_OCR = 'ocr';
    public const TYPE_TRANSLATION = 'translation';
    public const TYPE_MAPS = 'maps';
    public const TYPE_OTHER = 'other';
    
    /**
     * Provider constants
     */
    public const PROVIDER_OPENAI = 'openai';
    public const PROVIDER_GEMINI = 'gemini';
    public const PROVIDER_STRIPE = 'stripe';
    public const PROVIDER_PAYPAL = 'paypal';
    public const PROVIDER_TWILIO = 'twilio';
    public const PROVIDER_AWS_SNS = 'aws_sns';
    public const PROVIDER_SENDGRID = 'sendgrid';
    public const PROVIDER_AWS_SES = 'aws_ses';
    public const PROVIDER_AWS_S3 = 'aws_s3';
    public const PROVIDER_GOOGLE_CLOUD_STORAGE = 'google_cloud_storage';
    public const PROVIDER_GOOGLE_ANALYTICS = 'google_analytics';
    public const PROVIDER_GOOGLE_MAPS = 'google_maps';
    
    /**
     * Status constants
     */
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_PENDING = 'pending';
    public const STATUS_TIMEOUT = 'timeout';
    public const STATUS_CANCELLED = 'cancelled';
    
    /**
     * Action constants for AI services
     */
    public const ACTION_AI_TEXT_GENERATION = 'text_generation';
    public const ACTION_AI_CHAT_COMPLETION = 'chat_completion';
    public const ACTION_AI_IMAGE_GENERATION = 'image_generation';
    public const ACTION_AI_EMBEDDINGS = 'embeddings';
    public const ACTION_AI_CASE_RECOMMENDATION = 'case_recommendation';
    public const ACTION_AI_REPORT_GENERATION = 'report_generation';
    public const ACTION_AI_DOCUMENT_ANALYSIS = 'document_analysis';
    
    /**
     * Action constants for payment services
     */
    public const ACTION_PAYMENT_CHARGE = 'charge';
    public const ACTION_PAYMENT_REFUND = 'refund';
    public const ACTION_PAYMENT_CAPTURE = 'capture';
    public const ACTION_PAYMENT_VERIFY = 'verify';
    
    /**
     * Action constants for communication services
     */
    public const ACTION_SMS_SEND = 'send_sms';
    public const ACTION_EMAIL_SEND = 'send_email';
    
    /**
     * Action constants for storage services
     */
    public const ACTION_STORAGE_UPLOAD = 'upload';
    public const ACTION_STORAGE_DOWNLOAD = 'download';
    public const ACTION_STORAGE_DELETE = 'delete';
    public const ACTION_STORAGE_LIST = 'list';
    
    public function __construct()
    {
        $this->serviceUsageLogsTable = TableRegistry::getTableLocator()->get('ServiceUsageLogs');
    }
    
    /**
     * Start logging a service call
     * 
     * @param int $hospitalId Hospital ID
     * @param string $type Service type (use TYPE_* constants)
     * @param string $provider Service provider (use PROVIDER_* constants)
     * @param string $action Action being performed (use ACTION_* constants)
     * @param array $options Additional options
     * @return int Log ID for updating later
     */
    public function startLog(
        int $hospitalId,
        string $type,
        string $provider,
        string $action,
        array $options = []
    ): int {
        $this->startTime = microtime(true);
        
        try {
            $logData = [
                'hospital_id' => $hospitalId,
                'type' => $type,
                'provider' => $provider,
                'action' => $action,
                'user_id' => $options['user_id'] ?? null,
                'related_id' => $options['related_id'] ?? null,
                'request_data' => isset($options['request_data']) ? json_encode($options['request_data']) : null,
                'status' => self::STATUS_PENDING,
                'metadata' => $options['metadata'] ?? null,
                'created' => FrozenTime::now()
            ];
            
            $log = $this->serviceUsageLogsTable->newEntity($logData);
            
            if ($this->serviceUsageLogsTable->save($log)) {
                $this->logId = $log->id;
                
                Log::debug('Service usage log started', [
                    'log_id' => $this->logId,
                    'type' => $type,
                    'provider' => $provider,
                    'action' => $action
                ]);
                
                return $this->logId;
            }
            
            Log::error('Failed to create service usage log', [
                'errors' => $log->getErrors(),
                'data' => $logData
            ]);
            
            return 0;
            
        } catch (\Exception $e) {
            Log::error('Exception creating service usage log', [
                'error' => $e->getMessage(),
                'data' => $logData ?? []
            ]);
            return 0;
        }
    }
    
    /**
     * Complete a successful service call
     * 
     * @param int $logId Log ID from startLog()
     * @param array $options Completion options
     * @return bool Success
     */
    public function completeLog(int $logId, array $options = []): bool
    {
        $responseTimeMs = $this->calculateResponseTime();
        
        try {
            $log = $this->serviceUsageLogsTable->get($logId);
            
            $log->status = self::STATUS_SUCCESS;
            $log->response_time_ms = $responseTimeMs;
            
            if (isset($options['response_data'])) {
                $log->response_data = json_encode($options['response_data']);
            }
            
            if (isset($options['units_consumed'])) {
                $log->units_consumed = $options['units_consumed'];
            }
            
            if (isset($options['unit_cost'])) {
                $log->unit_cost = $options['unit_cost'];
            }
            
            if (isset($options['total_cost_usd'])) {
                $log->total_cost_usd = $options['total_cost_usd'];
            } elseif (isset($options['units_consumed']) && isset($options['unit_cost'])) {
                // Auto-calculate cost
                $log->total_cost_usd = $options['units_consumed'] * $options['unit_cost'];
            }
            
            if (isset($options['metadata'])) {
                $log->metadata = $options['metadata'];
            }
            
            $success = (bool)$this->serviceUsageLogsTable->save($log);
            
            if ($success) {
                Log::debug('Service usage log completed', [
                    'log_id' => $logId,
                    'response_time_ms' => $responseTimeMs,
                    'cost' => $log->total_cost_usd ?? 0
                ]);
            }
            
            return $success;
            
        } catch (\Exception $e) {
            Log::error('Failed to complete service usage log', [
                'log_id' => $logId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Mark a service call as failed
     * 
     * @param int $logId Log ID from startLog()
     * @param string $errorCode Error code
     * @param string $errorMessage Error message
     * @param array $options Additional options
     * @return bool Success
     */
    public function failLog(
        int $logId,
        string $errorCode,
        string $errorMessage,
        array $options = []
    ): bool {
        $responseTimeMs = $this->calculateResponseTime();
        
        try {
            $log = $this->serviceUsageLogsTable->get($logId);
            
            $log->status = $options['status'] ?? self::STATUS_FAILED;
            $log->response_time_ms = $responseTimeMs;
            $log->error_code = $errorCode;
            $log->error_message = $errorMessage;
            
            if (isset($options['response_data'])) {
                $log->response_data = json_encode($options['response_data']);
            }
            
            if (isset($options['metadata'])) {
                $log->metadata = $options['metadata'];
            }
            
            $success = (bool)$this->serviceUsageLogsTable->save($log);
            
            if ($success) {
                Log::error('Service usage log failed', [
                    'log_id' => $logId,
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'response_time_ms' => $responseTimeMs
                ]);
            }
            
            return $success;
            
        } catch (\Exception $e) {
            Log::error('Failed to update service usage log', [
                'log_id' => $logId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Quick log for simple service calls
     * Combines start and complete in one call
     * 
     * @param int $hospitalId Hospital ID
     * @param string $type Service type
     * @param string $provider Service provider
     * @param string $action Action performed
     * @param bool $success Whether the call succeeded
     * @param array $options Additional options
     * @return int Log ID
     */
    public function quickLog(
        int $hospitalId,
        string $type,
        string $provider,
        string $action,
        bool $success,
        array $options = []
    ): int {
        try {
            $logData = [
                'hospital_id' => $hospitalId,
                'type' => $type,
                'provider' => $provider,
                'action' => $action,
                'user_id' => $options['user_id'] ?? null,
                'related_id' => $options['related_id'] ?? null,
                'request_data' => isset($options['request_data']) ? json_encode($options['request_data']) : null,
                'response_data' => isset($options['response_data']) ? json_encode($options['response_data']) : null,
                'status' => $success ? self::STATUS_SUCCESS : self::STATUS_FAILED,
                'response_time_ms' => $options['response_time_ms'] ?? 0,
                'error_code' => $options['error_code'] ?? null,
                'error_message' => $options['error_message'] ?? null,
                'units_consumed' => $options['units_consumed'] ?? null,
                'unit_cost' => $options['unit_cost'] ?? null,
                'total_cost_usd' => $options['total_cost_usd'] ?? null,
                'metadata' => $options['metadata'] ?? null,
                'created' => FrozenTime::now()
            ];
            
            // Auto-calculate cost if not provided
            if (!isset($logData['total_cost_usd']) && 
                isset($logData['units_consumed']) && 
                isset($logData['unit_cost'])) {
                $logData['total_cost_usd'] = $logData['units_consumed'] * $logData['unit_cost'];
            }
            
            $log = $this->serviceUsageLogsTable->newEntity($logData);
            
            if ($this->serviceUsageLogsTable->save($log)) {
                return $log->id;
            }
            
            return 0;
            
        } catch (\Exception $e) {
            Log::error('Failed to create quick log', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
    
    /**
     * Get usage statistics
     * 
     * @param int $hospitalId Hospital ID
     * @param string|null $type Service type filter
     * @param string|null $provider Provider filter
     * @param \DateTime|null $startDate Start date
     * @param \DateTime|null $endDate End date
     * @return array Statistics
     */
    public function getUsageStats(
        int $hospitalId,
        ?string $type = null,
        ?string $provider = null,
        ?\DateTime $startDate = null,
        ?\DateTime $endDate = null
    ): array {
        $query = $this->serviceUsageLogsTable->find()
            ->where(['hospital_id' => $hospitalId]);
        
        if ($type) {
            $query->where(['type' => $type]);
        }
        
        if ($provider) {
            $query->where(['provider' => $provider]);
        }
        
        if ($startDate) {
            $query->where(['created >=' => $startDate]);
        }
        
        if ($endDate) {
            $query->where(['created <=' => $endDate]);
        }
        
        $stats = [
            'total_calls' => $query->count(),
            'successful_calls' => $query->where(['status' => self::STATUS_SUCCESS])->count(),
            'failed_calls' => $query->where(['status' => self::STATUS_FAILED])->count(),
            'total_cost' => 0,
            'total_units' => 0,
            'avg_response_time_ms' => 0,
            'by_provider' => [],
            'by_action' => []
        ];
        
        // Calculate totals
        $totals = $query->select([
            'total_cost' => $query->func()->sum('total_cost_usd'),
            'total_units' => $query->func()->sum('units_consumed'),
            'avg_response_time' => $query->func()->avg('response_time_ms')
        ])->first();
        
        if ($totals) {
            $stats['total_cost'] = (float)$totals->total_cost;
            $stats['total_units'] = (float)$totals->total_units;
            $stats['avg_response_time_ms'] = (int)$totals->avg_response_time;
        }
        
        // Group by provider
        $byProvider = $query->select([
            'provider',
            'count' => $query->func()->count('*'),
            'total_cost' => $query->func()->sum('total_cost_usd')
        ])
        ->group('provider')
        ->toArray();
        
        foreach ($byProvider as $row) {
            $stats['by_provider'][$row->provider] = [
                'count' => $row->count,
                'cost' => (float)$row->total_cost
            ];
        }
        
        // Group by action
        $byAction = $query->select([
            'action',
            'count' => $query->func()->count('*'),
            'total_cost' => $query->func()->sum('total_cost_usd')
        ])
        ->group('action')
        ->toArray();
        
        foreach ($byAction as $row) {
            $stats['by_action'][$row->action] = [
                'count' => $row->count,
                'cost' => (float)$row->total_cost
            ];
        }
        
        return $stats;
    }
    
    /**
     * Check if budget limit is exceeded
     * 
     * @param int $hospitalId Hospital ID
     * @param string $type Service type
     * @param string|null $provider Provider (optional)
     * @param float $budgetLimit Budget limit in USD
     * @param string $period Period (current_month, current_year, all_time)
     * @return array Budget status
     */
    public function checkBudget(
        int $hospitalId,
        string $type,
        ?string $provider,
        float $budgetLimit,
        string $period = 'current_month'
    ): array {
        $query = $this->serviceUsageLogsTable->find()
            ->where(['hospital_id' => $hospitalId, 'type' => $type]);
        
        if ($provider) {
            $query->where(['provider' => $provider]);
        }
        
        // Apply date filter based on period
        if ($period === 'current_month') {
            $query->where(['created >=' => FrozenTime::now()->startOfMonth()]);
        } elseif ($period === 'current_year') {
            $query->where(['created >=' => FrozenTime::now()->startOfYear()]);
        }
        
        $totalCost = (float)$query->select([
            'total' => $query->func()->sum('total_cost_usd')
        ])->first()->total;
        
        $remaining = $budgetLimit - $totalCost;
        $percentUsed = $budgetLimit > 0 ? ($totalCost / $budgetLimit) * 100 : 0;
        
        return [
            'budget_limit' => $budgetLimit,
            'total_spent' => $totalCost,
            'remaining' => $remaining,
            'percent_used' => round($percentUsed, 2),
            'is_exceeded' => $totalCost > $budgetLimit,
            'is_warning' => $percentUsed >= 80, // 80% threshold
            'period' => $period
        ];
    }
    
    /**
     * Calculate response time in milliseconds
     * 
     * @return int Response time in milliseconds
     */
    private function calculateResponseTime(): int
    {
        if (!isset($this->startTime)) {
            return 0;
        }
        
        $endTime = microtime(true);
        $durationSeconds = $endTime - $this->startTime;
        return (int)($durationSeconds * 1000);
    }
}
