<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class CaseAssignment extends Entity {
    protected array $_accessible = [
        'case_id' => true,
        'case_version_id' => true,
        'user_id' => true,
        'assigned_to' => true,
        'timestamp' => true,
        'notes' => true,
        'created' => true,
        'modified' => true,
        'case' => true,
        'case_version' => true,
        'user' => true,
        'assigned_to_user' => true,
    ];
}