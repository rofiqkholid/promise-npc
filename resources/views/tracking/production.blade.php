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
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="productionTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-64">Part Info / PO</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center w-32">Status PO</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">Routing Execution Overview</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-48">Action Production</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- DataTables will fill this -->
                </tbody>
            </table>
        </div>
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

$(document).ready(function() {
    $('#productionTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('tracking.production') }}",
        responsive: true,
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        stripeClasses: ['bg-white dark:bg-gray-800', 'bg-gray-50 dark:bg-gray-750'], // Native zebra striping
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
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-6 py-4 text-center text-slate-800 dark:text-slate-200 text-sm font-medium' },
            { data: 'part_info', name: 'product.part_no', className: 'px-6 py-4 align-top', orderable: false, searchable: false },
            { data: 'status_po', name: 'status', className: 'px-6 py-4 text-center align-middle', orderable: false, searchable: false },
            { data: 'routing_execution', name: 'routing_execution', className: 'px-6 py-4', orderable: false, searchable: false },
            { data: 'action_production', name: 'action', className: 'px-6 py-4 text-right align-middle pointer-events-auto', orderable: false, searchable: false }
        ],
        drawCallback: function() {
            // Style pagination buttons exactly like Activity Logs
            $('.dataTables_paginate').addClass('inline-flex -space-x-px rounded-md shadow-sm');
            $('.dataTables_paginate .paginate_button')
                .removeClass('paginate_button current disabled')
                .addClass('relative inline-flex items-center px-4 py-2 text-sm font-medium border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:z-20 cursor-pointer first:rounded-l-md last:rounded-r-md');
            $('.dataTables_paginate .active')
                .removeClass('bg-white text-gray-700 hover:bg-gray-50')
                .addClass('z-10 bg-gray-100 border-gray-300 text-gray-900 font-bold');
            $('.dataTables_paginate .disabled')
                .removeClass('hover:bg-gray-50 cursor-pointer text-gray-700')
                .addClass('opacity-50 cursor-not-allowed text-gray-400');
                
            // Fix classes applied by DataTables dynamically
            $('#productionTable_paginate a').each(function() {
                $(this).removeClass('paginate_button');
            });
            
            // Style DataTables Input Search
            $('.dataTables_filter input')
                .addClass('!pl-3 !pr-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-md')
                .css('margin-left', '0');
            $('.dataTables_length select')
                .addClass('py-2 pl-3 pr-8 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm rounded-md shadow-sm');
        }
    });
});
</script>
@endpush

