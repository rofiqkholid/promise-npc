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

    <!-- Filter Form -->
    <div class="p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/30">
        <form action="{{ route('activity-logs.index') }}" method="GET" class="space-y-4">
            <!-- Text Search -->
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Part Number, description, or user..."
                       class="!pl-10 block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2.5">
            </div>

            <!-- Filters Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 pt-2 border-t border-gray-200 dark:border-gray-700">
                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" onchange="this.form.submit()" class="block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="this.form.submit()" class="block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3">
                </div>

                <!-- Event Filter -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Action</label>
                    <select name="event" onchange="this.form.submit()" class="block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3">
                        <option value="">All Actions</option>
                        <option value="created" {{ request('event') == 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('event') == 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('event') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                        <option value="imported" {{ request('event') == 'imported' ? 'selected' : '' }}>Imported</option>
                        <option value="rollbacked" {{ request('event') == 'rollbacked' ? 'selected' : '' }}>Rollbacked</option>
                    </select>
                </div>

                <!-- User Filter -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">User (Causer)</label>
                    <select name="causer_id" onchange="this.form.submit()" class="block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->nik }}" {{ request('causer_id') == $user->nik ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Menu Filter -->
                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Subject (Menu)</label>
                    <select name="menu" onchange="this.form.submit()" class="block w-full shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-white py-2 px-3">
                        <option value="">All Menus</option>
                        @foreach($menus as $modelClass => $menuName)
                            <option value="{{ $modelClass }}" {{ request('menu') === $modelClass ? 'selected' : '' }}>{{ $menuName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-4">
                @if(request()->anyFilled(['search', 'start_date', 'end_date', 'event', 'causer_id', 'menu']))
                <a href="{{ route('activity-logs.index') }}" class="inline-flex justify-center items-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                    Reset Filters
                </a>
                @endif
                <!-- Hidden submit button so Enter key works on text inputs -->
                <button type="submit" class="hidden"></button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto" x-data="{ openModal: null }">
        <table id="activityTable" class="min-w-full table-fixed text-sm text-left">
            <thead class="bg-gray-50/80 text-gray-900 font-bold uppercase text-xs">
                <tr>
                    <th scope="col" class="px-6 py-3 font-semibold w-[5%] text-center">No.</th>
                    <th scope="col" class="px-6 py-3 font-semibold w-[20%]">Date / Time</th>
                    <th scope="col" class="px-6 py-3 font-semibold w-[25%]">User (Causer)</th>
                    <th scope="col" class="px-6 py-3 font-semibold w-[20%] text-left">Action</th>
                    <th scope="col" class="px-6 py-3 font-semibold w-[30%]">Subject</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($logs as $log)
                    <tr class="hover:bg-gray-100 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400 font-medium">
                            {{ $loop->iteration }}
                        </td>
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
                                if($log->event === 'rollbacked') $badgeClass = 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400';
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
                                    'Customer' => 'Customer Mapping Master',
                                    'NpcProcess' => 'Process Master',
                                    'NpcMasterRouting' => 'Routing per Part ID',
                                    'NpcMasterCheckpoint' => 'QE Point Master',
                                    'ProductCheckpoint' => 'Part Checksheet Master',
                                    'NpcEvent' => 'Event Data (PO)',
                                    'NpcRole' => 'NPC Roles',
                                    'User' => 'NPC User Access',
                                    'NpcPart' => 'Transaction',
                                    'NpcChecksheet' => 'Quality Check (QC)',
                                    'NpcChecksheetDetail' => 'Checksheet Details',
                                    'NpcPartProcess' => 'Production Process',
                                ];
                                
                                $baseMenuName = $menuNameMap[$modelBasename] ?? $modelBasename;
                                
                                $props = is_iterable($log->properties) ? $log->properties : collect(json_decode($log->properties ?? '[]', true));
                                $attrs = $props['attributes'] ?? [];
                                $oldAttrs = $props['old'] ?? [];
                                $combinedAttrs = array_merge($oldAttrs, $attrs);
                                
                                // Make NpcPart menu specific based on its status
                                if ($modelBasename === 'NpcPart' && isset($combinedAttrs['status'])) {
                                    $status = $combinedAttrs['status'];
                                    if ($status === 'PO_REGISTERED') {
                                        $baseMenuName = 'Production Routing Setup';
                                    } elseif (in_array($status, ['WAITING_DEPT_CONFIRM', 'IN_PRODUCTION'])) {
                                        $baseMenuName = 'Production Process';
                                    } elseif ($status === 'WAITING_QE_CHECK') {
                                        $baseMenuName = 'Quality Check (QC)';
                                    } elseif (in_array($status, ['WAITING_MGM_CHECK', 'WAITING_APPROVAL'])) {
                                        $baseMenuName = 'Management Check';
                                    } elseif ($status === 'FINISHED') {
                                        $baseMenuName = 'Finished Goods Stock';
                                    } elseif (in_array($status, ['OUTSTANDING', 'CLOSED'])) {
                                        $baseMenuName = 'Delivery History';
                                    }
                                }
                                
                                $displayModelName = $baseMenuName;
                                $isRollback = false;
                                
                                // Override with custom description if it's a manual log
                                if (!in_array($log->description, ['created', 'updated', 'deleted', 'imported']) && !empty($log->description)) {
                                    if ($log->event === 'rollbacked') {
                                        $displayModelName = $log->description;
                                        $isRollback = true;
                                    } else {
                                        $displayModelName = $log->description;
                                    }
                                }

                                $iconClass = 'fa-file-lines text-gray-500';
                                if ($log->event === 'created') $iconClass = 'fa-plus-circle text-emerald-500';
                                if ($log->event === 'updated') $iconClass = 'fa-pen-to-square text-blue-500';
                                if ($log->event === 'deleted') $iconClass = 'fa-trash-can text-red-500';
                                if ($log->event === 'rollbacked') $iconClass = 'fa-arrow-rotate-left text-orange-500';
                                
                                $changedFieldsStr = '';
                                if ($log->event === 'updated' && !empty($attrs) && is_array($attrs)) {
                                    $keys = array_keys($attrs);
                                    $keys = array_filter($keys, fn($k) => !in_array($k, ['updated_at', 'created_at']));
                                    if (!empty($keys)) {
                                        $changedFieldsStr = 'Updated: ' . implode(', ', $keys);
                                    }
                                }
                                
                                $identifier = null;
                                $subject = $log->subject;

                                // Prioritaskan mengambil nama/identitas dari Model asli (jika belum dihapus)
                                if ($subject) {
                                    if ($modelBasename === 'NpcChecksheet') {
                                        if ($subject->npcPart && $subject->npcPart->product) {
                                            $identifier = "Part: " . $subject->npcPart->product->part_no;
                                        }
                                    } elseif ($modelBasename === 'NpcPart') {
                                        if ($subject->product) {
                                            $identifier = "Part: " . $subject->product->part_no;
                                        }
                                    } elseif ($modelBasename === 'NpcEvent') {
                                        $eventName = optional($subject->customerCategory)->category_name ?? optional($subject->customerCategory)->name ?? '';
                                        $identifier = "PO: " . $subject->po_no . ($eventName ? " - Event: " . $eventName : "");
                                    } elseif ($modelBasename === 'NpcPartProcess') {
                                        if ($subject->npcPart && $subject->npcPart->product && $subject->npcProcess) {
                                            $identifier = "Part: " . $subject->npcPart->product->part_no . " - Process: " . $subject->npcProcess->process_name;
                                        }
                                    } elseif ($modelBasename === 'ProductCheckpoint') {
                                        if ($subject->product) {
                                            $identifier = "Part: " . $subject->product->part_no;
                                        }
                                    } elseif (isset($subject->part_no)) {
                                        $modelName = optional($subject->vehicleModel)->name ?? '';
                                        $identifier = $modelName ? $modelName . ' - ' . $subject->part_no : $subject->part_no;
                                    } elseif (isset($subject->name)) {
                                        $identifier = $subject->name;
                                    } elseif (isset($subject->po_no)) {
                                        $identifier = "PO: " . $subject->po_no;
                                    } elseif (isset($subject->point_check)) {
                                        $identifier = "Point: " . $subject->point_check;
                                    }
                                }

                                // Fallback jika Model sudah dihapus (DELETED) atau belum ketemu identitasnya
                                // Kita cari dari data JSON properties yang tersimpan saat aksi terjadi
                                if (!$identifier) {
                                    if ($modelBasename === 'NpcEvent' && isset($combinedAttrs['po_no'])) {
                                        $identifier = "PO: " . $combinedAttrs['po_no'];
                                    } elseif ($modelBasename === 'NpcChecksheet' && isset($combinedAttrs['npc_part_id'])) {
                                        $part = \App\Models\NpcPart::find($combinedAttrs['npc_part_id']);
                                        if ($part && $part->product) {
                                            $identifier = "Part: " . $part->product->part_no;
                                        } else {
                                            $identifier = "Part ID: " . $combinedAttrs['npc_part_id'];
                                        }
                                    } elseif ($modelBasename === 'NpcPart' && isset($combinedAttrs['product_id'])) {
                                        $prod = \App\Models\Product::find($combinedAttrs['product_id']);
                                        if ($prod) {
                                            $identifier = "Part: " . $prod->part_no;
                                        }
                                    } elseif (isset($combinedAttrs['product_id'])) {
                                        $prod = \App\Models\Product::find($combinedAttrs['product_id']);
                                        if ($prod) {
                                            $identifier = "Part: " . $prod->part_no;
                                        }
                                    } else {
                                        foreach(['part_no', 'customer_name', 'process_name', 'name', 'title', 'role_name', 'po_number', 'po_no', 'point_check'] as $k) {
                                            if (isset($combinedAttrs[$k])) {
                                                $identifier = $combinedAttrs[$k];
                                                break;
                                            }
                                        }
                                    }
                                }
                                
                                if (!$identifier) $identifier = "";
                            @endphp
                            <div class="flex items-start gap-2.5">
                                <div class="mt-0.5">
                                    <i class="fa-solid {{ $iconClass }} text-sm"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-gray-800 dark:text-gray-200 text-[13px]">{{ $displayModelName }}</div>
                                    @if($isRollback)
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 font-medium">Menu: {{ $baseMenuName }}</div>
                                    @endif
                                    @if($identifier)
                                    <div class="font-mono text-xs mt-1 text-gray-600 dark:text-gray-400 font-medium">{{ $identifier }}</div>
                                    @endif
                                    @if($changedFieldsStr)
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5 max-w-xs truncate" title="{{ $changedFieldsStr }}">
                                        {{ $changedFieldsStr }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#activityTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthChange: false, // Hide "Show 10 entries"
            info: false,         // Hide "Showing 1 to 10..."
            searching: false,    // Hide search box (we use custom filters)
            ordering: false,     // Disable column sorting for now
            stripeClasses: ['bg-white dark:bg-gray-800', 'bg-gray-50 dark:bg-gray-750'], // Native zebra striping
            dom: 'rt<"flex justify-center mt-6 mb-2"p>', // Render Table -> Pagination
            language: {
                paginate: {
                    previous: "Previous",
                    next: "Next"
                },
                emptyTable: '<div class="py-8 text-center"><i class="fa-solid fa-ghost text-4xl text-gray-300 dark:text-gray-600 mb-3 block"></i><p class="font-medium text-lg">No activity logs found</p><p class="text-sm mt-1">Changes made to tracked models will appear here.</p></div>'
            },
            drawCallback: function() {
                // Style the pagination container
                $('.dataTables_paginate').addClass('inline-flex -space-x-px rounded-md shadow-sm');
                
                // Reset native datatables styles and apply Tailwind classes to all buttons
                $('.dataTables_paginate .paginate_button')
                    .removeClass('paginate_button current disabled') // Clean up existing
                    .addClass('relative inline-flex items-center px-4 py-2 text-sm font-medium border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:z-20 cursor-pointer first:rounded-l-md last:rounded-r-md');
                
                // Style Active button (Page 1, 2, etc)
                $('.dataTables_paginate .active')
                    .removeClass('bg-white text-gray-700 hover:bg-gray-50')
                    .addClass('z-10 bg-gray-100 border-gray-300 text-gray-900 font-bold');
                
                // Style Disabled buttons (Prev/Next when at end)
                $('.dataTables_paginate .disabled')
                    .removeClass('hover:bg-gray-50 cursor-pointer text-gray-700')
                    .addClass('opacity-50 cursor-not-allowed text-gray-400');
                    
                // Fix classes applied by DataTables dynamically
                $('#activityTable_paginate a').each(function() {
                    $(this).removeClass('paginate_button');
                });
            }
        });
    });
</script>
@endpush
