@extends('layouts.app')

@section('title', 'Add Role')
@section('page_title', 'User Management / Roles / Add')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700 max-w-2xl">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-plus text-blue-500"></i> Add New Role
        </h2>
        <a href="{{ route('master.roles.index') }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 transition text-sm flex items-center gap-2 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>

    <form action="{{ route('master.roles.store') }}" method="POST" class="p-6">
        @csrf
        
        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="mt-1 w-full border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                    placeholder="e.g., Warehouse Manager">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" id="code" value="{{ old('code') }}" required
                    class="mt-1 w-full border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                    placeholder="e.g., wh_manager">
                <p class="text-xs text-gray-500 mt-1">Unique identifier (no spaces, lowercase).</p>
                @error('code')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                <textarea name="description" id="description" rows="3"
                    class="mt-1 w-full border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                    placeholder="Brief description of what this role can do...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 pt-5 flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 transition shadow-md shadow-blue-500/20 font-medium">
                Save & Continue to Permissions
            </button>
        </div>
    </form>
</div>
@endsection
