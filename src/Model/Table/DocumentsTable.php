<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
class DocumentsTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('documents');
        $this->setDisplayField('document_type');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Cases', [
            'foreignKey' => 'case_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('CasesExamsProcedures', [
            'foreignKey' => 'cases_exams_procedure_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator {
        $validator
            ->integer('case_id')
            ->notEmptyString('case_id');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('cases_exams_procedure_id')
            ->allowEmptyString('cases_exams_procedure_id');

        $validator
            ->scalar('document_type')
            ->maxLength('document_type', 50)
            ->requirePresence('document_type', 'create')
            ->notEmptyString('document_type');

        $validator
            ->scalar('file_path')
            ->maxLength('file_path', 255)
            ->requirePresence('file_path', 'create')
            ->notEmptyString('file_path');

        $validator
            ->scalar('file_type')
            ->maxLength('file_type', 50)
            ->allowEmptyString('file_type');

        $validator
            ->integer('file_size')
            ->allowEmptyString('file_size');

        $validator
            ->scalar('original_filename')
            ->maxLength('original_filename', 255)
            ->allowEmptyString('original_filename');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->dateTime('uploaded_at')
            ->requirePresence('uploaded_at', 'create')
            ->notEmptyDateTime('uploaded_at');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn(['case_id'], 'Cases'), ['errorField' => 'case_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
