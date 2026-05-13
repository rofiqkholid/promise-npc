@extends('layouts.app')

@section('title', 'Edit Delivery Target')
@section('page_title', 'Master Data / Delivery Targets / Edit')

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 max-w-2xl">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            <i class="fa-solid fa-pen-to-square text-green-600 mr-2"></i> Edit Delivery Target
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

    <form action="{{ route('master.delivery-targets.update', $deliveryTarget->hashed_id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="p-6 space-y-6">
            
            <div class="space-y-1">
                <label for="target_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Target Name (Delivery To) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-solid fa-building text-xs"></i>
                    </div>
                    <input type="text" id="target_name" name="target_name" required value="{{ old('target_name', $deliveryTarget->target_name) }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                        style="padding-left: 2.5rem;" placeholder="Example: MMKI, Fujitrans, PT. SAI...">
                </div>
            </div>

            <div class="flex items-center">
                <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $deliveryTarget->is_active) ? 'checked' : '' }}
                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded dark:border-gray-600 dark:bg-gray-700">
                <label for="is_active" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
                    Active Target (can be selected when creating an event)
                </label>
            </div>

        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 rounded-b-lg">
            <a href="{{ route('master.delivery-targets.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-lg shadow-md shadow-blue-500/20 text-sm font-medium hover:from-blue-700 hover:to-cyan-700 transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Update Target
            </button>
        </div>
    </form>
</div>
@endsection
