<?php

/**
 * Categories Repository
 * Manages custom AI Assistant categories
 */

namespace Leantime\Plugins\AIAssistant\Repositories;

defined('RESTRICTED') or exit('Restricted access');

use Leantime\Core\Db\Db;
use Illuminate\Database\ConnectionInterface;

class Categories
{
    private ConnectionInterface $db;
    
    public function __construct(Db $db)
    {
        $this->db = $db->getConnection();
    }
    
    /**
     * Install categories table if it doesn't exist
     */
    public function installIfNeeded(): bool
    {
        try {
            $sql = file_get_contents(__DIR__ . '/../Install/categories_migration.sql');
            $this->db->getPdo()->exec($sql);
            return true;
        } catch (\Exception $e) {
            error_log("AIAssistant Categories: Installation failed - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all categories
     * 
     * @return array
     */
    public function getAllCategories(): array
    {
        try {
            $sql = "SELECT * FROM zp_aiassistant_categories ORDER BY is_default DESC, name ASC";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("AIAssistant Categories: Failed to get categories - " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get category by name
     * 
     * @param string $name
     * @return array|null
     */
    public function getCategoryByName(string $name): ?array
    {
        try {
            $sql = "SELECT * FROM zp_aiassistant_categories WHERE name = :name LIMIT 1";
            $stmt = $this->db->getPdo()->prepare($sql);
            $stmt->execute(['name' => $name]);
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\Exception $e) {
            error_log("AIAssistant Categories: Failed to get category - " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new category
     * 
     * @param array $data
     * @return int|false Category ID or false on failure
     */
    public function createCategory(array $data): int|false
    {
        try {
            $sql = "INSERT INTO zp_aiassistant_categories (name, icon, color, keywords, is_default) 
                    VALUES (:name, :icon, :color, :keywords, :is_default)";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            $result = $stmt->execute([
                'name' => $data['name'] ?? '',
                'icon' => $data['icon'] ?? '📋',
                'color' => $data['color'] ?? '#6B7280',
                'keywords' => $data['keywords'] ?? '',
                'is_default' => $data['is_default'] ?? 0
            ]);
            
            return $result ? (int)$this->db->getPdo()->lastInsertId() : false;
        } catch (\Exception $e) {
            error_log("AIAssistant Categories: Failed to create category - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update category
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCategory(int $id, array $data): bool
    {
        try {
            $sql = "UPDATE zp_aiassistant_categories 
                    SET name = :name, icon = :icon, color = :color, keywords = :keywords 
                    WHERE id = :id";
            
            $stmt = $this->db->getPdo()->prepare($sql);
            return $stmt->execute([
                'id' => $id,
                'name' => $data['name'] ?? '',
                'icon' => $data['icon'] ?? '📋',
                'color' => $data['color'] ?? '#6B7280',
                'keywords' => $data['keywords'] ?? ''
            ]);
        } catch (\Exception $e) {
            error_log("AIAssistant Categories: Failed to update category - " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete category (only if not default)
     * 
     * @param int $id
     * @return bool
     */
    public function deleteCategory(int $id): bool
    {
        try {
            $sql = "DELETE FROM zp_aiassistant_categories WHERE id = :id AND is_default = 0";
            $stmt = $this->db->getPdo()->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (\Exception $e) {
            error_log("AIAssistant Categories: Failed to delete category - " . $e->getMessage());
            return false;
        }
    }
}
