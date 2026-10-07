<?php declare(strict_types=1);

/**
 * Connect MyUni — AI Image & Education Photography Service
 *
 * @package ConnectMyUni
 */

namespace ConnectMyUni\Services\Ai;

use ConnectMyUni\Helpers\MediaResolver;

class AiImageService
{
    /**
     * Curated high-definition commercial stock photography for education consultancies.
     */
    public const CURATED_STOCK_PHOTOS = [
        'imagen3_campus' => [
            'title' => 'International University Scholars (Oxford Quadrangle)',
            'url' => '/storage/uploads/events/2026/09/imagen3-photoreal-campus.jpg',
            'thumb' => '/storage/uploads/events/2026/09/imagen3-photoreal-campus.jpg',
            'tag' => '🌟 Ultra-Photoreal'
        ],
        'imagen3_medical' => [
            'title' => 'Medical School Laboratory & Science Team',
            'url' => '/storage/uploads/events/2026/09/imagen3-photoreal-medical.jpg',
            'thumb' => '/storage/uploads/events/2026/09/imagen3-photoreal-medical.jpg',
            'tag' => '🌟 Ultra-Photoreal'
        ],
        'imagen3_graduation' => [
            'title' => 'Global University Graduates in Caps & Gowns',
            'url' => '/storage/uploads/events/2026/09/imagen3-photoreal-graduation.jpg',
            'thumb' => '/storage/uploads/events/2026/09/imagen3-photoreal-graduation.jpg',
            'tag' => '🌟 Ultra-Photoreal'
        ],
        'campus_students' => [
            'title' => 'International Campus Life & Students',
            'url' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1200&h=630&q=85',
            'thumb' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=300&h=160&q=80',
            'tag' => '🎓 Students & Campus'
        ],
        'uk_europe' => [
            'title' => 'UK & Europe Historic University Architecture',
            'url' => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=1200&h=630&q=85',
            'thumb' => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=300&h=160&q=80',
            'tag' => '🇬🇧 UK & Europe'
        ],
        'medical_science' => [
            'title' => 'Medical Degree & Healthcare Education',
            'url' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=1200&h=630&q=85',
            'thumb' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=300&h=160&q=80',
            'tag' => '🩺 Medical & Science'
        ],
        'study_consultation' => [
            'title' => 'Study Abroad Advisory & Seminar',
            'url' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=1200&h=630&q=85',
            'thumb' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=300&h=160&q=80',
            'tag' => '💼 Advisory & Fair'
        ],
        'graduation' => [
            'title' => 'Graduation & Global Scholarship Success',
            'url' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1200&h=630&q=85',
            'thumb' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=300&h=160&q=80',
            'tag' => '📜 Graduation & Awards'
        ],
        'airport_travel' => [
            'title' => 'Visa Briefing & Flight Departure',
            'url' => 'https://images.unsplash.com/photo-1530521954074-e64f6810b32d?auto=format&fit=crop&w=1200&h=630&q=85',
            'thumb' => 'https://images.unsplash.com/photo-1530521954074-e64f6810b32d?auto=format&fit=crop&w=300&h=160&q=80',
            'tag' => '✈️ Travel & Departure'
        ],
        'library_study' => [
            'title' => 'Modern University Library & Research',
            'url' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=1200&h=630&q=85',
            'thumb' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?auto=format&fit=crop&w=300&h=160&q=80',
            'tag' => '📚 Library & Research'
        ],
        'global_classroom' => [
            'title' => 'International Classroom & Lecture Hall',
            'url' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=1200&h=630&q=85',
            'thumb' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=300&h=160&q=80',
            'tag' => '🏛️ Lecture & Workshop'
        ]
    ];

    /**
     * Fetch and save a curated real stock photo.
     */
    public function fetchStockPhoto(string $photoKey, string $category = 'events'): array
    {
        $photos = self::CURATED_STOCK_PHOTOS;
        if (!isset($photos[$photoKey])) {
            return ['success' => false, 'error' => 'Invalid photo selection.'];
        }

        $sourceUrl = $photos[$photoKey]['url'];
        
        // Handle local storage assets directly
        if (str_starts_with($sourceUrl, '/storage/')) {
            $localRel = ltrim($sourceUrl, '/');
            $localAbs = dirname(__DIR__, 3) . '/' . $localRel;
            if (file_exists($localAbs)) {
                $imageData = file_get_contents($localAbs);
                if ($imageData !== false && strlen($imageData) > 3000) {
                    return $this->saveImageFile($imageData, $category, 'stock');
                }
            }
        }
        
        $ch = curl_init($sourceUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'ConnectMyUni-PhotoService/1.0'
        ]);
        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || empty($imageData) || strlen($imageData) < 3000) {
            return ['success' => false, 'error' => 'Could not download the selected photo. Please try again.'];
        }

        return $this->saveImageFile($imageData, $category, 'stock');
    }

    /**
     * Generate an AI flyer or banner image using strict commercial photorealism.
     *
     * @param string $prompt Description or title for the event flyer.
     * @param string $category 'events' or 'hero'
     * @return array{success: bool, image_path?: string, image_url?: string, error?: string}
     */
    public function generateFlyer(string $prompt, string $category = 'events'): array
    {
        $prompt = trim($prompt);
        if ($prompt === '') {
            return ['success' => false, 'error' => 'Please provide a topic or prompt for the flyer.'];
        }

        // Clean prompt and enforce strict commercial photography keywords
        $cleanPrompt = preg_replace('/[^\w\s\-\,\.]/u', ' ', $prompt);
        $cleanPrompt = trim(preg_replace('/\s+/', ' ', $cleanPrompt));
        
        // 1. Check if fal.ai FLUX.1 is configured (Top-tier photorealism)
        $falKey = \ConnectMyUni\Config\env('FAL_KEY') ?: \ConnectMyUni\Config\env('FAL_API_KEY');
        if (!empty($falKey)) {
            $falRes = $this->generateWithFalAi($cleanPrompt, (string)$falKey, $category);
            if (!empty($falRes['success']) || !empty($falRes['is_fatal'])) {
                return $falRes;
            }
        }

        $enhancedPrompt = "Authentic commercial stock photography of real diverse university students, {$cleanPrompt}, real people, modern campus, natural sunlight, sharp 8k photorealistic, Canon EOS R5 photo, no anime, no 3D render, no CGI, no cartoon, no fantasy, no dark moody illustration, professional bright editorial lighting";
        
        $encoded = urlencode($enhancedPrompt);
        $width = 1200;
        $height = 630;
        $seed = rand(1000, 999999);

        // Fallback models
        $modelsToTry = ['turbo', 'flux'];
        $imageData = null;
        $lastError = '';

        foreach ($modelsToTry as $m) {
            $url = "https://image.pollinations.ai/prompt/{$encoded}?width={$width}&height={$height}&nologo=true&model={$m}&seed={$seed}";
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 25,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'ConnectMyUni-AI/1.0'
            ]);
            $res = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200 && !empty($res) && strlen((string)$res) > 3000) {
                $imageData = $res;
                break;
            } else {
                $lastError = $curlErr ?: "HTTP {$httpCode}";
            }
        }

        if (empty($imageData)) {
            return [
                'success' => false,
                'error' => 'AI image provider is currently busy. Please try again in a few moments: ' . $lastError
            ];
        }

        return $this->saveImageFile($imageData, $category, 'ai');
    }

    /**
     * Generate an ultra-photorealistic image using fal.ai (FLUX.1 schnell/dev).
     *
     * @param string $prompt Clean user prompt.
     * @param string $falKey Fal.ai API key.
     * @param string $category 'events' or 'hero'
     * @return array{success: bool, image_path?: string, image_url?: string, error?: string, is_fatal?: bool}
     */
    public function generateWithFalAi(string $prompt, string $falKey, string $category = 'events'): array
    {
        $enhancedPrompt = "Authentic commercial documentary photograph of university students, {$prompt}, real people, modern university campus, natural sunlight, sharp 8k photorealistic, shot on 35mm Canon EOS R5, high dynamic range, natural skin textures, no anime, no 3D render, no CGI, no cartoon";

        $url = 'https://fal.run/fal-ai/flux/schnell';
        $payload = json_encode([
            'prompt' => $enhancedPrompt,
            'image_size' => [
                'width' => 1200,
                'height' => 630
            ],
            'num_inference_steps' => 4,
            'enable_safety_checker' => true
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Key ' . trim($falKey),
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'ConnectMyUni-AI/1.0'
        ]);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['success' => false, 'error' => "Connection to fal.ai failed: {$curlErr}"];
        }

        $json = json_decode((string)$res, true);

        $detail = (string)($json['detail'] ?? $json['message'] ?? '');

        // Check for fal.ai credit / top-up requirement
        if (stripos($detail, 'TOP_UP') !== false || stripos($detail, 'credit') !== false || $httpCode === 402) {
            return [
                'success' => false,
                'is_fatal' => true,
                'error' => 'Your fal.ai account has $0.00 balance and requires a top-up. Please visit https://fal.ai/dashboard/billing to add credits ($1 to $5 gives hundreds of high-res FLUX generations).'
            ];
        }

        if ($httpCode === 401 || $httpCode === 403) {
            return [
                'success' => false,
                'is_fatal' => true,
                'error' => 'Fal.ai authentication failed (' . ($detail ?: 'Unauthorized') . '). Please check your key at https://fal.ai/dashboard/keys'
            ];
        }

        if ($httpCode !== 200 || empty($json['images'][0]['url'])) {
            $msg = $json['detail'] ?? $json['message'] ?? "HTTP {$httpCode}";
            return ['success' => false, 'error' => "Fal.ai generation error: {$msg}"];
        }

        $imageUrl = $json['images'][0]['url'];

        // Download the generated high-res image
        $ch2 = curl_init($imageUrl);
        curl_setopt_array($ch2, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'ConnectMyUni-AI/1.0'
        ]);
        $imgBytes = curl_exec($ch2);
        $downCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);

        if ($downCode !== 200 || empty($imgBytes) || strlen((string)$imgBytes) < 3000) {
            return ['success' => false, 'error' => 'Fal.ai generated the image, but could not download the final file.'];
        }

        return $this->saveImageFile((string)$imgBytes, $category, 'fal-flux');
    }

    /**
     * Save downloaded or generated image bytes to storage/uploads/{category}/{year}/{month}/
     */
    private function saveImageFile(string $imageData, string $category, string $prefix): array
    {
        $year = date('Y');
        $month = date('m');
        $targetDir = dirname(__DIR__, 3) . "/storage/uploads/{$category}/{$year}/{$month}";

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $hash = bin2hex(random_bytes(8));
        $filename = "{$prefix}-{$hash}.jpg";
        $filePath = "{$targetDir}/{$filename}";

        if (file_put_contents($filePath, $imageData) === false) {
            return ['success' => false, 'error' => 'Could not save image to storage.'];
        }

        $storageKey = "{$category}/{$year}/{$month}/{$filename}";
        $publicUrl = MediaResolver::url($storageKey);

        return [
            'success' => true,
            'image_path' => $storageKey,
            'image_url' => $publicUrl
        ];
    }
}
