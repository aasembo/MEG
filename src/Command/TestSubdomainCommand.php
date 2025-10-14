<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use App\Constants\SiteConstants;

/**
 * Test subdomain extraction command
 */
class TestSubdomainCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->setDescription('Test subdomain extraction logic.');
        $parser->addOption('host', [
            'help' => 'Host to test subdomain extraction on',
            'required' => true,
        ]);

        return $parser;
    }

    /**
     * Test subdomain extraction
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $host = $args->getOption('host');
        
        $io->out("Testing subdomain extraction for host: {$host}");
        $io->hr();
        
        // Extract subdomain using the same logic as middleware
        $subdomain = $this->extractSubdomain($host);
        
        $io->out("Host: {$host}");
        $io->out("Extracted Subdomain: " . ($subdomain ?: 'NONE'));
        
        // Test hospital lookup
        $hospitalsTable = $this->fetchTable('Hospitals');
        
        if ($subdomain) {
            $hospital = $hospitalsTable->find()
                ->where(['subdomain' => $subdomain, 'status' => SiteConstants::HOSPITAL_STATUS_ACTIVE])
                ->first();
                
            if ($hospital) {
                $io->success("✅ Hospital found: {$hospital->name} (ID: {$hospital->id})");
            } else {
                $io->error("❌ No active hospital found for subdomain: {$subdomain}");
            }
        } else {
            // Default to hospital1
            $hospital = $hospitalsTable->find()
                ->where(['subdomain' => 'hospital1', 'status' => SiteConstants::HOSPITAL_STATUS_ACTIVE])
                ->first();
                
            if ($hospital) {
                $io->success("✅ Defaulting to Hospital1: {$hospital->name} (ID: {$hospital->id})");
            } else {
                $io->error("❌ No active hospital1 found for default");
            }
        }
        
        return static::CODE_SUCCESS;
    }
    
    /**
     * Extract subdomain from host
     */
    private function extractSubdomain(string $host): ?string
    {
        // Remove port if present
        $host = explode(':', $host)[0];
        
        // Split by dots
        $parts = explode('.', $host);
        
        // If we have at least 3 parts (subdomain.domain.tld), extract subdomain
        if (count($parts) >= 3) {
            return $parts[0];
        }
        
        // Check for development hosts that might contain subdomain info
        if (strpos($host, 'hospital') === 0 && strlen($host) > 8) {
            // Extract subdomain from hosts like "hospital2.dev" or "hospital1.local"
            $subdomain = explode('.', $host)[0];
            if (preg_match('/^hospital\d+$/', $subdomain)) {
                return $subdomain;
            }
        }
        
        return null;
    }
}