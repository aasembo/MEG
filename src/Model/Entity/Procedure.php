<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Procedure extends Entity {
    protected array $_accessible = [
        'hospital_id' => true,
        'name' => true,
        'description' => true,
        'type' => true,
        'sedation_id' => true,
        'department_id' => true,
        'risk_level' => true,
        'consent_required' => true,
        'duration_minutes' => true,
        'cost' => true,
        'notes' => true,
        'created' => true,
        'modified' => true,
        'hospital' => true,
        'sedation' => true,
        'department' => true,
        'exams' => true,
    ];
}
