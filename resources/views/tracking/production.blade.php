@extends('layouts.app')

@section('title', $pageTitle ?? 'Process Production')
@section('page_title', 'Transaction / ' . ($pageTitle ?? 'Process Production'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-industry' }} text-blue-500"></i> {{ $pageTitle ?? 'Process Production' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    <!-- Filters -->
    <div class="px-6 pt-4 pb-4 flex flex-col xl:flex-row justify-between items-start xl:items-end gap-4">
        <div class="flex flex-wrap items-center gap-4 w-full xl:w-auto">
            <div class="w-full sm:w-auto">
                <select id="customerFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-48 rounded-md shadow-sm">
                    <option value="all">All Customers</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <select id="modelFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-48 rounded-md shadow-sm">
                    <option value="all">All Models</option>
                    @foreach($models ?? [] as $mod)
                        <option value="{{ $mod->id }}" data-customer="{{ $mod->customer_id }}">{{ $mod->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-auto">
                <select id="poFilter" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full sm:w-48 rounded-md shadow-sm">
                    <option value="all">All POs</option>
                    @foreach($poList ?? [] as $po)
                        <option value="{{ $po->po_no }}">{{ $po->po_no }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end w-full sm:w-auto">
                <button id="clearFiltersBtn" type="button" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium transition shadow-sm flex items-center gap-2 w-full justify-center min-w-[100px]">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </div>

        <div class="flex items-end w-full xl:w-[350px] shrink-0">
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </div>
                <input type="text" id="customSearchInput"
                    value="{{ request('search') }}"
                    placeholder="Search Part No, PO No..."
                    style="padding-left: 2.5rem; padding-right: 2.5rem;" class="py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-md">
                <button type="button" id="clearSearchBtn" style="display:none;"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500 transition cursor-pointer z-10">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="p-6 pt-0">
        <div class="border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-sm overflow-hidden">
            <table id="productionTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700 uppercase text-[11px] tracking-wider font-bold">
                    <tr>
                        <th scope="col" class="px-4 py-3 w-16">#</th>
                        <th scope="col" class="px-4 py-3 w-64">WO NO / PART INFO</th>
                        <th scope="col" class="px-4 py-3 text-center w-32">STATUS PO</th>
                        <th scope="col" class="px-4 py-3 text-center">ROUTING EXECUTION OVERVIEW</th>
                        <th scope="col" class="px-4 py-3 text-right w-48">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <!-- DataTables will fill this -->
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal: Production Done --}}
<div id="modal-complete" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-800 shadow-2xl w-full max-w-md mx-4 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 dark:border-gray-700">
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

                <div class="p-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded flex flex-col gap-1.5">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-200" id="modal-part-no"></div>
                            <div class="text-[11px] text-gray-500 font-medium" id="modal-part-name"></div>
                        </div>
                        <div class="text-right">
                            <div class="text-[10px] text-blue-700 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 px-1.5 py-0.5 uppercase font-bold" id="modal-po-no"></div>
                            <div class="text-[10px] text-gray-400 mt-1 uppercase" id="modal-model-name"></div>
                        </div>
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
            <div class="flex justify-end gap-3 px-4 py-2 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                <button type="button" onclick="closeCompleteModal()" class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 text-[13px] font-medium text-white bg-amber-500 hover:bg-amber-600 shadow-sm transition flex items-center gap-1">
                    <i class="fa-solid fa-check"></i> Complete Process
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openCompleteModal(partId, processId, processName, departmentName, targetDate, actionUrl, partQty, partNo, partName, poNo, modelName) {
    document.getElementById('form-complete').action = actionUrl;
    document.getElementById('modal-process-id').value = processId;
    document.getElementById('modal-process-name-title').textContent = processName;
    document.getElementById('modal-process-name').textContent = processName;
    document.getElementById('modal-department-name').textContent = departmentName;
    document.getElementById('modal-target-date').textContent = targetDate;
    
    document.getElementById('modal-part-no').textContent = partNo;
    document.getElementById('modal-part-name').textContent = partName;
    document.getElementById('modal-po-no').textContent = 'PO: ' + poNo;
    document.getElementById('modal-model-name').textContent = modelName;
    
    const qtyInput = document.querySelector('#modal-complete input[name="actual_qty"]');
    qtyInput.min = partQty;
    document.getElementById('modal-qty-helper').innerHTML = 'Minimum matches Planning PO: <b>' + Number(partQty || 0).toLocaleString('id-ID') + ' PCS</b>.';
    
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


$(document).ready(function() {
    let urlParams = new URLSearchParams(window.location.search);
    let table = $('#productionTable').DataTable({
        search: { search: urlParams.get('search') || '' },
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('tracking.production') }}",
            data: function (d) {
                d.customer_filter = $('#customerFilter').val();
                d.model_filter = $('#modelFilter').val();
                d.po_filter = $('#poFilter').val();
            }
        },
        responsive: true,
        stateSave: true,
        stateDuration: 60 * 60 * 24, // 24 hours
        stateSaveParams: function (settings, data) {
            data.customFilters = {
                customer: $('#customerFilter').val(),
                model: $('#modelFilter').val(),
                po: $('#poFilter').val()
            };
        },
        stateLoadParams: function (settings, data) {
            let urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('search')) {
                data.search.search = urlParams.get('search');
            }
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
                if ($('#customerFilter').val()) {
                    let customerId = $('#customerFilter').val();
                    $('#modelFilter option').each(function() {
                        if ($(this).val() == 'all') return;
                        if (!customerId || customerId == 'all' || $(this).data('customer') == customerId) {
                            $(this).prop('disabled', false).show();
                        } else {
                            $(this).prop('disabled', true).hide();
                        }
                    });
                }
                $('#customerFilter').trigger('change.select2');
                $('#modelFilter').trigger('change.select2');
                $('#poFilter').trigger('change.select2');
            }, 100);
        },
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        stripeClasses: ['bg-white dark:bg-slate-800', 'bg-slate-100 dark:bg-slate-800/50'],
        dom: '<"overflow-x-auto"t><"p-4 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-slate-500"ip>',
        language: {
            search: "",
            searchPlaceholder: "Search Part No, PO No...",
            lengthMenu: "Show _MENU_ rows",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            paginate: {
                previous: "Prev",
                next: "Next"
            }
        },
        columns: [
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
                    if (row.status === 'PO_REGISTERED') {
                        return `<div class="inline-flex flex-col items-center gap-1.5 px-3 py-2 bg-slate-50 border border-slate-200 text-[10px] text-slate-500 italic">
                            <i class="fa-solid fa-lock text-sm"></i> Planned
                        </div>`;
                    } else if (row.status === 'WAITING_DEPT_CONFIRM') {
                        return `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-yellow-100 border border-yellow-200 text-yellow-800 text-[10px] font-bold tracking-wide"><i class="fa-solid fa-gears fa-spin"></i> IN PROCESS</span>`;
                    } else {
                        return `<div class="text-[10px] text-gray-400 italic font-medium"><i class="fa-solid fa-check text-green-500"></i> Submitted to QC</div>`;
                    }
                }
            },
            { 
                data: 'processes', 
                name: 'processes', 
                className: 'px-4 py-2', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    const processes = (row.processes || []).slice().sort((a, b) => (a.sequence_order || 0) - (b.sequence_order || 0));
                    if (processes.length === 0) {
                        return `<span class="text-xs text-orange-500 italic flex items-center gap-1">
                            <i class="fa-solid fa-triangle-exclamation"></i> No Routing Yet
                        </span>`;
                    }
                    
                    const activeProcess = processes.find(p => p.status === 'WAITING');
                    
                    let html = `<div class="flex flex-col gap-2 relative before:absolute before:inset-0 before:ml-[9px] before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">`;
                    
                    processes.forEach(p => {
                        const isFinished = p.status === 'FINISHED';
                        const isActive = activeProcess && activeProcess.id === p.id;
                        
                        let circleColor = 'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400';
                        let icon = p.sequence_order || '';
                        let textColor = 'text-gray-400';
                        
                        if (isFinished) {
                            circleColor = 'bg-green-500 text-white ring-4 ring-white dark:ring-gray-800';
                            icon = '<i class="fa-solid fa-check text-[8px]"></i>';
                            textColor = 'text-gray-400 line-through';
                        } else if (isActive) {
                            circleColor = 'bg-amber-500 text-white ring-4 ring-amber-100 dark:ring-amber-900 shadow-lg';
                            icon = '<i class="fa-solid fa-gear fa-spin text-[8px]"></i>';
                            textColor = 'text-gray-900 dark:text-white font-black';
                        }
                        
                        const procName = p.process?.process_name || 'Unknown Process';
                        const deptName = p.department?.name || 'Unknown Department';
                        let targetStr = '-';
                        if (p.target_completion_date) {
                            const d = new Date(p.target_completion_date.split('T')[0] + 'T00:00:00');
                            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            targetStr = `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]}`;
                        }
                        
                        html += `<div class="relative flex items-center gap-3">
                            <div class="relative z-10 w-5 h-5 flex items-center justify-center font-bold text-[9px] ${circleColor} transition-colors">
                                ${icon}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[11px] font-bold ${textColor} transition-colors">${procName}</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-[9px] text-gray-500 ${isFinished ? 'opacity-50' : ''}"><i class="fa-solid fa-building-user text-[8px] mr-0.5"></i> ${deptName}</span>
                                    <span class="text-[9px] text-gray-500 ${isFinished ? 'opacity-50' : ''}"><i class="fa-regular fa-calendar-check text-[8px] mr-0.5"></i> Target: ${targetStr}</span>
                                </div>
                            </div>
                        </div>`;
                    });
                    
                    html += `</div>`;
                    return html;
                }
            },
            { 
                data: 'id', 
                name: 'id', 
                className: 'px-4 py-2 text-right align-middle pointer-events-auto', 
                orderable: false, 
                searchable: false,
                render: function(data, type, row) {
                    if (row.status === 'PO_REGISTERED') {
                        return `<div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed">
                            <i class="fa-solid fa-lock text-[8px]"></i> Not yet send to production
                        </div>`;
                    }
                    
                    const processes = (row.processes || []).slice().sort((a, b) => (a.sequence_order || 0) - (b.sequence_order || 0));
                    const waitingProcesses = processes.filter(p => p.status === 'WAITING');
                    const finishedProcesses = processes.filter(p => p.status === 'FINISHED');
                    const activeProcess = waitingProcesses[0];
                    
                    if (row.status === 'WAITING_DEPT_CONFIRM') {
                        if (!activeProcess) return '';
                        
                        const isLast = waitingProcesses.length === 1;
                        const hasFinishedProcess = finishedProcesses.length > 0;
                        const procName = (activeProcess.process?.process_name || 'Process').replace(/'/g, "\\'");
                        const deptName = (activeProcess.department?.name || '').replace(/'/g, "\\'");
                        let targetFormatted = '-';
                        if (activeProcess.target_completion_date) {
                            const d = new Date(activeProcess.target_completion_date.split('T')[0] + 'T00:00:00');
                            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                            targetFormatted = `${String(d.getDate()).padStart(2, '0')} ${months[d.getMonth()]} ${d.getFullYear()}`;
                        }
                        const completeUrl = row.complete_process_url || '';
                        
                        const partNo = (row.product?.part_no || 'Unknown Part').replace(/'/g, "\\'");
                        const partName = (row.product?.part_name || '').replace(/'/g, "\\'");
                        const poNo = (row.event?.po_no || 'Unknown PO').replace(/'/g, "\\'");
                        const modelName = (row.product?.vehicle_model?.name || 'Unknown Model').replace(/'/g, "\\'");
                        
                        let rollbackBtn = '';
                        if (hasFinishedProcess) {
                            const csrf = $('meta[name="csrf-token"]').attr('content') || '';
                            rollbackBtn = `<form action="${row.rollback_process_url}" method="POST">
                                <input type="hidden" name="_token" value="${csrf}">
                                <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 flex items-center justify-end w-full gap-1 font-semibold transition mb-2" onclick="confirmAction(event, 'Are you sure you want to rollback the previous process?')">
                                    <i class="fa-solid fa-rotate-left"></i> Rollback Previous Process
                                </button>
                            </form>`;
                        }
                        
                        return `<button type="button"
                            onclick="openCompleteModal('${row.hashed_id}', '${activeProcess.hashed_id || activeProcess.id}', '${procName}', '${deptName}', '${targetFormatted}', '${completeUrl}', ${row.qty}, '${partNo}', '${partName}', '${poNo}', '${modelName}')"
                            class="inline-flex px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white shadow-sm font-bold transition items-center gap-2 text-[11px] mb-2 w-full justify-center" style="background-color: #f59e0b;">
                            Complete ${procName} <i class="fa-solid fa-forward-step"></i>
                        </button>
                        ${rollbackBtn}
                        <p class="text-[9px] text-gray-400 italic text-right max-w-[150px] mx-auto float-right text-balance mt-1">
                            ${isLast ? 'Click if completed to submit to QC.' : 'Click to move to the next department.'}
                        </p>`;
                    }
                    
                    if (row.status === 'WAITING_QE_CHECK') {
                        const canRollback = !row.checksheet || !row.checksheet.qe_checked_by;
                        let rollbackBtn = '';
                        if (canRollback) {
                            const csrf = $('meta[name="csrf-token"]').attr('content') || '';
                            rollbackBtn = `<form action="${row.rollback_process_url}" method="POST">
                                <input type="hidden" name="_token" value="${csrf}">
                                <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold transition mt-1" onclick="confirmAction(event, 'Are you sure you want to rollback this part from QC to Production stage?')">
                                    <i class="fa-solid fa-rotate-left"></i> Rollback Production
                                </button>
                            </form>`;
                        }
                        return `<div class="flex flex-col items-end gap-2">
                            <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed w-full">
                                <i class="fa-solid fa-check-double text-[8px] text-green-500"></i> Submitted to QC
                            </div>
                            ${rollbackBtn}
                        </div>`;
                    }
                    
                    return `<div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed">
                        <i class="fa-solid fa-check-double text-[8px] text-green-500"></i> Completed
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
                
            $('#productionTable_paginate a').each(function() {
                $(this).removeClass('paginate_button');
            });
            
            $('.dataTables_filter input')
                .addClass('!pl-3 !pr-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-md')
                .css('margin-left', '0');
            $('.dataTables_length select')
                .addClass('py-2 pl-3 pr-8 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm rounded-md shadow-sm');
            
            let urlParams = new URLSearchParams(window.location.search);
            let openPartId = urlParams.get('open_part');
            
            if (openPartId && !window.modalAlreadyOpened) {
                setTimeout(() => {
                    let btn = $(`button[onclick*="'${openPartId}'"]`);
                    if (btn.length > 0) {
                        window.modalAlreadyOpened = true;
                        let url = new URL(window.location);
                        url.searchParams.delete('open_part');
                        window.history.replaceState({}, document.title, url);
                        btn.click();
                    }
                }, 200);
            }
        }
    });

    $('#customerFilter').on('change', function(e) {
        let customerId = $(this).val();
        
        if ($('#modelFilter').data('select2')) {
            $('#modelFilter').select2('destroy');
        }

        $('#modelFilter option').each(function() {
            if ($(this).val() == 'all') {
                $(this).prop('disabled', false);
                return;
            }
            if (!customerId || customerId == 'all' || $(this).data('customer') == customerId) {
                $(this).prop('disabled', false).show();
            } else {
                $(this).prop('disabled', true).hide();
            }
        });

        $('#modelFilter').select2({ width: '100%' });
        
        if ($('#modelFilter option:selected').prop('disabled')) {
            $('#modelFilter').val('all').trigger('change.select2');
        }
        
        table.ajax.reload();
    });

    $('#modelFilter').on('change', function(e) {
        table.ajax.reload();
    });

    $('#poFilter').on('change', function(e) {
        table.ajax.reload();
    });

    // Link Custom Search Bar to DataTables
    let debounceTimer;
    $('#customSearchInput').on('input', function() {
        if ($(this).val().length > 0) {
            $('#clearSearchBtn').show();
        } else {
            $('#clearSearchBtn').hide();
        }
        clearTimeout(debounceTimer);
        let val = this.value;
        debounceTimer = setTimeout(function() {
            table.search(val).draw();
        }, 500);
    });

    if ($('#customSearchInput').val() && $('#customSearchInput').val().length > 0) {
        $('#clearSearchBtn').show();
    }

    $('#clearSearchBtn').on('click', function(e) {
        e.preventDefault();
        $('#customSearchInput').val('');
        $(this).hide();
        table.search('').draw();
        $('#customSearchInput').focus();
    });

    $('#clearFiltersBtn').on('click', function(e) {
        e.preventDefault();
        $('#customSearchInput').val('');
        $('#clearSearchBtn').hide();
        
        $('#modelFilter').val('all').trigger('change.select2');
        $('#poFilter').val('all').trigger('change.select2');
        $('#customerFilter').val('all').trigger('change.select2');
        
        table.search('').draw();
    });
});
</script>
@endpush
