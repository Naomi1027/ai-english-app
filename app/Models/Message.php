<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    // フィラブルプロパティの追加
    protected $fillable = [
        'thread_id',
        'message_en',
        'message_ja',
        'sender',
        'audio_file_path'
    ];

    // threadメソッドを追加
    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }
}
