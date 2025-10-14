<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Test controller to verify redirection
 */
class TestController extends AppController
{
    /**
     * Simple test page that should redirect if hospital is inactive
     */
    public function check(): void
    {
        $hospital = $this->request->getSession()->read('Hospital.current');
        $queryHospital = $this->request->getQuery('hospital');
        
        $this->set('hospital', $hospital);
        $this->set('queryHospital', $queryHospital);
        $this->set('message', 'If you see this page, the redirect did NOT happen');
        
        $this->viewBuilder()->setLayout('ajax');
    }
}