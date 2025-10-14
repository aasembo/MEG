<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Nurse extends Entity {
    protected array $_accessible = [
        'user_id' => true,
        'hospital_id' => true,
        'phone' => true,
        'gender' => true,
        'dob' => true,
        'age' => true,
        'record_number' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'hospital' => true,
    ];
}
