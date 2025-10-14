<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use App\Constants\SiteConstants;

/**
 * TestSpecializedRecords command.
 */
class TestSpecializedRecordsCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/5/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->setDescription('Test specialized records creation for doctors, nurses, scientists, patients, and technicians.');

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $io->out('Testing specialized records...');
        
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        $hospitalsTable = TableRegistry::getTableLocator()->get('Hospitals');
        
        // Get a hospital for testing
        $hospital = $hospitalsTable->find()->first();
        if (!$hospital) {
            $io->error('No hospital found. Please create a hospital first.');
            return static::CODE_ERROR;
        }
        
        // Get specialized roles
        $specializedRoles = [
            'doctor' => $rolesTable->find()->where(['type' => SiteConstants::ROLE_TYPE_DOCTOR])->first(),
            'nurse' => $rolesTable->find()->where(['type' => SiteConstants::ROLE_TYPE_NURSE])->first(),
            'scientist' => $rolesTable->find()->where(['type' => SiteConstants::ROLE_TYPE_SCIENTIST])->first(),
            'patient' => $rolesTable->find()->where(['type' => SiteConstants::ROLE_TYPE_PATIENT])->first(),
            'technician' => $rolesTable->find()->where(['type' => SiteConstants::ROLE_TYPE_TECHNICIAN])->first(),
        ];
        
        $io->out('Available specialized roles:');
        foreach ($specializedRoles as $type => $role) {
            if ($role) {
                $io->out("- {$type}: {$role->name} (ID: {$role->id})");
            } else {
                $io->warning("- {$type}: NOT FOUND");
            }
        }
        
        // Test counts in specialized tables
        $io->out('');
        $io->out('Current counts in specialized tables:');
        
        $tables = ['Doctors', 'Nurses', 'Scientists', 'Patients', 'Technicians'];
        foreach ($tables as $tableName) {
            $table = TableRegistry::getTableLocator()->get($tableName);
            $count = $table->find()->count();
            $io->out("- {$tableName}: {$count} records");
        }
        
        return static::CODE_SUCCESS;
    }
}