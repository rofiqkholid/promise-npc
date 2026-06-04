@php
    $poParts = $po->parts;
    $totalParts = $poParts->count();
@endphp
<div class="text-[11px] font-medium text-gray-500 text-right w-full flex flex-col items-end gap-1">
    <span class="bg-gray-100 dark:bg-gray-700 px-2 py-1 border border-gray-200 dark:border-gray-600">IN: {{ $po->created_at->format('d M y') }}</span>
    @if($poParts->where('status', 'CLOSED')->count() === $totalParts && $totalParts > 0)
        <span class="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 px-2 py-1 border border-emerald-200 shadow-sm mt-1 font-bold"><i class="fa-solid fa-check-double"></i> COMPLETE</span>
    @else
        <span class="text-amber-600 font-bold mt-1 tracking-wide">ACTIVE</span>
    @endif
</div>
