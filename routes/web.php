<?php

use App\Http\Controllers\ThreadController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    // トップ画面
    Route::get('/top', [ThreadController::class, 'index'])->name('top');
    // 英会話画面
    Route::get('/thread/{threadId}', [ThreadController::class, 'show'])->name('thread.show');
});
