@extends('layouts.app')

@section('title', 'Add Part')
@section('page_title', 'Master Data / Event / Add Part')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-cube text-blue-500"></i> Form Add Part Output
            </h2>
            <span class="text-sm font-medium bg-blue-100 text-blue-800 py-1 px-3">{{ optional($event->customerCategory)->name ?? 'Event' }}</span>
        </div>

        <form action="{{ route('events.parts.store', $event->hashed_id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Search Data -->
                <div class="col-span-1 md:col-span-2 space-y-1 relative">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Search Part No / Name of DB Drawing (Optional)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-search text-xs"></i>
                        </div>
                        <input type="text" id="part_search" autocomplete="off"
                            class="w-full border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                            style="padding-left: 2.5rem;"
                            placeholder="Type Part No or Part Name to search...">
                        
                        <!-- Search Results Dropdown -->
                        <div id="search_results" class="hidden absolute z-30 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg max-h-60 overflow-y-auto">
                            <!-- Items go here -->
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 italic mt-1">*If part is not found, type manually in the field below.</p>
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        PO No
                    </label>
                    <input type="text" name="po_no" value="{{ $event->po_no }}" readonly
                        class="w-full border-gray-300 dark:border-gray-600 shadow-sm sm:text-sm dark:bg-gray-800 dark:text-gray-400 bg-gray-100 text-gray-500 cursor-not-allowed focus:ring-0 focus:border-gray-300">
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Part No <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="part_no_input" name="part_no" required value="{{ old('part_no') }}" readonly
                        class="w-full border-gray-300 dark:border-gray-600 shadow-sm sm:text-sm dark:bg-gray-800 dark:text-gray-400 bg-gray-100 text-gray-500 cursor-not-allowed focus:ring-0 focus:border-gray-300"
                        placeholder="Select from search above...">
                    @error('part_no') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Part Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="part_name_input" name="part_name" required value="{{ old('part_name') }}" readonly
                        class="w-full border-gray-300 dark:border-gray-600 shadow-sm sm:text-sm dark:bg-gray-800 dark:text-gray-400 bg-gray-100 text-gray-500 cursor-not-allowed focus:ring-0 focus:border-gray-300"
                        placeholder="Auto-filled from search...">
                    @error('part_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Qty <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="qty" required min="1" value="{{ old('qty', 1) }}"
                        class="w-full border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                        placeholder="Jumlah order">
                </div>

                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Delivery Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="delivery_date" required value="{{ old('delivery_date') }}"
                        class="w-full border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                </div>



            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <a href="{{ route('events.parts.index', $event->hashed_id) }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 shadow-sm text-[13px] font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-md shadow-blue-500/20 text-[13px] font-medium hover:from-blue-700 hover:to-cyan-700 transition">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Save Part
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('part_search');
        const searchResults = document.getElementById('search_results');
        const partNoInput = document.getElementById('part_no_input');
        const partNameInput = document.getElementById('part_name_input');
        const customerId = "{{ optional($event->customerCategory)->customer_id }}";
        
        let debounceTimer;

        function fetchProducts(query) {
            fetch("{{ route('api.data.products') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ search: query, customer_id: customerId })
            })
            .then(response => response.json())
            .then(data => {
                searchResults.innerHTML = '';
                if(data.results && data.results.length > 0) {
                    data.results.forEach(product => {
                        let div = document.createElement('div');
                        div.className = 'px-4 py-3 hover:bg-blue-50 dark:hover:bg-slate-700 cursor-pointer text-sm text-slate-700 dark:text-slate-200 border-b border-gray-100 dark:border-gray-700 last:border-0';
                        div.innerHTML = `<span class="font-bold font-mono text-blue-600 dark:text-blue-400">${product.part_no}</span> - ${product.part_name}`;
                        div.addEventListener('click', function() {
                            partNoInput.value = product.part_no;
                            partNameInput.value = product.part_name;
                            searchInput.value = product.part_no;
                            searchResults.classList.add('hidden');


                        });
                        searchResults.appendChild(div);
                    });
                    searchResults.classList.remove('hidden');
                } else {
                    searchResults.innerHTML = '<div class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400 italic">No part found...</div>';
                    searchResults.classList.remove('hidden');
                }
            })
            .catch(error => console.error('Error fetching products:', error));
        }

        searchInput.addEventListener('focus', function() {
            if (this.value.length === 0) {
                fetchProducts('');
            } else {
                searchResults.classList.remove('hidden');
            }
        });

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value;
            
            // Clear existing selection to force re-selection
            partNoInput.value = '';
            partNameInput.value = '';

            debounceTimer = setTimeout(() => {
                fetchProducts(query);
            }, 300);
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    });
</script>
@endpush
