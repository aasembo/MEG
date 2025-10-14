<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * TestLoginAuth command.
 */
class TestLoginAuthCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/5/en/console/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

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
        $usersTable = $this->fetchTable('Users');
        
        $io->out('Testing login authentication for superadmin@example.com...');
        
        // First let's see what the auth finder query looks like
        $authQuery = $usersTable->find('auth')
            ->where(['Users.email' => 'superadmin@example.com']);
            
        $io->out('Auth finder SQL: ' . $authQuery->sql());
        
        // Test with auth finder
        $user = $authQuery->first();
            
        if ($user) {
            $io->success('✓ User found via auth finder!');
            $io->out("  - ID: {$user->id}");
            $io->out("  - Email: {$user->email}");
            $io->out("  - Status: {$user->status}");
            $io->out("  - Role: " . ($user->role ? $user->role->type : 'NO ROLE'));
            $io->out("  - Password hash starts with: " . substr($user->password, 0, 10) . "...");
            
            // Test password verification
            $io->out('');
            $io->out('Testing password verification:');
            $testPasswords = ['admin123', 'password', 'admin', 'superadmin', 'test123'];
            
            foreach ($testPasswords as $testPassword) {
                if (password_verify($testPassword, $user->password)) {
                    $io->success("✓ Password '{$testPassword}' is CORRECT!");
                    break;
                } else {
                    $io->warning("✗ Password '{$testPassword}' is incorrect");
                }
            }
        } else {
            $io->error('✗ User NOT found via auth finder!');
            
            // Try without auth finder
            $userDirect = $usersTable->find()
                ->contain(['Roles'])
                ->where(['Users.email' => 'superadmin@example.com'])
                ->first();
                
            if ($userDirect) {
                $io->out('  - But user exists in database directly');
                $io->out("  - Status: {$userDirect->status}");
                $io->out("  - Role ID: {$userDirect->role_id}");
                $io->out("  - Role Type: " . ($userDirect->role ? $userDirect->role->type : 'NO ROLE'));
                
                // Let's test the auth finder conditions manually
                $io->out('');
                $io->out('Testing auth finder conditions:');
                
                // Check status condition
                $statusMatch = $userDirect->status === 'active';
                $io->out("  - Status matches 'active': " . ($statusMatch ? 'YES' : 'NO'));
                
                // Check role type condition
                $roleTypes = ['administrator', 'super', 'doctor', 'scientist', 'technician', 'nurse'];
                $roleMatch = $userDirect->role && in_array($userDirect->role->type, $roleTypes);
                $io->out("  - Role type in allowed list: " . ($roleMatch ? 'YES' : 'NO'));
                $io->out("  - Allowed role types: " . implode(', ', $roleTypes));
            } else {
                $io->out('  - User doesn\'t exist in database at all!');
            }
        }
    }
}