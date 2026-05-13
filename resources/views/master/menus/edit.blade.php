@extends('layouts.app')

@section('title', 'Edit Menu')
@section('page_title', 'Master Data / Menu Management / Edit')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 max-w-3xl">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-blue-500"></i> Edit Menu
        </h2>
        <a href="{{ route('master.menus.index') }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition text-sm flex items-center gap-2 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>

    <form action="{{ route('master.menus.update', $menu->hashed_id) }}" method="POST" class="p-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title', $menu->title) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="route_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Route Name</label>
                <input type="text" name="route_name" id="route_name" value="{{ old('route_name', $menu->route_name) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                <p class="text-xs text-gray-500">Leave blank if this is a parent dropdown menu.</p>
                @error('route_name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Parent Menu</label>
                <select name="parent_id" id="parent_id"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                    <option value="">-- No Parent (Root Level) --</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', $menu->parent_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->title }}
                        </option>
                    @endforeach
                </select>
                @error('parent_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Icon CSS Class</label>
                <input type="text" name="icon" id="icon" value="{{ old('icon', $menu->icon) }}"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                @error('icon')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label for="order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Display Order <span class="text-red-500">*</span></label>
                <input type="number" name="order" id="order" value="{{ old('order', $menu->order) }}" required
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                @error('order')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2 flex items-center mt-6">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $menu->is_active) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                    Active (Show in Sidebar)
                </label>
            </div>
        </div>

        <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-5 flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition shadow-md shadow-blue-500/20 font-medium">
                Update Menu
            </button>
        </div>
    </form>
</div>
@endsection
