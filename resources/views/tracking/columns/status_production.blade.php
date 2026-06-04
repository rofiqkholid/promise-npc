@if($part->status === 'PO_REGISTERED')
    <div class="inline-flex flex-col items-center gap-1.5 px-3 py-2 bg-slate-50 border border-slate-200 text-[10px] text-slate-500 italic">
        <i class="fa-solid fa-lock text-sm"></i> Planned
    </div>
@elseif($part->status === 'WAITING_DEPT_CONFIRM')
    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-yellow-100 border border-yellow-200 text-yellow-800 text-[10px] font-bold tracking-wide"><i class="fa-solid fa-gears fa-spin"></i> IN PROCESS</span>
@else
    <div class="text-[10px] text-gray-400 italic font-medium"><i class="fa-solid fa-check text-green-500"></i> Submitted to QC</div>
@endif
