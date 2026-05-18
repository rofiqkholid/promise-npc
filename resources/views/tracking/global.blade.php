@extends('layouts.app')

@section('title', $pageTitle ?? 'Global Tracking')
@section('page_title', 'Transaksi / ' . ($pageTitle ?? 'Global Tracking'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-list-check' }} text-blue-500"></i> {{ $pageTitle ?? 'Global Tracking' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    @if(isset($metrics))
    <!-- Dashboard Cards -->
    <div class="px-6 pt-6 pb-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Event -->
        <div class="bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-calendar-check mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total Events</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_events']) }}</h3>
            </div>
        </div>
        
        <!-- Card 2: Total PO -->
        <div class="bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-lg shadow-indigo-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-file-invoice mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total PO</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_pos']) }}</h3>
            </div>
        </div>

        <!-- Card 3: Total Part -->
        <div class="bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-amber-600 shadow-lg shadow-amber-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-cubes mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total Parts</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_parts']) }}</h3>
            </div>
        </div>

        <!-- Card 4: PO Close -->
        <div class="bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-flag-checkered mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Closed PO</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_po_close']) }}</h3>
            </div>
        </div>
    </div>
    @endif

    <!-- Table & Modals -->
    <div class="p-6" x-data="{ activeModal: {{ request('open_event', 'null') }}, activeGlobalPhotoModal: null }">

        <!-- Search Form -->
        <form action="{{ route('tracking.index') }}" method="GET" class="mb-4 flex items-center gap-2">
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search PO No, Customer, Delivery To..."
                    class="!pl-10 !pr-10 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-none">
                @if(request('search'))
                <a href="{{ route('tracking.index') }}" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500 transition">
                    <i class="fa-solid fa-xmark"></i>
                </a>
                @endif
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition shadow-sm rounded-none flex items-center gap-2 shrink-0">
                <i class="fa-solid fa-magnifying-glass"></i> Search
            </button>
            @if(request('search'))
            <span class="text-xs text-gray-500 dark:text-gray-400 italic">Results for: <strong>"{{ request('search') }}"</strong></span>
            @endif
        </form>

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-1/4">Event & PO Number</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-1/12 text-center">Part Count</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-1/12">Nearest</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center w-5/12">Overall Progress</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-1/6">System Duration</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($pos as $po)
                        @php
                            $poParts = $po->parts;
                            $totalParts = $poParts->count();
                            
                            $phases = ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK', 'WAITING_MGM_CHECK', 'FINISHED', 'CLOSED'];
                            $steps = [
                                ['icon' => 'fa-file-contract', 'title' => 'Draft'],
                                ['icon' => 'fa-industry', 'title' => 'Part Making'],
                                ['icon' => 'fa-microscope', 'title' => 'QE'],
                                ['icon' => 'fa-user-tie', 'title' => 'MGM'],
                                ['icon' => 'fa-boxes-stacked', 'title' => 'Stok'],
                            ];

                            $reachedCounts = [];
                            $passedCounts = [];
                            $isOverdueAny = false;
                            
                            foreach($steps as $idx => $step) {
                                $rCount = 0;
                                $pCount = 0;
                                foreach($poParts as $p) {
                                    $pIndex = array_search($p->status, $phases);
                                    if ($pIndex === false) $pIndex = -1;
                                    if ($p->status === 'CLOSED') $pIndex = 5;
                                    if ($p->status === 'OUTSTANDING') $pIndex = 4;
                                    
                                    if ($pIndex >= $idx) $rCount++;
                                    if ($pIndex > $idx || ($idx == 4 && in_array($p->status, ['CLOSED']))) {
                                        $pCount++;
                                    }
                                    
                                    if (!in_array($p->status, ['CLOSED'])) {
    $isDeliveryLate = \Carbon\Carbon::parse($p->delivery_date)->endOfDay()->isPast();
    
    // Cek apakah ada sub-proses yang terlambat di tabel npc_part_processes
    $hasLateSubProc = false;
    if ($p->processes) {
        foreach($p->processes as $proc) {
            if (empty($proc->actual_completion_date) && !empty($proc->target_completion_date)) {
                if (\Carbon\Carbon::today()->greaterThan(\Carbon\Carbon::parse($proc->target_completion_date)->startOfDay())) {
                    $hasLateSubProc = true; 
                    break;
                }
            }
        }
    }
    
    // Overdue jika delivery date lewat ATAU ada target proses yang terlewat
    if ($isDeliveryLate || $hasLateSubProc) {
        $isOverdueAny = true;
    }
}
                                }
                                $reachedCounts[$idx] = $rCount;
                                $passedCounts[$idx] = $pCount;
                            }
                            
                            $earliestDelivery = $poParts->min('delivery_date');
                        @endphp
                        
                        <tr @click="activeModal = {{ $po->id }}" class="cursor-pointer bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition group text-sm">
                            <td class="px-6 py-4">
                                <div class="flex items-start gap-4">
                                    <div class="mt-1 w-6 h-6 bg-blue-100 text-blue-500 dark:bg-blue-900/50 dark:text-blue-400 flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-expand text-xs"></i>
                                    </div>
                                    <div>
                                        <div class="text-blue-600 dark:text-blue-400 font-bold text-[11px] uppercase tracking-wide bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 px-2 py-0.5 inline-block mb-1">{{ optional($po->customerCategory)->name ?? 'Unknown Event' }}</div>
                                        <div class="text-gray-800 dark:text-gray-200 font-bold text-sm">{{ $po->po_no }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 px-3 py-1 font-bold text-xs">{{ $totalParts }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($earliestDelivery)
                                    <div class="text-xs {{ \Carbon\Carbon::parse($earliestDelivery)->endOfDay()->isPast() && (!isset($reachedCounts[4]) || $reachedCounts[4] !== $totalParts) ? 'text-red-500 font-bold' : 'text-gray-600 font-medium' }}"><i class="fa-regular fa-calendar-alt md:mr-1"></i> {{ \Carbon\Carbon::parse($earliestDelivery)->format('d M y') }}</div>
                                @else
                                    - 
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium align-middle">
                                <div class="flex w-full items-start justify-center min-w-[300px]">
                                    @foreach($steps as $idx => $step)
                                        @php
                                            $rCount = $reachedCounts[$idx];
                                            $pCount = $passedCounts[$idx];
                                            $pPct = $totalParts > 0 ? round(($pCount / $totalParts) * 100) : 0;
                                            
                                            $rCountNext = isset($reachedCounts[$idx+1]) ? $reachedCounts[$idx+1] : 0;
                                            $rPctNext = $totalParts > 0 ? round(($rCountNext / $totalParts) * 100) : 0;

                                            $lineBg = "bg-gray-200 dark:bg-gray-700";
                                            if ($rPctNext == 100) $lineBg = "bg-emerald-500";
                                            
                                            if ($pPct == 100) {
                                                $circleBorder = "border-emerald-500";
                                                $fillClass = "bg-emerald-500";
                                                $iconColor = "text-white";
                                                $titleClass = "text-emerald-700 dark:text-emerald-400";
                                                $showCheck = true;
                                            } else if ($rCount > 0) {
                                                // Either active or partially passed
                                                $circleBorder = "border-amber-500 ring-2 ring-amber-100";
                                                if ($isOverdueAny && $pPct < 100) {
                                                    $circleBorder = "border-red-500 ring-2 ring-red-100";
                                                }
                                                $fillClass = ($isOverdueAny && $pPct < 100) ? "bg-red-400" : "bg-amber-400";
                                                $iconColor = $pPct > 50 ? "text-white" : (($isOverdueAny && $pPct < 100) ? "text-red-700" : "text-amber-700");
                                                $titleClass = ($isOverdueAny && $pPct < 100) ? "text-red-600 font-extrabold" : "text-amber-600 font-extrabold";
                                                $showCheck = false;
                                            } else {
                                                $circleBorder = "border-gray-200 dark:border-gray-700";
                                                $fillClass = "bg-transparent";
                                                $iconColor = "text-gray-400";
                                                $titleClass = "text-gray-400";
                                                $showCheck = false;
                                            }
                                        @endphp
                                        <div class="flex flex-col items-center flex-1 relative group">
                                            @if($idx < count($steps) - 1)
                                                <div class="absolute w-[calc(100%-2.25rem)] top-[14px] left-[calc(50%+1.125rem)] h-[3px] {{ $lineBg }} overflow-hidden">
                                                    @if($rPctNext > 0 && $rPctNext < 100)
                                                        <div class="h-full bg-emerald-500 transition-all duration-700" style="width: {{ $rPctNext }}%"></div>
                                                    @endif
                                                </div>
                                            @endif
                                            
                                            <div class="relative w-8 h-8 z-10" title="{{ $pCount }} of {{ $totalParts }} Parts selesai ({{ $pPct }}%)">
                                                <div class="relative w-full h-full bg-white dark:bg-gray-800 border-2 {{ $circleBorder }} flex items-center justify-center text-[12px] overflow-hidden shadow-sm transition-all duration-300">
                                                    <div class="absolute bottom-0 left-0 right-0 {{ $fillClass }} transition-all duration-700 ease-out opacity-90" style="height: {{ $pPct }}%; z-index:0;"></div>
                                                    <i class="fa-solid {{ $step['icon'] }} relative z-10 {{ $iconColor }}"></i>
                                                </div>
                                                
                                                @if($showCheck)
                                                    <div class="absolute -bottom-1 -right-1.5 bg-white dark:bg-gray-800 w-4 h-4 flex items-center justify-center z-30 leading-none shadow-sm">
                                                        <i class="fa-solid fa-circle-check text-emerald-600 text-[12px]"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="flex flex-col items-center mt-2 h-8">
                                                <span class="text-[9px] font-bold uppercase tracking-wider text-center {{ $titleClass }} leading-tight block">{{ $step['title'] }}</span>
                                                @if($rCount > 0 && $pPct < 100)
                                                    <span class="text-[9px] font-black {{ $isOverdueAny ? 'text-red-600' : 'text-amber-600' }} mt-0.5">{{ $pPct }}%</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right align-top">
                                <div class="text-[11px] font-medium text-gray-500 text-right w-full flex flex-col items-end gap-1">
                                    <span class="bg-gray-100 dark:bg-gray-700 px-2 py-1 border border-gray-200 dark:border-gray-600">IN: {{ $po->created_at->format('d M y') }}</span>
                                    @if($poParts->where('status', 'CLOSED')->count() === $totalParts && $totalParts > 0)
                                        <span class="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 px-2 py-1 border border-emerald-200 shadow-sm mt-1 font-bold"><i class="fa-solid fa-check-double"></i> COMPLETE</span>
                                    @else
                                        <span class="text-amber-600 font-bold mt-1 tracking-wide"><i class="fa-solid fa-spinner fa-spin"></i> ACTIVE</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                    <p>No data PO / rute aktif.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Render All Modals for details -->
        @foreach($pos as $po)
            <div x-show="activeModal === {{ $po->id }}" class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak style="display: none;">
                <!-- Backdrop -->
                <div x-show="activeModal === {{ $po->id }}"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
              
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div x-show="activeModal === {{ $po->id }}"
                            @click.away="{{ request('from_dashboard') ? "window.location.href='".route('dashboard')."'" : 'activeModal = null' }}"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            class="relative transform overflow-hidden bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-5xl">
                            
                            <!-- Header -->
                            <div class="bg-gray-50/80 dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2" id="modal-title">
                                    <i class="fa-solid fa-list-check text-blue-500"></i> Rincian Part: {{ $po->po_no }}
                                </h3>
                                <button type="button" @click="{{ request('from_dashboard') ? "window.location.href='".route('dashboard')."'" : 'activeModal = null' }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>
                            
                            <!-- Body table -->
                            <div class="px-6 py-4 overflow-y-auto max-h-[70vh]">
                                <div class="border border-gray-200 dark:border-gray-700 overflow-hidden">
                                    <table class="w-full text-[11px] text-left text-slate-600 dark:text-slate-400">
                                        <thead class="bg-blue-50/50 dark:bg-blue-900/20 text-slate-700 dark:text-slate-300 border-b border-gray-200 dark:border-gray-700 uppercase tracking-wider">
                                            <tr>
                                                <th class="px-4 py-3 w-1/4">Part Details</th>
                                                <th class="px-4 py-3 w-1/5">Qty & Target</th>
                                                <th class="px-4 py-3 text-center">Progress</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800" x-data="{ expandedPM: null }">
                                            @foreach($po->parts as $part)
                                                <tr class="hover:bg-blue-50/30 transition">
                                                    <td class="px-4 py-3">
                                                        <div class="font-bold text-gray-800 dark:text-gray-200 text-xs">{{ optional($part->product)->part_no }}</div>
                                                        <div class="text-[10px] text-gray-500 mt-0.5">{{ optional($part->product)->part_name }}</div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="font-bold text-gray-700">{{ number_format($part->qty) }} PCS</div>
                                                        @if($part->delivered_qty > 0)
                                                        <div class="text-[10px] font-bold text-blue-600 mt-0.5">
                                                            <i class="fa-solid fa-truck-ramp-box"></i> Kirim: {{ number_format($part->delivered_qty) }} / {{ number_format($part->qty) }}
                                                        </div>
                                                        @endif
                                                        <div class="text-[10px] {{ \Carbon\Carbon::parse($part->delivery_date)->endOfDay()->isPast() && !in_array($part->status, ['CLOSED']) ? 'text-red-500 font-bold' : 'text-gray-500' }} mt-0.5">
                                                            <i class="fa-regular fa-calendar md:mr-1"></i> {{ \Carbon\Carbon::parse($part->delivery_date)->format('d M Y') }}
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 align-middle">
                                                        @php
                                                            // Steps definition for modal
                                                            $phases = ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK', 'WAITING_MGM_CHECK', 'FINISHED', 'CLOSED'];
                                                            $stepsArr = [
                                                                ['icon' => 'fa-file-contract', 'title' => 'Draft'],
                                                                ['icon' => 'fa-industry', 'title' => 'Part Making'],
                                                                ['icon' => 'fa-microscope', 'title' => 'QE'],
                                                                ['icon' => 'fa-user-tie', 'title' => 'MGM'],
                                                                ['icon' => 'fa-boxes-stacked', 'title' => 'Stok'],
                                                            ];
                                            
                                                           $pIndex = array_search($part->status, $phases);
if ($pIndex === false) $pIndex = -1;
if ($part->status === 'CLOSED') $pIndex = 5;
if ($part->status === 'OUTSTANDING') $pIndex = 4;

// 1. Cek keterlambatan of target pengiriman akhir
$isDeliveryOverdue = \Carbon\Carbon::parse($part->delivery_date)->endOfDay()->isPast();

// 2. Cek keterlambatan spesifik per sub-proses (Tabel npc_part_processes)
$hasLateProcess = false;
if ($part->processes) {
    foreach($part->processes as $proc) {
        if (empty($proc->actual_completion_date) && !empty($proc->target_completion_date)) {
            $targetDate = \Carbon\Carbon::parse($proc->target_completion_date)->startOfDay();
            if (\Carbon\Carbon::today()->greaterThan($targetDate)) {
                $hasLateProcess = true;
                break;
            }
        }
    }
}

// 3. Gabungkan logika: Node merah jika target akhir lewat ATAU target proses lewat
$pOverdue = ($isDeliveryOverdue || $hasLateProcess) && !in_array($part->status, ['CLOSED']);
                                                        @endphp
                                                        <div class="flex items-start w-full min-w-[200px] pt-1">
                                                            @foreach($stepsArr as $sIdx => $stepObj)
                                                                @php
                                                                    $isReached = $pIndex >= $sIdx;
                                                                    $isActive = $pIndex == $sIdx;
                                                                    $isPast = $pIndex > $sIdx;
                                                                    
                                                                    $lineBg = "bg-gray-200 dark:bg-gray-700";
                                                                    if ($isPast) {
                                                                        $lineBg = "bg-emerald-500";
                                                                    }
                                                                    
                                                                    if ($isPast || ($isReached && $sIdx == 4 && in_array($part->status, ['CLOSED']))) {
                                                                        $circleBorder = "border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600";
                                                                        if ($isActive) $circleBorder .= " ring-2 ring-emerald-100";
                                                                    } else if ($isActive) {
                                                                        if ($pOverdue) {
                                                                            $circleBorder = "border-red-500 bg-red-50 dark:bg-red-900/30 text-red-600 ring-2 ring-red-100 shadow-sm";
                                                                        } else {
                                                                            $circleBorder = "border-amber-500 bg-amber-50 dark:bg-amber-900/30 text-amber-600 ring-2 ring-amber-100 shadow-sm";
                                                                        }
                                                                    } else {
                                                                        $circleBorder = "border-gray-200 bg-white dark:bg-gray-800 text-gray-400";
                                                                    }
                                                                @endphp
                                                                <div class="flex flex-col items-center flex-1 relative">
                                                                    @if($sIdx < count($stepsArr) - 1)
                                                                        <div class="absolute h-[2px] {{ $lineBg }}" style="width: calc(100% - 24px); left: calc(50% + 12px); top: 11px;"></div>
                                                                    @endif
                                                                    
                                                                    @if($stepObj['title'] === 'Part Making')
                                                                        <div @click="expandedPM = expandedPM === {{ $part->id }} ? null : {{ $part->id }}" class="z-10 relative border-2 {{ $circleBorder }} w-6 h-6 flex items-center justify-center text-[10px] transition-all duration-300 cursor-pointer hover:scale-125 hover:shadow-md" title="Klik untuk melihat Detail Rute Part Making">
                                                                            <i class="fa-solid {{ $stepObj['icon'] }}"></i>
                                                                            @if($isPast || ($isReached && $sIdx == 4 && in_array($part->status, ['CLOSED'])))
                                                                                <div class="absolute -bottom-1 -right-1 bg-white dark:bg-gray-800 w-3 h-3 flex items-center justify-center text-[8px] text-emerald-600 shadow-sm">
                                                                                    <i class="fa-solid fa-circle-check"></i>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @else
                                                                        <div class="z-10 relative border-2 {{ $circleBorder }} w-6 h-6 flex items-center justify-center text-[10px] transition-all duration-300">
                                                                            <i class="fa-solid {{ $stepObj['icon'] }}"></i>
                                                                            @if($isPast || ($isReached && $sIdx == 4 && in_array($part->status, ['CLOSED'])))
                                                                                <div class="absolute -bottom-1 -right-1 bg-white dark:bg-gray-800 w-3 h-3 flex items-center justify-center text-[8px] text-emerald-600 shadow-sm">
                                                                                    <i class="fa-solid fa-circle-check"></i>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                    
                                                                    <span class="text-[8px] font-bold uppercase tracking-wider text-center mt-1.5 whitespace-nowrap {{ $isActive ? ($pOverdue ? 'text-red-600' : 'text-amber-600') : ($isReached ? 'text-emerald-600' : 'text-gray-400') }}">{{ $stepObj['title'] }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Sub Processes Expandable Row for Part Making -->
                                                <tr x-show="expandedPM === {{ $part->id }}" class="bg-blue-50/20 dark:bg-gray-800/30 transition-all" x-cloak style="display:none;">
                                                    <td colspan="3" class="px-4 py-3 border-l-4 border-blue-400">
                                                        <div class="ml-2">
                                                            <div class="flex items-center justify-between mb-2">
                                                                <h5 class="text-[9px] font-bold uppercase tracking-widest text-slate-500 flex items-center gap-1.5"><i class="fa-solid fa-route"></i> Rute Detail: Part Making</h5>
                                                                <div class="flex gap-2">
                                                                    @if($part->processes->where('status', 'FINISHED')->count() > 0)
                                                                    <button @click="activeGlobalPhotoModal = {{ $part->id }}" class="text-[9px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2 py-0.5 transition flex items-center gap-1 shadow-sm">
                                                                        <i class="fa-solid fa-camera"></i> Cek Qty & Foto
                                                                    </button>
                                                                    @endif
                                                                    @if($part->checksheet)
                                                                    <a href="{{ route('checksheets.edit', ['checksheet' => $part->checksheet->hashed_id, 'readonly' => 1]) }}" class="text-[9px] font-bold text-purple-600 bg-purple-50 hover:bg-purple-100 border border-purple-200 px-2 py-0.5 transition flex items-center gap-1 shadow-sm">
                                                                        <i class="fa-solid fa-clipboard-check"></i> Lihat Checksheet
                                                                    </a>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                            @if($part->processes && $part->processes->count() > 0)
                                                                @php
                                                                    $activeProcId = $part->processes->where('status', 'WAITING')->sortBy('sequence_order')->first()?->id;
                                                                @endphp
                                                                <div class="flex flex-wrap items-center gap-x-1 gap-y-2 mt-1 relative z-10 w-full overflow-x-auto pb-1">
                                                                    @foreach($part->processes->sortBy('sequence_order')->values() as $pIdx => $pProc)
                                                                        @php
                                                                           $spStatus = $pProc->status; 
    $spIcon = "fa-cogs";

    // Kalkulasi apakah node ini Overdue (baik saat masih in-progress maupun setelah selesai)
    $isSpLate = false;
    if (!empty($pProc->target_completion_date)) {
        $targetDate = \Carbon\Carbon::parse($pProc->target_completion_date)->startOfDay();
        
        if (!empty($pProc->actual_completion_date)) {
            // Case 1: SUDAH SELESAI. Cek apakah tanggal aktual melebihi target
            $actualDate = \Carbon\Carbon::parse($pProc->actual_completion_date)->startOfDay();
            if ($actualDate->greaterThan($targetDate)) {
                $isSpLate = true;
            }
        } else {
            // Case 2: NOT COMPLETED. Check if today exceeds target
            if (\Carbon\Carbon::today()->greaterThan($targetDate)) {
                $isSpLate = true;
            }
        }
    }

    // Penentuan Render Visual
    if ($spStatus === 'FINISHED' || !empty($pProc->actual_completion_date)) {
        if ($isSpLate) {
            // Render jika Done TAPI TERLAMBAT (Sequence 2)
            $spBg = "bg-red-100 text-red-700 border-red-300 ring-1 ring-red-200";
            $spIcon = "fa-check"; // Keep check icon because it's completed
        } else {
            // Render jika Done TEPAT WAKTU (Sequence 1)
            $spBg = "bg-emerald-100 text-emerald-700 border-emerald-300";
            $spIcon = "fa-check";
        }
    } elseif ($pProc->id === $activeProcId) {
        // Render MERAH jika proses aktif ini telat, AMBER jika masih aman
        $spBg = $isSpLate 
            ? "bg-red-100/80 text-red-700 border-red-300 ring-1 ring-red-300 shadow-sm" 
            : "bg-amber-100/80 text-amber-700 border-amber-300 ring-1 ring-amber-300 shadow-sm";
        $spIcon = "fa-spinner fa-spin";
    } else {
        // Render queue processes that haven't started
        $spBg = $isSpLate
            ? "bg-red-50 text-red-500 border-red-200 opacity-80"
            : "bg-gray-100 text-gray-500 border-gray-200 opacity-70";
        $spIcon = "fa-clock";
    }
                                                                        @endphp
                                                                        <div class="flex items-center shrink-0">
                                                                            <div class="flex items-center gap-1.5 px-2 py-1 text-[9px] font-bold border {{ $spBg }} shadow-sm">
                                                                                <i class="fa-solid {{ $spIcon }}"></i>
                                                                                {{ optional($pProc->process)->process_name ?? 'Process ' . ($pIdx+1) }}
                                                                            </div>
                                                                            @if($pIdx < $part->processes->count() - 1)
                                                                                <div class="w-3 h-px bg-gray-300 mx-1"></div>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <div class="text-[10px] text-gray-400 italic bg-gray-100/50 p-2 w-max">No route mapping (Routing) yet for this part.</div>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Footer -->
                            <div class="bg-gray-50 dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex flex-row-reverse">
                                <button type="button" @click="{{ request('from_dashboard') ? "window.location.href='".route('dashboard')."'" : 'activeModal = null' }}" class="inline-flex w-full justify-center bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Render Modals for Photos -->
        @foreach($pos as $po)
            @foreach($po->parts as $part)
                <div x-show="activeGlobalPhotoModal === {{ $part->id }}" class="relative z-[150]" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak style="display: none;">
                    <div x-show="activeGlobalPhotoModal === {{ $part->id }}"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
                  
                    <div class="fixed inset-0 z-[160] w-screen overflow-y-auto">
                        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                            <div x-show="activeGlobalPhotoModal === {{ $part->id }}"
                                @click.away="activeGlobalPhotoModal = null"
                                x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave="ease-in duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                class="relative transform overflow-hidden bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                                
                                <!-- Header -->
                                <div class="bg-gray-50/80 dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                    <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                        <i class="fa-solid fa-camera text-blue-500"></i> Production Report: {{ optional($part->product)->part_no }}
                                    </h3>
                                    <button type="button" @click="activeGlobalPhotoModal = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <i class="fa-solid fa-xmark text-xl"></i>
                                    </button>
                                </div>
                                
                                <!-- Body -->
                                <div class="p-6 max-h-[75vh] overflow-y-auto bg-gray-50/50 dark:bg-gray-900/50">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @foreach($part->processes->sortBy('sequence_order') as $idx => $p)
                                            <div class="flex flex-col bg-white dark:bg-gray-800 overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow transition-shadow group {{ $p->status === 'FINISHED' ? '' : 'opacity-60 saturate-50' }}">
                                                <!-- Image Box -->
                                                <div class="relative w-full aspect-video bg-gray-900 flex items-center justify-center border-b border-gray-100 dark:border-gray-700">
                                                    @if($p->photo_proof)
                                                        <img src="{{ Storage::url($p->photo_proof) }}" class="w-full h-full object-contain">
                                                        <a href="{{ Storage::url($p->photo_proof) }}" target="_blank" class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white font-bold text-sm gap-2 backdrop-blur-[2px]">
                                                            <i class="fa-solid fa-expand"></i> Perbesar Foto
                                                        </a>
                                                    @else
                                                        <div class="text-gray-500 dark:text-gray-400 flex flex-col items-center gap-2">
                                                            <i class="fa-solid fa-image text-3xl opacity-50"></i>
                                                            <span class="text-xs font-medium tracking-wide">No Photo Yet</span>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Status Floating Badge -->
                                                    <div class="absolute top-3 right-3 shadow-md">
                                                    @if($p->status === 'FINISHED')
                                                        <span class="px-2.5 py-1 bg-emerald-500 text-white text-[10px] font-black tracking-wider uppercase"><i class="fa-solid fa-check mr-1"></i> Done</span>
                                                    @else
                                                        <span class="px-2.5 py-1 bg-white/90 text-gray-700 text-[10px] font-bold tracking-wider shadow-sm uppercase">{{ $p->status }}</span>
                                                    @endif
                                                    </div>
                                                </div>

                                                <!-- Content Box -->
                                                <div class="p-4 flex flex-col flex-1">
                                                    <h4 class="font-bold text-base text-gray-800 dark:text-gray-100 mb-1 flex items-center gap-2">
                                                        <span class="flex-shrink-0 w-6 h-6 inline-flex items-center justify-center bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400 text-xs shadow-sm">{{ $p->sequence_order }}</span>
                                                        {{ optional($p->process)->process_name ?? 'Process ' . $p->sequence_order }}
                                                    </h4>
                                                    
                                                    <div class="mt-3 space-y-2">
                                                        <div class="flex items-center justify-between text-xs">
                                                            <span class="text-gray-500 dark:text-gray-400 font-medium"><i class="fa-solid fa-building-user w-4"></i> Department:</span> 
                                                            <span class="font-bold text-gray-700 dark:text-gray-200">{{ optional($p->department)->name ?? '-' }}</span>
                                                        </div>
                                                        <div class="flex items-center justify-between text-xs">
                                                            <span class="text-gray-500 dark:text-gray-400 font-medium"><i class="fa-regular fa-calendar-check w-4"></i> Tgl Aktual:</span> 
                                                            <span class="font-bold text-gray-700 dark:text-gray-200">{{ $p->actual_completion_date ? \Carbon\Carbon::parse($p->actual_completion_date)->format('d M Y') : '-' }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
                                                        <div class="flex flex-col">
                                                            <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Target Qty</span>
                                                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-0.5">{{ number_format($part->qty) }} PCS</span>
                                                        </div>
                                                        <div class="flex flex-col items-end">
                                                            <span class="text-[9px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest">Actual QTY</span>
                                                            @if($p->actual_qty)
                                                                <span class="text-sm font-black text-blue-600 dark:text-blue-400 mt-0.5">{{ number_format($p->actual_qty) }} PCS</span>
                                                            @else
                                                                <span class="text-xs font-bold text-amber-500 italic mt-1">Not Reported</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                <!-- Footer -->
                                <div class="bg-gray-50 dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                                    <button type="button" @click="activeGlobalPhotoModal = null" class="px-4 py-2 text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 transition">Close Report</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endforeach

    </div>

    @if($pos->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        {{ $pos->links() }}
    </div>
    @endif
</div>
@endsection

