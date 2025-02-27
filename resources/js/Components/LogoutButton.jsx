import React from 'react';
import { useForm } from '@inertiajs/react';

export function LogoutButton() {
    const { post } = useForm();

    const handleLogout = () => {
        post(route('logout'));
    };

    return (
        <button
            onClick={handleLogout}
            className="bg-gray-300 text-black px-4 py-2 rounded"
        >
            ログアウト
        </button>
    );
}
