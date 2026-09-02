@extends('layouts.app')

@section('title', $pageTitle ?? 'Production Tracking')
@section('page_title', 'Transaksi / ' . ($pageTitle ?? 'Production Tracking'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-list-check' }} text-blue-500"></i> {{ $pageTitle ?? 'Production Tracking (Routing)' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    @if(isset($metrics))
    <!-- Dashboard Cards -->
    <div class="px-6 pt-6 pb-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Event -->
        <div class="bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-calendar-check mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total Event</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_events']) }}</h3>
            </div>
        </div>
        
        <!-- Card 2: Total PO -->
        <div class="bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-lg shadow-indigo-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-file-invoice mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total PO</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_pos']) }}</h3>
            </div>
        </div>

        <!-- Card 3: Total Part -->
        <div class="bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-amber-600 shadow-lg shadow-amber-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-cubes mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total Part</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_parts']) }}</h3>
            </div>
        </div>

        <!-- Card 4: PO Close -->
        <div class="bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-flag-checkered mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">PO Close</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_po_close']) }}</h3>
            </div>
        </div>
    </div>
    @endif

    <!-- Table -->
    <div class="p-6">

        <!-- Filters -->
        <div class="mb-4 flex flex-col xl:flex-row justify-between items-start xl:items-end gap-4">
            <div class="flex flex-col md:flex-row flex-wrap gap-4 w-full xl:w-auto flex-1">
                <div class="w-full md:flex-1 xl:w-56">
                    <select id="customerFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full rounded-md shadow-sm">
                        <option value="">All Customers</option>
                        @foreach($customers ?? [] as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_filter') == $customer->id ? 'selected' : '' }}>{{ $customer->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:flex-1 xl:w-56">
                    <select id="modelFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full rounded-md shadow-sm">
                        <option value="">All Models</option>
                        @foreach($models ?? [] as $mod)
                            <option value="{{ $mod->id }}" data-customer="{{ $mod->customer_id }}" {{ request('model_filter') == $mod->id ? 'selected' : '' }}>{{ $mod->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:flex-1 xl:w-56">
                    <select id="poFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full rounded-md shadow-sm">
                        <option value="">All POs</option>
                        @foreach($poList ?? [] as $po)
                            <option value="{{ $po->po_no }}" {{ request('po_filter') == $po->po_no ? 'selected' : '' }}>{{ $po->po_no }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:flex-1 xl:w-48">
                    <input type="date" id="targetDateFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full rounded-md shadow-sm" value="{{ request('target_date_filter') }}" title="Filter by Target Delivery Date">
                </div>
                <div class="flex items-end w-full md:w-auto mt-1 md:mt-0">
                    <button type="button" id="clearFiltersBtn" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium transition shadow-sm flex items-center gap-2 w-full justify-center min-w-[100px]">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </button>
                </div>
            </div>

            <div class="flex items-end w-full xl:w-[350px] shrink-0">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <input type="text" id="searchInput"
                        value="{{ request('search') }}"
                        placeholder="Search Part No, Part Name, PO No..."
                        style="padding-left: 2.5rem; padding-right: 2.5rem;" class="py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-md">
                    <button type="button" id="clearSearchBtn" style="display:none;"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500 transition cursor-pointer z-10">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-4 py-2 font-semibold w-16">No</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Event</th>
                        <th scope="col" class="px-4 py-2 font-semibold">PO Number</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Part Info</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Qty & Target</th>
                        <th scope="col" class="px-4 py-2 font-semibold text-center">Overall Progress</th>
                        <th scope="col" class="px-4 py-2 font-semibold text-right">System Duration</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- DataTables will populate this -->
                </tbody>
            </table>
        </div>
    </div>


</div>

{{-- Modal: Production Done --}}
<div id="modal-complete" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 shadow-2xl w-full max-w-md mx-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-flag-checkered text-amber-500"></i> Confirm Production Done
            </h3>
            <button onclick="closeCompleteModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xl leading-none">&times;</button>
        </div>
        <form id="form-complete" method="POST">
            @csrf
            <input type="hidden" name="status" value="WAITING_QE_CHECK">
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Actual Completion Date <span class="text-red-500">*</span></label>
                    <input type="date" name="actual_completion_date" required
                        class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:bg-gray-700 dark:text-white">
                    <p class="text-[11px] text-gray-400 mt-1 italic">Date the part actually finished production.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Production Notes <span class="text-gray-400 text-[10px] font-normal">(optional)</span></label>
                    <textarea name="production_notes" rows="3" placeholder="Example: finished early / machine issues..."
                        class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:bg-gray-700 dark:text-white"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <button type="button" onclick="closeCompleteModal()" class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 text-[13px] font-medium text-white bg-amber-500 hover:bg-amber-600 shadow-sm transition flex items-center gap-1">
                    <i class="fa-solid fa-check"></i> Confirm Done
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openCompleteModal(partId, actionUrl) {
    document.getElementById('form-complete').action = actionUrl;
    document.getElementById('modal-complete').classList.remove('hidden');
    // Set today as default
    const dateInput = document.querySelector('#modal-complete input[name="actual_completion_date"]');
    if (!dateInput.value) dateInput.value = new Date().toISOString().substring(0, 10);
}
function closeCompleteModal() {
    document.getElementById('modal-complete').classList.add('hidden');
}
// Close on backdrop click
document.getElementById('modal-complete').addEventListener('click', function(e) {
    if (e.target === this) closeCompleteModal();
});<script>
$(document).ready(function() {
    let urlParams = new URLSearchParams(window.location.search);
    
    // Add indexTable ID to the table
    $('table').attr('id', 'indexTable');
    
    initPromiseDataTable('#indexTable', {
        pageLength: 15,
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
            },
            emptyTable: `<div class="flex flex-col items-center justify-center gap-3 py-8 text-gray-500">
                            <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                            <p>No routing / tracking data in this status.</p>
                        </div>`
        },
        search: { search: urlParams.get('search') || '' },
        ajax: {
            url: "{{ request()->url() }}",
            data: function (d) {
                d.customer_filter = $('#customerFilter').val() === 'all' ? '' : $('#customerFilter').val();
                d.model_filter = $('#modelFilter').val() === 'all' ? '' : $('#modelFilter').val();
                d.po_filter = $('#poFilter').val() === 'all' ? '' : $('#poFilter').val();
                d.target_date_filter = $('#targetDateFilter').val();
            }
        },
        stateSaveParams: function (settings, data) {
            data.customFilters = {
                customer: $('#customerFilter').val(),
                model: $('#modelFilter').val(),
                po: $('#poFilter').val(),
                targetDate: $('#targetDateFilter').val()
            };
        },
        stateLoadParams: function (settings, data) {
            let urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('search')) {
                data.search.search = urlParams.get('search');
            }
            if (data.customFilters) {
                if (data.customFilters.customer !== undefined) {
                    $('#customerFilter').val(data.customFilters.customer);
                }
                if (data.customFilters.model !== undefined) {
                    $('#modelFilter').val(data.customFilters.model);
                }
                if (data.customFilters.po !== undefined) {
                    $('#poFilter').val(data.customFilters.po);
                }
                if (data.customFilters.targetDate !== undefined) {
                    $('#targetDateFilter').val(data.customFilters.targetDate);
                }
            }
        },
        initComplete: function(settings, json) {
            setTimeout(function() {
                let hasCustomer = false;
                if ($('#customerFilter').val() && $('#customerFilter').val() !== 'all') {
                    $('#customerFilter').trigger('change');
                    hasCustomer = true;
                }
                if ($('#modelFilter').val() && $('#modelFilter').val() !== 'all' && !hasCustomer) {
                    $('#modelFilter').trigger('change');
                }
                if ($('#poFilter').val() && $('#poFilter').val() !== 'all') {
                    $('#poFilter').trigger('change');
                }
                if ($('#customerFilter').hasClass('select2-hidden-accessible')) {
                    $('#customerFilter').trigger('change.select2');
                }
                if ($('#modelFilter').hasClass('select2-hidden-accessible')) {
                    $('#modelFilter').trigger('change.select2');
                }
                if ($('#poFilter').hasClass('select2-hidden-accessible')) {
                    $('#poFilter').trigger('change.select2');
                }
            }, 100);
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-2 text-slate-800 dark:text-slate-200 text-[13px]' },
            { 
                data: 'event.customer_category.name', 
                name: 'event.customer_category.name', 
                className: 'px-4 py-2',
                orderable: false,
                render: function(data, type, row) {
                    const eventName = row.event?.customer_category?.name || 'Unknown Event';
                    let html = `<div class="text-blue-600 dark:text-blue-400 font-bold text-xs uppercase tracking-wide border border-blue-100 dark:border-blue-900 bg-blue-50 dark:bg-blue-900/30 px-2 py-1 inline-block mb-1">${eventName}</div>`;
                    if (row.has_ecn_update) {
                        html += `<div class="mt-1 text-[10px] font-bold text-red-600 dark:text-red-400 border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-900/30 px-2 py-0.5 inline-flex items-center gap-1 shadow-sm">
                                    <span class="relative flex h-2 w-2 mr-0.5">
                                        <span class="animate-ping absolute inline-flex h-full w-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex h-2 w-2 bg-red-500"></span>
                                    </span>
                                    ⚠️ ECN UPDATED
                                </div>`;
                    }
                    return html;
                }
            },
            { 
                data: 'event.po_no', 
                name: 'event.po_no', 
                className: 'px-4 py-2 text-gray-700 dark:text-gray-300 font-semibold text-[13px]',
                orderable: false,
                render: function(data, type, row) { return row.event?.po_no || ''; }
            },
            { 
                data: 'product.part_no', 
                name: 'product.part_no', 
                className: 'px-4 py-2',
                orderable: false,
                render: function(data, type, row) {
                    const partNo = row.product?.part_no || '';
                    const partName = row.product?.part_name || '';
                    return `<div class="text-gray-800 dark:text-gray-200 font-medium text-sm">${partNo}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">${partName}</div>`;
                }
            },
            { 
                data: 'qty', 
                name: 'qty', 
                className: 'px-4 py-2',
                orderable: false,
                render: function(data, type, row) {
                    const qty = Number(row.qty || 0).toLocaleString('id-ID');
                    let html = `<div class="text-gray-800 dark:text-gray-300 font-bold text-sm">${qty} PCS</div>`;
                    const deliveredQty = Number(row.delivered_qty || 0);
                    if (deliveredQty > 0) {
                        html += `<div class="text-[11px] font-bold text-blue-600 dark:text-blue-400 mt-1">
                                    <i class="fa-solid fa-truck-ramp-box"></i> Delivered: ${deliveredQty.toLocaleString('id-ID')} / ${qty}
                                </div>`;
                    }
                    const deliveryDate = row.delivery_date ? new Date(row.delivery_date.split('T')[0]).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: '2-digit' }) : '-';
                    html += `<div class="text-xs text-red-500 font-medium mt-1"><i class="fa-regular fa-calendar md:mr-1"></i> ${deliveryDate}</div>`;
                    return html;
                }
            },
            { 
                data: 'status', 
                name: 'status', 
                className: 'px-4 py-2 font-medium align-middle',
                orderable: false,
                render: function(data, type, row) {
                    const phases = ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK', 'WAITING_MGM_CHECK', 'FINISHED', 'CLOSED'];
                    const actualStatus = row.status === 'WAITING_APPROVAL' ? 'WAITING_MGM_CHECK' : row.status;
                    let currentIndex = phases.indexOf(actualStatus);
                    if (currentIndex === -1) currentIndex = -1;
                    if (row.status === 'CLOSED') currentIndex = 5;
                    if (row.status === 'OUTSTANDING') currentIndex = 4;
                    
                    const isOverdue = row.delivery_date && new Date(row.delivery_date).setHours(23,59,59,999) < new Date().getTime() && row.status !== 'CLOSED';
                    
                    const steps = [
                        { icon: 'fa-file-contract', title: 'Draft' },
                        { icon: 'fa-industry', title: 'Part Making' },
                        { icon: 'fa-microscope', title: 'QE' },
                        { icon: 'fa-user-tie', title: 'MGM' },
                        { icon: 'fa-boxes-stacked', title: 'Stock' }
                    ];
                    
                    let html = '<div class="flex items-start justify-center w-full max-w-sm pt-2"><div class="flex w-full">';
                    
                    steps.forEach((step, idx) => {
                        const isFinished = currentIndex > idx;
                        const isActive = (currentIndex === idx);
                        
                        let circleClass = "text-gray-400 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800";
                        let lineClass = "bg-gray-200 dark:bg-gray-700";
                        let titleClass = "text-gray-400";
                        
                        if (isFinished) {
                            circleClass = "text-white bg-emerald-500 border-emerald-500 shadow-sm";
                            lineClass = "bg-emerald-500";
                            titleClass = "text-emerald-700 dark:text-emerald-400";
                        } else if (isActive) {
                            if (isOverdue) {
                                circleClass = "text-red-600 border-red-500 bg-red-50 dark:bg-red-900/30 ring-4 ring-red-100 dark:ring-red-900/40";
                                titleClass = "text-red-700 dark:text-red-400 font-extrabold";
                            } else {
                                circleClass = "text-amber-600 border-amber-500 bg-amber-50 dark:bg-amber-900/30 ring-4 ring-amber-100 dark:ring-amber-900/40";
                                titleClass = "text-amber-700 dark:text-amber-400 font-extrabold";
                            }
                        }
                        
                        html += `<div class="flex flex-col items-center flex-1 relative group">`;
                        if (idx < steps.length - 1) {
                            html += `<div class="absolute w-[calc(100%-1.75rem)] top-3.5 left-[calc(50%+0.875rem)] h-[3px] ${lineClass}"></div>`;
                        }
                        html += `<div class="z-10 relative ${circleClass} border-2 w-7 h-7 flex items-center justify-center text-[10px] transition-all duration-300">
                                    <i class="fa-solid ${step.icon}"></i>`;
                        if (isFinished) {
                            html += `<div class="absolute -bottom-1 -right-1.5 bg-white dark:bg-gray-800 w-3.5 h-3.5 flex items-center justify-center leading-none">
                                        <i class="fa-solid fa-circle-check text-emerald-600 text-[12px]"></i>
                                    </div>`;
                        }
                        html += `</div>
                                <span class="text-[9px] mt-1.5 font-bold uppercase tracking-wider text-center ${titleClass}">${step.title}</span>
                            </div>`;
                    });
                    
                    html += '</div></div>';
                    
                    if (['CLOSED', 'OUTSTANDING'].includes(row.status)) {
                        const badgeClass = row.status === 'CLOSED' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-blue-100 text-blue-800 border-blue-200';
                        const iconClass = row.status === 'CLOSED' ? 'fa-flag-checkered' : 'fa-truck-fast';
                        const labelText = row.status === 'CLOSED' ? 'PROJECT CLOSED (DELIVERED)' : 'PARTIAL DELIVERY';
                        html += `<div class="mt-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 ${badgeClass} text-[10px] font-bold tracking-wide shadow-sm">
                                        <i class="fa-solid ${iconClass}"></i> 
                                        ${labelText}
                                    </span>
                                </div>`;
                    }
                    return html;
                }
            },
            { 
                data: 'created_at', 
                name: 'created_at', 
                className: 'px-4 py-2 text-right align-top',
                orderable: false,
                render: function(data, type, row) {
                    const inDate = row.created_at ? new Date(row.created_at.split('T')[0]).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                    let html = `<div class="text-[11px] font-medium text-gray-500 text-right w-full flex flex-col items-end gap-1">
                                    <span class="bg-gray-100 dark:bg-gray-700 px-2 py-1 border border-gray-200 dark:border-gray-600">IN: ${inDate}</span>`;
                    if (row.status === 'CLOSED') {
                        const outDate = row.actual_delivery ? new Date(row.actual_delivery.split('T')[0]).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                        html += `<span class="bg-gray-100 dark:bg-gray-700 px-2 py-1 border border-gray-200 dark:border-gray-600 mt-1">OUT: <strong>${outDate}</strong></span>`;
                        if (row.actual_delivery) {
                            const tDate = new Date(row.delivery_date.split('T')[0]);
                            const aDate = new Date(row.actual_delivery.split('T')[0]);
                            tDate.setHours(0,0,0,0);
                            aDate.setHours(0,0,0,0);
                            const diffTime = aDate.getTime() - tDate.getTime();
                            const diffDays = Math.round(diffTime / (1000 * 3600 * 24));
                            
                            let statusClass, statusText;
                            if (diffDays > 0) {
                                statusClass = "bg-red-100 text-red-700 border-red-200";
                                statusText = '<i class="fa-solid fa-circle-exclamation"></i> Late ' + diffDays + ' Days';
                            } else if (diffDays < 0) {
                                statusClass = "bg-blue-100 text-blue-700 border-blue-200";
                                statusText = '<i class="fa-solid fa-bolt"></i> Early ' + Math.abs(diffDays) + ' Days';
                            } else {
                                statusClass = "bg-green-100 text-green-700 border-green-200";
                                statusText = '<i class="fa-solid fa-check-double"></i> On Time';
                            }
                            html += `<span class="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 text-[10px] font-black tracking-wider uppercase border ${statusClass}">
                                        ${statusText}
                                    </span>`;
                        }
                    } else {
                        const created = new Date(row.created_at);
                        const now = new Date();
                        const diffDays = Math.floor((now - created) / (1000 * 3600 * 24));
                        let agoText = diffDays > 0 ? diffDays + " days" : "Today";
                        html += `<span class="text-amber-600 font-bold mt-1 tracking-wide"><i class="fa-solid fa-hourglass-half animate-pulse"></i> ${agoText}</span>`;
                    }
                    html += `</div>`;
                    return html;
                }
            }
        ]
    });

    function performSearch() {
        $('#indexTable').DataTable().ajax.reload();
    }

    let debounceTimer;
    $('#searchInput').on('input', function() {
        if ($(this).val().length > 0) {
            $('#clearSearchBtn').show();
        } else {
            $('#clearSearchBtn').hide();
        }
        clearTimeout(debounceTimer);
        let val = this.value;
        let table = $('#indexTable').DataTable();
        debounceTimer = setTimeout(function() {
            table.search(val).draw();
        }, 500);
    });
    
    if ($('#searchInput').val() && $('#searchInput').val().length > 0) {
        $('#clearSearchBtn').show();
    }
    
    $('#clearSearchBtn').on('click', function(e) {
        e.preventDefault();
        $('#searchInput').val('');
        $(this).hide();
        $('#indexTable').DataTable().search('').draw();
        $('#searchInput').focus();
    });

    function updateModelDropdown(customerId) {
        $('#modelFilter option').each(function() {
            if ($(this).val() == '') {
                $(this).prop('disabled', false);
                return;
            }
            if (!customerId || customerId == '' || $(this).data('customer') == customerId) {
                $(this).prop('disabled', false).show();
            } else {
                $(this).prop('disabled', true).hide();
            }
        });
        
        if ($('#modelFilter option:selected').prop('disabled')) {
            $('#modelFilter').val('');
        }
        
        setTimeout(function() {
            if ($('#modelFilter').hasClass('select2-hidden-accessible')) {
                $('#modelFilter').trigger('change.select2');
            }
        }, 10);
    }

    $('#customerFilter').on('change', function(e) {
        updateModelDropdown($(this).val());
        performSearch();
    });

    $('#modelFilter').on('change', function(e) {
        performSearch();
    });

    $('#poFilter').on('change', function(e) {
        performSearch();
    });

    $('#targetDateFilter').on('change', function(e) {
        performSearch();
    });

    $('#clearFiltersBtn').on('click', function(e) {
        e.preventDefault();
        
        $('#searchInput').val('');
        $('#clearSearchBtn').hide();
        
        $('#modelFilter').val('').trigger('change.select2');
        $('#poFilter').val('').trigger('change.select2');
        $('#customerFilter').val('').trigger('change.select2');
        $('#targetDateFilter').val('');
        
        updateModelDropdown('');
        $('#indexTable').DataTable().search('').draw();
    });
    
    setTimeout(function() {
        updateModelDropdown($('#customerFilter').val());
    }, 50);
});
</script>
@endpush
