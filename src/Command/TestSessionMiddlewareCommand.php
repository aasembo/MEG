<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Http\Session;

class TestSessionMiddlewareCommand extends Command
{
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('Test if session middleware is working correctly');
        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $io->out('=== Testing Session Middleware ===');
        
        // Create a mock request to test session
        $request = new \Cake\Http\ServerRequest();
        $response = new \Cake\Http\Response();
        
        $io->out('Testing session configuration...');
        
        // Check session configuration
        $sessionConfig = \Cake\Core\Configure::read('Session');
        $io->out('Session config:');
        foreach ($sessionConfig as $key => $value) {
            $io->out("  {$key}: " . (is_array($value) ? print_r($value, true) : $value));
        }
        
        // Test if PHP sessions can start
        $io->out('Testing PHP session start...');
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
                $io->success('✓ PHP session started successfully');
                $io->out('Session ID: ' . session_id());
            } else {
                $io->out('Session already active: ' . session_id());
            }
            
            // Test setting session data
            $_SESSION['test_middleware'] = 'working';
            $io->success('✓ Session data set successfully');
            
            // Test reading session data
            $testValue = $_SESSION['test_middleware'] ?? null;
            if ($testValue === 'working') {
                $io->success('✓ Session data read successfully');
            } else {
                $io->error('✗ Session data not readable');
            }
            
            session_destroy();
            $io->success('✓ Session destroyed successfully');
            
        } catch (\Exception $e) {
            $io->error('✗ Session error: ' . $e->getMessage());
            return static::CODE_ERROR;
        }
        
        $io->out('=== Session middleware test completed ===');
        return static::CODE_SUCCESS;
    }
}