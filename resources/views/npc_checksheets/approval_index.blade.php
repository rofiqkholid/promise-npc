@extends('layouts.app')

@section('title', 'Checksheet Approvals')
@section('page_title', 'Checksheet Approvals')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-clipboard-check text-blue-500"></i> Checksheet Approval Queue
        </h2>
    </div>

    <div class="p-6">


        <!-- Search Form -->
        <div class="mb-4" x-data="{
            searchQuery: '{{ request('search') }}',
            performSearch() {
                fetch('{{ route('checksheet-approvals.index') }}?search=' + encodeURIComponent(this.searchQuery))
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
                    placeholder="Search Part No, PO No, Event..."
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
                        <th class="px-6 py-4 font-semibold w-16">#</th>
                        <th class="px-6 py-4 font-semibold">Part No / Name</th>
                        <th class="px-6 py-4 font-semibold">Event</th>
                        <th class="px-6 py-4 font-semibold">GR / PO</th>
                        <th class="px-6 py-4 font-semibold">Model / Customer</th>
                        <th class="px-6 py-4 font-semibold text-center">Approval Stage</th>
                        <th class="px-6 py-4 font-semibold text-right w-40">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($checksheets as $index => $checksheet)
                        @php
                            $levelMap = [
                                'WAITING_QE_STAFF' => 'QE Staff / SPV',
                                'WAITING_MGM_STAFF' => 'NPC Staff / SPV',
                                'WAITING_QE_SPV' => 'QE Asst Mgr',
                                'WAITING_MGM_SPV' => 'NPC Asst Mgr',
                                'WAITING_QE_MGR' => 'QE Mgr',
                                'WAITING_MGM_MGR' => 'NPC Mgr',
                                'APPROVED' => 'Fully Approved'
                            ];
                            $levelName = $levelMap[$checksheet->approval_status] ?? str_replace('WAITING_', '', $checksheet->approval_status);
                        @endphp
                        <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group">
                            <td class="px-6 py-4 text-slate-800 dark:text-slate-200 text-sm">
                                {{ ($checksheets->currentPage() - 1) * $checksheets->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-800 dark:text-gray-200 font-bold text-sm">
                                    {{ optional($checksheet->npcPart->product)->part_no ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">
                                    {{ optional($checksheet->npcPart->product)->part_name ?? '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-blue-600 dark:text-blue-400 font-bold text-[11px] uppercase tracking-wide bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 px-2 py-0.5 inline-block mb-1">
                                    {{ optional(optional($checksheet->npcPart->event)->customerCategory)->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-700 dark:text-gray-300 font-semibold text-sm">
                                    {{ optional($checksheet->npcPart->event)->po_no ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ optional(optional($checksheet->npcPart->event)->deliveryGroup)->name ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-700 dark:text-gray-300 text-sm font-medium">
                                    {{ optional(optional($checksheet->npcPart->product)->vehicleModel)->name ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ optional(optional(optional($checksheet->npcPart->event)->customerCategory)->customer)->code ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($checksheet->approval_status === 'APPROVED')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 border border-emerald-200 text-emerald-800 text-[10px] font-bold">
                                        <i class="fa-solid fa-check-double"></i> FULLY APPROVED
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-yellow-100 border border-yellow-200 text-yellow-800 text-[10px] font-bold tracking-wide">
                                        <i class="fa-solid fa-hourglass-half animate-pulse"></i> {{ $levelName }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($checksheet->approval_status === 'APPROVED')
                                    <span class="text-xs text-emerald-600 font-semibold flex items-center justify-end gap-1">
                                        <i class="fa-solid fa-circle-check"></i> Completed
                                    </span>
                                @else
                                    <a href="{{ route('checksheet-approvals.show', $checksheet->hashed_id) }}"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white text-xs font-bold transition shadow-sm shadow-blue-500/20">
                                        <i class="fa-solid fa-eye"></i> Review & Approve
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                    <p>No checksheets waiting for approval.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        {{ $checksheets->links() }}
    </div>
</div>
@endsection
