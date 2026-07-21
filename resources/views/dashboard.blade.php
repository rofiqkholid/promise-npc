@extends('layouts.app')

@section('title', 'NPC Dashboard')
@section('page_title', 'Dashboard')

@section('css')
<style>
    /* Prevent body scroll to enforce single page view */
    body { overflow: hidden; }
</style>
@endsection

@section('content')
<!-- Dashboard Wrapper to dynamically fill space seamlessly -->
<div class="flex-1 flex flex-col gap-4 min-h-0 overflow-hidden">

    <!-- KPI Cards (Row 1) -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 flex-none">
        <!-- Total PO -->
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <div @click="open = !open" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex justify-between items-center cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors h-full">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">PO On Hand</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white leading-none">{{ $metrics['total_po'] }}</h3>
                </div>
                <div class="w-10 h-10 bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg">
                    <i class="fa-solid fa-file-invoice"></i>
                </div>
            </div>
            <!-- Popover -->
            <div x-show="open" x-transition.opacity class="absolute top-full left-0 mt-1 w-full max-h-56 overflow-y-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg z-50 p-2 custom-scrollbar" style="display: none;">
                @if(count($metrics['total_po_list'] ?? []) > 0)
                    <table class="w-full text-left text-xs">
                        <thead class="sticky top-0 bg-white dark:bg-slate-800 z-10">
                            <tr class="text-slate-500 border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-1 px-1 w-8">No</th>
                                <th class="pb-1 px-1">PO No.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($metrics['total_po_list'] ?? [] as $idx => $item)
                            <tr class="border-b border-slate-100 dark:border-slate-700/50 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-1.5 px-1 text-slate-500">{{ $idx + 1 }}</td>
                                <td class="py-1.5 px-1 font-medium text-slate-800 dark:text-slate-200">{{ $item->po_no ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-xs text-center text-slate-500 py-2">No data</p>
                @endif
            </div>
        </div>

        <!-- PO on hand -->
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <div @click="open = !open" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex justify-between items-center cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors h-full">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Total Active Projects</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white leading-none">{{ $metrics['po_on_hand'] }}</h3>
                </div>
                <div class="w-10 h-10 bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg">
                    <i class="fa-solid fa-clipboard-list"></i>
                </div>
            </div>
            <!-- Popover -->
            <div x-show="open" x-transition.opacity class="absolute top-full left-0 mt-1 w-full max-h-56 overflow-y-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg z-50 p-2 custom-scrollbar" style="display: none;">
                @if(count($metrics['po_on_hand_list'] ?? []) > 0)
                    <table class="w-full text-left text-xs">
                        <thead class="sticky top-0 bg-white dark:bg-slate-800 z-10">
                            <tr class="text-slate-500 border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-1 px-1 w-8">No</th>
                                <th class="pb-1 px-1">Model</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($metrics['po_on_hand_list'] ?? [] as $idx => $item)
                            <tr class="border-b border-slate-100 dark:border-slate-700/50 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-1.5 px-1 text-slate-500">{{ $idx + 1 }}</td>
                                <td class="py-1.5 px-1 font-medium text-slate-800 dark:text-slate-200">{{ optional(optional(optional($item->parts->first())->product)->vehicleModel)->name ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-xs text-center text-slate-500 py-2">No data</p>
                @endif
            </div>
        </div>

        <!-- PO Complete -->
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <div @click="open = !open" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex justify-between items-center cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors h-full">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">PO Complete</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white leading-none">{{ $metrics['po_complete'] }}</h3>
                </div>
                <div class="w-10 h-10 bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 text-lg">
                    <i class="fa-solid fa-file-circle-check"></i>
                </div>
            </div>
            <!-- Popover -->
            <div x-show="open" x-transition.opacity class="absolute top-full left-0 mt-1 w-full max-h-56 overflow-y-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg z-50 p-2 custom-scrollbar" style="display: none;">
                @if(count($metrics['po_complete_list'] ?? []) > 0)
                    <table class="w-full text-left text-xs">
                        <thead class="sticky top-0 bg-white dark:bg-slate-800 z-10">
                            <tr class="text-slate-500 border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-1 px-1 w-8">No</th>
                                <th class="pb-1 px-1">PO No.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($metrics['po_complete_list'] ?? [] as $idx => $item)
                            <tr class="border-b border-slate-100 dark:border-slate-700/50 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-1.5 px-1 text-slate-500">{{ $idx + 1 }}</td>
                                <td class="py-1.5 px-1 font-medium text-slate-800 dark:text-slate-200">{{ $item->po_no ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-xs text-center text-slate-500 py-2">No data</p>
                @endif
            </div>
        </div>

        <!-- Stock -->
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <div @click="open = !open" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex justify-between items-center cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors h-full">
                <div>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Remain PO</p>
                    <h3 class="text-2xl font-bold text-slate-800 dark:text-white leading-none">{{ $metrics['stock'] }}</h3>
                </div>
                <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <!-- Popover -->
            <div x-show="open" x-transition.opacity class="absolute top-full left-0 mt-1 w-full max-h-56 overflow-y-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg z-50 p-2 custom-scrollbar" style="display: none;">
                @if(count($metrics['stock_list'] ?? []) > 0)
                    <table class="w-full text-left text-xs">
                        <thead class="sticky top-0 bg-white dark:bg-slate-800 z-10">
                            <tr class="text-slate-500 border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-1 px-1 w-8">No</th>
                                <th class="pb-1 px-1">PO No.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($metrics['stock_list'] ?? [] as $idx => $item)
                            <tr class="border-b border-slate-100 dark:border-slate-700/50 last:border-0 hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                <td class="py-1.5 px-1 text-slate-500">{{ $idx + 1 }}</td>
                                <td class="py-1.5 px-1 font-medium text-slate-800 dark:text-slate-200">{{ $item->event->po_no ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-xs text-center text-slate-500 py-2">No data</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex gap-4 min-h-0 overflow-hidden">
        
        <!-- Left Column: Pipeline & Charts -->
        <div class="w-3/5 flex flex-col gap-4 min-h-0">
            


            <!-- Charts Row -->
            <div class="flex-1 flex flex-col min-h-0">
                <!-- Trend Chart -->
                <div class="flex-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex flex-col relative min-h-0">
                    @php 
                        $hasFilter = ($filterYear != date('Y') || $filterMonth || $filterCustomer || !empty($filterPo) || !empty($filterModel)); 
                    @endphp
                    <div class="flex justify-between items-center mb-2 flex-none">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-white">Plan vs Actual</h3>
                        <button type="button" onclick="document.getElementById('chartFilterForm').classList.toggle('hidden')" class="text-slate-500 hover:text-primary-600 focus:outline-none transition-colors" title="Toggle Filters">
                            <i class="fa-solid fa-filter {{ $hasFilter ? 'text-primary-500' : '' }}"></i>
                        </button>
                    </div>
                    
                    <form id="chartFilterForm" method="GET" action="{{ route('dashboard') }}" class="{{ $hasFilter ? '' : 'hidden' }} mb-3 bg-slate-50 dark:bg-slate-700/50 p-2 rounded border border-slate-200 dark:border-slate-600">
                        <div class="flex flex-wrap md:flex-nowrap items-end gap-2 text-[10px] pb-1">
                            <div class="flex-1 min-w-[90px]">
                                <label class="font-semibold text-slate-500 mb-0.5 block truncate">PO No.</label>
                                <input type="text" name="chart_po" value="{{ $filterPo }}" placeholder="Search..." class="w-full border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded px-2 py-1 focus:outline-none focus:border-primary-500 transition-colors" onchange="this.form.submit()">
                            </div>
                            
                            <div class="flex-1 min-w-[90px]">
                                <label class="font-semibold text-slate-500 mb-0.5 block truncate">Model</label>
                                <select name="chart_model" class="w-full border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded px-2 py-1 focus:outline-none focus:border-primary-500 transition-colors truncate" onchange="this.form.submit()">
                                    <option value="">All Models</option>
                                    @foreach($vehicleModels as $mod)
                                        <option value="{{ $mod->id }}" {{ $filterModel == $mod->id ? 'selected' : '' }}>{{ $mod->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex-1 min-w-[70px]">
                                <label class="font-semibold text-slate-500 mb-0.5 block truncate">Year</label>
                                <select name="chart_year" class="w-full border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded px-2 py-1 focus:outline-none focus:border-primary-500 transition-colors" onchange="this.form.submit()">
                                    @foreach($availableYears as $y)
                                        <option value="{{ $y }}" {{ $filterYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="flex-1 min-w-[80px]">
                                <label class="font-semibold text-slate-500 mb-0.5 block truncate">Month</label>
                                <select name="chart_month" class="w-full border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded px-2 py-1 focus:outline-none focus:border-primary-500 transition-colors" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $filterMonth == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                            {{ date('M', mktime(0, 0, 0, $m, 10)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex-1 min-w-[90px]">
                                <label class="font-semibold text-slate-500 mb-0.5 block truncate">Customer</label>
                                <select name="chart_customer" class="w-full border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded px-2 py-1 focus:outline-none focus:border-primary-500 transition-colors truncate" onchange="this.form.submit()">
                                    <option value="">All</option>
                                    @foreach($customerCategories as $cust)
                                        <option value="{{ $cust->id }}" {{ $filterCustomer == $cust->id ? 'selected' : '' }}>{{ $cust->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($hasFilter)
                                <div class="flex-none">
                                    <a href="{{ route('dashboard') }}" class="inline-flex justify-center items-center bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:hover:bg-rose-900/50 dark:text-rose-400 border border-rose-200 dark:border-rose-800 rounded px-2.5 py-1 transition-colors font-medium">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </form>
                    <div class="flex-1 w-full relative">
                        <div style="min-height: {{ max(150, count($trendChart['labels']) * 90) }}px; height: 100%;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Dept / Customer Charts (Tabs/Stacked or Side by Side internally) -->
                <!-- Let's put Dept Workload here -->
                {{-- Department Bottleneck temporarily hidden
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex flex-col relative min-h-0">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex-none mb-2">Department Bottleneck</h3>
                    <div class="flex-1 w-full relative">
                        <canvas id="deptChart"></canvas>
                    </div>
                </div>
                --}}
            </div>

        </div>

        <!-- Right Column: Lists & Tables -->
        <div class="w-2/5 flex flex-col gap-2 min-h-0">
            
            <!-- Nearest Events -->
            <div class="flex-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col min-h-0">
                <div class="py-1.5 px-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex items-center justify-between flex-none">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white"><i class="fa-regular fa-calendar-check text-slate-400 mr-1.5"></i> Upcoming Events</h3>
                    <a href="{{ route('events.index') }}" class="text-[9px] text-primary-600 font-medium">View All</a>
                </div>
                
                <div class="flex-1 overflow-x-auto overflow-y-auto custom-scrollbar relative">
                    @if($nearestEvents->count() > 0)
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 z-10">
                                <tr>
                                    <th class="py-1 px-2 pl-3 text-[9px] font-semibold text-slate-500 uppercase">Customer</th>
                                    <th class="py-1 px-2 text-[9px] font-semibold text-slate-500 uppercase">Model</th>
                                    <th class="py-1 px-2 text-[9px] font-semibold text-slate-500 uppercase">Event</th>
                                    <th class="py-1 px-2 text-[9px] font-semibold text-slate-500 uppercase">Batch</th>
                                    <th class="py-1 px-2 pr-3 text-[9px] font-semibold text-slate-500 uppercase text-right">Delv Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @foreach($nearestEvents as $evt)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 cursor-pointer transition-colors" onclick="window.location.href='{{ route('tracking.index', ['search' => $evt->event->po_no, 'open_event' => $evt->npc_event_id, 'from_dashboard' => 1]) }}'">
                                        <td class="py-1 px-2 pl-3">
                                            <p class="text-[9px] font-semibold text-slate-800 dark:text-white">{{ $evt->product->customer->code ?? '-' }}</p>
                                        </td>
                                        <td class="py-1 px-2">
                                            <p class="text-[9px] text-slate-600 dark:text-slate-400">{{ $evt->product->vehicleModel->name ?? '-' }}</p>
                                        </td>
                                        <td class="py-1 px-2">
                                            <p class="text-[9px] text-slate-600 dark:text-slate-400">{{ $evt->event->customerCategory->name ?? '-' }}</p>
                                        </td>
                                        <td class="py-1 px-2">
                                            <span class="text-[8px] font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded">{{ $evt->event->deliveryGroup->name ?? '-' }}</span>
                                        </td>
                                        <td class="py-1 px-2 pr-3 text-right">
                                            <span class="text-[9px] font-bold {{ \Carbon\Carbon::parse($evt->delivery_date)->isPast() ? 'text-rose-500' : 'text-slate-700 dark:text-slate-300' }}">
                                                {{ \Carbon\Carbon::parse($evt->delivery_date)->format('d M Y') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="h-full flex flex-col items-center justify-center p-4 text-center text-slate-400">
                            <i class="fa-regular fa-calendar-xmark text-xl mb-1"></i>
                            <p class="text-[10px]">No upcoming events found.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Required: ECN & Stagnant -->
            <div class="flex-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col min-h-0 overflow-hidden">
                <div class="py-1.5 px-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex-none flex justify-between items-center">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white flex items-center">
                        <span class="w-1.5 h-1.5 bg-rose-500 rounded-full mr-1.5 animate-pulse"></span> Action Required
                    </h3>
                </div>
                
                <div class="flex-1 overflow-y-auto custom-scrollbar p-0">
                    @if($ecnUpdates->count() > 0 || $stagnantParts->count() > 0 || $rolledBackParts->count() > 0)
                        <div class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <!-- Rolled Back Items -->
                            @foreach($rolledBackParts as $part)
                                <div class="py-1.5 px-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer" onclick="window.location.href='{{ route('tracking.index', ['search' => $part->event->po_no ?? '', 'open_event' => $part->npc_event_id, 'from_dashboard' => 1]) }}'">
                                    <div class="flex justify-between items-start">
                                        <div class="w-full">
                                            <span class="text-[8px] font-bold text-red-600 bg-red-100 px-1 uppercase mb-0.5 inline-block">Rolled Back</span>
                                            <p class="text-[10px] font-semibold text-slate-800 dark:text-white leading-tight">{{ $part->product->part_no }}</p>
                                            <div class="text-[9px] text-slate-500 mt-0.5 truncate flex justify-between w-full">
                                                <span>PO: {{ $part->event->po_no ?? '-' }}</span>
                                            </div>
                                            <p class="text-[9px] text-red-600 font-medium mt-1 truncate">
                                                <i class="fa-solid fa-triangle-exclamation"></i> {{ $part->rollback_reason }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <!-- ECN Items -->
                            @foreach($ecnUpdates as $part)
                                <div class="py-1.5 px-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer" onclick="window.location.href='{{ route('tracking.index', ['search' => $part->event->po_no ?? '', 'open_event' => $part->npc_event_id, 'from_dashboard' => 1]) }}'">
                                    <div class="flex justify-between items-start">
                                        @php
                                            $oldRev = optional($part->drawingRevision)->revision_no !== null && optional($part->drawingRevision)->revision_no !== '' ? optional($part->drawingRevision)->revision_no : '-';
                                            $oldEcn = optional($part->drawingRevision)->ecn_no ?: 'No ECN';
                                            
                                            $newRevObj = optional(optional(optional($part->product)->docPackage)->currentRevision);
                                            $newRev = $newRevObj->revision_no !== null && $newRevObj->revision_no !== '' ? $newRevObj->revision_no : '-';
                                            $newEcn = $newRevObj->ecn_no ?: 'No ECN';
                                            
                                            $isIdentical = ("Rev $oldRev ($oldEcn)" === "Rev $newRev ($newEcn)");
                                        @endphp
                                        <div>
                                            @if($isIdentical)
                                                <span class="text-[8px] font-bold text-amber-600 bg-amber-100 px-1 uppercase mb-0.5 inline-block" title="Document file re-uploaded">File Updated</span>
                                            @else
                                                <span class="text-[8px] font-bold text-rose-600 bg-rose-100 px-1 uppercase mb-0.5 inline-block">ECN Update</span>
                                            @endif
                                            <p class="text-[10px] font-semibold text-slate-800 dark:text-white leading-tight">{{ $part->product->part_no }}</p>
                                            @if(isset($part->po_count) && $part->po_count > 1)
                                                <p class="text-[9px] text-slate-500 mt-0.5" title="{{ $part->po_list ?? '' }}">{{ $part->po_count }} Active PO(s)</p>
                                            @else
                                                <p class="text-[9px] text-slate-500 mt-0.5">PO: {{ $part->event->po_no ?? '-' }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <!-- Stagnant Items -->
                            @foreach($stagnantParts as $part)
                                <div class="py-1.5 px-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer" onclick="window.location.href='{{ route('tracking.index', ['search' => $part->event->po_no ?? '', 'open_event' => $part->npc_event_id, 'from_dashboard' => 1]) }}'">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="text-[8px] font-bold text-amber-600 bg-amber-100 px-1 uppercase mb-0.5 inline-block">Stagnant > 3d</span>
                                            <p class="text-[10px] font-semibold text-slate-800 dark:text-white leading-tight">{{ $part->product->part_no }}</p>
                                            <p class="text-[9px] text-slate-500 mt-0.5">PO: {{ $part->event->po_no ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="h-full flex flex-col items-center justify-center p-4 text-center text-slate-400">
                            <i class="fa-regular fa-circle-check text-xl mb-1 text-emerald-400"></i>
                            <p class="text-[10px]">No pending actions.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Remain Delivery -->
            <div class="flex-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col min-h-0 overflow-hidden">
                <div class="py-1.5 px-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex-none flex justify-between items-center">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white flex items-center">
                        <i class="fa-solid fa-truck-ramp-box text-emerald-500 mr-1.5"></i> Remain Delivery
                    </h3>
                    <a href="{{ route('tracking.index') }}" class="text-[9px] text-primary-600 font-medium">View All</a>
                </div>
                
                <div class="flex-1 overflow-y-auto custom-scrollbar p-0">
                    @if($remainDeliveries->count() > 0)
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 z-10 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="py-1 px-3 text-[9px] font-semibold text-slate-500 uppercase">Model</th>
                                    <th class="py-1 px-3 text-[9px] font-semibold text-slate-500 uppercase text-right">Remain Qty</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @foreach($remainDeliveries as $deliv)
                                    @php
                                        $models = [];
                                        foreach($deliv->parts as $part) {
                                            if ($part->product && $part->product->vehicleModel) {
                                                $models[] = $part->product->vehicleModel->name;
                                            }
                                        }
                                        $modelStr = count($models) > 0 ? implode(', ', array_unique($models)) : '-';
                                        $percentage = $deliv->total_items > 0 ? round(($deliv->remaining_items / $deliv->total_items) * 100) : 0;
                                    @endphp
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer" onclick="window.location.href='{{ route('tracking.index', ['search' => $deliv->po_no ?? '', 'open_event' => $deliv->id, 'from_dashboard' => 1]) }}'">
                                        <td class="py-1.5 px-3">
                                            <p class="text-[10px] font-bold text-slate-800 dark:text-white truncate max-w-[120px]" title="{{ $modelStr }}">{{ $modelStr }}</p>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="text-[9px] font-medium text-slate-500 dark:text-slate-400">PO: {{ $deliv->po_no ?? '-' }}</span>
                                                <span class="text-[8px] text-slate-400">&bull;</span>
                                                <p class="text-[8px] text-slate-500">{{ $deliv->nearest_delivery_date ? \Carbon\Carbon::parse($deliv->nearest_delivery_date)->format('d M y') : '-' }}</p>
                                            </div>
                                        </td>
                                        <td class="py-1 px-3 text-right">
                                            <span class="text-[10px] font-bold text-slate-800 dark:text-white">{{ $percentage }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="h-full flex flex-col items-center justify-center p-4 text-center text-slate-400">
                            <i class="fa-regular fa-circle-check text-xl mb-1 text-emerald-400"></i>
                            <p class="text-[10px]">No remaining deliveries.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#9ca3af' : '#64748b';
    const gridColor = isDark ? '#334155' : '#f1f5f9';

    // Set defaults for Chart.js
    Chart.defaults.color = textColor;
    Chart.defaults.font.family = "'Outfit', sans-serif";
    
    // Register DataLabels plugin globally if available
    if (typeof ChartDataLabels !== 'undefined') {
        Chart.register(ChartDataLabels);
    }

    // 1. Trend Chart (Horizontal Grouped Bar for Plan vs Actual)
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: @json($trendChart['labels']),
                datasets: [
                    {
                        label: 'Plan',
                        data: @json($trendChart['new']),
                        backgroundColor: '#3b82f6', // blue
                        borderRadius: 4,
                        maxBarThickness: 20,
                        order: 2
                    },
                    {
                        label: 'Actual',
                        data: @json($trendChart['finished']),
                        backgroundColor: '#10b981', // emerald
                        borderRadius: 4,
                        maxBarThickness: 20,
                        order: 3
                    }
                ]
            },
            options: {
                indexAxis: 'y', // Make it horizontal
                layout: {
                    padding: { top: 10, right: 20 }
                },
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: {
                        color: '#ffffff',
                        font: { weight: 'bold', size: 11 },
                        formatter: function(value) {
                            return value > 0 ? value : '';
                        },
                        anchor: 'center',
                        align: 'center'
                    },
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, boxWidth: 8 }
                    },
                    tooltip: {
                        mode: 'index',
                        axis: 'y',
                        intersect: false,
                        backgroundColor: isDark ? '#1e293b' : '#ffffff',
                        titleColor: isDark ? '#f8fafc' : '#0f172a',
                        bodyColor: isDark ? '#cbd5e1' : '#475569',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        borderWidth: 1,
                        callbacks: {
                            title: function(tooltipItems) {
                                const index = tooltipItems[0].dataIndex;
                                const originalLabel = tooltipItems[0].chart.data.labels[index];
                                return originalLabel;
                            }
                        }
                    }
                },
                scales: {
                    x: { 
                        type: 'linear',
                        display: true,
                        position: 'bottom',
                        grid: { color: gridColor }, 
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    },
                    y: { 
                        grid: { display: false }, 
                        ticks: { autoSkip: false }
                    }
                }
            }
        });
    }

    // 2. Department Workload Chart (Horizontal Bar)
    const deptCtx = document.getElementById('deptChart');
    if (deptCtx) {
        const labels = @json($departmentChart['labels']);
        const data = @json($departmentChart['data']);
        
        new Chart(deptCtx, {
            type: 'bar',
            data: {
                labels: labels.length > 0 ? labels : ['No Data'],
                datasets: [{
                    label: 'Waiting Processes',
                    data: data.length > 0 ? data : [0],
                    backgroundColor: '#f59e0b',
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    datalabels: { display: false },
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#ffffff',
                        titleColor: isDark ? '#f8fafc' : '#0f172a',
                        bodyColor: isDark ? '#cbd5e1' : '#475569',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        borderWidth: 1,
                    }
                },
                scales: {
                    x: { 
                        grid: { color: gridColor }, 
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    // 3. Customer Proportion (Doughnut)
    const custCtx = document.getElementById('customerChart');
    if (custCtx) {
        const labels = @json($customerChart['labels']);
        const data = @json($customerChart['data']);
        
        new Chart(custCtx, {
            type: 'doughnut',
            data: {
                labels: labels.length > 0 ? labels : ['No Data'],
                datasets: [{
                    data: data.length > 0 ? data : [1],
                    backgroundColor: [
                        '#3b82f6', '#8b5cf6', '#ec4899', '#f43f5e', '#f59e0b', '#10b981', '#14b8a6', '#06b6d4'
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    datalabels: { display: false },
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 20 }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#ffffff',
                        titleColor: isDark ? '#f8fafc' : '#0f172a',
                        bodyColor: isDark ? '#cbd5e1' : '#475569',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        borderWidth: 1,
                    }
                }
            }
        });
    }
});
</script>
@endpush