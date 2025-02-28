<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Http\Requests\UpdateThreadRequest;
use App\Models\Message;
use App\Models\Thread;

class ThreadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): InertiaResponse
    {
        $threads = Thread::orderBy('id', 'desc')->get(); // スレッドデータを取得
        return Inertia::render('Top', [
            'threads' => $threads, // フロントエンドに渡す
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $thread = Thread::create([
            'title' => now()->format('Y-m-d H:i:s'),
        ]);

        return redirect()->route('thread.show', ['threadId' => $thread->id]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $threadId): InertiaResponse
    {
        $messages = Message::where('thread_id', $threadId)->get(); // メッセージデータを取得
        $threads = Thread::orderBy('id', 'desc')->get(); // スレッドデータを取得
        return Inertia::render('Thread/Show', [
            'threads' => $threads, // フロントエンドに渡す
            'messages' => $messages, // フロントエンドに渡す
            'threadId' => $threadId, // フロントエンドに渡す
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Thread $thread)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateThreadRequest $request, Thread $thread)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Thread $thread)
    {
        //
    }
}
