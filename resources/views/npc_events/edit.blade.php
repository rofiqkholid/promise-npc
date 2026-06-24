@extends('layouts.app')

@section('title', 'Edit Event')
@section('page_title', 'Master Data / Edit Event')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-blue-500"></i> Form Edit Event
            </h2>
        </div>

        <form action="{{ route('events.update', $event->hashed_id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            


            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nomor PO -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nomor PO <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="po_no" required class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Masukkan No. PO" value="{{ old('po_no', $event->po_no) }}">
                    @error('po_no') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Customer Select -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Customer <span class="text-red-500">*</span>
                    </label>
                    <select name="customer_id" id="customer_select" required data-placeholder="Select Customer..."
                        class="select2 w-full">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ (old('customer_id', $masterCustomerId) == $customer->id) ? 'selected' : '' }}>
                                {{ $customer->code }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Model Select -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Vehicle Model <span class="text-red-500">*</span>
                    </label>
                    <select name="model_id" id="model_select" required data-placeholder="Select Model..."
                        class="select2 w-full">
                        @foreach($models as $model)
                            <option value="{{ $model->id }}" {{ (old('model_id', $masterModelId) == $model->id) ? 'selected' : '' }}>
                                {{ $model->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('model_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Category Select -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Category Event <span class="text-red-500">*</span>
                    </label>
                    <select name="customer_category_id" id="category_select" required data-placeholder="Select Category Event..."
                        class="select2 w-full">
                        <option value="">Select Category</option>
                        @foreach($customer_categories as $cat)
                            <option value="{{ $cat->id }}" {{ (old('customer_category_id', $event->customer_category_id) == $cat->id) ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_category_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Delivery Group Select -->
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Delivery Group (GR) <span class="text-red-500">*</span>
                    </label>
                    <select name="delivery_group_id" id="delivery_group_id" required data-placeholder="Select Delivery Group..."
                        class="select2 w-full">
                        <option value="">Select Grup</option>
                        @foreach($delivery_groups as $group)
                            <option value="{{ $group->id }}" {{ old('delivery_group_id', $event->delivery_group_id) == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('delivery_group_id') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <!-- Tujuan Pengiriman -->
                <div class="space-y-1">
                    <label for="delivery_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tujuan Pengiriman (Delivery To)
                    </label>
                    <select id="delivery_to" name="delivery_to" data-placeholder="Select Tujuan..."
                        class="select2 w-full">
                        <option value="">Select Tujuan (Optional)</option>
                        @foreach($delivery_targets as $target)
                            <option value="{{ $target->target_name }}" {{ old('delivery_to', $event->delivery_to) == $target->target_name ? 'selected' : '' }}>
                                {{ $target->target_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Part Details Section (Dynamic) -->
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-md font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-cubes text-indigo-500"></i> Part Details & PO
                    </h3>
                    <button type="button" id="add_part_btn" class="px-3 py-1.5 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition text-sm font-medium flex items-center gap-1">
                        <i class="fa-solid fa-plus"></i> Add Part
                    </button>
                </div>
                
                <div id="parts_container" class="space-y-4">
                    <!-- Parts will be dynamically added here -->
                    @forelse($event->parts as $index => $part)
                    <div class="part-item bg-slate-50 dark:bg-gray-800/80 p-4 border border-slate-200 dark:border-gray-700">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"><i class="fa-solid fa-box-open mr-1"></i> Item Part</span>
                            @if($part->status == 'PO_REGISTERED')
                            <button type="button" class="remove-part text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 dark:hover:bg-red-900/30 p-1.5 transition flex items-center justify-center" title="Delete Part">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            @endif
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="space-y-1">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Target Delivery</label>
                                <input type="date" name="parts[{{ $index }}][delivery_date]" value="{{ \Carbon\Carbon::parse($part->delivery_date)->format('Y-m-d') }}" required class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                            <div class="space-y-1 relative">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Part Number</label>
                                <input type="text" class="part-no-display w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Search Part No..." autocomplete="off" value="{{ optional($part->product)->part_no }}">
                                <input type="hidden" name="parts[{{ $index }}][part_no]" class="part-no-input" value="{{ optional($part->product)->part_no }}" required>
                                <input type="hidden" name="parts[{{ $index }}][part_name]" class="part-name-input" value="{{ optional($part->product)->part_name }}">
                                <input type="hidden" name="parts[{{ $index }}][id]" value="{{ $part->id }}">
                                <div class="part-autocomplete hidden absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg max-h-52 overflow-y-auto text-sm"></div>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Qty</label>
                                <input type="number" name="parts[{{ $index }}][qty]" min="1" value="{{ $part->qty }}" required class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 text-gray-500 dark:text-gray-400 text-sm" id="empty_parts_msg">
                        No part has been added yet. Click the "Add Part" button above.
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <a href="{{ route('events.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 shadow-sm text-[13px] font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-md shadow-blue-500/20 text-[13px] font-medium hover:from-blue-700 hover:to-cyan-700 transition">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Update Event
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const customerSelect = document.getElementById('customer_select');
        const modelSelect = document.getElementById('model_select');
        const oldModelId = "{{ old('model_id', $event->model_id) }}";

        function loadModels(customerId, selectedModelId = null) {
            modelSelect.innerHTML = '<option value="">Memuat...</option>';
            modelSelect.disabled = true;
            $(modelSelect).trigger('change.select2');
            
            if (!customerId) {
                modelSelect.innerHTML = '<option value="">Select Model</option>';
                modelSelect.disabled = true;
                $(modelSelect).trigger('change.select2');
                return;
            }

            fetch("{{ route('api.data.models') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ customer_id: customerId })
            })
            .then(response => response.json())
            .then(data => {
                modelSelect.innerHTML = '<option value="">Select Model</option>';
                if(data.results && data.results.length > 0) {
                    data.results.forEach(model => {
                        let isSelected = selectedModelId == model.id ? 'selected' : '';
                        modelSelect.innerHTML += `<option value="${model.id}" ${isSelected}>${model.text}</option>`;
                    });
                    modelSelect.disabled = false;
                } else {
                    modelSelect.innerHTML = '<option value="">No models available</option>';
                }
                $(modelSelect).trigger('change.select2');
            })
            .catch(error => {
                console.error('Error fetching models:', error);
                modelSelect.innerHTML = '<option value="">-- Failed memuat data --</option>';
                $(modelSelect).trigger('change.select2');
            });
        }

        const categorySelect = document.getElementById('category_select');
        const oldCategoryId = "{{ old('customer_category_id', $event->customer_category_id) }}";

        function loadCategories(customerId, selectedCategoryId = null) {
            categorySelect.innerHTML = '<option value="">Memuat...</option>';
            categorySelect.disabled = true;
            $(categorySelect).trigger('change.select2');
            
            if (!customerId) {
                categorySelect.innerHTML = '<option value="">Select Category</option>';
                categorySelect.disabled = true;
                $(categorySelect).trigger('change.select2');
                return;
            }

            fetch("{{ route('api.data.customer-categories') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ customer_id: customerId })
            })
            .then(response => response.json())
            .then(data => {
                categorySelect.innerHTML = '<option value="">Select Category</option>';
                if(data.results && data.results.length > 0) {
                    data.results.forEach(cat => {
                        let isSelected = selectedCategoryId == cat.id ? 'selected' : '';
                        categorySelect.innerHTML += `<option value="${cat.id}" ${isSelected}>${cat.text}</option>`;
                    });
                    categorySelect.disabled = false;
                } else {
                    categorySelect.innerHTML = '<option value="">No categories available (Please add in Master Data)</option>';
                }
                $(categorySelect).trigger('change.select2');
            })
            .catch(error => {
                console.error('Error fetching categories:', error);
                categorySelect.innerHTML = '<option value="">-- Failed memuat data --</option>';
                $(categorySelect).trigger('change.select2');
            });
        }
        


        $('#customer_select').on('change', function() {
            loadModels(this.value);
            loadCategories(this.value);
        });

        if ("{{ old('customer_id') }}" && "{{ old('customer_id') }}" !== "{{ $masterCustomerId }}") {
             loadModels("{{ old('customer_id') }}", oldModelId);
             loadCategories("{{ old('customer_id') }}", oldCategoryId);
        }

        // --- Dynamic Parts Logic ---
        const partsContainer = document.getElementById('parts_container');
        const addPartBtn = document.getElementById('add_part_btn');
        const emptyMsg = document.getElementById('empty_parts_msg');
        let partIndex = {{ $event->parts->count() > 0 ? $event->parts->count() : 0 }};

        document.querySelectorAll('.part-item').forEach(partItem => {
            attachPartEvents(partItem);
        });

        function attachPartEvents(partItem) {
            const displayInput = partItem.querySelector('.part-no-display');
            const partNoInput = partItem.querySelector('.part-no-input');
            const partNameInput = partItem.querySelector('.part-name-input');
            const dropdown = partItem.querySelector('.part-autocomplete');
            let acTimer;

            function fetchProducts(q) {
                fetch("{{ route('api.data.products') }}", {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                    body: JSON.stringify({ search: q, model_id: document.getElementById('model_select').value })
                })
                .then(r => r.json())
                .then(data => {
                    dropdown.innerHTML = '';
                    if (data.results && data.results.length > 0) {
                        data.results.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'px-3 py-2 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-0';
                            div.innerHTML = `<span class="font-semibold font-mono text-blue-600 dark:text-blue-400 text-xs">${item.part_no}</span> <span class="text-gray-600 dark:text-gray-300 text-xs">${item.part_name}</span>`;
                            div.addEventListener('mousedown', function(e) {
                                e.preventDefault();
                                displayInput.value = item.part_no;
                                partNoInput.value = item.part_no;
                                partNameInput.value = item.part_name;
                                dropdown.classList.add('hidden');
                            });
                            dropdown.appendChild(div);
                        });
                        dropdown.classList.remove('hidden');
                    } else {
                        dropdown.innerHTML = '<div class="px-3 py-2 text-gray-400 text-xs italic">No results found</div>';
                        dropdown.classList.remove('hidden');
                    }
                });
            }

            displayInput.addEventListener('focus', function() {
                if (this.value.length === 0) {
                    fetchProducts('');
                } else {
                    dropdown.classList.remove('hidden');
                }
            });

            displayInput.addEventListener('input', function() {
                clearTimeout(acTimer);
                partNoInput.value = '';
                partNameInput.value = '';
                const q = this.value.trim();
                acTimer = setTimeout(() => {
                    fetchProducts(q);
                }, 300);
            });

            displayInput.addEventListener('blur', function() {
                setTimeout(() => dropdown.classList.add('hidden'), 150);
            });
        }

        addPartBtn.addEventListener('click', function() {
            if(emptyMsg) emptyMsg.style.display = 'none';

            const template = `
                <div class="part-item bg-slate-50 dark:bg-gray-800/80 p-4 border border-slate-200 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"><i class="fa-solid fa-box-open mr-1"></i> Item Part</span>
                        <button type="button" class="remove-part text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 dark:hover:bg-red-900/30 p-1.5 transition flex items-center justify-center" title="Delete Part">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Target Delivery</label>
                            <input type="date" name="parts[${partIndex}][delivery_date]" required class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                        <div class="space-y-1 relative">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Part Number</label>
                            <input type="text" class="part-no-display w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Search Part No..." autocomplete="off">
                            <input type="hidden" name="parts[${partIndex}][part_no]" class="part-no-input" required>
                            <input type="hidden" name="parts[${partIndex}][part_name]" class="part-name-input">
                            <div class="part-autocomplete hidden absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-lg max-h-52 overflow-y-auto text-sm"></div>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Qty</label>
                            <input type="number" name="parts[${partIndex}][qty]" min="1" value="1" required class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>
                </div>
            `;
            
            partsContainer.insertAdjacentHTML('beforeend', template);

            const allItems = partsContainer.querySelectorAll('.part-item');
            if (allItems.length >= 2) {
                const prev = allItems[allItems.length - 2];
                const curItem = allItems[allItems.length - 1];
                const prevDate = prev.querySelector('input[name*="[delivery_date]"]');
                if (prevDate) curItem.querySelector('input[name*="[delivery_date]"]').value = prevDate.value;
            }

            attachPartEvents(partsContainer.lastElementChild);
            partIndex++;
        });

        partsContainer.addEventListener('click', function(e) {
            if(e.target.closest('.remove-part')) {
                e.target.closest('.part-item').remove();
                if(partsContainer.querySelectorAll('.part-item').length === 0) {
                    if(emptyMsg) emptyMsg.style.display = 'block';
                }
            }
        });

    });
</script>
@endpush
