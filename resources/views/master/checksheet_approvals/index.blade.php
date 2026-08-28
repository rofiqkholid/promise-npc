@extends('layouts.app')

@section('title', 'Master Checksheet Approvals')
@section('page_title', 'Master Data / Master Checksheet Approvals')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
            <div class="flex-1">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                    <i class="fa-solid fa-clipboard-check text-blue-500"></i> Master Checksheet QC Approvals
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 pl-7">Approve or reject master checksheet mappings before they can be used.</p>
            </div>
        </div>

        <form id="filterForm" class="mb-4">
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-end gap-4">
                
                <!-- Left Side: Dropdowns -->
                <div class="flex flex-wrap items-center gap-4 w-full xl:w-auto">
                    <div class="w-full sm:w-auto">
                        <select name="customer_id" class="w-full sm:w-40 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-none text-sm bg-white dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 transition shadow-sm">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="w-full sm:w-auto">
                        <select name="model_id" class="w-full sm:w-40 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-none text-sm bg-white dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 transition shadow-sm">
                            <option value="">All Models</option>
                            @foreach($models as $m)
                                <option value="{{ $m->id }}" {{ request('model_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="w-full sm:w-auto">
                        <select name="status" class="w-full sm:w-40 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-none text-sm bg-white dark:bg-gray-700 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 transition shadow-sm">
                            <option value="">All Statuses</option>
                            <option value="MAPPED" {{ request('status') == 'MAPPED' ? 'selected' : '' }}>Mapped</option>
                            <option value="WAITING_APPROVAL" {{ request('status') == 'WAITING_APPROVAL' ? 'selected' : '' }}>Waiting Approval</option>
                            <option value="APPROVED" {{ request('status') == 'APPROVED' ? 'selected' : '' }}>Approved</option>
                            <option value="REJECTED" {{ request('status') == 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    
                    <div class="w-full sm:w-auto">
                        <button type="button" id="resetFilters" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 transition text-[13px] flex items-center justify-center gap-2 shadow-sm rounded-none w-full" title="Reset Filters">
                            <i class="fa-solid fa-rotate-right"></i> Reset
                        </button>
                    </div>
                </div>

                <!-- Right Side: Search Input -->
                <div class="w-full sm:w-64 lg:w-80">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fa-solid fa-search text-sm"></i>
                        </div>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" 
                            style="padding-left: 2.5rem; padding-right: 2.5rem;" class="py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-md" 
                            placeholder="Search Part No / Name...">
                        <button type="button" id="clearSearchBtn" style="display:none;" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500 transition cursor-pointer">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="px-6 pb-6">
        <div class="border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-sm overflow-hidden">
            <table id="checksheetApprovalTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 uppercase text-[11px] tracking-wider font-bold">
                    <tr>
                        <th scope="col" class="px-4 py-3 w-12 text-center">#</th>
                        <th scope="col" class="px-4 py-3">CUSTOMER</th>
                        <th scope="col" class="px-4 py-3">MODEL</th>
                        <th scope="col" class="px-4 py-3">PART NO</th>
                        <th scope="col" class="px-4 py-3">PART NAME</th>
                        <th scope="col" class="px-4 py-3">ECN</th>
                        <th scope="col" class="px-4 py-3 text-center">MAPPING STATUS</th>
                        <th scope="col" class="px-4 py-3 text-right">ACTION</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
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
        var table = initPromiseDataTable('#checksheetApprovalTable', {
            ajax: {
                url: "{{ route('master.checksheet_approvals.index') }}",
                data: function (d) {
                    d.customer_id = $('select[name="customer_id"]').val();
                    d.model_id = $('select[name="model_id"]').val();
                    d.status = $('select[name="status"]').val();
                    // Optional: Custom search if we use the top search input instead of DT default
                    d.search = { value: $('#searchInput').val(), regex: false };
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-2 text-center text-slate-500 text-[13px]' },
                { data: 'customer', name: 'customer.code', className: 'px-4 py-2', orderable: false },
                { data: 'model', name: 'vehicleModel.name', className: 'px-4 py-2', orderable: false },
                { data: 'part_no', name: 'part_no', className: 'px-4 py-2' },
                { data: 'part_name', name: 'part_name', className: 'px-4 py-2' },
                { data: 'ecn_info', name: 'ecn_info', className: 'px-4 py-2', orderable: false, searchable: false },
                { data: 'mapping_status', name: 'mapping_status', className: 'px-4 py-2 text-center', searchable: false, orderable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-4 py-2 text-right align-middle' }
            ],
            pageLength: 10,
            stripeClasses: ['bg-white dark:bg-slate-800', 'bg-slate-100 dark:bg-slate-800/50'],
            dom: '<"overflow-x-auto"t><"p-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500"ip>',
            language: {
                search: "",
                searchPlaceholder: "Search Part No, PO No...",
                lengthMenu: "Show _MENU_ rows",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                paginate: {
                    previous: "Prev",
                    next: "Next"
                }
            },
            stateSaveParams: function (settings, data) {
                data.customFilters = {
                    customer_id: $('select[name="customer_id"]').val(),
                    model_id: $('select[name="model_id"]').val(),
                    status: $('select[name="status"]').val(),
                    search_input: $('#searchInput').val()
                };
            },
            stateLoadParams: function (settings, data) {
                if (data.customFilters) {
                    if (data.customFilters.customer_id !== undefined) $('select[name="customer_id"]').val(data.customFilters.customer_id);
                    if (data.customFilters.model_id !== undefined) $('select[name="model_id"]').val(data.customFilters.model_id);
                    if (data.customFilters.status !== undefined) $('select[name="status"]').val(data.customFilters.status);
                    if (data.customFilters.search_input !== undefined) $('#searchInput').val(data.customFilters.search_input);
                }
            },
            initComplete: function(settings, json) {
                setTimeout(function() {
                    $('select[name="customer_id"]').trigger('change.select2');
                    $('select[name="model_id"]').trigger('change.select2');
                    $('select[name="status"]').trigger('change.select2');
                }, 100);
            }
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

        // Reset Button
        $('#resetFilters').on('click', function() {
            $('select[name="customer_id"]').val('ALL').trigger('change.select2');
            $('select[name="model_id"]').val('ALL').trigger('change.select2');
            $('select[name="status"]').val('WAITING_APPROVAL').trigger('change.select2'); // Default to waiting approval instead of ALL
            $('#searchInput').val('');
            table.ajax.reload();
        });
    });
</script>
@endpush
