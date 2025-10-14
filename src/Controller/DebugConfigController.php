<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Debug Controller for checking configuration
 */
class DebugConfigController extends AppController
{
    /**
     * Before filter callback
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Allow unauthenticated access
        if (method_exists($this, 'Authentication')) {
            $this->Authentication->addUnauthenticatedActions(['index']);
        }
    }
    
    /**
     * Debug configuration values
     */
    public function index()
    {
        $this->autoRender = false;
        
        echo "<h1>Configuration Debug</h1>";
        echo "<h2>Environment Variables</h2>";
        echo "OKTA_BASE_URL: " . ($_ENV['OKTA_BASE_URL'] ?? 'NOT SET') . "<br>";
        echo "OKTA_CLIENT_ID: " . ($_ENV['OKTA_CLIENT_ID'] ?? 'NOT SET') . "<br>";
        
        echo "<h2>CakePHP Configuration</h2>";
        echo "Okta.baseUrl: " . \Cake\Core\Configure::read('Okta.baseUrl') . "<br>";
        echo "Okta.clientId: " . \Cake\Core\Configure::read('Okta.clientId') . "<br>";
        
        echo "<h2>ENV Function Test</h2>";
        echo "env('OKTA_BASE_URL'): " . env('OKTA_BASE_URL', 'NOT SET') . "<br>";
        echo "env('OKTA_CLIENT_ID'): " . env('OKTA_CLIENT_ID', 'NOT SET') . "<br>";
        
        echo "<h2>File Exists Check</h2>";
        echo "config/.env exists: " . (file_exists(CONFIG . '.env') ? 'YES' : 'NO') . "<br>";
        
        exit;
    }
}