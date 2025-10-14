<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Authentication\PasswordHasher\DefaultPasswordHasher;
use Cake\ORM\Entity;

class User extends Entity {
    protected array $_accessible = [
        'role_id' => true,
        'hospital_id' => true,
        'username' => true,
        'first_name' => true,
        'last_name' => true,
        'email' => true,
        'password' => true,
        'okta_id' => true,
        'status' => true,
        'type' => true,
        'created' => true,
        'modified' => true,
        'role' => true,
        'hospital' => true,
        'cases' => true,
        'doctors' => true,
        'nurses' => true,
        'patients' => true,
        'scientists' => true,
        'technicians' => true,
        'user_logs' => true,
    ];

    protected array $_hidden = [
        'password',
    ];

    protected function _setPassword(?string $password): ?string {
        if ($password === null || strlen($password) === 0) {
            return null;
        }
        return (new DefaultPasswordHasher())->hash($password);
    }

    public function isSuper(): bool {
        return $this->role && $this->role->type === 'super';
    }

    public function isActive(): bool {
        return $this->status === 'active';
    }

    public function getFullName(): string {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
