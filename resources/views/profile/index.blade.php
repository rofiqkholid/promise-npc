@extends('layouts.app')

@section('title', 'User Profile')
@section('page_title', 'Account / Profile')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    
    <!-- Profile Info Card -->
    <div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/50">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-user-circle text-blue-600"></i>
                Profile Information
            </h2>
        </div>
        <div class="p-6">
            <div class="flex flex-col md:flex-row gap-8 items-center md:items-start">
                <!-- Avatar Section (Icon matching Header) -->
                <div class="shrink-0">
                    <div class="h-24 w-24 bg-slate-100 dark:bg-gray-700 text-slate-500 dark:text-gray-400 flex items-center justify-center text-4xl border border-slate-200 dark:border-gray-600 shadow-sm">
                        <i class="fa-solid fa-user"></i>
                    </div>
                </div>
                
                <!-- Details -->
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Full Name</label>
                        <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $user->name }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Email Address</label>
                        <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $user->email }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">NIK (Employee ID)</label>
                        <p class="text-gray-800 dark:text-gray-200 font-medium">{{ $user->nik }}</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Role / Position</label>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @forelse($user->roles as $role)
                                <span class="px-2.5 py-0.5 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-bold border border-blue-200 dark:border-blue-800">
                                    {{ $role->name }}
                                </span>
                            @empty
                                <span class="text-gray-500 text-sm italic">No Role Assigned</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Card -->
    <div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/50">
            <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-amber-500"></i>
                Account Security
            </h2>
        </div>
        <form action="{{ route('profile.password.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                    <div class="md:pt-2">
                        <label for="current_password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Current Password</label>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Required to verify your identity.</p>
                    </div>
                    <div class="md:col-span-2">
                        <div class="relative">
                            <input type="password" name="current_password" id="current_password" 
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('current_password') border-red-500 @enderror pr-10" 
                                placeholder="Enter current password"
                                required>
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-blue-500 transition-colors" data-target="current_password">
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <hr class="border-gray-100 dark:border-gray-700">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                    <div class="md:pt-2">
                        <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">New Password</label>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Use at least 6 unique characters.</p>
                    </div>
                    <div class="md:col-span-2">
                        <div class="relative">
                            <input type="password" name="password" id="password" 
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror pr-10" 
                                placeholder="Enter new password"
                                required>
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-blue-500 transition-colors" data-target="password">
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                    <div class="md:pt-2">
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Confirm New Password</label>
                    </div>
                    <div class="md:col-span-2">
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" 
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-blue-500 focus:border-blue-500 pr-10" 
                                placeholder="Confirm your new password"
                                required>
                            <button type="button" class="toggle-password absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-blue-500 transition-colors" data-target="password_confirmation">
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-md shadow-blue-500/20 text-sm font-medium hover:from-blue-700 hover:to-cyan-700 transition flex items-center gap-2" onclick="confirmAction(event, 'Are you sure you want to update your password?')">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.toggle-password');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        });
    });
</script>
@endpush
