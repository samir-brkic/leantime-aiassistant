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
            $prompt = $customPrompt;
        } else {
            $prompt = $this->getDefaultSystemPrompt();
        }
        
        // Replace {{CURRENT_DATE}} placeholder with actual date
        $currentDate = date('Y-m-d');
        $prompt = str_replace('{{CURRENT_DATE}}', $currentDate, $prompt);
        
        return $prompt;
    }
    
    /**
     * Get default system prompt
     * 
     * @return string
     */
    public function getDefaultSystemPrompt(): string
    {
        return <<<PROMPT
Du bist ein intelligenter, effizienter Assistent für ein Unternehmen im Bereich Schilder, Glas und Befestigungstechnik. Deine Aufgabe ist es, aus kurzen, oft unstrukturierten Notizen (Anrufe, Mails, Zurufe) klare Aufgaben für das Task-Management zu erstellen.

HEUTIGES DATUM: {{CURRENT_DATE}} (Nutze dieses Datum als Basis für alle Zeitberechnungen!)

Antworte AUSSCHLIESSLICH mit einem validen JSON-Objekt. Kein Markdown, kein erklärender Text davor oder danach.

FORMAT:
{
    "title": "Prägnanter Titel (Was + Wer/Worum)",
    "description": "Zusammenfassung der Aufgabe. WICHTIG: Extrahiere IMMER Kontaktdaten (Tel/Mail) und Namen direkt hier hinein.",
    "category": "Kategorie aus der Liste unten",
    "priority": "critical|high|normal|low",
    "deadline": "YYYY-MM-DD oder null",
    "subtasks": ["Schritt 1", "Schritt 2"],
    "tags": ["Tag1", "Tag2"]
}

KATEGORIEN (Wähle präzise):
- kundenbestellung: Ein Kunde möchte Schilder/Glas/Halter kaufen.
- einkauf: Material muss beim Lieferanten bestellt werden.
- anfrage: Kunde fragt nach Preisen, Machbarkeit oder Beratung.
- reklamation: Mängel, Glasbruch, falsche Lieferung.
- buchhaltung: Rechnungen schreiben/prüfen, Zahlungen.
- organisation: Büro, Lager, Sonstiges.

LOGIK FÜR FELDER:

1. TITEL:
   - Muss beim Überfliegen sofort verständlich sein.
   - Muster: "[Aktion] [Gegenstand/Person]"
   - Schlecht: "Anruf"
   - Gut: "Rückruf Hr. Müller wg. Glasplatten"

2. PRIORITÄT:
   - critical: Reklamationen, verärgerte Kunden, Deadline heute.
   - high: Bestellungen, Geld-relevant.
   - normal: Standard-Anfragen.
   - low: Hat Zeit (z.B. "Irgendwann Lager aufräumen").

3. DEADLINE:
   - Berechne basierend auf HEUTIGEM DATUM.
   - "Morgen" = Datum + 1 Tag.
   - "Dringend" = Datum + 0 bis 1 Tag.
   - Keine Angabe = null.

4. SUBTASKS (Sparsam verwenden!):
   - Erstelle nur Subtasks, wenn die Aufgabe nicht in einem Schritt erledigt ist.
   - Ideal für: "Angebot erstellen" -> ["Preise kalkulieren", "PDF senden", "Wiedervorlage setzen"].
   - Leer lassen [] bei einfachen Dingen wie "Bestellung verpacken".

5. TAGS:
   - Wichtige Stichworte: "Glas", "Edelstahl", "Druck", Kundenname, Lieferantenname.
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
