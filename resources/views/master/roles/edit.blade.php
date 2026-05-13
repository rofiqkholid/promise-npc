@extends('layouts.app')

@section('title', 'Edit Role & Permissions')
@section('page_title', 'User Management / Roles / Permissions')

@section('content')
<div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 max-w-5xl">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-blue-500"></i> Edit Role
        </h2>
        <a href="{{ route('master.roles.index') }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition text-sm flex items-center gap-2 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>

    <form action="{{ route('master.roles.update', $role->hashed_id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 pb-8 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" id="code" value="{{ old('code', $role->code) }}" required {{ $role->code === 'admin' ? 'readonly' : '' }}
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm {{ $role->code === 'admin' ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                    @error('code')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                    <input type="text" name="description" id="description" value="{{ old('description', $role->description) }}"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Permission Matrix -->
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Permission Matrix</h3>
                <p class="text-sm text-gray-500">Check the box to grant specific access rights to the menu.</p>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                    <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-semibold">Menu Name</th>
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
                                <td class="px-6 py-3 font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                                    @if($menu->icon) <i class="{{ $menu->icon }} w-5"></i> @endif
                                    {{ $menu->title }}
                                </td>
                                
                                @php
                                    $p = $roleMenuIds->get($menu->id);
                                @endphp

                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" name="permissions[{{ $menu->id }}][can_view]" value="1" {{ $p && $p->can_view ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" name="permissions[{{ $menu->id }}][can_create]" value="1" {{ $p && $p->can_create ? 'checked' : '' }}
                                        class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" name="permissions[{{ $menu->id }}][can_update]" value="1" {{ $p && $p->can_update ? 'checked' : '' }}
                                        class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" name="permissions[{{ $menu->id }}][can_delete]" value="1" {{ $p && $p->can_delete ? 'checked' : '' }}
                                        class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" name="permissions[{{ $menu->id }}][can_approve]" value="1" {{ $p && $p->can_approve ? 'checked' : '' }}
                                        class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                </td>
                            </tr>

                            {{-- Child Menus Rows --}}
                            @foreach($menu->children as $child)
                                @php
                                    $cp = $roleMenuIds->get($child->id);
                                @endphp
                                <tr class="bg-white dark:bg-gray-900 hover:bg-blue-50/30 transition">
                                    <td class="px-6 py-2 pl-12 text-slate-700 dark:text-slate-300 flex items-center gap-2">
                                        <i class="fa-solid fa-turn-up fa-rotate-90 text-gray-400 text-xs"></i>
                                        {{ $child->title }}
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <input type="checkbox" name="permissions[{{ $child->id }}][can_view]" value="1" {{ $cp && $cp->can_view ? 'checked' : '' }}
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <input type="checkbox" name="permissions[{{ $child->id }}][can_create]" value="1" {{ $cp && $cp->can_create ? 'checked' : '' }}
                                            class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <input type="checkbox" name="permissions[{{ $child->id }}][can_update]" value="1" {{ $cp && $cp->can_update ? 'checked' : '' }}
                                            class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <input type="checkbox" name="permissions[{{ $child->id }}][can_delete]" value="1" {{ $cp && $cp->can_delete ? 'checked' : '' }}
                                            class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                    </td>
                                    <td class="px-4 py-2 text-center">
                                        <input type="checkbox" name="permissions[{{ $child->id }}][can_approve]" value="1" {{ $cp && $cp->can_approve ? 'checked' : '' }}
                                            class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-lg shadow-blue-500/30 font-bold flex items-center gap-2">
                <i class="fa-solid fa-save"></i> Save Configuration
            </button>
        </div>
    </form>
</div>
@endsection
