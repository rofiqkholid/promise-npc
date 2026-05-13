@extends('layouts.app')

@section('title', 'Setup Master Checksheet')
@section('page_title', 'Master Data / Setup Checksheet / ' . $product->part_no)

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-6xl mx-auto">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                <i class="fa-solid fa-list-check text-blue-500 mr-2"></i> Master Checksheet Configuration
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                <strong>Part No:</strong> <span class="text-blue-600 dark:text-blue-400 font-bold">{{ $product->part_no }}</span> | <strong>Name:</strong> {{ $product->part_name }} | <strong>Customer:</strong> <span class="font-bold text-gray-800 dark:text-gray-200">{{ optional($product->customer)->code ?? '-' }}</span> | <strong>Model:</strong> <span class="font-medium text-gray-800 dark:text-gray-200">{{ optional($product->vehicleModel)->name ?? '-' }}</span>
            </p>
        </div>
        <div>
            <a href="{{ route('master.checksheets.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded shadow-sm text-sm font-medium transition dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 border border-gray-300 dark:border-gray-600">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
    
    <form action="{{ route('checksheets.setup.update', $product->hashed_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="p-6 space-y-8">
            
            <!-- SECTION: Spec Child Parts & Sketch -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Left: Spec Child Parts -->
                <div class="space-y-6">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white border-b pb-2 border-gray-200 dark:border-gray-700">1. Spec Child Parts</h3>
                    
                    <!-- Material Parts Table -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300">Material Parts</h4>
                            <button type="button" id="add-material-btn" class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-400"><i class="fa-solid fa-plus"></i> Add Material</button>
                        </div>
                        <table class="w-full text-sm text-left border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <thead class="bg-gray-100 dark:bg-gray-700/80 text-gray-800 dark:text-gray-200 uppercase text-[10px]">
                                <tr>
                                    <th class="px-3 py-2 w-12 text-center">No</th>
                                    <th class="px-3 py-2">Material Part</th>
                                    <th class="px-3 py-2 w-24">Thickness</th>
                                    <th class="px-3 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="material-parts-body" class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @forelse($materialParts as $idx => $mat)
                                <tr>
                                    <td class="p-2"><input type="text" name="material_parts[{{$idx}}][sequence_label]" value="{{ $mat->sequence_label }}" class="w-full p-1 text-center border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="1"></td>
                                    <td class="p-2">
                                        <select name="material_parts[{{$idx}}][inventory_material_id]" class="material-select2 w-full">
                                            @if($mat->inventory_material_id && isset($inventoryMaterials[$mat->inventory_material_id]))
                                                <option value="{{ $mat->inventory_material_id }}" selected>{{ $inventoryMaterials[$mat->inventory_material_id] }}</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td class="p-2"><input type="text" name="material_parts[{{$idx}}][thickness]" value="{{ $mat->thickness }}" class="w-full p-1 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. 1.4"></td>
                                    <td class="p-2 text-center"><button type="button" class="text-red-500 hover:text-red-700 remove-row"><i class="fa-solid fa-xmark"></i></button></td>
                                </tr>
                                @empty
                                <!-- Empty state handled by JS or user can click add -->
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- STD Parts Table -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300">STD Parts</h4>
                            <button type="button" id="add-std-btn" class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-400"><i class="fa-solid fa-plus"></i> Add STD Part</button>
                        </div>
                        <table class="w-full text-sm text-left border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                            <thead class="bg-gray-100 dark:bg-gray-700/80 text-gray-800 dark:text-gray-200 uppercase text-[10px]">
                                <tr>
                                    <th class="px-3 py-2 w-12 text-center">No</th>
                                    <th class="px-3 py-2">STD Part</th>
                                    <th class="px-3 py-2 w-24">Spec</th>
                                    <th class="px-3 py-2 w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="std-parts-body" class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @forelse($stdParts as $idx => $std)
                                <tr>
                                    <td class="p-2"><input type="text" name="std_parts[{{$idx}}][sequence_label]" value="{{ $std->sequence_label }}" class="w-full p-1 text-center border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="a"></td>
                                    <td class="p-2">
                                        <select name="std_parts[{{$idx}}][std_part_id]" class="std-select2 w-full">
                                            @if($std->stdPart)
                                                <option value="{{ $std->std_part_id }}" selected>{{ $std->stdPart->name }}</option>
                                            @endif
                                        </select>
                                    </td>
                                    <td class="p-2"><input type="text" name="std_parts[{{$idx}}][spec]" value="{{ $std->spec }}" class="w-full p-1 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Spec"></td>
                                    <td class="p-2 text-center"><button type="button" class="text-red-500 hover:text-red-700 remove-row"><i class="fa-solid fa-xmark"></i></button></td>
                                </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right: Sketch Image -->
                <div class="space-y-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white border-b pb-2 border-gray-200 dark:border-gray-700">2. Sketch Image (Problem History)</h3>
                    
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 flex flex-col items-center justify-center text-center bg-gray-50 dark:bg-gray-800/50 min-h-[300px] relative">
                        @if($product->productDetail && $product->productDetail->sketch_image_path)
                            <div class="mb-4 w-full h-full flex justify-center items-center">
                                <img src="{{ Storage::url($product->productDetail->sketch_image_path) }}" alt="Sketch" class="max-w-full max-h-[300px] object-contain rounded">
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Upload a new image to replace the current one.</p>
                        @else
                            <i class="fa-regular fa-image text-4xl text-gray-400 mb-3"></i>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">No sketch image uploaded</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Upload an image showing the part layout and pointers (a, b, c, 1, 2, 3...)</p>
                        @endif
                        
                        <input type="file" name="sketch_image" id="sketch_image" class="block w-full text-sm text-gray-500 dark:text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-full file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100
                            dark:file:bg-blue-900/30 dark:file:text-blue-400
                            cursor-pointer" accept="image/*">
                    </div>
                </div>
            </div>

            <!-- SECTION: Checkpoints -->
            <div>
                <div class="flex justify-between items-end border-b pb-2 border-gray-200 dark:border-gray-700 mb-4">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">3. Point Check Standards</h3>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="checkAll" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="checkAll" class="text-sm font-medium text-gray-700 dark:text-gray-300">Select All Points</label>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                        <thead class="bg-gray-100 dark:bg-gray-700/80 text-gray-800 dark:text-gray-200 uppercase text-xs tracking-wider">
                            <tr>
                                <th class="px-4 py-3 w-12 text-center">Use</th>
                                <th class="px-4 py-3 w-16 text-center">No</th>
                                <th class="px-4 py-3">Point Check</th>
                                <th class="px-4 py-3 w-[400px]">Standard / Parameter</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @php 
                                $currentCategory = null; 
                                $rowCounter = 1;
                            @endphp
                            
                            @foreach($masterPoints as $point)
                                @if($currentCategory !== $point->category)
                                    @php $currentCategory = $point->category; @endphp
                                    <tr class="bg-slate-200/50 dark:bg-slate-700/50">
                                        <td colspan="4" class="px-4 py-2 font-bold text-slate-800 dark:text-slate-200 uppercase text-xs">
                                            {{ $currentCategory ?: 'General / Uncategorized' }}
                                        </td>
                                    </tr>
                                @endif
                                
                                @php
                                    $isChecked = false;
                                    if ($isFirstTime) {
                                        $isChecked = true;
                                    } else {
                                        $isChecked = array_key_exists($point->id, $mappedData);
                                    }
                                    $stdText = $mappedData[$point->id] ?? '';
                                @endphp
                                <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" name="points[{{ $point->id }}][is_checked]" value="1" {{ $isChecked ? 'checked' : '' }} class="row-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-gray-500">{{ $rowCounter++ }}</td>
                                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">{{ $point->check_item }}</td>
                                    <td class="px-4 py-3">
                                        <input type="text" name="points[{{ $point->id }}][custom_standard]" value="{{ $stdText }}" placeholder="Leave blank if no standard" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-800 dark:text-white form-input p-2 {{ $isChecked ? '' : 'opacity-50' }}" {{ $isChecked ? '' : 'readonly' }} onfocus="this.select()">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 rounded-b-lg">
            <a href="{{ route('master.checksheets.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm text-sm font-medium">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-sm font-bold flex items-center gap-2 text-sm shadow-md">
                <i class="fa-solid fa-floppy-disk"></i> Save Configuration
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Styling for Select2 in dark mode */
    .dark .select2-container--default .select2-selection--single {
        background-color: #374151; /* gray-700 */
        border-color: #4B5563; /* gray-600 */
        color: white;
    }
    .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: white;
    }
    .dark .select2-dropdown {
        background-color: #374151;
        border-color: #4B5563;
    }
    .dark .select2-container--default .select2-results__option--selected {
        background-color: #4B5563;
    }
    .dark .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #2563EB; /* blue-600 */
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- CHECKBOX LOGIC ---
    const checkAll = document.getElementById('checkAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');

    function updateCheckAllState() {
        const total = rowCheckboxes.length;
        const checked = document.querySelectorAll('.row-checkbox:checked').length;
        checkAll.checked = (total > 0 && total === checked);
    }
    
    updateCheckAllState();

    checkAll.addEventListener('change', function() {
        rowCheckboxes.forEach(cb => {
            cb.checked = this.checked;
            toggleInputState(cb);
        });
    });

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateCheckAllState();
            toggleInputState(this);
        });
    });

    function toggleInputState(checkbox) {
        const input = checkbox.closest('tr').querySelector('input[type="text"]');
        if(checkbox.checked) {
            input.removeAttribute('readonly');
            input.classList.remove('opacity-50');
            input.classList.add('bg-white', 'dark:bg-gray-800');
            input.classList.remove('bg-gray-100', 'dark:bg-gray-900');
        } else {
            input.setAttribute('readonly', 'readonly');
            input.classList.add('opacity-50');
            input.classList.remove('bg-white', 'dark:bg-gray-800');
            input.classList.add('bg-gray-100', 'dark:bg-gray-900');
        }
    }
    
    rowCheckboxes.forEach(cb => toggleInputState(cb));

    // --- DYNAMIC TABLES LOGIC ---
    let matIndex = {{ count($materialParts) }};
    let stdIndex = {{ count($stdParts) }};

    function initSelect2(selector, url) {
        $(selector).select2({
            ajax: {
                url: url,
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        _token: '{{ csrf_token() }}',
                        search: params.term // search term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            placeholder: 'Search...',
            minimumInputLength: 0,
            width: '100%'
        });
    }

    // Init existing
    initSelect2('.material-select2', '{{ route('api.data.inventory-materials') }}');
    initSelect2('.std-select2', '{{ route('api.data.std-parts') }}');

    // Add Material Row
    document.getElementById('add-material-btn').addEventListener('click', function() {
        const tbody = document.getElementById('material-parts-body');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="p-2"><input type="text" name="material_parts[${matIndex}][sequence_label]" class="w-full p-1 text-center border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="${matIndex+1}"></td>
            <td class="p-2">
                <select name="material_parts[${matIndex}][inventory_material_id]" class="material-select2 w-full"></select>
            </td>
            <td class="p-2"><input type="text" name="material_parts[${matIndex}][thickness]" class="w-full p-1 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. 1.4"></td>
            <td class="p-2 text-center"><button type="button" class="text-red-500 hover:text-red-700 remove-row"><i class="fa-solid fa-xmark"></i></button></td>
        `;
        tbody.appendChild(tr);
        initSelect2($(tr).find('.material-select2'), '{{ route('api.data.inventory-materials') }}');
        matIndex++;
    });

    // Add STD Row
    document.getElementById('add-std-btn').addEventListener('click', function() {
        const tbody = document.getElementById('std-parts-body');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="p-2"><input type="text" name="std_parts[${stdIndex}][sequence_label]" class="w-full p-1 text-center border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="${String.fromCharCode(97 + stdIndex)}"></td>
            <td class="p-2">
                <select name="std_parts[${stdIndex}][std_part_id]" class="std-select2 w-full"></select>
            </td>
            <td class="p-2"><input type="text" name="std_parts[${stdIndex}][spec]" class="w-full p-1 border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Spec"></td>
            <td class="p-2 text-center"><button type="button" class="text-red-500 hover:text-red-700 remove-row"><i class="fa-solid fa-xmark"></i></button></td>
        `;
        tbody.appendChild(tr);
        
        const selectElem = $(tr).find('.std-select2');
        initSelect2(selectElem, '{{ route('api.data.std-parts') }}');
        
        stdIndex++;
    });

    // Remove row
    document.addEventListener('click', function(e) {
        if(e.target.closest('.remove-row')) {
            e.target.closest('tr').remove();
        }
    });

});
</script>
@endpush
