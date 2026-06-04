<div class="flex flex-col items-end gap-2 text-sm">
@if($part->status === 'CLOSED')
    <div class="px-3 py-2 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-[10px] text-blue-600 dark:text-blue-400 italic flex items-center gap-1.5 cursor-not-allowed font-bold">
        <i class="fa-solid fa-check-double text-[10px]"></i> Already Delivered (Closed)
    </div>
@elseif(!in_array($part->status, ['FINISHED', 'OUTSTANDING']))
    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center gap-1.5 cursor-not-allowed">
        <i class="fa-solid fa-lock text-[8px]"></i> Waiting for Process to Complete
    </div>
@else
    <button type="button" onclick="openDeliverModal('{{ $part->hashed_id }}', '{{ $part->qty - $part->delivered_qty }}', '{{ route('tracking.deliver', $part->hashed_id) }}', '{{ optional($part->product)->part_no }}')" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white shadow-sm font-medium transition text-xs flex items-center justify-center gap-2 w-full">
        <i class="fa-solid fa-truck-fast"></i> Deliver Parts
    </button>
    @if($part->checksheet)
    <a href="{{ route('checksheets.print-label', $part->hashed_id) }}" target="_blank" class="px-4 py-2 bg-white text-blue-600 border border-blue-200 hover:bg-blue-50 shadow-sm font-medium transition text-xs flex items-center justify-center gap-2 w-full">
        <i class="fa-solid fa-print"></i> Print QC Label
    </a>
    @endif
    <p class="text-[9px] text-gray-400 italic text-right w-full">Remaining: {{ number_format($part->qty - $part->delivered_qty) }} PCS</p>
@endif
</div>
