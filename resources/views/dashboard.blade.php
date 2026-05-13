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
<!-- Dashboard Wrapper to force single-screen height minus header/padding approx -->
<div class="h-[calc(100vh-10rem)] flex flex-col gap-4 overflow-hidden">

    <!-- KPI Cards (Row 1) -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 flex-none">
        <!-- Active Events -->
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex justify-between items-center">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Total Active Projects</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white leading-none">{{ $metrics['active_events'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 text-lg">
                <i class="fa-solid fa-folder-open"></i>
            </div>
        </div>

        <!-- In Production -->
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex justify-between items-center">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Parts in Production</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white leading-none">{{ $metrics['in_production'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400 text-lg">
                <i class="fa-solid fa-industry"></i>
            </div>
        </div>

        <!-- Pending QC -->
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex justify-between items-center">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Pending QC Checks</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white leading-none">{{ $metrics['pending_qc'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-purple-50 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400 text-lg">
                <i class="fa-solid fa-microscope"></i>
            </div>
        </div>

        <!-- Ready to Deliver -->
        <div class="bg-white dark:bg-slate-800 rounded-sm border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex justify-between items-center">
            <div>
                <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Stock (Ready to Deliver)</p>
                <h3 class="text-2xl font-bold text-slate-800 dark:text-white leading-none">{{ $metrics['ready_deliver'] }}</h3>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 text-lg">
                <i class="fa-solid fa-boxes-stacked"></i>
            </div>
        </div>
    </div>

    <!-- Main Content Area (Row 2, flex-1 to fill space) -->
    <div class="flex-1 flex gap-4 min-h-0">
        
        <!-- Left Column: Pipeline & Charts -->
        <div class="w-2/3 flex flex-col gap-4 min-h-0">
            
            <!-- Nearest Events -->
            <div class="bg-white dark:bg-slate-800 rounded-sm border border-slate-200 dark:border-slate-700 p-0 shadow-sm flex-none overflow-hidden">
                <div class="p-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white"><i class="fa-regular fa-calendar-check text-slate-400 mr-2"></i> Upcoming Events</h3>
                    <a href="{{ route('events.index') }}" class="text-[10px] text-primary-600 font-medium">View All</a>
                </div>
                
                <div class="overflow-x-auto overflow-y-auto max-h-[220px]">
                    @if($nearestEvents->count() > 0)
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 border-b border-slate-100 dark:border-slate-700 z-10">
                                <tr>
                                    <th class="p-2 pl-3 text-[10px] font-semibold text-slate-500 uppercase">Customer</th>
                                    <th class="p-2 text-[10px] font-semibold text-slate-500 uppercase">Model</th>
                                    <th class="p-2 text-[10px] font-semibold text-slate-500 uppercase">Event</th>
                                    <th class="p-2 pr-3 text-[10px] font-semibold text-slate-500 uppercase text-right">Delv Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @foreach($nearestEvents as $evt)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 cursor-pointer transition-colors" onclick="window.location.href='{{ route('tracking.index', ['search' => $evt->event->po_no, 'open_event' => $evt->npc_event_id, 'from_dashboard' => 1]) }}'">
                                        <td class="p-2 pl-3">
                                            <p class="text-[10px] font-semibold text-slate-800 dark:text-white">{{ $evt->product->customer->code ?? '-' }}</p>
                                        </td>
                                        <td class="p-2">
                                            <p class="text-[10px] text-slate-600 dark:text-slate-400">{{ $evt->product->vehicleModel->name ?? '-' }}</p>
                                        </td>
                                        <td class="p-2">
                                            <p class="text-[10px] text-slate-600 dark:text-slate-400">{{ $evt->event->customerCategory->name ?? '-' }}</p>
                                        </td>
                                        <td class="p-2 pr-3 text-right">
                                            <span class="text-[10px] font-bold {{ \Carbon\Carbon::parse($evt->delivery_date)->isPast() ? 'text-rose-500' : 'text-slate-700 dark:text-slate-300' }}">
                                                {{ \Carbon\Carbon::parse($evt->delivery_date)->format('d M Y') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-6 text-center text-slate-400">
                            <i class="fa-regular fa-calendar-xmark text-2xl mb-2"></i>
                            <p class="text-xs">No upcoming events found.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Charts Row -->
            <div class="flex-1 grid grid-cols-2 gap-4 min-h-0">
                <!-- Trend Chart -->
                <div class="bg-white dark:bg-slate-800 rounded-sm border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex flex-col relative min-h-0">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex-none mb-2">Event Progress (Items)</h3>
                    <div class="flex-1 w-full relative">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Dept / Customer Charts (Tabs/Stacked or Side by Side internally) -->
                <!-- Let's put Dept Workload here -->
                <div class="bg-white dark:bg-slate-800 rounded-sm border border-slate-200 dark:border-slate-700 p-4 shadow-sm flex flex-col relative min-h-0">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex-none mb-2">Department Bottleneck</h3>
                    <div class="flex-1 w-full relative">
                        <canvas id="deptChart"></canvas>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Lists & Tables -->
        <div class="w-1/3 flex flex-col gap-4 min-h-0">
            
            <!-- Action Required: ECN & Stagnant (Scrollable Body) -->
            <div class="flex-1 bg-white dark:bg-slate-800 rounded-sm border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col min-h-0 overflow-hidden">
                <div class="p-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex-none">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center">
                        <span class="w-2 h-2 rounded-full bg-rose-500 mr-2 animate-pulse"></span>
                        Action Required
                    </h3>
                </div>
                
                <div class="flex-1 overflow-y-auto p-0">
                    @if($ecnUpdates->count() > 0 || $stagnantParts->count() > 0)
                        <div class="divide-y divide-slate-100 dark:divide-slate-700/50">
                            <!-- ECN Items -->
                            @foreach($ecnUpdates as $part)
                                <div class="p-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="text-[9px] font-bold text-rose-600 bg-rose-100 px-1 rounded-sm uppercase mb-1 inline-block">ECN Update</span>
                                            <p class="text-xs font-semibold text-slate-800 dark:text-white leading-tight">{{ $part->product->part_no }}</p>
                                        </div>
                                        <a href="{{ route('events.parts.edit', ['event' => $part->event->npc_event_id ?? 0, 'part' => $part->hashed_id]) }}" class="text-[10px] bg-primary-50 text-primary-600 hover:bg-primary-100 px-2 py-1 rounded-sm font-medium">Review</a>
                                    </div>
                                </div>
                            @endforeach
                            <!-- Stagnant Items -->
                            @foreach($stagnantParts as $part)
                                <div class="p-3 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="text-[9px] font-bold text-amber-600 bg-amber-100 px-1 rounded-sm uppercase mb-1 inline-block">Stagnant > 7d</span>
                                            <p class="text-xs font-semibold text-slate-800 dark:text-white leading-tight">{{ $part->product->part_no }}</p>
                                        </div>
                                        <span class="text-[9px] text-slate-500 border border-slate-200 dark:border-slate-600 px-1 rounded-sm bg-slate-50 dark:bg-slate-800">{{ str_replace('_', ' ', $part->status) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="h-full flex flex-col items-center justify-center p-4 text-center text-slate-400">
                            <i class="fa-regular fa-circle-check text-2xl mb-2 text-emerald-400"></i>
                            <p class="text-xs">No pending actions required.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Deliveries (Scrollable Body) -->
            <div class="flex-1 bg-white dark:bg-slate-800 rounded-sm border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col min-h-0 overflow-hidden">
                <div class="p-3 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 flex-none flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center">
                        <i class="fa-solid fa-truck-ramp-box text-emerald-500 mr-2"></i>
                        Recent Deliveries
                    </h3>
                    <a href="{{ route('tracking.history') }}" class="text-[10px] text-primary-600 font-medium">View All</a>
                </div>
                
                <div class="flex-1 overflow-y-auto p-0">
                    @if($recentDeliveries->count() > 0)
                        <table class="w-full text-left border-collapse">
                            <thead class="sticky top-0 bg-slate-50 dark:bg-slate-800 z-10 border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="p-2 text-[10px] font-semibold text-slate-500 uppercase">Part</th>
                                    <th class="p-2 text-[10px] font-semibold text-slate-500 uppercase text-right">Qty</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @foreach($recentDeliveries as $deliv)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                                        <td class="p-2">
                                            <p class="text-xs font-semibold text-slate-800 dark:text-white">{{ $deliv->product->part_no }}</p>
                                            <p class="text-[10px] text-slate-500">{{ \Carbon\Carbon::parse($deliv->actual_delivery)->format('d M') }}</p>
                                        </td>
                                        <td class="p-2 text-right">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $deliv->delivered_qty }}</span>
                                            <span class="text-[9px] text-slate-400">/{{ $deliv->qty }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="h-full flex flex-col items-center justify-center p-4 text-center text-slate-400">
                            <i class="fa-solid fa-box-open text-2xl mb-2"></i>
                            <p class="text-xs">No recent deliveries.</p>
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

    // 1. Trend Chart (Line/Bar)
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: @json($trendChart['labels']),
                datasets: [
                    {
                        label: 'Total Items (Parts)',
                        data: @json($trendChart['new']),
                        backgroundColor: isDark ? 'rgba(59, 130, 246, 0.2)' : 'rgba(59, 130, 246, 0.1)',
                        borderColor: '#3b82f6',
                        borderWidth: 2,
                        borderRadius: 4,
                        order: 2
                    },
                    {
                        label: 'Finished Items',
                        data: @json($trendChart['finished']),
                        type: 'line',
                        backgroundColor: '#10b981',
                        borderColor: '#10b981',
                        borderWidth: 3,
                        tension: 0.3,
                        pointBackgroundColor: '#10b981',
                        order: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, boxWidth: 8 }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: isDark ? '#1e293b' : '#ffffff',
                        titleColor: isDark ? '#f8fafc' : '#0f172a',
                        bodyColor: isDark ? '#cbd5e1' : '#475569',
                        borderColor: isDark ? '#334155' : '#e2e8f0',
                        borderWidth: 1,
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        grid: { color: gridColor }, 
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
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