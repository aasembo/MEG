<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\CasesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\CasesTable Test Case
 */
class CasesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\CasesTable
     */
    protected $Cases;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Cases',
        'app.Users',
        'app.Hospitals',
        'app.PatientUsers',
        'app.CurrentUsers',
        'app.CurrentVersions',
        'app.Departments',
        'app.Sedations',
        'app.CaseVersions',
        'app.CaseAssignments',
        'app.CaseAudits',
        'app.CasesExamsProcedures',
        'app.Documents',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Cases') ? [] : ['className' => CasesTable::class];
        $this->Cases = $this->getTableLocator()->get('Cases', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Cases);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\CasesTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\CasesTable::buildRules()
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findByHospital method
     *
     * @return void
     * @link \App\Model\Table\CasesTable::findByHospital()
     */
    public function testFindByHospital(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findAssignedTo method
     *
     * @return void
     * @link \App\Model\Table\CasesTable::findAssignedTo()
     */
    public function testFindAssignedTo(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test findByTechnician method
     *
     * @return void
     * @link \App\Model\Table\CasesTable::findByTechnician()
     */
    public function testFindByTechnician(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
