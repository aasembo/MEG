<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Sedation extends Entity {
    protected array $_accessible = [
        'hospital_id' => true,
        'level' => true,
        'type' => true,
        'description' => true,
        'monitoring_required' => true,
        'pre_medication_required' => true,
        'risk_category' => true,
        'recovery_time' => true,
        'medications' => true,
        'contraindications' => true,
        'notes' => true,
        'created' => true,
        'modified' => true,
        'hospital' => true,
        'procedures' => true,
        'cases' => true,
    ];
}
