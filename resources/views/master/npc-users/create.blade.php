@extends('layouts.app')

@section('title', 'Add NPC User')
@section('page_title', 'User Management / NPC Users / Add Access')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700 max-w-2xl">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-user-plus text-blue-500"></i> Grant NPC Access to User
        </h2>
        <a href="{{ route('master.npc-users.index') }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 transition text-sm flex items-center gap-2 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>

    <form action="{{ route('master.npc-users.store') }}" method="POST" class="p-6">
        @csrf
        
        <div class="space-y-6">
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Promise User <span class="text-red-500">*</span></label>
                
                <div x-data="{
                    open: false,
                    search: '',
                    selectedId: '{{ old('user_id') }}',
                    selectedName: '',
                    users: [
                        @foreach($availableUsers as $user)
                            { id: '{{ $user->id }}', name: '{{ addslashes($user->name) }}', email: '{{ addslashes($user->email) }}' },
                        @endforeach
                    ],
                    get filteredUsers() {
                        if (this.search === '') {
                            return this.users;
                        }
                        return this.users.filter(u => u.name.toLowerCase().includes(this.search.toLowerCase()) || u.email.toLowerCase().includes(this.search.toLowerCase()));
                    },
                    init() {
                        if (this.selectedId) {
                            let user = this.users.find(u => u.id == this.selectedId);
                            if (user) {
                                this.selectedName = user.name + ' (' + user.email + ')';
                            }
                        }
                        this.$watch('open', value => {
                            if (value) {
                                setTimeout(() => { this.$refs.searchInput.focus() }, 50);
                            }
                        });
                    },
                    selectUser(user) {
                        this.selectedId = user.id;
                        this.selectedName = user.name + ' (' + user.email + ')';
                        this.open = false;
                        this.search = '';
                    }
                }" class="relative w-full" @click.away="open = false">
                    <input type="hidden" name="user_id" :value="selectedId">
                    
                    <button type="button" @click="open = !open"
                        class="relative w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 shadow-sm pl-4 pr-10 py-3 text-left cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm transition-all hover:bg-gray-50 dark:hover:bg-gray-700">
                        
                        <div class="flex items-center">
                            <span class="block truncate" x-text="selectedName || 'Search and select a user...'" :class="{'text-gray-400': !selectedName, 'text-gray-900 dark:text-white font-medium': selectedName}"></span>
                        </div>
                        
                        <span class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                            <i class="fa-solid fa-chevron-down text-gray-400 text-sm transition-transform duration-300" :class="{'rotate-180': open}"></i>
                        </span>
                    </button>

                    <div x-show="open" style="display: none;" 
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute z-50 mt-2 w-full bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden focus:outline-none">
                        
                        <!-- Search Area -->
                        <div class="p-2 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                            <div class="relative flex items-center">
                                <input type="text" x-model="search" x-ref="searchInput" placeholder="Type name or email to search..."
                                    class="w-full bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white py-2 pl-3 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent sm:text-sm transition-shadow placeholder-gray-400"
                                    @keydown.enter.prevent="">
                                <button type="button" x-show="search.length > 0" @click="search = ''; $refs.searchInput.focus()" class="absolute right-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition" style="display: none;">
                                    <i class="fa-solid fa-times-circle"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Dropdown List -->
                        <ul class="overflow-y-auto py-1 overscroll-contain" style="max-height: 260px;">
                            <template x-for="user in filteredUsers" :key="user.id">
                                <li @click="selectUser(user)"
                                    class="text-gray-900 dark:text-gray-200 cursor-pointer select-none relative py-2.5 pl-4 pr-9 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-blue-900/40 dark:hover:text-blue-300 transition-colors group border-b border-gray-50 dark:border-gray-800/50 last:border-0">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 text-blue-600 dark:bg-blue-800 dark:text-blue-200 text-xs flex items-center justify-center font-bold mr-3 group-hover:bg-blue-500 group-hover:text-white transition-colors shadow-sm" x-text="user.name.substring(0,1)"></div>
                                        <div class="flex flex-col">
                                            <span class="font-semibold block truncate text-sm" x-text="user.name"></span>
                                            <span class="font-normal block truncate text-xs text-gray-500 dark:text-gray-400 group-hover:text-blue-500 dark:group-hover:text-blue-300 transition-colors mt-0.5" x-text="user.email"></span>
                                        </div>
                                    </div>
                                    <span x-show="selectedId === user.id" class="absolute inset-y-0 right-0 flex items-center pr-5 text-blue-600 dark:text-blue-400">
                                        <i class="fa-solid fa-check text-lg"></i>
                                    </span>
                                </li>
                            </template>
                            <li x-show="filteredUsers.length === 0" class="text-gray-500 dark:text-gray-400 cursor-default select-none relative py-8 px-4 text-center">
                                <i class="fa-solid fa-user-xmark text-3xl mb-3 text-gray-300 dark:text-gray-600 block"></i>
                                <span class="text-sm font-medium">No users found</span>
                                <p class="text-xs text-gray-400 mt-1">Try searching with a different keyword</p>
                            </li>
                        </ul>
                    </div>
                </div>

                @if($availableUsers->isEmpty())
                    <p class="text-yellow-600 dark:text-yellow-400 text-xs mt-2"><i class="fa-solid fa-info-circle"></i> All existing Promise users already have an NPC Role. Please create a new user in the All Promise Users menu first.</p>
                @endif
                @error('user_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assign NPC Roles <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($roles as $role)
                        <label class="flex items-start gap-2 p-3 border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 transition">
                            <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" 
                                {{ in_array($role->id, old('role_ids', [])) ? 'checked' : '' }}
                                class="mt-0.5 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $role->name }}</span>
                                <span class="text-xs text-gray-500 font-mono">{{ $role->code }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('role_ids')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                @error('role_ids.*')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 pt-5 border-t border-gray-200 dark:border-gray-700 flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 transition shadow-md shadow-blue-500/20 font-medium flex items-center gap-2" {{ $availableUsers->isEmpty() ? 'disabled' : '' }}>
                <i class="fa-solid fa-save"></i> Grant Access
            </button>
        </div>
    </form>
</div>
@endsection
