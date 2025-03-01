<?php

use App\Http\Controllers\ThreadController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;


require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    // トップ画面
    Route::get('/top', [ThreadController::class, 'index'])->name('top');
    // 英会話画面
    Route::get('/thread/{threadId}', [ThreadController::class, 'show'])->name('thread.show');
    // 新規スレッド作成
    Route::get('/thread', [ThreadController::class, 'store'])->name('thread.store');
    // メッセージを保存
    Route::post('/thread/{threadId}/message', [MessageController::class, 'store'])->name('message.store')->where('threadId', '[0-9]+');
});
