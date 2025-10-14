<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Modality extends Entity {
    protected array $_accessible = [
        'hospital_id' => true,
        'name' => true,
        'description' => true,
        'created' => true,
        'modified' => true,
        'hospital' => true,
        'exams' => true,
    ];
}