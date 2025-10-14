<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ConnectionManager;

/**
 * Check Tables Command
 */
class CheckTablesCommand extends Command
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
        $parser->setDescription('Check if medical management tables exist.');

        return $parser;
    }

    /**
     * Check for medical management tables
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $io->out('Checking medical management tables...');
        $io->hr();
        
        $connection = ConnectionManager::get('default');
        $schema = $connection->getSchemaCollection();
        
        $tablesToCheck = [
            'departments',
            'exams', 
            'exams_procedures',
            'modalities',
            'procedures',
            'sedations'
        ];
        
        $existingTables = [];
        $missingTables = [];
        
        foreach ($tablesToCheck as $tableName) {
            try {
                $tableSchema = $schema->describe($tableName);
                $existingTables[] = $tableName;
                
                $io->success("✅ Table '{$tableName}' exists");
                
                // Show columns
                $columns = $tableSchema->columns();
                $io->out("   Columns: " . implode(', ', $columns));
                
            } catch (\Exception $e) {
                $missingTables[] = $tableName;
                $io->error("❌ Table '{$tableName}' does not exist");
            }
        }
        
        $io->hr();
        $io->out("Summary:");
        $io->out("Existing tables: " . count($existingTables));
        $io->out("Missing tables: " . count($missingTables));
        
        if (!empty($missingTables)) {
            $io->out("\nYou need to create these tables first:");
            foreach ($missingTables as $table) {
                $io->out("  - {$table}");
            }
        }
        
        return static::CODE_SUCCESS;
    }
}