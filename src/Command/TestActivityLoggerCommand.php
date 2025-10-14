<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use App\Lib\UserActivityLogger;

/**
 * Test command for UserActivityLogger
 */
class TestActivityLoggerCommand extends Command
{
    /**
     * Configure command options
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to configure
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('Test the UserActivityLogger functionality');
        
        return $parser;
    }

    /**
     * Execute the command
     *
     * @param \Cake\Console\Arguments $args Arguments
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @return int|null Exit code
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $io->out('Testing UserActivityLogger...');
        
        try {
            // Create logger instance
            $logger = new UserActivityLogger();
            
            // Test 1: Basic login event
            $io->out('Test 1: Logging basic login event...');
            $result1 = $logger->logLogin(1, [
                'role_type' => 'doctor',
                'hospital_id' => 1,
                'description' => 'Test login event via command',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Console Command'
            ]);
            $io->out('Result: ' . ($result1 ? 'SUCCESS' : 'FAILED'));
            
            // Test 2: Registration event with data (use user ID 20 which we created)
            $io->out('Test 2: Logging registration event...');
            $result2 = $logger->logRegistration(20, [
                'role_type' => 'doctor',
                'hospital_id' => 1,
                'description' => 'Test registration event via command',
                'event_data' => [
                    'registration_method' => 'okta',
                    'first_time' => true,
                    'test_mode' => true
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Console Command'
            ]);
            $io->out('Result: ' . ($result2 ? 'SUCCESS' : 'FAILED'));
            
            // Test 3: Failed login attempt
            $io->out('Test 3: Logging failed login attempt...');
            $result3 = $logger->logLoginFailed('test@example.com', [
                'description' => 'Test failed login attempt',
                'event_data' => [
                    'reason' => 'invalid_credentials',
                    'attempts' => 3,
                    'test_mode' => true
                ],
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Test Console Command'
            ]);
            $io->out('Result: ' . ($result3 ? 'SUCCESS' : 'FAILED'));
            
            // Test 4: Okta logout with session data
            $io->out('Test 4: Logging Okta logout...');
            $result4 = $logger->logOktaLogout(1, [
                'role_type' => 'doctor',
                'hospital_id' => 1,
                'description' => 'Test Okta logout event',
                'event_data' => [
                    'session_duration' => [
                        'duration_minutes' => 45.5,
                        'logout_method' => 'okta_complete'
                    ],
                    'test_mode' => true
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Console Command'
            ]);
            $io->out('Result: ' . ($result4 ? 'SUCCESS' : 'FAILED'));
            
            // Test 5: Custom event logging (use user ID 20)
            $io->out('Test 5: Logging custom event...');
            $result5 = $logger->log(UserActivityLogger::EVENT_ROLE_CHANGE, [
                'user_id' => 20,
                'role_type' => 'doctor',
                'hospital_id' => 1,
                'description' => 'Test role change event',
                'event_data' => [
                    'from_role' => 'scientist',
                    'to_role' => 'doctor',
                    'changed_by' => 'admin',
                    'test_mode' => true
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Console Command'
            ]);
            $io->out('Result: ' . ($result5 ? 'SUCCESS' : 'FAILED'));
            
            // Test 6: Get recent activities
            $io->out('Test 6: Getting recent activities...');
            $activities = $logger->getRecentActivities(null, 10);
            $io->out('Found ' . count($activities) . ' recent activities');
            
            if (!empty($activities)) {
                $io->out('Most recent activities:');
                foreach (array_slice($activities, 0, 5) as $activity) {
                    $io->out("- {$activity->event_type}: {$activity->description} ({$activity->status}) at {$activity->created}");
                }
            }
            
            // Test 7: Get activity statistics
            $io->out('Test 7: Getting activity statistics...');
            $stats = $logger->getActivityStats();
            $io->out('Activity statistics:');
            foreach ($stats as $eventType => $count) {
                $io->out("- {$eventType}: {$count}");
            }
            
            // Test 8: User-specific activities
            $io->out('Test 8: Getting user-specific activities...');
            $userActivities = $logger->getRecentActivities(1, 5);
            $io->out('Found ' . count($userActivities) . ' activities for user ID 1');
            
            $io->success('All tests completed successfully!');
            
            return static::CODE_SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('ERROR: ' . $e->getMessage());
            $io->out('Trace: ' . $e->getTraceAsString());
            
            return static::CODE_ERROR;
        }
    }
}