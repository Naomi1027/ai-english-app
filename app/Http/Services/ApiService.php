<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;

class ApiService
{
    public function callWhiperApi($audioFilePath)
    {
        $response = Http::attach(
                'file',
                file_get_contents(
                    storage_path('app/public/' . $audioFilePath)
                ),
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

        return $response->json();
    }
}
