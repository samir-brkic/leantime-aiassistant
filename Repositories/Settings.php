<?php

namespace Leantime\Plugins\AIAssistant\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db;

/**
 * Settings Repository
 * 
 * Manages database operations for AI Assistant settings
 */
class Settings
{
    private ConnectionInterface $db;
    private string $table = 'aiassistant_settings';

    /**
     * Constructor with Dependency Injection
     */
    public function __construct(Db $db)
    {
        $this->db = $db->getConnection();
    }

    /**
     * Get a single setting by key
     * 
     * @param string $key The setting key
     * @return mixed The setting value or null if not found
     */
    public function getSetting(string $key): mixed
    {
        $sql = "SELECT value FROM zp_" . $this->table . " WHERE setting_key = :key LIMIT 1";
        $stmn = $this->db->getPdo()->prepare($sql);
        $stmn->bindValue(':key', $key, \PDO::PARAM_STR);
        $stmn->execute();
        
        $result = $stmn->fetch(\PDO::FETCH_ASSOC);
        
        return $result ? $result['value'] : null;
    }

    /**
     * Get all settings as associative array
     * 
     * @return array Associative array of all settings [key => value]
     */
    public function getAllSettings(): array
    {
        $sql = "SELECT setting_key, value FROM zp_" . $this->table;
        $stmn = $this->db->getPdo()->prepare($sql);
        $stmn->execute();
        
        $results = $stmn->fetchAll(\PDO::FETCH_ASSOC);
        $settings = [];
        
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['value'];
        }
        
        return $settings;
    }

    /**
     * Save a single setting (insert or update)
     * 
     * @param string $key The setting key
     * @param mixed $value The setting value
     * @return bool Success status
     */
    public function saveSetting(string $key, mixed $value): bool
    {
        $sql = "INSERT INTO zp_" . $this->table . " (setting_key, value) 
                VALUES (:key, :value) 
                ON DUPLICATE KEY UPDATE value = :value, updated_at = CURRENT_TIMESTAMP";
        
        $stmn = $this->db->getPdo()->prepare($sql);
        $stmn->bindValue(':key', $key, \PDO::PARAM_STR);
        $stmn->bindValue(':value', $value, \PDO::PARAM_STR);
        
        return $stmn->execute();
    }

    /**
     * Save multiple settings at once
     * 
     * @param array $settings Associative array of settings [key => value]
     * @return bool Success status
     */
    public function saveSettings(array $settings): bool
    {
        try {
            $this->db->getPdo()->beginTransaction();
            
            foreach ($settings as $key => $value) {
                $this->saveSetting($key, $value);
            }
            
            $this->db->getPdo()->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->getPdo()->rollBack();
            error_log("AIAssistant Settings Save Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a setting
     * 
     * @param string $key The setting key to delete
     * @return bool Success status
     */
    public function deleteSetting(string $key): bool
    {
        $sql = "DELETE FROM zp_" . $this->table . " WHERE setting_key = :key";
        $stmn = $this->db->getPdo()->prepare($sql);
        $stmn->bindValue(':key', $key, \PDO::PARAM_STR);
        
        return $stmn->execute();
    }

    /**
     * Check if settings table exists and install if needed
     * 
     * @return bool True if table exists or was created successfully
     */
    public function installIfNeeded(): bool
    {
        try {
            // Check if table exists
            $sql = "SHOW TABLES LIKE 'zp_" . $this->table . "'";
            $stmn = $this->db->getPdo()->prepare($sql);
            $stmn->execute();
            
            if ($stmn->rowCount() > 0) {
                return true; // Table already exists
            }
            
            // Table doesn't exist, run migration
            $migrationFile = __DIR__ . '/../Install/migration.sql';
            if (!file_exists($migrationFile)) {
                error_log("AIAssistant migration.sql not found");
                return false;
            }
            
            $sql = file_get_contents($migrationFile);
            $this->db->getPdo()->exec($sql);
            
            error_log("AIAssistant: Settings table created successfully");
            return true;
            
        } catch (\Exception $e) {
            error_log("AIAssistant Install Error: " . $e->getMessage());
            return false;
        }
    }
}
