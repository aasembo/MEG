<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * RolesFixture
 */
class RolesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'name' => 'Lorem ipsum dolor sit amet',
                'type' => 'Lorem ipsum dolor sit amet',
                'status' => 'Lorem ip',
                'created' => '2025-09-30 12:07:30',
                'modified' => '2025-09-30 12:07:30',
            ],
        ];
        parent::init();
    }
}
