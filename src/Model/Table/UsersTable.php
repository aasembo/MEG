<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Constants\SiteConstants;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('username');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Roles', [
            'foreignKey' => 'role_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Hospitals', [
            'foreignKey' => 'hospital_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Cases', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Doctors', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Nurses', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Patients', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Scientists', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('Technicians', [
            'foreignKey' => 'user_id',
        ]);
        $this->hasMany('UserLogs', [
            'foreignKey' => 'user_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator {
        $validator
            ->nonNegativeInteger('role_id')
            ->notEmptyString('role_id');

        $validator
            ->integer('hospital_id')
            ->notEmptyString('hospital_id');

        $validator
            ->scalar('username')
            ->maxLength('username', 255)
            ->requirePresence('username', 'create')
            ->notEmptyString('username');

        $validator
            ->scalar('first_name')
            ->maxLength('first_name', 255)
            ->requirePresence('first_name', 'create')
            ->notEmptyString('first_name');

        $validator
            ->scalar('last_name')
            ->maxLength('last_name', 255)
            ->requirePresence('last_name', 'create')
            ->notEmptyString('last_name');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->allowEmptyString('password');

        $validator
            ->scalar('okta_id')
            ->maxLength('okta_id', 50)
            ->allowEmptyString('okta_id');

        $validator
            ->scalar('status')
            ->maxLength('status', 10)
            ->notEmptyString('status');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);
        $rules->add($rules->existsIn(['role_id'], 'Roles'), ['errorField' => 'role_id']);
        
        // Custom rule for hospital_id: allow 0 for super users, otherwise must exist in hospitals table
        $rules->add(function ($entity, $options) {
            // If hospital_id is 0, check if user has super role
            if ($entity->hospital_id === 0) {
                // Load role to check type
                if ($entity->role_id) {
                    $rolesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Roles');
                    $role = $rolesTable->find()->where(['id' => $entity->role_id])->first();
                    if ($role && $role->type === 'super') {
                        return true; // Allow hospital_id = 0 for super users
                    }
                }
                return false; // Don't allow hospital_id = 0 for non-super users
            }
            
            // For hospital_id > 0, check if it exists in hospitals table
            $hospitalsTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Hospitals');
            return $hospitalsTable->exists(['id' => $entity->hospital_id]);
        }, ['errorField' => 'hospital_id', 'message' => 'Invalid hospital assignment']);

        return $rules;
    }

    public function findAuth(SelectQuery $query, array $options): SelectQuery {
        return $query
            ->contain(['Roles'])
            ->leftJoinWith('Hospitals') // Use LEFT JOIN for hospitals to include super users with hospital_id = 0
            ->where([
                'Users.status' => SiteConstants::USER_STATUS_ACTIVE,
                'Roles.type IN' => [
                    SiteConstants::ROLE_TYPE_ADMINISTRATOR,
                    SiteConstants::ROLE_TYPE_SUPER,
                    SiteConstants::ROLE_TYPE_DOCTOR,
                    SiteConstants::ROLE_TYPE_SCIENTIST,
                    SiteConstants::ROLE_TYPE_TECHNICIAN,
                    SiteConstants::ROLE_TYPE_NURSE
                ]
            ]);
    }
}
