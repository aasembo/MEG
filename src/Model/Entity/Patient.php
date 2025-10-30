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

    /**
     * Virtual field for first_name - retrieved from associated User
     */
    protected function _getFirstName(): ?string
    {
        return $this->user->first_name ?? null;
    }

    /**
     * Virtual field for last_name - retrieved from associated User
     */
    protected function _getLastName(): ?string
    {
        return $this->user->last_name ?? null;
    }

    /**
     * Virtual field for date_of_birth - alias for dob
     */
    protected function _getDateOfBirth()
    {
        return $this->dob ?? null;
    }

    /**
     * Virtual field for age - calculated from dob
     */
    protected function _getAge(): ?int
    {
        if (!$this->dob) {
            return null;
        }
        
        $dob = new \DateTime($this->dob->format('Y-m-d'));
        $now = new \DateTime();
        return $now->diff($dob)->y;
    }
}
