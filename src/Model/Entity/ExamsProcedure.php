<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class ExamsProcedure extends Entity {
    protected array $_accessible = [
        'exam_id' => true,
        'procedure_id' => true,
        'notes' => true,
        'preparation_instructions' => true,
        'post_procedure_care' => true,
        'contrast_required' => true,
        'sedation_required' => true,
        'created' => true,
        'modified' => true,
        'exam' => true,
        'procedure' => true,
    ];
}
