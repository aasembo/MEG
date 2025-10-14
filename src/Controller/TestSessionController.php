<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Http\Response;

class TestSessionController extends Controller
{
    public function initialize(): void
    {
        parent::initialize();
        // Disable CSRF for this test
        $this->getEventManager()->off($this->Csrf);
    }

    public function index(): Response
    {
        $session = $this->getRequest()->getSession();
        
        // Test setting a session value
        $session->write('test.timestamp', time());
        $session->write('test.value', 'Hello Session!');
        
        $output = [
            'session_id' => session_id(),
            'session_data' => $session->read(),
            'raw_session' => $_SESSION ?? null,
            'session_started' => session_status() === PHP_SESSION_ACTIVE,
            'test_value' => $session->read('test.value'),
            'timestamp' => $session->read('test.timestamp'),
        ];
        
        $this->viewBuilder()->setLayout(false);
        $this->set('output', $output);
        $this->render('/Test/session_test');
        
        return $this->getResponse();
    }
    
    public function check(): Response
    {
        $session = $this->getRequest()->getSession();
        
        $output = [
            'session_id' => session_id(),
            'session_data' => $session->read(),
            'raw_session' => $_SESSION ?? null,
            'session_started' => session_status() === PHP_SESSION_ACTIVE,
            'test_value' => $session->read('test.value'),
            'timestamp' => $session->read('test.timestamp'),
            'session_check' => 'Session persistence test',
        ];
        
        $this->viewBuilder()->setLayout(false);
        $this->set('output', $output);
        $this->render('/Test/session_test');
        
        return $this->getResponse();
    }
}