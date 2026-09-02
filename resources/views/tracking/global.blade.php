@extends('layouts.app')

@section('title', $pageTitle ?? 'Global Tracking')
@section('page_title', 'Transactions / ' . ($pageTitle ?? 'Global Tracking'))

@section('content')
<div id="globalTrackingContainer"
     class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden" 
     x-data="globalTrackingComponent()"
     @open-po-modal.window="openModal($event.detail)"
     @open-photo-modal.window="activePhotoPart = $event.detail">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-list-check' }} text-blue-500"></i> {{ $pageTitle ?? 'Global Tracking' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    @if(isset($metrics))
    <!-- Dashboard Cards -->
    <div class="px-6 pt-6 pb-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Event -->
        <div class="bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-calendar-check mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total Events</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_events']) }}</h3>
            </div>
        </div>
        
        <!-- Card 2: Total PO -->
        <div class="bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-lg shadow-indigo-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-file-invoice mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total PO</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_pos']) }}</h3>
            </div>
        </div>

        <!-- Card 3: Total Part -->
        <div class="bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 bg-gradient-to-br from-amber-500 to-amber-600 shadow-lg shadow-amber-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-cubes mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Total Parts</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_parts']) }}</h3>
            </div>
        </div>

        <!-- Card 4: PO Close -->
        <div class="bg-white dark:bg-gray-800 p-4 border border-gray-200 dark:border-gray-700 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex items-center gap-4 transition-transform hover:-translate-y-1 duration-300">
            <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/30 flex items-center justify-center text-white text-xl">
                <i class="fa-solid fa-flag-checkered mt-1"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider mb-1">Closed PO</p>
                <h3 class="text-2xl font-black text-gray-800 dark:text-white leading-none">{{ number_format($metrics['total_po_close']) }}</h3>
            </div>
        </div>
    </div>
    @endif

    <!-- Table -->
    <div class="p-6">
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="globalTrackingTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-4 py-2 font-semibold w-16">No</th>
                        <th scope="col" class="px-4 py-2 font-semibold w-1/4">Event & PO Number</th>
                        <th scope="col" class="px-4 py-2 font-semibold w-1/12 text-center">Part Count</th>
                        <th scope="col" class="px-4 py-2 font-semibold w-1/12">Nearest</th>
                        <th scope="col" class="px-4 py-2 font-semibold text-center w-5/12">Overall Progress</th>
                        <th scope="col" class="px-4 py-2 font-semibold text-right w-1/6">System Duration</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- DataTables will fill this -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Teleport Modal: PO Part Details -->
    <template x-teleport="body">
        <div>
            <!-- Detail Modal -->
            <div x-show="selectedPo !== null" class="relative z-[100]" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak style="display: none;">
                <!-- Backdrop -->
                <div x-show="selectedPo !== null"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
              
                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div x-show="selectedPo !== null"
                            @click.away="closeModal()"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            class="relative transform overflow-hidden bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-5xl">
                            
                            <!-- Header -->
                            <div class="bg-gray-50/80 dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2" id="modal-title">
                                    <i class="fa-solid fa-list-check text-blue-500"></i> Part Details: <span x-text="selectedPo?.po_no || '-'"></span>
                                </h3>
                                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>
                            
                            <!-- Body table -->
                            <div class="px-4 py-2 overflow-y-auto max-h-[70vh]">
                                <div class="border border-gray-200 dark:border-gray-700 overflow-hidden">
                                    <table class="w-full text-[11px] text-left text-slate-600 dark:text-slate-400">
                                        <thead class="bg-blue-50/50 dark:bg-blue-900/20 text-slate-700 dark:text-slate-300 border-b border-gray-200 dark:border-gray-700 uppercase tracking-wider">
                                            <tr>
                                                <th class="px-4 py-3 w-1/4">Part Details</th>
                                                <th class="px-4 py-3 w-1/5">Qty & Target</th>
                                                <th class="px-4 py-3 text-center">Progress</th>
                                            </tr>
                                        </thead>
                                        <template x-for="part in (selectedPo?.parts || [])" :key="part.id">
                                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
                                                <tr class="hover:bg-blue-50/30 transition">
                                                    <td class="px-4 py-3">
                                                        <div class="font-bold text-gray-800 dark:text-gray-200 text-xs" x-text="part.product?.part_no || '-'"></div>
                                                        <div class="text-[10px] text-gray-500 mt-0.5" x-text="part.product?.part_name || '-'"></div>
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        <div class="font-bold text-gray-700 dark:text-gray-300" x-text="Number(part.qty || 0).toLocaleString('id-ID') + ' PCS'"></div>
                                                        <template x-if="part.delivered_qty > 0">
                                                            <div class="text-[10px] font-bold text-blue-600 mt-0.5">
                                                                <i class="fa-solid fa-truck-ramp-box"></i> Delivered: <span x-text="Number(part.delivered_qty).toLocaleString('id-ID') + ' / ' + Number(part.qty).toLocaleString('id-ID')"></span>
                                                            </div>
                                                        </template>
                                                        <div class="text-[10px] mt-0.5" :class="isPartLate(part) ? 'text-red-500 font-bold' : 'text-gray-500'">
                                                            <i class="fa-regular fa-calendar md:mr-1"></i> <span x-text="formatDateStr(part.delivery_date)"></span>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-3 align-middle">
                                                        <div class="flex items-start w-full min-w-[200px] pt-1">
                                                            <template x-for="(step, sIdx) in getPartSteps(part)" :key="sIdx">
                                                                <div class="flex flex-col items-center flex-1 relative">
                                                                    <template x-if="sIdx < 4">
                                                                        <div class="absolute h-[2px]" :class="step.isPast ? 'bg-emerald-500' : 'bg-gray-200 dark:bg-gray-700'" style="width: calc(100% - 24px); left: calc(50% + 12px); top: 11px;"></div>
                                                                    </template>
                                                                    
                                                                    <div @click="step.title === 'Part Making' ? (expandedPM = (expandedPM === part.id ? null : part.id)) : null"
                                                                         :class="[
                                                                            'z-10 relative border-2 w-6 h-6 flex items-center justify-center text-[10px] transition-all duration-300',
                                                                            step.circleClass,
                                                                            step.title === 'Part Making' ? 'cursor-pointer hover:scale-125 hover:shadow-md' : ''
                                                                         ]"
                                                                         :title="step.title === 'Part Making' ? 'Click to view Part Making Route Details' : ''">
                                                                        <i class="fa-solid" :class="step.icon"></i>
                                                                        <template x-if="step.isPast || (step.isReached && sIdx === 4 && part.status === 'CLOSED')">
                                                                            <div class="absolute -bottom-1 -right-1 bg-white dark:bg-gray-800 w-3 h-3 flex items-center justify-center text-[8px] text-emerald-600 shadow-sm">
                                                                                <i class="fa-solid fa-circle-check"></i>
                                                                            </div>
                                                                        </template>
                                                                    </div>
                                                                    <span class="text-[8px] font-bold uppercase tracking-wider text-center mt-1.5 whitespace-nowrap" :class="step.textClass" x-text="step.title"></span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Sub-processes Expandable Row -->
                                                <tr x-show="expandedPM === part.id" class="bg-blue-50/20 dark:bg-gray-800/30 transition-all" x-cloak>
                                                    <td colspan="3" class="px-4 py-3 border-l-4 border-blue-400">
                                                        <div class="ml-2">
                                                            <div class="flex items-center justify-between mb-2">
                                                                <h5 class="text-[9px] font-bold uppercase tracking-widest text-slate-500 flex items-center gap-1.5"><i class="fa-solid fa-route"></i> Route Details: Part Making</h5>
                                                                <div class="flex gap-2">
                                                                    <template x-if="(part.processes || []).some(p => p.status === 'FINISHED')">
                                                                        <button type="button" @click="activePhotoPart = part" class="text-[9px] font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2 py-0.5 transition flex items-center gap-1 shadow-sm">
                                                                            <i class="fa-solid fa-camera"></i> Check Qty & Photo
                                                                        </button>
                                                                    </template>
                                                                    <template x-if="part.checksheet">
                                                                        <a :href="'/checksheets/' + part.checksheet.id + '/edit?readonly=1'" class="text-[9px] font-bold text-purple-600 bg-purple-50 hover:bg-purple-100 border border-purple-200 px-2 py-0.5 transition flex items-center gap-1 shadow-sm">
                                                                            <i class="fa-solid fa-clipboard-check"></i> View Checksheet
                                                                        </a>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                            
                                                            <template x-if="!part.processes || part.processes.length === 0">
                                                                <div class="text-[10px] text-gray-400 italic bg-gray-100/50 p-2 w-max">No route mapping (Routing) yet for this part.</div>
                                                            </template>
                                                            <template x-if="part.processes && part.processes.length > 0">
                                                                <div class="flex flex-wrap items-center gap-x-1 gap-y-2 mt-1 relative z-10 w-full overflow-x-auto pb-1">
                                                                    <template x-for="(pProc, pIdx) in getSortedProcesses(part)" :key="pProc.id">
                                                                        <div class="flex items-center shrink-0">
                                                                            <div class="flex items-center gap-1.5 px-2 py-1 text-[9px] font-bold border shadow-sm" :class="getProcessBadgeClass(part, pProc)">
                                                                                <i class="fa-solid" :class="getProcessIcon(part, pProc)"></i>
                                                                                <span x-text="pProc.process?.process_name || ('Process ' + (pIdx + 1))"></span>
                                                                            </div>
                                                                            <template x-if="pIdx < (part.processes || []).length - 1">
                                                                                <div class="w-3 h-px bg-gray-300 mx-1"></div>
                                                                            </template>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </template>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Footer -->
                            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 border-t border-gray-200 dark:border-gray-700 flex flex-row-reverse">
                                <button type="button" @click="closeModal()" class="inline-flex w-full justify-center bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 sm:mt-0 sm:w-auto transition-colors">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photos Modal -->
            <div x-show="activePhotoPart !== null" class="relative z-[150]" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak style="display: none;">
                <div x-show="activePhotoPart !== null"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
              
                <div class="fixed inset-0 z-[160] w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div x-show="activePhotoPart !== null"
                            @click.away="activePhotoPart = null"
                            x-transition:enter="ease-out duration-300"
                            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave="ease-in duration-200"
                            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            class="relative transform overflow-hidden bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                            
                            <!-- Header -->
                            <div class="bg-gray-50/80 dark:bg-gray-800 px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                                <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-camera text-blue-500"></i> Production Report: <span x-text="activePhotoPart?.product?.part_no || '-'"></span>
                                </h3>
                                <button type="button" @click="activePhotoPart = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>
                            
                            <!-- Body -->
                            <div class="p-6 max-h-[75vh] overflow-y-auto bg-gray-50/50 dark:bg-gray-900/50">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <template x-for="p in ((activePhotoPart?.processes || []).slice().sort((a,b) => (a.sequence_order || 0) - (b.sequence_order || 0)))" :key="p.id">
                                        <div class="flex flex-col bg-white dark:bg-gray-800 overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow transition-shadow group" :class="p.status === 'FINISHED' ? '' : 'opacity-60 saturate-50'">
                                            <!-- Image Box -->
                                            <div class="relative w-full aspect-video bg-gray-900 flex items-center justify-center border-b border-gray-100 dark:border-gray-700">
                                                <template x-if="p.photo_proof">
                                                    <div class="w-full h-full relative">
                                                        <img :src="'{{ url('file/storage') }}/' + p.photo_proof.replace('public/', '').replace(/^\/+/, '')" class="w-full h-full object-contain">
                                                        <a :href="'{{ url('file/storage') }}/' + p.photo_proof.replace('public/', '').replace(/^\/+/, '')" target="_blank" class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white font-bold text-sm gap-2 backdrop-blur-[2px]">
                                                            <i class="fa-solid fa-expand"></i> Enlarge Photo
                                                        </a>
                                                    </div>
                                                </template>
                                                <template x-if="!p.photo_proof">
                                                    <div class="text-gray-500 dark:text-gray-400 flex flex-col items-center gap-2">
                                                        <i class="fa-solid fa-image text-3xl opacity-50"></i>
                                                        <span class="text-xs font-medium tracking-wide">No Photo Yet</span>
                                                    </div>
                                                </template>
                                                
                                                <!-- Status Floating Badge -->
                                                <div class="absolute top-3 right-3 shadow-md">
                                                    <template x-if="p.status === 'FINISHED'">
                                                        <span class="px-2.5 py-1 bg-emerald-500 text-white text-[10px] font-black tracking-wider uppercase"><i class="fa-solid fa-check mr-1"></i> Done</span>
                                                    </template>
                                                    <template x-if="p.status !== 'FINISHED'">
                                                        <span class="px-2.5 py-1 bg-white/90 text-gray-700 text-[10px] font-bold tracking-wider shadow-sm uppercase" x-text="p.status"></span>
                                                    </template>
                                                </div>
                                            </div>

                                            <!-- Content Box -->
                                            <div class="p-4 flex flex-col flex-1">
                                                <h4 class="font-bold text-base text-gray-800 dark:text-gray-100 mb-1 flex items-center gap-2">
                                                    <span class="flex-shrink-0 w-6 h-6 inline-flex items-center justify-center bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400 text-xs shadow-sm" x-text="p.sequence_order"></span>
                                                    <span x-text="p.process?.process_name || ('Process ' + p.sequence_order)"></span>
                                                </h4>
                                                
                                                <div class="mt-3 space-y-2">
                                                    <div class="flex items-center justify-between text-xs">
                                                        <span class="text-gray-500 dark:text-gray-400 font-medium"><i class="fa-solid fa-building-user w-4"></i> Department:</span> 
                                                        <span class="font-bold text-gray-700 dark:text-gray-200" x-text="p.department?.name || '-'"></span>
                                                    </div>
                                                    <div class="flex items-center justify-between text-xs">
                                                        <span class="text-gray-500 dark:text-gray-400 font-medium"><i class="fa-regular fa-calendar-check w-4"></i> Actual Date:</span> 
                                                        <span class="font-bold text-gray-700 dark:text-gray-200" x-text="formatDateStr(p.actual_completion_date)"></span>
                                                    </div>
                                                </div>

                                                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
                                                    <div class="flex flex-col">
                                                        <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Target Qty</span>
                                                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-0.5" x-text="Number(activePhotoPart?.qty || 0).toLocaleString('id-ID') + ' PCS'"></span>
                                                    </div>
                                                    <div class="flex flex-col items-end">
                                                        <span class="text-[9px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest">Actual QTY</span>
                                                        <template x-if="p.actual_qty">
                                                            <span class="text-sm font-black text-blue-600 dark:text-blue-400 mt-0.5" x-text="Number(p.actual_qty).toLocaleString('id-ID') + ' PCS'"></span>
                                                        </template>
                                                        <template x-if="!p.actual_qty">
                                                            <span class="text-xs font-bold text-amber-500 italic mt-1">Not Reported</span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Footer -->
                            <div class="bg-gray-50 dark:bg-gray-800 px-4 py-2 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                                <button type="button" @click="activePhotoPart = null" class="px-4 py-2 text-[13px] font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 transition">Close Report</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
window.globalPosData = {};

function formatDateStr(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr.split('T')[0] + 'T00:00:00');
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]} ${d.getFullYear()}`;
}

function isPartLate(part) {
    if (!part || !part.delivery_date) return false;
    if (['CLOSED'].includes(part.status)) return false;
    const d = new Date(part.delivery_date.split('T')[0] + 'T00:00:00');
    return new Date().setHours(0,0,0,0) > d.getTime();
}

function globalTrackingComponent() {
    return {
        selectedPo: null,
        activePhotoPart: null,
        expandedPM: null,
        openEventId: {{ request('open_event') ? request('open_event') : 'null' }},
        fromDashboard: {{ request('from_dashboard') ? 'true' : 'false' }},
        openModal(po) {
            this.selectedPo = po;
            this.expandedPM = null;
        },
        closeModal() {
            if (this.fromDashboard) {
                window.location.href = '{{ route('dashboard') }}';
            } else {
                this.selectedPo = null;
                this.expandedPM = null;
            }
        },
        getPartSteps(part) {
            const phases = ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK', 'WAITING_MGM_CHECK', 'FINISHED', 'CLOSED'];
            const stepDefs = [
                { icon: 'fa-file-contract', title: 'Draft' },
                { icon: 'fa-industry', title: 'Part Making' },
                { icon: 'fa-microscope', title: 'QE' },
                { icon: 'fa-user-tie', title: 'MGM' },
                { icon: 'fa-boxes-stacked', title: 'Delivery' }
            ];

            let mappedStatus = part.status === 'WAITING_APPROVAL' ? 'WAITING_MGM_CHECK' : part.status;
            let pIndex = phases.indexOf(mappedStatus);
            if (part.status === 'CLOSED') pIndex = 5;
            if (part.status === 'OUTSTANDING') pIndex = 4;

            const isLate = isPartLate(part);

            return stepDefs.map((step, sIdx) => {
                const isReached = pIndex >= sIdx;
                const isActive = pIndex === sIdx;
                const isPast = pIndex > sIdx;

                let circleClass = 'border-gray-200 bg-white dark:bg-gray-800 text-gray-400';
                if (isPast || (isReached && sIdx === 4 && part.status === 'CLOSED')) {
                    circleClass = 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600';
                    if (isActive) circleClass += ' ring-2 ring-emerald-100';
                } else if (isActive) {
                    if (isLate) {
                        circleClass = 'border-red-500 bg-red-50 dark:bg-red-900/30 text-red-600 ring-2 ring-red-100 shadow-sm';
                    } else {
                        circleClass = 'border-amber-500 bg-amber-50 dark:bg-amber-900/30 text-amber-600 ring-2 ring-amber-100 shadow-sm';
                    }
                }

                let textClass = isActive ? (isLate ? 'text-red-600' : 'text-amber-600') : (isReached ? 'text-emerald-600' : 'text-gray-400');

                return {
                    ...step,
                    isReached,
                    isActive,
                    isPast,
                    circleClass,
                    textClass
                };
            });
        },
        getSortedProcesses(part) {
            return (part.processes || []).slice().sort((a,b) => (a.sequence_order || 0) - (b.sequence_order || 0));
        },
        getProcessBadgeClass(part, pProc) {
            const processes = this.getSortedProcesses(part);
            const activeProc = processes.find(p => p.status === 'WAITING');
            const today = new Date().setHours(0,0,0,0);
            
            let isSpLate = false;
            if (pProc.target_completion_date) {
                const targetDate = new Date(pProc.target_completion_date.split('T')[0] + 'T00:00:00').getTime();
                if (pProc.actual_completion_date) {
                    const actualDate = new Date(pProc.actual_completion_date.split('T')[0] + 'T00:00:00').getTime();
                    if (actualDate > targetDate) isSpLate = true;
                } else {
                    if (today > targetDate) isSpLate = true;
                }
            }

            if (pProc.status === 'FINISHED' || pProc.actual_completion_date) {
                return isSpLate ? 'bg-red-100 text-red-700 border-red-300 ring-1 ring-red-200' : 'bg-emerald-100 text-emerald-700 border-emerald-300';
            } else if (activeProc && pProc.id === activeProc.id) {
                return isSpLate ? 'bg-red-100/80 text-red-700 border-red-300 ring-1 ring-red-300 shadow-sm' : 'bg-amber-100/80 text-amber-700 border-amber-300 ring-1 ring-amber-300 shadow-sm';
            } else {
                return isSpLate ? 'bg-red-50 text-red-500 border-red-200 opacity-80' : 'bg-gray-100 text-gray-500 border-gray-200 opacity-70';
            }
        },
        getProcessIcon(part, pProc) {
            const processes = this.getSortedProcesses(part);
            const activeProc = processes.find(p => p.status === 'WAITING');
            if (pProc.status === 'FINISHED' || pProc.actual_completion_date) {
                return 'fa-check';
            } else if (activeProc && pProc.id === activeProc.id) {
                return 'fa-circle-dot';
            }
            return 'fa-clock';
        }
    };
}

function calculatePoProgress(po) {
    const poParts = po.parts || [];
    const totalParts = poParts.length;
    const phases = ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK', 'WAITING_MGM_CHECK', 'FINISHED', 'CLOSED'];
    const steps = [
        { icon: 'fa-file-contract', title: 'Draft' },
        { icon: 'fa-industry', title: 'Part Making' },
        { icon: 'fa-microscope', title: 'QE' },
        { icon: 'fa-user-tie', title: 'MGM' },
        { icon: 'fa-boxes-stacked', title: 'Delivery' }
    ];

    const reachedCounts = [];
    const passedCounts = [];
    let isOverdueAny = false;
    const today = new Date().setHours(0,0,0,0);

    steps.forEach((step, idx) => {
        let rCount = 0;
        let pCount = 0;

        poParts.forEach(p => {
            let mappedStatus = p.status === 'WAITING_APPROVAL' ? 'WAITING_MGM_CHECK' : p.status;
            let pIndex = phases.indexOf(mappedStatus);
            if (p.status === 'CLOSED') pIndex = 5;
            if (p.status === 'OUTSTANDING') pIndex = 4;

            if (pIndex >= idx) rCount++;
            if (pIndex > idx || (idx === 4 && p.status === 'CLOSED')) {
                pCount++;
            }

            if (!['CLOSED'].includes(p.status)) {
                if (p.delivery_date) {
                    const dDate = new Date(p.delivery_date.split('T')[0] + 'T00:00:00').getTime();
                    if (today > dDate) isOverdueAny = true;
                }
                if (p.processes) {
                    p.processes.forEach(proc => {
                        if (!proc.actual_completion_date && proc.target_completion_date) {
                            const tDate = new Date(proc.target_completion_date.split('T')[0] + 'T00:00:00').getTime();
                            if (today > tDate) isOverdueAny = true;
                        }
                    });
                }
            }
        });

        reachedCounts[idx] = rCount;
        passedCounts[idx] = pCount;
    });

    return { steps, reachedCounts, passedCounts, totalParts, isOverdueAny };
}

function renderGlobalProgress(po) {
    const { steps, reachedCounts, passedCounts, totalParts, isOverdueAny } = calculatePoProgress(po);
    
    let html = `<div class="flex w-full items-start justify-center min-w-[300px] cursor-pointer hover:scale-105 transition-transform" onclick="window.triggerPoModal(${po.id})" title="Click to view part details">`;
    
    steps.forEach((step, idx) => {
        const rCount = reachedCounts[idx] || 0;
        const pCount = passedCounts[idx] || 0;
        const pPct = totalParts > 0 ? Math.round((pCount / totalParts) * 100) : 0;
        
        const rCountNext = reachedCounts[idx + 1] || 0;
        const rPctNext = totalParts > 0 ? Math.round((rCountNext / totalParts) * 100) : 0;

        let lineBg = 'bg-gray-200 dark:bg-gray-700';
        if (rPctNext === 100) lineBg = 'bg-emerald-500';

        let circleBorder = 'border-gray-200 dark:border-gray-700';
        let fillClass = 'bg-transparent';
        let iconColor = 'text-gray-400';
        let titleClass = 'text-gray-400';
        let showCheck = false;

        if (pPct === 100) {
            circleBorder = 'border-emerald-500';
            fillClass = 'bg-emerald-500';
            iconColor = 'text-white';
            titleClass = 'text-emerald-700 dark:text-emerald-400';
            showCheck = true;
        } else if (rCount > 0) {
            circleBorder = 'border-amber-500 ring-2 ring-amber-100';
            if (isOverdueAny && pPct < 100) {
                circleBorder = 'border-red-500 ring-2 ring-red-100';
            }
            fillClass = (isOverdueAny && pPct < 100) ? 'bg-red-400' : 'bg-amber-400';
            iconColor = pPct > 50 ? 'text-white' : ((isOverdueAny && pPct < 100) ? 'text-red-700' : 'text-amber-700');
            titleClass = (isOverdueAny && pPct < 100) ? 'text-red-600 font-extrabold' : 'text-amber-600 font-extrabold';
        }

        let nextLine = '';
        if (idx < steps.length - 1) {
            let fillWidth = '';
            if (rPctNext > 0 && rPctNext < 100) {
                fillWidth = `<div class="h-full bg-emerald-500 transition-all duration-700" style="width: ${rPctNext}%"></div>`;
            }
            nextLine = `<div class="absolute w-[calc(100%-2.25rem)] top-[14px] left-[calc(50%+1.125rem)] h-[3px] ${lineBg} overflow-hidden">${fillWidth}</div>`;
        }

        let checkBadge = '';
        if (showCheck) {
            checkBadge = `<div class="absolute -bottom-1 -right-1.5 bg-white dark:bg-gray-800 w-4 h-4 flex items-center justify-center z-30 leading-none shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-600 text-[12px]"></i>
            </div>`;
        }

        let pctLabel = '';
        if (rCount > 0 && pPct < 100) {
            pctLabel = `<span class="text-[9px] font-black ${isOverdueAny ? 'text-red-600' : 'text-amber-600'} mt-0.5">${pPct}%</span>`;
        }

        html += `
        <div class="flex flex-col items-center flex-1 relative group">
            ${nextLine}
            <div class="relative w-8 h-8 z-10">
                <div class="relative w-full h-full bg-white dark:bg-gray-800 border-2 ${circleBorder} flex items-center justify-center text-[12px] overflow-hidden shadow-sm transition-all duration-300">
                    <div class="absolute bottom-0 left-0 right-0 ${fillClass} transition-all duration-700 ease-out opacity-90" style="height: ${pPct}%; z-index:0;"></div>
                    <i class="fa-solid ${step.icon} relative z-10 ${iconColor}"></i>
                </div>
                ${checkBadge}
            </div>
            <div class="flex flex-col items-center mt-2 h-8">
                <span class="text-[9px] font-bold uppercase tracking-wider text-center ${titleClass} leading-tight block">${step.title}</span>
                ${pctLabel}
            </div>
        </div>`;
    });

    html += `</div>`;
    return html;
}

window.triggerPoModal = function(poId) {
    const po = window.globalPosData[poId];
    if (po) {
        window.dispatchEvent(new CustomEvent('open-po-modal', { detail: po }));
    }
};

$(document).ready(function() {
    initPromiseDataTable('#globalTrackingTable', {
        ajax: {
            url: "{{ route('tracking.index') }}",
            dataSrc: function (json) {
                (json.data || []).forEach(row => {
                    window.globalPosData[row.id] = row;
                });
                
                // If open_event request parameter is set
                const openEventId = {{ request('open_event') ? request('open_event') : 'null' }};
                if (openEventId && window.globalPosData[openEventId]) {
                    setTimeout(() => {
                        window.triggerPoModal(openEventId);
                    }, 200);
                }
                return json.data;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-2 text-center text-slate-800 dark:text-slate-200 text-[13px] font-medium' },
            { 
                data: 'po_no', 
                name: 'po_no', 
                className: 'px-4 py-2 align-top', 
                orderable: false,
                render: function(data, type, row) {
                    const catName = row.customer_category?.name || 'Unknown Event';
                    const poNo = row.po_no || '-';
                    return `<div class="text-blue-600 dark:text-blue-400 font-bold text-[11px] uppercase tracking-wide bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 px-2 py-0.5 inline-block mb-1 cursor-pointer hover:underline" onclick="window.triggerPoModal(${row.id})">${catName}</div>
                            <div class="text-gray-800 dark:text-gray-200 font-bold text-sm hover:text-blue-600 cursor-pointer" onclick="window.triggerPoModal(${row.id})">${poNo}</div>`;
                }
            },
            { 
                data: 'total_parts', 
                name: 'total_parts', 
                className: 'px-4 py-2 text-center align-middle', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    const count = (row.parts || []).length;
                    return `<span class="bg-gray-100 border border-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300 px-3 py-1 font-bold text-xs cursor-pointer hover:bg-blue-50 hover:text-blue-600" onclick="window.triggerPoModal(${row.id})">${count}</span>`;
                }
            },
            { 
                data: 'earliest_delivery', 
                name: 'earliest_delivery', 
                className: 'px-4 py-2 align-middle', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    if (!row.earliest_delivery) return '-';
                    const d = new Date(row.earliest_delivery.split('T')[0] + 'T00:00:00');
                    const isPast = new Date().setHours(0,0,0,0) > d.getTime();
                    const isComplete = row.closed_parts === row.total_parts && row.total_parts > 0;
                    const isLate = isPast && !isComplete;
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    const formatted = `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]} ${String(d.getFullYear()).slice(-2)}`;
                    
                    return `<div class="text-xs ${isLate ? 'text-red-500 font-bold' : 'text-gray-600 font-medium'}">
                        <i class="fa-regular fa-calendar-alt md:mr-1"></i> ${formatted}
                    </div>`;
                }
            },
            { 
                data: 'id', 
                name: 'overall_progress', 
                className: 'px-4 py-2 align-middle cursor-pointer', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    return renderGlobalProgress(row);
                }
            },
            { 
                data: 'created_date_formatted', 
                name: 'created_at', 
                className: 'px-4 py-2 text-right align-top', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    const inDate = row.created_date_formatted || '-';
                    const isComplete = row.closed_parts === row.total_parts && row.total_parts > 0;
                    const badge = isComplete 
                        ? `<span class="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 px-2 py-1 border border-emerald-200 shadow-sm mt-1 font-bold"><i class="fa-solid fa-check-double"></i> COMPLETE</span>`
                        : `<span class="text-amber-600 font-bold mt-1 tracking-wide">ACTIVE</span>`;
                        
                    return `<div class="text-[11px] font-medium text-gray-500 text-right w-full flex flex-col items-end gap-1">
                        <span class="bg-gray-100 dark:bg-gray-700 px-2 py-1 border border-gray-200 dark:border-gray-600">IN: ${inDate}</span>
                        ${badge}
                    </div>`;
                }
            }
        ]
    });
});
</script>
@endpush
