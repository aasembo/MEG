<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ExamsProceduresTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ExamsProceduresTable Test Case
 */
class ExamsProceduresTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ExamsProceduresTable
     */
    protected $ExamsProcedures;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.ExamsProcedures',
        'app.Exams',
        'app.Procedures',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('ExamsProcedures') ? [] : ['className' => ExamsProceduresTable::class];
        $this->ExamsProcedures = $this->getTableLocator()->get('ExamsProcedures', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->ExamsProcedures);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\ExamsProceduresTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\ExamsProceduresTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
