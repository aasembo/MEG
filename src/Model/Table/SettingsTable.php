<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Utility\Security;
use Cake\Core\Configure;
use Cake\Validation\Validator;
use Cake\Log\Log;

/**
 * Settings Model
 *
 * Universal settings table for system-wide and hospital-specific configuration
 * hospital_id = 0: System-wide settings (not editable by hospital admins)
 * hospital_id > 0: Hospital-specific settings
 */
class SettingsTable extends Table
{
    const SYSTEM_HOSPITAL_ID = 0;

    /**
     * Initialize method
     *
     * @param array $config Configuration array
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        
        $this->setTable('settings');
        $this->setPrimaryKey('id');
        $this->setDisplayField('name');
        
        $this->addBehavior('Timestamp');
        
        $this->belongsTo('Hospitals', array(
            'foreignKey' => 'hospital_id',
            'joinType' => 'LEFT'
        ));
        
        $this->belongsTo('LastModifiedByUsers', array(
            'className' => 'Users',
            'foreignKey' => 'last_modified_by'
        ));
    }

    /**
     * Default validation rules
     *
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->integer('hospital_id')
            ->notEmptyString('hospital_id');

        $validator
            ->scalar('category')
            ->maxLength('category', 50)
            ->notEmptyString('category');

        $validator
            ->scalar('name')
            ->maxLength('name', 100)
            ->notEmptyString('name');

        $validator
            ->scalar('value')
            ->notEmptyString('value');

        $validator
            ->scalar('data_type')
            ->notEmptyString('data_type');

        $validator
            ->boolean('is_encrypted')
            ->notEmptyString('is_encrypted');

        $validator
            ->boolean('is_active')
            ->notEmptyString('is_active');

        $validator
            ->scalar('description')
            ->maxLength('description', 65535)
            ->allowEmptyString('description');

        return $validator;
    }

    /**
     * Get a setting value
     * Falls back to system setting if hospital setting not found
     *
     * @param int $hospitalId Hospital ID (0 for system)
     * @param string $key Setting key (e.g., 'ai.openai.api_key')
     * @param mixed $default Default value if not found
     * @param bool $fallbackToSystem Whether to check system settings if not found
     * @return mixed
     */
    public function getSetting(int $hospitalId, string $key, $default = null, bool $fallbackToSystem = true)
    {
        $parts = $this->parseKey($key);
        $category = $parts['category'];
        $name = $parts['name'];
        
        // Try hospital-specific setting first
        $setting = $this->findSetting($hospitalId, $category, $name);
        
        // Fall back to system setting if not found and allowed
        if (!$setting && $fallbackToSystem && $hospitalId !== self::SYSTEM_HOSPITAL_ID) {
            $setting = $this->findSetting(self::SYSTEM_HOSPITAL_ID, $category, $name);
        }
        
        if (!$setting) {
            return $default;
        }
        
        return $this->castValue($setting->value, $setting->data_type, $setting->is_encrypted);
    }

    /**
     * Set a setting value
     *
     * @param int $hospitalId Hospital ID (0 for system)
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @param string $dataType Data type
     * @param bool $encrypt Whether to encrypt
     * @param int|null $userId User ID performing the action
     * @return bool
     */
    public function setSetting(int $hospitalId, string $key, $value, string $dataType = 'string', bool $encrypt = false, ?int $userId = null): bool
    {
        $parts = $this->parseKey($key);
        $category = $parts['category'];
        $name = $parts['name'];
        
        // Find existing or create new
        $setting = $this->findSetting($hospitalId, $category, $name);
        
        $data = array(
            'hospital_id' => $hospitalId,
            'category' => $category,
            'name' => $name,
            'value' => $this->prepareValue($value, $dataType, $encrypt),
            'data_type' => $dataType,
            'is_encrypted' => $encrypt,
            'is_active' => true
        );
        
        if ($userId) {
            $data['last_modified_by'] = $userId;
        }
        
        if ($setting) {
            $setting = $this->patchEntity($setting, $data);
        } else {
            $setting = $this->newEntity($data);
        }
        
        $result = (bool)$this->save($setting);
        
        if ($result) {
            Log::info('Setting updated', array(
                'hospital_id' => $hospitalId,
                'key' => $key,
                'user_id' => $userId
            ));
        }
        
        return $result;
    }

    /**
     * Get all settings for a hospital by category
     * Merges system defaults with hospital overrides
     *
     * @param int $hospitalId Hospital ID
     * @param string $category Category
     * @param bool $includeSystemDefaults Whether to include system defaults
     * @return array
     */
    public function getByCategory(int $hospitalId, string $category, bool $includeSystemDefaults = true): array
    {
        $result = array();
        
        // Get system defaults first
        if ($includeSystemDefaults && $hospitalId !== self::SYSTEM_HOSPITAL_ID) {
            $systemSettings = $this->fetchSettings(self::SYSTEM_HOSPITAL_ID, $category);
            foreach ($systemSettings as $setting) {
                $result[$setting->name] = $this->castValue(
                    $setting->value,
                    $setting->data_type,
                    $setting->is_encrypted
                );
            }
        }
        
        // Get hospital-specific settings (override system defaults)
        $hospitalSettings = $this->fetchSettings($hospitalId, $category);
        foreach ($hospitalSettings as $setting) {
            $result[$setting->name] = $this->castValue(
                $setting->value,
                $setting->data_type,
                $setting->is_encrypted
            );
        }
        
        return $result;
    }

    /**
     * Get grouped settings (organized by prefix)
     *
     * @param int $hospitalId Hospital ID
     * @param string $category Category
     * @param bool $includeSystemDefaults Whether to include system defaults
     * @return array
     */
    public function getGrouped(int $hospitalId, string $category, bool $includeSystemDefaults = true): array
    {
        $settings = $this->getByCategory($hospitalId, $category, $includeSystemDefaults);
        
        $grouped = array();
        foreach ($settings as $key => $value) {
            $parts = explode('.', $key);
            $current = &$grouped;
            
            foreach ($parts as $i => $part) {
                if ($i === count($parts) - 1) {
                    $current[$part] = $value;
                } else {
                    if (!isset($current[$part])) {
                        $current[$part] = array();
                    }
                    $current = &$current[$part];
                }
            }
        }
        
        return $grouped;
    }

    /**
     * Get all system settings (hospital_id = 0)
     *
     * @param string|null $category Optional category filter
     * @return array
     */
    public function getSystemSettings(?string $category = null): array
    {
        if ($category) {
            return $this->getByCategory(self::SYSTEM_HOSPITAL_ID, $category, false);
        }
        
        $settings = $this->find()
            ->where(array(
                'hospital_id' => self::SYSTEM_HOSPITAL_ID,
                'is_active' => true
            ))
            ->all();
        
        $result = array();
        foreach ($settings as $setting) {
            $key = $setting->category . '.' . $setting->name;
            $result[$key] = $this->castValue(
                $setting->value,
                $setting->data_type,
                $setting->is_encrypted
            );
        }
        
        return $result;
    }

    /**
     * Delete a setting
     *
     * @param int $hospitalId Hospital ID
     * @param string $key Setting key
     * @return bool
     */
    public function removeSetting(int $hospitalId, string $key): bool
    {
        $parts = $this->parseKey($key);
        $category = $parts['category'];
        $name = $parts['name'];
        
        return (bool)$this->deleteAll(array(
            'hospital_id' => $hospitalId,
            'category' => $category,
            'name' => $name
        ));
    }

    /**
     * Check if a setting exists
     *
     * @param int $hospitalId Hospital ID
     * @param string $key Setting key
     * @return bool
     */
    public function hasSetting(int $hospitalId, string $key): bool
    {
        $parts = $this->parseKey($key);
        $category = $parts['category'];
        $name = $parts['name'];
        
        return $this->exists(array(
            'hospital_id' => $hospitalId,
            'category' => $category,
            'name' => $name,
            'is_active' => true
        ));
    }

    /**
     * Get all categories for a hospital
     *
     * @param int $hospitalId Hospital ID
     * @return array
     */
    public function getCategories(int $hospitalId): array
    {
        $query = $this->find()
            ->select(array('category'))
            ->where(array(
                'hospital_id' => $hospitalId,
                'is_active' => true
            ))
            ->group('category')
            ->order(array('category' => 'ASC'));
        
        $categories = array();
        foreach ($query as $row) {
            $categories[] = $row->category;
        }
        
        return $categories;
    }

    // ========================================================================
    // Private Helper Methods
    // ========================================================================

    /**
     * Find a setting by hospital, category, and name
     *
     * @param int $hospitalId Hospital ID
     * @param string $category Category
     * @param string $name Name
     * @return \App\Model\Entity\Setting|null
     */
    private function findSetting(int $hospitalId, string $category, string $name)
    {
        return $this->find()
            ->where(array(
                'hospital_id' => $hospitalId,
                'category' => $category,
                'name' => $name,
                'is_active' => true
            ))
            ->first();
    }

    /**
     * Fetch settings by hospital and category
     *
     * @param int $hospitalId Hospital ID
     * @param string $category Category
     * @return \Cake\ORM\Query
     */
    private function fetchSettings(int $hospitalId, string $category)
    {
        return $this->find()
            ->where(array(
                'hospital_id' => $hospitalId,
                'category' => $category,
                'is_active' => true
            ))
            ->all();
    }

    /**
     * Parse setting key into category and name
     *
     * @param string $key Setting key
     * @return array
     */
    private function parseKey(string $key): array
    {
        $parts = explode('.', $key, 2);
        
        if (count($parts) === 2) {
            return array(
                'category' => $parts[0],
                'name' => $parts[1]
            );
        }
        
        return array(
            'category' => 'general',
            'name' => $key
        );
    }

    /**
     * Prepare value for storage
     *
     * IMPORTANT: The 'value' column is TEXT type, so we must manually JSON encode.
     * CakePHP will NOT automatically encode TEXT columns.
     *
     * @param mixed $value Value
     * @param string $dataType Data type
     * @param bool $encrypt Whether to encrypt
     * @return string
     */
    private function prepareValue($value, string $dataType, bool $encrypt): string
    {
        // Clean the value to ensure valid UTF-8 (but only for actual strings)
        if (is_string($value)) {
            // Remove any invalid UTF-8 characters
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }
        
        // Encode with JSON_UNESCAPED_UNICODE to handle UTF-8 properly
        try {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            // If JSON encoding fails, log error and use safe fallback
            Log::error('JSON encoding failed in prepareValue: ' . $e->getMessage(), array(
                'value_type' => gettype($value),
                'data_type' => $dataType
            ));
            // Fallback: convert to string and encode
            $encoded = json_encode((string)$value, JSON_UNESCAPED_UNICODE);
        }
        
        if ($encrypt) {
            // Try encryptionKey first, then fall back to salt
            $encryptionKey = Configure::read('Security.encryptionKey');
            if (!$encryptionKey) {
                $encryptionKey = Configure::read('Security.salt');
            }
            
            // Ensure we have a valid encryption key
            if (empty($encryptionKey) || $encryptionKey === '__SALT__') {
                throw new \RuntimeException(
                    'No valid encryption key configured. Please set Security.encryptionKey or Security.salt in config/app_local.php'
                );
            }
            
            try {
                // Encrypt returns binary data, so we must base64 encode it for TEXT column
                $encrypted = Security::encrypt($encoded, $encryptionKey);
                return base64_encode($encrypted);
            } catch (\Exception $e) {
                Log::error('Encryption failed in prepareValue: ' . $e->getMessage());
                throw new \RuntimeException('Failed to encrypt setting value: ' . $e->getMessage());
            }
        }
        
        return $encoded;
    }

    /**
     * Cast and decrypt a value
     *
     * IMPORTANT: The 'value' column is TEXT type, so values are JSON-encoded strings.
     * We must manually JSON decode them.
     *
     * @param mixed $value Stored value (JSON-encoded string from TEXT column)
     * @param string $dataType Data type
     * @param bool $isEncrypted Whether encrypted
     * @return mixed
     */
    private function castValue($value, string $dataType, bool $isEncrypted)
    {
        // Handle null values
        if ($value === null) {
            return null;
        }
        
        // If encrypted, decrypt first (only if value is string)
        if ($isEncrypted && is_string($value)) {
            // Try encryptionKey first, then fall back to salt
            $encryptionKey = Configure::read('Security.encryptionKey');
            if (!$encryptionKey) {
                $encryptionKey = Configure::read('Security.salt');
            }
            
            // Ensure we have a valid encryption key
            if (empty($encryptionKey) || $encryptionKey === '__SALT__') {
                throw new \RuntimeException(
                    'No valid encryption key configured. Please set Security.encryptionKey or Security.salt in config/app_local.php'
                );
            }
            
            try {
                // Base64 decode first (since we encoded it for TEXT column storage)
                $binaryData = base64_decode($value, true);
                if ($binaryData === false) {
                    Log::error('Failed to base64 decode encrypted setting value');
                    return null;
                }
                
                // Now decrypt the binary data
                $value = Security::decrypt($binaryData, $encryptionKey);
            } catch (\Exception $e) {
                Log::error('Failed to decrypt setting value: ' . $e->getMessage());
                return null;
            }
        }
        
        // If value is already the correct type (not a string), return it
        if (!is_string($value)) {
            return $value;
        }
        
        // Clean UTF-8 before decoding
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        
        // Try to decode JSON with error handling
        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            // If JSON decode failed, log warning and use the original value
            Log::warning('Failed to decode JSON setting value: ' . $e->getMessage());
            $decoded = $value;
        }
        
        // Cast to appropriate type
        switch ($dataType) {
            case 'boolean':
                if (is_bool($decoded)) {
                    return $decoded;
                }
                return filter_var($decoded, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int)$decoded;
            case 'float':
                return (float)$decoded;
            case 'array':
            case 'object':
                return is_array($decoded) ? $decoded : array();
            default:
                return $decoded;
        }
    }
}
