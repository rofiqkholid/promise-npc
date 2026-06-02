@extends('layouts.app')

@section('title', $pageTitle ?? 'Process Production')
@section('page_title', 'Transaction / ' . ($pageTitle ?? 'Process Production'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-industry' }} text-blue-500"></i> {{ $pageTitle ?? 'Process Production' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    <!-- Table -->
    <div class="p-6">

        <!-- Search Form -->
        <div class="mb-4" x-data="{
            searchQuery: '{{ request('search') }}',
            performSearch() {
                fetch('{{ route('tracking.production') }}?search=' + encodeURIComponent(this.searchQuery))
                .then(res => res.text())
                .then(html => {
                    let doc = new DOMParser().parseFromString(html, 'text/html');
                    document.querySelector('tbody').innerHTML = doc.querySelector('tbody').innerHTML;
                    let pagination = document.querySelector('.p-4.border-t nav');
                    let newPagination = doc.querySelector('.p-4.border-t nav');
                    if(pagination && newPagination) pagination.parentElement.innerHTML = newPagination.parentElement.innerHTML;
                    window.history.pushState(null, '', '?search=' + encodeURIComponent(this.searchQuery));
                });
            }
        }">
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" x-model="searchQuery" x-ref="searchInput"
                    placeholder="Search Part No, Part Name, PO No..."
                    @input.debounce.500ms="performSearch()"
                    class="!pl-10 !pr-10 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-none">
                <button type="button" x-show="searchQuery.length > 0" style="display:none;"
                    @click="searchQuery=''; performSearch(); $refs.searchInput.focus()"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-64">Part Info / PO</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center w-32">Status PO</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Routing Execution Overview</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-48">Action Production</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($parts as $part)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition text-sm">
                        <td class="px-6 py-4 text-slate-800 dark:text-slate-200 text-sm">
                            {{ ($parts->currentPage() - 1) * $parts->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-200 font-bold text-sm">{{ optional($part->product)->part_no }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-2">{{ optional($part->product)->part_name }}</div>
                            
                            <div class="text-blue-600 dark:text-blue-400 font-semibold text-[10px] uppercase">PO: {{ optional($part->event)->po_no }}</div>
                            <div class="text-[10px] text-gray-400 mt-1"><span class="font-bold text-gray-600 dark:text-gray-300">{{ number_format($part->qty) }} PCS</span> | Delv Target: {{ \Carbon\Carbon::parse($part->delivery_date)->format('d M') }}</div>
                        </td>
                        <td class="px-6 py-4 text-center align-middle">
                            @if($part->status === 'PO_REGISTERED')
                                <div class="inline-flex flex-col items-center gap-1.5 px-3 py-2 bg-slate-50 border border-slate-200 text-[10px] text-slate-500 italic">
                                    <i class="fa-solid fa-lock text-sm"></i> Planned
                                </div>
                            @elseif($part->status === 'WAITING_DEPT_CONFIRM')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-yellow-100 border border-yellow-200 text-yellow-800 text-[10px] font-bold tracking-wide"><i class="fa-solid fa-gears fa-spin"></i> IN PROCESS</span>
                            @else
                                <div class="text-[10px] text-gray-400 italic font-medium"><i class="fa-solid fa-check text-green-500"></i> Submitted to QC</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
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
                        </td>
                        <td class="px-6 py-4 text-right align-middle pointer-events-auto">
                            @if($part->status === 'PO_REGISTERED')
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Not yet send to production
                                </div>
                            @elseif($part->status === 'WAITING_DEPT_CONFIRM')
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
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-solid fa-industry text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>Not yet in production</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        {{ $parts->links() }}
    </div>
</div>

{{-- Modal: Production Done --}}
<div id="modal-complete" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 shadow-2xl w-full max-w-md mx-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-flag-checkered text-amber-500"></i> Completion Confirmation <span id="modal-process-name-title"></span>
            </h3>
            <button onclick="closeCompleteModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-xl leading-none">&times;</button>
        </div>
        <form id="form-complete" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="process_id" id="modal-process-id" value="">
            <div class="px-6 py-5 space-y-4">
                <div class="p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-800/50 flex flex-col gap-1">
                    <p class="text-xs text-amber-800 dark:text-amber-200 font-medium">You are about to complete the following process stage:</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span id="modal-process-name" class="font-black text-amber-600 dark:text-amber-400"></span>
                        <span class="text-gray-400 dark:text-gray-500 text-[10px]">IN DEPARTMENT</span>
                        <span id="modal-department-name" class="font-bold text-gray-600 dark:text-gray-300 uppercase text-[10px]"></span>
                    </div>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-gray-500 dark:text-gray-400 text-[10px]"><i class="fa-solid fa-crosshairs"></i> Target Deadline:</span>
                        <span id="modal-target-date" class="font-bold text-gray-700 dark:text-gray-200 text-[10px]"></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Total Qty Completed <span class="text-red-500">*</span></label>
                        <input type="number" name="actual_qty" required min="0" placeholder="Pcs Count"
                            class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:bg-gray-700 dark:text-white">
                        <p class="text-[9px] text-gray-400 mt-1 italic" id="modal-qty-helper">Total actual parts (Actual Qty).</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Actual Completion Date <span class="text-red-500">*</span></label>
                        <input type="date" name="actual_completion_date" required readonly
                            class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-amber-500 focus:ring-amber-500 bg-gray-100 dark:bg-gray-700 dark:text-white cursor-not-allowed text-gray-600">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Upload Part Photo Evidence<span class="text-red-500">*</span></label>
                    <input type="file" name="photo" required accept="image/jpeg,image/png,image/gif"
                        class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-xs file:font-semibold file:bg-amber-50 file:text-amber-700 dark:file:bg-amber-900/30 dark:file:text-amber-400 hover:file:bg-amber-100 uppercase file:cursor-pointer border border-gray-300 dark:border-gray-600">
                    <p class="text-[10px] text-gray-400 mt-1 italic">Max 5 MB (JPG/PNG). Photo of a batch of parts.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Production Notes <span class="text-gray-400 text-[10px] font-normal">(optional)</span></label>
                    <textarea name="production_notes" rows="3" placeholder="Example: Completed ahead of schedule..."
                        class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:bg-gray-700 dark:text-white"></textarea>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <button type="button" onclick="closeCompleteModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-amber-500 hover:bg-amber-600 shadow-sm transition flex items-center gap-1">
                    <i class="fa-solid fa-check"></i> Complete Process
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openCompleteModal(partId, processId, processName, departmentName, targetDate, actionUrl, partQty) {
    document.getElementById('form-complete').action = actionUrl;
    document.getElementById('modal-process-id').value = processId;
    document.getElementById('modal-process-name-title').textContent = processName;
    document.getElementById('modal-process-name').textContent = processName;
    document.getElementById('modal-department-name').textContent = departmentName;
    document.getElementById('modal-target-date').textContent = targetDate;
    
    const qtyInput = document.querySelector('#modal-complete input[name="actual_qty"]');
    qtyInput.min = partQty;
    document.getElementById('modal-qty-helper').innerHTML = 'Minimal sama dengan Planning PO: <b>' + partQty + ' PCS</b>.';
    
    document.getElementById('modal-complete').classList.remove('hidden');
    // Set today as default
    const dateInput = document.querySelector('#modal-complete input[name="actual_completion_date"]');
    if (!dateInput.value) dateInput.value = new Date().toISOString().substring(0, 10);
}
function closeCompleteModal() {
    document.getElementById('modal-complete').classList.add('hidden');
}
// Close on backdrop click
document.getElementById('modal-complete').addEventListener('click', function(e) {
    if (e.target === this) closeCompleteModal();
});
</script>
@endpush

