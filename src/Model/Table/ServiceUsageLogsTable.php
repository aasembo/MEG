<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ServiceUsageLogs Model
 *
 * Tracks usage of AI services, notifications, and other metered services
 * for cost tracking and billing purposes
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
            ->scalar('service_type')
            ->maxLength('service_type', 50)
            ->requirePresence('service_type', 'create')
            ->notEmptyString('service_type')
            ->add('service_type', 'validType', array(
                'rule' => array('inList', array('ai', 'notification', 'storage', 'other')),
                'message' => 'Service type must be ai, notification, storage, or other',
            ));

        $validator
            ->scalar('service_name')
            ->maxLength('service_name', 100)
            ->requirePresence('service_name', 'create')
            ->notEmptyString('service_name');

        $validator
            ->scalar('provider')
            ->maxLength('provider', 50)
            ->allowEmptyString('provider');

        $validator
            ->integer('tokens_used')
            ->allowEmptyString('tokens_used');

        $validator
            ->decimal('cost')
            ->allowEmptyString('cost');

        $validator
            ->integer('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->scalar('metadata')
            ->allowEmptyString('metadata');

        return $validator;
    }

    /**
     * Get usage summary for a hospital
     *
     * @param int $hospitalId Hospital ID
     * @param string $period Period (today, week, month, year)
     * @return array Summary statistics
     */
    public function getUsageSummary(int $hospitalId, string $period = 'month'): array
    {
        $conditions = array('hospital_id' => $hospitalId);

        // Add date filter
        $dateField = 'created >=';
        switch ($period) {
            case 'today':
                $conditions[$dateField] = date('Y-m-d 00:00:00');
                break;
            case 'week':
                $conditions[$dateField] = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case 'month':
                $conditions[$dateField] = date('Y-m-01 00:00:00');
                break;
            case 'year':
                $conditions[$dateField] = date('Y-01-01 00:00:00');
                break;
        }

        // Get summary by service type
        $query = $this->find()
            ->where($conditions)
            ->select(array(
                'service_type',
                'provider',
                'total_tokens' => 'SUM(tokens_used)',
                'total_cost' => 'SUM(cost)',
                'request_count' => 'COUNT(*)',
            ))
            ->group(array('service_type', 'provider'));

        $summary = array(
            'period' => $period,
            'total_cost' => 0.0,
            'total_tokens' => 0,
            'total_requests' => 0,
            'by_service' => array(),
        );

        foreach ($query as $row) {
            $serviceType = $row->service_type;
            $provider = $row->provider ?: 'unknown';

            if (!isset($summary['by_service'][$serviceType])) {
                $summary['by_service'][$serviceType] = array(
                    'total_cost' => 0.0,
                    'total_tokens' => 0,
                    'total_requests' => 0,
                    'by_provider' => array(),
                );
            }

            $summary['by_service'][$serviceType]['by_provider'][$provider] = array(
                'tokens' => (int)$row->total_tokens,
                'cost' => (float)$row->total_cost,
                'requests' => (int)$row->request_count,
            );

            $summary['by_service'][$serviceType]['total_cost'] += (float)$row->total_cost;
            $summary['by_service'][$serviceType]['total_tokens'] += (int)$row->total_tokens;
            $summary['by_service'][$serviceType]['total_requests'] += (int)$row->request_count;

            $summary['total_cost'] += (float)$row->total_cost;
            $summary['total_tokens'] += (int)$row->total_tokens;
            $summary['total_requests'] += (int)$row->request_count;
        }

        return $summary;
    }

    /**
     * Get top users by cost
     *
     * @param int $hospitalId Hospital ID
     * @param int $limit Number of users to return
     * @param string $period Period (today, week, month, year)
     * @return array Top users
     */
    public function getTopUsers(int $hospitalId, int $limit = 10, string $period = 'month'): array
    {
        $conditions = array('hospital_id' => $hospitalId);

        // Add date filter
        $dateField = 'created >=';
        switch ($period) {
            case 'today':
                $conditions[$dateField] = date('Y-m-d 00:00:00');
                break;
            case 'week':
                $conditions[$dateField] = date('Y-m-d 00:00:00', strtotime('-7 days'));
                break;
            case 'month':
                $conditions[$dateField] = date('Y-m-01 00:00:00');
                break;
            case 'year':
                $conditions[$dateField] = date('Y-01-01 00:00:00');
                break;
        }

        $query = $this->find()
            ->where($conditions)
            ->contain('Users')
            ->select(array(
                'user_id',
                'total_cost' => 'SUM(cost)',
                'total_requests' => 'COUNT(*)',
            ))
            ->group('user_id')
            ->order(array('total_cost' => 'DESC'))
            ->limit($limit);

        $topUsers = array();
        foreach ($query as $row) {
            $topUsers[] = array(
                'user_id' => $row->user_id,
                'user' => $row->user,
                'total_cost' => (float)$row->total_cost,
                'total_requests' => (int)$row->total_requests,
            );
        }

        return $topUsers;
    }
}
