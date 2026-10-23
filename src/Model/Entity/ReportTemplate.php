<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ReportTemplate Entity
 * A doctor's saved set of custom layout slides used to start a new MEG report
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $slides_data JSON list of {title, subtitle, layout_data}
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\User $user
 */
class ReportTemplate extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'name' => true,
        'slides_data' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];

    /**
     * Get the template's slides in order
     *
     * @return array<array{title: string|null, subtitle: string|null, layout: array}>
     */
    public function getSlides(): array
    {
        $slides = json_decode((string)$this->slides_data, true);

        return is_array($slides) ? $slides : [];
    }
}
