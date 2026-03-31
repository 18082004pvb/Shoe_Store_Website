<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiChatService
{
    public function ask(array $messages): array
    {
        $provider = env('AI_PROVIDER', 'gemini');

        switch ($provider) {
            case 'gemini':
            default:
                return $this->askGemini($messages);
        }
    }

    protected function askGemini(array $messages): array
    {
        $apiKey = config('services.gemini.key');
        $model = config('services.gemini.model', 'gemini-2.5-flash');

        if (!$apiKey) {
            throw new \Exception('Thiếu GEMINI_API_KEY trong file .env');
        }

        $prompt = $this->flattenMessages($messages);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $response = Http::timeout(90)->post($url, [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'maxOutputTokens' => 1024,
            ],
        ]);

        if (!$response->successful()) {
            throw new \Exception('Gemini API Error: ' . $response->body());
        }

        $json = $response->json();

        return [
            'response_id' => $json['responseId'] ?? null,
            'text' => $json['candidates'][0]['content']['parts'][0]['text'] ?? 'Xin lỗi, tôi chưa thể trả lời lúc này.',
            'raw' => $json,
        ];
    }

    protected function flattenMessages(array $messages): string
    {
        $result = [];

        foreach ($messages as $message) {
            $role = strtoupper($message['role'] ?? 'USER');
            $text = '';

            if (!empty($message['content']) && is_array($message['content'])) {
                foreach ($message['content'] as $content) {
                    if (($content['type'] ?? '') === 'input_text') {
                        $text .= ($content['text'] ?? '') . "\n";
                    }
                }
            }

            $result[] = $role . ":\n" . trim($text);
        }

        return trim(implode("\n\n", $result));
    }
}