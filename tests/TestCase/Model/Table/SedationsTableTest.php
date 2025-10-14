<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SedationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SedationsTable Test Case
 */
class SedationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SedationsTable
     */
    protected $Sedations;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Sedations',
        'app.Hospitals',
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
        $config = $this->getTableLocator()->exists('Sedations') ? [] : ['className' => SedationsTable::class];
        $this->Sedations = $this->getTableLocator()->get('Sedations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Sedations);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\SedationsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\SedationsTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
