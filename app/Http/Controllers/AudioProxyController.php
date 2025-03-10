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

            // ファイルパスにai_audioが含まれていない場合は追加
            $fullPath = str_contains($filePath, 'ai_audio/')
                ? $filePath
                : 'ai_audio/' . $filePath;

            Log::info('Fetching audio file from R2: ' . $fullPath);

            // ファイルを3つの方法で試す
            // 1. Storageファサードを使用
            if (Storage::disk('s3')->exists($fullPath)) {
                Log::info('File exists in R2 bucket. Fetching with Storage facade...');
                $fileContents = Storage::disk('s3')->get($fullPath);

                Log::info('File retrieved successfully. Size: ' . strlen($fileContents) . ' bytes');

                return $this->createSuccessResponse($fileContents, $filePath);
            }

            // 2. 直接R2 URLにアクセス - CloudflareStorageのAPI形式
            Log::warning('File not found using Storage facade. Trying direct R2 access (cloudflarestorage.com)...');
            $r2Url = 'https://367be3a2035528943240074d0096e0cd.r2.cloudflarestorage.com/' . $fullPath;

            $response = Http::withHeaders([
                'Accept' => 'audio/wav,audio/*;q=0.9,*/*;q=0.8',
            ])->get($r2Url);

            if ($response->successful()) {
                Log::info('File retrieved via direct R2 cloudflarestorage URL. Size: ' . strlen($response->body()) . ' bytes');
                return $this->createSuccessResponse($response->body(), $filePath);
            }

            // 3. r2.devドメインを試す
            Log::warning('Direct cloudflarestorage access failed. Trying r2.dev domain...');
            $r2DevUrl = 'https://fls-9e57ee01-7e4d-4e84-83fc-2aa82169155b.r2.dev/' . $fullPath;

            $devResponse = Http::withHeaders([
                'Accept' => 'audio/wav,audio/*;q=0.9,*/*;q=0.8',
            ])->get($r2DevUrl);

            if ($devResponse->successful()) {
                Log::info('File retrieved via r2.dev URL. Size: ' . strlen($devResponse->body()) . ' bytes');
                return $this->createSuccessResponse($devResponse->body(), $filePath);
            }

            // 全ての方法が失敗した場合
            Log::error('Audio file not found with any method: ' . $fullPath);
            return response()->json([
                'error' => 'ファイルが見つかりません',
                'path' => $fullPath,
                'methods_tried' => [
                    'storage_facade' => 'failed',
                    'r2_cloudflarestorage_url' => $r2Url . ' - ' . $response->status(),
                    'r2_dev_url' => $r2DevUrl . ' - ' . $devResponse->status()
                ]
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
     * 成功レスポンスを生成するヘルパーメソッド
     */
    private function createSuccessResponse($fileContents, $filePath)
    {
        return response($fileContents)
            ->header('Content-Type', 'audio/wav')
            ->header('Content-Disposition', 'inline; filename="' . $filePath . '"')
            ->header('Cache-Control', 'max-age=86400')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, X-Requested-With');
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
