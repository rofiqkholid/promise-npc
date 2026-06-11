@extends('layouts.app')

@section('title', 'All Promise Users')
@section('page_title', 'User Management / All Promise Users')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-users text-blue-500"></i> All Promise Users
        </h2>

    </div>

    <div class="p-6">
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="promiseUsersTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-4 py-2 font-semibold w-16">No</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Name</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Email</th>
                        <th scope="col" class="px-4 py-2 font-semibold">Registered At</th>
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
        initPromiseDataTable('#promiseUsersTable', {
            ajax: "{{ route('master.promise-users.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-2 font-medium text-slate-500 dark:text-slate-400' },
                { data: 'name', name: 'name', className: 'px-4 py-2 font-bold text-slate-900 dark:text-white' },
                { data: 'email', name: 'email', className: 'px-4 py-2 text-slate-500' },
                { data: 'created_at', name: 'created_at', className: 'px-4 py-2 text-slate-500' }
            ]
        });
    });
</script>
@endpush
