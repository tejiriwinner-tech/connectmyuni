<?php declare(strict_types=1);

namespace ConnectMyUni\Services\Ai;

use ConnectMyUni\Middleware\AuthMiddleware;
use ConnectMyUni\Repositories\AiContentRequestRepository;
use ConnectMyUni\Services\Ai\Providers\NvidiaNIMProvider;
use ConnectMyUni\Services\Ai\Providers\OpenAiProvider;
use Throwable;
use function ConnectMyUni\Config\env;

/**
 * Server-side service driving AI content generation, improvement, and the
 * approve/history workflow.
 *
 * Security & design notes:
 *   - The provider API key is read ONLY inside the provider (server-side) and is
 *     never forwarded to the browser.
 *   - GUARDRAIL is appended to every system prompt and CANNOT be overridden by
 *     browser-supplied input (anti-hallucination guardrail).
 *   - Generated/improved output is persisted as history only
 *     (AiContentRequestRepository); no university/event/hero-slide CRUD tables
 *     are written by this service.
 */
class AiContentService
{
    /** Server-side anti-hallucination guardrail; never exposed to or overridable by the browser. */
    public const GUARDRAIL = 'IMPORTANT: Do not fabricate or invent factual claims '
        . '(tuition fees, ranking positions, admission/entry requirements, course '
        . 'fees, dates, statistics, accreditation status, phone numbers, email '
        . 'addresses, or physical addresses). If a fact is uncertain, emit a '
        . 'placeholder of the form "[verify with university]" and flag it. Prefer '
        . 'concise, human-reviewable output; the human editor is responsible for '
        . 'verifying every fact before publication.';

    /** Allowlist of content types that may be generated/improved/approved. */
    public const ALLOWED_CONTENT_TYPES = [
        'event',
        'university',
        'hero_slide',
        'blog_post',
        'social_caption',
        'website_content',
    ];

    /**
     * Output JSON key schema per content type. Keys are aligned with the
     * $aiFieldMap used by the CRUD pages and with the "Use This Content"
     * mapping in ai-generator-core.php, so generated keys resolve to the
     * target form fields.
     *
     * @var array<string, array<int, string>>
     */
    private static array $fieldSchemas = [
        'event'           => ['title','category','audience','location','event_date','key_points','tone','length','description','details','cta'],
        'university'      => ['name','country','city','location','programs','selling_points','target_students','tone','length','overview','website_url'],
        'hero_slide'      => ['title','subtitle','cta_text','cta_url','headline','destination','tone','length'],
        'blog_post'       => ['title','summary','body','slug','seo_keywords','audience','tone','length'],
        'social_caption'  => ['caption','hashtags','platform','audience','cta','tone'],
        'website_content' => ['title','section','purpose','audience','summary','key_points','tone','length'],
    ];

    public function __construct(
        private AiProviderInterface $provider,
        private ?AiContentRequestRepository $repo = null
    ) {
    }

    /**
     * Repository accessor. Production uses the canonical repository (real DB);
     * tests may inject a throwaway repository/PDO via the constructor.
     */
    private function repo(): AiContentRequestRepository
    {
        return $this->repo ?? new AiContentRequestRepository();
    }

    /** @return string[] */
    public static function allowedContentTypes(): array
    {
        return self::ALLOWED_CONTENT_TYPES;
    }

        public function isConfigured(): bool
    {
        return $this->provider->isConfigured();
    }

    /**
     * Resolve the active AI provider from configuration (env() AI_PROVIDER).
     *
     * Selection uses a short, unambiguous identifier — NEVER the URL:
     *   'nvidia'        -> NvidiaNIMProvider (NVIDIA NIM)
     *   'openai' | other -> OpenAiProvider (default, backward compatible)
     *
     * Reads env() (process env first, then .env) so the operator can switch the
     * provider via environment without code changes. No API key or secret is
     * read or returned by this method — only the provider instance.
     */
    public static function resolveProvider(?string $provider = null): AiProviderInterface
    {
        $which = strtolower(trim((string) ($provider ?? env('AI_PROVIDER', 'openai'))));

        if ($which === 'nvidia') {
            return new NvidiaNIMProvider();
        }

        return new OpenAiProvider();
    }

    public function providerName(): string
    {
        return $this->provider->getName();
    }

    /**
     * Generate structured content for a given content type.
     *
     * @param array $params Context fields collected from the generated form.
     * @return array{success:bool, requestId?:int, data?:array|string, error?:string|null, error_code?:string}
     */
    public function generateContent(string $contentType, array $params): array
    {
        if (!in_array($contentType, self::ALLOWED_CONTENT_TYPES, true)) {
            return [
                'success'    => false,
                'error'      => 'Unsupported content type: ' . $contentType,
                'error_code' => 'unsupported_content_type',
            ];
        }

        $fields       = self::$fieldSchemas[$contentType] ?? [];
        $systemPrompt = $this->buildGenerationSystemPrompt($contentType, $fields);
        $userPrompt   = $this->buildUserPrompt($params);

        $response = $this->provider->generate($systemPrompt, $userPrompt, [
            'temperature' => (float) ($params['temperature'] ?? 0.7),
            'max_tokens'  => 2000,
        ]);

        if (empty($response['success'])) {
            return [
                'success' => false,
                'error'   => $response['error'] ?? 'AI generation failed.',
            ];
        }

        $data = $response['data'];

        // Persist as history (best-effort); never expose DB errors to the client.
        // Provider/model metadata is obtained from the provider abstraction, never
        // hard-coded here.
        try {
            $repo      = $this->repo();
            $requestId = $repo->create([
                'admin_user_id'     => AuthMiddleware::userId(),
                'content_type'      => $contentType,
                'prompt'            => $userPrompt,
                'generated_content' => $data,
                'status'            => 'generated',
                'provider'          => $this->provider->getProvider(),
                'model'             => $this->provider->getModel(),
            ]);
        } catch (Throwable $e) {
            error_log('[AI generate] Failed to persist generation history: ' . $e->getMessage());
            $requestId = 0;
        }

        return [
            'success'   => true,
            'requestId' => $requestId,
            'data'      => $data,
            'approved'  => false,
        ];
    }

    /**
     * Improve existing content.
     *
     * @return array{success:bool, data?:string, error?:string|null}
     */
    public function improveContent(string $existing, string $instruction, string $context = ''): array
    {
        $systemPrompt = 'You are an assistant that improves existing university-website '
            . 'content. Return the improved text as a JSON object with exactly one key, '
            . '"improved", whose value is the improved content as a plain string (no '
            . 'markdown fences). Keep improvements concise and factual. ' . self::GUARDRAIL;

        $userPrompt = 'Existing content:' . "\n" . $existing . "\n\nInstruction:" . "\n" . $instruction;
        if ($context !== '') {
            $userPrompt .= "\n\nContext:\n" . $context;
        }

        $response = $this->provider->generate($systemPrompt, $userPrompt, [
            'temperature' => 0.5,
            'max_tokens'  => 2000,
        ]);

        if (empty($response['success'])) {
            return ['success' => false, 'error' => $response['error'] ?? 'AI improvement failed.'];
        }

        $data     = $response['data'];
        $improved = is_array($data) && array_key_exists('improved', $data)
            ? (string) $data['improved']
            : (is_string($data) ? $data : (string) json_encode($data));

        return ['success' => true, 'data' => $improved];
    }

    /**
     * Record an approval against a generation request (history only; never
     * writes to any CRUD table).
     */
    public function approveContent(int $requestId, array $approvedContent): bool
    {
        return $this->repo()->updateApproval($requestId, 'approved', $approvedContent);
    }

    /** @return array<int, array> Recent generation requests for the history view. */
    public function getRecentHistory(int $limit = 20, ?int $adminUserId = null): array
    {
        return $this->repo()->getRecent($limit, $adminUserId);
    }

    private function buildGenerationSystemPrompt(string $contentType, array $fields): string
    {
        $typeLabel = ucfirst(str_replace('_', ' ', $contentType));
        $schema    = '';
        foreach ($fields as $f) {
            $schema .= '   - ' . $f . "\n";
        }

        return "You are a precise content generator for a university website CMS.\n"
            . "Produce ONLY valid JSON (no prose, no markdown fences) with exactly these "
            . "top-level keys for the content type '" . $typeLabel . "':\n"
            . $schema
            . "Keep text values concise and human-reviewable. " . self::GUARDRAIL;
    }

    private function buildUserPrompt(array $params): string
    {
        $parts = [];
        foreach ($params as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $val    = is_array($v) ? implode(', ', $v) : (string) $v;
            $parts[] = $k . ': ' . $val;
        }
        $context = trim(implode("\n", $parts));

        return "Generate structured JSON content for the context below.\n\nContext:\n"
            . $context . "\n\nOutput:";
    }
}

