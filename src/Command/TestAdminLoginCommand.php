<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Test admin login validation command
 */
class TestAdminLoginCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->setDescription('Test admin login validation scenarios.');
        $parser->addOption('email', [
            'help' => 'Email address to test',
            'required' => true,
        ]);
        $parser->addOption('hospital-id', [
            'help' => 'Hospital ID context (1 for hospital1, 2 for hospital2)',
            'required' => true,
        ]);

        return $parser;
    }

    /**
     * Test admin login validation
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $email = $args->getOption('email');
        $hospitalId = (int)$args->getOption('hospital-id');
        
        $io->out("Testing admin login validation for: {$email} against hospital ID: {$hospitalId}");
        $io->hr();
        
        // Get hospital data
        $hospitalsTable = $this->fetchTable('Hospitals');
        $hospital = $hospitalsTable->get($hospitalId);
        $io->out("Hospital Context: {$hospital->name} (ID: {$hospital->id}, Subdomain: {$hospital->subdomain})");
        
        // Get user data
        $usersTable = $this->fetchTable('Users');
        $user = $usersTable->find()
            ->contain(['Roles'])
            ->where(['Users.email' => $email])
            ->first();
            
        if (!$user) {
            $io->error("❌ User not found: {$email}");
            return static::CODE_ERROR;
        }
        
        $io->out("User Found: {$user->email} (ID: {$user->id}, Hospital ID: {$user->hospital_id}, Role: {$user->role->type})");
        
        // Validate admin access
        $validations = [];
        
        // Check if user is admin
        if ($user->role->type === 'administrator') {
            $validations[] = "✅ User has administrator role";
        } else {
            $validations[] = "❌ User does not have administrator role (has: {$user->role->type})";
        }
        
        // Check if user belongs to hospital
        if ($user->hospital_id == $hospitalId) {
            $validations[] = "✅ User belongs to the correct hospital";
        } else {
            $validations[] = "❌ User belongs to different hospital (User: {$user->hospital_id}, Required: {$hospitalId})";
        }
        
        // Check user status
        if ($user->status === SiteConstants::USER_STATUS_ACTIVE) {
            $validations[] = "✅ User account is active";
        } else {
            $validations[] = "❌ User account is not active (Status: {$user->status})";
        }
        
        $io->out("Validation Results:");
        foreach ($validations as $validation) {
            $io->out("  {$validation}");
        }
        
        $io->hr();
        
        // Final determination
        $canAccess = ($user->role->type === 'administrator' && 
                     $user->hospital_id == $hospitalId && 
                     $user->status === 'active');
                     
        if ($canAccess) {
            $io->success("✅ LOGIN ALLOWED: User can access admin panel for {$hospital->name}");
        } else {
            $io->error("❌ LOGIN DENIED: User cannot access admin panel for {$hospital->name}");
        }
        
        return static::CODE_SUCCESS;
    }
}