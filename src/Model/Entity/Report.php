<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Report Entity
 *
 * @property int $id
 * @property int $case_id
 * @property int $hospital_id
 * @property array|null $report_data
 * @property array|null $ai_insights
 * @property string|null $confidence_score
 * @property string|null $technician_notes
 * @property array|null $scientist_review
 * @property array|null $doctor_approval
 * @property string|null $status
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\MedicalCase $case
 * @property \App\Model\Entity\Hospital $hospital
 */
class Report extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'case_id' => true,
        'hospital_id' => true,
        'report_data' => true,
        'ai_insights' => true,
        'confidence_score' => true,
        'technician_notes' => true,
        'scientist_review' => true,
        'doctor_approval' => true,
        'status' => true,
        'created' => true,
        'modified' => true,
        'case' => true,
        'hospital' => true,
    ];
}
