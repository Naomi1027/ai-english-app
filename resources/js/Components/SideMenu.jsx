import React from 'react';
import { HiPlus, HiChatAlt2 } from "react-icons/hi";

export function SideMenu({ threads, isOpen, onClose }) { // isOpenとonCloseプロパティを追加
    return (
        <div className={`
            ${isOpen ? 'translate-x-0' : '-translate-x-full'}
            md:translate-x-0
            fixed md:relative
            w-64
            bg-orange-600
            text-white
            h-screen
            overflow-y-auto
            transition-transform
            duration-300
            ease-in-out
            z-30
        `}>
            <div className="flex items-center p-4 text-2xl font-bold">
                <img src="/favicon.png" alt="Chat Icon" className="w-8 h-8 mr-2" />
                <span>AIEnglishApp</span>
                <button
                    onClick={onClose}
                    className="ml-auto md:hidden"
                >
                    ✕
                </button>
            </div>
            <ul className="space-y-2">
                <li>
                <a href={ route('thread.store') } className="flex items-center p-2 text-base font-normal text-white hover:bg-orange-400">
                        <HiPlus className="w-6 h-6" />
                        <span className="ml-3">新規英会話作成</span>
                    </a>
                </li>
                {threads.map((thread, index) => ( // threadsをループして表示
                    <li key={index}>
                        <a href={ route('thread.show', { threadId: thread.id }) } className="flex items-center p-2 text-base font-normal text-white hover:bg-orange-400">
                            <HiChatAlt2 className="w-6 h-6" />
                            <span className="ml-3">{thread.title}</span> {/* タイトルを表示 */}
                        </a>
                    </li>
                ))}
            </ul>
        </div>
    );
}
