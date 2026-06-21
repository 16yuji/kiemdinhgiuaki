<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OpenAiTextService
{
    public function available(): bool
    {
        return filled(config('travelmate_ai.openai_api_key'))
            && config('travelmate_ai.provider') !== 'fallback';
    }

    public function generate(string $instructions, string $input, int $maxOutputTokens = 700): ?string
    {
        if (!$this->available()) {
            return null;
        }

        try {
            $response = Http::timeout((int) config('travelmate_ai.timeout', 18))
                ->withToken(config('travelmate_ai.openai_api_key'))
                ->acceptJson()
                ->post(config('travelmate_ai.openai_endpoint'), [
                    'model' => config('travelmate_ai.openai_model'),
                    'instructions' => $instructions,
                    'input' => $input,
                    'max_output_tokens' => $maxOutputTokens,
                ]);
        } catch (\Throwable) {
            return null;
        }

        if (!$response->ok()) {
            return null;
        }

        $payload = $response->json();
        $text = $payload['output_text'] ?? null;

        if (is_string($text) && filled($text)) {
            return trim($text);
        }

        $output = $payload['output'] ?? [];
        foreach ($output as $item) {
            foreach (($item['content'] ?? []) as $content) {
                $candidate = $content['text'] ?? null;
                if (is_string($candidate) && filled($candidate)) {
                    return trim($candidate);
                }
            }
        }

        return null;
    }

    public function generateJson(string $instructions, string $input, int $maxOutputTokens = 900): ?array
    {
        $text = $this->generate($instructions . "\nTra ve JSON hop le, khong boc trong Markdown.", $input, $maxOutputTokens);

        if (!$text) {
            return null;
        }

        $text = trim($text);
        $text = Str::of($text)
            ->replace('```json', '')
            ->replace('```', '')
            ->trim()
            ->toString();

        $decoded = json_decode($text, true);

        return is_array($decoded) ? $decoded : null;
    }
}
