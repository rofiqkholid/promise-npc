@if(in_array($part->status, ['FINISHED', 'OUTSTANDING', 'CLOSED']))
    <div class="flex flex-col gap-1.5 mt-1">
        <span class="text-[11px] font-medium text-slate-600 dark:text-slate-400 flex items-center gap-1.5 line-through decoration-slate-300 opacity-60">
            <i class="fa-solid fa-check text-green-500"></i> Production Done
        </span>
        @if($part->qc_target_date)
        <span class="text-[11px] font-medium text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
            <i class="fa-solid fa-check-double text-emerald-500"></i> QC Passed: {{ \Carbon\Carbon::parse($part->qc_target_date)->format('d M y') }}
        </span>
        @endif
        @if($part->mgm_target_date)
        <span class="text-[11px] font-medium text-purple-700 dark:text-purple-400 flex items-center gap-1.5">
            <i class="fa-solid fa-check-double text-purple-500"></i> MGM Check: {{ \Carbon\Carbon::parse($part->mgm_target_date)->format('d M y') }}
        </span>
        @endif
    </div>
@else
    <div class="mt-2 text-slate-400 text-[10px] font-medium italic">
        Not yet finished ({{ str_replace('_', ' ', $part->status) }})
    </div>
@endif
