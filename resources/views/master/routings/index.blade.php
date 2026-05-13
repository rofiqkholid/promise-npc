@extends('layouts.app')

@section('title', 'Master Data Routing')
@section('page_title', 'Master Data / Routing (Flow Process)')

@push('styles')
    <style>
        .sortable-item:last-child .process-arrow {
            display: none;
        }
        .sortable-ghost {
            opacity: 0.4;
        }
    </style>
@endpush

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-route text-blue-500"></i> Register Master Routing
        </h2>
        <a href="{{ route('master.routings.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Add Routing
        </a>
    </div>

    <div class="p-6">

        <!-- Search Form -->
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <form action="{{ route('master.routings.index') }}" method="GET" class="w-full sm:w-auto"
                x-data="{ 
                    searchQuery: '{{ request('search') }}',
                    performSearch() {
                        fetch('?search=' + this.searchQuery)
                        .then(res => res.text())
                        .then(html => {
                            let doc = new DOMParser().parseFromString(html, 'text/html');
                            document.querySelector('tbody').innerHTML = doc.querySelector('tbody').innerHTML;
                            
                            let pagination = document.querySelector('.mt-4 nav') || document.querySelector('.p-4.border-t nav');
                            let newPagination = doc.querySelector('.mt-4 nav') || doc.querySelector('.p-4.border-t nav');
                            
                            if(pagination && newPagination) {
                                pagination.parentElement.innerHTML = newPagination.parentElement.innerHTML;
                            } else if (newPagination) {
                                let container = document.querySelector('.bg-white.shadow-sm');
                                let div = document.createElement('div');
                                div.className = 'p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50';
                                div.appendChild(newPagination.parentElement.children[0]);
                                container.appendChild(div);
                            } else if (pagination) {
                                pagination.parentElement.innerHTML = '';
                            }
                            
                            // Re-initialize sortable
                            if (typeof Sortable !== 'undefined') {
                                document.querySelectorAll('.sortable-container').forEach(container => {
                                    new Sortable(container, {
                                        handle: '.cursor-move',
                                        animation: 150,
                                        ghostClass: 'sortable-ghost',
                                        onEnd: window.sortableOnEndFunction
                                    });
                                });
                            }
                            
                            window.history.pushState(null, '', '?search=' + this.searchQuery);
                        });
                    }
                }" @submit.prevent="performSearch()">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text" name="search" x-model="searchQuery" x-ref="searchInput" placeholder="Search part no, name..." 
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
                        <th scope="col" class="px-6 py-4 font-semibold w-16">#</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Flow Process (Routing)</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($routings as $index => $routing)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group">
                        <td class="px-6 py-4 text-slate-800 dark:text-slate-200 text-sm">{{ $routings->firstItem() + $index }}</td>
                        <td class="px-6 py-4 text-blue-600 dark:text-blue-400 font-semibold text-sm">{{ optional($routing->part)->part_no ?? 'N/A' }}</td>
                        <td class="px-6 py-4">
                            <div class="text-slate-600 dark:text-slate-400 text-sm font-medium">{{ optional($routing->part)->part_name ?? '-' }}</div>
                            <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                <i class="fa-solid fa-car text-gray-400"></i>
                                {{ optional(optional($routing->part)->vehicleModel)->name ?? 'Unknown Model' }}
                                <span class="text-gray-400 mx-1">|</span>
                                <i class="fa-solid fa-building text-gray-400 text-[10px]"></i>
                                {{ optional(optional(optional($routing->part)->vehicleModel)->customer)->code ?? 'Unknown Customer' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-700 dark:text-slate-300 text-sm">
                            <div class="flex flex-wrap gap-2 items-center sortable-container" data-part-id="{{ $routing->part_id }}">
                                @foreach($routing->processes as $i => $procRouting)
                                    <div class="sortable-item flex items-center gap-2 cursor-move group/badge" data-id="{{ $procRouting->id }}">
                                        <span class="inline-flex items-center px-2.5 py-1 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs font-semibold text-slate-700 dark:text-slate-300 group-hover/badge:bg-blue-50 dark:group-hover/badge:bg-blue-900/30 group-hover/badge:border-blue-300 dark:group-hover/badge:border-blue-700 transition" title="Drag to change sequence">
                                            <i class="fa-solid fa-grip-vertical text-slate-400 mr-1.5 group-hover/badge:text-blue-500"></i>
                                            {{ optional($procRouting->process)->process_name ?? 'Unknown' }}
                                        </span>
                                        <i class="fa-solid fa-arrow-right text-slate-300 dark:text-slate-500 text-xs process-arrow"></i>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
                                <a href="{{ route('master.routings.edit', $routing->part_id) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('master.routings.destroy', $routing->part_id) }}" method="POST" class="inline" onsubmit="confirmAction(event, 'Are you sure you want to delete routing for this part?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 transition" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-solid fa-route text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No master routing data yet. Click "Add Routing" to start.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($routings->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        {{ $routings->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Sortable !== 'undefined') {
            const containers = document.querySelectorAll('.sortable-container');
            
            containers.forEach(container => {
                new Sortable(container, {
                    handle: '.cursor-move',
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    onEnd: window.sortableOnEndFunction = function (evt) {
                        const itemEl = evt.item;
                        const parentEl = evt.to;
                        const partId = parentEl.getAttribute('data-part-id');
                        
                        // Collect new order
                        const order = [];
                        parentEl.querySelectorAll('.sortable-item').forEach(item => {
                            order.push(item.getAttribute('data-id'));
                        });

                        // Send AJAX
                        fetch("{{ route('master.routings.reorder') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ order: order })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.success) {
                                // Show tiny success toast using the layout's Toast mechanism if available, or just ignore since it's saved.
                            } else {
                                alert('Failed to save new sequence.');
                            }
                        })
                        .catch(err => {
                            console.error('Error reordering:', err);
                            alert('Terjadi kesalahan saat menghubungi server.');
                        });
                    }
                });
            });
        }
    });
</script>
@endpush
