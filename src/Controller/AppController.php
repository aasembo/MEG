<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use App\Constants\SiteConstants;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
        
        // Set hospital context for all controllers except Auth and System controllers
        // System controllers manage multiple hospitals and don't need specific hospital context
        $prefix = $this->request->getParam('prefix');
        if ($this->getName() !== 'Auth' && $prefix !== 'System') {
            $this->setHospitalContext();
        }
        
        // Load authentication for role-based controllers, admin controllers, and auth controller
        // Only load for Pages controller if it's NOT the home action (to allow public access to homepage)
        if (in_array($prefix, ['Doctor', 'Scientist', 'Technician', 'Admin', 'System']) || 
            $this->getName() === 'Auth' ||
            ($this->getName() === 'Pages' && $this->request->getParam('action') !== 'home')) {
            $this->loadComponent('Authentication.Authentication');
        }

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    /**
     * Detect hospital from subdomain and set context
     *
     * @return void
     */
    protected function setHospitalContext(): void
    {
        // Check if hospital context is already set in session
        $hospital = $this->request->getSession()->read('Hospital.current');
        
        if (!$hospital) {
            // First, check if the middleware has set hospital context in request attributes
            $hospitalFromMiddleware = $this->request->getAttribute('hospital_context');
            
            if ($hospitalFromMiddleware) {
                // Use the hospital context from middleware
                $hospital = $hospitalFromMiddleware;
                $this->request->getSession()->write('Hospital.current', $hospital);
            } else {
                $host = $this->request->host();
                if ($host === null) {
                    // Handle cases where host is not available (e.g., CLI context)
                    $host = 'localhost';
                }
                $subdomain = $this->extractSubdomain($host);
                
                // For development: Allow hospital switching via query parameter
                $queryHospital = $this->request->getQuery('hospital');
                $mainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
                if ($queryHospital && ($host === 'localhost' || strpos($host, 'localhost:') === 0 || $host === $mainDomain || strpos($host, $mainDomain) !== false)) {
                    $subdomain = $queryHospital;
                }
                
                $hospitalsTable = $this->fetchTable('Hospitals');
                
                // If no subdomain detected, try to use a default hospital for development
                if (empty($subdomain)) {
                    // For development/localhost access, use the first active hospital as default
                    if ($host === 'localhost' || strpos($host, 'localhost:') === 0) {
                        $defaultHospital = $hospitalsTable->find()
                            ->where(['status' => SiteConstants::HOSPITAL_STATUS_ACTIVE])
                            ->orderBy(['id' => 'ASC'])
                            ->first();
                            
                        if ($defaultHospital) {
                            $hospital = $defaultHospital;
                            $this->request->getSession()->write('Hospital.current', $hospital);
                        }
                    }
                    // For main domain without subdomain, don't set hospital context
                    if (!$hospital) {
                        return;
                    }
                } else {
                    // Subdomain was specified - check if it exists (both active and inactive)
                    $hospitalCheck = $hospitalsTable->find()
                        ->where(['subdomain' => $subdomain])
                        ->first();
                    
                    // Debug logging for development
                    $mainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
                    if ($host === 'localhost' || strpos($host, 'localhost:') === 0 || $host === $mainDomain || strpos($host, $mainDomain) !== false) {
                        $this->log("Hospital check for subdomain '{$subdomain}': " . ($hospitalCheck ? "Found hospital '{$hospitalCheck->name}' with status '{$hospitalCheck->status}'" : "Not found"), 'debug');
                    }
                
                // If hospital doesn't exist at all, redirect
                if (!$hospitalCheck) {
                    // Clear any existing hospital session
                    $this->request->getSession()->delete('Hospital.current');
                    
                    $this->Flash->error(__('Hospital "{0}" does not exist. Redirecting to main site.', $subdomain), [
                        'element' => 'error',
                        'params' => ['autoDismiss' => true]
                    ]);
                    
                    $redirectUrl = $this->buildMainDomainUrl($host);
                    
                    // Add headers to prevent caching of redirect
                    $response = $this->response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                                               ->withHeader('Pragma', 'no-cache')
                                               ->withHeader('Expires', '0');
                    $this->setResponse($response);
                    
                    $this->redirect($redirectUrl);
                    return;
                }
                
                // If hospital exists but is inactive, redirect
                if ($hospitalCheck->status !== SiteConstants::HOSPITAL_STATUS_ACTIVE) {
                    // Clear any existing hospital session
                    $this->request->getSession()->delete('Hospital.current');
                    
                    // Set a session flag to show message after redirect
                    $this->request->getSession()->write('Hospital.redirected', [
                        'hospital' => $hospitalCheck->subdomain,
                        'reason' => SiteConstants::HOSPITAL_STATUS_INACTIVE
                    ]);
                    
                    // For development, show a more helpful message
                    $mainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
                    if ($host === 'localhost' || strpos($host, 'localhost:') === 0 || $host === $mainDomain || strpos($host, $mainDomain) !== false) {
                        $this->Flash->error(__('Hospital "{0}" is currently inactive. Please try a different hospital or go to the main site.', $subdomain), [
                            'element' => 'error',
                            'params' => ['autoDismiss' => false]
                        ]);
                    } else {
                        $this->Flash->error(__('This hospital is currently inactive. Redirecting to main site.'), [
                            'element' => 'error',
                            'params' => ['autoDismiss' => true]
                        ]);
                    }
                    
                    // Redirect to main domain (without subdomain)
                    $redirectUrl = $this->buildMainDomainUrl($host);
                    
                    // Add headers to prevent caching of redirect
                    $response = $this->response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                                               ->withHeader('Pragma', 'no-cache')
                                               ->withHeader('Expires', '0');
                    $this->setResponse($response);
                    
                    $this->redirect($redirectUrl);
                    return;
                }
                
                // Hospital exists and is active - use it
                $hospital = $hospitalCheck;
                    
                    // If hospital doesn't exist at all, redirect
                    if (!$hospitalCheck) {
                        // Clear any existing hospital session
                        $this->request->getSession()->delete('Hospital.current');
                        
                        $this->Flash->error(__('Hospital "{0}" does not exist. Redirecting to main site.', $subdomain), [
                            'element' => 'error',
                            'params' => ['autoDismiss' => true]
                        ]);
                        
                        $redirectUrl = $this->buildMainDomainUrl($host);
                        
                        // Add headers to prevent caching of redirect
                        $response = $this->response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                                                   ->withHeader('Pragma', 'no-cache')
                                                   ->withHeader('Expires', '0');
                        $this->setResponse($response);
                        
                        $this->redirect($redirectUrl);
                        return;
                    }
                    
                    // If hospital exists but is inactive, redirect
                    if ($hospitalCheck->status !== SiteConstants::HOSPITAL_STATUS_ACTIVE) {
                        // Clear any existing hospital session
                        $this->request->getSession()->delete('Hospital.current');
                        
                        // Set a session flag to show message after redirect
                        $this->request->getSession()->write('Hospital.redirected', [
                            'hospital' => $hospitalCheck->subdomain,
                            'reason' => SiteConstants::HOSPITAL_STATUS_INACTIVE
                        ]);
                        
                        // For development, show a more helpful message
                        $mainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
                        if ($host === 'localhost' || strpos($host, 'localhost:') === 0 || $host === $mainDomain || strpos($host, $mainDomain) !== false) {
                            $this->Flash->error(__('Hospital "{0}" is currently inactive. Please try a different hospital or go to the main site.', $subdomain), [
                                'element' => 'error',
                                'params' => ['autoDismiss' => false]
                            ]);
                        } else {
                            $this->Flash->error(__('This hospital is currently inactive. Redirecting to main site.'), [
                                'element' => 'error',
                                'params' => ['autoDismiss' => true]
                            ]);
                        }
                        
                        // Redirect to main domain (without subdomain)
                        $redirectUrl = $this->buildMainDomainUrl($host);
                        
                        // Add headers to prevent caching of redirect
                        $response = $this->response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                                                   ->withHeader('Pragma', 'no-cache')
                                                   ->withHeader('Expires', '0');
                        $this->setResponse($response);
                        
                        $this->redirect($redirectUrl);
                        return;
                    }
                    
                    // Hospital exists and is active - use it
                    $hospital = $hospitalCheck;
                }
            }
            
            if ($hospital) {
                // Store hospital context in session
                $this->request->getSession()->write('Hospital.current', $hospital);
            }
        } else {
            // Check if the current hospital in session is still active
            $hospitalsTable = $this->fetchTable('Hospitals');
            $hospitalCheck = $hospitalsTable->find()
                ->where(['id' => $hospital->id])
                ->first();
                
            if (!$hospitalCheck || $hospitalCheck->status !== SiteConstants::HOSPITAL_STATUS_ACTIVE) {
                // Clear session and redirect to main domain
                $this->request->getSession()->delete('Hospital.current');
                
                // Set a session flag to show message after redirect
                $this->request->getSession()->write('Hospital.redirected', [
                    'hospital' => $hospital->subdomain,
                    'reason' => 'deactivated'
                ]);
                
                $this->Flash->error(__('Your hospital session has expired or the hospital is no longer active. Redirecting to main site.'), [
                    'element' => 'error',
                    'params' => ['autoDismiss' => true]
                ]);
                
                $host = $this->request->host();
                $redirectUrl = $this->buildMainDomainUrl($host);
                $this->redirect($redirectUrl);
                return;
            }
        }
        
        if ($hospital) {
            $this->set('currentHospital', $hospital);
        }
        
        // Check if we were redirected from an inactive hospital
        $redirected = $this->request->getSession()->read('Hospital.redirected');
        if ($redirected) {
            $this->request->getSession()->delete('Hospital.redirected');
            
            $messages = [
                SiteConstants::HOSPITAL_STATUS_INACTIVE => __('You were redirected from "{0}" hospital because it is currently inactive.', $redirected['hospital']),
                'deactivated' => __('Your session for "{0}" hospital expired because it was deactivated.', $redirected['hospital']),
                'nonexistent' => __('You were redirected because "{0}" hospital does not exist.', $redirected['hospital'])
            ];
            
            $message = $messages[$redirected['reason']] ?? __('You were redirected from "{0}" hospital.', $redirected['hospital']);
            
            $this->Flash->warning($message, [
                'element' => 'error',
                'params' => ['autoDismiss' => false]
            ]);
        }
    }

    /**
     * Extract subdomain from host
     *
     * @param string $host Full hostname
     * @return string|null Subdomain or null if none
     */
    protected function extractSubdomain(string $host): ?string
    {
        // Remove port if present
        $host = explode(':', $host)[0];
        
        // Split by dots
        $parts = explode('.', $host);
        
        // If localhost or IP address, no subdomain
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }
        
        // Special handling for meg.www domain
        if ($host === 'meg.www') {
            return null; // meg.www is the main domain, no subdomain
        }
        
        // If it's a subdomain of meg.www (like hospital1.meg.www)
        if (count($parts) === 3 && $parts[1] === 'meg' && $parts[2] === 'www') {
            return $parts[0]; // Return the subdomain part
        }
        
        // If only domain.tld, no subdomain
        if (count($parts) <= 2) {
            return null;
        }
        
        // Return first part as subdomain for standard domains
        return $parts[0];
    }
    
    /**
     * Get main domain without subdomain
     *
     * @param string $host Full hostname
     * @return string Main domain
     */
    protected function getMainDomain(string $host): string
    {
        // Remove port if present
        $host = explode(':', $host)[0];
        
        // Split by dots
        $parts = explode('.', $host);
        
        // If localhost or IP address, return as is
        if ($host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return $host;
        }
        
        // Special handling for meg.www domain
        if ($host === 'meg.www') {
            return 'meg.www'; // meg.www is the main domain
        }
        
        // If it's a subdomain of meg.www (like hospital1.meg.www)
        if (count($parts) === 3 && $parts[1] === 'meg' && $parts[2] === 'www') {
            return 'meg.www'; // Return main domain without subdomain
        }
        
        // If only domain.tld, return as is
        if (count($parts) <= 2) {
            return $host;
        }
        
        // Return domain without subdomain (last two parts) for standard domains
        return implode('.', array_slice($parts, -2));
    }
    
    /**
     * Build main domain URL with proper protocol and port
     *
     * @param string $host Full hostname with port
     * @return string Complete URL to main domain
     */
    protected function buildMainDomainUrl(string $host): string
    {
        $originalHost = $host;
        $port = '';
        
        // Extract port if present
        if (strpos($host, ':') !== false) {
            $parts = explode(':', $host);
            $host = $parts[0];
            $port = ':' . $parts[1];
        }
        
        $mainDomain = $this->getMainDomain($host);
        
        // For localhost development, redirect to localhost with port
        if ($mainDomain === 'localhost') {
            return 'http://localhost' . $port;
        }
        
        // Get configured main domain
        $configuredMainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
        
        // For configured main domain, always redirect to port 80 (no port in URL)
        if ($mainDomain === $configuredMainDomain) {
            return 'http://' . $configuredMainDomain;
        }
        
        // For production domains, use HTTPS and no port
        $protocol = $this->request->is('ssl') ? 'https' : 'http';
        return $protocol . '://' . $mainDomain;
    }
}
