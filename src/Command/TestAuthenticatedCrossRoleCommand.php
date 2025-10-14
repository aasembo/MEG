<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Http\ServerRequest;
use Cake\Http\Session;
use Cake\ORM\TableRegistry;
use App\Controller\Admin\LoginController;

/**
 * Test authenticated cross-role access command
 */
class TestAuthenticatedCrossRoleCommand extends Command
{
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->setDescription('Test cross-role access protection with authenticated user simulation');
        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $io->out('Testing cross-role access protection with authenticated user...');
        
        // Get our test doctor user
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $user = $usersTable->find()
            ->contain(['Roles'])
            ->where(['email' => 'test.doctor@hospital1.com'])
            ->first();
            
        if (!$user) {
            $io->error('Test doctor user not found. Please create one first.');
            return static::CODE_ERROR;
        }
        
        $io->out("Found test user: {$user->email} with role: {$user->role->type}");
        
        // Create a simulated authenticated session
        $request = new ServerRequest();
        $session = new Session();
        
        // Simulate authentication session data
        $session->write('Auth.User', [
            'id' => $user->id,
            'email' => $user->email,
            'role_id' => $user->role_id,
            'hospital_id' => $user->hospital_id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => ['type' => $user->role->type]
        ]);
        
        $request = $request->withAttribute('session', $session);
        
        $io->out('Simulated session created with doctor authentication.');
        
        // Now test what would happen when this authenticated doctor tries to access admin login
        try {
            // Create the admin login controller
            $controller = new LoginController($request);
            
            // Check authentication status
            $authResult = $controller->getRequest()->getAttribute('authentication');
            if ($authResult) {
                $io->out('Authentication component result: ' . ($authResult->isValid() ? 'valid' : 'invalid'));
                if ($authResult->isValid()) {
                    $identity = $authResult->getData();
                    $io->out('User identity found: ' . json_encode($identity));
                    $io->out('User role type: ' . ($identity['role']['type'] ?? 'unknown'));
                } else {
                    $io->out('No valid identity found in authentication result');
                }
            } else {
                $io->out('No authentication component found');
            }
            
            // Check session data
            $sessionUser = $session->read('Auth.User');
            if ($sessionUser) {
                $io->out('Session user data: ' . json_encode($sessionUser));
                $io->out('Session user role: ' . ($sessionUser['role']['type'] ?? 'unknown'));
            } else {
                $io->out('No user data in session');
            }
            
            $io->success('Cross-role access test completed. Check the output above for authentication details.');
            
        } catch (\Exception $e) {
            $io->error('Error during cross-role test: ' . $e->getMessage());
            $io->out('Stack trace: ' . $e->getTraceAsString());
            return static::CODE_ERROR;
        }
        
        return static::CODE_SUCCESS;
    }
}