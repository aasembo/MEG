<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ServiceUsageLog Entity
 *
 * @property int $id
 * @property int $hospital_id
 * @property string $type
 * @property string $provider
 * @property int|null $related_id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $request_data
 * @property string|null $response_data
 * @property string $status
 * @property int|null $response_time_ms
 * @property string|null $error_code
 * @property string|null $error_message
 * @property float|null $units_consumed
 * @property float|null $unit_cost
 * @property float|null $total_cost_usd
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
        'type' => true,
        'provider' => true,
        'related_id' => true,
        'user_id' => true,
        'action' => true,
        'request_data' => true,
        'response_data' => true,
        'status' => true,
        'response_time_ms' => true,
        'error_code' => true,
        'error_message' => true,
        'units_consumed' => true,
        'unit_cost' => true,
        'total_cost_usd' => true,
        'metadata' => true,
        'created' => true,
        'hospital' => true,
        'user' => true,
    ];
}
