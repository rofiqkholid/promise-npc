@extends('layouts.app')

@section('title', $pageTitle ?? 'Finished Goods Stock')
@section('page_title', 'Transaction / ' . ($pageTitle ?? 'Finished Goods Stock (FG)'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid {{ $pageIcon ?? 'fa-boxes-stacked' }} text-blue-500"></i> {{ $pageTitle ?? 'Finished Goods Stock (FG)' }}
            </h2>
            @if(isset($pageDesc))
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="p-6">
        <!-- Filters -->
        <div class="mb-4 flex flex-col md:flex-row justify-between gap-4">
            <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                <div class="w-full md:w-64">
                    <select id="filter_customer" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full rounded-md shadow-sm">
                        <option value="">All Customers</option>
                        @foreach($customers ?? [] as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_filter') == $customer->id ? 'selected' : '' }}>{{ $customer->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-64">
                    <select id="filter_model" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full rounded-md shadow-sm">
                        <option value="">All Models</option>
                        @foreach($models ?? [] as $mod)
                            <option value="{{ $mod->id }}" data-customer="{{ $mod->customer_id }}" {{ request('model_filter') == $mod->id ? 'selected' : '' }}>{{ $mod->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="button" id="clearFiltersBtn" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium transition shadow-sm flex items-center gap-2 w-full justify-center">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="stockTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-4 py-2 font-semibold w-16">No</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Delivery Target & Time</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Part Info</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Qty</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Status Process</th>
                        <th scope="col" class="px-4 py-2 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- DataTables Data -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Deliver Modal -->
<div id="deliverModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full flex items-center justify-center">
    <div class="relative w-full max-w-md bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden scale-95 opacity-0 transition-all duration-300" id="deliverModalContent">
        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-truck-ramp-box text-blue-500"></i> Parts Delivery Form
            </h3>
            <button type="button" onclick="closeDeliverModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <form id="deliverForm" method="POST" action="">
            @csrf
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Part No: <strong id="modalPartNo" class="text-gray-800 dark:text-gray-200"></strong><br>
                    Please enter the quantity of parts to be delivered to the customer.
                </p>
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Delivery Qty <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" id="modalDeliveredQty" name="delivered_qty" min="1" required
                            class="w-full pl-4 pr-12 py-2 border border-gray-300 dark:border-gray-600 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-bold text-lg text-gray-800 dark:bg-gray-700 dark:text-white">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-500 font-semibold text-sm">
                            PCS
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Remaining parts to deliver: <strong id="modalMaxQtyText" class="text-blue-600 dark:text-blue-400"></strong> PCS
                    </p>
                </div>
                
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800/50 p-3 text-xs text-yellow-800 dark:text-yellow-300 mb-2">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> Make sure you have printed the Delivery Note from your internal system before this process.
                </div>
            </div>
            
            <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <button type="button" onclick="closeDeliverModal()" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 shadow-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600 transition text-[13px]">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white shadow-sm font-bold transition flex items-center gap-2 text-[13px]">
                    <i class="fa-solid fa-paper-plane"></i> Delivery Process
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        initPromiseDataTable('#stockTable', {
            ajax: {
                url: "{{ route('tracking.stock') }}",
                data: function (d) {
                    d.customer_filter = $('#filter_customer').val();
                    d.model_filter = $('#filter_model').val();
                    d.status_filter = $('#filter_status').val();
                }
            },
            stateSaveParams: function (settings, data) {
                data.customFilters = {
                    customer: $('#filter_customer').val(),
                    model: $('#filter_model').val(),
                    status: $('#filter_status').val()
                };
            },
            stateLoadParams: function (settings, data) {
                if (data.customFilters) {
                    if (data.customFilters.customer !== undefined) {
                        $('#filter_customer').val(data.customFilters.customer);
                    }
                    if (data.customFilters.model !== undefined) {
                        $('#filter_model').val(data.customFilters.model);
                    }
                    if (data.customFilters.status !== undefined) {
                        $('#filter_status').val(data.customFilters.status);
                    }
                }
            },
            initComplete: function(settings, json) {
                setTimeout(function() {
                    let hasCustomer = false;
                    if ($('#filter_customer').val()) {
                        $('#filter_customer').trigger('change');
                        hasCustomer = true;
                    }
                    if ($('#filter_model').val() && !hasCustomer) {
                        $('#filter_model').trigger('change');
                    }
                    if ($('#filter_status').val()) {
                        $('#filter_status').trigger('change');
                    }
                    if ($('#filter_customer').hasClass('select2-hidden-accessible')) {
                        $('#filter_customer').trigger('change.select2');
                    }
                    if ($('#filter_model').hasClass('select2-hidden-accessible')) {
                        $('#filter_model').trigger('change.select2');
                    }
                    if ($('#filter_status').hasClass('select2-hidden-accessible')) {
                        $('#filter_status').trigger('change.select2');
                    }
                }, 100);
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-2 text-slate-800 dark:text-slate-200 text-[13px]' },
                { data: 'delivery_target', name: 'delivery_target', className: 'px-4 py-2', orderable: false },
                { data: 'part_info', name: 'part_info', className: 'px-4 py-2', orderable: false },
                { data: 'qty_target', name: 'qty_target', className: 'px-4 py-2', orderable: false },
                { data: 'approval_info', name: 'approval_info', className: 'px-4 py-2 align-top', orderable: false, searchable: false },
                { data: 'action_stock', name: 'action_stock', orderable: false, searchable: false, className: 'px-4 py-2 text-right pointer-events-auto' }
            ]
        });

        function performSearch() {
            $('#stockTable').DataTable().ajax.reload();
        }

        $('#filter_customer').on('change', function(e) {
            let customerId = $(this).val();
            if ($('#filter_model').data('select2')) {
                $('#filter_model').select2('destroy');
            }
            $('#filter_model option').each(function() {
                if ($(this).val() == '') {
                    $(this).prop('disabled', false);
                    return;
                }
                if (!customerId || $(this).data('customer') == customerId) {
                    $(this).prop('disabled', false).show();
                } else {
                    $(this).prop('disabled', true).hide();
                }
            });
            $('#filter_model').select2({ width: '100%' });
            
            // If the currently selected model is now disabled, reset it
            if ($('#filter_model option:selected').prop('disabled')) {
                $('#filter_model').val('').trigger('change.select2');
            }
            performSearch();
        });

        $('#filter_model').on('change', function(e) {
            performSearch();
        });

        $('#clearFiltersBtn').on('click', function(e) {
            e.preventDefault();
            $('#filter_model').val('');
            $('#filter_customer').val('').trigger('change');
        });
        
        let initialCustomerId = $('#filter_customer').val();
        if (initialCustomerId) {
            if ($('#filter_model').data('select2')) {
                $('#filter_model').select2('destroy');
            }
            $('#filter_model option').each(function() {
                if ($(this).val() == '') return;
                if ($(this).data('customer') == initialCustomerId) {
                    $(this).prop('disabled', false).show();
                } else {
                    $(this).prop('disabled', true).hide();
                }
            });
            $('#filter_model').select2({ width: '100%' });
        }
    });

    function openDeliverModal(id, maxQty, url, partNo) {
        const modal = document.getElementById('deliverModal');
        const modalContent = document.getElementById('deliverModalContent');
        const form = document.getElementById('deliverForm');
        const qtyInput = document.getElementById('modalDeliveredQty');
        const maxQtyText = document.getElementById('modalMaxQtyText');
        const partNoText = document.getElementById('modalPartNo');
        
        form.action = url;
        qtyInput.max = maxQty;
        qtyInput.value = maxQty;
        maxQtyText.textContent = maxQty;
        partNoText.textContent = partNo;
        
        modal.classList.remove('hidden');
        // Trigger reflow
        void modal.offsetWidth;
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }
    
    function closeDeliverModal() {
        const modal = document.getElementById('deliverModal');
        const modalContent = document.getElementById('deliverModalContent');
        
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>
@endpush

