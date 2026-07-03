@extends('layouts.app')

@section('title', 'Edit NPC User Permissions')
@section('page_title', 'User Management / NPC Users / Edit Permissions')

@section('content')
<div class="mb-6 bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700 max-w-5xl">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-shield-halved text-blue-500"></i> Edit NPC Access: {{ $user->name }}
        </h2>
        <a href="{{ route('master.npc-users.index') }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 transition text-sm flex items-center gap-2 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>

    <form action="{{ route('master.npc-users.update', $user->hashed_id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="p-6">
            <!-- Role Selection -->
            <div class="mb-8 pb-8 border-b border-gray-200 dark:border-gray-700">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Assigned NPC Roles <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @php
                        $currentRoleIds = $user->roles->pluck('id')->toArray();
                    @endphp
                    @foreach($roles as $role)
                        <label class="flex items-start gap-2 p-3 border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-blue-50 dark:hover:bg-gray-700 transition">
                            <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" 
                                {{ in_array($role->id, old('role_ids', $currentRoleIds)) ? 'checked' : '' }}
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
            </div>

            <!-- Permission Matrix -->
            <div class="mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">Individual Permissions (Exceptions)</h3>
                    <p class="text-sm text-gray-500">
                        Locked checkbox (transparent green) means access rights have been granted by the <strong>Role</strong>.<br>
                        Check the grey box to grant specific additional rights for this user (even if their role has no access).
                    </p>
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="document.querySelectorAll('input[type=checkbox]:not(:disabled)').forEach(el => el.checked = true)" class="px-3 py-1.5 bg-green-600 text-white rounded text-sm hover:bg-green-700 transition shadow flex items-center gap-2">
                        <i class="fa-solid fa-check-double"></i> Check All
                    </button>
                    <button type="button" onclick="document.querySelectorAll('input[type=checkbox]:not(:disabled)').forEach(el => el.checked = false)" class="px-3 py-1.5 bg-gray-500 text-white rounded text-sm hover:bg-gray-600 transition shadow flex items-center gap-2">
                        <i class="fa-solid fa-square-minus"></i> Uncheck All
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                    <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th scope="col" class="px-4 py-2 font-semibold">Menu Name</th>
                            <th scope="col" class="px-4 py-4 text-center font-semibold">View (Show)</th>
                            <th scope="col" class="px-4 py-4 text-center font-semibold">Create (Add)</th>
                            <th scope="col" class="px-4 py-4 text-center font-semibold">Update (Edit/Finish)</th>
                            <th scope="col" class="px-4 py-4 text-center font-semibold">Delete</th>
                            <th scope="col" class="px-4 py-4 text-center font-semibold">Approve</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($menus as $menu)
                            {{-- Parent Menu Row --}}
                            <tr class="bg-gray-50 dark:bg-gray-800/80 hover:bg-blue-50/30 transition">
                                <td class="px-4 py-2 font-bold text-slate-800 dark:text-slate-200">
                                    <div class="flex items-center gap-2">
                                        @if($menu->icon) <i class="{{ $menu->icon }} w-5"></i> @endif
                                        {{ $menu->title }}
                                    </div>
                                </td>
                                
                                @php
                                    $inherited = $inheritedPermissions[$menu->id] ?? null;
                                    $p = $userMenuIds->get($menu->id);
                                @endphp

                                <td class="px-4 py-3 text-center">
                                    @if($inherited && $inherited['can_view'])
                                        <input type="checkbox" checked disabled class="h-4 w-4 text-blue-300 border-gray-300 opacity-50 cursor-not-allowed" title="Inherited from Role">
                                    @else
                                        <input type="checkbox" name="permissions[{{ $menu->id }}][can_view]" value="1" {{ $p && $p->can_view ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 cursor-pointer">
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($inherited && $inherited['can_create'])
                                        <input type="checkbox" checked disabled class="h-4 w-4 text-green-300 border-gray-300 opacity-50 cursor-not-allowed" title="Inherited from Role">
                                    @else
                                        <input type="checkbox" name="permissions[{{ $menu->id }}][can_create]" value="1" {{ $p && $p->can_create ? 'checked' : '' }} class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 cursor-pointer">
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($inherited && $inherited['can_update'])
                                        <input type="checkbox" checked disabled class="h-4 w-4 text-orange-300 border-gray-300 opacity-50 cursor-not-allowed" title="Inherited from Role">
                                    @else
                                        <input type="checkbox" name="permissions[{{ $menu->id }}][can_update]" value="1" {{ $p && $p->can_update ? 'checked' : '' }} class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 cursor-pointer">
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($inherited && $inherited['can_delete'])
                                        <input type="checkbox" checked disabled class="h-4 w-4 text-red-300 border-gray-300 opacity-50 cursor-not-allowed" title="Inherited from Role">
                                    @else
                                        <input type="checkbox" name="permissions[{{ $menu->id }}][can_delete]" value="1" {{ $p && $p->can_delete ? 'checked' : '' }} class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 cursor-pointer">
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($inherited && $inherited['can_approve'])
                                        <input type="checkbox" checked disabled class="h-4 w-4 text-purple-300 border-gray-300 opacity-50 cursor-not-allowed" title="Inherited from Role">
                                    @else
                                        <input type="checkbox" name="permissions[{{ $menu->id }}][can_approve]" value="1" {{ $p && $p->can_approve ? 'checked' : '' }} class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 cursor-pointer">
                                    @endif
                                </td>
                            </tr>

                            {{-- Child Menus Rows --}}
                            @foreach($menu->children as $child)
                                @php
                                    $cinherited = $inheritedPermissions[$child->id] ?? null;
                                    $cp = $userMenuIds->get($child->id);
                                @endphp
                                <tr class="bg-white dark:bg-gray-900 hover:bg-blue-50/30 transition">
                                    <td class="px-6 py-2 pl-12 text-slate-700 dark:text-slate-300">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-turn-up fa-rotate-90 text-gray-400 text-xs"></i>
                                            {{ $child->title }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-4 py-2 text-center">
                                        @if($cinherited && $cinherited['can_view'])
                                            <input type="checkbox" checked disabled class="h-4 w-4 text-blue-300 border-gray-300 opacity-50 cursor-not-allowed" title="Inherited from Role">
                                        @else
                                            <input type="checkbox" name="permissions[{{ $child->id }}][can_view]" value="1" {{ $cp && $cp->can_view ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 cursor-pointer">
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if($cinherited && $cinherited['can_create'])
                                            <input type="checkbox" checked disabled class="h-4 w-4 text-green-300 border-gray-300 opacity-50 cursor-not-allowed" title="Inherited from Role">
                                        @else
                                            <input type="checkbox" name="permissions[{{ $child->id }}][can_create]" value="1" {{ $cp && $cp->can_create ? 'checked' : '' }} class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 cursor-pointer">
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if($cinherited && $cinherited['can_update'])
                                            <input type="checkbox" checked disabled class="h-4 w-4 text-orange-300 border-gray-300 opacity-50 cursor-not-allowed" title="Inherited from Role">
                                        @else
                                            <input type="checkbox" name="permissions[{{ $child->id }}][can_update]" value="1" {{ $cp && $cp->can_update ? 'checked' : '' }} class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 cursor-pointer">
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if($cinherited && $cinherited['can_delete'])
                                            <input type="checkbox" checked disabled class="h-4 w-4 text-red-300 border-gray-300 opacity-50 cursor-not-allowed" title="Inherited from Role">
                                        @else
                                            <input type="checkbox" name="permissions[{{ $child->id }}][can_delete]" value="1" {{ $cp && $cp->can_delete ? 'checked' : '' }} class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 cursor-pointer">
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        @if($cinherited && $cinherited['can_approve'])
                                            <input type="checkbox" checked disabled class="h-4 w-4 text-purple-300 border-gray-300 opacity-50 cursor-not-allowed" title="Inherited from Role">
                                        @else
                                            <input type="checkbox" name="permissions[{{ $child->id }}][can_approve]" value="1" {{ $cp && $cp->can_approve ? 'checked' : '' }} class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 cursor-pointer">
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white hover:bg-blue-700 transition shadow-lg shadow-blue-500/30 font-bold flex items-center gap-2">
                <i class="fa-solid fa-save"></i> Save Roles & Permissions
            </button>
        </div>
    </form>
</div>
@endsection
