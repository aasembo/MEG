<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Doctor extends Entity {
    protected array $_accessible = [
        'user_id' => true,
        'hospital_id' => true,
        'phone' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'hospital' => true,
        'cases' => true,
    ];
}
