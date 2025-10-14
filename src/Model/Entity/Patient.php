<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Patient extends Entity {
    protected array $_accessible = [
        'user_id' => true,
        'hospital_id' => true,
        'gender' => true,
        'dob' => true,
        'age' => true,
        'phone' => true,
        'address' => true,
        'notes' => true,
        'emergency_contact_name' => true,
        'emergency_contact_phone' => true,
        'medical_record_number' => true,
        'financial_record_number' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'hospital' => true,
        'cases' => true,
    ];
}
