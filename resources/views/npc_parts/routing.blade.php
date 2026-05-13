@extends('layouts.app')

@section('title', 'Routing Part: ' . optional($part->product)->part_no)
@section('page_title', 'Production Tracking / Routing')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-route text-blue-500"></i> Setup Routing & Jadwal Process
            </h2>
            <div class="text-sm">
                <span class="text-gray-500 dark:text-gray-400">Target Delivery:</span>
                <span class="font-bold text-gray-800 dark:text-white">{{ \Carbon\Carbon::parse($part->delivery_date)->format('d M Y') }}</span>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 dark:bg-gray-800/50 p-4 border border-slate-200 dark:border-gray-700 mb-6">
                <div>
                    <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Event/Project</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ optional(optional($part->event)->customerCategory)->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">PO No</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ optional($part->event)->po_no }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Part No</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ optional($part->product)->part_no }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Quantity</span>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $part->qty }}</span>
                </div>
            </div>

            <form action="{{ route('parts.routing.update', $part->hashed_id) }}" method="POST">
                @csrf
                
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-md font-semibold text-gray-700 dark:text-gray-200">Process Sequence Register</h3>
                </div>

                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left w-12 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name Process</th>
                                <th scope="col" class="px-4 py-3 text-left w-40 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Department</th>
                                <th scope="col" class="px-4 py-3 text-left w-48 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Completion Target</th>
                            </tr>
                        </thead>
                        <tbody id="routing-container" class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            <!-- Rows will be added here -->
                        </tbody>
                    </table>
                    <div id="empty-state" class="text-center py-8 text-sm text-gray-500 dark:text-gray-400 hidden">
                        No process has been added yet.
                    </div>
                </div>

                <!-- QC & MGM Schedule Section -->
                <div class="mb-6 p-5 bg-emerald-50/30 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30">
                    <h3 class="text-md font-semibold text-emerald-800 dark:text-emerald-200 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-emerald-500"></i> Quality & Management Schedule
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">
                                Target Quality Check (QE)
                            </label>
                            <input type="date" name="qc_target_date" value="{{ $part->qc_target_date }}"
                                class="w-full border-emerald-200 dark:border-emerald-800 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                            <p class="text-[10px] text-emerald-600/70 dark:text-emerald-400/70 italic mt-1 leading-tight">Jadwal part mulai dicek dan diinput oleh tim Quality.</p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">
                                Target Management Check (MGM)
                            </label>
                            <input type="date" name="mgm_target_date" value="{{ $part->mgm_target_date }}"
                                class="w-full border-emerald-200 dark:border-emerald-800 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                            <p class="text-[10px] text-emerald-600/70 dark:text-emerald-400/70 italic mt-1 leading-tight">Jadwal part mulai divalidasi dan diapprove oleh tim Management.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('tracking.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-md shadow-blue-500/20 text-sm font-medium hover:from-blue-700 hover:to-cyan-700 transition">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Save Routing
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('routing-container');
        const emptyState = document.getElementById('empty-state');
        
        let processIndex = 0;
        
        // Data for dropdowns
        const masterProcesses = @json($masterProcesses);
        const masterDepartments = @json($departments);
        const existingData = @json($part->processes);

        function updateRowNumbers() {
            const rows = container.querySelectorAll('tr');
            rows.forEach((row, index) => {
                row.querySelector('.row-number').textContent = index + 1;
                // Update hidden sequence input
                row.querySelector('.sequence-input').value = index + 1;
            });
            if(rows.length === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
            }
        }

        function createRow(data = null) {
            const row = document.createElement('tr');
            row.className = 'group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition';
            
            const processName = data ? (data.process_name || '') : '';
            const departmentId = data ? data.department_id : '';
            const departmentName = data ? (data.department_name || '') : '';

            row.innerHTML = `
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-500 dark:text-gray-400">
                    <span class="row-number cursor-move text-gray-400 hover:text-gray-600"><i class="fa-solid fa-grip-vertical mr-2"></i></span>
                    <input type="hidden" name="routing[${processIndex}][sequence_order]" class="sequence-input" value="">
                </td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">${processName}</span>
                    <input type="hidden" name="routing[${processIndex}][process_name]" value="${processName}">
                </td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 shadow-sm">
                        ${departmentName}
                    </span>
                    <input type="hidden" name="routing[${processIndex}][department_id]" value="${departmentId}">
                </td>
                <td class="px-4 py-2 whitespace-nowrap">
                    <input type="date" name="routing[${processIndex}][target_completion_date]" required value="${data && data.target_completion_date ? data.target_completion_date : ''}" class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                </td>
            `;

            container.appendChild(row);
            
            processIndex++;
            updateRowNumbers();
        }

        // Load existing
        if(existingData && existingData.length > 0) {
            existingData.forEach(d => createRow(d));
        } else {
            // If data is really empty, we just show empty state
            emptyState.classList.remove('hidden');
        }

        if (typeof Sortable !== 'undefined') {
            new Sortable(container, {
                handle: '.cursor-move',
                animation: 150,
                ghostClass: 'bg-gray-100',
                onEnd: function() {
                    updateRowNumbers();
                }
            });
        }
    });
</script>
@endpush
