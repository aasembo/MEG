<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'role_id' => 1,
                'hospital_id' => 1,
                'username' => 'Lorem ipsum dolor sit amet',
                'first_name' => 'Lorem ipsum dolor sit amet',
                'last_name' => 'Lorem ipsum dolor sit amet',
                'email' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'okta_id' => 'Lorem ipsum dolor sit amet',
                'status' => 'Lorem ip',
                'created' => '2025-09-30 12:01:00',
                'modified' => '2025-09-30 12:01:00',
            ],
        ];
        parent::init();
    }
}
