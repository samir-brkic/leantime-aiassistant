<?php

namespace Leantime\Plugins\AIAssistant\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\UI\Template;
use Leantime\Core\Language;
use Leantime\Plugins\AIAssistant\Services\AIAssistant;
use Leantime\Plugins\AIAssistant\Repositories\Settings as SettingsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Settings Controller
 * 
 * Manages AI Assistant settings page and API endpoints
 */
class Settings extends Controller
{
    private AIAssistant $aiAssistant;
    private SettingsRepository $settingsRepo;

    /**
     * Constructor with Dependency Injection
     */
    public function __construct(
        IncomingRequest $incomingRequest,
        Template $tpl,
        Language $language,
        AIAssistant $aiAssistant,
        SettingsRepository $settingsRepo
    ) {
        $this->aiAssistant = $aiAssistant;
        $this->settingsRepo = $settingsRepo;
        
        parent::__construct($incomingRequest, $tpl, $language);
    }

    /**
     * Initialize - ensure settings table exists
     */
    public function init(): void
    {
        $this->settingsRepo->installIfNeeded();
        
        // Load language files
        $langFile = __DIR__ . '/../Language/' . $this->language->getCurrentLanguage() . '.ini';
        if (file_exists($langFile)) {
            $langArray = parse_ini_file($langFile, false);
            $this->language->ini_array = array_merge($this->language->ini_array, $langArray);
        }
    }

    /**
     * GET - Display settings page
     * 
     * @return Response
     * @throws \Exception
     */
    public function get(): Response
    {
        // Load current settings
        $settings = $this->settingsRepo->getAllSettings();
        
        // Get default prompt for reference
        $defaultPrompt = $this->aiAssistant->getDefaultSystemPrompt();
        
        // Pass to template
        $this->tpl->assign('settings', $settings);
        $this->tpl->assign('provider', $settings['provider'] ?? 'ollama');
        $this->tpl->assign('ollama_url', $settings['ollama_url'] ?? 'http://192.168.200.40:11434');
        $this->tpl->assign('ollama_model', $settings['ollama_model'] ?? '');
        $this->tpl->assign('openai_api_key', $settings['openai_api_key'] ?? '');
        $this->tpl->assign('openai_base_url', $settings['openai_base_url'] ?? 'https://api.openai.com/v1');
        $this->tpl->assign('openai_model', $settings['openai_model'] ?? 'gpt-4');
        $this->tpl->assign('timeout', $settings['timeout'] ?? 30);
        $this->tpl->assign('system_prompt', $settings['system_prompt'] ?? $defaultPrompt);
        $this->tpl->assign('default_prompt', $defaultPrompt);
        
        return $this->tpl->display("aiAssistant.settings");
    }

    /**
     * POST - Save settings or handle AJAX
     * 
     * @param array $params
     * @return Response|JsonResponse
     */
    public function post(array $params): Response|JsonResponse
    {
        // Handle AJAX requests
        if (isset($params['action'])) {
            switch ($params['action']) {
                case 'loadModels':
                    return $this->loadModels($params);
                case 'loadOpenAIModels':
                    return $this->loadOpenAIModels($params);
                case 'testConnection':
                    return $this->testConnection($params);
                case 'resetPrompt':
                    return $this->resetPrompt();
            }
        }
        
        try {
            // Validate and sanitize input
            $settings = [
                'provider' => $params['provider'] ?? 'ollama',
                'ollama_url' => $params['ollama_url'] ?? '',
                'ollama_model' => $params['ollama_model'] ?? '',
                'openai_api_key' => $params['openai_api_key'] ?? '',
                'openai_base_url' => $params['openai_base_url'] ?? 'https://api.openai.com/v1',
                'openai_model' => $params['openai_model'] ?? 'gpt-4',
                'timeout' => (int)($params['timeout'] ?? 30),
                'system_prompt' => $params['system_prompt'] ?? ''
            ];
            
            // Save settings
            $success = $this->settingsRepo->saveSettings($settings);
            
            if ($success) {
                $this->tpl->setNotification(
                    $this->language->__('aiassistant.messages.success.saved'),
                    'success'
                );
            } else {
                $this->tpl->setNotification(
                    'Error saving settings',
                    'error'
                );
            }
            
        } catch (\Exception $e) {
            error_log("AIAssistant Settings Save Error: " . $e->getMessage());
            $this->tpl->setNotification(
                'Error: ' . $e->getMessage(),
                'error'
            );
        }
        
        return $this->get();
    }

    /**
     * AJAX - Load Ollama models
     * 
     * @return JsonResponse
     */
    private function loadModels(array $params): JsonResponse
    {
        $url = $params['url'] ?? '';
        
        if (empty($url)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'URL required'
            ], 400);
        }
        
        $models = $this->aiAssistant->getOllamaModels($url);
        
        if (empty($models)) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->language->__('aiassistant.messages.error.no_models')
            ]);
        }
        
        return new JsonResponse([
            'success' => true,
            'models' => $models
        ]);
    }

    /**
     * AJAX - Test provider connection
     * 
     * @return JsonResponse
     */
    private function testConnection(array $params): JsonResponse
    {
        $provider = $params['provider'] ?? '';
        
        if ($provider === 'ollama') {
            $url = $params['url'] ?? '';
            $model = $params['model'] ?? '';
            
            if (empty($url) || empty($model)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'URL and model required'
                ], 400);
            }
            
            $success = $this->aiAssistant->testOllamaConnection($url, $model);
            
        } else {
            $apiKey = $params['api_key'] ?? '';
            $baseUrl = $params['base_url'] ?? 'https://api.openai.com/v1';
            
            if (empty($apiKey)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'API key required'
                ], 400);
            }
            
            $success = $this->aiAssistant->testOpenAIConnection($apiKey, $baseUrl);
        }
        
        $message = $success 
            ? $this->language->__('aiassistant.messages.success.connected')
            : $this->language->__('aiassistant.messages.error.connection');
        
        return new JsonResponse([
            'success' => $success,
            'message' => $message
        ]);
    }
    
    /**
     * AJAX - Reset system prompt to default
     */
    private function resetPrompt(): JsonResponse
    {
        $defaultPrompt = $this->aiAssistant->getDefaultSystemPrompt();
        
        return new JsonResponse([
            'success' => true,
            'prompt' => $defaultPrompt
        ]);
    }

    /**
     * AJAX - Load OpenAI models
     */
    private function loadOpenAIModels(array $params): JsonResponse
    {
        $apiKey = $params['api_key'] ?? '';
        $baseUrl = $params['base_url'] ?? 'https://api.openai.com/v1';
        
        if (empty($apiKey)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'API key required'
            ], 400);
        }
        
        $models = $this->aiAssistant->getOpenAIModels($apiKey, $baseUrl);
        
        if (empty($models)) {
            return new JsonResponse([
                'success' => false,
                'message' => $this->language->__('aiassistant.messages.error.no_models')
            ]);
        }
        
        return new JsonResponse([
            'success' => true,
            'models' => $models
        ]);
    }
}
