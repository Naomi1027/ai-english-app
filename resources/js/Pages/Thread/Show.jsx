import { Head } from '@inertiajs/react'
import { SideMenu } from '../../Components/SideMenu'
import { LogoutButton } from '../../Components/LogoutButton'
import { HiMicrophone, HiSpeakerphone } from 'react-icons/hi'

export default function Top({ threads }) { // threadsを受け取る
    return (
        <>
            <Head title="Show" />
            <div className="flex h-screen overflow-hidden">
                <SideMenu threads={threads} />
                <div className="flex-1 p-4 bg-gray-200 text-white relative">
                    <div className="flex justify-end">
                        <LogoutButton />
                    </div>
                    <div className="flex flex-col h-full justify-between">
                        <div className="flex flex-col space-y-4 overflow-y-auto">
                            <div className="flex items-center">
                                <div className="mr-2 bg-gray-600 p-2 rounded-full">
                                    AI
                                </div>
                                <div className="bg-gray-700 p-2 rounded-lg max-w-xs">
                                    <p>This is a longer dummy text in English that serves as a placeholder for the actual content. It is meant to demonstrate how the layout will look with real text and to ensure that the design accommodates various lengths of text without breaking the layout.</p>
                                </div>
                                <div className="flex items-center ml-2">
                                    <button className="bg-gray-600 p-1 rounded-full">
                                        <HiSpeakerphone size={24} />
                                    </button>
                                    <button className="bg-gray-600 p-1 rounded-full ml-1">
                                        Aあ
                                    </button>
                                </div>
                            </div>
                            <div className="flex justify-end items-center">
                                <div className="bg-blue-600 p-2 rounded-lg max-w-xs">
                                    <p>This is a longer dummy text in English that serves as a placeholder for the actual content. It is meant to demonstrate how the layout will look with real text and to ensure that the design accommodates various lengths of text without breaking the layout.</p>
                                </div>
                                <div className="ml-2 bg-blue-600 p-2 rounded-full">
                                    You
                                </div>
                            </div>
                        </div>
                        <div className="flex justify-end pb-10">
                            <button className="bg-green-600 p-6 rounded-full">
                                <HiMicrophone size={32} />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </>
    )
}
