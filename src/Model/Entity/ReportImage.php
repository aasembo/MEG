<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ReportImage Entity
 *
 * @property int $id
 * @property int $report_id
 * @property int|null $user_id
 * @property string $s3_key
 * @property string $file_path
 * @property string|null $original_filename
 * @property int|null $file_size
 * @property string|null $mime_type
 * @property string|null $description
 * @property string|null $text_above
 * @property int $slide_order
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Report $report
 * @property \App\Model\Entity\User $user
 */
class ReportImage extends Entity
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
        'description' => true,
        'text_above' => true,
        'slide_order' => true,
        'created' => true,
        'modified' => true,
        'report' => true,
        'user' => true,
    ];
}
