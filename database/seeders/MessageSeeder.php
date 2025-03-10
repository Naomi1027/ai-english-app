<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Message;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //メッセージデータの作成
        // Message::create([
        //     'thread_id' => 1,
        //     'message_en' => 'Hello, how are you?',
        //     'message_ja' => 'こんにちは、元気ですか？',
        //     'sender' => '1', //ユーザー
        //     'audio_file_path' => 'audio/1.mp3',
        // ]);
        // Message::create([
        //     'thread_id' => 1,
        //     'message_en' => 'I am fine, thank you.',
        //     'message_ja' => '元気です、ありがとう。',
        //     'sender' => '2', //AI
        //     'audio_file_path' => 'audio/2.mp3',
        // ]);
        // Message::create([
        //     'thread_id' => 1,
        //     'message_en' => 'What is your name?',
        //     'message_ja' => 'あなたの名前は何ですか？',
        //     'sender' => '1', //ユーザー
        //     'audio_file_path' => 'audio/3.mp3',
        // ]);
    }
}
