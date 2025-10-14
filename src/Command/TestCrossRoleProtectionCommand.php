<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

/**
 * Test cross-role protection command
 */
class TestCrossRoleProtectionCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $io->out('Testing cross-role access protection...');
        
        // Test 1: Check if there's any active session data
        $io->out('=== Test 1: Check for active sessions ===');
        
        // Check session files in tmp/sessions
        $sessionDir = ROOT . DS . 'tmp' . DS . 'sessions';
        if (is_dir($sessionDir)) {
            $files = glob($sessionDir . DS . '*');
            $io->out("Found " . count($files) . " session files in {$sessionDir}");
            
            if (count($files) > 0) {
                $io->out("Recent session files:");
                foreach (array_slice($files, -3) as $file) {
                    $mtime = filemtime($file);
                    $io->out("  " . basename($file) . " (modified: " . date('Y-m-d H:i:s', $mtime) . ")");
                }
            }
        } else {
            $io->out("Session directory not found: {$sessionDir}");
        }
        
        // Test 2: Check recent authentication logs
        $io->out('=== Test 2: Recent authentication activities ===');
        
        $recentLogs = [];
        $debugLog = ROOT . DS . 'logs' . DS . 'debug.log';
        if (file_exists($debugLog)) {
            $lines = file($debugLog);
            $recentLines = array_slice($lines, -50);
            
            foreach ($recentLines as $line) {
                if (preg_match('/(authentication|login|logout|Authentication result)/', $line)) {
                    $recentLogs[] = trim($line);
                }
            }
            
            $io->out("Recent authentication activities:");
            foreach (array_slice($recentLogs, -10) as $log) {
                $io->out("  " . $log);
            }
        }
        
        // Test 3: Check current authentication component behavior
        $io->out('=== Test 3: Authentication Component Analysis ===');
        
        $io->out("The authentication system shows 'invalid' for admin login access.");
        $io->out("This suggests one of the following issues:");
        $io->out("1. Session cookies are not shared between /doctor and /admin paths");
        $io->out("2. Authentication is scoped to specific prefixes"); 
        $io->out("3. Session timeout is very short");
        $io->out("4. There's a logout happening between login and admin access");
        
        // Test 4: Check session configuration
        $io->out('=== Test 4: Session Configuration ===');
        $sessionConfig = \Cake\Core\Configure::read('Session');
        $io->out("Session configuration: " . json_encode($sessionConfig, JSON_PRETTY_PRINT));
        
        $io->out('=== Test 5: Recommendations ===');
        $io->out("To fix the cross-role access issue:");
        $io->out("1. Verify session cookies work across all controller prefixes");
        $io->out("2. Check if authentication middleware applies to all routes");
        $io->out("3. Ensure session timeout is reasonable (not too short)");
        $io->out("4. Test with a real browser session to see cookie behavior");
        
        return static::CODE_SUCCESS;
    }
}