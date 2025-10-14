<?php
declare(strict_types=1);

namespace App\Controller;

use App\Constants\SiteConstants;

/**
 * Debug controller to help troubleshoot redirection issues
 */
class DebugController extends AppController
{
    /**
     * Initialize method - skip hospital context setting for debugging
     */
    public function initialize(): void
    {
        parent::initialize();
        
        // Use a simple layout for debugging
        $this->viewBuilder()->setLayout('ajax');
    }
    
    /**
     * Test method to show current hospital context and redirection logic
     */
    public function test(): void
    {
                // For development: Allow hospital switching via query parameter
        $queryHospital = $this->request->getQuery('hospital');
        $mainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
        if ($queryHospital && ($host === 'localhost' || strpos($host, 'localhost:') === 0 || $host === $mainDomain || strpos($host, $mainDomain) !== false)) {
            $subdomain = $queryHospital;
        }
        
        if (empty($subdomain)) {
            $subdomain = 'hospital1';
        }
        
        // Get hospital info
        $hospitalsTable = $this->fetchTable('Hospitals');
        $hospital = $hospitalsTable->find()
            ->where(['subdomain' => $subdomain])
            ->first();
        
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        $debugInfo = [
            'request_host' => $host,
            'extracted_subdomain' => $originalSubdomain,
            'query_hospital' => $queryHospital,
            'final_subdomain' => $subdomain,
            'hospital_found' => $hospital ? $hospital->toArray() : null,
            'hospital_status' => $hospital ? $hospital->status : 'not found',
            'session_hospital' => $currentHospital ? $currentHospital->toArray() : null,
            'should_redirect' => $hospital && $hospital->status !== SiteConstants::HOSPITAL_STATUS_ACTIVE,
            'redirect_url' => $hospital && $hospital->status !== SiteConstants::HOSPITAL_STATUS_ACTIVE ? $this->buildMainDomainUrl($host) : 'no redirect needed',
            'actual_redirect_happening' => false // We'll check this
        ];
        
        // Check if normal AppController would redirect
        if ($hospital && $hospital->status !== SiteConstants::HOSPITAL_STATUS_ACTIVE) {
            $debugInfo['actual_redirect_happening'] = true;
            $debugInfo['redirect_reason'] = 'Hospital is inactive';
        }
        
        $this->set('debugInfo', $debugInfo);
    }
    
    /**
     * Force redirect test
     */
    public function forceRedirect(): void
    {
        $host = $this->request->host();
        $redirectUrl = $this->buildMainDomainUrl($host);
        
        $this->Flash->error("Force redirect test to: {$redirectUrl}");
        $this->redirect($redirectUrl);
    }
    
    /**
     * Raw test that bypasses all hospital logic
     */
    public function raw(): void
    {
        $host = $this->request->host();
        $queryHospital = $this->request->getQuery('hospital');
        
        // Simple test
        $this->set('host', $host);
        $this->set('queryHospital', $queryHospital);
        $this->set('currentUrl', $this->request->getUri());
        
        $this->viewBuilder()->setLayout('ajax');
        $this->render('raw');
    }
}