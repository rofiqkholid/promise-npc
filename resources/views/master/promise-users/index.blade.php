@extends('layouts.app')

@section('title', 'All Promise Users')
@section('page_title', 'User Management / All Promise Users')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-users text-blue-500"></i> All Promise Users
        </h2>
        <a href="{{ route('master.promise-users.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i> Add User
        </a>
    </div>

    <div class="p-6">

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Email</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Registered At</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($users as $user)
                    <tr class="bg-white dark:bg-gray-800 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group">
                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-slate-100 dark:bg-gray-700 text-slate-500 dark:text-gray-400 flex items-center justify-center border border-slate-200 dark:border-gray-600">
                                    <i class="fa-solid fa-user text-xs"></i>
                                </div>
                                {{ $user->name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
                                <a href="{{ route('master.promise-users.edit', $user) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 transition" title="Edit Profil">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                @if(auth()->id() !== $user->nik)
                                <form action="{{ route('master.promise-users.destroy', $user) }}" method="POST" class="inline" onsubmit="confirmAction(event, 'Delete this user permanently from the Promise system?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 transition" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No users registered yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
