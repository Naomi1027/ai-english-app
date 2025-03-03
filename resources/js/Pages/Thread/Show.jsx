import { Head } from '@inertiajs/react'
import { SideMenu } from '../../Components/SideMenu'
import { LogoutButton } from '../../Components/LogoutButton'
import { HiMicrophone, HiSpeakerphone } from 'react-icons/hi'
import { useState, useRef, useEffect } from 'react'
import axios from 'axios'

export default function Show({ threads, messages: initialMessages, threadId }) {
    const [messages, setMessages] = useState(initialMessages); // messagesの状態を定義
    const [isRecording, setIsRecording] = useState(false);
    const [isLoading, setIsLoading] = useState(false); // ローディング状態を追加
    const mediaRecorderRef = useRef(null);
    const audioChunksRef = useRef([]);
    const audioRefs = useRef({}); // 音声ファイルの参照を保持
    const [playingAudio, setPlayingAudio] = useState(null);
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

    const handleAudioPlayback = (audioFilePath) => {
        // 現在再生中の音声があれば停止
        if (playingAudio) {
            playingAudio.pause();
            playingAudio.currentTime = 0;
        }

        // 同じ音声を再度クリックした場合は停止して終了
        if (playingAudio?.src.includes(audioFilePath)) {
            setPlayingAudio(null);
            return;
        }

        // 新しい音声を再生
        const audio = new Audio(`/storage/${audioFilePath}`);
        audio.play().catch(error => {
            console.error('音声ファイルの再生に失敗しました:', error);
        });

        // 再生終了時の処理
        audio.onended = () => {
            setPlayingAudio(null);
        };

        setPlayingAudio(audio);
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
        // コンポーネントのアンマウント時に音声を停止
        return () => {
            if (playingAudio) {
                playingAudio.pause();
                playingAudio.currentTime = 0;
            }
        };
    }, [playingAudio]);

    // 最新のメッセージが追加されたときの自動再生
    useEffect(() => {
        const latestMessage = messages[messages.length - 1];
        if (latestMessage && latestMessage.audio_file_path && latestMessage.sender === 2) {
            handleAudioPlayback(latestMessage.audio_file_path);
            // スクロールを一番下に設定
            const messageContainer = document.getElementById('message-container');
            if (messageContainer) {
                messageContainer.scrollTop = messageContainer.scrollHeight; // スクロール位置を一番下に設定
            }
        }
    }, [messages]); // messagesが更新されるたびに実行

    return (
        <>
            <Head title="Show" />
            {isLoading && ( // ローディングアニメーションの表示
                <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                    <div className="loader border-4 border-t-4 border-t-blue-500 border-gray-300 rounded-full w-16 h-16 animate-spin"></div> {/* TailwindCSSを使用したローディングアニメーション */}
                </div>
            )}
            <div className={`flex h-screen overflow-hidden ${isLoading ? 'pointer-events-none' : ''}`}>
                <SideMenu threads={threads} />
                <div className="flex-1 p-4 bg-gray-300 text-white relative">
                    <div className="flex justify-end">
                        <LogoutButton />
                    </div>
                    <div className="flex flex-col h-full justify-between pt-8">
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
                                className={`bg-green-600 p-6 rounded-full ${isRecording ? 'bg-red-600' : ''}`}
                                onClick={handleRecording}
                            >
                                <HiMicrophone size={32} />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    )
}
