<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Setting Entity
 *
 * @property int $id
 * @property int $hospital_id
 * @property string $category
 * @property string $name
 * @property string $value
 * @property string $data_type
 * @property bool $is_encrypted
 * @property bool $is_active
 * @property string|null $description
 * @property int|null $last_modified_by
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Hospital $hospital
 * @property \App\Model\Entity\User $last_modified_by_user
 */
class Setting extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'hospital_id' => true,
        'category' => true,
        'name' => true,
        'value' => true,
        'data_type' => true,
        'is_encrypted' => true,
        'is_active' => true,
        'description' => true,
        'last_modified_by' => true,
        'created' => true,
        'modified' => true,
        'hospital' => true,
        'last_modified_by_user' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'value',
    ];
}
