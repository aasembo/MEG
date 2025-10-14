<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\ModelAwareTrait;

/**
 * CreateSuperUser command.
 */
class CreateSuperUserCommand extends Command
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
        $parser->setDescription('Create a super admin user for the admin panel');
        $parser->addOption('email', [
            'help' => 'Email address for the super user',
            'required' => true,
        ]);
        $parser->addOption('password', [
            'help' => 'Password for the super user',
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
        $rolesTable = $this->fetchTable('Roles');

        // Check if user already exists
        $existingUser = $usersTable->find()
            ->where(['email' => $email])
            ->first();

        if ($existingUser) {
            $io->error('User with this email already exists');
            return static::CODE_ERROR;
        }

        // Find or create the super role
        $superRole = $rolesTable->find()
            ->where(['type' => SiteConstants::ROLE_TYPE_SUPER])
            ->first();

        if (!$superRole) {
            // Create super role if it doesn't exist
            $superRole = $rolesTable->newEntity([
                'name' => 'Super Administrator',
                'type' => SiteConstants::ROLE_TYPE_SUPER,
                'description' => 'Full system access'
            ]);
            
            if (!$rolesTable->save($superRole)) {
                $io->error('Failed to create super role');
                return static::CODE_ERROR;
            }
            $io->out('Created super role');
        }

        // Create new super user
        $user = $usersTable->newEntity([
            'email' => $email,
            'password' => $password,
            'role_id' => $superRole->id,
            'status' => SiteConstants::USER_STATUS_ACTIVE,
            'username' => explode('@', $email)[0], // Use email prefix as username
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'hospital_id' => 1, // Default hospital_id
        ]);

        if ($usersTable->save($user)) {
            $io->success("Super user created successfully!");
            $io->out("Email: {$email}");
            $io->out("Role: super");
            $mainDomain = \Cake\Core\Configure::read('App.mainDomain', 'meg.www');
            $io->out("You can now log in to the admin panel at http://{$mainDomain}/admin");
        } else {
            $io->error('Failed to create user');
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