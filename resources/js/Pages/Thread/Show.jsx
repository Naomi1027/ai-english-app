import React from 'react'
import { Head } from '@inertiajs/react'
import { SideMenu } from '../../Components/SideMenu'
import { HiMicrophone, HiSpeakerphone } from 'react-icons/hi'
import { useState, useRef, useEffect } from 'react'
import axios from 'axios'

// エラーバウンダリーコンポーネント
class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true };
    }

    componentDidCatch(error, errorInfo) {
        console.error('Error caught by boundary:', error, errorInfo);
    }

    render() {
        if (this.state.hasError) {
            return <div className="text-red-500">エラーが発生しました。ページをリロードしてください。</div>;
        }

        return this.props.children;
    }
}

export default function Show({ threads, messages: initialMessages, threadId }) {
    const [messages, setMessages] = useState(initialMessages); // messagesの状態を定義
    const [isRecording, setIsRecording] = useState(false);
    const [isLoading, setIsLoading] = useState(false); // ローディング状態を追加
    const mediaRecorderRef = useRef(null);
    const audioChunksRef = useRef([]);
    const audioRefs = useRef({}); // 音声ファイルの参照を保持
    const [shouldPlayAudio, setShouldPlayAudio] = useState(true); // 音声再生フラグを追加

    const handleRecording = async () => {
        if (isRecording) {
            // 録音停止
            mediaRecorderRef.current.stop();
            setIsRecording(false);
        } else {
            // 録音開始
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            mediaRecorderRef.current = new MediaRecorder(stream);
            mediaRecorderRef.current.ondataavailable = (event) => {
                audioChunksRef.current.push(event.data);
            };
            mediaRecorderRef.current.onstop = async () => {
                const audioBlob = new Blob(audioChunksRef.current, { type: 'audio/wav' });
                const formData = new FormData();
                formData.append('audio', audioBlob, 'audio.wav');

                setIsLoading(true); // ローディング開始

                try {
                    // 音声データを送信
                    await axios.post(`/thread/${threadId}/message`, formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data',
                        },
                    });
                    window.location.reload();
                } catch (error) {
                    alert('音声データの送信に失敗しました');
                    console.error('Error sending audio data:', error);
                } finally {
                    setIsLoading(false); // ローディング終了
                }

                audioChunksRef.current = []; // チャンクをリセット
            };
            mediaRecorderRef.current.start();
            setIsRecording(true);
        }
    };

    const handleAudioPlayback = async (audioFilePath) => {
        if (audioRefs.current[audioFilePath]) {
            // 既に再生中の場合は停止
            audioRefs.current[audioFilePath].pause();
            delete audioRefs.current[audioFilePath];
            console.log('Audio playback stopped');
        } else {
            try {
                // ファイル名を抽出
                const fileName = audioFilePath.includes('/')
                    ? audioFilePath.split('/').pop()
                    : audioFilePath;

                console.log('Extracted file name:', fileName);

                // APIプロキシエンドポイントのみを使用
                const proxyPath = import.meta.env.VITE_AUDIO_PROXY_PATH || '/api/audio';
                const audioApiUrl = `${window.location.origin}${proxyPath}/${fileName}`;
                console.log('Using API proxy method:', audioApiUrl);

                const apiResponse = await fetch(audioApiUrl);
                if (!apiResponse.ok) {
                    throw new Error(`API error: ${apiResponse.status}`);
                }

                const audioBlob = await apiResponse.blob();
                console.log('Successfully fetched audio via API proxy');

                // 音声の再生
                const audioUrl = URL.createObjectURL(audioBlob);
                console.log('Created object URL for audio');

                // 新たに再生
                const audio = new Audio();
                audio.src = audioUrl;

                // メタデータがロードされたら再生開始
                audio.onloadedmetadata = () => {
                    audioRefs.current[audioFilePath] = audio;
                    audio.play().catch(error => {
                        console.error('音声ファイルの再生に失敗しました:', error);
                    });
                };

                audio.onended = () => {
                    URL.revokeObjectURL(audioUrl); // Blobの解放
                    delete audioRefs.current[audioFilePath];
                };

                audio.onerror = (e) => {
                    console.error('音声ファイルの読み込みに失敗しました:', e);
                    URL.revokeObjectURL(audioUrl);
                    delete audioRefs.current[audioFilePath];
                };
            } catch (error) {
                console.error('音声ファイルの処理に失敗しました:', error);
            }
        }
    };

    const handleTranslate = async (messageId) => {
        const message = messages.find(msg => msg.id === messageId);

        if (!message.message_ja) {
            // message_jaが無い場合のみリクエストを送信
            try {
                const response = await axios.post(`/thread/${threadId}/message/${messageId}/translate`);
                // message_jaに翻訳結果を保持
                const updatedMessages = messages.map(msg =>
                    msg.id === messageId ? { ...msg, message_ja: response.data.message, showJapanese: true } : msg // 初回クリックで日本語を表示
                );
                // ステートを更新
                setMessages(updatedMessages);
                setShouldPlayAudio(false); // 音声再生を防ぐフラグを設定
            } catch (error) {
                console.error('翻訳に失敗しました:', error);
                alert('翻訳に失敗しました');
            }
        } else {
            // message_jaがある場合は表示を切り替え
            const updatedMessages = messages.map(msg =>
                msg.id === messageId ? { ...msg, showJapanese: !msg.showJapanese } : msg
            );
            setMessages(updatedMessages);
        }
    };

    useEffect(() => {
        const playLatestMessage = async () => {
            // 最新のメッセージの音声ファイルを再生
            const latestMessage = messages[messages.length - 1];
            if (latestMessage && latestMessage.audio_file_path) {
                await handleAudioPlayback(latestMessage.audio_file_path);

                // スクロールを一番下に設定
                const messageContainer = document.getElementById('message-container');
                if (messageContainer) {
                    messageContainer.scrollTop = messageContainer.scrollHeight;
                }
            }
        };

        playLatestMessage();
    }, []);

    return (
        <ErrorBoundary>
            <Head title="Show" />
            {isLoading && (
                <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                    <div className="loader border-4 border-t-4 border-t-blue-500 border-gray-300 rounded-full w-16 h-16 animate-spin"></div>
                </div>
            )}
            <div className={`flex h-screen overflow-hidden ${isLoading ? 'pointer-events-none' : ''}`}>
                <SideMenu threads={threads} />
                <div className="flex-1 p-4 bg-gray-800 text-white relative">
                    <div className="flex flex-col h-full justify-between">
                        <div id="message-container" className="flex flex-col space-y-4 overflow-y-auto"> {/* IDを追加 */}
                            {messages.map((message, index) => (
                                message.sender === 1 ? (
                                    // ユーザのメッセージ
                                    <div key={index} className="flex justify-end items-center">
                                        <div className="bg-blue-600 p-2 rounded-lg max-w-xs">
                                            <p>{message.message_en}</p>
                                        </div>
                                        <div className="ml-2 bg-blue-600 p-2 rounded-full">
                                            You
                                        </div>
                                    </div>
                                ) : (
                                    // AIのメッセージ
                                    <div key={index} className="flex items-center">
                                        <div className="mr-2 bg-gray-600 p-2 rounded-full">
                                            AI
                                        </div>
                                        <div className="bg-gray-700 p-2 rounded-lg max-w-xs">
                                            <p>{message.showJapanese ? message.message_ja : message.message_en}</p> {/* 表示切り替え */}
                                        </div>
                                        <div className="flex items-center ml-2">
                                            <button
                                                className="bg-gray-600 p-1 rounded-full"
                                                onClick={() => handleAudioPlayback(message.audio_file_path)} // 音声再生のハンドラを追加
                                            >
                                                <HiSpeakerphone size={24} />
                                            </button>
                                            <button
                                                className="bg-gray-600 p-1 rounded-full ml-1"
                                                onClick={() => handleTranslate(message.id)}
                                            >
                                                A訳
                                            </button>
                                        </div>
                                    </div>
                                )
                            ))}
                        </div>
                        <div className="flex justify-end pb-10">
                            <button
                                className={`bg-gray-600 p-6 rounded-full ${isRecording ? 'bg-red-600' : 'bg-green-600'}`}
                                onClick={handleRecording}
                            >
                                <HiMicrophone size={32} />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </ErrorBoundary>
    )
}
