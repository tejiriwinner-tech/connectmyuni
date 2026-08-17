<?php

/**
 * Connect MyUni — AI Provider Interface
 *
 * Contract for AI providers (OpenAI, Anthropic, Mock, etc.)
 *
 * @package ConnectMyUni
 */

declare(strict_types=1);

namespace ConnectMyUni\Services\Ai;

interface AiProviderInterface
{
    /**
     * Check if the provider is properly configured (e.g. API key present)
     */
    public function isConfigured(): bool;

    /**
     * Get provider identifier name (short, stable identifier used for logging,
     * e.g. "openai" or "mock"). This is separate from getModel() so generation
     * records can persist provider and model independently.
     */
    public function getProvider(): string;

    /**
     * Get the configured model identifier (e.g. "gpt-4o-mini" or "test-model").
     */
    public function getModel(): string;

    /**
     * Get human-readable provider identifier name
     */
    public function getName(): string;

    /**
     * Generate content based on system prompt and user parameters
     *
     * @param string $systemPrompt System instructions and security guardrails
     * @param string $userPrompt   Structured user input/context
     * @param array  $options      Optional parameters (temperature, max_tokens, etc.)
     * @return array Standardized array response ['success' => bool, 'data' => array|string, 'error' => string|null]
     */
    public function generate(string $systemPrompt, string $userPrompt, array $options = []): array;
}
