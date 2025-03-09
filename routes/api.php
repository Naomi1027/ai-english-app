<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AudioProxyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 音声ファイルプロキシルート
Route::get('/audio/{filePath}', [AudioProxyController::class, 'getAudio']);

// CORSプリフライトリクエスト対応
Route::options('/audio/{filePath}', [AudioProxyController::class, 'handleOptions']);
Route::options('/audio', [AudioProxyController::class, 'handleOptions']);
