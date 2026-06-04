@extends('layouts.app')

@section('title', 'Master Checkpoint QA')
@section('page_title', 'Master Data / Quality Checkpoints')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-list-check text-blue-500"></i> Checksheet Point Reference Register
        </h2>
        <a href="{{ route('master.checkpoints.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Add New Point
        </a>
    </div>

    <div class="p-6">

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="checkpointsTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16 text-center">Sequence</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Check Item</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-24">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-24">Action</th>
                    </tr>
                </thead>
                <tbody>
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
        initPromiseDataTable('#checkpointsTable', {
            ajax: "{{ route('master.checkpoints.index') }}",
            columns: [
                { data: 'point_number', name: 'point_number', className: 'px-6 py-4' },
                { data: 'check_item', name: 'check_item', className: 'px-6 py-4 font-semibold text-slate-900 dark:text-white whitespace-pre-wrap' },
                { data: 'is_active', name: 'is_active', className: 'px-6 py-4', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-6 py-4 text-right' }
            ],
            order: [[0, 'asc']] // Default order by sequence
        });
    });
</script>
@endpush

