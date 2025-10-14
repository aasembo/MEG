<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class CaseVersionsTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('case_versions');
        $this->setDisplayField('id');
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
        $this->hasMany('CaseAssignments', [
            'foreignKey' => 'case_version_id',
        ]);
        $this->hasMany('CaseAudits', [
            'foreignKey' => 'case_version_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator {
        $validator
            ->integer('case_id')
            ->notEmptyString('case_id');

        $validator
            ->integer('version_number')
            ->notEmptyString('version_number');

        $validator
            ->integer('user_id')
            ->notEmptyString('user_id');

        $validator
            ->dateTime('timestamp')
            ->notEmptyDateTime('timestamp');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn('case_id', 'Cases'), ['errorField' => 'case_id']);
        $rules->add($rules->existsIn('user_id', 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    public function findLatestVersion(SelectQuery $query, array $options): SelectQuery {
        return $query
            ->where(['CaseVersions.case_id' => $options['case_id']])
            ->orderBy(['CaseVersions.version_number' => 'DESC'])
            ->limit(1);
    }
}