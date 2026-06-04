@if($part->status === 'PO_REGISTERED')
    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed">
        <i class="fa-solid fa-lock text-[8px]"></i> Not yet send to production
    </div>
@elseif($part->status === 'WAITING_DEPT_CONFIRM')
    @php
        $activeProcess = $part->processes->where('status', 'WAITING')->sortBy('sequence_order')->first();
    @endphp
    @if(isset($activeProcess))
        @php
            $isLast = $part->processes->where('status', 'WAITING')->count() === 1;
            $hasFinishedProcess = $part->processes->where('status', 'FINISHED')->count() > 0;
        @endphp
        <button type="button"
            onclick="openCompleteModal('{{ $part->hashed_id }}', '{{ $activeProcess->hashed_id }}', '{{ optional($activeProcess->process)->process_name }}', '{{ optional($activeProcess->department)->name }}', '{{ $activeProcess->target_completion_date ? \Carbon\Carbon::parse($activeProcess->target_completion_date)->format('d M Y') : '-' }}', '{{ route('tracking.process.complete', $part->hashed_id) }}', {{ $part->qty }})"
            class="inline-flex px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white shadow-sm font-bold transition items-center gap-2 text-[11px] mb-2 w-full justify-center" style="background-color: #f59e0b;">
            Complete {{ optional($activeProcess->process)->process_name }} <i class="fa-solid fa-forward-step"></i>
        </button>
        @if($hasFinishedProcess)
            <form action="{{ route('tracking.process.rollback', $part->hashed_id) }}" method="POST">
                @csrf
                <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 flex items-center justify-end w-full gap-1 font-semibold transition mb-2" onclick="confirmAction(event, 'Are you sure you want to rollback the previous process?')">
                    <i class="fa-solid fa-rotate-left"></i> Rollback Previous Process
                </button>
            </form>
        @endif
        <p class="text-[9px] text-gray-400 italic text-right max-w-[150px] mx-auto float-right text-balance mt-1">
            {{ $isLast ? 'Click if completed to submit to QC.' : 'Click to move to the next department.' }}
        </p>
    @endif
@elseif($part->status === 'WAITING_QE_CHECK')
    @php
        $checksheet = $part->checksheet;
        $canRollback = !$checksheet || !$checksheet->qe_checked_by;
    @endphp
    <div class="flex flex-col items-end gap-2">
        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed w-full">
            <i class="fa-solid fa-check-double text-[8px] text-green-500"></i> Submitted to QC
        </div>
        @if($canRollback)
        <form action="{{ route('tracking.process.rollback', $part->hashed_id) }}" method="POST">
            @csrf
            <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold transition mt-1" onclick="confirmAction(event, 'Are you sure you want to rollback this part from QC to Production stage?')">
                <i class="fa-solid fa-rotate-left"></i> Rollback Production
            </button>
        </form>
        @endif
    </div>
@else
    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed">
        <i class="fa-solid fa-check-double text-[8px] text-green-500"></i> Completed
    </div>
@endif
