<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class CaseAssignmentsTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('case_assignments');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Cases', [
            'foreignKey' => 'case_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('CaseVersions', [
            'foreignKey' => 'case_version_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('AssignedToUsers', [
            'className' => 'Users',
            'foreignKey' => 'assigned_to',
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
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->integer('assigned_to')
            ->notEmptyString('assigned_to');

        $validator
            ->dateTime('timestamp')
            ->notEmptyDateTime('timestamp');

        $validator
            ->scalar('notes')
            ->allowEmptyString('notes');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn('case_id', 'Cases'), ['errorField' => 'case_id']);
        $rules->add($rules->existsIn('case_version_id', 'CaseVersions'), ['errorField' => 'case_version_id']);
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn('assigned_to', 'AssignedToUsers'), ['errorField' => 'assigned_to']);

        return $rules;
    }

    public function findByCase(SelectQuery $query, array $options): SelectQuery {
        return $query->where(['CaseAssignments.case_id' => $options['case_id']]);
    }
}