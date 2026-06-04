@extends('layouts.app')

@section('title', 'Master Data Event')
@section('page_title', 'Data Event')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-calendar-check text-blue-500"></i> Register Project Event
        </h2>
        <div class="flex items-center gap-3">
            <a href="{{ route('events.import') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm font-medium text-sm flex items-center gap-2">
                <i class="fa-solid fa-file-excel text-green-600"></i> Import Excel
            </a>
            <a href="{{ route('events.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add Event
            </a>
        </div>
    </div>

    <div class="p-6">

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="eventsTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">#</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Event Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Customer</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Model</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Category</th>
                        <th scope="col" class="px-6 py-4 font-semibold">GR</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Delivery To</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-32">Action</th>
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
        initPromiseDataTable('#eventsTable', {
            ajax: "{{ route('events.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-6 py-4 text-slate-800 dark:text-slate-200 text-sm' },
                { data: 'event_name', name: 'event_name', className: 'px-6 py-4' },
                { data: 'customer', name: 'customer', className: 'px-6 py-4 text-slate-600 dark:text-slate-400 text-sm', orderable: false },
                { data: 'model', name: 'model', className: 'px-6 py-4' },
                { data: 'category', name: 'category', className: 'px-6 py-4' },
                { data: 'gr', name: 'gr', className: 'px-6 py-4' },
                { data: 'delivery_to', name: 'delivery_to', className: 'px-6 py-4' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-6 py-4 text-right' }
            ]
        });
    });
</script>
@endpush
