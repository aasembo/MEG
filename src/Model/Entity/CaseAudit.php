<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class CaseAudit extends Entity {
    protected array $_accessible = [
        'case_id' => true,
        'case_version_id' => true,
        'field_name' => true,
        'old_value' => true,
        'new_value' => true,
        'changed_by' => true,
        'timestamp' => true,
        'created' => true,
        'case' => true,
        'case_version' => true,
        'changed_by_user' => true,
    ];

    public function getChangeDescription(): string {
        $fieldLabels = [
            'status' => 'Status',
            'priority' => 'Priority',
            'patient_id' => 'Patient',
            'date' => 'Date',
            'assigned_to' => 'Assignment'
        ];

        $fieldLabel = $fieldLabels[$this->field_name] ?? ucfirst(str_replace('_', ' ', $this->field_name));
        
        return sprintf('%s changed from "%s" to "%s"', $fieldLabel, $this->old_value, $this->new_value);
    }
}