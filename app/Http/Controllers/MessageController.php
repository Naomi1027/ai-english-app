<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Http\Services\ApiService;

class MessageController extends Controller
{
    public function store(Request $request, int $threadId)
    {
        //音声データを保存
        if ($request->hasFile('audio')) {
            $audio = $request->file('audio');
            // ファイル名を日時に指定して保存
            $timestamp = now()->format('YmdHis');
            $path = $audio->storeAs('audio', "audio_{$timestamp}.wav", 'public'); // 音声データを保存

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
            $response = $apiService->callWhiperApi($path);
            $message_en = $response['text'];

            $message->update([
                'message_en' => $message_en,
            ]);


        return response()->json(['message' => '音声データが保存されました'], 200);
    }
    return response()->json(['message' => '音声データが保存されませんでした'], 400);
    }
}
