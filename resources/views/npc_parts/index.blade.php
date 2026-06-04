@extends('layouts.app')

@section('title', 'Part Detailss Event')
@section('page_title', 'Master Data / Event / Parts')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-blue-500"></i> Information Event
        </h2>
    </div>
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <span class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase text-gray-500">Event Name</span>
            <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">{{ optional($event->customerCategory)->name ?? '-' }}</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Customer</span>
            <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">{{ optional(optional($event->customerCategory)->customer)->code ?? '-' }}</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Model</span>
            <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">{{ optional(optional(optional($event->parts->first())->product)->vehicleModel)->name ?? '-' }}</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Delivery To</span>
            <span class="block text-sm font-medium text-gray-800 dark:text-gray-200">{{ $event->delivery_to ?? '-' }}</span>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-cubes text-blue-500"></i> Part List / Item
        </h2>
        <div class="flex gap-2">
            <a href="{{ route('events.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition font-medium text-sm flex items-center gap-2">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
            <a href="{{ route('events.parts.create', $event->hashed_id) }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add Part
            </a>
        </div>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="eventPartsTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">#</th>
                        <th scope="col" class="px-6 py-4 font-semibold">PO No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Qty</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Delv Date</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Process</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Dept</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-32">Action</th>
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
        initPromiseDataTable('#eventPartsTable', {
            ajax: "{{ route('events.parts.index', $event->hashed_id) }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-6 py-4 text-slate-800 dark:text-slate-200 text-sm' },
                { data: 'po_no', name: 'po_no', className: 'px-6 py-4', orderable: false },
                { data: 'part_no', name: 'part_no', className: 'px-6 py-4', orderable: false },
                { data: 'part_name', name: 'part_name', className: 'px-6 py-4', orderable: false },
                { data: 'qty', name: 'qty', className: 'px-6 py-4', orderable: false },
                { data: 'delv_date', name: 'delv_date', className: 'px-6 py-4', orderable: false },
                { data: 'process_label', name: 'process_label', className: 'px-6 py-4', orderable: false },
                { data: 'dept_label', name: 'dept_label', className: 'px-6 py-4', orderable: false },
                { data: 'status_label', name: 'status_label', className: 'px-6 py-4 text-sm', orderable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-6 py-4 text-right' }
            ]
        });
    });
</script>
@endpush

