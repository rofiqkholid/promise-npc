@extends('layouts.app')

@section('title', 'Routing Part: ' . optional($part->product)->part_no)
@section('page_title', 'Production Tracking / Routing')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
        <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-route text-blue-500"></i> Setup Routing & Process Schedule
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

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md">
                    <div class="flex items-center gap-2 text-red-600 dark:text-red-400 font-semibold mb-2">
                        <i class="fa-solid fa-triangle-exclamation"></i> Validation Error
                    </div>
                    <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="routing-form" action="{{ route('parts.routing.update', $part->hashed_id) }}" method="POST">
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
                            <input type="date" name="qc_target_date" value="{{ $part->qc_target_date }}" max="{{ \Carbon\Carbon::parse($part->delivery_date)->format('Y-m-d') }}"
                                class="w-full border-emerald-200 dark:border-emerald-800 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                            <p class="text-[10px] text-emerald-600/70 dark:text-emerald-400/70 italic mt-1 leading-tight">The schedule for the part to be checked and inputted by the Quality team.</p>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">
                                Target Management Check (MGM)
                            </label>
                            <input type="date" name="mgm_target_date" value="{{ $part->mgm_target_date }}" max="{{ \Carbon\Carbon::parse($part->delivery_date)->format('Y-m-d') }}"
                                class="w-full border-emerald-200 dark:border-emerald-800 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                            <p class="text-[10px] text-emerald-600/70 dark:text-emerald-400/70 italic mt-1 leading-tight">The schedule for the part to be validated and approved by the Management team.</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('tracking.setup') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 shadow-sm text-[13px] font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-md shadow-blue-500/20 text-[13px] font-medium hover:from-blue-700 hover:to-cyan-700 transition">
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
            updateDateConstraints();
        }

        function updateDateConstraints() {
            const rows = container.querySelectorAll('tr');
            let minDate = null;
            
            rows.forEach((row) => {
                const dateInput = row.querySelector('input[type="date"]');
                if (dateInput) {
                    if (minDate) {
                        dateInput.setAttribute('min', minDate);
                    } else {
                        dateInput.removeAttribute('min');
                    }
                    
                    if (dateInput.value) {
                        minDate = dateInput.value;
                    } else if (dateInput.getAttribute('min')) {
                        minDate = dateInput.getAttribute('min');
                    }
                }
            });

            // Update QC & MGM constraints
            const qcDateInput = document.querySelector('input[name="qc_target_date"]');
            const mgmDateInput = document.querySelector('input[name="mgm_target_date"]');

            if (qcDateInput) {
                if (minDate) {
                    qcDateInput.setAttribute('min', minDate);
                } else {
                    qcDateInput.removeAttribute('min');
                }
            }

            if (mgmDateInput) {
                let mgmMinDate = minDate;
                if (qcDateInput && qcDateInput.value) {
                    mgmMinDate = qcDateInput.value;
                } else if (qcDateInput && qcDateInput.getAttribute('min')) {
                    mgmMinDate = qcDateInput.getAttribute('min');
                }

                if (mgmMinDate) {
                    mgmDateInput.setAttribute('min', mgmMinDate);
                } else {
                    mgmDateInput.removeAttribute('min');
                }
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
                    <input type="date" name="routing[${processIndex}][target_completion_date]" required max="{{ \Carbon\Carbon::parse($part->delivery_date)->format('Y-m-d') }}" value="${data && data.target_completion_date ? data.target_completion_date : ''}" class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
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

        // Attach event listener to update constraints when a date is changed
        container.addEventListener('change', function(e) {
            if (e.target && e.target.type === 'date') {
                updateDateConstraints();
            }
        });

        const qcDateInput = document.querySelector('input[name="qc_target_date"]');
        if (qcDateInput) {
            qcDateInput.addEventListener('change', updateDateConstraints);
        }

        // Initialize constraints on load
        updateDateConstraints();

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

        document.getElementById('routing-form').addEventListener('submit', function(e) {
            const rows = container.querySelectorAll('tr');
            let previousDate = null;
            let previousProcessName = null;
            
            for(let i=0; i<rows.length; i++) {
                const row = rows[i];
                const dateInput = row.querySelector('input[type="date"]');
                const processName = row.querySelector('td:nth-child(2) span').textContent.trim();
                
                if(!dateInput || !dateInput.value) continue;
                
                if(previousDate && dateInput.value < previousDate) {
                    e.preventDefault();
                    alert(`Invalid date sequence!\nThe target date for process '${processName}' cannot be earlier than the previous process '${previousProcessName}'.`);
                    dateInput.focus();
                    return false;
                }
                previousDate = dateInput.value;
                previousProcessName = processName;
            }

            const qcDateInput = document.querySelector('input[name="qc_target_date"]');
            const mgmDateInput = document.querySelector('input[name="mgm_target_date"]');

            if (qcDateInput && qcDateInput.value && previousDate && qcDateInput.value < previousDate) {
                e.preventDefault();
                alert(`Invalid date sequence!\nThe target date for Quality Check (QE) cannot be earlier than the last process '${previousProcessName}'.`);
                qcDateInput.focus();
                return false;
            }

            let qcMinDate = previousDate;
            if (qcDateInput && qcDateInput.value) {
                qcMinDate = qcDateInput.value;
            }

            if (mgmDateInput && mgmDateInput.value && qcMinDate && mgmDateInput.value < qcMinDate) {
                e.preventDefault();
                let compareName = (qcDateInput && qcDateInput.value) ? 'Quality Check (QE)' : (previousProcessName ? `the last process '${previousProcessName}'` : 'the previous process');
                alert(`Invalid date sequence!\nThe target date for Management Check (MGM) cannot be earlier than ${compareName}.`);
                mgmDateInput.focus();
                return false;
            }
        });
    });
</script>
@endpush
