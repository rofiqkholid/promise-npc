@extends('layouts.app')

@section('title', 'Add Promise User')
@section('page_title', 'User Management / All Promise Users / Add')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700 max-w-2xl">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-user-plus text-blue-500"></i> Add New Promise User
        </h2>
        <a href="{{ route('master.promise-users.index') }}" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 transition text-sm flex items-center gap-2 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>

    <form action="{{ route('master.promise-users.store') }}" method="POST" class="p-6">
        @csrf
        
        <div class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="mt-1 w-full border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                    placeholder="John Doe">
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                    class="mt-1 w-full border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                    placeholder="john@example.com">
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" id="password" required minlength="8"
                    class="mt-1 w-full border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                    placeholder="Minimum 8 characters">
                @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-8 pt-5 flex justify-end">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 transition shadow-md shadow-blue-500/20 font-medium flex items-center gap-2">
                <i class="fa-solid fa-save"></i> Save User
            </button>
        </div>
    </form>
</div>
@endsection
