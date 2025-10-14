<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use App\Constants\SiteConstants;

class HospitalsTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('hospitals');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('Users', [
            'foreignKey' => 'hospital_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator {
        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->scalar('subdomain')
            ->maxLength('subdomain', 100)
            ->requirePresence('subdomain', 'create')
            ->notEmptyString('subdomain');

        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->requirePresence('status', 'create')
            ->notEmptyString('status')
            ->inList('status', ['active', 'inactive']);

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->isUnique(['name'], 'Hospital name must be unique.'));
        $rules->add($rules->isUnique(['subdomain'], 'Hospital subdomain must be unique.'));

        return $rules;
    }

    public function findActive(SelectQuery $query, array $options): SelectQuery {
        return $query->where(['status' => SiteConstants::HOSPITAL_STATUS_ACTIVE]);
    }
}