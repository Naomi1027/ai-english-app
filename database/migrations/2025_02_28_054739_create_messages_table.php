<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('thread_id')->unsigned()->comment('スレッドID');
            $table->text('message_en')->nullable()->comment('英語のメッセージ');
            $table->text('message_ja')->nullable()->comment('日本語のメッセージ');
            $table->smallInteger('sender')->comment('送信者 1:ユーザー 2:AI');
            $table->string('audio_file_path')->nullable()->comment('音声ファイルのパス');
            $table->timestamps();

            // 外部キー制約の追加
            $table->foreign('thread_id')->references('id')->on('threads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
