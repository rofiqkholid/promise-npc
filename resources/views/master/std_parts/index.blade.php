@extends('layouts.app')

@section('title', 'Master STD Parts')
@section('page_title', 'Master Data / STD Parts')

@section('content')
<div x-data="{ showImportModal: false }">
    <div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-screwdriver-wrench text-blue-500"></i> Master STD Part Register
            </h2>
            <div class="flex gap-2">
                <button type="button" @click="showImportModal = true" class="px-4 py-2 bg-white text-gray-700 dark:bg-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm font-medium text-sm flex items-center gap-2 rounded-none">
                    <i class="fa-solid fa-file-excel text-green-600"></i> Import Excel
                </button>
                <a href="{{ route('master.std-parts.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2 rounded-none">
                    <i class="fa-solid fa-plus"></i> Add New STD Part
                </a>
            </div>
        </div>

    <div class="p-6">

        <!-- Search Form -->
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <form action="{{ route('master.std-parts.index') }}" method="GET" class="w-full sm:w-auto"
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
                    <input type="text" name="search" x-model="searchQuery" x-ref="searchInput" placeholder="Search STD part name..." 
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
                        <th scope="col" class="px-6 py-4 font-semibold w-16 text-center">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-24">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-24">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stdParts as $part)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group">
                        <td class="px-6 py-4 font-bold text-center text-indigo-600 dark:text-indigo-400">{{ ($stdParts->currentPage() - 1) * $stdParts->perPage() + $loop->iteration }}</td>
                        <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">{{ $part->name }}</td>
                        <td class="px-6 py-4">
                            @if($part->is_active)
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border border-green-200 dark:border-green-800">
                                    Active
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
                                <a href="{{ route('master.std-parts.edit', $part->hashed_id) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('master.std-parts.destroy', $part->hashed_id) }}" method="POST" class="inline" onsubmit="confirmAction(event, 'Permanently delete this STD part?')">
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
                                <p>No STD parts registered yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $stdParts->links() }}
        </div>
    </div>

    <!-- Import Modal -->
    <div x-show="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak style="display: none;">
        <div x-show="showImportModal" @click.away="showImportModal = false" x-transition class="bg-white dark:bg-gray-800 w-full max-w-md shadow-xl border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Import STD Parts</h3>
                <button @click="showImportModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Upload an Excel file (.xlsx, .xls) or CSV to bulk import STD Parts. Please use the provided template to ensure correct formatting.</p>
                    <a href="{{ route('master.std-parts.import.template') }}" class="inline-flex items-center gap-2 px-3 py-1.5 border border-green-600 text-green-600 hover:bg-green-50 dark:border-green-500 dark:text-green-400 dark:hover:bg-green-900/30 text-sm font-medium transition rounded-none">
                        <i class="fa-solid fa-download"></i> Download Template
                    </a>
                </div>
                
                <form action="{{ route('master.std-parts.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select File <span class="text-red-500">*</span></label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="block w-full text-sm text-gray-500 dark:text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-none file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-200 cursor-pointer">
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="showImportModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition text-sm rounded-none">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 transition text-sm flex items-center gap-2 rounded-none">
                            <i class="fa-solid fa-upload"></i> Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
