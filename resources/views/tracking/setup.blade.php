@extends('layouts.app')

@section('title', $pageTitle ?? 'Production Routing Setup')
@section('page_title', 'Transaction / ' . ($pageTitle ?? 'Production Routing Setup'))
@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-route' }} text-blue-500"></i> {{ $pageTitle ?? 'Production Routing Setup' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    <!-- Filters -->
    <div class="px-6 pt-4 pb-4 flex flex-col xl:flex-row justify-between items-start xl:items-end gap-4">
        <div class="flex flex-wrap items-center gap-4 w-full xl:w-auto">
            <div class="w-full sm:w-auto">
                <select id="customerFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-48 rounded-md shadow-sm">
                    <option value="all">All Customers</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_filter') == $customer->id ? 'selected' : '' }}>{{ $customer->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <select id="modelFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-48 rounded-md shadow-sm">
                    <option value="all">All Models</option>
                    @foreach($models ?? [] as $mod)
                        <option value="{{ $mod->id }}" data-customer="{{ $mod->customer_id }}" {{ request('model_filter') == $mod->id ? 'selected' : '' }}>{{ $mod->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <select id="poFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-48 rounded-md shadow-sm">
                    <option value="all">All POs</option>
                    @foreach($poList ?? [] as $po)
                        <option value="{{ $po->po_no }}" {{ request('po_filter') == $po->po_no ? 'selected' : '' }}>{{ $po->po_no }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <input type="date" id="targetDateFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-40 rounded-md shadow-sm" value="{{ request('target_date_filter') }}" title="Filter by Target Delivery Date">
            </div>
            <div class="flex items-end w-full sm:w-auto">
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

    <!-- Table -->
    <div class="p-6 pt-0">
        <div class="border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-sm overflow-hidden">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 uppercase text-[11px] tracking-wider font-bold">
                    <tr>
                        <th scope="col" class="px-4 py-3 w-16">#</th>
                        <th scope="col" class="px-4 py-3">EVENT / PO</th>
                        <th scope="col" class="px-4 py-3">PART INFO</th>
                        <th scope="col" class="px-4 py-3">QTY / DELIVERY TARGET</th>
                        <th scope="col" class="px-4 py-3">ROUTING INFO</th>
                        <th scope="col" class="px-4 py-3 text-right w-48">ACTION SETUP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <!-- DataTables will populate this -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let urlParams = new URLSearchParams(window.location.search);
    
    // Add setupTable ID to the table
    $('table').attr('id', 'setupTable');
    
    initPromiseDataTable('#setupTable', {
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
            url: "{{ route('tracking.setup') }}",
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
                data: 'event.po_no', 
                name: 'event.po_no', 
                className: 'px-4 py-2', 
                orderable: false,
                render: function(data, type, row) {
                    const poNo = row.event?.po_no || '';
                    const eventName = row.event?.customer_category?.name || 'Unknown Event';
                    const createdAt = row.created_at ? new Date(row.created_at.split('T')[0]).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                    return `<div class="text-blue-600 dark:text-blue-400 font-bold text-sm">${poNo}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium">${eventName}</div>
                            <div class="text-[10px] text-gray-400 mt-1"><i class="fa-regular fa-clock"></i> Registered: ${createdAt}</div>`;
                }
            },
            { 
                data: 'product.part_no', 
                name: 'product.part_no', 
                className: 'px-4 py-2', 
                orderable: false,
                render: function(data, type, row) {
                    const partNo = row.product?.part_no || '';
                    const partName = row.product?.part_name || '';
                    return `<div class="text-gray-800 dark:text-gray-200 font-bold text-sm">${partNo}</div>
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
                    const deliveryDate = row.delivery_date ? new Date(row.delivery_date.split('T')[0]).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: '2-digit' }) : '-';
                    const isOverdue = row.is_overdue;
                    const colorClass = isOverdue ? 'text-red-500' : 'text-gray-500 dark:text-gray-400';
                    return `<div class="text-gray-800 dark:text-gray-300 font-black text-lg mb-0.5">${qty} <span class="text-xs font-semibold text-gray-500">PCS</span></div>
                            <div class="text-xs ${colorClass} font-medium"><i class="fa-regular fa-calendar md:mr-1"></i> ${deliveryDate}</div>`;
                }
            },
            { 
                data: 'processes', 
                name: 'processes', 
                className: 'px-4 py-2', 
                orderable: false,
                render: function(data, type, row) {
                    if (row.processes && row.processes.length > 0) {
                        let html = '<div class="flex flex-wrap gap-1 mb-1.5">';
                        row.processes.forEach(function(process) {
                            const deptName = process.department?.name || '';
                            const processName = process.process?.process_name || 'Unknown Process';
                            html += `<span class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-[10px] font-semibold px-1.5 py-0.5" title="${deptName}">
                                        <span class="text-gray-400 font-bold">${process.sequence_order}.</span>
                                        ${processName}
                                    </span>`;
                        });
                        html += '</div>';
                        return html;
                    } else {
                        return `<div class="inline-flex items-center gap-1.5 px-2 py-1 bg-orange-50 text-orange-700 border border-orange-200 text-[10px] font-medium">
                                    <i class="fa-solid fa-triangle-exclamation"></i> No Routing Yet
                                </div>`;
                    }
                }
            },
            { 
                data: 'id', 
                name: 'id', 
                className: 'px-4 py-2 text-right align-middle pointer-events-auto w-48',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    if (row.status === 'PO_REGISTERED') {
                        return `<a href="${row.routing_edit_url}" class="inline-flex px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm font-medium transition items-center gap-2 text-xs" style="background-color: #4f46e5;">
                                    <i class="fa-solid fa-route"></i> Set Routing Schedule
                                </a>`;
                    } else {
                        let html = `<div class="flex flex-col items-end gap-2">
                                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed w-full">
                                            <i class="fa-solid fa-check text-[8px]"></i> Setup is ready to send to production
                                        </div>`;
                        if (row.can_rollback_setup) {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
                            html += `<form action="${row.rollback_setup_url}" method="POST">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold transition mt-1" onclick="confirmAction(event, 'Are you sure you want to cancel the routing setup and return the part to the initial stage (PO_REGISTERED)?')">
                                            <i class="fa-solid fa-rotate-left"></i> Rollback Setup
                                        </button>
                                    </form>`;
                        }
                        html += `</div>`;
                        return html;
                    }
                }
            }
        ],
        drawCallback: function() {
            let urlParams = new URLSearchParams(window.location.search);
            let openPartId = urlParams.get('open_part');
            if (openPartId && !window.modalAlreadyOpened) {
                setTimeout(() => {
                    let btn = $(`a[href$="/${openPartId}/routing"]`);
                    if (btn.length > 0) {
                        window.modalAlreadyOpened = true;
                        let url = new URL(window.location);
                        url.searchParams.delete('open_part');
                        window.history.replaceState({}, document.title, url);
                        window.location.href = btn.attr('href');
                    }
                }, 300); // slight delay to ensure everything is ready
            }
        }
    });

    function performSearch() {
        $('#setupTable').DataTable().ajax.reload();
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
        let table = $('#setupTable').DataTable();
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
        $('#setupTable').DataTable().search('').draw();
        $('#searchInput').focus();
    });

    function updateModelDropdown(customerId) {
        $('#modelFilter option').each(function() {
            if ($(this).val() == 'all') {
                $(this).prop('disabled', false);
                return;
            }
            if (!customerId || customerId == 'all' || $(this).data('customer') == customerId) {
                $(this).prop('disabled', false).show();
            } else {
                $(this).prop('disabled', true).hide();
            }
        });
        
        // If the currently selected model is now disabled, reset it
        if ($('#modelFilter option:selected').prop('disabled')) {
            $('#modelFilter').val('all');
        }
        
        // Re-trigger select2 without destroying it
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
        
        $('#modelFilter').val('all').trigger('change.select2');
        $('#poFilter').val('all').trigger('change.select2');
        $('#customerFilter').val('all').trigger('change.select2');
        $('#targetDateFilter').val('');
        
        updateModelDropdown('all');
        $('#setupTable').DataTable().search('').draw();
    });
    
    // Just apply constraints based on current customerFilter on load
    setTimeout(function() {
        updateModelDropdown($('#customerFilter').val());
    }, 50);
});
</script>
@endpush
