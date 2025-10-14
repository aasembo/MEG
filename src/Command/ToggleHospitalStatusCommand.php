<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * ToggleHospitalStatus command.
 */
class ToggleHospitalStatusCommand extends Command
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
        $parser->setDescription('Toggle hospital status between active and inactive');
        
        $parser->addArgument('subdomain', [
            'help' => 'Hospital subdomain to toggle status for',
            'required' => true
        ]);
        
        $parser->addOption('status', [
            'help' => 'Set specific status (active or inactive)',
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
        $subdomain = $args->getArgument('subdomain');
        $status = $args->getOption('status');
        
        $hospitalsTable = $this->fetchTable('Hospitals');
        
        $hospital = $hospitalsTable->find()
            ->where(['subdomain' => $subdomain])
            ->first();
            
        if (!$hospital) {
            $io->error("Hospital with subdomain '{$subdomain}' not found.");
            return static::CODE_ERROR;
        }
        
        $io->out("Current status of '{$hospital->name}' ({$subdomain}): {$hospital->status}");
        
        if ($status) {
            $newStatus = $status;
        } else {
            // Toggle status
            $newStatus = $hospital->status === SiteConstants::HOSPITAL_STATUS_ACTIVE ? SiteConstants::HOSPITAL_STATUS_INACTIVE : SiteConstants::HOSPITAL_STATUS_ACTIVE;
        }
        
        $hospital->status = $newStatus;
        
        if ($hospitalsTable->save($hospital)) {
            $io->success("Hospital '{$hospital->name}' status changed to: {$newStatus}");
            
            if ($newStatus === 'inactive') {
                $io->warning("Users accessing {$subdomain}.yourdomain.com will now be redirected to the main domain.");
            }
            
            return static::CODE_SUCCESS;
        } else {
            $io->error('Failed to update hospital status.');
            $io->out('Validation errors:');
            foreach ($hospital->getErrors() as $field => $errors) {
                foreach ($errors as $error) {
                    $io->out("  - {$field}: {$error}");
                }
            }
            return static::CODE_ERROR;
        }
    }
}