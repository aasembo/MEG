<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * Command to debug cross-role access issues
 */
class DebugCrossRoleAccessCommand extends Command
{
    /**
     * Configure command options
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('Debug cross-role access issues by examining session and authentication state');
        $parser->addOption('user-id', [
            'help' => 'User ID to simulate session for',
            'short' => 'u',
        ]);
        return $parser;
    }

    /**
     * Execute the command
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $io->out('Debugging cross-role access...');
        
        // Check if there are any active sessions
        $io->out('Checking for active sessions...');
        
        // Check session files in tmp/sessions (if using file-based sessions)
        $sessionPath = ROOT . DS . 'tmp' . DS . 'sessions';
        if (is_dir($sessionPath)) {
            $sessionFiles = glob($sessionPath . DS . '*');
            $io->out('Found ' . count($sessionFiles) . ' session files');
            
            if (!empty($sessionFiles)) {
                $io->out('Recent session files:');
                $recentFiles = array_slice($sessionFiles, -3);
                foreach ($recentFiles as $file) {
                    $io->out('  - ' . basename($file) . ' (modified: ' . date('Y-m-d H:i:s', filemtime($file)) . ')');
                }
            }
        }
        
        $userId = $args->getOption('user-id');
        if ($userId) {
            $io->out("Checking user {$userId} details...");
            
            $usersTable = $this->fetchTable('Users');
            $user = $usersTable->find()
                ->contain(['Roles'])
                ->where(['Users.id' => $userId])
                ->first();
            
            if ($user) {
                $io->out("User found:");
                $io->out("  - ID: {$user->id}");
                $io->out("  - Email: {$user->email}");
                $io->out("  - Role: " . ($user->role ? $user->role->name : 'No role'));
                $io->out("  - Role Type: " . ($user->role ? $user->role->type : 'No role type'));
                $io->out("  - Status: {$user->status}");
                
                // Check what dashboard this user should be redirected to
                $roleRoutes = [
                    'administrator' => '/admin/dashboard',
                    'doctor' => '/doctor/dashboard',
                    'technician' => '/technician/dashboard',
                    'scientist' => '/scientist/dashboard',
                    'super' => '/system/dashboard',
                ];
                
                $expectedDashboard = $roleRoutes[$user->role->type] ?? 'Unknown';
                $io->out("  - Expected Dashboard: {$expectedDashboard}");
                
            } else {
                $io->error("User {$userId} not found");
            }
        }
        
        $io->out('');
        $io->out('Authentication Configuration Check:');
        
        // Check if authentication middleware is properly configured
        $appFile = ROOT . DS . 'src' . DS . 'Application.php';
        $appContent = file_get_contents($appFile);
        
        if (strpos($appContent, 'AuthenticationMiddleware') !== false) {
            $io->success('✓ AuthenticationMiddleware is loaded');
        } else {
            $io->error('✗ AuthenticationMiddleware not found');
        }
        
        if (strpos($appContent, 'allowUnauthenticated') !== false) {
            $io->success('✓ allowUnauthenticated configuration found');
        } else {
            $io->warning('! allowUnauthenticated configuration not found');
        }
        
        $io->out('');
        $io->out('Login Controller Protection Check:');
        
        $controllers = [
            'Admin' => 'src/Controller/Admin/LoginController.php',
            'Doctor' => 'src/Controller/Doctor/LoginController.php',
            'Scientist' => 'src/Controller/Scientist/LoginController.php',
            'Technician' => 'src/Controller/Technician/LoginController.php',
        ];
        
        foreach ($controllers as $name => $file) {
            $content = file_get_contents(ROOT . DS . $file);
            
            if (strpos($content, 'getResult()') !== false && 
                strpos($content, 'isValid()') !== false &&
                strpos($content, 'redirectToUserDashboard') !== false) {
                $io->success("✓ {$name} controller has protection logic");
            } else {
                $io->error("✗ {$name} controller missing protection logic");
            }
        }
        
        $io->out('');
        $io->out('Recommendations:');
        $io->out('1. Try accessing /admin/login while logged in as a doctor');
        $io->out('2. Check the browser network tab for any redirects');
        $io->out('3. Look at logs/debug.log for authentication debug messages');
        $io->out('4. Clear browser cache and cookies to ensure fresh session');
        
        return static::CODE_SUCCESS;
    }
}