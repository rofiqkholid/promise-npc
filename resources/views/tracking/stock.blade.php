@extends('layouts.app')

@section('title', $pageTitle ?? 'Finished Goods Stock')
@section('page_title', 'Transaction / ' . ($pageTitle ?? 'Finished Goods Stock (FG)'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid {{ $pageIcon ?? 'fa-boxes-stacked' }} text-blue-500"></i> {{ $pageTitle ?? 'Finished Goods Stock (FG)' }}
            </h2>
            @if(isset($pageDesc))
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="p-6">

        <!-- Search Form -->
        <div class="mb-4" x-data="{
            searchQuery: '{{ request('search') }}',
            performSearch() {
                fetch('{{ route('tracking.stock') }}?search=' + encodeURIComponent(this.searchQuery))
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
                        <th scope="col" class="px-6 py-4 font-semibold">Delivery Target & Time</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part Info</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Qty</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Status Process</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($parts as $part)
                    @php
                        // Countdown remaining days
                        $now = \Carbon\Carbon::now()->startOfDay();
                        $target = \Carbon\Carbon::parse($part->delivery_date)->startOfDay();
                        $diffDays = $now->diffInDays($target, false);
                        
                        $isOverdue = $diffDays < 0;
                        $isUrgent = $diffDays >= 0 && $diffDays <= 3;
                        
                        $timeStatusClass = $isOverdue ? 'bg-red-100 text-red-700 border-red-200' : ($isUrgent ? 'bg-orange-100 text-orange-700 border-orange-200' : 'bg-green-100 text-green-700 border-green-200');
                        $timeStatusText = $isOverdue ? 'Overdue ' . abs($diffDays) . ' Days' : ($diffDays == 0 ? 'Deliver Today' : 'Remaining ' . $diffDays . ' Days');
                        $timeStatusIcon = $isOverdue ? 'fa-triangle-exclamation' : 'fa-clock';
                        
                        // Retrieve customer info
                        $customerName = optional(optional(optional($part->product)->vehicleModel)->customer)->code ?? 'Unknown Customer';
                        $modelName = optional(optional($part->product)->vehicleModel)->name ?? '-';
                        
                        $categoryName = optional(optional($part->event)->customerCategory)->name ?? '-';
                        $grName = optional(optional($part->event)->deliveryGroup)->name ?? '-';
                    @endphp
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition text-sm">
                        
                        <td class="px-6 py-4 text-slate-800 dark:text-slate-200 text-sm">
                            {{ ($parts->currentPage() - 1) * $parts->perPage() + $loop->iteration }}
                        </td>

                        {{-- Delivery Target --}}
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800 dark:text-gray-100 mb-1 flex items-center gap-1.5">
                                <i class="fa-solid fa-building text-gray-400"></i> {{ $customerName }}
                            </div>
                            <div class="text-xs text-gray-500 font-medium mb-2 pl-4">
                                <div class="mb-1">Model: <span class="text-blue-600 dark:text-blue-400">{{ $modelName }}</span></div>
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-800 text-[9px] font-bold tracking-wider" title="Category Customer">{{ $categoryName }}</span>
                                    <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 text-[9px] font-bold tracking-wider" title="Delivery Group (GR)">{{ $grName }}</span>
                                </div>
                            </div>
                            
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-bold border {{ $timeStatusClass }}">
                                <i class="fa-solid {{ $timeStatusIcon }}"></i> {{ $timeStatusText }}
                            </span>
                        </td>
                        
                        {{-- Part Info --}}
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-200 font-bold text-sm">{{ optional($part->product)->part_no }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ optional($part->product)->part_name }}</div>
                            <div class="text-[10px] text-gray-400 mt-1 uppercase">PO: {{ optional($part->event)->po_no }}</div>
                        </td>
                        
                        {{-- Qty & Target Date --}}
                        <td class="px-6 py-4">
                            <div class="text-gray-800 dark:text-gray-300 font-black text-lg mb-0.5">{{ number_format($part->qty) }} <span class="text-xs font-semibold text-gray-500">PCS</span></div>
                            @if($part->delivered_qty > 0)
                            <div class="text-[11px] font-bold text-blue-600 dark:text-blue-400 mb-1">
                                <i class="fa-solid fa-truck-ramp-box"></i> Delivered: {{ number_format($part->delivered_qty) }} / {{ number_format($part->qty) }}
                            </div>
                            @endif
                            <div class="text-[11px] font-medium text-gray-500">
                                Target: {{ \Carbon\Carbon::parse($part->delivery_date)->format('d M Y') }}
                            </div>
                        </td>
                        
                        {{-- Approval Info --}}
                        <td class="px-6 py-4 align-top">
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
                        </td>
                        
                        {{-- Action --}}
                        <td class="px-6 py-4 text-right pointer-events-auto">
                            <div class="flex flex-col items-end gap-2 text-sm">
                            @if($part->status === 'CLOSED')
                                <div class="px-3 py-2 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-[10px] text-blue-600 dark:text-blue-400 italic flex items-center gap-1.5 cursor-not-allowed font-bold">
                                    <i class="fa-solid fa-check-double text-[10px]"></i> Already Delivered (Closed)
                                </div>
                            @elseif(!in_array($part->status, ['FINISHED', 'OUTSTANDING']))
                                <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center gap-1.5 cursor-not-allowed">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Waiting for Process to Complete
                                </div>
                            @else
                                <button type="button" onclick="openDeliverModal('{{ $part->hashed_id }}', '{{ $part->qty - $part->delivered_qty }}', '{{ route('tracking.deliver', $part->hashed_id) }}', '{{ optional($part->product)->part_no }}')" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white shadow-sm font-medium transition text-xs flex items-center justify-center gap-2 w-full">
                                    <i class="fa-solid fa-truck-fast"></i> Deliver Parts
                                </button>
                                @if($part->checksheet)
                                <a href="{{ route('checksheets.print-label', $part->hashed_id) }}" target="_blank" class="px-4 py-2 bg-white text-blue-600 border border-blue-200 hover:bg-blue-50 shadow-sm font-medium transition text-xs flex items-center justify-center gap-2 w-full">
                                    <i class="fa-solid fa-print"></i> Print QC Label
                                </a>
                                @endif
                                <p class="text-[9px] text-gray-400 italic text-right w-full">Remaining: {{ number_format($part->qty - $part->delivered_qty) }} PCS</p>
                            @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-solid fa-box-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>Warehouse empty / No parts ready to deliver.</p>
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

<!-- Deliver Modal -->
<div id="deliverModal" class="fixed inset-0 z-50 hidden bg-gray-900/50 backdrop-blur-sm overflow-y-auto h-full w-full flex items-center justify-center">
    <div class="relative w-full max-w-md bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden scale-95 opacity-0 transition-all duration-300" id="deliverModalContent">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-truck-ramp-box text-blue-500"></i> Parts Delivery Form
            </h3>
            <button type="button" onclick="closeDeliverModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <form id="deliverForm" method="POST" action="">
            @csrf
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Part No: <strong id="modalPartNo" class="text-gray-800 dark:text-gray-200"></strong><br>
                    Please enter the quantity of parts to be delivered to the customer.
                </p>
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Delivery Qty <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" id="modalDeliveredQty" name="delivered_qty" min="1" required
                            class="w-full pl-4 pr-12 py-2 border border-gray-300 dark:border-gray-600 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-bold text-lg text-gray-800 dark:bg-gray-700 dark:text-white">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-gray-500 font-semibold text-sm">
                            PCS
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Remaining parts to deliver: <strong id="modalMaxQtyText" class="text-blue-600 dark:text-blue-400"></strong> PCS
                    </p>
                </div>
                
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800/50 p-3 text-xs text-yellow-800 dark:text-yellow-300 mb-2">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> Make sure you have printed the Delivery Note from your internal system before this process.
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <button type="button" onclick="closeDeliverModal()" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 shadow-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-600 transition text-sm">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white shadow-sm font-bold transition flex items-center gap-2 text-sm">
                    <i class="fa-solid fa-paper-plane"></i> Delivery Process
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openDeliverModal(id, maxQty, url, partNo) {
        const modal = document.getElementById('deliverModal');
        const modalContent = document.getElementById('deliverModalContent');
        const form = document.getElementById('deliverForm');
        const qtyInput = document.getElementById('modalDeliveredQty');
        const maxQtyText = document.getElementById('modalMaxQtyText');
        const partNoText = document.getElementById('modalPartNo');
        
        form.action = url;
        qtyInput.max = maxQty;
        qtyInput.value = maxQty;
        maxQtyText.textContent = maxQty;
        partNoText.textContent = partNo;
        
        modal.classList.remove('hidden');
        // Trigger reflow
        void modal.offsetWidth;
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }
    
    function closeDeliverModal() {
        const modal = document.getElementById('deliverModal');
        const modalContent = document.getElementById('deliverModalContent');
        
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
</script>
@endpush

