<?php

namespace Leantime\Plugins\AIAssistant\Services;

use Leantime\Plugins\AIAssistant\Repositories\Categories as CategoriesRepository;

/**
 * Category Manager Service
 * 
 * Manages task categories with icons and colors (database-driven)
 */
class CategoryManager
{
    private CategoriesRepository $categoriesRepo;
    private array $categoriesCache = [];
    private bool $cacheLoaded = false;
    
    public function __construct(CategoriesRepository $categoriesRepo)
    {
        $this->categoriesRepo = $categoriesRepo;
    }
    
    /**
     * Load categories from database into cache
     */
    private function loadCategories(): void
    {
        if ($this->cacheLoaded) {
            return;
        }
        
        $dbCategories = $this->categoriesRepo->getAllCategories();
        
        // Transform to internal format
        foreach ($dbCategories as $cat) {
            $this->categoriesCache[$cat['name']] = [
                'id' => $cat['id'],
                'name' => ucfirst($cat['name']),
                'icon' => $cat['icon'],
                'color' => $cat['color'],
                'keywords' => explode(',', $cat['keywords'] ?? ''),
                'is_default' => $cat['is_default']
            ];
        }
        
        $this->cacheLoaded = true;
    }

    /**
     * Get all categories
     * 
     * @return array
     */
    public function getAllCategories(): array
    {
        $this->loadCategories();
        return $this->categoriesCache;
    }

    /**
     * Get category details by key
     * 
     * @param string $categoryKey
     * @return array|null
     */
    public function getCategory(string $categoryKey): ?array
    {
        $this->loadCategories();
        return $this->categoriesCache[$categoryKey] ?? null;
    }

    /**
     * Get category icon
     * 
     * @param string $categoryKey
     * @return string Emoji or FontAwesome icon
     */
    public function getCategoryIcon(string $categoryKey): string
    {
        $this->loadCategories();
        return $this->categoriesCache[$categoryKey]['icon'] ?? '📋';
    }

    /**
     * Get category color
     * 
     * @param string $categoryKey
     * @return string Hex color code
     */
    public function getCategoryColor(string $categoryKey): string
    {
        $this->loadCategories();
        return $this->categoriesCache[$categoryKey]['color'] ?? '#6B7280';
    }

    /**
     * Get category name (translated)
     * 
     * @param string $categoryKey
     * @return string
     */
    public function getCategoryName(string $categoryKey): string
    {
        $this->loadCategories();
        return $this->categoriesCache[$categoryKey]['name'] ?? ucfirst($categoryKey);
    }

    /**
     * Detect category from text (keyword matching)
     * Fallback if AI doesn't provide a category
     * 
     * @param string $text Text to analyze
     * @return string Category key
     */
    public function detectCategory(string $text): string
    {
        $this->loadCategories();
        $text = strtolower($text);
        
        // Score each category by keyword matches
        $scores = [];
        foreach ($this->categoriesCache as $key => $category) {
            $score = 0;
            foreach ($category['keywords'] as $keyword) {
                $keyword = trim(strtolower($keyword));
                if (!empty($keyword) && strpos($text, $keyword) !== false) {
                    $score++;
                }
            }
            $scores[$key] = $score;
        }
        
        // Return category with highest score, or 'anfrage' as default
        arsort($scores);
        $topCategory = array_key_first($scores);
        
        return $scores[$topCategory] > 0 ? $topCategory : 'anfrage';
    }

    /**
     * Validate if category exists
     * 
     * @param string $categoryKey
     * @return bool
     */
    public function isValidCategory(string $categoryKey): bool
    {
        $this->loadCategories();
        return isset($this->categoriesCache[$categoryKey]);
    }
}

