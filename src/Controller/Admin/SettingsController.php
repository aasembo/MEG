<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Event\EventInterface;

/**
 * Settings Controller
 *
 * Admin interface for managing hospital-specific settings
 * Does NOT allow editing system-level settings (hospital_id = 0)
 *
 * @property \App\Model\Table\SettingsTable $Settings
 */
class SettingsController extends AppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('admin');
    }

    /**
     * Before filter callback
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return \Cake\Http\Response|null|void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        
        // Only administrators can access settings
        // Add additional role checks if needed
    }

    /**
     * Index method - Shows all settings categories
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        $user = $this->Authentication->getIdentity();
        $hospitalId = $user ? $user->hospital_id : 1;
        
        // Admin can only manage AI Configuration and Notifications
        $categories = array('ai', 'notifications');
        
        // Get settings count by category
        $settingsCounts = array();
        foreach ($categories as $category) {
            $count = $this->Settings->find()
                ->where(array(
                    'hospital_id' => $hospitalId,
                    'category' => $category,
                    'is_active' => true
                ))
                ->count();
            $settingsCounts[$category] = $count;
        }
        
        $this->set(compact('categories', 'settingsCounts', 'hospitalId'));
    }

    /**
     * AI Settings management
     *
     * @return \Cake\Http\Response|null|void
     */
    public function ai()
    {
        $user = $this->Authentication->getIdentity();
        $hospitalId = $user ? $user->hospital_id : 1;
        
        if ($this->request->is(array('post', 'put'))) {
            $data = $this->request->getData();
            $userId = $user ? $user->id : null;
            
            $success = true;
            $errors = array();
            
            // Enforce mutual exclusivity: only one provider can be enabled at a time
            $openaiEnabled = isset($data['openai']['enabled']) && $data['openai']['enabled'];
            $geminiEnabled = isset($data['gemini']['enabled']) && $data['gemini']['enabled'];
            
            if ($openaiEnabled && $geminiEnabled) {
                $this->Flash->error(__('Only one AI provider can be enabled at a time. Please disable one provider before enabling another.'));
                $aiSettings = $this->Settings->getGrouped($hospitalId, 'ai', true);
                $this->set(compact('aiSettings', 'hospitalId'));
                return;
            }
            
            // Save OpenAI settings
            if (isset($data['openai'])) {
                foreach ($data['openai'] as $key => $value) {
                    // Skip saving API key if it's the placeholder value
                    if ($key === 'api_key' && (empty($value) || $value === '••••••••••••')) {
                        continue;
                    }
                    
                    // Convert checkbox values to boolean
                    if ($key === 'enabled') {
                        $value = !empty($value) && $value !== false && $value !== '0';
                    }
                    
                    // Skip empty values for optional fields
                    if ($value === '' && !in_array($key, array('enabled'))) {
                        continue;
                    }
                    
                    $fullKey = 'ai.openai.' . $key;
                    $dataType = $this->getDataType($key, $value);
                    $encrypt = in_array($key, array('api_key'));
                    
                    if (!$this->Settings->setSetting($hospitalId, $fullKey, $value, $dataType, $encrypt, $userId)) {
                        $success = false;
                        $errors[] = 'Failed to save OpenAI setting: ' . $key;
                    }
                }
                
                // If OpenAI is enabled, automatically disable Gemini
                if ($openaiEnabled) {
                    $this->Settings->setSetting($hospitalId, 'ai.gemini.enabled', false, 'boolean', false, $userId);
                    $this->Settings->setSetting($hospitalId, 'ai.default_provider', 'openai', 'string', false, $userId);
                }
            }
            
            // Save Gemini settings
            if (isset($data['gemini'])) {
                foreach ($data['gemini'] as $key => $value) {
                    // Skip saving API key if it's the placeholder value
                    if ($key === 'api_key' && (empty($value) || $value === '••••••••••••')) {
                        continue;
                    }
                    
                    // Convert checkbox values to boolean
                    if ($key === 'enabled') {
                        $value = !empty($value) && $value !== false && $value !== '0';
                    }
                    
                    // Skip empty values for optional fields
                    if ($value === '' && !in_array($key, array('enabled'))) {
                        continue;
                    }
                    
                    $fullKey = 'ai.gemini.' . $key;
                    $dataType = $this->getDataType($key, $value);
                    $encrypt = in_array($key, array('api_key'));
                    
                    if (!$this->Settings->setSetting($hospitalId, $fullKey, $value, $dataType, $encrypt, $userId)) {
                        $success = false;
                        $errors[] = 'Failed to save Gemini setting: ' . $key;
                    }
                }
                
                // If Gemini is enabled, automatically disable OpenAI
                if ($geminiEnabled) {
                    $this->Settings->setSetting($hospitalId, 'ai.openai.enabled', false, 'boolean', false, $userId);
                    $this->Settings->setSetting($hospitalId, 'ai.default_provider', 'gemini', 'string', false, $userId);
                }
            }
            
            // Save general AI settings
            if (isset($data['general'])) {
                foreach ($data['general'] as $key => $value) {
                    $fullKey = 'ai.' . $key;
                    $dataType = $this->getDataType($key, $value);
                    
                    if (!$this->Settings->setSetting($hospitalId, $fullKey, $value, $dataType, false, $userId)) {
                        $success = false;
                        $errors[] = 'Failed to save AI setting: ' . $key;
                    }
                }
            }
            
            // Save budget settings
            if (isset($data['budget'])) {
                foreach ($data['budget'] as $key => $value) {
                    $fullKey = 'ai.budget.' . $key;
                    $dataType = $this->getDataType($key, $value);
                    
                    if (!$this->Settings->setSetting($hospitalId, $fullKey, $value, $dataType, false, $userId)) {
                        $success = false;
                        $errors[] = 'Failed to save budget setting: ' . $key;
                    }
                }
            }
            
            if ($success) {
                $this->Flash->success(__('AI settings have been saved successfully.'));
                return $this->redirect(array('action' => 'ai'));
            } else {
                $this->Flash->error(__('Some settings could not be saved. Please try again.'));
                if (!empty($errors)) {
                    foreach ($errors as $error) {
                        $this->Flash->error($error);
                    }
                }
            }
        }
        
        // Load current settings
        $aiSettings = $this->Settings->getGrouped($hospitalId, 'ai', true);
        
        $this->set(compact('aiSettings', 'hospitalId'));
    }

    /**
     * Security Settings management
     *
     * @return \Cake\Http\Response|null|void
     */
    public function security()
    {
        $user = $this->Authentication->getIdentity();
        $hospitalId = $user ? $user->hospital_id : 1;
        
        if ($this->request->is(array('post', 'put'))) {
            $data = $this->request->getData();
            $userId = $user ? $user->id : null;
            
            $success = true;
            
            foreach ($data as $key => $value) {
                $fullKey = 'security.' . $key;
                $dataType = $this->getDataType($key, $value);
                
                if (!$this->Settings->setSetting($hospitalId, $fullKey, $value, $dataType, false, $userId)) {
                    $success = false;
                }
            }
            
            if ($success) {
                $this->Flash->success(__('Security settings have been saved successfully.'));
                return $this->redirect(array('action' => 'security'));
            } else {
                $this->Flash->error(__('Some settings could not be saved. Please try again.'));
            }
        }
        
        $securitySettings = $this->Settings->getGrouped($hospitalId, 'security', true);
        
        $this->set(compact('securitySettings', 'hospitalId'));
    }

    /**
     * Notifications Settings management
     *
     * @return \Cake\Http\Response|null|void
     */
    public function notifications()
    {
        $user = $this->Authentication->getIdentity();
        $hospitalId = $user ? $user->hospital_id : 1;
        
        if ($this->request->is(array('post', 'put'))) {
            $data = $this->request->getData();
            $userId = $user ? $user->id : null;
            
            $success = true;
            
            // Handle email settings
            if (isset($data['email'])) {
                foreach ($data['email'] as $key => $value) {
                    $fullKey = 'notifications.email.' . $key;
                    $dataType = $this->getDataType($key, $value);
                    $encrypt = in_array($key, array('password', 'api_key'));
                    
                    if (!$this->Settings->setSetting($hospitalId, $fullKey, $value, $dataType, $encrypt, $userId)) {
                        $success = false;
                    }
                }
            }
            
            // Handle SMS settings
            if (isset($data['sms'])) {
                foreach ($data['sms'] as $key => $value) {
                    $fullKey = 'notifications.sms.' . $key;
                    $dataType = $this->getDataType($key, $value);
                    $encrypt = in_array($key, array('api_key', 'api_secret'));
                    
                    if (!$this->Settings->setSetting($hospitalId, $fullKey, $value, $dataType, $encrypt, $userId)) {
                        $success = false;
                    }
                }
            }
            
            if ($success) {
                $this->Flash->success(__('Notification settings have been saved successfully.'));
                return $this->redirect(array('action' => 'notifications'));
            } else {
                $this->Flash->error(__('Some settings could not be saved. Please try again.'));
            }
        }
        
        $notificationSettings = $this->Settings->getGrouped($hospitalId, 'notifications', true);
        
        $this->set(compact('notificationSettings', 'hospitalId'));
    }

    /**
     * Test AI Provider Connection
     *
     * @return \Cake\Http\Response|null|void
     */
    public function testProvider()
    {
        $this->request->allowMethod(array('post'));
        $this->autoRender = false;
        
        $provider = $this->request->getData('provider');
        $user = $this->Authentication->getIdentity();
        $hospitalId = $user ? $user->hospital_id : 1;
        
        // Load provider settings
        $settings = $this->Settings->getGrouped($hospitalId, 'ai', true);
        
        $result = array(
            'success' => false,
            'message' => 'Provider test not implemented yet'
        );
        
        // TODO: Implement actual provider testing
        // This would call OpenAI or Gemini API to verify credentials
        
        return $this->response->withType('application/json')
            ->withStringBody(json_encode($result));
    }

    /**
     * Helper method to determine data type from value
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return string
     */
    private function getDataType(string $key, $value): string
    {
        // Boolean fields
        if (in_array($key, array('enabled', 'fallback_enabled', 'two_factor', 'require_special', 'require_numbers', 'require_uppercase'))) {
            return 'boolean';
        }
        
        // Integer fields
        if (in_array($key, array('max_retries', 'timeout_seconds', 'alert_threshold', 'session_timeout', 'min_length', 'expiry_days', 'max_tokens'))) {
            return 'integer';
        }
        
        // Float fields
        if (in_array($key, array('temperature', 'monthly_limit', 'top_p'))) {
            return 'float';
        }
        
        // Array fields
        if (is_array($value)) {
            return 'array';
        }
        
        // Default to string
        return 'string';
    }
}
