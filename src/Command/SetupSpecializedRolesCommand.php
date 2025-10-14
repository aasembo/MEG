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
 * SetupSpecializedRoles command.
 */
class SetupSpecializedRolesCommand extends Command
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
        $parser->setDescription('Setup specialized roles for doctors, nurses, scientists, patients, and technicians.');

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
        $io->out('Setting up specialized roles...');
        
        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        
        $specializedRoles = [
            ['name' => 'Doctor', 'type' => SiteConstants::ROLE_TYPE_DOCTOR, 'status' => SiteConstants::USER_STATUS_ACTIVE],
            ['name' => 'Nurse', 'type' => SiteConstants::ROLE_TYPE_NURSE, 'status' => SiteConstants::USER_STATUS_ACTIVE],
            ['name' => 'Scientist', 'type' => SiteConstants::ROLE_TYPE_SCIENTIST, 'status' => SiteConstants::USER_STATUS_ACTIVE],
            ['name' => 'Patient', 'type' => SiteConstants::ROLE_TYPE_PATIENT, 'status' => SiteConstants::USER_STATUS_ACTIVE],
            ['name' => 'Technician', 'type' => SiteConstants::ROLE_TYPE_TECHNICIAN, 'status' => SiteConstants::USER_STATUS_ACTIVE],
        ];
        
        foreach ($specializedRoles as $roleData) {
            $existingRole = $rolesTable->find()
                ->where(['type' => $roleData['type']])
                ->first();
                
            if ($existingRole) {
                $io->out("Role '{$roleData['name']}' already exists.");
                continue;
            }
            
            $role = $rolesTable->newEmptyEntity();
            $role = $rolesTable->patchEntity($role, $roleData);
            
            if ($rolesTable->save($role)) {
                $io->success("Created role: {$roleData['name']} (type: {$roleData['type']})");
            } else {
                $io->error("Failed to create role: {$roleData['name']}");
                $io->out(print_r($role->getErrors(), true));
            }
        }
        
        $io->out('Specialized roles setup complete!');
        
        return static::CODE_SUCCESS;
    }
}