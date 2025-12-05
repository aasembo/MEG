<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ReportSlide Entity
 *
 * @property int $id
 * @property int $report_id
 * @property int|null $user_id
 * @property string $s3_key
 * @property string $file_path
 * @property string|null $original_filename
 * @property int|null $file_size
 * @property string|null $mime_type
 * @property int $slide_order
 * @property string|null $title
 * @property string|null $description
 * @property string|null $html_content
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Report $report
 * @property \App\Model\Entity\User $user
 */
class ReportSlide extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'report_id' => true,
        'user_id' => true,
        's3_key' => true,
        'file_path' => true,
        'original_filename' => true,
        'file_size' => true,
        'mime_type' => true,
        'slide_order' => true,
        'title' => true,
        'description' => true,
        'html_content' => true,
        'cases_exams_procedure_id' => true,
        'document_id' => true,
        'created' => true,
        'modified' => true,
        'report' => true,
        'user' => true,
    ];
}
