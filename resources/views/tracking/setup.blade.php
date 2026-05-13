@extends('layouts.app')

@section('title', $pageTitle ?? 'Production Routing Setup')
@section('page_title', 'Transaksi / ' . ($pageTitle ?? 'Production Routing Setup'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-route' }} text-blue-500"></i> {{ $pageTitle ?? 'Production Routing Setup' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    <!-- Table -->
    <div class="p-6">
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">Event / PO</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part Info</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Qty / Delivery Target</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Routing Info</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-48">Action Setup</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($parts as $part)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group text-sm">
                        <td class="px-6 py-4">
                            <div class="text-blue-600 dark:text-blue-400 font-bold text-sm">{{ optional($part->event)->po_no }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-medium">{{ optional(optional($part->event)->customerCategory)->name ?? 'Unknown Event' }}</div>
                            <div class="text-[10px] text-gray-400 mt-1"><i class="fa-regular fa-clock"></i> Login: {{ $part->created_at->format('d M Y') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-200 font-bold text-sm">{{ optional($part->product)->part_no }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ optional($part->product)->part_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-300 font-black text-lg mb-0.5">{{ number_format($part->qty) }} <span class="text-xs font-semibold text-gray-500">PCS</span></div>
                            <div class="text-xs text-red-500 font-medium"><i class="fa-regular fa-calendar md:mr-1"></i> {{ \Carbon\Carbon::parse($part->delivery_date)->format('d M y') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($part->processes->count() > 0)
                                <div class="flex flex-wrap gap-1 mb-1.5">
                                    @foreach($part->processes as $process)
                                        <span class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 text-[10px] font-semibold px-1.5 py-0.5" title="{{ optional($process->department)->name }}">
                                            <span class="text-gray-400 font-bold">{{ $process->sequence_order }}.</span>
                                            {{ optional($process->process)->process_name ?? 'Unknown Process' }}
                                        </span>
                                    @endforeach
                                </div>
                                <span class="text-[10px] text-gray-400 italic">Routing pending for production</span>
                            @else
                                <div class="inline-flex items-center gap-1.5 px-2 py-1 bg-orange-50 text-orange-700 border border-orange-200 text-[10px] font-medium">
                                    <i class="fa-solid fa-triangle-exclamation"></i> No Routing Yet
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right align-middle pointer-events-auto">
                            @if($part->status === 'PO_REGISTERED')
                                <a href="{{ route('parts.routing.edit', $part->hashed_id) }}" class="inline-flex px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm font-medium transition items-center gap-2 text-xs" style="background-color: #4f46e5;">
                                    <i class="fa-solid fa-route"></i> Set Routing Schedule
                                </a>
                            @else
                                @php
                                    // Can rollback if status is WAITING_DEPT_CONFIRM and no production process is FINISHED yet
                                    $canRollbackSetup = $part->status === 'WAITING_DEPT_CONFIRM' && !$part->processes->where('status', 'FINISHED')->count();
                                @endphp
                                <div class="flex flex-col items-end gap-2">
                                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed w-full">
                                        <i class="fa-solid fa-check text-[8px]"></i> Setup is ready to send to production
                                    </div>
                                    @if($canRollbackSetup)
                                    <form action="{{ route('tracking.setup.rollback', $part->hashed_id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold transition mt-1" onclick="confirmAction(event, 'Are you sure you want to cancel the routing setup and return the part to the initial stage (PO_REGISTERED)?')">
                                            <i class="fa-solid fa-rotate-left"></i> Rollback Setup
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No data available.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($parts->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        {{ $parts->links() }}
    </div>
    @endif
</div>
@endsection

