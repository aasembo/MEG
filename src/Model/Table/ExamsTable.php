<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ExamsTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('exams');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Hospitals', [
            'foreignKey' => 'hospital_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Modalities', [
            'foreignKey' => 'modality_id',
        ]);
        $this->belongsTo('Departments', [
            'foreignKey' => 'department_id',
        ]);
        $this->hasMany('ExamsProcedures', [
            'foreignKey' => 'exam_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator {
        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->nonNegativeInteger('hospital_id')
            ->notEmptyString('hospital_id');

        $validator
            ->allowEmptyString('modality_id');

        $validator
            ->allowEmptyString('department_id');

        $validator
            ->integer('duration')
            ->allowEmptyString('duration');

        $validator
            ->decimal('cost')
            ->allowEmptyString('cost');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn('hospital_id', 'Hospitals'), ['errorField' => 'hospital_id']);
        $rules->add($rules->existsIn('modality_id', 'Modalities'), ['errorField' => 'modality_id']);
        $rules->add($rules->existsIn('department_id', 'Departments'), ['errorField' => 'department_id']);

        return $rules;
    }
}