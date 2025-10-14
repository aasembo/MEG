<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Exam extends Entity {
    protected array $_accessible = [
        'name' => true,
        'hospital_id' => true,
        'modality_id' => true,
        'department_id' => true,
        'duration' => true,
        'cost' => true,
        'created' => true,
        'modified' => true,
        'hospital' => true,
        'modality' => true,
        'department' => true,
        'exams_procedures' => true,
    ];
}