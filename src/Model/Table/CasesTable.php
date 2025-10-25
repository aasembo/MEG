<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Cases Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $PatientUsers
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $CurrentUsers
 * @property \App\Model\Table\CaseVersionsTable&\Cake\ORM\Association\BelongsTo $CurrentVersions
 * @property \App\Model\Table\HospitalsTable&\Cake\ORM\Association\BelongsTo $Hospitals
 * @property \App\Model\Table\DepartmentsTable&\Cake\ORM\Association\BelongsTo $Departments
 * @property \App\Model\Table\SedationsTable&\Cake\ORM\Association\BelongsTo $Sedations
 * @property \App\Model\Table\CaseAssignmentsTable&\Cake\ORM\Association\HasMany $CaseAssignments
 * @property \App\Model\Table\CaseAuditsTable&\Cake\ORM\Association\HasMany $CaseAudits
 * @property \App\Model\Table\CaseVersionsTable&\Cake\ORM\Association\HasMany $CaseVersions
 * @property \App\Model\Table\CasesExamsProceduresTable&\Cake\ORM\Association\HasMany $CasesExamsProcedures
 * @property \App\Model\Table\DocumentsTable&\Cake\ORM\Association\HasMany $Documents
 * @property \App\Model\Table\ExamsProceduresTable&\Cake\ORM\Association\BelongsToMany $ExamsProcedures
 *
 * @method \App\Model\Entity\MedicalCase newEmptyEntity()
 * @method \App\Model\Entity\MedicalCase newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\MedicalCase> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\MedicalCase get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\MedicalCase findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\MedicalCase patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\MedicalCase> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\MedicalCase|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\MedicalCase saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\MedicalCase>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MedicalCase>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MedicalCase>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MedicalCase> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MedicalCase>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MedicalCase>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\MedicalCase>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\MedicalCase> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CasesTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('cases');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
        $this->setEntityClass('MedicalCase');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Hospitals', [
            'foreignKey' => 'hospital_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('PatientUsers', [
            'className' => 'Users',
            'foreignKey' => 'patient_id',
        ]);
        $this->belongsTo('CurrentUsers', [
            'className' => 'Users',
            'foreignKey' => 'current_user_id',
        ]);
        $this->belongsTo('CurrentVersions', [
            'className' => 'CaseVersions',
            'foreignKey' => 'current_version_id',
        ]);
        $this->belongsTo('Departments', [
            'foreignKey' => 'department_id',
        ]);
        $this->belongsTo('Sedations', [
            'foreignKey' => 'sedation_id',
        ]);
        $this->hasMany('CaseAssignments', [
            'foreignKey' => 'case_id',
        ]);
        $this->hasMany('CaseAudits', [
            'foreignKey' => 'case_id',
        ]);
        $this->hasMany('CaseVersions', [
            'foreignKey' => 'case_id',
        ]);
        $this->hasMany('CasesExamsProcedures', [
            'foreignKey' => 'case_id',
        ]);
        $this->hasMany('Documents', [
            'foreignKey' => 'case_id',
        ]);
        $this->hasMany('DocumentsBkp', [
            'foreignKey' => 'case_id',
        ]);
        $this->belongsToMany('ExamsProcedures', [
            'foreignKey' => 'case_id',
            'targetForeignKey' => 'exams_procedure_id',
            'joinTable' => 'cases_exams_procedures',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonNegativeInteger('user_id')
            ->notEmptyString('user_id');

        $validator
            ->nonNegativeInteger('hospital_id')
            ->notEmptyString('hospital_id');

        $validator
            ->nonNegativeInteger('patient_id')
            ->allowEmptyString('patient_id');

        $validator
            ->nonNegativeInteger('department_id')
            ->allowEmptyString('department_id');

        $validator
            ->nonNegativeInteger('sedation_id')
            ->allowEmptyString('sedation_id');

        $validator
            ->nonNegativeInteger('current_user_id')
            ->allowEmptyString('current_user_id');

        $validator
            ->nonNegativeInteger('current_version_id')
            ->allowEmptyString('current_version_id');

        $validator
            ->date('date')
            ->allowEmptyDate('date');

        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->notEmptyString('status');

        $validator
            ->scalar('priority')
            ->maxLength('priority', 10)
            ->notEmptyString('priority');

        $validator
            ->scalar('notes')
            ->allowEmptyString('notes');

        $validator
            ->scalar('symptoms')
            ->allowEmptyString('symptoms');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        $rules->add($rules->existsIn(['hospital_id'], 'Hospitals'), ['errorField' => 'hospital_id']);
        
        // Only validate patient_id if it's not empty
        $rules->add($rules->existsIn(['patient_id'], 'PatientUsers'), [
            'errorField' => 'patient_id',
            'allowNullableNulls' => true
        ]);
        
        // Only validate current_user_id if it's not empty
        $rules->add($rules->existsIn(['current_user_id'], 'CurrentUsers'), [
            'errorField' => 'current_user_id',
            'allowNullableNulls' => true
        ]);
        
        // Only validate current_version_id if it's not empty
        $rules->add($rules->existsIn(['current_version_id'], 'CurrentVersions'), [
            'errorField' => 'current_version_id',
            'allowNullableNulls' => true
        ]);
        
        // Only validate department_id if it's not empty  
        $rules->add($rules->existsIn(['department_id'], 'Departments'), [
            'errorField' => 'department_id',
            'allowNullableNulls' => true
        ]);
        
        // Only validate sedation_id if it's not empty
        $rules->add($rules->existsIn(['sedation_id'], 'Sedations'), [
            'errorField' => 'sedation_id', 
            'allowNullableNulls' => true
        ]);

        return $rules;
    }
}
