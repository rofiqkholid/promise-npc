@extends('layouts.app')

@section('title', 'Edit Internal Category')
@section('page_title', 'Master Data / Internal Category / Edit')

@section('content')
<div class="max-w-2xl bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-blue-500"></i> Edit Internal Category
        </h2>
        <a href="{{ route('master.internal-categories.index') }}" class="text-slate-500 hover:text-slate-700 dark:text-gray-400 dark:hover:text-gray-200 transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back
        </a>
    </div>

    <form action="{{ route('master.internal-categories.update', $internalCategory->hashed_id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="p-6 space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-1">Internal Category Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $internalCategory->name) }}" required
                    class="block w-full px-3 py-2 bg-white dark:bg-gray-900 border border-slate-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white sm:text-sm transition-colors">
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 rounded-b-lg">
            <a href="{{ route('master.internal-categories.index') }}" class="px-4 py-2 border border-slate-300 dark:border-gray-600 rounded-lg text-slate-700 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700 transition">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md shadow-blue-500/20 transition">Update Category</button>
        </div>
    </form>
</div>
@endsection
