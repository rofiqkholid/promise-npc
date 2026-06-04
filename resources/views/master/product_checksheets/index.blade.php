@extends('layouts.app')

@section('title', 'Master Checksheet Part')
@section('page_title', 'Master Data / Master Checksheet Part')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
            <div class="flex-1">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-list-check text-blue-500"></i> Master Checksheet per Part
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 pl-7">Manage QC checkpoint specifications for each part number.</p>
            </div>
            <div>
                <a href="{{ route('master.checksheets.import') }}" class="px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-md shadow-emerald-500/20 font-medium text-sm flex items-center gap-2">
                    <i class="fa-solid fa-file-import"></i> Import Excel
                </a>
            </div>
        </div>

        <form id="searchForm" method="GET" action="{{ route('master.checksheets.index') }}" class="w-full">
            <div class="flex flex-wrap gap-3 items-end justify-between">
                
                <!-- Left Side: Dropdown Filters -->
                <div class="flex flex-wrap gap-3 flex-1">
                    <div class="w-full sm:w-40">
                        <select name="customer_id" class="w-full py-2 pl-3 pr-10 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all rounded-none" onchange="document.getElementById('searchForm').submit()">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="w-full sm:w-40">
                        <select name="model_id" class="w-full py-2 pl-3 pr-10 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all rounded-none" onchange="document.getElementById('searchForm').submit()">
                            <option value="">All Models</option>
                            @foreach($models as $model)
                                <option value="{{ $model->id }}" {{ request('model_id') == $model->id ? 'selected' : '' }}>
                                    {{ $model->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="w-full sm:w-40">
                        <select name="status" class="w-full py-2 pl-3 pr-10 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all rounded-none" onchange="document.getElementById('searchForm').submit()">
                            <option value="">All Statuses</option>
                            <option value="mapped" {{ request('status') == 'mapped' ? 'selected' : '' }}>Mapped</option>
                            <option value="unmapped" {{ request('status') == 'unmapped' ? 'selected' : '' }}>Unmapped</option>
                        </select>
                    </div>
                    
                    @if(request('search') || request('customer_id') || request('model_id') || request('status'))
                        <a href="{{ route('master.checksheets.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 transition text-sm flex items-center gap-2 shadow-sm rounded-none" title="Clear Filters">
                            <i class="fa-solid fa-xmark"></i> Clear
                        </a>
                    @endif
                </div>

                <!-- Right Side: Search Input -->
                <div class="w-full sm:w-64 lg:w-80">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" 
                            class="w-full pr-4 py-2 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder-gray-400 rounded-none" 
                            style="padding-left: 2.5rem;"
                            placeholder="Search Part No / Name...">
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="p-6">
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="checksheetSetupTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-12 text-center">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Customer</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Model</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Name Part</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Mapping Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- DataTables Data -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTables
        var table = initPromiseDataTable('#checksheetSetupTable', {
            ajax: {
                url: "{{ route('master.checksheets.index') }}",
                data: function (d) {
                    d.customer_id = $('select[name="customer_id"]').val();
                    d.model_id = $('select[name="model_id"]').val();
                    d.status = $('select[name="status"]').val();
                    // Optional: Custom search if we use the top search input instead of DT default
                    d.search = { value: $('#searchInput').val(), regex: false };
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-6 py-4 text-center text-slate-500 text-sm' },
                { data: 'customer', name: 'customer.code', className: 'px-6 py-4', orderable: false },
                { data: 'model', name: 'vehicleModel.name', className: 'px-6 py-4', orderable: false },
                { data: 'part_no', name: 'part_no', className: 'px-6 py-4' },
                { data: 'part_name', name: 'part_name', className: 'px-6 py-4' },
                { data: 'mapping_status', name: 'mapping_status', className: 'px-6 py-4 text-center', searchable: false, orderable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-6 py-4 text-right align-middle' }
            ],
            // Since we use custom filters and search box:
            dom: '<"flex justify-between items-center mb-4"<"text-sm"l>>rt<"flex justify-between items-center mt-4 text-sm"ip>'
        });

        // Search trigger
        let typingTimer;
        const doneTypingInterval = 500;
        const $input = $('#searchInput');

        $input.on('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(doneTyping, doneTypingInterval);
        });
        $input.on('keydown', function () {
            clearTimeout(typingTimer);
        });
        function doneTyping () {
            table.ajax.reload();
        }

        // Dropdown triggers
        $('select[name="customer_id"], select[name="model_id"], select[name="status"]').on('change', function() {
            table.ajax.reload();
        });
    });
</script>
@endpush
