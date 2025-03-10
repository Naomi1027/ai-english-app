<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ApiService
{
    /**
     * @param string $audioFilePath
     */
    public function callWhiperApi($audioFilePath)
    {
        // S3から音声ファイルを読み込む
        if (!Storage::disk('s3')->exists($audioFilePath)) {
            throw new \Exception('Audio file not found in S3: ' . $audioFilePath);
        }

        $audioContent = Storage::disk('s3')->get($audioFilePath);

        $response = Http::attach(
                'file',
                $audioContent,
                'audio.wav'
            )
            ->withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            ])
            ->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
                'language' => 'en',
            ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to call API: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * @param Collection<Message> $modelMessages
     */
    public function callGptApi($modelMessages)
    {
        $systemMessage = [
            'role' => 'system',
            'content' => 'You are a friendly person having a casual conversation in English with the user. Respond naturally and keep the conversation engaging. Do not provide lists, extensive advice, or instructional content unless the user specifically asks for it.',
        ];

        $messages = $modelMessages->map(function($message) {
            return [
                'role' => $message->role === 1 ? 'user' : 'assistant',
                'content' => $message->message_en,
            ];
        })->toArray();

        $messages = array_merge([$systemMessage], $messages);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => $messages,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to call API: ' . $response->body());
        }

        return $response->json();

    }

    /**
     * @param string $aiMessageText
     */
    public function callTtsApi($aiMessageText)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])
        ->post('https://api.openai.com/v1/audio/speech', [
            'model' => 'tts-1',
            'input' => $aiMessageText,
            'voice' => 'shimmer',
            'response_format' => 'wav',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to call API: ' . $response->body());
        }

        // S3に音声ファイルを保存
        $fileName = 'ai_audio/speech_' . now()->format('Ymd_His') . '.wav';
        Storage::disk('s3')->put($fileName, $response->body());

        return $fileName; // S3上のパスを返す
    }

    /**
     * 英語の文章を日本語に翻訳するためのAPIリクエスト
     *
     * @param string $englishText
     * @return array
     */
    public function callTranslateApi($englishText)
    {
        $systemMessage = [
            'role' => 'system',
            'content' => 'Please translate the English text provided into Japanese.',
        ];

        $userMessage = [
            'role' => 'user',
            'content' => $englishText,
        ];

        $messages = [$systemMessage, $userMessage];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => $messages,
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to call API: ' . $response->body());
        }

        return $response->json();
    }
}
