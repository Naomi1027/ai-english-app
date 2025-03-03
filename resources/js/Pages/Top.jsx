import { Head, Link } from '@inertiajs/react'
import { SideMenu } from '../Components/SideMenu'
import { LogoutButton } from '../Components/LogoutButton'

export default function Top({ threads }) { // threadsを受け取る
    return (
        <>
            <Head title="Top" />
            <div className="flex h-screen">
            <SideMenu threads={threads} /> {/* threadsをSideMenuに渡す */}
                <div className="flex-1 p-4 bg-gray-300 text-white">
                    <div className="flex justify-end">
                        <LogoutButton />
                    </div>
                    <div className="max-w-3xl mx-auto mt-16 text-gray-800">
                        <h1 className="text-4xl font-bold mb-8 text-center">AI英会話アシスタント</h1>

                        <div className="bg-white rounded-lg p-8 shadow-lg">
                            <p className="text-xl mb-6">
                                AIと英会話の練習ができるプラットフォームへようこそ！
                                24時間365日、いつでもあなたのペースで英会話の練習ができます。
                            </p>

                            <h2 className="text-2xl font-semibold mb-4">主な特徴</h2>
                            <ul className="space-y-4">
                                <li className="flex items-start">
                                    <span className="text-green-500 mr-2">✓</span>
                                    リアルタイムでAIと英会話練習
                                </li>
                                <li className="flex items-start">
                                    <span className="text-green-500 mr-2">✓</span>
                                    文法やボキャブラリーの即時フィードバック
                                </li>
                                <li className="flex items-start">
                                    <span className="text-green-500 mr-2">✓</span>
                                    様々なシチュエーションでの会話練習
                                </li>
                                <li className="flex items-start">
                                    <span className="text-green-500 mr-2">✓</span>
                                    会話履歴の保存と復習機能
                                </li>
                            </ul>

                            <h2 className="text-2xl font-semibold mt-8 mb-4">使い方</h2>
                            <ol className="list-decimal list-inside space-y-3 pl-4">
                                <li>
                                    左サイドメニューの<span className="font-semibold text-blue-500">新規英会話作成</span>ボタンをクリックして、ログインして下さい。
                                </li>
                                <li>
                                    <span className="font-semibold text-blue-500">マイク</span>ボタンをクリックして、AIとの英会話を始めましょう！
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </>
    )
}
