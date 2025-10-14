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
 * CreateTestUser command.
 */
class CreateTestUserCommand extends Command
{
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
        $parser->setDescription('Create a test user with a specialized role to test automatic record creation.');
        
        $parser->addOption('role', [
            'help' => 'Role type (doctor, nurse, scientist, patient, technician)',
            'required' => true,
        ]);
        
        $parser->addOption('email', [
            'help' => 'User email',
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
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $roleType = $args->getOption('role');
        $email = $args->getOption('email');
        
        $io->out("Creating test user with role: {$roleType}");
        
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        $hospitalsTable = TableRegistry::getTableLocator()->get('Hospitals');
        
        // Get the role
        $role = $rolesTable->find()->where(['type' => $roleType])->first();
        if (!$role) {
            $io->error("Role type '{$roleType}' not found.");
            return static::CODE_ERROR;
        }
        
        // Get a hospital
        $hospital = $hospitalsTable->find()->first();
        if (!$hospital) {
            $io->error('No hospital found. Please create a hospital first.');
            return static::CODE_ERROR;
        }
        
        // Create user data
        $userData = [
            'role_id' => $role->id,
            'hospital_id' => $hospital->id,
            'username' => 'test_' . $roleType . '_' . time(), // Make username unique
            'first_name' => 'Test',
            'last_name' => ucfirst($roleType),
            'email' => $email,
            'password' => 'test123',
            'status' => SiteConstants::USER_STATUS_ACTIVE,
            'type' => 'regular'
        ];
        
        $user = $usersTable->newEmptyEntity();
        $user = $usersTable->patchEntity($user, $userData);
        
        if ($usersTable->save($user)) {
            $io->success("User created successfully with ID: {$user->id}");
            
            // Now manually trigger the specialized record creation to test
            $user = $usersTable->get($user->id, ['contain' => ['Roles']]);
            
            // Create specialized record
            $this->createSpecializedRecord($user, $io);
            
            return static::CODE_SUCCESS;
        } else {
            $io->error('Failed to create user:');
            $io->out(print_r($user->getErrors(), true));
            return static::CODE_ERROR;
        }
    }
    
    /**
     * Create a corresponding record in specialized table based on user role
     *
     * @param \App\Model\Entity\User $user The user entity
     * @param \Cake\Console\ConsoleIo $io Console IO for output
     * @return void
     */
    private function createSpecializedRecord($user, $io)
    {
        if (!$user->role) {
            $io->warning('User has no role, skipping specialized record creation.');
            return;
        }

        $roleType = $user->role->type;
        $specializedData = [
            'user_id' => $user->id,
            'hospital_id' => $user->hospital_id ?: 0,
        ];

        $io->out("Creating {$roleType} record for user {$user->id}...");

        switch ($roleType) {
            case 'doctor':
                $doctorsTable = TableRegistry::getTableLocator()->get('Doctors');
                $doctor = $doctorsTable->newEmptyEntity();
                $doctor = $doctorsTable->patchEntity($doctor, $specializedData);
                if ($doctorsTable->save($doctor)) {
                    $io->success("Doctor record created with ID: {$doctor->id}");
                } else {
                    $io->error('Failed to create doctor record.');
                }
                break;

            case 'nurse':
                $nursesTable = TableRegistry::getTableLocator()->get('Nurses');
                $nurse = $nursesTable->newEmptyEntity();
                $nurse = $nursesTable->patchEntity($nurse, $specializedData);
                if ($nursesTable->save($nurse)) {
                    $io->success("Nurse record created with ID: {$nurse->id}");
                } else {
                    $io->error('Failed to create nurse record.');
                }
                break;

            case 'scientist':
                $scientistsTable = TableRegistry::getTableLocator()->get('Scientists');
                $scientist = $scientistsTable->newEmptyEntity();
                $scientist = $scientistsTable->patchEntity($scientist, $specializedData);
                if ($scientistsTable->save($scientist)) {
                    $io->success("Scientist record created with ID: {$scientist->id}");
                } else {
                    $io->error('Failed to create scientist record.');
                }
                break;

            case 'patient':
                $patientsTable = TableRegistry::getTableLocator()->get('Patients');
                $patientData = array_merge($specializedData, [
                    'gender' => 'M',
                    'dob' => '1990-01-01',
                    'phone' => '555-0123',
                    'address' => '123 Test Street, Test City, TC 12345',
                    'notes' => 'Test patient created by command',
                    'emergency_contact_name' => 'Emergency Contact',
                    'emergency_contact_phone' => '555-0199'
                ]);
                $io->out('Patient data being saved: ' . print_r($patientData, true));
                $patient = $patientsTable->newEmptyEntity();
                $patient = $patientsTable->patchEntity($patient, $patientData);
                $io->out('Patient entity after patch: ' . print_r($patient->toArray(), true));
                if ($patientsTable->save($patient)) {
                    $io->success("Patient record created with ID: {$patient->id}");
                    // Verify what was actually saved
                    $savedPatient = $patientsTable->get($patient->id);
                    $io->out('Saved patient data: ' . print_r($savedPatient->toArray(), true));
                } else {
                    $io->error('Failed to create patient record.');
                    $io->out('Validation errors: ' . print_r($patient->getErrors(), true));
                }
                break;

            case 'technician':
                $techniciansTable = TableRegistry::getTableLocator()->get('Technicians');
                $technician = $techniciansTable->newEmptyEntity();
                $technician = $techniciansTable->patchEntity($technician, $specializedData);
                if ($techniciansTable->save($technician)) {
                    $io->success("Technician record created with ID: {$technician->id}");
                } else {
                    $io->error('Failed to create technician record.');
                }
                break;
                
            default:
                $io->warning("No specialized table for role type: {$roleType}");
        }
    }
}