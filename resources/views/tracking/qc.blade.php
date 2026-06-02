@extends('layouts.app')

@section('title', $pageTitle ?? 'Quality Control (QC)')
@section('page_title', 'Transaction / ' . ($pageTitle ?? 'Quality Control (QC)'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-microscope' }} text-blue-500"></i> {{ $pageTitle ?? 'Quality Control (QC)' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    <!-- Table -->
    <div class="p-6" x-data="{
        activePhotoModal: null,
        searchQuery: '{{ request('search') }}',
        performSearch() {
            fetch('{{ route('tracking.qc') }}?search=' + encodeURIComponent(this.searchQuery))
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

        <!-- Search Form -->
        <div class="mb-4">
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
                        <th scope="col" class="px-6 py-4 font-semibold">QC Progress</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-48">Action QC</th>
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
                            <div class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50 dark:bg-gray-700 px-2 py-0.5 inline-block border border-gray-200 dark:border-gray-600">{{ optional($part->product->vehicleModel)->name ?? 'Unknown Model' }}</div>
                            <div class="text-gray-800 dark:text-gray-300 font-black flex items-center gap-1.5 mt-2"><i class="fa-solid fa-boxes-stacked text-gray-400"></i> Initial Target: {{ number_format($part->qty) }} <span class="text-xs font-semibold text-gray-500">PCS</span></div>
                        </td>
                        <td class="px-6 py-4 text-center align-middle">
                            @if(in_array($part->status, ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM']))
                                <div class="inline-flex flex-col items-center gap-1.5 px-3 py-2 bg-slate-50 border border-slate-200 text-[10px] text-slate-500 italic">
                                    <i class="fa-solid fa-industry text-sm"></i> In Production
                                </div>
                            @else
                                <div class="flex flex-col items-center gap-1">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 border border-green-200 text-green-700 text-[10px] font-bold"><i class="fa-solid fa-check-double"></i> Production Done</span>
                                    <span class="text-[11px] text-gray-500 font-medium">Date: {{ $part->actual_completion_date ? \Carbon\Carbon::parse($part->actual_completion_date)->format('d M y') : '-' }}</span>
                                    <button @click="activePhotoModal = {{ $part->id }}" class="mt-1 px-3 py-1 bg-white border border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-[10px] shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition flex items-center gap-1.5 font-bold text-gray-700 dark:text-gray-300">
                                        <i class="fa-solid fa-camera text-blue-500"></i> Check Qty Report & Photo
                                    </button>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center align-middle">
                            @if(in_array($part->status, ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM']))
                                <div class="text-[10px] text-gray-400 italic font-medium">Waiting for Parts Registration</div>
                            @elseif($part->status === 'WAITING_QE_CHECK')
                                @php
                                    $hasChecksheet = $part->checksheet()->exists();
                                @endphp
                                @if($hasChecksheet)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-100 border border-blue-200 text-blue-800 text-[10px] font-bold shadow-sm"><i class="fa-solid fa-pen-to-square"></i> FILLED & BEING CHECKED</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-orange-100 border border-orange-200 text-orange-800 text-[10px] font-bold shadow-sm"><i class="fa-solid fa-triangle-exclamation animate-pulse"></i> NOT YET INPUT BY QC</span>
                                @endif
                                <div class="mt-2 text-[10px] text-gray-500"><i class="fa-solid fa-calendar-check text-gray-400 mr-1"></i> Target QC: <span class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($part->qc_target_date)->format('d M') }}</span></div>
                            @else
                                <div class="text-[10px] text-emerald-600 font-bold bg-emerald-50 border border-emerald-100 px-2 py-1 inline-flex items-center gap-1"><i class="fa-solid fa-shield-halved"></i> PASSED QC</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right align-middle pointer-events-auto">
                            @if(in_array($part->status, ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM']))
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Not Yet Registered in QC
                                </div>
                            @elseif($part->status === 'WAITING_QE_CHECK')
                                <a href="{{ route('checksheets.create', $part->hashed_id) }}" class="inline-flex px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white shadow-sm font-bold transition items-center gap-2 text-[11px]" style="background-color: #f97316;">
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
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-solid fa-microscope text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No parts in the quality inspection queue currently.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Render Modals for Photos -->
        @foreach($parts as $part)
            @if(!in_array($part->status, ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM']))
            <div x-show="activePhotoModal === {{ $part->id }}" class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak style="display: none;">
                <div x-show="activePhotoModal === {{ $part->id }}"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
              
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div x-show="activePhotoModal === {{ $part->id }}"
                            @click.away="activePhotoModal = null"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            class="relative transform overflow-hidden bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                            
                            <!-- Header -->
                            <div class="bg-gray-50/80 dark:bg-gray-800 px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-camera text-blue-500"></i> Production Report: {{ optional($part->product)->part_no }}
                                </h3>
                                <button type="button" @click="activePhotoModal = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>
                            
                                <!-- Body -->
                                <div class="p-6 max-h-[75vh] overflow-y-auto bg-gray-50/50 dark:bg-gray-900/50">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @foreach($part->processes->sortBy('sequence_order') as $idx => $p)
                                            <div class="flex flex-col bg-white dark:bg-gray-800 overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow transition-shadow group {{ $p->status === 'FINISHED' ? '' : 'opacity-60 saturate-50' }}">
                                                <!-- Image Box -->
                                                <div class="relative w-full aspect-video bg-gray-900 flex items-center justify-center border-b border-gray-100 dark:border-gray-700">
                                                    @if($p->photo_proof)
                                                        <img src="{{ Storage::url($p->photo_proof) }}" class="w-full h-full object-contain">
                                                        <a href="{{ Storage::url($p->photo_proof) }}" target="_blank" class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white font-bold text-sm gap-2 backdrop-blur-[2px]">
                                                            <i class="fa-solid fa-expand"></i> Enlarge Photo
                                                        </a>
                                                    @else
                                                        <div class="text-gray-500 dark:text-gray-400 flex flex-col items-center gap-2">
                                                            <i class="fa-solid fa-image text-3xl opacity-50"></i>
                                                            <span class="text-xs font-medium tracking-wide">No Photo Yet</span>
                                                        </div>
                                                    @endif
                                                    
                                                    <!-- Status Floating Badge -->
                                                    <div class="absolute top-3 right-3 shadow-md">
                                                    @if($p->status === 'FINISHED')
                                                        <span class="px-2.5 py-1 bg-emerald-500 text-white text-[10px] font-black tracking-wider uppercase"><i class="fa-solid fa-check mr-1"></i> Done</span>
                                                    @else
                                                        <span class="px-2.5 py-1 bg-white/90 text-gray-700 text-[10px] font-bold tracking-wider shadow-sm uppercase">{{ $p->status }}</span>
                                                    @endif
                                                    </div>
                                                </div>

                                                <!-- Content Box -->
                                                <div class="p-4 flex flex-col flex-1">
                                                    <h4 class="font-bold text-base text-gray-800 dark:text-gray-100 mb-1 flex items-center gap-2">
                                                        <span class="flex-shrink-0 w-6 h-6 inline-flex items-center justify-center bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400 text-xs shadow-sm">{{ $p->sequence_order }}</span>
                                                        {{ optional($p->process)->process_name ?? 'Process ' . $p->sequence_order }}
                                                    </h4>
                                                    
                                                    <div class="mt-3 space-y-2">
                                                        <div class="flex items-center justify-between text-xs">
                                                            <span class="text-gray-500 dark:text-gray-400 font-medium"><i class="fa-solid fa-building-user w-4"></i> Department:</span> 
                                                            <span class="font-bold text-gray-700 dark:text-gray-200">{{ optional($p->department)->name ?? '-' }}</span>
                                                        </div>
                                                        <div class="flex items-center justify-between text-xs">
                                                            <span class="text-gray-500 dark:text-gray-400 font-medium"><i class="fa-regular fa-calendar-check w-4"></i> Actual Date:</span> 
                                                            <span class="font-bold text-gray-700 dark:text-gray-200">{{ $p->actual_completion_date ? \Carbon\Carbon::parse($p->actual_completion_date)->format('d M Y') : '-' }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
                                                        <div class="flex flex-col">
                                                            <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Target Qty</span>
                                                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-0.5">{{ number_format($part->qty) }} PCS</span>
                                                        </div>
                                                        <div class="flex flex-col items-end">
                                                            <span class="text-[9px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest">Actual Result</span>
                                                            @if($p->actual_qty)
                                                                <span class="text-sm font-black text-blue-600 dark:text-blue-400 mt-0.5">{{ number_format($p->actual_qty) }} PCS</span>
                                                            @else
                                                                <span class="text-xs font-bold text-amber-500 italic mt-1">Not Reported Yet</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            
                            <!-- Footer -->
                            <div class="bg-gray-50 dark:bg-gray-800 px-6 py-3 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                                <button type="button" @click="activePhotoModal = null" class="px-4 py-2 text-sm font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 transition">Close Report</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach

    </div>

    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        {{ $parts->links() }}
    </div>
</div>
@endsection

