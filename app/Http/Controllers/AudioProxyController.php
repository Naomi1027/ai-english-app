<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AudioProxyController extends Controller
{
    /**
     * R2から音声ファイルを取得してレスポンスとして返す
     *
     * @param string $filePath
     * @return \Illuminate\Http\Response
     */
    public function getAudio($filePath)
    {
        try {
            // ファイル名をデコード
            $filePath = urldecode($filePath);

            // S3ディスク（R2に接続）からファイルを取得
            $fullPath = 'ai_audio/' . $filePath;
            Log::info('Fetching audio file from R2: ' . $fullPath);

            // 複数の取得方法を試す
            // 方法1: Storageファサードを使用
            if (Storage::disk('s3')->exists($fullPath)) {
                Log::info('File exists in R2 bucket. Fetching...');
                $fileContents = Storage::disk('s3')->get($fullPath);

                Log::info('File retrieved successfully. Size: ' . strlen($fileContents) . ' bytes');

                // Content-Typeを設定（wavファイルの場合）
                return response($fileContents)
                       ->header('Content-Type', 'audio/wav')
                       ->header('Content-Disposition', 'inline; filename="' . $filePath . '"')
                       ->header('Cache-Control', 'max-age=86400') // 24時間キャッシュ
                       ->header('Access-Control-Allow-Origin', '*') // CORSの許可
                       ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                       ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With');
            }

            // 方法2: 直接R2 URLにアクセス（フォールバック）
            Log::warning('File not found in S3 disk. Trying direct R2 access...');
            $r2Url = 'https://367be3a2035528943240074d0096e0cd.r2.cloudflarestorage.com/' . $fullPath;

            $response = Http::get($r2Url);

            if ($response->successful()) {
                Log::info('File retrieved via direct R2 access. Size: ' . strlen($response->body()) . ' bytes');

                return response($response->body())
                       ->header('Content-Type', 'audio/wav')
                       ->header('Content-Disposition', 'inline; filename="' . $filePath . '"')
                       ->header('Cache-Control', 'max-age=86400')
                       ->header('Access-Control-Allow-Origin', '*') // CORSの許可
                       ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
                       ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With');
            }

            // 両方の方法が失敗した場合
            Log::error('Audio file not found with either method: ' . $fullPath);
            return response()->json([
                'error' => 'ファイルが見つかりません',
                'path' => $fullPath,
                'r2_url_tried' => $r2Url,
                'r2_response_status' => $response->status()
            ], 404)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With');

        } catch (\Exception $e) {
            Log::error('Failed to get audio file: ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'ファイルの取得に失敗しました',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With');
        }
    }

    /**
     * OPTIONS リクエストのハンドリング (CORS プリフライトリクエスト対応)
     *
     * @return \Illuminate\Http\Response
     */
    public function handleOptions()
    {
        return response('', 200)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With')
            ->header('Access-Control-Max-Age', '86400'); // 24時間
    }
}
