<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ExamsProceduresTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('exams_procedures');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Exams', [
            'foreignKey' => 'exam_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Procedures', [
            'foreignKey' => 'procedure_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('CasesExamsProcedures', [
            'foreignKey' => 'exams_procedure_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator {
        $validator
            ->integer('exam_id')
            ->notEmptyString('exam_id');

        $validator
            ->integer('procedure_id')
            ->notEmptyString('procedure_id');

        $validator
            ->scalar('notes')
            ->allowEmptyString('notes');

        $validator
            ->scalar('preparation_instructions')
            ->allowEmptyString('preparation_instructions');

        $validator
            ->scalar('post_procedure_care')
            ->allowEmptyString('post_procedure_care');

        $validator
            ->notEmptyString('contrast_required');

        $validator
            ->notEmptyString('sedation_required');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn(['exam_id'], 'Exams'), ['errorField' => 'exam_id']);
        $rules->add($rules->existsIn(['procedure_id'], 'Procedures'), ['errorField' => 'procedure_id']);

        return $rules;
    }
}
