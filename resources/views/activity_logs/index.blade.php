@extends('layouts.app')

@section('title', 'System Activity Logs')
@section('page_title', 'System / Activity Logs')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 max-w-7xl mx-auto">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                <i class="fa-solid fa-clock-rotate-left text-blue-500 mr-2"></i> System Activity Logs
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Audit trail of changes across Master Data and Transactions.
            </p>
        </div>
    </div>

    <!-- Search Form -->
    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
        <form action="{{ route('activity-logs.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by description, subject model, or user name..."
                       class="!pl-10 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2">
            </div>
            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none transition">
                Search
            </button>
            @if(request('search'))
            <a href="{{ route('activity-logs.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                Clear
            </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto" x-data="{ openModal: null }">
        <table class="min-w-full table-fixed divide-y divide-gray-200 dark:divide-gray-700 text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 uppercase text-xs">
                <tr>
                    <th scope="col" class="px-6 py-3 font-semibold w-1/4">Date / Time</th>
                    <th scope="col" class="px-6 py-3 font-semibold w-1/4">User (Causer)</th>
                    <th scope="col" class="px-6 py-3 font-semibold w-1/4 text-left">Action</th>
                    <th scope="col" class="px-6 py-3 font-semibold w-1/4">Subject</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">
                            <div class="font-bold">{{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $log->created_at->timezone('Asia/Jakarta')->format('H:i:s') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-800 dark:text-gray-300 font-medium">
                            @if($log->causer)
                                <i class="fa-solid fa-user-circle text-gray-400 mr-1"></i> {{ $log->causer->name ?? 'System' }}
                            @else
                                <span class="italic text-gray-500">System</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-left">
                            @php
                                $badgeClass = 'bg-gray-100 text-gray-800';
                                if($log->event === 'created') $badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400';
                                if($log->event === 'updated') $badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
                                if($log->event === 'deleted') $badgeClass = 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
                            @endphp
                            <span class="inline-flex items-center justify-center min-w-[90px] px-3 py-1 rounded-full text-xs font-bold {{ $badgeClass }} uppercase tracking-wider">
                                {{ $log->event ?? $log->description }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-xs">
                            @php
                                $modelBasename = class_basename($log->subject_type);
                                
                                // Map Model to Menu Name
                                $menuNameMap = [
                                    'Customer' => 'Customer Mapping',
                                    'NpcProcess' => 'Process Master',
                                    'NpcMasterRouting' => 'Routing per Part ID',
                                    'NpcMasterCheckpoint' => 'QE Point Master',
                                    'ProductCheckpoint' => 'Part Checksheet Master',
                                    'NpcEvent' => 'Event Data (PO)',
                                    'NpcRole' => 'NPC Role',
                                    'User' => 'NPC User Access',
                                    'NpcPart' => 'Part Transaction',
                                    'NpcChecksheet' => 'Checksheet Transaction',
                                    'NpcChecksheetDetail' => 'Checksheet Details',
                                    'NpcPartProcess' => 'Part Process Progress',
                                ];
                                
                                $displayModelName = $menuNameMap[$modelBasename] ?? $modelBasename;
                                // Override with custom description if it's a manual log
                                if (!in_array($log->description, ['created', 'updated', 'deleted', 'imported']) && !empty($log->description)) {
                                    $displayModelName = $log->description;
                                }
                                
                                // Find a readable identifier from attributes or old values
                                $attrs = $log->attribute_changes['attributes'] ?? [];
                                $oldAttrs = $log->attribute_changes['old'] ?? [];
                                $combinedAttrs = array_merge($oldAttrs, $attrs);
                                
                                $identifier = null;
                                // Try to resolve identifier by checking common name fields or resolving foreign keys
                                foreach(['part_no', 'customer_name', 'process_name', 'name', 'title', 'role_name', 'po_number', 'part_id'] as $k) {
                                    if (isset($combinedAttrs[$k])) {
                                        // Use the resolveValue closure defined below in the Changes column if possible,
                                        // but since it's defined lower down, we'll implement a quick resolver here
                                        $val = $combinedAttrs[$k];
                                        if (str_ends_with($k, '_id') && $val) {
                                            $relation = str_replace('_id', '', $k);
                                            $models = ['App\\Models\\Npc' . Str::studly($relation), 'App\\Models\\' . Str::studly($relation), 'App\\Models\\Product'];
                                            foreach ($models as $m) {
                                                if (class_exists($m)) {
                                                    try {
                                                        $rel = $m::find($val);
                                                        if ($rel) {
                                                            if (isset($rel->part_no)) {
                                                                $modelName = optional($rel->vehicleModel)->name ?? '';
                                                                $identifier = $modelName ? $modelName . ' - ' . $rel->part_no : $rel->part_no;
                                                            } else {
                                                                $nameAttr = $relation . '_name';
                                                                if (isset($rel->$nameAttr)) {
                                                                    $identifier = $rel->$nameAttr;
                                                                } elseif (isset($rel->name)) {
                                                                    $identifier = $rel->name;
                                                                }
                                                            }
                                                            break;
                                                        }
                                                    } catch(\Exception $e) {}
                                                }
                                            }
                                        } else {
                                            $identifier = $val;
                                        }
                                        
                                        if ($identifier) break;
                                    }
                                }
                                
                                // Fallback for manual logs where attribute_changes is empty but subject exists
                                if (!$identifier && $log->subject) {
                                    if (isset($log->subject->part_no)) {
                                        $modelName = optional($log->subject->vehicleModel)->name ?? '';
                                        $identifier = $modelName ? $modelName . ' - ' . $log->subject->part_no : $log->subject->part_no;
                                    } elseif (isset($log->subject->name)) {
                                        $identifier = $log->subject->name;
                                    }
                                }
                                
                                if (!$identifier) $identifier = "ID: " . $log->subject_id;
                            @endphp
                            <div class="font-bold text-gray-800 dark:text-gray-200">{{ $displayModelName }}</div>
                            <div class="font-mono text-xs mt-0.5 text-blue-600 dark:text-blue-400 font-semibold">{{ $identifier }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-ghost text-4xl text-gray-300 dark:text-gray-600 mb-3 block"></i>
                            <p class="font-medium text-lg">No activity logs found</p>
                            <p class="text-sm mt-1">Changes made to tracked models will appear here.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
