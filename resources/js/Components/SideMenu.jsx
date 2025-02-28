import { Sidebar } from "flowbite-react";
import { HiPlus, HiChatAlt2, HiOutlineChat } from "react-icons/hi";

export function SideMenu({ threads }) { // threadsを受け取る
    return (
        <div className="w-64 bg-orange-600 text-white h-screen overflow-y-auto">
            <div className="flex items-center p-4 text-2xl font-bold">
                <img src="/favicon.png" alt="Chat Icon" className="w-8 h-8 mr-2" />
                AIEnglishApp
            </div>
            <ul className="space-y-2">
                <li>
                    <a href="#" className="flex items-center p-2 text-base font-normal text-white hover:bg-orange-500">
                        <HiPlus className="w-6 h-6" />
                        <span className="ml-3">新規スレッド作成</span>
                    </a>
                </li>
                {threads.map((thread, index) => ( // threadsをループして表示
                    <li key={index}>
                        <a href="#" className="flex items-center p-2 text-base font-normal text-white hover:bg-orange-500">
                            <HiChatAlt2 className="w-6 h-6" />
                            <span className="ml-3">{thread.title}</span> {/* タイトルを表示 */}
                        </a>
                    </li>
                ))}
            </ul>
        </div>
    );
}
