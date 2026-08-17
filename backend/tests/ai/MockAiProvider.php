<?php declare(strict_types=1);

/**
 * Connect MyUni — Mock AI Provider (test only)
 *
 * Deterministic, offline implementation of AiProviderInterface used by the
 * authenticated integration harness (backend/tests/ai/ai_integration_test.php).
 *
 * It never contacts the internet or OpenAI, is always "configured", and
 * exposes predictable provider/model metadata:
 *
 *   provider = 'mock'
 *   model    = 'test-model'
 *
 * It is intentionally isolated from production (no production config or env
 * variable selects it; the harness loads it explicitly via require_once).
 *
 * @package ConnectMyUni
 */

namespace ConnectMyUni\Tests\Ai\Providers;

use ConnectMyUni\Services\Ai\AiProviderInterface;

class MockAiProvider implements AiProviderInterface
{
    public function isConfigured(): bool
    {
        return true;
    }

    public function getProvider(): string
    {
        return 'mock';
    }

    public function getModel(): string
    {
        return 'test-model';
    }

    public function getName(): string
    {
        return 'Mock (' . $this->getModel() . ')';
    }

    /**
     * Return a deterministic, human-reviewable placeholder payload. No real
     * factual claims are fabricated (consistent with the server-side guardrail).
     */
    public function generate(string $systemPrompt, string $userPrompt, array $options = []): array
    {
        return [
            'success' => true,
            'data'    => [
                'title'       => 'Deterministic Mock Title',
                'category'    => 'webinar',
                'location'    => 'Mock City',
                'description' => 'Deterministic mock description generated offline for testing.',
                'details'     => 'Safe placeholder content — verify before use.',
            ],
            'error'   => null,
        ];
    }
}