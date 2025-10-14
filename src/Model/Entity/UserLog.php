<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class UserLog extends Entity {
    protected array $_accessible = [
        'user_id' => true,
        'event_type' => true,
        'description' => true,
        'event_data' => true,
        'ip_address' => true,
        'user_agent' => true,
        'hospital_id' => true,
        'role_type' => true,
        'status' => true,
        'created' => true,
        'user' => true,
        'hospital' => true,
    ];
}
