@extends('layouts.app')

@section('title', $pageTitle ?? 'Global Tracking')
@section('page_title', 'Transactions / ' . ($pageTitle ?? 'Global Tracking'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-list-check' }} text-blue-500"></i> {{ $pageTitle ?? 'Global Tracking' }}
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
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total Events</p>
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
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total Parts</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_parts']) }}</h3>
            </div>
        </div>

        <!-- Card 4: PO Close -->
        <div class="bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-flag-checkered mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Closed PO</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_po_close']) }}</h3>
            </div>
        </div>
    </div>
    @endif

    <!-- Table & Modals -->
    <div class="p-6" x-data="{ activeModal: {{ request('open_event', 'null') }}, activeGlobalPhotoModal: null }">

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="globalTrackingTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-1/4">Event & PO Number</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-1/12 text-center">Part Count</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-1/12">Nearest</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center w-5/12">Overall Progress</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-1/6">System Duration</th>
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
        initPromiseDataTable('#globalTrackingTable', {
            ajax: "{{ route('tracking.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-6 py-4 text-center text-slate-800 dark:text-slate-200 text-sm font-medium' },
                { data: 'po_info', name: 'po_no', className: 'px-6 py-4 align-top', orderable: false },
                { data: 'part_count', name: 'part_count', className: 'px-6 py-4 text-center align-middle', orderable: false, searchable: false },
                { data: 'nearest', name: 'nearest', className: 'px-6 py-4 align-middle', orderable: false, searchable: false },
                { data: 'overall_progress', name: 'overall_progress', className: 'px-6 py-4 align-middle', orderable: false, searchable: false },
                { data: 'system_duration', name: 'system_duration', className: 'px-6 py-4 text-right align-top', orderable: false, searchable: false }
            ]
        });
    });
</script>
@endpush
