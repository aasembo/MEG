<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ServiceUsageLog Entity
 *
 * @property int $id
 * @property int $hospital_id
 * @property string $service_type
 * @property string $service_name
 * @property string|null $provider
 * @property int|null $tokens_used
 * @property float|null $cost
 * @property int|null $user_id
 * @property string|null $metadata
 * @property \Cake\I18n\FrozenTime $created
 *
 * @property \App\Model\Entity\Hospital $hospital
 * @property \App\Model\Entity\User $user
 */
class ServiceUsageLog extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'hospital_id' => true,
        'service_type' => true,
        'service_name' => true,
        'provider' => true,
        'tokens_used' => true,
        'cost' => true,
        'user_id' => true,
        'metadata' => true,
        'created' => true,
        'hospital' => true,
        'user' => true,
    ];
}
