<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Test hospital routing command
 */
class TestHospitalRoutingCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/5/en/console-commands.html#defining-arguments-and-options
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->setDescription('Test hospital routing functionality.');

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out('Testing Hospital Routing...');
        
        // Get hospital data
        $hospitalsTable = $this->fetchTable('Hospitals');
        $hospitals = $hospitalsTable->find('all')->toArray();
        
        $io->out('Found ' . count($hospitals) . ' hospitals:');
        foreach ($hospitals as $hospital) {
            $io->out("  - ID: {$hospital->id}, Name: {$hospital->name}, Subdomain: {$hospital->subdomain}, Status: {$hospital->status}");
        }
        
        // Get admin users
        $usersTable = $this->fetchTable('Users');
        $adminUsers = $usersTable->find()
            ->contain(['Roles'])
            ->where(['Roles.type' => 'administrator'])
            ->toArray();
            
        $io->out('Found ' . count($adminUsers) . ' admin users:');
        foreach ($adminUsers as $user) {
            $io->out("  - ID: {$user->id}, Email: {$user->email}, Hospital ID: {$user->hospital_id}, Status: {$user->status}");
        }
        
        return static::CODE_SUCCESS;
    }
}