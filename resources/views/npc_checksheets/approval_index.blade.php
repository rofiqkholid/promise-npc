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
        <div class="mb-4 flex flex-col md:flex-row justify-between gap-4">
            <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                <div class="w-full md:w-48">
                    <select id="filterCustomer" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full rounded-md shadow-sm">
                        <option value="">All Customers</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-48">
                    <select id="filterModel" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full rounded-md shadow-sm">
                        <option value="">All Models</option>
                        @foreach($models as $m)
                            <option value="{{ $m->id }}">{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full md:w-48">
                    <select id="filterStage" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full rounded-md shadow-sm">
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
                <div class="flex items-end">
                    <button type="button" id="clearFiltersBtn" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium transition shadow-sm flex items-center gap-2 w-full justify-center">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="checksheetApprovalTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-4 py-2 font-semibold w-16">#</th>
                        <th class="px-4 py-2 font-semibold">Part No / Name</th>
                        <th class="px-4 py-2 font-semibold">Event</th>
                        <th class="px-4 py-2 font-semibold">GR / PO</th>
                        <th class="px-4 py-2 font-semibold">Model / Customer</th>
                        <th class="px-4 py-2 font-semibold text-center">Approval Stage</th>
                        <th class="px-4 py-2 font-semibold text-right w-40">Action</th>
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
        initPromiseDataTable('#checksheetApprovalTable', {
            ajax: {
                url: "{{ route('checksheet-approvals.index') }}",
                data: function (d) {
                    d.stage = $('#filterStage').val();
                    d.customer = $('#filterCustomer').val();
                    d.model = $('#filterModel').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-2 text-slate-800 dark:text-slate-200 text-[13px]' },
                { data: 'part_info', name: 'part_info', className: 'px-4 py-2', orderable: false },
                { data: 'event_info', name: 'event_info', className: 'px-4 py-2', orderable: false },
                { data: 'po_info', name: 'po_info', className: 'px-4 py-2', orderable: false },
                { data: 'model_customer', name: 'model_customer', className: 'px-4 py-2', orderable: false },
                { data: 'approval_stage', name: 'approval_stage', className: 'px-4 py-2 text-center', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-4 py-2 text-right' }
            ]
        });

        let isResetting = false;

        $('#filterStage, #filterCustomer, #filterModel').on('change', function() {
            if (!isResetting) {
                $('#checksheetApprovalTable').DataTable().ajax.reload();
            }
        });

        $('#clearFiltersBtn').on('click', function() {
            isResetting = true;
            $('#filterStage').val('').trigger('change');
            $('#filterCustomer').val('').trigger('change');
            $('#filterModel').val('').trigger('change');
            isResetting = false;
            
            $('#checksheetApprovalTable').DataTable().ajax.reload();
        });
    });
</script>
@endpush
