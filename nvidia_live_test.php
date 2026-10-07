<?php

$root = __DIR__;

require_once $root . '/backend/config/app.php';
require_once $root . '/backend/services/ai/AiProviderInterface.php';
require_once $root . '/backend/services/ai/providers/NvidiaNIMProvider.php';

$provider = new \ConnectMyUni\Services\Ai\Providers\NvidiaNIMProvider();

$result = $provider->generate(
    'You are a professional study-abroad content writer.',
    'Write a short professional study-abroad caption about studying in Canada. Return a concise caption suitable for social media.',
    [
        'temperature' => 0.7,
        'max_tokens' => 500
    ]
);

echo 'SUCCESS=' . (($result['success'] ?? false) ? 'YES' : 'NO') . PHP_EOL;
echo 'PROVIDER=' . ($result['provider'] ?? 'n/a') . PHP_EOL;
echo 'MODEL=' . ($result['model'] ?? 'n/a') . PHP_EOL;

if (!empty($result['data'])) {
    echo 'DATA=' . json_encode($result['data'], JSON_UNESCAPED_UNICODE) . PHP_EOL;
}

if (!empty($result['error'])) {
    echo 'ERROR=' . $result['error'] . PHP_EOL;
}
