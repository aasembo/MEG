<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * SlideLayoutTemplate Entity
 * A doctor's saved custom slide layout (grid structure, headers and text; no images)
 *
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $preset
 * @property string|null $title
 * @property string|null $subtitle
 * @property string|null $layout_data
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\User $user
 */
class SlideLayoutTemplate extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'user_id' => true,
        'name' => true,
        'preset' => true,
        'title' => true,
        'subtitle' => true,
        'layout_data' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
    ];

    /**
     * Get decoded grid layout data
     *
     * @return array
     */
    public function getLayoutData(): array
    {
        return \App\Service\SlideGridLayoutService::decode($this->layout_data);
    }
}
