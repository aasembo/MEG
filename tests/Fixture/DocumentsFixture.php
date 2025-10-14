<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DocumentsFixture
 */
class DocumentsFixture extends TestFixture
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
                'case_id' => 1,
                'user_id' => 1,
                'cases_exams_procedure_id' => 1,
                'document_type' => 'Lorem ipsum dolor sit amet',
                'file_path' => 'Lorem ipsum dolor sit amet',
                'file_type' => 'Lorem ipsum dolor sit amet',
                'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'uploaded_at' => '2025-10-06 12:40:53',
                'created' => '2025-10-06 12:40:53',
                'modified' => '2025-10-06 12:40:53',
            ],
        ];
        parent::init();
    }
}
