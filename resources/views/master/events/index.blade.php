@extends('layouts.app')

@section('title', 'Master Data Event')
@section('page_title', 'Data Event')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-calendar-check text-blue-500"></i> Register Project Event
        </h2>
        <div class="flex items-center gap-3">
            <a href="{{ route('events.import') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm font-medium text-[13px] flex items-center gap-2">
                <i class="fa-solid fa-file-excel text-green-600"></i> Import Excel
            </a>
            <a href="{{ route('events.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-[13px] flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add Event
            </a>
        </div>
    </div>

    <div class="p-6">
        <form id="searchForm" method="GET" action="{{ route('events.index') }}" class="w-full mb-4">
            <div class="flex flex-wrap gap-3 items-end">
                <div class="w-full sm:w-48">
                    <select name="customer_id" id="filter_customer" class="w-full py-2 pl-3 pr-10 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all rounded-none">
                        <option value="">All Customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="w-full sm:w-48">
                    <select name="model_id" id="filter_model" class="w-full py-2 pl-3 pr-10 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all rounded-none">
                        <option value="">All Models</option>
                        @foreach($models as $model)
                            <option value="{{ $model->id }}" {{ request('model_id') == $model->id ? 'selected' : '' }} data-customer-id="{{ $model->customer_id }}">
                                {{ $model->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="w-full sm:w-48">
                    <select name="po_no" id="filter_po" class="w-full py-2 pl-3 pr-10 bg-white text-sm border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all rounded-none">
                        <option value="">All POs</option>
                        @foreach($poList as $po)
                            <option value="{{ $po->po_no }}" {{ request('po_no') == $po->po_no ? 'selected' : '' }}>
                                {{ $po->po_no }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="button" id="resetFiltersBtn" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 transition text-[13px] flex items-center gap-2 shadow-sm rounded-none" title="Reset Filters">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </form>

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="eventsTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-4 py-2 font-semibold w-16">#</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Event Name</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Customer</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Model</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Category</th>
                        <th scope="col" class="px-4 py-2 font-semibold">GR</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Delivery To</th>
                        <th scope="col" class="px-4 py-2 font-semibold text-right w-32">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- DataTables will fill this -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        initPromiseDataTable('#eventsTable', {
            ajax: {
                url: "{{ route('events.index') }}",
                data: function (d) {
                    d.customer_id = $('#filter_customer').val();
                    d.model_id = $('#filter_model').val();
                    d.po_no = $('#filter_po').val();
                }
            },
            stateSaveParams: function (settings, data) {
                data.customFilters = {
                    customer_id: $('#filter_customer').val(),
                    model_id: $('#filter_model').val(),
                    po_no: $('#filter_po').val()
                };
            },
            stateLoadParams: function (settings, data) {
                if (data.customFilters) {
                    if (data.customFilters.customer_id !== undefined) {
                        $('#filter_customer').val(data.customFilters.customer_id);
                    }
                    if (data.customFilters.model_id !== undefined) {
                        $('#filter_model').val(data.customFilters.model_id);
                    }
                    if (data.customFilters.po_no !== undefined) {
                        $('#filter_po').val(data.customFilters.po_no);
                    }
                }
            },
            initComplete: function(settings, json) {
                setTimeout(function() {
                    let hasFilter = false;
                    if ($('#filter_customer').val()) {
                        $('#filter_customer').trigger('change');
                        hasFilter = true;
                    }
                    if ($('#filter_model').val() && !hasFilter) {
                        $('#filter_model').trigger('change');
                    }
                    if ($('#filter_customer').hasClass('select2-hidden-accessible')) {
                        $('#filter_customer').trigger('change.select2');
                    }
                    if ($('#filter_model').hasClass('select2-hidden-accessible')) {
                        $('#filter_model').trigger('change.select2');
                    }
                    if ($('#filter_po').val()) {
                        $('#filter_po').trigger('change');
                    }
                    if ($('#filter_po').hasClass('select2-hidden-accessible')) {
                        $('#filter_po').trigger('change.select2');
                    }
                }, 100);
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-2 text-slate-800 dark:text-slate-200 text-[13px]' },
                { data: 'event_name', name: 'event_name', className: 'px-4 py-2' },
                { data: 'customer', name: 'customer', className: 'px-4 py-2 text-slate-600 dark:text-slate-400 text-[13px]', orderable: false },
                { data: 'model', name: 'model', className: 'px-4 py-2' },
                { data: 'category', name: 'category', className: 'px-4 py-2' },
                { data: 'gr', name: 'gr', className: 'px-4 py-2' },
                { data: 'delivery_to', name: 'delivery_to', className: 'px-4 py-2' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-4 py-2 text-right' }
            ]
        });

        // Filter models based on selected customer
        function updateModelFilter() {
            let selectedCustomer = $('#filter_customer').val();
            let modelSelect = $('#filter_model');
            let currentModel = modelSelect.val();
            let modelValid = false;
            
            modelSelect.find('option').each(function() {
                let custId = $(this).data('customer-id');
                if (!custId || !selectedCustomer || custId == selectedCustomer) {
                    $(this).prop('disabled', false).show();
                    if ($(this).val() == currentModel) modelValid = true;
                } else {
                    $(this).prop('disabled', true).hide();
                }
            });
            
            if (!modelValid && currentModel != '') {
                modelSelect.val('');
            }

            if (modelSelect.hasClass('select2-hidden-accessible')) {
                modelSelect.trigger('change.select2');
            }
        }
        
        $('#filter_customer').on('change', function() {
            updateModelFilter();
            $('#eventsTable').DataTable().ajax.reload();
        });
        
        $('#filter_model').on('change', function() {
            $('#eventsTable').DataTable().ajax.reload();
        });

        $('#filter_po').on('change', function() {
            $('#eventsTable').DataTable().ajax.reload();
        });

        // Reset Filters
        $('#resetFiltersBtn').on('click', function() {
            $('#filter_customer').val('').trigger('change.select2');
            $('#filter_model').val('').trigger('change.select2');
            $('#filter_po').val('').trigger('change.select2');
            updateModelFilter();

            let table = $('#eventsTable').DataTable();
            table.search('');
            $('.dataTables_filter input').val('');
            table.ajax.reload();
        });
        
        updateModelFilter();
    });
</script>
@endpush
