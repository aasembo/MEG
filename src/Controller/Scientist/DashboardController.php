<?php
declare(strict_types=1);

namespace App\Controller\Scientist;

use App\Controller\AppController;

/**
 * Scientist Dashboard Controller
 *
 * Handles the scientist dashboard and related functionality
 */
class DashboardController extends AppController
{
    /**
     * Scientist dashboard index
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        // Check if user is authenticated and has scientist role
        $user = $this->Authentication->getIdentity();
        if (!$user) {
            $this->Flash->error(__('Authentication required.'));
            return $this->redirect(['prefix' => 'Scientist', 'controller' => 'Login', 'action' => 'login']);
        }
        
        // Load user with role relationship to check role.type
        $usersTable = $this->fetchTable('Users');
        $userWithRole = $usersTable->find()
            ->contain(['Roles'])
            ->where(['Users.id' => $user->id])
            ->first();
            
        if (!$userWithRole || !$userWithRole->role || $userWithRole->role->type !== 'scientist') {
            $this->Flash->error(__('Access denied. Scientist privileges required.'));
            return $this->redirect(['prefix' => 'Scientist', 'controller' => 'Login', 'action' => 'login']);
        }

        // Get current hospital context
        $currentHospital = $this->request->getSession()->read('Hospital.current');
        
        $this->set(compact('user', 'currentHospital'));
    }
}