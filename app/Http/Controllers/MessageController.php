<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Http\Services\ApiService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    public function store(Request $request, int $threadId)
    {
        //音声データを保存
        if ($request->hasFile('audio')) {
            $audio = $request->file('audio');
            // ファイル名を日時に指定して保存
            $timestamp = now()->format('YmdHis');
            $path = Storage::disk('s3')->putFileAs(
                'audio',
                $audio,
                "audio_{$timestamp}.wav"
            );

            // データベースに保存する処理
            $message = Message::create([
                'thread_id' => $threadId,
                'message_en' => 'dummy',
                'message_ja' => '',
                'audio_file_path' => $path,
                'sender' => 1, // ユーザー
            ]);

            // 音声データをAPIに送信
            $apiService = new ApiService();
            try {
                $response = $apiService->callWhiperApi($path);

                if (isset($response['text'])) {
                    $message->update([
                        'message_en' => $response['text'],
                    ]);
                } else {
                    Log::error('API response does not contain text field', ['response' => $response]);
                    return response()->json(['message' => 'API回答の処理に失敗しました'], 500);
                }
            } catch (\Exception $e) {
                Log::error('API call failed', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'API呼び出しに失敗しました'], 500);
            }

            // 過去のメッセージを取得
            $messages = Message::where('thread_id', $threadId)->get();
            // GPTにAPIリクエスト
            try {
                $gptResponse = $apiService->callGptApi($messages);
                $aiMessageText = $gptResponse['choices'][0]['message']['content'];
                // データベースに保存する処理を追加
                $aiMessage = Message::create([
                    'thread_id' => $threadId,
                    'message_en' => $aiMessageText,
                    'message_ja' => '',
                    'audio_file_path' => '',
                    'sender' => 2, // AI
                ]);
            } catch (\Exception $e) {
                Log::error('API call failed', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'API呼び出しに失敗しました'], 500);
            }

            // TTSにAPIリクエスト
            try {
            $aiAudioFilePath = $apiService->callTtsApi($aiMessageText);
            // 音声ファイルパスを上書き
            $aiMessage->update([
                'audio_file_path' => $aiAudioFilePath,
            ]);
        } catch (\Exception $e) {
            Log::error('API call failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['message' => '音声データが保存されました'], 200);
    }
    return response()->json(['message' => '音声データが保存されませんでした'], 400);
    }

    public function translate(Request $request, int $threadId, int $messageId)
    {
        // メッセージを取得
        $message = Message::find($messageId);

        if (!$message) {
            return response()->json(['message' => 'メッセージが見つかりませんでした'], 404);
        }

        // GPTにAPIリクエスト
        $apiService = new ApiService();
        try {
        $gptResponse = $apiService->callTranslateApi($message->message_en);

        if (!isset($gptResponse['choices'][0]['message']['content'])) {
            Log::error('API response does not contain content field', ['response' => $gptResponse]);
            return response()->json(['message' => '翻訳の処理に失敗しました'], 500);
        }

        $aiMessageJa = $gptResponse['choices'][0]['message']['content'];
        $message->update([
            'message_ja' => $aiMessageJa
        ]);

        return response()->json(['message' => $aiMessageJa], 200);

        } catch (\Exception $e) {
            Log::error('API call failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => '翻訳に失敗しました'], 500);
        }
    }
}
