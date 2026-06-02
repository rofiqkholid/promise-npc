@extends('layouts.app')

@section('title', $pageTitle ?? 'Management Check')
@section('page_title', 'Transaction / ' . ($pageTitle ?? 'Management Check'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-user-tie' }} text-blue-500"></i> {{ $pageTitle ?? 'Management Check' }}
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
                fetch('{{ route('tracking.mgm') }}?search=' + encodeURIComponent(this.searchQuery))
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
                        <th scope="col" class="px-6 py-4 font-semibold w-72">Product Identity</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Quality Validation Status (QC)</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-48">Final Validation (MGM)</th>
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
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1.5">{{ optional($part->product)->part_name }}</div>
                            <div class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50 dark:bg-gray-700 px-2 py-0.5 inline-block border border-gray-200 dark:border-gray-600 mb-2">PO: {{ optional($part->event)->po_no }} | CV: {{ optional($part->product->vehicleModel)->name ?? 'Unknown Model' }}</div>
                            <div class="text-gray-800 dark:text-gray-300 font-black flex items-center gap-1.5"><i class="fa-solid fa-boxes-stacked text-gray-400"></i> {{ number_format($part->qty) }} <span class="text-xs font-semibold text-gray-500">PCS</span></div>
                        </td>
                        <td class="px-6 py-4 text-center align-middle">
                            @if(in_array($part->status, ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK']))
                                <div class="inline-flex flex-col items-center gap-1.5 px-3 py-2 bg-slate-50 border border-slate-200 text-[10px] text-slate-500 italic">
                                    <i class="fa-solid fa-microscope text-sm"></i> Currently in QC Inspection
                                </div>
                            @else
                                <div class="flex flex-col items-center gap-1">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 border border-green-200 text-green-700 text-[10px] font-bold shadow-sm"><i class="fa-solid fa-check-double"></i> PASSED QC CERTIFICATION</span>
                                    <span class="text-[11px] text-gray-500 font-medium mt-1">Date Input: {{ $part->qc_target_date ? \Carbon\Carbon::parse($part->qc_target_date)->format('d M Y') : '-' }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right align-middle pointer-events-auto">
                            <div class="flex flex-col items-end gap-2">
                                @if(in_array($part->status, ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK']))
                                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed w-full max-w-[150px]">
                                        <i class="fa-solid fa-lock text-[8px]"></i> Not Yet Registered in MGM
                                    </div>
                                @elseif($part->status === 'WAITING_MGM_CHECK')
                                    <a href="{{ route('checksheets.create', $part->hashed_id) }}" class="inline-flex px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white shadow-sm font-bold transition items-center justify-center gap-2 text-[11px] w-full max-w-[150px]" style="background-color: #a855f7;">
                                        <i class="fa-solid fa-user-check"></i> MGM Checksheet Form
                                    </a>
                                    @if($part->checksheet)
                                    <a href="{{ route('checksheets.export', $part->checksheet->hashed_id) }}" class="inline-flex px-4 py-2 bg-green-500 hover:bg-green-600 text-white shadow-sm font-bold transition items-center justify-center gap-2 text-[11px] w-full max-w-[150px]">
                                        <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a>

                                    @endif
                                    <p class="text-[9px] text-gray-400 mt-1 italic text-right max-w-[150px] text-balance">Review checksheet and sign the FG parts check</p>
                                @else
                                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed w-full max-w-[150px]">
                                        <i class="fa-solid fa-lock text-[8px]"></i> Completed
                                    </div>
                                    @if(in_array($part->status, ['WAITING_APPROVAL', 'FINISHED']) && $part->delivered_qty == 0)
                                    @php
                                        $checksheet = $part->checksheet;
                                        $canRollback = !$checksheet || $checksheet->approval_status === null || $checksheet->approval_status === 'WAITING_MGM_STAFF';
                                    @endphp
                                    @if($canRollback)
                                    <form action="{{ route('tracking.mgm.rollback', $part->hashed_id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold transition mt-1" onclick="confirmAction(event, 'Are you sure you want to rollback this part to MGM Check stage?')">
                                            <i class="fa-solid fa-rotate-left"></i> Rollback MGM
                                        </button>
                                    </form>
                                    @endif
                                    @endif
                                    @if($part->checksheet)
                                    <a href="{{ route('checksheets.export', $part->checksheet->hashed_id) }}" class="inline-flex px-4 py-2 bg-green-500 hover:bg-green-600 text-white shadow-sm font-bold transition items-center justify-center gap-2 text-[11px] w-full max-w-[150px]">
                                        <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a>

                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-solid fa-user-tie text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No management check (MGM) submissions currently.</p>
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
@endsection

