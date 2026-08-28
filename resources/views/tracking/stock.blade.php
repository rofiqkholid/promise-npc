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
        <div class="mb-4 flex flex-col xl:flex-row justify-between gap-4 items-start xl:items-end">
            <div class="flex flex-wrap items-center gap-4 w-full xl:w-auto">
                <div class="w-full sm:w-auto">
                    <select id="filter_customer" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-48 rounded-md shadow-sm">
                        <option value="">All Customers</option>
                        @foreach($customers ?? [] as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_filter') == $customer->id ? 'selected' : '' }}>{{ $customer->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-auto">
                    <select id="filter_model" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-48 rounded-md shadow-sm">
                        <option value="">All Models</option>
                        @foreach($models ?? [] as $mod)
                            <option value="{{ $mod->id }}" data-customer="{{ $mod->customer_id }}" {{ request('model_filter') == $mod->id ? 'selected' : '' }}>{{ $mod->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-auto">
                    <select id="filter_po" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-48 rounded-md shadow-sm">
                        <option value="">All POs</option>
                        @foreach($poList ?? [] as $po)
                            <option value="{{ $po->po_no }}" {{ request('po_filter') == $po->po_no ? 'selected' : '' }}>{{ $po->po_no }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end w-full sm:w-auto">
                    <button type="button" id="clearFiltersBtn" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium transition shadow-sm flex items-center gap-2 w-full justify-center min-w-[100px]">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </button>
                </div>
            </div>

            <div class="flex items-end w-full md:w-[350px] shrink-0">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <input type="text" id="customSearchInput"
                        value="{{ request('search') }}"
                        placeholder="Search Part No, PO No..."
                        style="padding-left: 2.5rem; padding-right: 2.5rem;" class="py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-md">
                    <button type="button" id="clearSearchBtn" style="display:none;"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500 transition cursor-pointer z-10">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-sm overflow-hidden">
            <table id="stockTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 uppercase text-[11px] tracking-wider font-bold">
                    <tr>
                        <th scope="col" class="px-4 py-3 w-16">#</th>
                        <th scope="col" class="px-4 py-3">DELIVERY TARGET & TIME</th>
                        <th scope="col" class="px-4 py-3">PART INFO</th>
                        <th scope="col" class="px-4 py-3">QTY</th>
                        <th scope="col" class="px-4 py-3">STATUS PROCESS</th>
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
        let urlParams = new URLSearchParams(window.location.search);
        initPromiseDataTable('#stockTable', {
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
            search: { search: urlParams.get('search') || '' },
            ajax: {
                url: "{{ route('tracking.stock') }}",
                data: function (d) {
                    d.customer_filter = $('#filter_customer').val();
                    d.model_filter = $('#filter_model').val();
                    d.po_filter = $('#filter_po').val();
                    d.status_filter = $('#filter_status').val();
                }
            },
            stateSaveParams: function (settings, data) {
                data.customFilters = {
                    customer: $('#filter_customer').val(),
                    model: $('#filter_model').val(),
                    po: $('#filter_po').val(),
                    status: $('#filter_status').val()
                };
            },
            stateLoadParams: function (settings, data) {
                let urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('search')) {
                    data.search.search = urlParams.get('search');
                }
                if (data.customFilters) {
                    if (data.customFilters.customer !== undefined) {
                        $('#filter_customer').val(data.customFilters.customer);
                    }
                    if (data.customFilters.model !== undefined) {
                        $('#filter_model').val(data.customFilters.model);
                    }
                    if (data.customFilters.po !== undefined) {
                        $('#filter_po').val(data.customFilters.po);
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
                    if ($('#filter_po').val()) {
                        $('#filter_po').trigger('change');
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
                    if ($('#filter_po').hasClass('select2-hidden-accessible')) {
                        $('#filter_po').trigger('change.select2');
                    }
                    if ($('#filter_status').hasClass('select2-hidden-accessible')) {
                        $('#filter_status').trigger('change.select2');
                    }
                }, 100);
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-2 text-slate-800 dark:text-slate-200 text-[13px]' },
                { 
                    data: 'delivery_date', 
                    name: 'delivery_date', 
                    className: 'px-4 py-2', 
                    orderable: false,
                    render: function(data, type, row) {
                        const customerCode = row.product?.vehicle_model?.customer?.code || row.event?.customer_category?.name || 'Unknown Customer';
                        const modelName = row.product?.vehicle_model?.name || '-';
                        const categoryName = row.event?.customer_category?.name || '-';
                        const grName = row.event?.delivery_group?.name || '-';
                        
                        let timeBadge = '';
                        if (row.delivery_date) {
                            const targetDate = new Date(row.delivery_date.split('T')[0] + 'T00:00:00');
                            const today = new Date();
                            today.setHours(0, 0, 0, 0);
                            
                            if (['CLOSED', 'OUTSTANDING'].includes(row.status) && row.actual_delivery) {
                                const actualDate = new Date(row.actual_delivery.split('T')[0] + 'T00:00:00');
                                const diffDeliv = Math.round((targetDate - actualDate) / (1000 * 60 * 60 * 24));
                                if (diffDeliv < 0) {
                                    timeBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold border bg-red-100 text-red-700 border-red-200">
                                        <i class="fa-solid fa-circle-xmark"></i> Delivered Late ${Math.abs(diffDeliv)} Days
                                    </span>`;
                                } else if (diffDeliv === 0) {
                                    timeBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold border bg-blue-100 text-blue-700 border-blue-200">
                                        <i class="fa-solid fa-bolt"></i> Delivered On Time
                                    </span>`;
                                } else {
                                    timeBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold border bg-blue-100 text-blue-700 border-blue-200">
                                        <i class="fa-solid fa-bolt"></i> Delivered Early ${diffDeliv} Days
                                    </span>`;
                                }
                            } else {
                                const diffDays = Math.round((targetDate - today) / (1000 * 60 * 60 * 24));
                                if (diffDays < 0) {
                                    timeBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold border bg-red-100 text-red-700 border-red-200">
                                        <i class="fa-solid fa-triangle-exclamation"></i> Overdue ${Math.abs(diffDays)} Days
                                    </span>`;
                                } else if (diffDays === 0) {
                                    timeBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold border bg-orange-100 text-orange-700 border-orange-200">
                                        <i class="fa-solid fa-clock"></i> Deliver Today
                                    </span>`;
                                } else if (diffDays <= 3) {
                                    timeBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold border bg-orange-100 text-orange-700 border-orange-200">
                                        <i class="fa-solid fa-clock"></i> Remaining ${diffDays} Days
                                    </span>`;
                                } else {
                                    timeBadge = `<span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold border bg-green-100 text-green-700 border-green-200">
                                        <i class="fa-solid fa-clock"></i> Remaining ${diffDays} Days
                                    </span>`;
                                }
                            }
                        }
                        
                        return `<div class="font-bold text-gray-800 dark:text-gray-100 mb-1 flex items-center gap-1.5">
                                    <i class="fa-solid fa-building text-gray-400"></i> ${customerCode}
                                </div>
                                <div class="text-xs text-gray-500 font-medium mb-2 pl-4">
                                    <div class="mb-1">Model: <span class="text-blue-600 dark:text-blue-400">${modelName}</span></div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800 text-[9px] font-bold tracking-wider" title="Category Customer">${categoryName}</span>
                                        <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 text-[9px] font-bold tracking-wider" title="Delivery Group (GR)">${grName}</span>
                                    </div>
                                </div>
                                ${timeBadge}`;
                    }
                },
                { 
                    data: 'product.part_no', 
                    name: 'product.part_no', 
                    className: 'px-4 py-2', 
                    orderable: false,
                    render: function(data, type, row) {
                        const partNo = row.product?.part_no || '-';
                        const partName = row.product?.part_name || '-';
                        const modelName = row.product?.vehicle_model?.name || 'Unknown Model';
                        const poNo = row.event?.po_no || 'Unknown PO';
                        const qtyFormatted = Number(row.qty || 0).toLocaleString('id-ID');
                        
                        return `<div class="text-gray-800 dark:text-gray-200 font-bold text-sm flex items-center flex-wrap gap-1">
                                    ${partNo} 
                                    <span class="text-[10px] font-bold text-blue-700 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-300 px-1.5 py-0.5 rounded border border-blue-200 dark:border-blue-700 uppercase">PO: ${poNo}</span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1.5 mt-0.5">${partName}</div>
                                <div class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50 dark:bg-gray-700 px-2 py-0.5 inline-block border border-gray-200 dark:border-gray-600">${modelName}</div>
                                <div class="text-gray-800 dark:text-gray-300 font-black flex items-center gap-1.5 mt-2"><i class="fa-solid fa-boxes-stacked text-gray-400"></i> Initial Target: ${qtyFormatted} <span class="text-xs font-semibold text-gray-500">PCS</span></div>`;
                    }
                },
                { 
                    data: 'qty', 
                    name: 'qty', 
                    className: 'px-4 py-2', 
                    orderable: false,
                    render: function(data, type, row) {
                        const qty = Number(row.qty || 0).toLocaleString('id-ID');
                        const deliveredQty = Number(row.delivered_qty || 0);
                        const deliveredHtml = deliveredQty > 0 ? `
                            <div class="text-[11px] font-bold text-blue-600 dark:text-blue-400 mb-1">
                                <i class="fa-solid fa-truck-ramp-box"></i> Delivered: ${deliveredQty.toLocaleString('id-ID')} / ${qty}
                            </div>` : '';
                        
                        let targetDateStr = '-';
                        if (row.delivery_date) {
                            const d = new Date(row.delivery_date.split('T')[0] + 'T00:00:00');
                            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            targetDateStr = `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]} ${d.getFullYear()}`;
                        }
                        
                        return `<div class="text-gray-800 dark:text-gray-300 font-black text-lg mb-0.5">${qty} <span class="text-xs font-semibold text-gray-500">PCS</span></div>
                                ${deliveredHtml}
                                <div class="text-[11px] font-medium text-gray-500">
                                    Target: ${targetDateStr}
                                </div>`;
                    }
                },
                { 
                    data: 'status', 
                    name: 'status', 
                    className: 'px-4 py-2 align-top', 
                    orderable: false, 
                    searchable: false,
                    render: function(data, type, row) {
                        if (['FINISHED', 'OUTSTANDING', 'CLOSED'].includes(row.status)) {
                            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            let qcHtml = '';
                            if (row.qc_target_date) {
                                const d = new Date(row.qc_target_date.split('T')[0] + 'T00:00:00');
                                const dateStr = `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]} ${String(d.getFullYear()).slice(-2)}`;
                                qcHtml = `<span class="text-[11px] font-medium text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-check-double text-emerald-500"></i> QC Passed: ${dateStr}
                                </span>`;
                            }
                            let mgmHtml = '';
                            if (row.mgm_target_date) {
                                const d = new Date(row.mgm_target_date.split('T')[0] + 'T00:00:00');
                                const dateStr = `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]} ${String(d.getFullYear()).slice(-2)}`;
                                mgmHtml = `<span class="text-[11px] font-medium text-purple-700 dark:text-purple-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-check-double text-purple-500"></i> MGM Check: ${dateStr}
                                </span>`;
                            }
                            
                            return `<div class="flex flex-col gap-1.5 mt-1">
                                        <span class="text-[11px] font-medium text-slate-600 dark:text-slate-400 flex items-center gap-1.5 line-through decoration-slate-300 opacity-60">
                                            <i class="fa-solid fa-check text-green-500"></i> Production Done
                                        </span>
                                        ${qcHtml}
                                        ${mgmHtml}
                                    </div>`;
                        }
                        
                        const statusText = (row.status || '').replace(/_/g, ' ');
                        return `<div class="mt-2 text-slate-400 text-[10px] font-medium italic">
                                    Not yet finished (${statusText})
                                </div>`;
                    }
                },
                { 
                    data: 'id', 
                    name: 'id', 
                    orderable: false, 
                    searchable: false, 
                    className: 'px-4 py-2 text-right pointer-events-auto',
                    render: function(data, type, row) {
                        if (row.status === 'CLOSED') {
                            return `<div class="flex flex-col items-end gap-2 text-sm">
                                        <div class="px-3 py-2 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-[10px] text-blue-600 dark:text-blue-400 italic flex items-center gap-1.5 cursor-not-allowed font-bold">
                                            <i class="fa-solid fa-check-double text-[10px]"></i> Already Delivered (Closed)
                                        </div>
                                    </div>`;
                        }
                        if (!['FINISHED', 'OUTSTANDING'].includes(row.status)) {
                            return `<div class="flex flex-col items-end gap-2 text-sm">
                                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center gap-1.5 cursor-not-allowed">
                                            <i class="fa-solid fa-lock text-[8px]"></i> Waiting for Process to Complete
                                        </div>
                                    </div>`;
                        }
                        
                        const remainingQty = (row.qty || 0) - (row.delivered_qty || 0);
                        const remainingFormatted = Number(remainingQty).toLocaleString('id-ID');
                        const partNo = (row.product?.part_no || '').replace(/'/g, "\\'");
                        const deliverUrl = row.deliver_url || '';
                        const printUrl = row.print_label_url || '';
                        const checksheetBtn = row.checksheet ? `
                            <a href="${printUrl}" target="_blank" class="px-4 py-2 bg-white text-blue-600 border border-blue-200 hover:bg-blue-50 shadow-sm font-medium transition text-xs flex items-center justify-center gap-2 w-full">
                                <i class="fa-solid fa-print"></i> Print QC Label
                            </a>` : '';
                            
                        return `<div class="flex flex-col items-end gap-2 text-sm">
                                    <button type="button" onclick="openDeliverModal('${row.hashed_id}', '${remainingQty}', '${deliverUrl}', '${partNo}')" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white shadow-sm font-medium transition text-xs flex items-center justify-center gap-2 w-full">
                                        <i class="fa-solid fa-truck-fast"></i> Deliver Parts
                                    </button>
                                    ${checksheetBtn}
                                    <p class="text-[9px] text-gray-400 italic text-right w-full">Remaining: ${remainingFormatted} PCS</p>
                                </div>`;
                    }
                }
            ],
            drawCallback: function() {
                let urlParams = new URLSearchParams(window.location.search);
                let openPartId = urlParams.get('open_part');
                if (openPartId && !window.modalAlreadyOpened) {
                    setTimeout(() => {
                        let btn = $(`button[onclick*="openDeliverModal('${openPartId}'"]`);
                        if (btn.length > 0) {
                            btn.click();
                            window.modalAlreadyOpened = true;
                            let url = new URL(window.location);
                            url.searchParams.delete('open_part');
                            window.history.replaceState({}, document.title, url);
                        }
                    }, 200);
                }
            }
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

        // Link Custom Search Bar to DataTables
        let debounceTimer;
        $('#customSearchInput').on('input', function() {
            if ($(this).val().length > 0) {
                $('#clearSearchBtn').show();
            } else {
                $('#clearSearchBtn').hide();
            }
            clearTimeout(debounceTimer);
            let val = this.value;
            let table = $('#stockTable').DataTable();
            debounceTimer = setTimeout(function() {
                table.search(val).draw();
            }, 500);
        });

        if ($('#customSearchInput').val() && $('#customSearchInput').val().length > 0) {
            $('#clearSearchBtn').show();
        }

        $('#clearSearchBtn').on('click', function(e) {
            e.preventDefault();
            $('#customSearchInput').val('');
            $(this).hide();
            $('#stockTable').DataTable().search('').draw();
            $('#customSearchInput').focus();
        });

        $('#filter_model').on('change', function(e) {
            performSearch();
        });

        $('#filter_po').on('change', function(e) {
            performSearch();
        });

        $('#clearFiltersBtn').on('click', function(e) {
            e.preventDefault();
            $('#customSearchInput').val('');
            $('#clearSearchBtn').hide();
            $('#filter_model').val('').trigger('change.select2');
            $('#filter_po').val('').trigger('change.select2');
            $('#filter_status').val('').trigger('change.select2');
            $('#filter_customer').val('').trigger('change.select2');
            $('#stockTable').DataTable().search('').draw();
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

