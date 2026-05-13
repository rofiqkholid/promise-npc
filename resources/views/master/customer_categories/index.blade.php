@extends('layouts.app')

@section('title', 'Customer Category Mapping')
@section('page_title', 'Master Data / Customer Category')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-link text-blue-500"></i> Customer Category Register
        </h2>
        <a href="{{ route('master.customer-categories.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Mapping New
        </a>
    </div>

    <div class="p-6">

        <!-- Search Form -->
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <form action="{{ route('master.customer-categories.index') }}" method="GET" class="w-full sm:w-auto"
                x-data="{ 
                    searchQuery: '{{ request('search') }}',
                    performSearch() {
                        fetch('?search=' + this.searchQuery)
                        .then(res => res.text())
                        .then(html => {
                            let doc = new DOMParser().parseFromString(html, 'text/html');
                            document.querySelector('tbody').innerHTML = doc.querySelector('tbody').innerHTML;
                            
                            let pagination = document.querySelector('.mt-4 nav');
                            let newPagination = doc.querySelector('.mt-4 nav');
                            if(pagination && newPagination) {
                                pagination.innerHTML = newPagination.innerHTML;
                            } else if (newPagination) {
                                let container = document.querySelector('.p-6');
                                let div = document.createElement('div');
                                div.className = 'mt-4';
                                div.appendChild(newPagination);
                                container.appendChild(div);
                            }
                            
                            window.history.pushState(null, '', '?search=' + this.searchQuery);
                        });
                    }
                }" @submit.prevent="performSearch()">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text" name="search" x-model="searchQuery" x-ref="searchInput" placeholder="Search customer, term..." 
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
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Customer</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Customer Category Term</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Mapping to Internal</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-24">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group">
                        <td class="px-6 py-4 font-medium text-slate-500 dark:text-slate-400">{{ ($categories->currentPage() - 1) * $categories->perPage() + $loop->iteration }}</td>
                        <td class="px-6 py-4 font-medium text-slate-800 dark:text-gray-200">
                            {{ $category->customer ? $category->customer->code : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $category->name }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                <i class="fa-solid fa-arrow-right-arrow-left text-[10px]"></i>
                                {{ $category->internalCategory ? $category->internalCategory->name : 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
                                <a href="{{ route('master.customer-categories.edit', $category->hashed_id) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('master.customer-categories.destroy', $category->hashed_id) }}" method="POST" class="inline" onsubmit="confirmAction(event, 'Permanently delete this mapping?')">
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
                        <td colspan="5" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No customer category mapping data registered yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection

