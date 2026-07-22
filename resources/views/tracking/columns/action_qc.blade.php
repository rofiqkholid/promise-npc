@if(in_array($part->status, ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM']))
    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed">
        <i class="fa-solid fa-lock text-[8px]"></i> Not Yet Registered in QC
    </div>
@elseif($part->status === 'WAITING_QE_CHECK')
    @php
        $masterStatus = optional($part->product->productDetail)->master_checksheet_status ?? 'DRAFT';
    @endphp
    @if($masterStatus !== 'APPROVED')
        <div class="px-3 py-2 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800/50 text-[10px] text-yellow-700 dark:text-yellow-400 font-bold flex flex-col items-center justify-center gap-1.5 w-full max-w-[150px] text-center float-right cursor-not-allowed mb-2">
            <div><i class="fa-solid fa-clock"></i> Master Not Approved</div>
            <div class="font-normal text-[8px] leading-tight">Please wait for QC to approve the Master Checksheet.</div>
        </div>
        <div class="clear-both"></div>
    @endif
    <a href="{{ $masterStatus === 'APPROVED' ? route('checksheets.create', $part->hashed_id) : '#' }}" class="inline-flex px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white shadow-sm font-bold transition items-center gap-2 text-[11px] {{ $masterStatus !== 'APPROVED' ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}" style="background-color: #f97316;">
        <i class="fa-regular fa-clipboard"></i> Input Quality (QC)
    </a>
    <p class="text-[9px] text-gray-400 mt-2 italic text-right max-w-[150px] mx-auto float-right text-balance">Fill quality parameter form & pass to MGM</p>
@else
    <div class="flex flex-col items-end gap-2">
        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed w-full max-w-[150px]">
            <i class="fa-solid fa-lock text-[8px]"></i> Already Inspected
        </div>
        @if($part->status === 'WAITING_MGM_CHECK')
        @php
            $checksheet = $part->checksheet;
            $canRollback = !$checksheet || !$checksheet->mgm_checked_by;
        @endphp
        @if($canRollback)
        <form action="{{ route('tracking.qc.rollback', $part->hashed_id) }}" method="POST">
            @csrf
            <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold transition mt-1" onclick="confirmAction(event, 'Are you sure you want to rollback this part from MGM to QC Check stage?')">
                <i class="fa-solid fa-rotate-left"></i> Rollback QC
            </button>
        </form>
        @endif
        @endif
        @if($part->checksheet)
        <a href="{{ route('checksheets.print-label', $part->hashed_id) }}" target="_blank" class="inline-flex px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white shadow-sm font-bold transition items-center justify-center gap-2 text-[11px] w-full max-w-[150px]">
            <i class="fa-solid fa-print"></i> Print QC Label
        </a>
        @endif
    </div>
@endif
