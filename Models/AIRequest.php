<?php

namespace Leantime\Plugins\AIAssistant\Models;

/**
 * AI Request Model
 * 
 * Represents a request to the AI provider for text analysis
 */
class AIRequest
{
    public function __construct(
        public string $text,
        public string $provider = 'ollama',
        public array $config = [],
        public string $systemPrompt = '',
        public int $timeout = 30
    ) {}

    /**
     * Convert to array for API request
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'provider' => $this->provider,
            'config' => $this->config,
            'systemPrompt' => $this->systemPrompt,
            'timeout' => $this->timeout
        ];
    }

    /**
     * Validate request data
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return !empty($this->text) && !empty($this->provider);
    }
}
