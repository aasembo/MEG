<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class NursesTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('nurses');
        $this->setDisplayField('gender');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Hospitals', [
            'foreignKey' => 'hospital_id',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator {
        $validator
            ->nonNegativeInteger('user_id')
            ->notEmptyString('user_id');

        $validator
            ->nonNegativeInteger('hospital_id')
            ->notEmptyString('hospital_id');

        $validator
            ->scalar('phone')
            ->maxLength('phone', 20)
            ->allowEmptyString('phone');

        $validator
            ->scalar('gender')
            ->maxLength('gender', 2)
            ->requirePresence('gender', 'create')
            ->notEmptyString('gender');

        $validator
            ->date('dob')
            ->requirePresence('dob', 'create')
            ->notEmptyDate('dob');

        $validator
            ->requirePresence('age', 'create')
            ->notEmptyString('age');

        $validator
            ->nonNegativeInteger('record_number')
            ->requirePresence('record_number', 'create')
            ->notEmptyString('record_number');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['hospital_id'], 'Hospitals'), ['errorField' => 'hospital_id']);

        return $rules;
    }
}
