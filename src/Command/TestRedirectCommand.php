<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * TestRedirect command to test redirection logic
 */
class TestRedirectCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('Test hospital redirection logic with various hostnames');
        
        $parser->addArgument('hostname', [
            'help' => 'Hostname to test (e.g., meg.www, hospital2.meg.www, localhost:8765)',
            'required' => true
        ]);
        
        $parser->addOption('hospital', [
            'help' => 'Hospital parameter for localhost testing'
        ]);

        return $parser;
    }

    /**
     * Test the redirection logic
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $hostname = $args->getArgument('hostname');
        $hospitalParam = $args->getOption('hospital');
        
        $io->out("<info>Testing redirection logic for: {$hostname}</info>");
        $io->out('');
        
        // Simulate the subdomain extraction
        $subdomain = $this->extractSubdomain($hostname);
        $io->out("Extracted subdomain: " . ($subdomain ?: 'null'));
        
        // Apply query parameter logic if applicable
        $mainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
        if ($hospitalParam && ($hostname === 'localhost' || strpos($hostname, 'localhost:') === 0 || $hostname === $mainDomain || strpos($hostname, $mainDomain) !== false)) {
            $subdomain = $hospitalParam;
            $io->out("Applied hospital parameter: {$hospitalParam}");
        }
        
        $hospitalsTable = $this->fetchTable('Hospitals');
        
        // If no subdomain detected, don't set any hospital context (main domain behavior)
        if (empty($subdomain)) {
            $io->out("<success>Main domain access - no hospital context set</success>");
            return static::CODE_SUCCESS;
        } else {
            // Subdomain was specified - check if it exists
            $hospital = $hospitalsTable->find()
                ->where(['subdomain' => $subdomain])
                ->first();
        }
        
        $io->out("Final subdomain to check: {$subdomain}");
        $io->out('');
            
        if ($hospital) {
            $io->out("Hospital found: {$hospital->name}");
            $io->out("Status: {$hospital->status}");
            
            if ($hospital->status !== SiteConstants::HOSPITAL_STATUS_ACTIVE) {
                $io->out("<error>Hospital is INACTIVE - should redirect (NO fallback to hospital1)</error>");
                
                $mainDomain = $this->getMainDomain($hostname);
                $redirectUrl = $this->buildMainDomainUrl($hostname);
                
                $io->out("Main domain: {$mainDomain}");
                $io->out("Redirect URL: {$redirectUrl}");
            } else {
                $io->out("<success>Hospital is ACTIVE - should allow access</success>");
            }
        } else {
            if ($subdomain === 'hospital1') {
                $io->out("<error>Default hospital1 not found - should redirect</error>");
            } else {
                $io->out("<error>Hospital not found for subdomain: {$subdomain} - should redirect (NO fallback)</error>");
            }
        }
        
        return static::CODE_SUCCESS;
    }
    
    /**
     * Extract subdomain from host (copy of AppController logic)
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
     * Get main domain without subdomain (copy of AppController logic)
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
     * Build main domain URL with proper protocol and port (copy of AppController logic)
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
        $protocol = 'http'; // Default to HTTP for testing
        return $protocol . '://' . $mainDomain;
    }
}