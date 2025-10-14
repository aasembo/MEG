<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

/**
 * Real-time session diagnostic command
 */
class DiagnoseSessionCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $io->out('=== Session Diagnosis ===');
        
        // Check session files
        $sessionPath = ROOT . DS . 'tmp' . DS . 'sessions';
        $files = glob($sessionPath . DS . '*');
        
        $io->out('Found ' . count($files) . ' session files:');
        
        foreach ($files as $file) {
            $mtime = filemtime($file);
            $age = time() - $mtime;
            $io->out("  " . basename($file) . " (age: {$age}s)");
            
            if ($age < 300) { // Less than 5 minutes old
                $content = file_get_contents($file);
                $io->out("  Content: " . substr($content, 0, 200) . "...");
                
                // Try to decode session data
                if (strpos($content, 'Auth') !== false) {
                    $io->success("  ✓ Contains Auth data");
                }
                if (strpos($content, 'oauth_id_token') !== false) {
                    $io->success("  ✓ Contains Okta token");
                }
                if (strpos($content, 'Hospital') !== false) {
                    $io->success("  ✓ Contains Hospital data");
                }
            }
        }
        
        // Check recent authentication activity
        $io->out('=== Recent Authentication Activity ===');
        $debugLog = ROOT . DS . 'logs' . DS . 'debug.log';
        if (file_exists($debugLog)) {
            $cmd = "tail -n 50 {$debugLog} | grep -E '(authentication|login|logout|Auth data|Session)' | tail -n 10";
            $output = shell_exec($cmd);
            if ($output) {
                $lines = explode("\n", trim($output));
                foreach ($lines as $line) {
                    $io->out("  " . $line);
                }
            }
        }
        
        return static::CODE_SUCCESS;
    }
}