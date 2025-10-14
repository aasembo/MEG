<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UserLogsTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('user_logs');
        $this->setDisplayField('event_type');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
        $this->belongsTo('Hospitals', [
            'foreignKey' => 'hospital_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator {
        $validator
            ->nonNegativeInteger('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->nonNegativeInteger('hospital_id')
            ->allowEmptyString('hospital_id');

        $validator
            ->scalar('event_type')
            ->maxLength('event_type', 50)
            ->requirePresence('event_type', 'create')
            ->notEmptyString('event_type');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('event_data')
            ->allowEmptyString('event_data');

        $validator
            ->scalar('ip_address')
            ->maxLength('ip_address', 45)
            ->allowEmptyString('ip_address');

        $validator
            ->scalar('user_agent')
            ->maxLength('user_agent', 500)
            ->allowEmptyString('user_agent');

        $validator
            ->scalar('role_type')
            ->maxLength('role_type', 50)
            ->allowEmptyString('role_type');

        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->allowEmptyString('status');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id', 'message' => 'User does not exist']);
        $rules->add($rules->existsIn(['hospital_id'], 'Hospitals'), ['errorField' => 'hospital_id', 'message' => 'Hospital does not exist']);

        return $rules;
    }
}
