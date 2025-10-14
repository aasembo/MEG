<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * ListHospitals command.
 */
class ListHospitalsCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/5/en/console-commands/option-parsers.html
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('List all hospitals and their current status');
        
        $parser->addOption('status', [
            'help' => 'Filter by status (active or inactive)',
            'choices' => ['active', 'inactive']
        ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $status = $args->getOption('status');
        
        $hospitalsTable = $this->fetchTable('Hospitals');
        
        $query = $hospitalsTable->find();
        
        if ($status) {
            $query->where(['status' => $status]);
        }
        
        $hospitals = $query->orderAsc('subdomain')->all();
        
        if ($hospitals->isEmpty()) {
            if ($status) {
                $io->warning("No {$status} hospitals found.");
            } else {
                $io->warning("No hospitals found in the database.");
            }
            return static::CODE_SUCCESS;
        }
        
        $io->out('');
        $io->out('<info>Hospital Status Report</info>');
        $io->out('======================');
        $io->out('');
        
        $activeCount = 0;
        $inactiveCount = 0;
        
        foreach ($hospitals as $hospital) {
            $statusIcon = $hospital->status === 'active' ? '<success>✓</success>' : '<error>✗</error>';
            $statusText = $hospital->status === 'active' ? '<success>ACTIVE</success>' : '<error>INACTIVE</error>';
            
            $io->out(sprintf(
                '%s %-15s %-25s %s',
                $statusIcon,
                $hospital->subdomain,
                $hospital->name,
                $statusText
            ));
            
            if ($hospital->status === 'active') {
                $activeCount++;
            } else {
                $inactiveCount++;
            }
            
            // Show development URL
            if (!$status || $hospital->status === 'active') {
                $io->out("    <comment>Development URL: http://localhost:8765/?hospital={$hospital->subdomain}</comment>");
            }
            $io->out('');
        }
        
        $io->out('Summary:');
        $io->out("  Active hospitals:   {$activeCount}");
        $io->out("  Inactive hospitals: {$inactiveCount}");
        $io->out("  Total hospitals:    " . ($activeCount + $inactiveCount));
        $io->out('');
        
        if ($inactiveCount > 0) {
            $io->warning("Note: Inactive hospitals will redirect users to the main domain.");
        }
        
        return static::CODE_SUCCESS;
    }
}