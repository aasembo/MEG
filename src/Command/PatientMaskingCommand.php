<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use App\Service\PatientMaskingService;

/**
 * Patient Masking Management Command
 * 
 * Allows administrators to manage patient data masking settings via CLI
 */
class PatientMaskingCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('Manage patient data masking settings');
        
        $parser->addArgument('action', [
            'help' => 'Action to perform: status, enable, disable',
            'required' => false,
            'choices' => ['status', 'enable', 'disable']
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
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $maskingService = new PatientMaskingService();
        $subcommand = $args->getArgument('action') ?? 'status';
        
        switch ($subcommand) {
            case 'status':
                $this->showStatus($maskingService, $io);
                break;
                
            case 'enable':
                $this->enableMasking($maskingService, $io);
                break;
                
            case 'disable':
                $this->disableMasking($maskingService, $io);
                break;
                
            default:
                $io->error('Unknown action. Use: status, enable, or disable');
                return static::CODE_ERROR;
        }
        
        return static::CODE_SUCCESS;
    }
    
    /**
     * Show current masking status
     */
    protected function showStatus(PatientMaskingService $maskingService, ConsoleIo $io): void
    {
        $status = $maskingService->getMaskingStatus();
        
        $io->out('<info>Patient Data Masking Status</info>');
        $io->hr();
        
        $io->out('Status: ' . ($status['enabled'] ? '<success>ENABLED</success>' : '<warning>DISABLED</warning>'));
        $io->out('Config Key: ' . $status['config_key']);
        $io->out('Default State: ' . $status['default_state']);
        $io->out('Last Modified: ' . $status['last_modified']);
        
        if ($status['warning']) {
            $io->out('');
            $io->warning($status['warning']);
        }
        
        $io->out('');
        $io->out('<info>Available Commands:</info>');
        $io->out('  bin/cake patient_masking enable   - Enable masking');
        $io->out('  bin/cake patient_masking disable  - Disable masking');
        $io->out('  bin/cake patient_masking status   - Show this status');
    }
    
    /**
     * Enable patient data masking
     */
    protected function enableMasking(PatientMaskingService $maskingService, ConsoleIo $io): void
    {
        if ($maskingService->isMaskingEnabled()) {
            $io->warning('Patient data masking is already enabled.');
            return;
        }
        
        $confirm = $io->askChoice(
            'Enable patient data masking? This will protect sensitive patient information.',
            ['y', 'n'],
            'y'
        );
        
        if ($confirm === 'y') {
            $maskingService->enableMasking();
            $io->success('Patient data masking has been enabled.');
            $io->out('All patient data will now be masked based on user roles.');
        } else {
            $io->out('Operation cancelled.');
        }
    }
    
    /**
     * Disable patient data masking
     */
    protected function disableMasking(PatientMaskingService $maskingService, ConsoleIo $io): void
    {
        if (!$maskingService->isMaskingEnabled()) {
            $io->warning('Patient data masking is already disabled.');
            return;
        }
        
        $io->warning('WARNING: Disabling masking will expose sensitive patient information!');
        $io->out('This should only be done temporarily for administrative purposes.');
        $io->out('');
        
        $confirm = $io->askChoice(
            'Are you sure you want to disable patient data masking?',
            ['y', 'n'],
            'n'
        );
        
        if ($confirm === 'y') {
            $doubleConfirm = $io->askChoice(
                'This will make sensitive patient data visible to all users. Continue?',
                ['y', 'n'],
                'n'
            );
            
            if ($doubleConfirm === 'y') {
                $maskingService->disableMasking();
                $io->warning('Patient data masking has been disabled.');
                $io->out('Sensitive patient information is now visible to all users.');
                $io->out('Remember to re-enable masking when administrative tasks are complete.');
            } else {
                $io->out('Operation cancelled.');
            }
        } else {
            $io->out('Operation cancelled.');
        }
    }
}