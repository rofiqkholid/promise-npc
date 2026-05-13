@extends('layouts.app')

@section('title', 'Edit STD Part')
@section('page_title', 'Master Data / STD Parts / Edit')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 max-w-2xl">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-blue-500"></i> Edit STD Part
        </h2>
    </div>
    <form action="{{ route('master.std-parts.update', $std_part->hashed_id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="p-6 space-y-5">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Part Name <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" class="w-full text-sm border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" value="{{ old('name', $std_part->name) }}" required>
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" {{ $std_part->is_active ? 'checked' : '' }}>
                <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Status</label>
            </div>
        </div>
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 rounded-b-lg">
            <a href="{{ route('master.std-parts.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm text-sm font-medium">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-sm font-medium text-sm flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Update Data
            </button>
        </div>
    </form>
</div>
@endsection
