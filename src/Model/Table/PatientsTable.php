<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class PatientsTable extends Table {
    public function initialize(array $config): void {
        parent::initialize($config);

        $this->setTable('patients');
        $this->setDisplayField('gender');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Hospitals', [
            'foreignKey' => 'hospital_id',
            'joinType' => 'INNER',
        ]);
        $this->hasMany('Cases', [
            'foreignKey' => 'patient_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator {
        $validator
            ->nonNegativeInteger('user_id')
            ->notEmptyString('user_id');

        $validator
            ->nonNegativeInteger('hospital_id')
            ->notEmptyString('hospital_id');

        $validator
            ->scalar('gender')
            ->maxLength('gender', 2)
            ->requirePresence('gender', 'create')
            ->notEmptyString('gender')
            ->inList('gender', ['M', 'F', 'O'], 'Gender must be M (Male), F (Female), or O (Other)');

        $validator
            ->date('dob')
            ->requirePresence('dob', 'create')
            ->notEmptyDate('dob');

        $validator
            ->scalar('phone')
            ->maxLength('phone', 20)
            ->allowEmptyString('phone');

        $validator
            ->scalar('address')
            ->allowEmptyString('address');

        $validator
            ->scalar('notes')
            ->allowEmptyString('notes');

        $validator
            ->scalar('emergency_contact_name')
            ->maxLength('emergency_contact_name', 255)
            ->allowEmptyString('emergency_contact_name');

        $validator
            ->scalar('emergency_contact_phone')
            ->maxLength('emergency_contact_phone', 255)
            ->allowEmptyString('emergency_contact_phone');

        $validator
            ->scalar('medical_record_number')
            ->maxLength('medical_record_number', 50)
            ->allowEmptyString('medical_record_number');

        $validator
            ->scalar('financial_record_number')
            ->maxLength('financial_record_number', 50)
            ->allowEmptyString('financial_record_number');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['hospital_id'], 'Hospitals'), ['errorField' => 'hospital_id']);

        return $rules;
    }
}
