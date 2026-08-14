@extends('layouts.app')

@section('title', $pageTitle ?? 'Quality Control (QC)')
@section('page_title', 'Transaction / ' . ($pageTitle ?? 'Quality Control (QC)'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-microscope' }} text-blue-500"></i> {{ $pageTitle ?? 'Quality Control (QC)' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    <!-- Bulk Print & Table Container -->
    <div x-data="{
        selectedParts: [],
        selectAll: false,
        toggleAll(event) {
            const isChecked = event.target.checked;
            const checkboxes = document.querySelectorAll('#qcTable .part-checkbox');
            let ids = [];
            let idsToRemove = [];
            checkboxes.forEach(cb => {
                cb.checked = isChecked;
                if (isChecked) {
                    ids.push(cb.value);
                } else {
                    idsToRemove.push(cb.value);
                }
            });

            if (isChecked) {
                this.selectedParts = [...new Set([...this.selectedParts, ...ids])];
            } else {
                this.selectedParts = this.selectedParts.filter(id => !idsToRemove.includes(id));
            }
            this.updateSelectAllState();
        },
        syncCheckboxes() {
            const checkboxes = document.querySelectorAll('#qcTable .part-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.selectedParts.includes(cb.value);
            });
            this.updateSelectAllState();
        },
        updateSelectAllState() {
            this.$nextTick(() => {
                const checkboxes = document.querySelectorAll('#qcTable .part-checkbox');
                const selectAllCb = document.getElementById('selectAllParts');
                if (checkboxes.length > 0) {
                    const allChecked = Array.from(checkboxes).every(cb => this.selectedParts.includes(cb.value));
                    this.selectAll = allChecked;
                    if (selectAllCb) selectAllCb.checked = allChecked;
                } else {
                    this.selectAll = false;
                    if (selectAllCb) selectAllCb.checked = false;
                }
            });
        },
        init() {
            $(document).on('change', '#qcTable .part-checkbox', (e) => {
                const val = e.target.value;
                const isChecked = e.target.checked;
                if (isChecked) {
                    if (!this.selectedParts.includes(val)) {
                        this.selectedParts.push(val);
                    }
                } else {
                    this.selectedParts = this.selectedParts.filter(i => i !== val);
                }
                this.updateSelectAllState();
            });

            $('#qcTable').on('draw.dt', () => {
                this.$nextTick(() => {
                    this.syncCheckboxes();
                });
            });
        }
    }">
        <!-- Bulk Print Bar -->
        <div x-show="selectedParts.length > 0" style="display: none;" class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 bg-blue-50 dark:bg-blue-900/20 flex justify-between items-center transition-all">
            <span class="text-sm font-medium text-blue-800 dark:text-blue-300">
                <span x-text="selectedParts.length"></span> labels selected
            </span>
            <form action="{{ route('checksheets.bulk-print-labels') }}" method="POST" target="_blank" class="m-0">
                @csrf
                <template x-for="id in selectedParts" :key="id">
                    <input type="hidden" name="part_ids[]" :value="id">
                </template>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm font-medium shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-print"></i> Print Selected Labels
                </button>
            </form>
        </div>

        <!-- Table Filters & Content -->
        <div class="p-6">
            <div class="mb-4 flex flex-col md:flex-row justify-between gap-4">
                <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
                    <div class="w-full md:w-64">
                        <select id="customerFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full rounded-md shadow-sm">
                            <option value="">All Customers</option>
                            @foreach($customers ?? [] as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_filter') == $customer->id ? 'selected' : '' }}>{{ $customer->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-64">
                        <select id="modelFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full rounded-md shadow-sm">
                            <option value="">All Models</option>
                            @foreach($models ?? [] as $mod)
                                <option value="{{ $mod->id }}" data-customer="{{ $mod->customer_id }}" {{ request('model_filter') == $mod->id ? 'selected' : '' }}>{{ $mod->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-64">
                        <select id="poFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full rounded-md shadow-sm">
                            <option value="">All POs</option>
                            @foreach($poList ?? [] as $po)
                                <option value="{{ $po->po_no }}" {{ request('po_filter') == $po->po_no ? 'selected' : '' }}>{{ $po->po_no }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" id="clearFiltersBtn" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium transition shadow-sm flex items-center gap-2 w-full justify-center">
                            <i class="fa-solid fa-rotate-left"></i> Reset
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
                <table id="qcTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                    <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                        <tr>
                            <th scope="col" class="px-4 py-2 text-center w-12">
                                <input type="checkbox" id="selectAllParts" @change="toggleAll($event)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer">
                            </th>
                            <th scope="col" class="px-4 py-2 font-semibold w-16">No</th>
                            <th scope="col" class="px-4 py-2 font-semibold w-64">Part Info / PO</th>
                            <th scope="col" class="px-4 py-2 font-semibold text-center w-32">Status PO</th>
                            <th scope="col" class="px-4 py-2 font-semibold text-center">QC Progress</th>
                            <th scope="col" class="px-4 py-2 font-semibold text-right w-48">Action QC</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- DataTables will fill this -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Single Production Report Modal -->
<div x-data="{
    open: false,
    partData: null,
    formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr.split('T')[0] + 'T00:00:00');
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]} ${d.getFullYear()}`;
    },
    formatNumber(num) {
        return Number(num || 0).toLocaleString('id-ID');
    },
    imageUrl(path) {
        if (!path) return '';
        const clean = path.replace('public/', '').replace(/^\//, '');
        return '{{ url('file/storage') }}/' + clean;
    }
}"
@open-report-modal.window="partData = $event.detail; open = true;"
x-show="open" 
class="relative z-[100]" 
role="dialog" 
aria-modal="true" 
x-cloak 
style="display: none;">

    <div x-show="open"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity"></div>
    
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="open"
                @click.away="open = false"
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
                        <i class="fa-solid fa-camera text-blue-500"></i> Production Report: <span x-text="partData?.product?.part_no || ''"></span>
                    </h3>
                    <button type="button" @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                
                <!-- Body -->
                <div class="p-6 max-h-[75vh] overflow-y-auto bg-gray-50/50 dark:bg-gray-900/50">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="partData && partData.processes && partData.processes.length > 0">
                        <template x-for="(p, idx) in (partData?.processes || []).slice().sort((a,b) => (a.sequence_order || 0) - (b.sequence_order || 0))" :key="p.id || idx">
                            <div class="flex flex-col bg-white dark:bg-gray-800 overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow transition-shadow group" :class="p.status === 'FINISHED' ? '' : 'opacity-60 saturate-50'">
                                <!-- Image Box -->
                                <div class="relative w-full aspect-video bg-gray-900 flex items-center justify-center border-b border-gray-100 dark:border-gray-700">
                                    <template x-if="p.photo_proof">
                                        <div class="w-full h-full relative">
                                            <img :src="imageUrl(p.photo_proof)" class="w-full h-full object-contain">
                                            <a :href="imageUrl(p.photo_proof)" target="_blank" class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity text-white font-bold text-sm gap-2 backdrop-blur-[2px]">
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
                                            <span class="font-bold text-gray-700 dark:text-gray-200" x-text="formatDate(p.actual_completion_date)"></span>
                                        </div>
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-between">
                                        <div class="flex flex-col">
                                            <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Target Qty</span>
                                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-0.5" x-text="formatNumber(partData?.qty) + ' PCS'"></span>
                                        </div>
                                        <div class="flex flex-col items-end">
                                            <span class="text-[9px] font-bold text-blue-500 dark:text-blue-400 uppercase tracking-widest">Actual Result</span>
                                            <template x-if="p.actual_qty">
                                                <span class="text-sm font-black text-blue-600 dark:text-blue-400 mt-0.5" x-text="formatNumber(p.actual_qty) + ' PCS'"></span>
                                            </template>
                                            <template x-if="!p.actual_qty">
                                                <span class="text-xs font-bold text-amber-500 italic mt-1">Not Reported Yet</span>
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
                    <button type="button" @click="open = false" class="px-4 py-2 text-[13px] font-medium border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 transition">Close Report</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.openProductionReportModal = function(row) {
        window.dispatchEvent(new CustomEvent('open-report-modal', { detail: row }));
    };

    $(document).ready(function() {
        $('#qcTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('tracking.qc') }}",
                data: function (d) {
                    d.customer_filter = $('#customerFilter').val();
                    d.model_filter = $('#modelFilter').val();
                    d.po_filter = $('#poFilter').val();
                }
            },
            responsive: true,
            stateSave: true,
            stateSaveParams: function (settings, data) {
                data.customFilters = {
                    customer: $('#customerFilter').val(),
                    model: $('#modelFilter').val(),
                    po: $('#poFilter').val()
                };
            },
            stateLoadParams: function (settings, data) {
                if (data.customFilters) {
                    if (data.customFilters.customer !== undefined) {
                        $('#customerFilter').val(data.customFilters.customer);
                    }
                    if (data.customFilters.model !== undefined) {
                        $('#modelFilter').val(data.customFilters.model);
                    }
                    if (data.customFilters.po !== undefined) {
                        $('#poFilter').val(data.customFilters.po);
                    }
                }
            },
            initComplete: function(settings, json) {
                setTimeout(function() {
                    let hasFilter = false;
                    if ($('#customerFilter').val()) {
                        $('#customerFilter').trigger('change');
                        hasFilter = true;
                    }
                    if ($('#modelFilter').val() && !hasFilter) {
                        $('#modelFilter').trigger('change');
                    }
                    if ($('#customerFilter').hasClass('select2-hidden-accessible')) {
                        $('#customerFilter').trigger('change.select2');
                    }
                    if ($('#modelFilter').hasClass('select2-hidden-accessible')) {
                        $('#modelFilter').trigger('change.select2');
                    }
                    if ($('#poFilter').hasClass('select2-hidden-accessible')) {
                        $('#poFilter').trigger('change.select2');
                    }
                }, 100);
            },
            stateDuration: 60 * 60 * 24, // 24 hours
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
            stripeClasses: ['bg-white dark:bg-gray-800', 'bg-gray-50 dark:bg-gray-750'],
            dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-4"<"w-full md:w-auto"l><"w-full md:w-80"f>>rt<"flex flex-col md:flex-row justify-between items-center mt-6 gap-4"ip>',
            language: {
                search: "",
                searchPlaceholder: "Search Part No, PO No...",
                lengthMenu: "Show _MENU_ entries",
                paginate: {
                    previous: "Prev",
                    next: "Next"
                }
            },
            columns: [
                { 
                    data: 'hashed_id', 
                    name: 'id', 
                    orderable: false, 
                    searchable: false, 
                    className: 'px-4 py-2 text-center align-middle',
                    render: function(data, type, row) {
                        if (row.checksheet) {
                            return `<input type="checkbox" class="part-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4 cursor-pointer" value="${row.hashed_id}">`;
                        }
                        return `<input type="checkbox" disabled class="rounded border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-700 text-gray-300 w-4 h-4 cursor-not-allowed opacity-30" title="QC Label belum tersedia (Part belum selesai QC)">`;
                    }
                },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-2 text-center text-slate-800 dark:text-slate-200 text-[13px] font-medium' },
                { 
                    data: 'product.part_no', 
                    name: 'product.part_no', 
                    className: 'px-4 py-2 align-top', 
                    orderable: false, 
                    searchable: false,
                    render: function(data, type, row) {
                        const partNo = row.product?.part_no || '-';
                        const partName = row.product?.part_name || '-';
                        const modelName = row.product?.vehicle_model?.name || 'Unknown Model';
                        const poNo = row.event?.po_no || 'Unknown PO';
                        const qtyFormatted = Number(row.qty || 0).toLocaleString('id-ID');
                        
                        return `<div class="text-gray-800 dark:text-gray-200 font-bold text-sm flex items-center flex-wrap gap-1">
                                    ${partNo} 
                                    <span class="text-[10px] font-bold text-blue-700 bg-blue-50 dark:bg-blue-900/30 dark:text-blue-300 px-1.5 py-0.5 rounded border border-blue-200 dark:border-blue-700 uppercase">PO: ${poNo}</span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1.5 mt-0.5">${partName}</div>
                                <div class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50 dark:bg-gray-700 px-2 py-0.5 inline-block border border-gray-200 dark:border-gray-600">${modelName}</div>
                                <div class="text-gray-800 dark:text-gray-300 font-black flex items-center gap-1.5 mt-2"><i class="fa-solid fa-boxes-stacked text-gray-400"></i> Initial Target: ${qtyFormatted} <span class="text-xs font-semibold text-gray-500">PCS</span></div>`;
                    }
                },
                { 
                    data: 'status', 
                    name: 'status', 
                    className: 'px-4 py-2 text-center align-middle', 
                    orderable: false, 
                    searchable: false,
                    render: function(data, type, row) {
                        if (['PO_REGISTERED', 'WAITING_DEPT_CONFIRM'].includes(row.status)) {
                            return `<div class="inline-flex flex-col items-center gap-1.5 px-3 py-2 bg-slate-50 border border-slate-200 text-[10px] text-slate-500 italic">
                                <i class="fa-solid fa-industry text-sm"></i> In Production
                            </div>`;
                        }
                        
                        let dateStr = '-';
                        if (row.actual_completion_date) {
                            const d = new Date(row.actual_completion_date.split('T')[0] + 'T00:00:00');
                            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            dateStr = `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]} ${String(d.getFullYear()).slice(-2)}`;
                        }
                        
                        const rowJson = JSON.stringify(row).replace(/'/g, "&apos;").replace(/"/g, "&quot;");
                        return `<div class="flex flex-col items-center gap-1">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 border border-green-200 text-green-700 text-[10px] font-bold"><i class="fa-solid fa-check-double"></i> Production Done</span>
                            <span class="text-[11px] text-gray-500 font-medium">Date: ${dateStr}</span>
                            <button type="button" onclick='openProductionReportModal(${rowJson})' class="mt-1 px-3 py-1 bg-white border border-gray-300 dark:bg-gray-700 dark:border-gray-600 text-[10px] shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition flex items-center gap-1.5 font-bold text-gray-700 dark:text-gray-300">
                                <i class="fa-solid fa-camera text-blue-500"></i> Check Qty Report & Photo
                            </button>
                        </div>`;
                    }
                },
                { 
                    data: 'qc_target_date', 
                    name: 'qc_target_date', 
                    className: 'px-4 py-2 text-center align-middle', 
                    orderable: false, 
                    searchable: false,
                    render: function(data, type, row) {
                        if (['PO_REGISTERED', 'WAITING_DEPT_CONFIRM'].includes(row.status)) {
                            return `<div class="text-[10px] text-gray-400 italic font-medium">Waiting for Parts Registration</div>`;
                        }
                        if (row.status === 'WAITING_QE_CHECK') {
                            const badge = row.has_checksheet ? 
                                `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-100 border border-blue-200 text-blue-800 text-[10px] font-bold shadow-sm"><i class="fa-solid fa-pen-to-square"></i> FILLED & BEING CHECKED</span>` :
                                `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-orange-100 border border-orange-200 text-orange-800 text-[10px] font-bold shadow-sm"><i class="fa-solid fa-triangle-exclamation animate-pulse"></i> NOT YET INPUT BY QC</span>`;
                            
                            let targetStr = '-';
                            if (row.qc_target_date) {
                                const d = new Date(row.qc_target_date.split('T')[0] + 'T00:00:00');
                                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                targetStr = `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]}`;
                            }
                            return `${badge}<div class="mt-2 text-[10px] text-gray-500"><i class="fa-solid fa-calendar-check text-gray-400 mr-1"></i> Target QC: <span class="font-bold text-gray-700">${targetStr}</span></div>`;
                        }
                        return `<div class="text-[10px] text-emerald-600 font-bold bg-emerald-50 border border-emerald-100 px-2 py-1 inline-flex items-center gap-1"><i class="fa-solid fa-shield-halved"></i> PASSED QC</div>`;
                    }
                },
                { 
                    data: 'id', 
                    name: 'id', 
                    className: 'px-4 py-2 text-right align-middle pointer-events-auto', 
                    orderable: false, 
                    searchable: false,
                    render: function(data, type, row) {
                        if (['PO_REGISTERED', 'WAITING_DEPT_CONFIRM'].includes(row.status)) {
                            return `<div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed">
                                <i class="fa-solid fa-lock text-[8px]"></i> Not Yet Registered in QC
                            </div>`;
                        }
                        if (row.status === 'WAITING_QE_CHECK') {
                            return `<a href="${row.create_checksheet_url}" class="inline-flex px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white shadow-sm font-bold transition items-center gap-2 text-[11px]" style="background-color: #f97316;">
                                <i class="fa-regular fa-clipboard"></i> Input Quality (QC)
                            </a>
                            <p class="text-[9px] text-gray-400 mt-2 italic text-right max-w-[150px] mx-auto float-right text-balance">Fill quality parameter form & pass to MGM</p>`;
                        }
                        
                        let rollbackBtn = '';
                        if (row.can_rollback) {
                            const csrf = $('meta[name="csrf-token"]').attr('content') || '';
                            rollbackBtn = `<form action="${row.rollback_qc_url}" method="POST">
                                <input type="hidden" name="_token" value="${csrf}">
                                <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold transition mt-1" onclick="confirmAction(event, 'Are you sure you want to rollback this part from MGM to QC Check stage?')">
                                    <i class="fa-solid fa-rotate-left"></i> Rollback QC
                                </button>
                            </form>`;
                        }
                        
                        let printBtn = '';
                        if (row.has_checksheet) {
                            printBtn = `<a href="${row.print_label_url}" target="_blank" class="inline-flex px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white shadow-sm font-bold transition items-center justify-center gap-2 text-[11px] w-full max-w-[150px]">
                                <i class="fa-solid fa-print"></i> Print QC Label
                            </a>`;
                        }
                        
                        return `<div class="flex flex-col items-end gap-2">
                            <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed w-full max-w-[150px]">
                                <i class="fa-solid fa-lock text-[8px]"></i> Already Inspected
                            </div>
                            ${rollbackBtn}
                            ${printBtn}
                        </div>`;
                    }
                }
            ],
            drawCallback: function() {
                $('.dataTables_paginate').addClass('inline-flex -space-x-px rounded-md shadow-sm');
                $('.dataTables_paginate .paginate_button')
                    .removeClass('paginate_button current disabled')
                    .addClass('relative inline-flex items-center px-4 py-2 text-[13px] font-medium border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:z-20 cursor-pointer first:rounded-l-md last:rounded-r-md');
                $('.dataTables_paginate .active')
                    .removeClass('bg-white text-gray-700 hover:bg-gray-50')
                    .addClass('z-10 bg-gray-100 border-gray-300 text-gray-900 font-bold');
                $('.dataTables_paginate .disabled')
                    .removeClass('hover:bg-gray-50 cursor-pointer text-gray-700')
                    .addClass('opacity-50 cursor-not-allowed text-gray-400');
                    
                $('#qcTable_paginate a').each(function() {
                    $(this).removeClass('paginate_button');
                });
                
                $('.dataTables_filter input')
                    .addClass('!pl-3 !pr-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-md')
                    .css('margin-left', '0');
                $('.dataTables_length select')
                    .addClass('py-2 pl-3 pr-8 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm rounded-md shadow-sm');
            }
        });

        function performSearch() {
            $('#qcTable').DataTable().ajax.reload();
        }

        $('#customerFilter').on('change', function(e) {
            let customerId = $(this).val();
            if ($('#modelFilter').data('select2')) {
                $('#modelFilter').select2('destroy');
            }
            $('#modelFilter option').each(function() {
                if ($(this).val() == '') {
                    $(this).prop('disabled', false);
                    return;
                }
                if (!customerId || $(this).data('customer') == customerId) {
                    $(this).prop('disabled', false).show();
                } else {
                    $(this).prop('disabled', true).hide();
                }
            });
            $('#modelFilter').select2({ width: '100%' });
            
            if ($('#modelFilter option:selected').prop('disabled')) {
                $('#modelFilter').val('').trigger('change.select2');
            }
            
            performSearch();
        });

        $('#modelFilter').on('change', function(e) {
            performSearch();
        });

        $('#poFilter').on('change', function(e) {
            performSearch();
        });

        $('#clearFiltersBtn').on('click', function(e) {
            e.preventDefault();
            $('#modelFilter').val('');
            $('#poFilter').val('');
            $('#customerFilter').val('').trigger('change');
        });
        
        let initialCustomerId = $('#customerFilter').val();
        if (initialCustomerId) {
            if ($('#modelFilter').data('select2')) {
                $('#modelFilter').select2('destroy');
            }
            $('#modelFilter option').each(function() {
                if ($(this).val() == '') return;
                if ($(this).data('customer') == initialCustomerId) {
                    $(this).prop('disabled', false).show();
                } else {
                    $(this).prop('disabled', true).hide();
                }
            });
            $('#modelFilter').select2({ width: '100%' });
        }
    });
</script>
@endpush