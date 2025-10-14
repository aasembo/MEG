<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class CaseVersion extends Entity {
    protected array $_accessible = [
        'case_id' => true,
        'version_number' => true,
        'user_id' => true,
        'timestamp' => true,
        'created' => true,
        'modified' => true,
        'case' => true,
        'user' => true,
        'case_assignments' => true,
        'case_audits' => true,
    ];
}