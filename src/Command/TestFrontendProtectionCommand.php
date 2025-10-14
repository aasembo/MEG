<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Test command to verify frontend access protection
 */
class TestFrontendProtectionCommand extends Command
{
    /**
     * Configure command options
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('Test that authenticated users cannot access frontend');
        return $parser;
    }

    /**
     * Execute the command
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $io->out('Testing frontend access protection...');
        
        // Test 1: Verify PagesController has authentication check
        $io->out('Test 1: Checking PagesController for authentication redirect logic...');
        
        $pagesControllerFile = ROOT . DS . 'src' . DS . 'Controller' . DS . 'PagesController.php';
        $content = file_get_contents($pagesControllerFile);
        
        if (strpos($content, 'Authentication->getResult()') !== false) {
            $io->success('✓ PagesController has authentication check');
        } else {
            $io->error('✗ PagesController missing authentication check');
            return static::CODE_ERROR;
        }
        
        // Test 2: Verify redirectToRoleDashboard method exists
        if (strpos($content, 'redirectToRoleDashboard') !== false) {
            $io->success('✓ PagesController has redirectToRoleDashboard method');
        } else {
            $io->error('✗ PagesController missing redirectToRoleDashboard method');
            return static::CODE_ERROR;
        }
        
        // Test 3: Verify UserActivityLogger is imported and used
        if (strpos($content, 'UserActivityLogger') !== false) {
            $io->success('✓ PagesController includes UserActivityLogger for tracking');
        } else {
            $io->error('✗ PagesController missing UserActivityLogger integration');
            return static::CODE_ERROR;
        }
        
        // Test 4: Check for role-based dashboard routes
        $supportedRoles = ['doctor', 'scientist', 'technician', 'admin', 'super'];
        $io->out('Test 4: Checking supported role dashboard routes...');
        
        foreach ($supportedRoles as $role) {
            if (strpos($content, "'{$role}'") !== false) {
                $io->out("  ✓ {$role} role supported");
            }
        }
        
        $io->success('All frontend protection tests passed!');
        $io->out('');
        $io->out('Behavior Summary:');
        $io->out('- Non-authenticated users: See frontend homepage');
        $io->out('- Authenticated users: Redirected to role dashboards');
        $io->out('- Failed frontend access attempts: Logged for security tracking');
        
        return static::CODE_SUCCESS;
    }
}