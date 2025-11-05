<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

class AdminController extends AppController {
    public function initialize(): void {
        parent::initialize();
        
        // Set admin layout for all admin pages
        $this->viewBuilder()->setLayout('admin');
    }
    
    /**
     * Get current authenticated user
     * Simple helper method - role checking is handled by RoleBasedAccessMiddleware
     */
    protected function getAuthUser() {
        return $this->Authentication->getIdentity();
    }
    
    /**
     * Redirect user to appropriate role-based dashboard
     *
     * @param string $roleType User role type
     * @return \Cake\Http\Response
     */
    private function redirectToRoleDashboard(string $roleType): \Cake\Http\Response
    {
        // Map role types to dashboard routes
        $roleRoutes = [
            'administrator' => ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
            'doctor' => ['prefix' => 'Doctor', 'controller' => 'Dashboard', 'action' => 'index'],
            'technician' => ['prefix' => 'Technician', 'controller' => 'Dashboard', 'action' => 'index'],
            'scientist' => ['prefix' => 'Scientist', 'controller' => 'Dashboard', 'action' => 'index'],
            'nurse' => ['prefix' => 'Admin', 'controller' => 'Dashboard', 'action' => 'index'],
            'super' => ['prefix' => 'System', 'controller' => 'Dashboard', 'action' => 'index']
        ];
        
        $route = $roleRoutes[strtolower($roleType)] ?? null;
        
        if ($route) {
            return $this->redirect($route);
        }
        
        // Fallback to homepage if role not recognized
        return $this->redirect(['controller' => 'Pages', 'action' => 'home', 'prefix' => false]);
    }
}