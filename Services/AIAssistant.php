<?php

namespace Leantime\Plugins\AIAssistant\Services;

use Leantime\Plugins\AIAssistant\Repositories\Settings as SettingsRepository;
use Leantime\Plugins\AIAssistant\Models\AIRequest;

/**
 * AI Assistant Service
 * 
 * Handles communication with Ollama and OpenAI APIs
 */
class AIAssistant
{
    private SettingsRepository $settingsRepo;

    public function __construct(SettingsRepository $settingsRepo)
    {
        $this->settingsRepo = $settingsRepo;
    }

    /**
     * Get available Ollama models
     * 
     * @param string $url Ollama base URL
     * @return array List of available models
     */
    public function getOllamaModels(string $url): array
    {
        try {
            $endpoint = rtrim($url, '/') . '/api/tags';
            
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json']
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("AIAssistant: Ollama API returned HTTP $httpCode");
                return [];
            }
            
            $data = json_decode($response, true);
            
            if (!isset($data['models'])) {
                return [];
            }
            
            // Extract model names
            $models = [];
            foreach ($data['models'] as $model) {
                $models[] = $model['name'] ?? $model['model'] ?? 'unknown';
            }
            
            return $models;
            
        } catch (\Exception $e) {
            error_log("AIAssistant: Error fetching Ollama models - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Test Ollama connection
     * 
     * @param string $url Ollama base URL
     * @param string $model Model name
     * @return bool True if connection successful
     */
    public function testOllamaConnection(string $url, string $model): bool
    {
        try {
            $endpoint = rtrim($url, '/') . '/api/generate';
            
            $payload = json_encode([
                'model' => $model,
                'prompt' => 'Test',
                'stream' => false
            ]);
            
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_TIMEOUT => 60, // Increased for large models
                CURLOPT_HTTPHEADER => ['Content-Type: application/json']
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode === 200;
            
        } catch (\Exception $e) {
            error_log("AIAssistant: Ollama connection test failed - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Test OpenAI connection
     * 
     * @param string $apiKey OpenAI API key
     * @param string $baseUrl OpenAI base URL
     * @return bool True if connection successful
     */
    public function testOpenAIConnection(string $apiKey, string $baseUrl): bool
    {
        try {
            $endpoint = rtrim($baseUrl, '/') . '/models';
            
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            return $httpCode === 200;
            
        } catch (\Exception $e) {
            error_log("AIAssistant: OpenAI connection test failed - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Analyze text using configured AI provider
     * 
     * @param string $text Text to analyze
     * @return string|null JSON response from AI or null on failure
     */
    public function analyzeText(string $text): ?string
    {
        $settings = $this->settingsRepo->getAllSettings();
        $provider = $settings['provider'] ?? 'ollama';
        
        $systemPrompt = $this->getSystemPrompt();
        
        if ($provider === 'ollama') {
            return $this->analyzeWithOllama($text, $systemPrompt, $settings);
        } else {
            return $this->analyzeWithOpenAI($text, $systemPrompt, $settings);
        }
    }

    /**
     * Analyze text with Ollama
     * 
     * @param string $text
     * @param string $systemPrompt
     * @param array $settings
     * @return string|null
     */
    private function analyzeWithOllama(string $text, string $systemPrompt, array $settings): ?string
    {
        try {
            $url = $settings['ollama_url'] ?? 'http://192.168.200.40:11434';
            $model = $settings['ollama_model'] ?? '';
            $timeout = (int)($settings['timeout'] ?? 30);
            
            if (empty($model)) {
                error_log("AIAssistant: No Ollama model configured");
                return null;
            }
            
            $endpoint = rtrim($url, '/') . '/api/generate';
            
            $prompt = $systemPrompt . "\n\nNotiz:\n" . $text . "\n\nAntwort als JSON:";
            
            $payload = json_encode([
                'model' => $model,
                'prompt' => $prompt,
                'stream' => false,
                'format' => 'json'
            ]);
            
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json']
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("AIAssistant: Ollama API returned HTTP $httpCode");
                return null;
            }
            
            $data = json_decode($response, true);
            return $data['response'] ?? null;
            
        } catch (\Exception $e) {
            error_log("AIAssistant: Ollama analysis failed - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Analyze text with OpenAI
     * 
     * @param string $text
     * @param string $systemPrompt
     * @param array $settings
     * @return string|null
     */
    private function analyzeWithOpenAI(string $text, string $systemPrompt, array $settings): ?string
    {
        try {
            $apiKey = $settings['openai_api_key'] ?? '';
            $baseUrl = $settings['openai_base_url'] ?? 'https://api.openai.com/v1';
            $model = $settings['openai_model'] ?? 'gpt-4';
            $timeout = (int)($settings['timeout'] ?? 30);
            
            if (empty($apiKey)) {
                error_log("AIAssistant: No OpenAI API key configured");
                return null;
            }
            
            $endpoint = rtrim($baseUrl, '/') . '/chat/completions';
            
            $payload = json_encode([
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $text]
                ],
                'response_format' => ['type' => 'json_object']
            ]);
            
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("AIAssistant: OpenAI API returned HTTP $httpCode - $response");
                return null;
            }
            
            $data = json_decode($response, true);
            return $data['choices'][0]['message']['content'] ?? null;
            
        } catch (\Exception $e) {
            error_log("AIAssistant: OpenAI analysis failed - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get system prompt for AI (from settings or default)
     * 
     * @return string
     */
    private function getSystemPrompt(): string
    {
        // Get custom prompt from settings or use default
        $customPrompt = $this->settingsRepo->getSetting('system_prompt');
        
        if (!empty($customPrompt)) {
            return $customPrompt;
        }
        
        // Default prompt
        return $this->getDefaultSystemPrompt();
    }
    
    /**
     * Get default system prompt
     * 
     * @return string
     */
    public function getDefaultSystemPrompt(): string
    {
        return <<<PROMPT
Du bist ein intelligenter Assistent für Büroaufgaben. Analysiere die folgende Notiz und extrahiere strukturierte Informationen.

Antworte NUR mit einem validen JSON-Objekt in diesem Format:
{
    "title": "Kurzer, prägnanter Aufgabentitel",
    "description": "Ausführliche Beschreibung mit allen wichtigen Details",
    "category": "Kategorie aus der Liste unten",
    "priority": "critical|high|normal|low",
    "deadline": "Datum im Format YYYY-MM-DD oder null",
    "subtasks": [],
    "tags": []
}

KATEGORIEN (wähle die passendste):
- bestellung: Wenn etwas bestellt werden soll
- anfrage: Allgemeine Informationsanfragen, Auskunft einholen
- reklamation: Beschwerden, Probleme, Defekte, Mängel
- angebot: Angebote erstellen, kalkulieren oder versenden
- rechnung: Rechnungen schreiben, prüfen oder bezahlen
- lagerpruefung: Bestand kontrollieren, Verfügbarkeit prüfen
- followup: Nachfassen, Rückrufe, Erinnerungen, Wiedervorlagen
- lieferant: Kontakt mit Lieferanten, Bestellungen, Anfragen

PRIORITÄT (wähle realistisch):
- critical: Sofortige Handlung erforderlich, blockiert andere Prozesse
- high: Zeitkritisch, sollte heute/morgen erledigt werden
- normal: Reguläre Aufgabe, normale Bearbeitungszeit
- low: Kann warten, nicht zeitkritisch

SUBTASKS (NUR wenn sinnvoll!):
- **Erstelle KEINE Subtasks bei einfachen Aufgaben** (z.B. "Herrn Müller zurückrufen")
- **Erstelle Subtasks NUR wenn**:
  * Die Aufgabe mehrere klar trennbare Schritte hat
  * Es ein mehrstufiger Prozess ist (z.B. Angebot erstellen → versenden → nachfassen)
  * Verschiedene Aktionen erforderlich sind (z.B. prüfen, bestellen, archivieren)
- Wenn keine Subtasks nötig: subtasks bleibt ein leeres Array []
- Wenn Subtasks sinnvoll: max. 3-5 Subtasks, konkret und umsetzbar

DEADLINE:
- Erkenne Zeitangaben: "morgen" = +1 Tag, "in 3 Tagen" = +3 Tage, "nächste Woche" = +7 Tage
- Wenn keine Zeitangabe: deadline = null
- Format: YYYY-MM-DD (z.B. 2026-02-07)

TAGS:
- Extrahiere relevante Keywords aus der Notiz
- Typische Tags: Kundennamen, Projektnamen, Lieferanten, "dringend", "wichtig"
- Ohne # (wird automatisch hinzugefügt)
- Wenn keine relevanten Tags: leeres Array []

BEISPIELE:

Einfache Aufgabe (KEINE Subtasks):
Notiz: "Herrn Schmidt wegen Angebot zurückrufen"
→ subtasks: [] (nur ein Schritt!)

Komplexe Aufgabe (MIT Subtasks):
Notiz: "Angebot für Projekt XY erstellen, an Kunde senden und in 3 Tagen nachfassen"
→ subtasks: ["Angebot kalkulieren", "Angebot per Email versenden", "Nachfass-Termin in Kalender"]

Sei intelligent und flexibel!
PROMPT;
    }

    /**
     * Get available OpenAI models
     */
    public function getOpenAIModels(string $apiKey, string $baseUrl): array
    {
        try {
            $endpoint = rtrim($baseUrl, '/') . '/models';
            
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json'
                ]
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("AIAssistant: OpenAI API returned HTTP $httpCode");
                return [];
            }
            
            $data = json_decode($response, true);
            
            if (!isset($data['data'])) {
                return [];
            }
            
            // Extract and filter model IDs (only GPT models)
            $models = [];
            foreach ($data['data'] as $model) {
                $id = $model['id'] ?? '';
                // Filter for GPT models only
                if (stripos($id, 'gpt') !== false) {
                    $models[] = $id;
                }
            }
            
            // Sort alphabetically
            sort($models);
            
            return $models;
            
        } catch (\Exception $e) {
            error_log("AIAssistant: Failed to fetch OpenAI models - " . $e->getMessage());
            return [];
        }
    }
}
