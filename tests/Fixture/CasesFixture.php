<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CasesFixture
 */
class CasesFixture extends TestFixture
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
                'user_id' => 1,
                'hospital_id' => 1,
                'patient_id' => 1,
                'department_id' => 1,
                'sedation_id' => 1,
                'current_user_id' => 1,
                'current_version_id' => 1,
                'date' => '2025-10-07',
                'status' => 'Lorem ipsum dolor ',
                'priority' => 'Lorem ip',
                'notes' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'created' => '2025-10-07 04:14:24',
                'modified' => '2025-10-07 04:14:24',
            ],
        ];
        parent::init();
    }
}
