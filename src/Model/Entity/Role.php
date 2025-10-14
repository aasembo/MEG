<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Role extends Entity {
    protected array $_accessible = [
        'name' => true,
        'type' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
        'users' => true,
    ];
}
