@extends('layouts.app')

@section('title', 'Master Menu Management')
@section('page_title', 'Master Data / Menu Management')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-list-ul text-blue-500"></i> Menu Management
        </h2>
        <a href="{{ route('master.menus.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-[13px] flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Add Menu
        </a>
    </div>

    <div class="p-6">

        <!-- Search Form -->
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <form action="{{ route('master.menus.index') }}" method="GET" class="w-full sm:w-auto"
                x-data="{ 
                    searchQuery: '{{ request('search') }}',
                    performSearch() {
                        fetch('?search=' + this.searchQuery)
                        .then(res => res.text())
                        .then(html => {
                            let doc = new DOMParser().parseFromString(html, 'text/html');
                            document.querySelector('tbody').innerHTML = doc.querySelector('tbody').innerHTML;
                            
                            window.history.pushState(null, '', '?search=' + this.searchQuery);
                        });
                    }
                }" @submit.prevent="performSearch()">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text" name="search" x-model="searchQuery" x-ref="searchInput" placeholder="Search title or route..." 
                        @input.debounce.500ms="performSearch()"
                        class="!pl-10 !pr-10 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-none">
                    
                    <button type="button" x-show="searchQuery.length > 0" style="display: none;"
                        @click="searchQuery = ''; performSearch(); $refs.searchInput.focus()"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500 transition outline-none">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-4 py-2 font-semibold">Title</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Route / URL</th>
                        <th scope="col" class="px-4 py-2 font-semibold text-center">Icon</th>
                        <th scope="col" class="px-4 py-2 font-semibold text-center">Order</th>
                        <th scope="col" class="px-4 py-2 font-semibold text-center">Status</th>
                        <th scope="col" class="px-4 py-2 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menus as $menu)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group">
                        <td class="px-4 py-2">
                            @if($menu->parent_id)
                                <span class="text-gray-400 mr-2"><i class="fa-solid fa-turn-up fa-rotate-90"></i></span>
                                <span class="text-slate-600 dark:text-slate-300">{{ $menu->title }}</span>
                            @else
                                <span class="font-bold text-slate-900 dark:text-white">{{ $menu->title }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 font-mono text-xs text-slate-500">
                            {{ $menu->route ?: '-' }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            @if($menu->icon)
                                <i class="{{ $menu->icon }} text-lg"></i>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center font-semibold text-slate-700 dark:text-slate-300">
                            {{ $menu->sort_order }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            @if($menu->is_active)
                                <span class="px-2 py-1 text-xs border bg-green-100 text-green-800 border-green-200 dark:bg-green-900/40 dark:text-green-400 dark:border-green-800">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs border bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-900/40 dark:text-gray-400 dark:border-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
                                <a href="{{ route('master.menus.edit', $menu->hashed_id) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('master.menus.destroy', $menu->hashed_id) }}" method="POST" class="inline" onsubmit="confirmAction(event, 'Delete ini secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 transition" title="Delete">
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

    <div class="px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
    </div>
</div>
@endsection
