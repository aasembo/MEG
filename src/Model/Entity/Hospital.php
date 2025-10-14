<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class Hospital extends Entity {
    protected array $_accessible = [
        'name' => true,
        'subdomain' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
        'users' => true,
    ];

    public function isActive(): bool {
        return $this->status === 'active';
    }
}