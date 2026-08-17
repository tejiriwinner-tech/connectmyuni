<?php

/**
 * Connect MyUni — OpenAI Provider Implementation
 *
 * Communicates with OpenAI REST API (Chat Completions)
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Services\Ai\Providers;

use ConnectMyUni\Services\Ai\AiProviderInterface;
use function ConnectMyUni\Config\env;

class OpenAiProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $model;
    private int $timeout;

    public function __construct(?string $apiKey = null, ?string $model = null, int $timeout = 25)
    {
        $this->apiKey = $apiKey ?? (string) env('AI_API_KEY', '');
        $this->model  = $model  ?? (string) env('AI_MODEL', 'gpt-4o-mini');
        $this->timeout = $timeout;
    }

    public function isConfigured(): bool
    {
        return !empty(trim($this->apiKey));
    }

    public function getProvider(): string
    {
        return 'openai';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getName(): string
    {
        return 'OpenAI (' . $this->model . ')';
    }

    public function generate(string $systemPrompt, string $userPrompt, array $options = []): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'data'    => null,
                'error'   => 'AI Provider is not configured. Please set AI_API_KEY in your .env file.'
            ];
        }

        $url = 'https://api.openai.com/v1/chat/completions';

        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens'  => $options['max_tokens'] ?? 2000,
            'response_format' => ['type' => 'json_object']
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . trim($this->apiKey)
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTPCODE);
        $curlErr      = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            error_log("[AI Provider Error] cURL error: " . $curlErr);
            return [
                'success' => false,
                'data'    => null,
                'error'   => 'Failed to connect to the AI provider. Check server logs.'
            ];
        }

        if ($httpCode !== 200) {
            $errData = json_decode((string)$responseBody, true);
            $errMsg  = $errData['error']['message'] ?? "Provider returned HTTP status $httpCode";
            error_log("[AI Provider Error] HTTP $httpCode: $errMsg");
            // Never forward the provider-supplied message (it can embed request
            // metadata); the status code alone is safe and useful.
            return [
                'success' => false,
                'data'    => null,
                'error'   => 'AI service error (HTTP ' . $httpCode . '). Check server logs.'
            ];
        }

        $decoded = json_decode((string)$responseBody, true);
        $content = $decoded['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            return [
                'success' => false,
                'data'    => null,
                'error'   => 'AI Provider returned an empty response.'
            ];
        }

        $jsonContent = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("[AI Provider Error] Malformed JSON in output: " . json_last_error_msg());
            return [
                'success' => false,
                'data'    => ['raw_text' => $content],
                'error'   => 'Received malformed JSON from AI provider.'
            ];
        }

        return [
            'success' => true,
            'data'    => $jsonContent,
            'error'   => null
        ];
    }
}
