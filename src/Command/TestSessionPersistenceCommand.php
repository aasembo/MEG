<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\ORM\TableRegistry;

/**
 * Test session persistence across routes
 */
class TestSessionPersistenceCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $io->out('=== Session Persistence Test ===');
        
        // 1. Check if there are any active sessions
        $io->out('1. Checking for active sessions...');
        
        $sessionPath = ROOT . DS . 'tmp' . DS . 'sessions';
        if (is_dir($sessionPath)) {
            $files = glob($sessionPath . DS . '*');
            $io->out("Found " . count($files) . " session files in {$sessionPath}");
            
            // Check recent files
            foreach ($files as $file) {
                $mtime = filemtime($file);
                $age = time() - $mtime;
                if ($age < 3600) { // Less than 1 hour old
                    $io->out("  Active session: " . basename($file) . " (age: {$age}s)");
                    
                    // Try to read session data
                    $content = file_get_contents($file);
                    if ($content) {
                        $io->out("  Content preview: " . substr($content, 0, 100) . "...");
                    }
                }
            }
        }
        
        // 2. Check recent authentication logs
        $io->out('2. Recent authentication activity...');
        $debugLog = ROOT . DS . 'logs' . DS . 'debug.log';
        if (file_exists($debugLog)) {
            $cmd = "tail -n 100 {$debugLog} | grep -E '(authenticated|login|logout)' | tail -n 5";
            $output = shell_exec($cmd);
            if ($output) {
                $lines = explode("\n", trim($output));
                foreach ($lines as $line) {
                    $io->out("  " . $line);
                }
            }
        }
        
        // 3. Check session configuration effective values
        $io->out('3. Effective session configuration...');
        $io->out("  Cookie name: " . session_name());
        $io->out("  Cookie path: " . ini_get('session.cookie_path'));
        $io->out("  Cookie domain: " . ini_get('session.cookie_domain'));
        $io->out("  Save handler: " . ini_get('session.save_handler'));
        $io->out("  Save path: " . ini_get('session.save_path'));
        
        // 4. Diagnosis
        $io->out('4. Root Cause Analysis...');
        $io->out("The issue appears to be related to:");
        $io->out("  - Session not persisting between different controller prefixes");
        $io->out("  - Possible session ID regeneration or loss");
        $io->out("  - Authentication middleware not finding session data");
        
        $io->out('5. Next Steps...');
        $io->out("  1. Test with a manual session cookie debug");
        $io->out("  2. Use database sessions for better reliability");
        $io->out("  3. Add session ID logging to track session continuity");
        
        return static::CODE_SUCCESS;
    }
}