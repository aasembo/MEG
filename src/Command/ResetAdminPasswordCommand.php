<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ModelAwareTrait;
use App\Constants\SiteConstants;

/**
 * ResetAdminPassword command.
 */
class ResetAdminPasswordCommand extends Command
{
    use ModelAwareTrait;

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/5/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->setDescription('Reset password for an admin user');
        $parser->addOption('email', [
            'help' => 'Email address of the admin user',
            'required' => true,
        ]);
        $parser->addOption('password', [
            'help' => 'New password for the admin user',
            'required' => true,
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
        $email = $args->getOption('email');
        $password = $args->getOption('password');

        if (!$email || !$password) {
            $io->error('Email and password are required');
            return static::CODE_ERROR;
        }

        $usersTable = $this->fetchTable('Users');

        // Find the user
        $user = $usersTable->find()
            ->contain(['Roles'])
            ->where(['Users.email' => $email])
            ->first();

        if (!$user) {
            $io->error("User with email '{$email}' not found");
            return static::CODE_ERROR;
        }

        // Check if user has admin/super role
        if (!$user->role || !in_array($user->role->type, [SiteConstants::ROLE_TYPE_SUPER, SiteConstants::ROLE_TYPE_ADMIN])) {
            $io->error("User '{$email}' is not an admin user");
            return static::CODE_ERROR;
        }

        // Update the password
        $user->password = $password;
        
        if ($usersTable->save($user)) {
            $io->success("Password reset successfully for user: {$email}");
            $io->out("Role: {$user->role->type}");
            $io->out("Status: {$user->status}");
            $mainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
            $io->out("You can now log in to the admin panel at http://{$mainDomain}/admin");
        } else {
            $io->error('Failed to reset password');
            foreach ($user->getErrors() as $field => $errors) {
                foreach ($errors as $error) {
                    $io->error("{$field}: {$error}");
                }
            }
            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }
}