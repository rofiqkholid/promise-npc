@extends('layouts.app')

@section('title', $pageTitle ?? 'Management Check')
@section('page_title', 'Transaction / ' . ($pageTitle ?? 'Management Check'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-user-tie' }} text-blue-500"></i> {{ $pageTitle ?? 'Management Check' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    <!-- Table -->
    <div class="p-6">

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

        <div class="flex items-end w-full xl:w-auto">
            <div class="relative w-full sm:w-80 xl:w-80">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" id="searchInput"
                    value="{{ request('search') }}"
                    placeholder="Search Part No, Part Name, PO No..."
                    style="padding-left: 2.5rem; padding-right: 2.5rem;" class="py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-md">
                <button type="button" id="clearSearchBtn" style="display:none;"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    </div>

        <div class="border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-sm overflow-hidden">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 uppercase text-[11px] tracking-wider font-bold">
                    <tr>
                        <th scope="col" class="px-4 py-3 w-16">#</th>
                        <th scope="col" class="px-4 py-3 w-72">PRODUCT IDENTITY</th>
                        <th scope="col" class="px-4 py-3 text-center">QUALITY VALIDATION STATUS (QC)</th>
                        <th scope="col" class="px-4 py-3 text-right w-48">FINAL VALIDATION (MGM)</th>
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
    
    // Add mgmTable ID to the table
    $('table').attr('id', 'mgmTable');
    
    initPromiseDataTable('#mgmTable', {
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
                            <i class="fa-solid fa-user-tie text-4xl text-gray-300 dark:text-gray-600"></i>
                            <p>No management check (MGM) submissions currently.</p>
                        </div>`
        },
        search: { search: urlParams.get('search') || '' },
        ajax: {
            url: "{{ route('tracking.mgm') }}",
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
                data: 'product.part_no', 
                name: 'product.part_no', 
                className: 'px-4 py-2',
                orderable: false,
                render: function(data, type, row) {
                    const partNo = row.product?.part_no || '';
                    const partName = row.product?.part_name || '';
                    const poNo = row.event?.po_no || '';
                    const modelName = row.product?.vehicle_model?.name || 'Unknown Model';
                    const qty = Number(row.qty || 0).toLocaleString('id-ID');
                    let html = `<div class="text-gray-800 dark:text-gray-200 font-bold text-sm">${partNo}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1.5">${partName}</div>
                            <div class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50 dark:bg-gray-700 px-2 py-0.5 inline-block border border-gray-200 dark:border-gray-600 mb-2">PO: ${poNo} | MODEL: ${modelName}</div>
                            <div class="text-gray-800 dark:text-gray-300 font-black flex items-center gap-1.5"><i class="fa-solid fa-boxes-stacked text-gray-400"></i> ${qty} <span class="text-xs font-semibold text-gray-500">PCS</span></div>`;
                    if (row.rollback_reason) {
                        html += `<div class="mt-2 flex items-start gap-1.5 text-[10px] text-orange-700 bg-orange-50 p-1.5 border border-orange-200 dark:text-orange-400 dark:bg-orange-900/30 dark:border-orange-800">
                                    <i class="fa-solid fa-clock-rotate-left mt-0.5"></i>
                                    <div class="font-medium text-balance">
                                        <span class="font-bold">Rolled Back:</span> ${row.rollback_reason}
                                    </div>
                                </div>`;
                    }
                    return html;
                }
            },
            { 
                data: 'status', 
                name: 'status', 
                className: 'px-4 py-2 text-center align-middle',
                orderable: false,
                render: function(data, type, row) {
                    if (['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK'].includes(row.status)) {
                        return `<div class="inline-flex flex-col items-center gap-1.5 px-3 py-2 bg-slate-50 border border-slate-200 text-[10px] text-slate-500 italic">
                                    <i class="fa-solid fa-microscope text-sm"></i> Currently in QC Inspection
                                </div>`;
                    } else {
                        const dateInput = row.qc_target_date ? new Date(row.qc_target_date.split('T')[0]).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';
                        return `<div class="flex flex-col items-center gap-1">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 border border-green-200 text-green-700 text-[10px] font-bold shadow-sm"><i class="fa-solid fa-check-double"></i> PASSED QC CERTIFICATION</span>
                                    <span class="text-[11px] text-gray-500 font-medium mt-1">Date Input: ${dateInput}</span>
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
                    let html = '<div class="flex flex-col items-end gap-2">';
                    if (['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK'].includes(row.status)) {
                        html += `<div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed w-full max-w-[150px]">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Not Yet Registered in MGM
                                </div>`;
                    } else if (row.status === 'WAITING_MGM_CHECK') {
                        const masterStatus = row.master_status || 'DRAFT';
                        if (masterStatus !== 'APPROVED') {
                            html += `<div class="px-3 py-2 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800/50 text-[10px] text-yellow-700 dark:text-yellow-400 font-bold flex flex-col items-center justify-center gap-1.5 w-full max-w-[150px] text-center cursor-not-allowed mb-2">
                                        <div><i class="fa-solid fa-clock"></i> Master Not Approved</div>
                                    </div>`;
                        }
                        const createUrl = masterStatus === 'APPROVED' ? row.create_checksheet_url : '#';
                        const extraClass = masterStatus !== 'APPROVED' ? 'opacity-50 cursor-not-allowed pointer-events-none' : '';
                        html += `<a href="${createUrl}" class="inline-flex px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white shadow-sm font-bold transition items-center justify-center gap-2 text-[11px] w-full max-w-[150px] ${extraClass}" style="background-color: #a855f7;">
                                    <i class="fa-solid fa-user-check"></i> MGM Checksheet Form
                                </a>`;
                        if (row.export_checksheet_url) {
                            html += `<a href="${row.export_checksheet_url}" class="inline-flex px-4 py-2 bg-green-500 hover:bg-green-600 text-white shadow-sm font-bold transition items-center justify-center gap-2 text-[11px] w-full max-w-[150px]">
                                        <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a>`;
                        }
                        html += `<p class="text-[9px] text-gray-400 mt-1 italic text-right max-w-[150px] text-balance">Review checksheet and sign the FG parts check</p>`;
                    } else {
                        html += `<div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed w-full max-w-[150px]">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Completed
                                </div>`;
                        if (['WAITING_APPROVAL', 'FINISHED'].includes(row.status) && Number(row.delivered_qty || 0) === 0) {
                            const checksheet = row.checksheet;
                            const canRollback = !checksheet || !checksheet.approval_status || ['WAITING_MGM_STAFF', 'APPROVED'].includes(checksheet.approval_status);
                            if (canRollback) {
                                const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
                                html += `<form action="/tracking/${row.hashed_id}/mgm-rollback" method="POST" class="rollback-form">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <input type="hidden" name="rollback_reason" class="rollback-reason-input">
                                            <button type="button" class="text-[10px] text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold transition mt-1" onclick="confirmRollbackWithReason(event)">
                                                <i class="fa-solid fa-rotate-left"></i> Rollback MGM
                                            </button>
                                        </form>`;
                            }
                        }
                        if (row.export_checksheet_url) {
                            html += `<a href="${row.export_checksheet_url}" class="inline-flex px-4 py-2 bg-green-500 hover:bg-green-600 text-white shadow-sm font-bold transition items-center justify-center gap-2 text-[11px] w-full max-w-[150px]">
                                        <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a>`;
                        }
                    }
                    html += '</div>';
                    return html;
                }
            }
        ],
        drawCallback: function() {
            let urlParams = new URLSearchParams(window.location.search);
            let openPartId = urlParams.get('open_part');
            if (openPartId && !window.modalAlreadyOpened) {
                setTimeout(() => {
                    let btn = $(`a[href$="/${openPartId}/checksheet/create"]`);
                    if (btn.length > 0) {
                        window.modalAlreadyOpened = true;
                        let url = new URL(window.location);
                        url.searchParams.delete('open_part');
                        window.history.replaceState({}, document.title, url);
                        window.location.href = btn.attr('href');
                    }
                }, 300);
            }
        }
    });

    function performSearch() {
        $('#mgmTable').DataTable().ajax.reload();
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
        let table = $('#mgmTable').DataTable();
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
        $('#mgmTable').DataTable().search('').draw();
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
        
        if ($('#modelFilter option:selected').prop('disabled')) {
            $('#modelFilter').val('all');
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
        
        $('#modelFilter').val('all').trigger('change.select2');
        $('#poFilter').val('all').trigger('change.select2');
        $('#customerFilter').val('all').trigger('change.select2');
        $('#targetDateFilter').val('');
        
        updateModelDropdown('all');
        $('#mgmTable').DataTable().search('').draw();
    });
    
    setTimeout(function() {
        updateModelDropdown($('#customerFilter').val());
    }, 50);
});

    function confirmRollbackWithReason(event) {
        event.preventDefault();
        const form = $(event.currentTarget).closest('.rollback-form');
        
        Swal.fire({
            title: 'Rollback to MGM Check',
            text: 'Please enter the reason for rolling back this part. The current checksheet will be deleted permanently.',
            icon: 'warning',
            input: 'textarea',
            inputPlaceholder: 'Type your reason here...',
            inputAttributes: {
                'aria-label': 'Type your reason here'
            },
            showCancelButton: true,
            confirmButtonColor: '#ef4444', // red-500
            cancelButtonColor: '#6b7280', // gray-500
            confirmButtonText: 'Yes, Rollback!',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'You need to write something!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                form.find('.rollback-reason-input').val(result.value);
                form.submit();
            }
        });
    }
</script>
@endpush
