<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class SedationsTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('sedations');
        $this->setDisplayField('level');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Hospitals', [
            'foreignKey' => 'hospital_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Procedures', [
            'foreignKey' => 'sedation_id',
        ]);
        $this->hasMany('Cases', [
            'foreignKey' => 'sedation_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator {
        $validator
            ->nonNegativeInteger('hospital_id')
            ->notEmptyString('hospital_id');

        $validator
            ->scalar('level')
            ->maxLength('level', 100)
            ->requirePresence('level', 'create')
            ->notEmptyString('level');

        $validator
            ->scalar('type')
            ->maxLength('type', 100)
            ->allowEmptyString('type');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->notEmptyString('monitoring_required');

        $validator
            ->notEmptyString('pre_medication_required');

        $validator
            ->scalar('risk_category')
            ->maxLength('risk_category', 10)
            ->notEmptyString('risk_category');

        $validator
            ->allowEmptyString('recovery_time');

        $validator
            ->scalar('medications')
            ->allowEmptyString('medications');

        $validator
            ->scalar('contraindications')
            ->allowEmptyString('contraindications');

        $validator
            ->scalar('notes')
            ->allowEmptyString('notes');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn(['hospital_id'], 'Hospitals'), ['errorField' => 'hospital_id']);

        return $rules;
    }
}
