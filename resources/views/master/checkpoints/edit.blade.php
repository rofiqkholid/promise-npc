@extends('layouts.app')

@section('title', 'Edit Master Checkpoint')
@section('page_title', 'Master Data / Quality Checkpoints / Edit')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 max-w-2xl">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            <i class="fa-solid fa-pen-to-square text-indigo-600 mr-2"></i> Edit Checksheet Point
        </h2>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-700 p-4 m-6 rounded-lg mb-0 text-sm border-l-4 border-red-500">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('master.checkpoints.update', $checkpoint->hashed_id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="p-6 space-y-6">
            
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
                <!-- Point Sequence -->
                <div class="space-y-1 sm:col-span-1">
                    <label for="point_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Sequence <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="point_number" name="point_number" required min="1" max="99" value="{{ old('point_number', $checkpoint->point_number) }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white font-mono font-bold text-center">
                </div>

                <!-- Check Item -->
                <div class="space-y-1 sm:col-span-3">
                    <label for="check_item" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Check Item / Description <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="check_item" name="check_item" required value="{{ old('check_item', $checkpoint->check_item) }}" 
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                </div>
            </div>


            
            <div class="flex items-center mt-6">
                <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $checkpoint->is_active) ? 'checked' : '' }}
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700">
                <label for="is_active" class="ml-2 block text-sm text-slate-900 dark:text-slate-300">
                    Use this point in Digital QE Checksheet forms
                </label>
            </div>

        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 rounded-b-lg">
            <a href="{{ route('master.checkpoints.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg shadow-md shadow-blue-500/20 text-sm font-medium hover:from-blue-700 hover:to-cyan-700 transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Update Checkpoint
            </button>
        </div>
    </form>
</div>
@endsection
