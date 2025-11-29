<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * ReportImages Model
 *
 * @property \App\Model\Table\ReportsTable&\Cake\ORM\Association\BelongsTo $Reports
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\ReportImage newEmptyEntity()
 * @method \App\Model\Entity\ReportImage newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\ReportImage> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\ReportImage get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\ReportImage findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\ReportImage patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\ReportImage> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\ReportImage|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\ReportImage saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\ReportImage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ReportImage>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ReportImage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ReportImage> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ReportImage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ReportImage>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\ReportImage>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\ReportImage> deleteManyOrFail(iterable $entities, array $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class ReportImagesTable extends Table
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

        $this->setTable('report_images');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Reports', [
            'foreignKey' => 'report_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'LEFT',
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
            ->integer('report_id')
            ->requirePresence('report_id', 'create')
            ->notEmptyString('report_id');

        $validator
            ->integer('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->scalar('s3_key')
            ->maxLength('s3_key', 255)
            ->requirePresence('s3_key', 'create')
            ->notEmptyString('s3_key');

        $validator
            ->scalar('file_path')
            ->maxLength('file_path', 512)
            ->requirePresence('file_path', 'create')
            ->notEmptyString('file_path');

        $validator
            ->scalar('original_filename')
            ->maxLength('original_filename', 255)
            ->requirePresence('original_filename', 'create')
            ->notEmptyString('original_filename');

        $validator
            ->integer('file_size')
            ->requirePresence('file_size', 'create')
            ->notEmptyString('file_size');

        $validator
            ->scalar('mime_type')
            ->maxLength('mime_type', 100)
            ->requirePresence('mime_type', 'create')
            ->notEmptyString('mime_type');

        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->integer('slide_order')
            ->notEmptyString('slide_order');

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
        $rules->add($rules->existsIn(['report_id'], 'Reports'), ['errorField' => 'report_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
