@extends('layouts.app')

@section('title', 'Checksheet Approvals')
@section('page_title', 'Checksheet Approvals')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-clipboard-check text-blue-500"></i> Checksheet Approval Queue
        </h2>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="checksheetApprovalTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="px-6 py-4 font-semibold w-16">#</th>
                        <th class="px-6 py-4 font-semibold">Part No / Name</th>
                        <th class="px-6 py-4 font-semibold">Event</th>
                        <th class="px-6 py-4 font-semibold">GR / PO</th>
                        <th class="px-6 py-4 font-semibold">Model / Customer</th>
                        <th class="px-6 py-4 font-semibold text-center">Approval Stage</th>
                        <th class="px-6 py-4 font-semibold text-right w-40">Action</th>
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
            ajax: "{{ route('checksheet-approvals.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-6 py-4 text-slate-800 dark:text-slate-200 text-sm' },
                { data: 'part_info', name: 'part_info', className: 'px-6 py-4', orderable: false },
                { data: 'event_info', name: 'event_info', className: 'px-6 py-4', orderable: false },
                { data: 'po_info', name: 'po_info', className: 'px-6 py-4', orderable: false },
                { data: 'model_customer', name: 'model_customer', className: 'px-6 py-4', orderable: false },
                { data: 'approval_stage', name: 'approval_stage', className: 'px-6 py-4 text-center', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-6 py-4 text-right' }
            ]
        });
    });
</script>
@endpush
