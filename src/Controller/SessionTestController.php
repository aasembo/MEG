<?php
declare(strict_types=1);

namespace App\Controller;

use App\Constants\SiteConstants;

/**
 * SessionTest Controller - Temporary for debugging session issues
 */
class SessionTestController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        // Allow all actions without authentication for testing
        if ($this->components()->has('Authentication')) {
            $this->Authentication->allowUnauthenticated(['test', 'setData', 'getData']);
        }
    }
    
    public function test()
    {
        $this->viewBuilder()->setLayout(false);
        $this->autoRender = false;
        
        echo "<h1>Session Test</h1>";
        echo "<h2>Raw Session Data:</h2>";
        echo "<pre>" . print_r($_SESSION ?? [], true) . "</pre>";
        
        echo "<h2>CakePHP Session Data:</h2>";
        echo "<pre>" . print_r($this->request->getSession()->read(), true) . "</pre>";
        
        echo "<h2>Session ID:</h2>";
        echo "<p>" . session_id() . "</p>";
        
        echo "<h2>Cookies:</h2>";
        echo "<pre>" . print_r($_COOKIE ?? [], true) . "</pre>";
        
        echo "<h2>Session Configuration:</h2>";
        echo "<pre>" . print_r(\Cake\Core\Configure::read('Session'), true) . "</pre>";
        
        echo "<h2>Test Links:</h2>";
        echo "<p><a href='/session-test/setData'>Set Test Data</a></p>";
        echo "<p><a href='/session-test/getData'>Get Test Data</a></p>";
    }
    
    public function setData()
    {
        $this->viewBuilder()->setLayout(false);
        $this->autoRender = false;
        
        $this->request->getSession()->write('test_key', 'test_value_' . time());
        $this->request->getSession()->write('Auth.User', [
            'id' => 999,
            'email' => 'test@example.com',
            'role' => ['type' => SiteConstants::ROLE_TYPE_DOCTOR]
        ]);
        
        echo "<h1>Session Data Set</h1>";
        echo "<p>Test data has been written to session.</p>";
        echo "<p><a href='/session-test/test'>View Session Data</a></p>";
    }
    
    public function getData()
    {
        $this->viewBuilder()->setLayout(false);
        $this->autoRender = false;
        
        $testKey = $this->request->getSession()->read('test_key');
        $authData = $this->request->getSession()->read('Auth.User');
        
        echo "<h1>Session Data Retrieved</h1>";
        echo "<h2>Test Key:</h2>";
        echo "<p>" . ($testKey ?: 'NOT FOUND') . "</p>";
        
        echo "<h2>Auth Data:</h2>";
        echo "<pre>" . print_r($authData ?: 'NOT FOUND', true) . "</pre>";
        
        echo "<p><a href='/session-test/test'>View Full Session Data</a></p>";
    }
}