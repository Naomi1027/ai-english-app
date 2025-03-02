<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

class ApiService
{
    /**
     * @param string $audioFilePath
     */
    public function callWhiperApi($audioFilePath)
    {
        $filePath = storage_path('app/public/' . $audioFilePath);
        if (!file_exists($filePath)) {
            throw new \Exception('Audio file not found: ' . $audioFilePath);
        }

        $response = Http::attach(
                'file',
                file_get_contents($filePath),
                'audio.wav'
            )
            ->withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                // 'Content-Type' => 'multipart/form-data',
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
            'content' => 'You are a helpful English teacher. Please speak English.',
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
            // alloy, ash, coral, echo, fable, onyx, nova, sage, shimmer
            'voice' => 'shimmer',
            'response_format' => 'wav',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to call API: ' . $response->body());
        }

        // 音声ファイルの保存
        $fileName = 'speech_' . now()->format('Ymd_His') . '.wav';
        $filePath = storage_path('app/public/ai_audio/' . $fileName);

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, $response->body());

        // 修正: 返すパスを相対パスに変更
        return 'ai_audio/' . $fileName; // 保存した音声のファイルパスを相対パスで返す
    }
}
