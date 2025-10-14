<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class CaseAuditsTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('case_audits');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp', [
            'events' => [
                'Model.beforeSave' => [
                    'created' => 'new'
                ]
            ]
        ]);

        $this->belongsTo('Cases', [
            'foreignKey' => 'case_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('CaseVersions', [
            'foreignKey' => 'case_version_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('ChangedByUsers', [
            'className' => 'Users',
            'foreignKey' => 'changed_by',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator {
        $validator
            ->integer('case_id')
            ->notEmptyString('case_id');

        $validator
            ->integer('case_version_id')
            ->notEmptyString('case_version_id');

        $validator
            ->scalar('field_name')
            ->maxLength('field_name', 100)
            ->notEmptyString('field_name');

        $validator
            ->scalar('old_value')
            ->maxLength('old_value', 255)
            ->notEmptyString('old_value');

        $validator
            ->scalar('new_value')
            ->maxLength('new_value', 255)
            ->notEmptyString('new_value');

        $validator
            ->integer('changed_by')
            ->notEmptyString('changed_by');

        $validator
            ->dateTime('timestamp')
            ->notEmptyDateTime('timestamp');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn('case_id', 'Cases'), ['errorField' => 'case_id']);
        $rules->add($rules->existsIn('case_version_id', 'CaseVersions'), ['errorField' => 'case_version_id']);
        $rules->add($rules->existsIn('changed_by', 'ChangedByUsers'), ['errorField' => 'changed_by']);

        return $rules;
    }

    public function findByCase(SelectQuery $query, array $options): SelectQuery {
        return $query
            ->where(['CaseAudits.case_id' => $options['case_id']])
            ->orderBy(['CaseAudits.timestamp' => 'DESC']);
    }

    public function logChange(int $caseId, int $caseVersionId, string $fieldName, string $oldValue, string $newValue, int $changedBy) {
        $audit = $this->newEntity([
            'case_id' => $caseId,
            'case_version_id' => $caseVersionId,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => $changedBy,
            'timestamp' => new \DateTime()
        ]);

        return $this->save($audit);
    }
}