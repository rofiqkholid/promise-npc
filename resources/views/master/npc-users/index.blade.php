@extends('layouts.app')

@section('title', 'NPC Users')
@section('page_title', 'User Management / NPC Users')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-users-gear text-blue-500"></i> NPC Application Users
        </h2>
        <a href="{{ route('master.npc-users.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
            <i class="fa-solid fa-user-plus"></i> Add NPC User
        </a>
    </div>

    <div class="p-6">

    <div class="overflow-x-auto">
        <table id="npcUsersTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16 text-center">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">NIK</th>
                        <th scope="col" class="px-6 py-4 font-semibold">NPC Roles</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
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
        initPromiseDataTable('#npcUsersTable', {
            ajax: "{{ route('master.npc-users.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-6 py-4 font-bold text-center text-indigo-600 dark:text-indigo-400' },
                { data: 'name_email', name: 'name_email', className: 'px-6 py-4' },
                { data: 'nik', name: 'nik', className: 'px-6 py-4' },
                { data: 'roles', name: 'roles', orderable: false, searchable: false, className: 'px-6 py-4' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-6 py-4 text-right' }
            ]
        });
    });
</script>
@endpush
