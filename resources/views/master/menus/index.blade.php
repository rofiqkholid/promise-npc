@extends('layouts.app')

@section('title', 'Master Menu Management')
@section('page_title', 'Master Data / Menu Management')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-list-ul text-blue-500"></i> Menu Management
        </h2>
        <a href="{{ route('master.menus.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Add Menu
        </a>
    </div>

    <div class="p-6">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Title</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Route / URL</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Icon</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Order</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menus as $menu)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group">
                        <td class="px-6 py-4">
                            @if($menu->parent_id)
                                <span class="text-gray-400 mr-2"><i class="fa-solid fa-turn-up fa-rotate-90"></i></span>
                                <span class="text-slate-600 dark:text-slate-300">{{ $menu->title }}</span>
                            @else
                                <span class="font-bold text-slate-900 dark:text-white">{{ $menu->title }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-mono text-xs text-slate-500">
                            {{ $menu->route_name ?: '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($menu->icon)
                                <i class="{{ $menu->icon }} text-lg"></i>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center font-semibold text-slate-700 dark:text-slate-300">
                            {{ $menu->order }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($menu->is_active)
                                <span class="px-2 py-1 text-xs rounded border bg-green-100 text-green-800 border-green-200 dark:bg-green-900/40 dark:text-green-400 dark:border-green-800">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded border bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-900/40 dark:text-gray-400 dark:border-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
                                <a href="{{ route('master.menus.edit', $menu->hashed_id) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 rounded-md transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('master.menus.destroy', $menu->hashed_id) }}" method="POST" class="inline" onsubmit="confirmAction(event, 'Delete ini secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-md transition" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No menu data registered yet.</p>
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
