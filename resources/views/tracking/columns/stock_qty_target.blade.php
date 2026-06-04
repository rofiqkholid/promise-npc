<div class="text-gray-800 dark:text-gray-300 font-black text-lg mb-0.5">{{ number_format($part->qty) }} <span class="text-xs font-semibold text-gray-500">PCS</span></div>
@if($part->delivered_qty > 0)
<div class="text-[11px] font-bold text-blue-600 dark:text-blue-400 mb-1">
    <i class="fa-solid fa-truck-ramp-box"></i> Delivered: {{ number_format($part->delivered_qty) }} / {{ number_format($part->qty) }}
</div>
@endif
<div class="text-[11px] font-medium text-gray-500">
    Target: {{ \Carbon\Carbon::parse($part->delivery_date)->format('d M Y') }}
</div>
