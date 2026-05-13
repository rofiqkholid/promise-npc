@extends('layouts.app')

@section('title', 'Edit Category Mapping')
@section('page_title', 'Master Data / Customer Category / Edit')

@section('content')
<div class="max-w-2xl bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-pen-to-square text-blue-500"></i> Edit Category Mapping
        </h2>
        <a href="{{ route('master.customer-categories.index') }}" class="text-slate-500 hover:text-slate-700 dark:text-gray-400 dark:hover:text-gray-200 transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back
        </a>
    </div>

    <form action="{{ route('master.customer-categories.update', $customerCategory->hashed_id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="p-6 space-y-5">
            <div>
                <label for="customer_id" class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-1">Customer <span class="text-red-500">*</span></label>
                <select name="customer_id" id="customer_id" class="select2 block w-full" required data-placeholder="Select Customer...">
                    <option value=""></option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $customerCategory->customer_id) == $customer->id ? 'selected' : '' }}>
                            {{ $customer->code }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-1">Customer Category Term <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $customerCategory->name) }}" required
                    class="block w-full px-3 py-2 bg-white dark:bg-gray-900 border border-slate-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:text-white sm:text-sm transition-colors">
                @error('name')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-blue-50/50 dark:bg-gray-700/30 p-4 rounded-lg border border-blue-100 dark:border-gray-600">
                <label for="internal_category_id" class="block text-sm font-medium text-slate-700 dark:text-gray-300 mb-1">Map to Internal Category <span class="text-red-500">*</span></label>
                <select name="internal_category_id" id="internal_category_id" class="select2 block w-full" required data-placeholder="Select Internal Category...">
                    <option value=""></option>
                    @foreach($internalCategories as $ic)
                        <option value="{{ $ic->id }}" {{ old('internal_category_id', $customerCategory->internal_category_id) == $ic->id ? 'selected' : '' }}>
                            {{ $ic->name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-slate-500 dark:text-gray-400"><i class="fa-solid fa-info-circle mr-1 text-blue-500"></i> The terms above will be recorded as the internal category you selected in the master data.</p>
                @error('internal_category_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 rounded-b-lg">
            <a href="{{ route('master.customer-categories.index') }}" class="px-4 py-2 border border-slate-300 dark:border-gray-600 rounded-lg text-slate-700 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700 transition">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow-md shadow-blue-500/20 transition">Update Mapping</button>
        </div>
    </form>
</div>
@endsection
