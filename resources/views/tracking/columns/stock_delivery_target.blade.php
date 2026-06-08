@php
    $now = \Carbon\Carbon::now()->startOfDay();
    $target = \Carbon\Carbon::parse($part->delivery_date)->startOfDay();
    $diffDays = $now->diffInDays($target, false);
    
    $isOverdue = $diffDays < 0;
    $isUrgent = $diffDays >= 0 && $diffDays <= 3;
    
    $timeStatusClass = $isOverdue ? 'bg-red-100 text-red-700 border-red-200' : ($isUrgent ? 'bg-orange-100 text-orange-700 border-orange-200' : 'bg-green-100 text-green-700 border-green-200');
    $timeStatusText = $isOverdue ? 'Overdue ' . abs($diffDays) . ' Days' : ($diffDays == 0 ? 'Deliver Today' : 'Remaining ' . $diffDays . ' Days');
    $timeStatusIcon = $isOverdue ? 'fa-triangle-exclamation' : 'fa-clock';
    
    $customerName = optional(optional(optional($part->product)->vehicleModel)->customer)->code ?? 'Unknown Customer';
    $modelName = optional(optional($part->product)->vehicleModel)->name ?? '-';
    
    $categoryName = optional(optional($part->event)->customerCategory)->name ?? '-';
    $grName = optional(optional($part->event)->deliveryGroup)->name ?? '-';
@endphp
<div class="font-bold text-gray-800 dark:text-gray-100 mb-1 flex items-center gap-1.5">
    <i class="fa-solid fa-building text-gray-400"></i> {{ $customerName }}
</div>
<div class="text-xs text-gray-500 font-medium mb-2 pl-4">
    <div class="mb-1">Model: <span class="text-blue-600 dark:text-blue-400">{{ $modelName }}</span></div>
    <div class="flex items-center gap-1.5">
        <span class="px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800 text-[9px] font-bold tracking-wider" title="Category Customer">{{ $categoryName }}</span>
        <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 text-[9px] font-bold tracking-wider" title="Delivery Group (GR)">{{ $grName }}</span>
    </div>
</div>

@if(in_array($part->status, ['CLOSED', 'OUTSTANDING']) && $part->actual_delivery)
    @php
        $actual = \Carbon\Carbon::parse($part->actual_delivery)->startOfDay();
        $diffDelivered = $actual->diffInDays($target, false);
        $delivClass = $diffDelivered < 0 ? 'bg-red-100 text-red-700 border-red-200' : 'bg-blue-100 text-blue-700 border-blue-200';
        $delivText = $diffDelivered < 0 ? 'Late ' . abs($diffDelivered) . ' Days' : ($diffDelivered == 0 ? 'On Time' : 'Early ' . $diffDelivered . ' Days');
        $delivIcon = $diffDelivered < 0 ? 'fa-circle-xmark' : 'fa-bolt';
    @endphp
    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold border {{ $delivClass }}">
        <i class="fa-solid {{ $delivIcon }}"></i> Delivered {{ $delivText }}
    </span>
@else
    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold border {{ $timeStatusClass }}">
        <i class="fa-solid {{ $timeStatusIcon }}"></i> {{ $timeStatusText }}
    </span>
@endif
