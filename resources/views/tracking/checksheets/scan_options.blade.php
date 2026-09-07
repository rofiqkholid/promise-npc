@extends('layouts.app')

@section('title', 'Select Checksheet Action')
@section('page_title', 'Checksheet Action / ' . (optional($part->event)->po_no ?? 'Part Has Been Deleted'))

@section('content')
<div class="max-w-md mx-auto bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 p-6 rounded-lg mt-8 text-center">
    
    <div class="mb-6">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mb-4">
            <i class="fa-solid fa-qrcode text-3xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Select Action</h2>
        <p class="text-gray-500 dark:text-gray-400 text-sm">
            Part No: <span class="font-bold text-gray-700 dark:text-gray-300">{{ optional($part->product)->part_no }}</span>
        </p>
    </div>

    <div class="space-y-4">
        @if($canMgm)
            <a href="{{ route('checksheets.create', $part->hashed_id) }}" class="block w-full px-4 py-4 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/40 border border-blue-200 dark:border-blue-800 rounded-lg transition text-left group">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-500 text-white rounded-lg flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-base font-bold text-blue-800 dark:text-blue-300">Input MGM Check</h3>
                        <p class="text-xs text-blue-600/80 dark:text-blue-400/80 mt-0.5">Fill checksheet form for MGM check</p>
                    </div>
                </div>
            </a>
        @else
            <div class="block w-full px-4 py-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg text-left opacity-60 cursor-not-allowed" title="You do not have access">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gray-400 text-white rounded-lg flex items-center justify-center text-xl flex-shrink-0">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-base font-bold text-gray-600 dark:text-gray-400">Input MGM Check</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Access denied (No permission)</p>
                    </div>
                </div>
            </div>
        @endif

        @if($canApproval)
            @php
                $isInApprovalPhase = in_array($part->status, ['WAITING_APPROVAL', 'FINISHED', 'CLOSED', 'OUTSTANDING']);
            @endphp
            @if($checksheet && $isInApprovalPhase)
                <a href="{{ route('checksheet-approvals.show', $checksheet->hashed_id) }}" class="block w-full px-4 py-4 bg-purple-50 hover:bg-purple-100 dark:bg-purple-900/20 dark:hover:bg-purple-900/40 border border-purple-200 dark:border-purple-800 rounded-lg transition text-left group">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-500 text-white rounded-lg flex items-center justify-center text-xl flex-shrink-0 group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-file-signature"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-bold text-purple-800 dark:text-purple-300">Checksheet Approval</h3>
                            <p class="text-xs text-purple-600/80 dark:text-purple-400/80 mt-0.5">View or approve this checksheet</p>
                        </div>
                    </div>
                </a>
            @else
                <div class="block w-full px-4 py-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg text-left opacity-60 cursor-not-allowed" title="{{ !$checksheet ? 'Checksheet has not been created by QC/MGM' : 'Part has not been submitted to approval phase' }}">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gray-400 text-white rounded-lg flex items-center justify-center text-xl flex-shrink-0">
                            <i class="fa-solid fa-file-circle-xmark"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-base font-bold text-gray-600 dark:text-gray-400">Checksheet Approval</h3>
                            <p class="text-xs text-gray-500 mt-0.5">{{ !$checksheet ? 'Checksheet not created yet' : 'Not in approval phase yet' }}</p>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="block w-full px-4 py-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg text-left opacity-60 cursor-not-allowed" title="You do not have access">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gray-400 text-white rounded-lg flex items-center justify-center text-xl flex-shrink-0">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-base font-bold text-gray-600 dark:text-gray-400">Checksheet Approval</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Access denied (No permission)</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="mt-8">
        <a href="{{ route('tracking.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 underline"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Tracking</a>
    </div>
</div>
@endsection
