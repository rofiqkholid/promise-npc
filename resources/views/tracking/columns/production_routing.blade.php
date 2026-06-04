@if($part->processes->count() > 0)
    @php
        $activeProcess = $part->processes->where('status', 'WAITING')->sortBy('sequence_order')->first();
        $isAllFinished = $part->processes->where('status', 'WAITING')->isEmpty();
    @endphp
    <div class="flex flex-col gap-2 relative before:absolute before:inset-0 before:ml-[9px] before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">
        @foreach($part->processes->sortBy('sequence_order') as $process)
            @php
                $isFinished = $process->status === 'FINISHED';
                $isActive = $activeProcess && $activeProcess->id === $process->id;
                
                if ($isFinished) {
                    $circleColor = 'bg-green-500 text-white ring-4 ring-white dark:ring-gray-800';
                    $icon = '<i class="fa-solid fa-check text-[8px]"></i>';
                    $textColor = 'text-gray-400 line-through';
                } elseif ($isActive) {
                    $circleColor = 'bg-amber-500 text-white ring-4 ring-amber-100 dark:ring-amber-900 shadow-lg';
                    $icon = '<i class="fa-solid fa-gear fa-spin text-[8px]"></i>';
                    $textColor = 'text-gray-900 dark:text-white font-black';
                } else {
                    $circleColor = 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400';
                    $icon = $process->sequence_order;
                    $textColor = 'text-gray-400';
                }
            @endphp
            <div class="relative flex items-center gap-3">
                <div class="relative z-10 w-5 h-5 flex items-center justify-center font-bold text-[9px] {{ $circleColor }} transition-colors">
                    {!! $icon !!}
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] font-bold {{ $textColor }} transition-colors">{{ optional($process->process)->process_name ?? 'Unknown Process' }}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-[9px] text-gray-500 {{ $isFinished ? 'opacity-50' : '' }}"><i class="fa-solid fa-building-user text-[8px] mr-0.5"></i> {{ optional($process->department)->name ?? 'Unknown Department' }}</span>
                        <span class="text-[9px] text-gray-500 {{ $isFinished ? 'opacity-50' : '' }}"><i class="fa-regular fa-calendar-check text-[8px] mr-0.5"></i> Target: {{ $process->target_completion_date ? \Carbon\Carbon::parse($process->target_completion_date)->format('d M') : '-' }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <span class="text-xs text-orange-500 italic flex items-center gap-1">
        <i class="fa-solid fa-triangle-exclamation"></i> No Routing Yet
    </span>
@endif
