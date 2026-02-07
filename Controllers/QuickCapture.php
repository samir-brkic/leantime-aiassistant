<?php

namespace Leantime\Plugins\AIAssistant\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\UI\Template;
use Leantime\Core\Language;
use Leantime\Plugins\AIAssistant\Services\AIAssistant;
use Leantime\Plugins\AIAssistant\Services\TaskGenerator;
use Leantime\Plugins\AIAssistant\Repositories\Settings as SettingsRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Quick Capture Controller
 * 
 * Main interface for converting notes to tasks using AI
 */
class QuickCapture extends Controller
{
    private AIAssistant $aiAssistant;
    private TaskGenerator $taskGenerator;
    private SettingsRepository $settingsRepo;
    private ProjectService $projectService;

    /**
     * Constructor with Dependency Injection
     */
    public function __construct(
        IncomingRequest $incomingRequest,
        Template $tpl,
        Language $language,
        AIAssistant $aiAssistant,
        TaskGenerator $taskGenerator,
        SettingsRepository $settingsRepo,
        ProjectService $projectService
    ) {
        $this->aiAssistant = $aiAssistant;
        $this->taskGenerator = $taskGenerator;
        $this->settingsRepo = $settingsRepo;
        $this->projectService = $projectService;
        
        parent::__construct($incomingRequest, $tpl, $language);
    }

    /**
     * Initialize
     */
    public function init(): void
    {
        // Ensure user is logged in
        if (!session("userdata.id")) {
            $this->tpl->redirect(BASE_URL . '/auth/login');
        }
        
        // Load language files
        $langFile = __DIR__ . '/../Language/' . $this->language->getCurrentLanguage() . '.ini';
        if (file_exists($langFile)) {
            $langArray = parse_ini_file($langFile, false);
            $this->language->ini_array = array_merge($this->language->ini_array, $langArray);
        }
    }

    /**
     * GET - Display Quick Capture page
     * 
     * @return Response
     * @throws \Exception
     */
    public function get(): Response
    {
        // Check if AI provider is configured
        $settings = $this->settingsRepo->getAllSettings();
        $isConfigured = $this->isAIConfigured($settings);
        
        if (!$isConfigured) {
            $this->tpl->setNotification(
                $this->language->__('aiassistant.messages.error.not_configured'),
                'error'
            );
        }
        
        // Get user's projects
        $userId = session("userdata.id");
        $projects = $this->projectService->getProjectsAssignedToUser($userId);
        
        // Pass data to template
        $this->tpl->assign('projects', $projects);
        $this->tpl->assign('isConfigured', $isConfigured);
        
        return $this->tpl->display("aiAssistant.quickcapture");
    }

    /**
     * AJAX - Analyze text with AI
     * 
     * @return JsonResponse
     */
    public function analyze(): JsonResponse
    {
        try {
            $text = $_POST['text'] ?? '';
            
            if (empty($text)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Text is required'
                ], 400);
            }
            
            // Check if AI is configured
            $settings = $this->settingsRepo->getAllSettings();
            if (!$this->isAIConfigured($settings)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $this->language->__('aiassistant.messages.error.not_configured')
                ]);
            }
            
            // Call AI to analyze text
            $aiResponse = $this->aiAssistant->analyzeText($text);
            
            if (!$aiResponse) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $this->language->__('aiassistant.messages.error.ai_failed')
                ]);
            }
            
            // Generate preview
            $preview = $this->taskGenerator->getTaskPreview($aiResponse);
            
            if (!$preview) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Invalid AI response format'
                ]);
            }
            
            return new JsonResponse([
                'success' => true,
                'preview' => $preview,
                'rawResponse' => $aiResponse
            ]);
            
        } catch (\Exception $e) {
            error_log("AIAssistant Analyze Error: " . $e->getMessage());
            return new JsonResponse([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX - Create tasks from AI response
     * 
     * @return JsonResponse
     */
    public function createTasks(): JsonResponse
    {
        try {
            $aiResponse = $_POST['aiResponse'] ?? '';
            $projectId = (int)($_POST['projectId'] ?? 0);
            
            if (empty($aiResponse) || $projectId <= 0) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'AI response and project ID are required'
                ], 400);
            }
            
            $userId = session("userdata.id");
            
            // Create tasks
            $result = $this->taskGenerator->createTaskFromAI($aiResponse, $projectId, $userId);
            
            if (!$result['success']) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
            
            return new JsonResponse([
                'success' => true,
                'message' => $this->language->__('aiassistant.messages.success.tasks_created'),
                'mainTaskId' => $result['mainTaskId'],
                'subtaskIds' => $result['subtaskIds']
            ]);
            
        } catch (\Exception $e) {
            error_log("AIAssistant Create Tasks Error: " . $e->getMessage());
            return new JsonResponse([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if AI provider is configured
     * 
     * @param array $settings
     * @return bool
     */
    private function isAIConfigured(array $settings): bool
    {
        $provider = $settings['provider'] ?? '';
        
        if ($provider === 'ollama') {
            return !empty($settings['ollama_url']) && !empty($settings['ollama_model']);
        } elseif ($provider === 'openai') {
            return !empty($settings['openai_api_key']);
        }
        
        return false;
    }
}
