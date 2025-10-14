<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ScientistsTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('scientists');
        $this->setDisplayField('id');
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
            ->maxLength('phone', 50)
            ->allowEmptyString('phone');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['hospital_id'], 'Hospitals'), ['errorField' => 'hospital_id']);

        return $rules;
    }
}
