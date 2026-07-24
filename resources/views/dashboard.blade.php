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
<div class="flex flex-col gap-4 h-[calc(100vh-10rem)] overflow-hidden">

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
    <div class="flex-1 flex flex-col gap-4 min-h-0 overflow-hidden">
        
        <!-- Top Row: Charts Container -->
        <div class="flex-1 flex gap-4 min-h-0">
            
            @php 
                $hasFilter = ($filterYear != date('Y') || $filterMonth || $filterCustomer || !empty($filterPo) || !empty($filterModel)); 
            @endphp
            
            <!-- Left Widget: Plan vs Actual -->
            <div class="w-1/2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col min-h-0 rounded overflow-hidden" x-data="{ currentSlide: 0, maxSlide: {{ count($poChunks) > 0 ? count($poChunks) - 1 : 0 }} }">
                <!-- Header & Controls -->
                <div class="p-2.5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex justify-between items-center flex-none">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white flex items-center">
                        <i class="fa-solid fa-chart-line text-primary-500 mr-2"></i> Plan vs Actual
                    </h3>
                    <div class="flex items-center gap-3">
                        <!-- Slider Controls -->
                        <div class="flex items-center bg-white dark:bg-slate-700 rounded border border-slate-200 dark:border-slate-600 overflow-hidden" x-show="maxSlide > 0" style="display: none;">
                            <button @click="currentSlide = Math.max(0, currentSlide - 1)" :disabled="currentSlide === 0" :class="{'opacity-30 cursor-not-allowed bg-slate-100 dark:bg-slate-800': currentSlide === 0, 'hover:bg-slate-100 text-primary-600': currentSlide > 0}" class="px-2 py-0.5 transition-colors text-slate-500">
                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                            </button>
                            <span class="px-2 py-0.5 text-[10px] font-bold text-slate-700 dark:text-slate-300 border-x border-slate-200 dark:border-slate-600 min-w-[3rem] text-center" x-text="(currentSlide + 1) + ' / ' + (maxSlide + 1)"></span>
                            <button @click="currentSlide = Math.min(maxSlide, currentSlide + 1)" :disabled="currentSlide === maxSlide" :class="{'opacity-30 cursor-not-allowed bg-slate-100 dark:bg-slate-800': currentSlide === maxSlide, 'hover:bg-slate-100 text-primary-600': currentSlide < maxSlide}" class="px-2 py-0.5 transition-colors text-slate-500">
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </button>
                        </div>
                        <button type="button" onclick="document.getElementById('chartFilterForm').classList.toggle('hidden')" class="text-slate-500 hover:text-primary-600 focus:outline-none transition-colors" title="Toggle Filters">
                            <i class="fa-solid fa-filter {{ $hasFilter ? 'text-primary-500' : '' }}"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Form Filter -->
                <form id="chartFilterForm" method="GET" action="{{ route('dashboard') }}" class="{{ $hasFilter ? '' : 'hidden' }} bg-slate-50 dark:bg-slate-700/50 p-2 border-b border-slate-200 dark:border-slate-600 flex-none">
                    <div class="flex flex-wrap md:flex-nowrap items-end gap-2 text-[10px] pb-1">
                        <div class="flex-1 min-w-[90px]">
                            <label class="font-semibold text-slate-500 mb-0.5 block truncate">PO No.</label>
                            <input type="text" name="chart_po" value="{{ $filterPo }}" placeholder="Search..." class="w-full border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded px-2 py-1 focus:outline-none focus:border-primary-500 transition-colors" onchange="this.form.submit()">
                        </div>
                        
                        <div class="flex-1 min-w-[90px]">
                            <label class="font-semibold text-slate-500 mb-0.5 block truncate">Model</label>
                            <select name="chart_model" class="w-full border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded px-2 py-1 focus:outline-none focus:border-primary-500 transition-colors truncate" onchange="this.form.submit()">
                                <option value="">All</option>
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

                        @if($hasFilter)
                            <div class="flex-none">
                                <a href="{{ route('dashboard') }}" class="inline-flex justify-center items-center bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-900/30 border border-rose-200 rounded px-2.5 py-1 font-medium">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </form>

                <!-- Canvas Wrapper -->
                <div class="flex-1 relative overflow-hidden flex flex-col min-h-0 bg-slate-50/50 dark:bg-slate-900/20">
                    @if(count($poChunks) > 0)
                        @foreach($poChunks as $index => $chunk)
                        <div :class="currentSlide === {{ $index }} ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'" class="absolute inset-0 flex flex-col min-h-0 w-full h-full p-4 transition-opacity duration-300">
                            <canvas id="trendChart-{{ $index }}"></canvas>
                        </div>
                        @endforeach
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                            <i class="fa-solid fa-chart-line text-2xl mb-2 opacity-50"></i>
                            <p class="text-xs">No active POs</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Widget: PO Progress -->
            <div class="w-1/2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col min-h-0 rounded overflow-hidden" x-data="{ currentSlide: 0, maxSlide: {{ count($poChunks) > 0 ? count($poChunks) - 1 : 0 }} }">
                <!-- Header -->
                <div class="p-2.5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex justify-between items-center flex-none">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white flex items-center">
                        <i class="fa-solid fa-chart-column text-emerald-500 mr-2"></i> PO Progress
                    </h3>
                    <!-- Slider Controls -->
                    <div class="flex items-center gap-3">
                        <div class="flex items-center bg-white dark:bg-slate-700 rounded border border-slate-200 dark:border-slate-600 overflow-hidden" x-show="maxSlide > 0" style="display: none;">
                            <button @click="currentSlide = Math.max(0, currentSlide - 1)" :disabled="currentSlide === 0" :class="{'opacity-30 cursor-not-allowed bg-slate-100 dark:bg-slate-800': currentSlide === 0, 'hover:bg-slate-100 text-primary-600': currentSlide > 0}" class="px-2 py-0.5 transition-colors text-slate-500">
                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                            </button>
                            <span class="px-2 py-0.5 text-[10px] font-bold text-slate-700 dark:text-slate-300 border-x border-slate-200 dark:border-slate-600 min-w-[3rem] text-center" x-text="(currentSlide + 1) + ' / ' + (maxSlide + 1)"></span>
                            <button @click="currentSlide = Math.min(maxSlide, currentSlide + 1)" :disabled="currentSlide === maxSlide" :class="{'opacity-30 cursor-not-allowed bg-slate-100 dark:bg-slate-800': currentSlide === maxSlide, 'hover:bg-slate-100 text-primary-600': currentSlide < maxSlide}" class="px-2 py-0.5 transition-colors text-slate-500">
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Canvas Wrapper -->
                <div class="flex-1 relative overflow-hidden flex flex-col min-h-0 bg-slate-50/50 dark:bg-slate-900/20">
                    @if(count($poChunks) > 0)
                        @foreach($poChunks as $index => $chunk)
                        <div :class="currentSlide === {{ $index }} ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'" class="absolute inset-0 flex flex-col min-h-0 w-full h-full p-4 transition-opacity duration-300">
                            <canvas id="progressChart-{{ $index }}"></canvas>
                        </div>
                        @endforeach
                    @else
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                            <i class="fa-solid fa-chart-column text-2xl mb-2 opacity-50"></i>
                            <p class="text-xs">No active POs</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
            
        <!-- Bottom Row: Lists & Tables -->
        <div class="h-[220px] flex-none flex gap-4 min-h-0">
            
            <!-- Nearest Events -->
            <div class="flex-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden flex flex-col min-h-0">
                <div class="py-1.5 px-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex items-center justify-between flex-none">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white"><i class="fa-regular fa-calendar-check text-slate-400 mr-1.5"></i> Upcoming Events</h3>
                    <a href="{{ route('events.index') }}" class="text-[9px] text-primary-600 font-medium">View All</a>
                </div>
                
                <div class="flex-1 overflow-x-auto overflow-y-auto custom-scrollbar relative">
                    @if($nearestEvents->count() > 0)
                        <table class="w-full table-fixed text-left border-collapse">
                            <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 z-10">
                                <tr>
                                    <th class="w-[24%] py-1 px-1 pl-2 text-[8px] font-semibold text-slate-500 uppercase tracking-tighter">Customer</th>
                                    <th class="w-[22%] py-1 px-1 text-[8px] font-semibold text-slate-500 uppercase tracking-tighter">Model</th>
                                    <th class="w-[15%] py-1 px-1 text-[8px] font-semibold text-slate-500 uppercase tracking-tighter">Event</th>
                                    <th class="w-[15%] py-1 px-1 text-[8px] font-semibold text-slate-500 uppercase tracking-tighter">Batch</th>
                                    <th class="w-[24%] py-1 px-1 pr-2 text-[8px] font-semibold text-slate-500 uppercase text-right tracking-tighter">Delv Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @foreach($nearestEvents as $evt)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 cursor-pointer transition-colors" onclick="window.location.href='{{ route('tracking.index', ['search' => $evt->event->po_no, 'open_event' => $evt->npc_event_id, 'from_dashboard' => 1]) }}'">
                                        <td class="py-1.5 px-1 pl-2 overflow-hidden">
                                            <p class="text-[9px] font-semibold text-slate-800 dark:text-white truncate" title="{{ $evt->product->customer->code ?? '-' }}">{{ $evt->product->customer->code ?? '-' }}</p>
                                        </td>
                                        <td class="py-1.5 px-1 overflow-hidden">
                                            <p class="text-[9px] text-slate-600 dark:text-slate-400 truncate" title="{{ $evt->product->vehicleModel->name ?? '-' }}">{{ $evt->product->vehicleModel->name ?? '-' }}</p>
                                        </td>
                                        <td class="py-1.5 px-1">
                                            <p class="text-[9px] text-slate-600 dark:text-slate-400" title="{{ $evt->event->customerCategory->name ?? '-' }}">{{ $evt->event->customerCategory->name ?? '-' }}</p>
                                        </td>
                                        <td class="py-1.5 px-1">
                                            <span class="text-[8px] font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-700 px-1 py-0.5 rounded whitespace-nowrap" title="{{ $evt->event->deliveryGroup->name ?? '-' }}">{{ $evt->event->deliveryGroup->name ?? '-' }}</span>
                                        </td>
                                        <td class="py-1.5 px-1 pr-2 text-right">
                                            <span class="text-[9px] font-bold whitespace-nowrap {{ \Carbon\Carbon::parse($evt->delivery_date)->isPast() ? 'text-rose-500' : 'text-slate-700 dark:text-slate-300' }}">
                                                {{ \Carbon\Carbon::parse($evt->delivery_date)->format('d M y') }}
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

            <!-- Achievment Delivery -->
            <div class="flex-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col min-h-0 overflow-hidden">
                <div class="py-1.5 px-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex-none flex justify-between items-center">
                    <h3 class="text-xs font-bold text-slate-800 dark:text-white flex items-center">
                        <i class="fa-solid fa-truck-ramp-box text-emerald-500 mr-1.5"></i> Achievment Delivery (Remain Delivery)
                    </h3>
                    <a href="{{ route('tracking.index') }}" class="text-[9px] text-primary-600 font-medium">View All</a>
                </div>
                
                <div class="flex-1 overflow-y-auto custom-scrollbar p-0">
                    @if($remainDeliveries->count() > 0)
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 z-10 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="py-1 px-3 text-[9px] font-semibold text-slate-500 uppercase">Model</th>
                                    <th class="py-1 px-3 text-[9px] font-semibold text-slate-500 uppercase text-right">Achievment</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @foreach($remainDeliveries as $deliv)
                                    @php
                                        $modelStr = $deliv->vehicleModel ? $deliv->vehicleModel->name : '-';
                                        $completed_items = $deliv->total_items - $deliv->remaining_items;
                                        $percentage = $deliv->total_items > 0 ? floor(($completed_items / $deliv->total_items) * 100) : 0;
                                        $percentage = min(99, $percentage); // Cap at 99% for non-closed deliveries
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
                            <p class="text-[10px]">No achievment deliveries.</p>
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
    const poChunks = @json($poChunks);
    
    if (poChunks && poChunks.length > 0) {
        poChunks.forEach((chunk, index) => {
            const labels = chunk.map(po => po.chartLabel);
            const ctx = document.getElementById('trendChart-' + index);
            if (ctx) {
                const dataPlan = chunk.map(po => po.totalItems);
                const dataActual = chunk.map(po => po.finishedItems);
                
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Plan',
                                data: dataPlan,
                                backgroundColor: '#3b82f6',
                                borderRadius: 4,
                                maxBarThickness: 35,
                                categoryPercentage: 0.8,
                                barPercentage: 0.8,
                                order: 2
                            },
                            {
                                label: 'Actual',
                                data: dataActual,
                                backgroundColor: '#10b981',
                                borderRadius: 4,
                                maxBarThickness: 35,
                                categoryPercentage: 0.8,
                                barPercentage: 0.8,
                                order: 3
                            }
                        ]
                    },
                    options: {
                        layout: { padding: { top: 25, right: 5 } },
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            datalabels: {
                                color: '#ffffff',
                                font: { weight: 'bold', size: 9 },
                                formatter: function(value) { return value > 0 ? value : ''; },
                                anchor: 'end',
                                align: 'bottom'
                            },
                            legend: {
                                position: 'top',
                                labels: { usePointStyle: true, boxWidth: 6, font: { size: 10 } }
                            },
                            tooltip: {
                                mode: 'index',
                                axis: 'x',
                                intersect: false,
                                backgroundColor: isDark ? '#1e293b' : '#ffffff',
                                titleColor: isDark ? '#f8fafc' : '#0f172a',
                                bodyColor: isDark ? '#cbd5e1' : '#475569',
                                borderColor: isDark ? '#334155' : '#e2e8f0',
                                borderWidth: 1,
                                callbacks: {
                                    title: function(tooltipItems) {
                                        const tIndex = tooltipItems[0].dataIndex;
                                        return chunk[tIndex].chartTooltip || tooltipItems[0].chart.data.labels[tIndex];
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { 
                                grid: { display: false }, 
                                ticks: { autoSkip: false, font: { size: 9 }, maxRotation: 0, minRotation: 0 }
                            },
                            y: { 
                                type: 'linear', display: true, position: 'left',
                                grid: { color: gridColor }, beginAtZero: true,
                                ticks: { stepSize: 1, font: { size: 9 } }
                            }
                        }
                    }
                });
            }

            // --- PO Progress (Stacked Vertical Bar Chart) ---
            const ctxProg = document.getElementById('progressChart-' + index);
            if (ctxProg) {
                const deptColors = {
                    'Finished': '#10b981',
                    'Draft': '#94a3b8',
                    'QC': '#fbbf24',
                    'MGM': '#a855f7',
                    'Produksi': '#60a5fa',
                    'ME': '#6366f1',
                    'CAM': '#06b6d4',
                    'CNC': '#f97316',
                    'Unknown': '#fb7185'
                };
                
                const departments = ['Finished', 'Draft', 'QC', 'MGM', 'Produksi', 'ME', 'CAM', 'CNC', 'Unknown'];
                
                const datasetsProg = departments.map(dept => {
                    const data = chunk.map(po => {
                        if (dept === 'Finished') return po.finishedItems || 0;
                        return po.deptCounts[dept] || 0;
                    });
                    
                    return {
                        label: dept,
                        data: data,
                        backgroundColor: deptColors[dept] || '#38bdf8',
                        borderRadius: 0,
                        maxBarThickness: 50,
                        categoryPercentage: 0.7,
                        barPercentage: 0.8,
                    };
                }).filter(ds => ds.data.some(val => val > 0)); // Only include departments with data

                new Chart(ctxProg, {
                    type: 'bar',
                    data: {
                        labels: labels, // same labels as plan vs actual
                        datasets: datasetsProg
                    },
                    options: {
                        layout: { padding: { top: 25, right: 5 } },
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            datalabels: {
                                color: '#ffffff',
                                font: { weight: 'bold', size: 9 },
                                formatter: function(value) { return value > 0 ? value : ''; },
                                anchor: 'center',
                                align: 'center'
                            },
                            legend: {
                                position: 'top',
                                labels: { usePointStyle: true, boxWidth: 6, font: { size: 9 } }
                            },
                            tooltip: {
                                mode: 'index',
                                axis: 'x',
                                intersect: false,
                                backgroundColor: isDark ? '#1e293b' : '#ffffff',
                                titleColor: isDark ? '#f8fafc' : '#0f172a',
                                bodyColor: isDark ? '#cbd5e1' : '#475569',
                                borderColor: isDark ? '#334155' : '#e2e8f0',
                                borderWidth: 1,
                                callbacks: {
                                    title: function(tooltipItems) {
                                        const tIndex = tooltipItems[0].dataIndex;
                                        return chunk[tIndex].chartTooltip || tooltipItems[0].chart.data.labels[tIndex];
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { 
                                stacked: true,
                                grid: { display: false }, 
                                ticks: { autoSkip: false, font: { size: 9 }, maxRotation: 0, minRotation: 0 }
                            },
                            y: { 
                                stacked: true,
                                type: 'linear', display: true, position: 'left',
                                grid: { color: gridColor }, beginAtZero: true,
                                ticks: { stepSize: 1, font: { size: 9 } }
                            }
                        }
                    }
                });
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