<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\Constants\SiteConstants;

class CasesExamsProceduresTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('cases_exams_procedures');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Cases', [
            'foreignKey' => 'case_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ExamsProcedures', [
            'foreignKey' => 'exams_procedure_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Documents', [
            'foreignKey' => 'cases_exams_procedure_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator {
        $validator
            ->integer('case_id')
            ->notEmptyString('case_id');

        $validator
            ->integer('exams_procedure_id')
            ->notEmptyString('exams_procedure_id');

        $validator
            ->dateTime('scheduled_at')
            ->allowEmptyDateTime('scheduled_at');

        $validator
            ->scalar('status')
            ->maxLength('status', 50)
            ->allowEmptyString('status')
            ->inList('status', [
                SiteConstants::CASE_STATUS_PENDING, 
                SiteConstants::CASE_STATUS_SCHEDULED, 
                SiteConstants::CASE_STATUS_IN_PROGRESS, 
                SiteConstants::CASE_STATUS_COMPLETED, 
                SiteConstants::CASE_STATUS_CANCELLED
            ]);

        $validator
            ->scalar('notes')
            ->allowEmptyString('notes');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn(['case_id'], 'Cases'), ['errorField' => 'case_id']);
        $rules->add($rules->existsIn(['exams_procedure_id'], 'ExamsProcedures'), ['errorField' => 'exams_procedure_id']);

        return $rules;
    }

    public function findWithDocuments(SelectQuery $query, array $options): SelectQuery {
        return $query->contain([
            'Cases',
            'ExamsProcedures' => [
                'Exams',
                'Procedures'
            ],
            'Documents'
        ]);
    }

    public function findByCase(SelectQuery $query, array $options): SelectQuery {
        return $query
            ->where(['CasesExamsProcedures.case_id' => $options['case_id']])
            ->contain([
                'ExamsProcedures' => [
                    'Exams',
                    'Procedures'
                ],
                'Documents'
            ]);
    }
}