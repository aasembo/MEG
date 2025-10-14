<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

/**
 * Test session behavior across different controller prefixes
 */
class TestSessionBehaviorCommand extends Command
{
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $io->out('Testing session behavior across controller prefixes...');
        
        // Check session configuration
        $io->out('=== Session Configuration ===');
        $sessionConfig = \Cake\Core\Configure::read('Session');
        $io->out("Session defaults: " . ($sessionConfig['defaults'] ?? 'not set'));
        
        // Check PHP session configuration
        $io->out('=== PHP Session Settings ===');
        $io->out("session.cookie_path: " . ini_get('session.cookie_path'));
        $io->out("session.cookie_domain: " . ini_get('session.cookie_domain'));
        $io->out("session.cookie_lifetime: " . ini_get('session.cookie_lifetime'));
        $io->out("session.gc_maxlifetime: " . ini_get('session.gc_maxlifetime'));
        $io->out("session.save_handler: " . ini_get('session.save_handler'));
        $io->out("session.save_path: " . ini_get('session.save_path'));
        
        // Check if session files exist
        $savePath = ini_get('session.save_path');
        if ($savePath && is_dir($savePath)) {
            $sessionFiles = glob($savePath . '/sess_*');
            $io->out("Active session files in {$savePath}: " . count($sessionFiles));
        } else {
            $io->out("Session save path not accessible or using different handler");
        }
        
        $io->out('=== Diagnosis ===');
        $io->out("The issue you're experiencing suggests:");
        $io->out("1. Session cookies may be scoped to specific paths (/doctor vs /admin)");
        $io->out("2. Different prefixes might be creating separate session contexts");
        $io->out("3. Session timeout might be very short");
        
        $io->out('=== Recommended Solutions ===');
        $io->out("1. Set explicit session cookie path to '/' in session config");
        $io->out("2. Ensure session cookie domain is consistent");
        $io->out("3. Increase session lifetime if needed");
        $io->out("4. Use database sessions for better persistence");
        
        return static::CODE_SUCCESS;
    }
}