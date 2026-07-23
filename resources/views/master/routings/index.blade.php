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
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-route text-blue-500"></i> Register Master Routing
        </h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('master.routings.import') }}" class="px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-md shadow-emerald-500/20 font-medium text-[13px] flex items-center gap-2">
                <i class="fa-solid fa-file-import"></i> Import Excel
            </a>
            <a href="{{ route('master.routings.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-[13px] flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add Routing
            </a>
        </div>
    </div>

    <div class="p-6">
        <form id="searchForm" method="GET" action="{{ route('master.routings.index') }}" class="w-full mb-4">
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

                @if(request('search') || request('customer_id') || request('model_id'))
                    <a href="{{ route('master.routings.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 transition text-[13px] flex items-center gap-2 shadow-sm rounded-none" title="Clear Filters">
                        <i class="fa-solid fa-xmark"></i> Clear
                    </a>
                @endif
            </div>
        </form>

        <div class="overflow-x-auto">
            <table id="routingsTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-4 py-2 font-semibold w-16">#</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Part No</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Part Name</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Flow Process (Routing)</th>
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
        let table = initPromiseDataTable('#routingsTable', {
            ajax: {
                url: "{{ route('master.routings.index') }}",
                data: function (d) {
                    d.customer_id = $('#filter_customer').val();
                    d.model_id = $('#filter_model').val();
                }
            },
            stateSaveParams: function (settings, data) {
                data.customFilters = {
                    customer_id: $('#filter_customer').val(),
                    model_id: $('#filter_model').val()
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
                }, 100);
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-2 text-slate-800 dark:text-slate-200 text-[13px]' },
                { data: 'part_no', name: 'part_no', className: 'px-4 py-2 text-[13px]' },
                { data: 'part_name', name: 'part_name', className: 'px-4 py-2' },
                { data: 'flow_process', name: 'flow_process', className: 'px-4 py-2 text-slate-700 dark:text-slate-300 text-[13px]', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-4 py-2 text-right' }
            ],
            drawCallback: function(settings) {
                // Re-initialize Sortable after each draw
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
            }
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
                    $(this).show();
                    if ($(this).val() == currentModel) modelValid = true;
                } else {
                    $(this).hide();
                }
            });
            
            if (!modelValid && currentModel != '') {
                modelSelect.val('');
            }
        }
        
        $('#filter_customer').on('change', function() {
            updateModelFilter();
            $('#routingsTable').DataTable().ajax.reload();
        });
        
        $('#filter_model').on('change', function() {
            $('#routingsTable').DataTable().ajax.reload();
        });
        
        updateModelFilter();
    });

    document.addEventListener('DOMContentLoaded', function() {
        window.sortableOnEndFunction = function (evt) {
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
        };
    });
</script>
@endpush
