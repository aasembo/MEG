<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\View\Exception\MissingTemplateException;
use App\Lib\UserActivityLogger;
use App\Constants\SiteConstants;

class PagesController extends AppController
{
    private $activityLogger;
    
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('frontend');
        $this->activityLogger = new UserActivityLogger();
        
        // Only load Authentication component for non-home actions
        // The home action needs special handling to check authentication without requiring it
        if ($this->request->getParam('action') !== 'home') {
            $this->loadComponent('Authentication.Authentication');
        }
    }
    
    public function home()
    {
        // Check if user is authenticated by examining session data
        $sessionUser = $this->request->getSession()->read('Auth');
        if ($sessionUser && isset($sessionUser['id'])) {
            // User is authenticated, load their full details with role
            $usersTable = $this->fetchTable('Users');
            $userWithRole = $usersTable->find()
                ->contain(['Roles'])
                ->where(['Users.id' => $sessionUser['id']])
                ->first();
            
            if ($userWithRole && $userWithRole->role && $userWithRole->role->type) {
                // Log the redirect activity
                $this->activityLogger->log(SiteConstants::EVENT_UNAUTHORIZED_ACCESS_ATTEMPT, [
                    'user_id' => $sessionUser['id'],
                    'role_type' => $userWithRole->role->type,
                    'request' => $this->request,
                    'status' => SiteConstants::ACTIVITY_STATUS_WARNING,
                    'description' => 'Authenticated user attempted to access frontend homepage, redirected to dashboard',
                    'event_data' => [
                        'attempted_url' => $this->request->getRequestTarget(),
                        'redirect_reason' => 'authenticated_user_dashboard_redirect'
                    ]
                ]);
                
                return $this->redirectToRoleDashboard($userWithRole->role->type);
            }
        }
        
        try {
            $rolesTable = $this->fetchTable('Roles');
            $roles = $rolesTable->find('list', [
                'keyField' => 'id',
                'valueField' => 'name'
            ])
            ->where(['type !=' => 'super'])
            ->toArray();
            
            $roleTypes = $rolesTable->find('list', [
                'keyField' => 'type',
                'valueField' => 'name'
            ])
            ->where(['type !=' => 'super'])
            ->toArray();
            
            $usersTable = $this->fetchTable('Users');
            $hospitalsTable = $this->fetchTable('Hospitals');
            
            $stats = [
                'total_users' => $usersTable->find()->count(),
                'total_hospitals' => $hospitalsTable->find()->where(['status' => SiteConstants::HOSPITAL_STATUS_ACTIVE])->count(),
                'total_records' => $usersTable->find()->count() + $hospitalsTable->find()->count(),
                'total_medical_staff' => $usersTable->find()
                    ->contain(['Roles'])
                    ->where(['Roles.type IN' => ['doctor', 'nurse']])
                    ->count(),
                'total_patients' => $usersTable->find()
                    ->contain(['Roles'])
                    ->where(['Roles.type' => 'patient'])
                    ->count(),
                'total_scientists' => $usersTable->find()
                    ->contain(['Roles'])
                    ->where(['Roles.type' => 'scientist'])
                    ->count()
            ];
        } catch (\Exception $e) {
            $roles = [];
            $roleTypes = [];
            $stats = [
                'total_users' => 0,
                'total_hospitals' => 0,
                'total_records' => 0,
                'total_medical_staff' => 0,
                'total_patients' => 0,
                'total_scientists' => 0
            ];
            $this->log($e->getMessage(), 'error');
        }
        
        $this->set(compact('roles', 'roleTypes', 'stats'));
    }

    public function display(string ...$path): ?Response
    {
        if (!$path) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }
        $this->set(compact('page', 'subpage'));

        try {
            return $this->render(implode('/', $path));
        } catch (MissingTemplateException $exception) {
            if (Configure::read('debug')) {
                throw $exception;
            }
            throw new NotFoundException();
        }
    }
    
    /**
     * Redirect user to their role-specific dashboard
     *
     * @param string $role User role type
     * @return \Cake\Http\Response
     */
    private function redirectToRoleDashboard(string $role): \Cake\Http\Response
    {
        // Map role names to dashboard routes (using role types)
        $roleRoutes = [
            'admin' => ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
            'administrator' => ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
            'doctor' => ['prefix' => 'Doctor', 'controller' => 'Dashboard', 'action' => 'index'],
            'technician' => ['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index'],
            'scientist' => ['prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index'],
            'nurse' => ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
            'super_administrator' => ['prefix' => 'System', 'controller' => 'Dashboard', 'action' => 'index'],
            'super' => ['prefix' => 'System', 'controller' => 'Dashboard', 'action' => 'index'],
            'superadmin' => ['prefix' => 'System', 'controller' => 'Dashboard', 'action' => 'index'],
        ];
        
        $route = $roleRoutes[strtolower($role)] ?? null;
        
        if ($route) {
            return $this->redirect($route);
        }
        
        // Fallback: if role not recognized, redirect to a generic dashboard or logout
        return $this->redirect(['controller' => 'Users', 'action' => 'logout', 'prefix' => false]);
    }
}
