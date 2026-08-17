<?php declare(strict_types=1);

/**
 * Connect MyUni — NVIDIA NIM Provider Implementation
 *
 * Speaks the OpenAI-compatible Chat Completions endpoint exposed by NVIDIA NIM
 * (https://integrate.api.nvidia.com/v1/chat/completions), using an OpenAI-shaped
 * request/response so it drops into the existing AiContentService contract
 * unchanged.
 *
 * Selection is driven by the short, unambiguous provider identifier
 * (AI_PROVIDER=nvidia) resolved by AiContentService::resolveProvider() — the URL
 * is NEVER used as the identifier. Backward compatibility is preserved: when
 * AI_PROVIDER is unset or "openai", the default OpenAiProvider is used.
 *
 * @package ConnectMyUni
 */

namespace ConnectMyUni\Services\Ai\Providers;

use ConnectMyUni\Services\Ai\AiProviderInterface;
use function ConnectMyUni\Config\env;

class NvidiaNIMProvider implements AiProviderInterface
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private int $timeout;

    public function __construct(?string $apiKey = null, ?string $model = null, ?string $baseUrl = null, int $timeout = 25)
    {
        $this->apiKey   = $apiKey ?? (string) env('AI_API_KEY', '');
        $this->model    = $model  ?? (string) env('AI_MODEL', 'meta/muse-glimmer-30b');
        $this->baseUrl  = rtrim((string) ($baseUrl ?? (string) env('AI_BASE_URL', 'https://integrate.api.nvidia.com/v1')), '/');
        $this->timeout  = $timeout;
    }

    public function isConfigured(): bool
    {
        // Require both a key and a base URL: neither is ever read back to callers.
        return !empty(trim($this->apiKey)) && $this->baseUrl !== '';
    }

    public function getProvider(): string
    {
        return 'nvidia';
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getName(): string
    {
        return 'NVIDIA NIM (' . $this->model . ')';
    }

    public function generate(string $systemPrompt, string $userPrompt, array $options = []): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'data'    => null,
                'error'   => 'AI Provider is not configured. Set AI_API_KEY and AI_BASE_URL in the server configuration.',
            ];
        }

        $url = $this->baseUrl . '/chat/completions';

        $payload = [
            'model' => $options['model'] ?? $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens'  => $options['max_tokens'] ?? 2000,
            'response_format' => ['type' => 'json_object'],
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . trim($this->apiKey),
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
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $responseBody = curl_exec($ch);
                $httpCode     = curl_getinfo($ch, \CURLINFO_HTTP_CODE);
        $curlErr      = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            // Internal detail to server logs only; generic message to the client.
            error_log('[AI Provider Error][nvidia] cURL error: ' . $curlErr);
            return [
                'success' => false,
                'data'    => null,
                'error'   => 'Failed to connect to the AI provider. Check server logs.',
            ];
        }

        if ($httpCode !== 200) {
            $errData = json_decode((string) $responseBody, true);
            $errMsg  = $errData['error']['message'] ?? "Provider returned HTTP status $httpCode";
            error_log("[AI Provider Error][nvidia] HTTP $httpCode: $errMsg");
            // Never forward provider-supplied detail (may embed request metadata).
            return [
                'success' => false,
                'data'    => null,
                'error'   => 'AI service error (HTTP ' . $httpCode . '). Check server logs.',
            ];
        }

        $decoded = json_decode((string) $responseBody, true);
        $content = $decoded['choices'][0]['message']['content'] ?? null;

                if ($content === null || $content === '') {
            return [
                'success' => false,
                'data'    => null,
                'error'   => 'AI Provider returned an empty response.',
            ];
        }

                // The model is asked to return JSON; decode it so the service can map fields.
        $jsonContent = json_decode((string) $content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('[AI Provider Error][nvidia] Malformed JSON in output: ' . json_last_error_msg());
            return [
                'success' => false,
                'data'    => ['raw_text' => $content],
                'error'   => 'Received malformed JSON from AI provider.',
            ];
        }

        return [
            'success' => true,
            'data'    => $jsonContent,
            'error'   => null,
        ];
    }
}
