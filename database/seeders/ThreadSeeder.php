<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Thread;

class ThreadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //スレッドデータの作成
        Thread::create([
            'title' => '英語学習',
        ]);
        Thread::create([
            'title' => '英会話の練習',
        ]);
    }
}
