<?php
declare(strict_types=1);

namespace App\Controller\Doctor;

use App\Controller\AppController;

/**
 * Doctor Dashboard Controller
 *
 * Handles the doctor dashboard and related functionality
 */
class DashboardController extends AppController
{
    /**
     * Doctor dashboard index
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        // Check if user is authenticated and has doctor role
        $user = $this->Authentication->getIdentity();
        if (!$user) {
            $this->Flash->error(__('Authentication required.'));
            return $this->redirect(['prefix' => 'Doctor', 'controller' => 'Login', 'action' => 'login']);
        }
        
        // Load user with role relationship to check role.type
        $usersTable = $this->fetchTable('Users');
        $userWithRole = $usersTable->find()
            ->contain(['Roles'])
            ->where(['Users.id' => $user->id])
            ->first();
            
        if (!$userWithRole || !$userWithRole->role || $userWithRole->role->type !== 'doctor') {
            $this->Flash->error(__('Access denied. Doctor privileges required.'));
            return $this->redirect(['prefix' => 'Doctor', 'controller' => 'Login', 'action' => 'login']);
        }

        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        $this->set(compact('user', 'currentHospital'));
    }
}