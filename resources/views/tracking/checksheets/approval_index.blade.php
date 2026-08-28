@extends('layouts.app')

@section('title', 'Checksheet Approvals')
@section('page_title', 'Checksheet Approvals')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-clipboard-check text-blue-500"></i> Checksheet Approval Queue
        </h2>
    </div>

    <div class="p-6">
        <!-- Filters -->
        <div class="mb-4 flex flex-col xl:flex-row justify-between gap-4 items-start xl:items-end">
            <div class="flex flex-wrap items-center gap-4 w-full xl:w-auto">
                <div class="w-full sm:w-auto">
                    <select id="filterCustomer" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-40 rounded-md shadow-sm">
                        <option value="">All Customers</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-auto">
                    <select id="filterModel" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-40 rounded-md shadow-sm">
                        <option value="">All Models</option>
                        @foreach($models as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-auto">
                    <select id="filterPo" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-40 rounded-md shadow-sm">
                        <option value="">All POs</option>
                        @foreach($poList ?? [] as $po)
                            <option value="{{ $po->po_no }}">{{ $po->po_no }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-auto">
                    <select id="filterStage" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-40 rounded-md shadow-sm">
                        <option value="">All Stages</option>
                        <option value="WAITING_QE_STAFF">Waiting QE Staff</option>
                        <option value="WAITING_MGM_STAFF">Waiting NPC Staff</option>
                        <option value="WAITING_QE_SPV">Waiting QE SPV</option>
                        <option value="WAITING_MGM_SPV">Waiting NPC SPV</option>
                        <option value="WAITING_QE_ASSMAN">Waiting QE Asst. Mgr</option>
                        <option value="WAITING_MGM_ASSMAN">Waiting NPC Asst. Mgr</option>
                        <option value="WAITING_QE_MGR">Waiting QE Mgr</option>
                        <option value="WAITING_MGM_MGR">Waiting NPC Mgr</option>
                        <option value="APPROVED">Fully Approved</option>
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
                        placeholder="Search..."
                        style="padding-left: 2.5rem; padding-right: 2.5rem;" class="py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-md">
                    <button type="button" id="clearSearchBtn" style="display:none;"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500 transition cursor-pointer z-10">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-sm overflow-hidden">
            <table id="checksheetApprovalTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 uppercase text-[11px] tracking-wider font-bold">
                    <tr>
                        <th class="px-4 py-3 w-16">#</th>
                        <th class="px-4 py-3">PART NO / NAME</th>
                        <th class="px-4 py-3">EVENT</th>
                        <th class="px-4 py-3">GR / PO</th>
                        <th class="px-4 py-3">MODEL / CUSTOMER</th>
                        <th class="px-4 py-3 text-center">APPROVAL STAGE</th>
                        <th class="px-4 py-3 text-right w-40">ACTION</th>
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
        let urlParams = new URLSearchParams(window.location.search);
        initPromiseDataTable('#checksheetApprovalTable', {
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
                url: "{{ route('checksheet-approvals.index') }}",
                data: function (d) {
                    d.stage = $('#filterStage').val();
                    d.customer = $('#filterCustomer').val();
                    d.model = $('#filterModel').val();
                    d.po = $('#filterPo').val();
                }
            },
            stateSaveParams: function (settings, data) {
                data.customFilters = {
                    stage: $('#filterStage').val(),
                    customer: $('#filterCustomer').val(),
                    model: $('#filterModel').val(),
                    po: $('#filterPo').val()
                };
            },
            stateLoadParams: function (settings, data) {
                let urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('search')) {
                    data.search.search = urlParams.get('search');
                }
                if (data.customFilters) {
                    if (data.customFilters.stage !== undefined) {
                        $('#filterStage').val(data.customFilters.stage);
                    }
                    if (data.customFilters.customer !== undefined) {
                        $('#filterCustomer').val(data.customFilters.customer);
                    }
                    if (data.customFilters.model !== undefined) {
                        $('#filterModel').val(data.customFilters.model);
                    }
                    if (data.customFilters.po !== undefined) {
                        $('#filterPo').val(data.customFilters.po);
                    }
                }
            },
            initComplete: function(settings, json) {
                setTimeout(function() {
                    if ($('#filterStage').val()) {
                        $('#filterStage').trigger('change');
                    }
                    if ($('#filterCustomer').val()) {
                        $('#filterCustomer').trigger('change');
                    }
                    if ($('#filterModel').val()) {
                        $('#filterModel').trigger('change');
                    }
                    if ($('#filterStage').hasClass('select2-hidden-accessible')) {
                        $('#filterStage').trigger('change.select2');
                    }
                    if ($('#filterCustomer').hasClass('select2-hidden-accessible')) {
                        $('#filterCustomer').trigger('change.select2');
                    }
                    if ($('#filterModel').hasClass('select2-hidden-accessible')) {
                        $('#filterModel').trigger('change.select2');
                    }
                    if ($('#filterPo').val()) {
                        $('#filterPo').trigger('change');
                    }
                    if ($('#filterPo').hasClass('select2-hidden-accessible')) {
                        $('#filterPo').trigger('change.select2');
                    }
                }, 100);
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-2 text-slate-800 dark:text-slate-200 text-[13px]' },
                { data: 'part_info', name: 'part_info', className: 'px-4 py-2', orderable: false },
                { data: 'event_info', name: 'event_info', className: 'px-4 py-2', orderable: false },
                { data: 'po_info', name: 'po_info', className: 'px-4 py-2', orderable: false },
                { data: 'model_customer', name: 'model_customer', className: 'px-4 py-2', orderable: false },
                { data: 'approval_stage', name: 'approval_stage', className: 'px-4 py-2 text-center', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-4 py-2 text-right' }
            ],
            drawCallback: function() {
                let urlParams = new URLSearchParams(window.location.search);
                let openChecksheetId = urlParams.get('open_checksheet');
                if (openChecksheetId && !window.modalAlreadyOpened) {
                    setTimeout(() => {
                        let btn = $(`a[href$="/checksheet-approvals/${openChecksheetId}"]`);
                        if (btn.length > 0) {
                            window.modalAlreadyOpened = true;
                            let url = new URL(window.location);
                            url.searchParams.delete('open_checksheet');
                            window.history.replaceState({}, document.title, url);
                            window.location.href = btn.attr('href');
                        }
                    }, 200);
                }
            }
        });

        let isResetting = false;

        $('#filterStage, #filterCustomer, #filterModel, #filterPo').on('change', function() {
            if (!isResetting) {
                $('#checksheetApprovalTable').DataTable().ajax.reload();
            }
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
            let table = $('#checksheetApprovalTable').DataTable();
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
            $('#checksheetApprovalTable').DataTable().search('').draw();
            $('#customSearchInput').focus();
        });

        $('#clearFiltersBtn').on('click', function(e) {
            e.preventDefault();
            $('#customSearchInput').val('');
            $('#clearSearchBtn').hide();
            
            $('#filterStage').val('').trigger('change.select2');
            $('#filterCustomer').val('').trigger('change.select2');
            $('#filterModel').val('').trigger('change.select2');
            $('#filterPo').val('').trigger('change.select2');
            
            $('#checksheetApprovalTable').DataTable().search('').draw();
        });
    });

    function confirmRollbackWithReason(event) {
        event.preventDefault();
        const form = $(event.currentTarget).closest('.rollback-form-approval');
        
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
