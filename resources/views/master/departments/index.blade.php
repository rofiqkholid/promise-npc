@extends('layouts.app')

@section('title', 'Master Department')
@section('page_title', 'Master Data / Department')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-building text-blue-500"></i> Department Register
        </h2>
        <a href="{{ route('master.departments.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Add Department
        </a>
    </div>

    <div class="p-6">

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="departmentsTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Code/Short Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Full Name (Remarks)</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-32">Status</th>
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
        initPromiseDataTable('#departmentsTable', {
            ajax: "{{ route('master.departments.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-6 py-4 font-medium text-slate-500 dark:text-slate-400' },
                { data: 'name', name: 'name', className: 'px-6 py-4 font-bold text-slate-900 dark:text-white' },
                { data: 'full_name', name: 'full_name', className: 'px-6 py-4 text-slate-700 dark:text-slate-300 font-medium' },
                { data: 'is_active', name: 'is_active', className: 'px-6 py-4' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-6 py-4 text-right' }
            ]
        });
    });
</script>
@endpush

