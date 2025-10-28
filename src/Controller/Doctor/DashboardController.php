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
        
        // Fetch cases table for statistics (CakePHP 5 syntax)
        $casesTable = $this->fetchTable('Cases');
        $caseAssignmentsTable = $this->fetchTable('CaseAssignments');
        
        // Helper function to build base query with assignment check
        $buildAssignedQuery = function() use ($casesTable, $caseAssignmentsTable, $user, $currentHospital) {
            return $casesTable->find()
                ->where([
                    'Cases.hospital_id' => $currentHospital->id,
                    'OR' => [
                        ['Cases.current_user_id' => $user->id],
                        function ($exp) use ($caseAssignmentsTable, $user) {
                            return $exp->exists(
                                $caseAssignmentsTable->find()
                                    ->select(['id'])
                                    ->where([
                                        'CaseAssignments.case_id = Cases.id',
                                        'CaseAssignments.user_id' => $user->id
                                    ])
                            );
                        }
                    ]
                ]);
        };
        
        // Get case statistics for doctor (includes assigned cases from CaseAssignments)
        // Note: Doctors don't have 'draft' status - they start with 'assigned' when case is assigned to them
        $totalCases = $buildAssignedQuery()->count();
            
        $assignedCases = $buildAssignedQuery()
            ->where(['Cases.doctor_status' => 'assigned'])
            ->count();
            
        $inProgressCases = $buildAssignedQuery()
            ->where(['Cases.doctor_status' => 'in_progress'])
            ->count();
            
        $completedCases = $buildAssignedQuery()
            ->where(['Cases.doctor_status' => 'completed'])
            ->count();
            
        $cancelledCases = $buildAssignedQuery()
            ->where(['Cases.doctor_status' => 'cancelled'])
            ->count();
            
        // Get recent cases (last 10) - includes assigned cases from CaseAssignments
        $recentCases = $buildAssignedQuery()
            ->contain([
                'PatientUsers',
                'Departments',
                'Hospitals',
                'CurrentUsers'
            ])
            ->order(['Cases.modified' => 'DESC'])
            ->limit(10)
            ->all();
            
        // Get priority statistics - includes assigned cases from CaseAssignments
        $urgentCases = $buildAssignedQuery()
            ->where([
                'Cases.priority' => 'urgent',
                'Cases.doctor_status NOT IN' => ['completed', 'cancelled']
            ])
            ->count();
            
        $highPriorityCases = $buildAssignedQuery()
            ->where([
                'Cases.priority' => 'high',
                'Cases.doctor_status NOT IN' => ['completed', 'cancelled']
            ])
            ->count();
        
        $this->set(compact(
            'user',
            'userWithRole',
            'currentHospital',
            'totalCases',
            'assignedCases',
            'inProgressCases',
            'completedCases',
            'cancelledCases',
            'recentCases',
            'urgentCases',
            'highPriorityCases'
        ));
    }
}