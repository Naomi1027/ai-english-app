import { Sidebar } from "flowbite-react";
import { HiPlus, HiChatAlt2, HiOutlineChat } from "react-icons/hi";

export function SideMenu() {
    return (
        <div className="w-64 bg-orange-600 text-white">
            <div className="flex items-center p-4 text-2xl font-bold">
                <HiOutlineChat className="w-8 h-8 mr-2" />
                AIEnglishApp
            </div>
            <ul className="space-y-2">
                <li>
                    <a href="#" className="flex items-center p-2 text-base font-normal text-white hover:bg-green-600">
                        <HiPlus className="w-6 h-6" />
                        <span className="ml-3">新規スレッド作成</span>
                    </a>
                </li>
                {["英会話スレッド1", "英会話スレッド2", "英会話スレッド3", "英会話スレッド4"].map((thread, index) => (
                    <li key={index}>
                        <a href="#" className="flex items-center p-2 text-base font-normal text-white hover:bg-green-600">
                            <HiChatAlt2 className="w-6 h-6" />
                            <span className="ml-3">{thread}</span>
                        </a>
                    </li>
                ))}
            </ul>
        </div>
    );
}
