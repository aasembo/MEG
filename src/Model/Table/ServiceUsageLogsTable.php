<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ServiceUsageLogs Model
 * 
 * Universal logging system for all external service usage including:
 * - AI services (OpenAI, Gemini, etc.)
 * - Payment gateways (Stripe, PayPal, etc.)
 * - Communication services (SMS, Email)
 * - Storage services (AWS S3, Google Cloud Storage)
 * - Analytics and other external APIs
 * 
 * Features:
 * - Comprehensive request/response logging
 * - Cost tracking and budget monitoring
 * - Performance metrics (response times)
 * - Error tracking and alerting
 * - Usage analytics and reporting
 *
 * @property \App\Model\Table\HospitalsTable&\Cake\ORM\Association\BelongsTo $Hospitals
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\ServiceUsageLog newEmptyEntity()
 * @method \App\Model\Entity\ServiceUsageLog newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\ServiceUsageLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ServiceUsageLog get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ServiceUsageLog findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ServiceUsageLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\ServiceUsageLog[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ServiceUsageLog|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ServiceUsageLog saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ServiceUsageLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ServiceUsageLog>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ServiceUsageLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ServiceUsageLog> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ServiceUsageLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ServiceUsageLog>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ServiceUsageLog>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ServiceUsageLog> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ServiceUsageLogsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('service_usage_logs');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        // Associations
        $this->belongsTo('Hospitals', array(
            'foreignKey' => 'hospital_id',
            'joinType' => 'INNER',
        ));

        $this->belongsTo('Users', array(
            'foreignKey' => 'user_id',
            'joinType' => 'LEFT',
        ));
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('hospital_id')
            ->requirePresence('hospital_id', 'create')
            ->notEmptyString('hospital_id');

        $validator
            ->scalar('type')
            ->maxLength('type', 50)
            ->requirePresence('type', 'create')
            ->notEmptyString('type')
            ->add('type', 'validType', array(
                'rule' => array('inList', array('ai', 'payment', 'sms', 'email', 'storage', 'analytics', 'ocr', 'translation', 'maps', 'other')),
                'message' => 'Invalid service type',
            ));

        $validator
            ->scalar('provider')
            ->maxLength('provider', 50)
            ->requirePresence('provider', 'create')
            ->notEmptyString('provider');

        $validator
            ->integer('related_id')
            ->allowEmptyString('related_id');

        $validator
            ->integer('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->scalar('action')
            ->maxLength('action', 100)
            ->requirePresence('action', 'create')
            ->notEmptyString('action');

        $validator
            ->scalar('request_data')
            ->allowEmptyString('request_data');

        $validator
            ->scalar('response_data')
            ->allowEmptyString('response_data');

        $validator
            ->scalar('status')
            ->requirePresence('status', 'create')
            ->notEmptyString('status')
            ->add('status', 'validStatus', array(
                'rule' => array('inList', array('success', 'failed', 'pending', 'timeout', 'cancelled')),
                'message' => 'Invalid status',
            ));

        $validator
            ->integer('response_time_ms')
            ->allowEmptyString('response_time_ms');

        $validator
            ->scalar('error_code')
            ->maxLength('error_code', 50)
            ->allowEmptyString('error_code');

        $validator
            ->scalar('error_message')
            ->allowEmptyString('error_message');

        $validator
            ->decimal('units_consumed')
            ->allowEmptyString('units_consumed');

        $validator
            ->decimal('unit_cost')
            ->allowEmptyString('unit_cost');

        $validator
            ->decimal('total_cost_usd')
            ->allowEmptyString('total_cost_usd');

        $validator
            ->scalar('metadata')
            ->allowEmptyString('metadata');

        return $validator;
    }

    /**
     * Get usage summary for a hospital
     *
     * @param int $hospitalId Hospital ID
     * @param string|null $startDate Start date (Y-m-d format)
     * @param string|null $endDate End date (Y-m-d format)
     * @return array Summary statistics
     */
    public function getUsageSummary(int $hospitalId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = $this->find()
            ->where(array('hospital_id' => $hospitalId));

        if ($startDate) {
            $query->where(array('created >=' => $startDate));
        }

        if ($endDate) {
            $query->where(array('created <=' => $endDate));
        }

        $summary = $query->select(array(
            'total_calls' => $query->func()->count('*'),
            'successful_calls' => $query->func()->sum(
                $query->newExpr()->addCase(
                    array($query->newExpr()->eq('status', 'success')),
                    array(1),
                    array('integer')
                )
            ),
            'failed_calls' => $query->func()->sum(
                $query->newExpr()->addCase(
                    array($query->newExpr()->eq('status', 'failed')),
                    array(1),
                    array('integer')
                )
            ),
            'total_cost' => $query->func()->sum('total_cost_usd'),
            'total_units' => $query->func()->sum('units_consumed'),
            'avg_response_time' => $query->func()->avg('response_time_ms')
        ))->first();

        return array(
            'total_calls' => (int)($summary->total_calls ?? 0),
            'successful_calls' => (int)($summary->successful_calls ?? 0),
            'failed_calls' => (int)($summary->failed_calls ?? 0),
            'success_rate' => $summary->total_calls > 0 
                ? round(($summary->successful_calls / $summary->total_calls) * 100, 2) 
                : 0,
            'total_cost' => (float)($summary->total_cost ?? 0),
            'total_units' => (float)($summary->total_units ?? 0),
            'avg_response_time_ms' => (int)($summary->avg_response_time ?? 0)
        );
    }

    /**
     * Get usage by service type
     *
     * @param int $hospitalId Hospital ID
     * @param string|null $startDate Start date
     * @param string|null $endDate End date
     * @return array Usage grouped by type
     */
    public function getUsageByType(int $hospitalId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = $this->find()
            ->where(array('hospital_id' => $hospitalId));

        if ($startDate) {
            $query->where(array('created >=' => $startDate));
        }

        if ($endDate) {
            $query->where(array('created <=' => $endDate));
        }

        $results = $query->select(array(
            'type',
            'call_count' => $query->func()->count('*'),
            'total_cost' => $query->func()->sum('total_cost_usd'),
            'total_units' => $query->func()->sum('units_consumed')
        ))
        ->group('type')
        ->order(array('total_cost' => 'DESC'))
        ->toArray();

        $usage = array();
        foreach ($results as $row) {
            $usage[$row->type] = array(
                'calls' => (int)$row->call_count,
                'cost' => (float)($row->total_cost ?? 0),
                'units' => (float)($row->total_units ?? 0)
            );
        }

        return $usage;
    }

    /**
     * Get usage by provider
     *
     * @param int $hospitalId Hospital ID
     * @param string $type Service type filter
     * @param string|null $startDate Start date
     * @param string|null $endDate End date
     * @return array Usage grouped by provider
     */
    public function getUsageByProvider(
        int $hospitalId,
        string $type,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $query = $this->find()
            ->where(array(
                'hospital_id' => $hospitalId,
                'type' => $type
            ));

        if ($startDate) {
            $query->where(array('created >=' => $startDate));
        }

        if ($endDate) {
            $query->where(array('created <=' => $endDate));
        }

        $results = $query->select(array(
            'provider',
            'call_count' => $query->func()->count('*'),
            'success_count' => $query->func()->sum(
                $query->newExpr()->addCase(
                    array($query->newExpr()->eq('status', 'success')),
                    array(1),
                    array('integer')
                )
            ),
            'total_cost' => $query->func()->sum('total_cost_usd'),
            'total_units' => $query->func()->sum('units_consumed'),
            'avg_response_time' => $query->func()->avg('response_time_ms')
        ))
        ->group('provider')
        ->order(array('total_cost' => 'DESC'))
        ->toArray();

        $usage = array();
        foreach ($results as $row) {
            $usage[$row->provider] = array(
                'calls' => (int)$row->call_count,
                'successful' => (int)($row->success_count ?? 0),
                'cost' => (float)($row->total_cost ?? 0),
                'units' => (float)($row->total_units ?? 0),
                'avg_response_time_ms' => (int)($row->avg_response_time ?? 0)
            );
        }

        return $usage;
    }

    /**
     * Get recent errors
     *
     * @param int $hospitalId Hospital ID
     * @param int $limit Number of errors to return
     * @return array Recent errors
     */
    public function getRecentErrors(int $hospitalId, int $limit = 10): array
    {
        return $this->find()
            ->where(array(
                'hospital_id' => $hospitalId,
                'status' => 'failed'
            ))
            ->order(array('created' => 'DESC'))
            ->limit($limit)
            ->toArray();
    }

    /**
     * Get monthly cost trend
     *
     * @param int $hospitalId Hospital ID
     * @param int $months Number of months to retrieve
     * @return array Monthly costs
     */
    public function getMonthlyCostTrend(int $hospitalId, int $months = 6): array
    {
        $query = $this->find()
            ->where(array('hospital_id' => $hospitalId))
            ->where(array('created >=' => date('Y-m-d', strtotime("-{$months} months"))));

        $results = $query->select(array(
            'month' => $query->func()->concat(array(
                $query->func()->year(array('created' => 'identifier')),
                "'-'",
                $query->func()->month(array('created' => 'identifier'))
            )),
            'total_cost' => $query->func()->sum('total_cost_usd'),
            'call_count' => $query->func()->count('*')
        ))
        ->group('month')
        ->order(array('month' => 'ASC'))
        ->toArray();

        $trend = array();
        foreach ($results as $row) {
            $trend[$row->month] = array(
                'cost' => (float)($row->total_cost ?? 0),
                'calls' => (int)$row->call_count
            );
        }

        return $trend;
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
        $query = $this->find()
            ->where(array('hospital_id' => $hospitalId, 'type' => $type));

        if ($provider) {
            $query->where(array('provider' => $provider));
        }

        // Apply date filter based on period
        if ($period === 'current_month') {
            $query->where(array('created >=' => date('Y-m-01 00:00:00')));
        } elseif ($period === 'current_year') {
            $query->where(array('created >=' => date('Y-01-01 00:00:00')));
        }

        $result = $query->select(array(
            'total' => $query->func()->sum('total_cost_usd')
        ))->first();

        $totalCost = (float)($result->total ?? 0);
        $remaining = $budgetLimit - $totalCost;
        $percentUsed = $budgetLimit > 0 ? ($totalCost / $budgetLimit) * 100 : 0;

        return array(
            'budget_limit' => $budgetLimit,
            'total_spent' => $totalCost,
            'remaining' => $remaining,
            'percent_used' => round($percentUsed, 2),
            'is_exceeded' => $totalCost > $budgetLimit,
            'is_warning' => $percentUsed >= 80, // 80% threshold
            'period' => $period
        );
    }
}
