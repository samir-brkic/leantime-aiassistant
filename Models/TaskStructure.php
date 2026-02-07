<?php

namespace Leantime\Plugins\AIAssistant\Models;

/**
 * Task Structure Model
 * 
 * Represents the structured task data extracted by AI
 */
class TaskStructure
{
    public function __construct(
        public string $title = '',
        public string $description = '',
        public string $category = '',
        public int $priority = 3, // Default: Medium
        public ?string $deadline = null,
        public array $subtasks = [],
        public array $tags = [],
        public int $projectId = 0
    ) {}

    /**
     * Create from AI response (JSON)
     * 
     * @param string $jsonResponse JSON string from AI
     * @return self|null
     */
    public static function fromAIResponse(string $jsonResponse): ?self
    {
        try {
            error_log("AIAssistant: Parsing AI response JSON...");
            $data = json_decode($jsonResponse, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("AIAssistant: Invalid JSON response - " . json_last_error_msg());
                return null;
            }
            
            error_log("AIAssistant: JSON decoded successfully. Keys: " . implode(', ', array_keys($data)));
            error_log("AIAssistant: Subtasks in JSON: " . json_encode($data['subtasks'] ?? []));
            
            return new self(
                title: $data['title'] ?? '',
                description: $data['description'] ?? '',
                category: $data['category'] ?? '',
                priority: self::mapPriority($data['priority'] ?? 'normal'),
                deadline: self::parseDeadline($data['deadline'] ?? null),
                subtasks: $data['subtasks'] ?? [],
                tags: $data['tags'] ?? []
            );
            
        } catch (\Exception $e) {
            error_log("AIAssistant: Error parsing AI response - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Map priority string to Leantime priority integer
     * 
     * @param string $priority Priority name (critical, high, normal, low)
     * @return int Priority integer (1-5)
     */
    private static function mapPriority(string $priority): int
    {
        return match(strtolower($priority)) {
            'critical', 'dringend', 'urgent' => 1,
            'high', 'hoch' => 2,
            'medium', 'normal', 'mittel' => 3,
            'low', 'niedrig' => 4,
            'lowest', 'sehr niedrig' => 5,
            default => 3
        };
    }

    /**
     * Parse deadline string to date
     * 
     * @param string|null $deadline Deadline string (e.g., "morgen", "in 3 Tagen")
     * @return string|null Date in Y-m-d format or null
     */
    private static function parseDeadline(?string $deadline): ?string
    {
        if (empty($deadline)) {
            return null;
        }

        $deadline = strtolower(trim($deadline));
        $now = new \DateTime();

        // German patterns
        if (preg_match('/morgen|tomorrow/', $deadline)) {
            $now->modify('+1 day');
            return $now->format('Y-m-d');
        }

        if (preg_match('/in (\d+) tag(en)?/', $deadline, $matches)) {
            $days = (int)$matches[1];
            $now->modify("+{$days} days");
            return $now->format('Y-m-d');
        }

        if (preg_match('/n[äa]chste woche|next week/', $deadline)) {
            $now->modify('+1 week');
            return $now->format('Y-m-d');
        }

        // Try to parse as date
        try {
            $date = new \DateTime($deadline);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convert to array for task creation
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'priority' => $this->priority,
            'deadline' => $this->deadline,
            'subtasks' => $this->subtasks,
            'tags' => $this->tags,
            'projectId' => $this->projectId
        ];
    }

    /**
     * Validate task structure
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return !empty($this->title) && $this->projectId > 0;
    }
}
