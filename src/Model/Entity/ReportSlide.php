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
 * @property string|null $slide_type
 * @property int $layout_columns
 * @property string|null $col1_type
 * @property string|null $col1_content
 * @property string|null $col1_image_path
 * @property string|null $col1_header
 * @property string|null $col2_type
 * @property string|null $col2_content
 * @property string|null $col2_image_path
 * @property string|null $col2_header
 * @property string|null $subtitle
 * @property string|null $footer_text
 * @property string|null $legend_data
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
        'slide_type' => true,
        'layout_columns' => true,
        'col1_type' => true,
        'col1_content' => true,
        'col1_image_path' => true,
        'col1_header' => true,
        'col2_type' => true,
        'col2_content' => true,
        'col2_image_path' => true,
        'col2_header' => true,
        'subtitle' => true,
        'footer_text' => true,
        'legend_data' => true,
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

    /**
     * Get the slide configuration from PPT_REPORT_PAGES
     *
     * @return array|null
     */
    public function getSlideConfig(): ?array
    {
        if (empty($this->slide_type)) {
            return null;
        }
        
        $pptPages = unserialize(PPT_REPORT_PAGES);
        return $pptPages[$this->slide_type] ?? null;
    }

    /**
     * Get decoded legend data
     *
     * @return array
     */
    public function getLegendItems(): array
    {
        if (empty($this->legend_data)) {
            return [];
        }
        
        $data = json_decode($this->legend_data, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Set legend data from array
     *
     * @param array $items Legend items
     * @return void
     */
    public function setLegendItems(array $items): void
    {
        $this->legend_data = json_encode($items);
    }
}
