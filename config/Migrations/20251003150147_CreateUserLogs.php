<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateUserLogs extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('user_logs');
        
        // User reference (nullable for anonymous events)
        $table->addColumn('user_id', 'integer', [
            'default' => null,
            'null' => true,
            'signed' => false,
        ]);
        
        // Event type (login, logout, register, etc.)
        $table->addColumn('event_type', 'string', [
            'limit' => 50,
            'null' => false,
        ]);
        
        // Event description
        $table->addColumn('description', 'string', [
            'limit' => 255,
            'null' => true,
        ]);
        
        // Additional data in JSON format
        $table->addColumn('event_data', 'text', [
            'null' => true,
        ]);
        
        // User's IP address
        $table->addColumn('ip_address', 'string', [
            'limit' => 45, // IPv6 compatible
            'null' => true,
        ]);
        
        // User agent string
        $table->addColumn('user_agent', 'string', [
            'limit' => 500,
            'null' => true,
        ]);
        
        // Hospital context
        $table->addColumn('hospital_id', 'integer', [
            'default' => null,
            'null' => true,
            'signed' => false,
        ]);
        
        // Role context at time of event
        $table->addColumn('role_type', 'string', [
            'limit' => 50,
            'null' => true,
        ]);
        
        // Event status (success, failed, etc.)
        $table->addColumn('status', 'string', [
            'limit' => 20,
            'default' => 'success',
            'null' => false,
        ]);
        
        // Timestamps
        $table->addColumn('created', 'datetime', [
            'default' => null,
            'null' => false,
        ]);
        
        // Indexes for better performance
        $table->addIndex(['user_id']);
        $table->addIndex(['event_type']);
        $table->addIndex(['created']);
        $table->addIndex(['hospital_id']);
        $table->addIndex(['ip_address']);
        $table->addIndex(['status']);
        
        // Foreign key constraints
        $table->addForeignKey('user_id', 'users', 'id', [
            'delete' => 'SET_NULL',
            'update' => 'CASCADE'
        ]);
        
        $table->addForeignKey('hospital_id', 'hospitals', 'id', [
            'delete' => 'SET_NULL', 
            'update' => 'CASCADE'
        ]);
        
        $table->create();
    }
}
