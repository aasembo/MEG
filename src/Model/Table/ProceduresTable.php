<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ProceduresTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('procedures');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Hospitals', [
            'foreignKey' => 'hospital_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Sedations', [
            'foreignKey' => 'sedation_id',
        ]);
        $this->belongsTo('Departments', [
            'foreignKey' => 'department_id',
        ]);
        $this->belongsToMany('Exams', [
            'foreignKey' => 'procedure_id',
            'targetForeignKey' => 'exam_id',
            'joinTable' => 'exams_procedures',
        ]);
    }

    public function validationDefault(Validator $validator): Validator {
        $validator
            ->nonNegativeInteger('hospital_id')
            ->notEmptyString('hospital_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('type')
            ->maxLength('type', 100)
            ->allowEmptyString('type');

        $validator
            ->integer('sedation_id')
            ->allowEmptyString('sedation_id');

        $validator
            ->integer('department_id')
            ->allowEmptyString('department_id');

        $validator
            ->scalar('risk_level')
            ->maxLength('risk_level', 50)
            ->allowEmptyString('risk_level');

        $validator
            ->boolean('consent_required')
            ->allowEmptyString('consent_required');

        $validator
            ->allowEmptyString('duration_minutes');

        $validator
            ->nonNegativeInteger('cost')
            ->allowEmptyString('cost');

        $validator
            ->scalar('notes')
            ->allowEmptyString('notes');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn(['hospital_id'], 'Hospitals'), ['errorField' => 'hospital_id']);
        $rules->add($rules->existsIn(['sedation_id'], 'Sedations'), ['errorField' => 'sedation_id']);
        $rules->add($rules->existsIn(['department_id'], 'Departments'), ['errorField' => 'department_id']);

        return $rules;
    }
}
