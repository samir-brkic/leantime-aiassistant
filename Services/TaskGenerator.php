<?php

namespace Leantime\Plugins\AIAssistant\Services;

use Leantime\Plugins\AIAssistant\Models\TaskStructure;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Core\Language;

/**
 * Task Generator Service
 * 
 * Creates Leantime tickets from AI-generated task structures
 */
class TaskGenerator
{
    private TicketService $ticketService;
    private TicketRepository $ticketRepository;
    private CategoryManager $categoryManager;
    private Language $language;

    public function __construct(
        TicketService $ticketService,
        TicketRepository $ticketRepository,
        CategoryManager $categoryManager,
        Language $language
    ) {
        $this->ticketService = $ticketService;
        $this->ticketRepository = $ticketRepository;
        $this->categoryManager = $categoryManager;
        $this->language = $language;
    }

    /**
     * Create task from AI response
     * 
     * @param string $aiResponse JSON response from AI
     * @param int $projectId Project ID
     * @param int $userId User ID creating the task
     * @return array Result with success status and task ID(s)
     */
    public function createTaskFromAI(string $aiResponse, int $projectId, int $userId): array
    {
        try {
            error_log("AIAssistant: Starting task creation - ProjectID: $projectId, UserID: $userId");
            error_log("AIAssistant: AI Response: " . substr($aiResponse, 0, 200));
            
            // Parse AI response to TaskStructure
            $taskStructure = TaskStructure::fromAIResponse($aiResponse);
            
            if (!$taskStructure) {
                error_log("AIAssistant: Failed to parse AI response to TaskStructure");
                return [
                    'success' => false,
                    'message' => 'Invalid AI response format'
                ];
            }
            
            error_log("AIAssistant: TaskStructure created - Title: " . $taskStructure->title);
            
            $taskStructure->projectId = $projectId;
            
            if (!$taskStructure->isValid()) {
                error_log("AIAssistant: TaskStructure validation failed");
                return [
                    'success' => false,
                    'message' => 'Invalid task structure'
                ];
            }
            
            error_log("AIAssistant: TaskStructure valid, creating main task...");
            
            // Create main task
            $mainTaskId = $this->createMainTask($taskStructure, $userId);
            
            error_log("AIAssistant: createMainTask returned: " . var_export($mainTaskId, true));
            
            if (!$mainTaskId) {
                return [
                    'success' => false,
                    'message' => 'Failed to create main task'
                ];
            }
            
            // Create subtasks
            $subtaskIds = [];
            if (!empty($taskStructure->subtasks)) {
                error_log("AIAssistant: Found " . count($taskStructure->subtasks) . " subtasks to create");
                $subtaskIds = $this->createSubtasks($mainTaskId, $taskStructure->subtasks, $projectId, $userId);
                error_log("AIAssistant: Subtask creation completed, created " . count($subtaskIds) . " subtasks");
            } else {
                error_log("AIAssistant: No subtasks found in TaskStructure");
            }
            
            return [
                'success' => true,
                'mainTaskId' => $mainTaskId,
                'subtaskIds' => $subtaskIds,
                'message' => 'Tasks created successfully'
            ];
            
        } catch (\Exception $e) {
            error_log("AIAssistant: Task creation failed - " . $e->getMessage());
            error_log("AIAssistant: Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Error creating tasks: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create main task
     * 
     * @param TaskStructure $taskStructure
     * @param int $userId
     * @return int|false Task ID or false on failure
     */
    private function createMainTask(TaskStructure $taskStructure, int $userId): int|false
    {
        $values = [
            'headline' => $taskStructure->title,
            'description' => $this->formatDescription($taskStructure),
            'projectId' => $taskStructure->projectId,
            'editorId' => $userId,
            'userId' => $userId,
            'priority' => $taskStructure->priority,
            'type' => 'task',
            'status' => 3, // Open/To Do status (adjust based on Leantime config)
        ];
        
        // Add deadline if provided
        if ($taskStructure->deadline) {
            $values['dateToFinish'] = $taskStructure->deadline;
        }
        
        // Add tags
        if (!empty($taskStructure->tags)) {
            $values['tags'] = implode(',', $taskStructure->tags);
        }
        
        error_log("AIAssistant: Calling quickAddTicket with values: " . json_encode($values));
        
        $result = $this->ticketService->quickAddTicket($values);
        
        error_log("AIAssistant: quickAddTicket result type: " . gettype($result) . ", value: " . var_export($result, true));
        
        // quickAddTicket has buggy return type declaration (array|bool) but actually returns int on success
        // Check for both int and boolean true as success
        if (is_int($result) && $result > 0) {
            error_log("AIAssistant: Task created successfully with ID: $result");
            return $result;
        }
        
        if ($result === true) {
            error_log("AIAssistant: Task created but no ID returned (Leantime bug?), checking last insert ID");
            // Try to get the last inserted ticket ID from repository
            // This is a workaround for the buggy return type
            return $this->getLastCreatedTicketId($taskStructure->projectId, $taskStructure->title);
        }
        
        if (is_array($result)) {
            error_log("AIAssistant: Failed to create task - " . ($result['message'] ?? 'Unknown error'));
        } else {
            error_log("AIAssistant: Failed to create task - quickAddTicket returned: " . var_export($result, true));
        }
        
        return false;
    }

    /**
     * Create subtasks
     * 
     * @param int $parentTaskId
     * @param array $subtasks
     * @param int $projectId
     * @param int $userId
     * @return array Subtask IDs
     */
    private function createSubtasks(int $parentTaskId, array $subtasks, int $projectId, int $userId): array
    {
        $subtaskIds = [];
        
        error_log("AIAssistant: Creating " . count($subtasks) . " subtasks for parent task ID: $parentTaskId");
        
        foreach ($subtasks as $index => $subtaskText) {
            error_log("AIAssistant: Creating subtask #" . ($index + 1) . ": $subtaskText");
            
            $values = [
                'headline' => $subtaskText,
                'description' => '',
                'projectId' => $projectId,
                'editorId' => $userId,
                'userId' => $userId,
                'type' => 'subtask',
                'dependingTicketId' => $parentTaskId,
                'priority' => 3, // Medium priority for subtasks
                'status' => 3 // Open
            ];
            
            error_log("AIAssistant: Subtask values: " . json_encode($values));
            
            $subtaskId = $this->ticketService->quickAddTicket($values);
            
            error_log("AIAssistant: Subtask creation result: " . var_export($subtaskId, true));
            
            // Same bug as main task: quickAddTicket returns true instead of ID
            if (is_int($subtaskId) && $subtaskId > 0) {
                $subtaskIds[] = $subtaskId;
                error_log("AIAssistant: Subtask created successfully with ID: $subtaskId");
            } elseif ($subtaskId === true) {
                // Subtask created but no ID returned - that's OK, we can't track it but it exists
                error_log("AIAssistant: Subtask #" . ($index + 1) . " created (no ID returned due to Leantime bug)");
                // We'll count it as success even though we don't have the ID
                $subtaskIds[] = -1; // Placeholder ID
            } else {
                error_log("AIAssistant: Failed to create subtask #" . ($index + 1));
            }
        }
        
        error_log("AIAssistant: Created " . count($subtaskIds) . " subtasks total");
        
        return $subtaskIds;
    }

    /**
     * Format task description with category and metadata
     * 
     * @param TaskStructure $taskStructure
     * @return string Formatted description
     */
    private function formatDescription(TaskStructure $taskStructure): string
    {
        $description = $taskStructure->description;
        
        // Add category badge
        if ($taskStructure->category) {
            $categoryName = $this->categoryManager->getCategoryName($taskStructure->category);
            $categoryIcon = $this->categoryManager->getCategoryIcon($taskStructure->category);
            
            $description = "**📁 Kategorie:** {$categoryName}\n\n" . $description;
        }
        
        // Add AI-generated note
        $description .= "\n\n---\n*Automatisch erstellt via AI Assistant*";
        
        return $description;
    }

    /**
     * Get task creation preview (without actually creating tasks)
     * Used for preview in Quick Capture UI
     * 
     * @param string $aiResponse JSON response from AI
     * @return array|null Preview data
     */
    public function getTaskPreview(string $aiResponse): ?array
    {
        $taskStructure = TaskStructure::fromAIResponse($aiResponse);
        
        if (!$taskStructure) {
            return null;
        }
        
        return [
            'title' => $taskStructure->title,
            'description' => $taskStructure->description,
            'category' => $taskStructure->category,
            'categoryName' => $this->categoryManager->getCategoryName($taskStructure->category),
            'categoryIcon' => $this->categoryManager->getCategoryIcon($taskStructure->category),
            'categoryColor' => $this->categoryManager->getCategoryColor($taskStructure->category),
            'priority' => $taskStructure->priority,
            'priorityLabel' => $this->getPriorityLabel($taskStructure->priority),
            'deadline' => $taskStructure->deadline,
            'subtasks' => $taskStructure->subtasks,
            'tags' => $taskStructure->tags
        ];
    }

    /**
     * Get priority label
     * 
     * @param int $priority
     * @return string
     */
    private function getPriorityLabel(int $priority): string
    {
        return match($priority) {
            1 => 'Critical',
            2 => 'High',
            3 => 'Medium',
            4 => 'Low',
            5 => 'Lowest',
            default => 'Medium'
        };
    }

    /**
     * Get last created ticket ID (workaround for quickAddTicket return bug)
     * 
     * @param int $projectId
     * @param string $headline
     * @return int|false
     */
    private function getLastCreatedTicketId(int $projectId, string $headline): int|false
    {
        try {
            // Get all tickets from project
            $tickets = $this->ticketRepository->getAllByProjectId($projectId);
            
            if (empty($tickets)) {
                error_log("AIAssistant: No tickets found in project $projectId");
                return false;
            }
            
            // Find ticket with matching headline (most recent)
            // Sort by ID descending to get newest first
            usort($tickets, function($a, $b) {
                return $b->id - $a->id;
            });
            
            foreach ($tickets as $ticket) {
                if ($ticket->headline === $headline) {
                    error_log("AIAssistant: Found last created ticket ID: " . $ticket->id);
                    return (int)$ticket->id;
                }
            }
            
            error_log("AIAssistant: Could not find ticket with headline: $headline");
            return false;
            
        } catch (\Exception $e) {
            error_log("AIAssistant: Error getting last ticket ID - " . $e->getMessage());
            return false;
        }
    }
}
