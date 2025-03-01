<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

class ApiService
{
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
}
