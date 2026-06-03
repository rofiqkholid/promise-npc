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

        <!-- Search Form -->
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <form action="{{ route('master.npc-users.index') }}" method="GET" class="w-full sm:w-auto"
                x-data="{ 
                    searchQuery: '{{ request('search') }}',
                    performSearch() {
                        fetch('?search=' + this.searchQuery)
                        .then(res => res.text())
                        .then(html => {
                            let doc = new DOMParser().parseFromString(html, 'text/html');
                            document.querySelector('tbody').innerHTML = doc.querySelector('tbody').innerHTML;
                            
                            let pagination = document.getElementById('pagination-container');
                            let newDocPagination = doc.getElementById('pagination-container');
                            if (pagination && newDocPagination) {
                                pagination.innerHTML = newDocPagination.innerHTML;
                            } else if (newDocPagination) {
                                let container = document.querySelector('.bg-white.shadow');
                                let div = document.createElement('div');
                                div.id = 'pagination-container';
                                div.className = 'px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50';
                                div.innerHTML = newDocPagination.innerHTML;
                                container.appendChild(div);
                            } else if (pagination) {
                                pagination.innerHTML = '';
                            }
                            
                            window.history.pushState(null, '', '?search=' + this.searchQuery);
                        });
                    }
                }" @submit.prevent="performSearch()">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text" name="search" x-model="searchQuery" x-ref="searchInput" placeholder="Search name, email..." 
                        @input.debounce.500ms="performSearch()"
                        class="!pl-10 !pr-10 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-none">
                    
                    <button type="button" x-show="searchQuery.length > 0" style="display: none;"
                        @click="searchQuery = ''; performSearch(); $refs.searchInput.focus()"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500 transition outline-none">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Email</th>
                        <th scope="col" class="px-6 py-4 font-semibold">NPC Roles</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($users as $user)
                    <tr class="bg-white dark:bg-gray-800 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group">
                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-slate-100 dark:bg-gray-700 text-slate-500 dark:text-gray-400 flex items-center justify-center border border-slate-200 dark:border-gray-600">
                                    <i class="fa-solid fa-user text-xs"></i>
                                </div>
                                {{ $user->name }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($user->roles as $role)
                                    <span class="px-2.5 py-1 text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
                                <a href="{{ route('master.npc-users.edit', $user->hashed_id) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 transition" title="Edit Hak Akses Individu">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </a>
                                @if(auth()->id() !== $user->nik)
                                <form action="{{ route('master.npc-users.destroy', $user->hashed_id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="confirmRevoke(this.closest('form'))" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 transition" title="Revoke NPC Access">
                                        <i class="fa-solid fa-user-xmark"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No NPC users found. Assign NPC roles to users from the Promise Users list.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
        </tbody>
        </table>
    </div>

    <div id="pagination-container" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
        {{ $users->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmRevoke(form) {
    Swal.fire({
        title: 'Revoke NPC Access?',
        text: 'Are you sure you want to revoke NPC access from this user? They will remain in the master Promise Users list.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: '<i class="fa-solid fa-user-xmark mr-1"></i> Yes, Revoke Access',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
        color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#111827',
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>
@endpush
