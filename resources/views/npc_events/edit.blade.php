@extends('layouts.app')

@section('title', 'Edit Event')
@section('page_title', 'Master Data / Edit Event')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
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

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <a href="{{ route('events.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-md shadow-blue-500/20 text-sm font-medium hover:from-blue-700 hover:to-cyan-700 transition">
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


    });
</script>
@endpush
