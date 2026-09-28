@extends('layouts.app')

@section('title', 'Input Quality Checksheet')
@section('page_title', 'Checksheet Production / ' . (optional($part->event)->po_no ?? 'Part Has Been Deleted'))

@section('content')
@php
    $canApprove = auth()->check() && auth()->user()->canApproveChecksheetStage($checksheet->approval_status);
    $readonly = !$canApprove;
@endphp

<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 max-w-5xl mx-auto">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                <i class="fa-solid fa-clipboard-check text-blue-500 mr-2"></i> PART EVENT DELIVERY CHECKSHEET
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                <strong>Part No:</strong> {{ optional($part->product)->part_no ?? 'N/A' }} | <strong>Customer:</strong> {{ optional(optional(optional($part->event)->customerCategory)->customer)->code ?? 'N/A' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('checksheets.preview', $checksheet->hashed_id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow-sm transition">
                <i class="fa-solid fa-print"></i> Preview Report
            </a>
            <a href="{{ route('checksheets.export', $checksheet->hashed_id) }}" class="inline-flex items-center gap-2 px-4 py-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold shadow-sm transition">
                <i class="fa-regular fa-file-excel"></i> Export Excel
            </a>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-800 text-sm font-semibold shadow-sm border border-blue-200">
                <i class="fa-solid fa-user-shield"></i> Approval Review Mode
            </span>
        </div>
    </div>

    @if($checksheet->reject_reason)
    @php
        $levelMap = [
            'WAITING_QE_STAFF'   => 'QE Staff',
            'WAITING_MGM_STAFF'  => 'NPC Staff',
            'WAITING_QE_SPV'     => 'QE SPV',
            'WAITING_MGM_SPV'    => 'NPC SPV',
            'WAITING_QE_ASSMAN'  => 'QE Asst Mgr',
            'WAITING_MGM_ASSMAN' => 'NPC Asst Mgr',
            'WAITING_QE_MGR'     => 'QE Mgr',
            'WAITING_MGM_MGR'    => 'NPC Mgr',
            'APPROVED'           => 'Fully Approved'
        ];
        $fromStageName = $levelMap[$checksheet->rejected_from_stage] ?? str_replace('WAITING_', '', $checksheet->rejected_from_stage ?? '');
    @endphp
    @if($checksheet->resubmitted_at)
    <!-- Telah Diperbaiki / Disesuaikan Banner -->
    <div class="m-4 p-4 bg-emerald-50 dark:bg-emerald-950/40 border-l-4 border-emerald-500 rounded-r shadow-sm">
        <div class="flex items-start gap-3">
            <div class="text-emerald-500 text-xl font-bold mt-0.5"><i class="fa-solid fa-circle-check"></i></div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider flex items-center gap-2">
                    Checksheet Revised & Resubmitted
                    <span class="px-2 py-0.5 text-[11px] bg-emerald-200 dark:bg-emerald-800/60 text-emerald-900 dark:text-emerald-100 font-bold rounded">Resubmitted to Approver</span>
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Resubmitted by: <strong>{{ optional($checksheet->resubmittedBy)->name ?? 'User' }}</strong>
                    on {{ \Carbon\Carbon::parse($checksheet->resubmitted_at)->format('d M Y, H:i') }}
                </p>
            </div>
        </div>
    </div>
    @else
    <!-- Checksheet Previously Rejected Banner -->
    <div class="m-4 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-r shadow-sm">
        <div class="flex items-start gap-3">
            <div class="text-red-500 text-xl font-bold mt-0.5"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-red-800 dark:text-red-300 uppercase tracking-wider flex items-center gap-2">
                    Checksheet Previously Rejected (Perlu Perbaikan)
                    @if($fromStageName)
                        <span class="px-2 py-0.5 text-[11px] bg-red-200 dark:bg-red-800/60 text-red-900 dark:text-red-100 font-bold rounded">Returned from {{ $fromStageName }}</span>
                    @endif
                </h4>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Rejected by: <strong>{{ optional($checksheet->rejectedBy)->name ?? 'Approver' }}</strong>
                    @if($checksheet->rejected_at) on {{ \Carbon\Carbon::parse($checksheet->rejected_at)->format('d M Y, H:i') }} @endif
                </p>
                <div class="mt-2 text-sm text-red-800 dark:text-red-200 font-medium bg-white dark:bg-gray-800 p-3 rounded border border-red-200 dark:border-red-800/50 shadow-2xs">
                    <strong>Reason for Rejection:</strong> {{ $checksheet->reject_reason }}
                </div>
                @if($checksheet->reject_photo_path)
                <div class="mt-3 pt-3 border-t border-red-200 dark:border-red-800/50">
                    <span class="block text-xs font-bold text-red-800 dark:text-red-300 mb-1.5"><i class="fa-solid fa-camera text-red-500 mr-1"></i> Attached Rejection Photo:</span>
                    <a href="{{ url('file/storage/' . ltrim(str_replace('public/', '', $checksheet->reject_photo_path), '/')) }}" target="_blank" class="inline-block group relative rounded overflow-hidden border border-red-300 dark:border-red-700 shadow-sm hover:shadow-md transition">
                        <img src="{{ url('file/storage/' . ltrim(str_replace('public/', '', $checksheet->reject_photo_path), '/')) }}" alt="Reject Photo" class="h-32 w-auto object-cover rounded">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-bold gap-1">
                            <i class="fa-solid fa-magnifying-glass-plus"></i> View Full Image
                        </div>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
    @endif

    <!-- Part Context Info -->
    <div class="px-4 py-2 grid grid-cols-2 md:grid-cols-5 gap-4 bg-slate-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Event/Project</span>
            <span class="text-sm font-medium text-gray-700 dark:text-white">{{ optional(optional($part->event)->customerCategory)->name ?? 'N/A' }}</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">ECN / Revision</span>
            <span class="text-sm font-bold {{ $part->has_ecn_update ? 'text-red-600' : 'text-gray-700 dark:text-white' }}">
                @if($part->drawingRevision)
                    {{ $part->drawingRevision->ecn_no ?? 'No ECN' }} (Rev {{ $part->drawingRevision->revision_no }})
                    @if($part->has_ecn_update)
                        <i class="fa-solid fa-triangle-exclamation" title="A newer revision exists in Master Data!"></i>
                    @endif
                @else
                    N/A
                @endif
            </span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Qty Output</span>
            <span class="text-sm font-bold text-gray-700 dark:text-white">{{ $part->qty }} PCS</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Target Delivery</span>
            <span class="text-sm font-medium text-gray-700 dark:text-white">{{ \Carbon\Carbon::parse($part->delivery_date)->format('d M Y') }}</span>
        </div>
        <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Production Done</span>
            <span class="text-sm font-medium text-gray-700 dark:text-white">
                @if($part->processes->count() > 0)
                    {{ \Carbon\Carbon::parse($part->processes->last()->actual_completion_date ?? \Carbon\Carbon::now())->format('d M Y') }}
                @else
                    N/A
                @endif
            </span>
        </div>
    </div>

    <form id="approval-form" action="{{ route('checksheet-approvals.store', $checksheet->hashed_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="previous_url" value="{{ base64_encode($previousUrl ?? route('checksheet-approvals.index')) }}">
        <input type="hidden" name="details_json" id="details_json">
        @php
            $levelMap = [
                'WAITING_QE_STAFF'   => 'QE Staff',
                'WAITING_MGM_STAFF'  => 'NPC Staff',
                'WAITING_QE_SPV'     => 'QE SPV',
                'WAITING_MGM_SPV'    => 'NPC SPV',
                'WAITING_QE_ASSMAN'  => 'QE Asst Mgr',
                'WAITING_MGM_ASSMAN' => 'NPC Asst Mgr',
                'WAITING_QE_MGR'     => 'QE Mgr',
                'WAITING_MGM_MGR'    => 'NPC Mgr',
                'APPROVED'           => 'Fully Approved'
            ];
            $levelName = $levelMap[$checksheet->approval_status] ?? str_replace('WAITING_', '', $checksheet->approval_status);
        @endphp

        @if ($errors->any())
            <div class="px-4 py-2 mx-6 mt-4 bg-red-50 border border-red-200 text-red-600 text-[13px]">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="p-6">

            <!--=============================
                   MGM CHECKLIST FORM 
            ==============================-->
            <div class="mb-6">


                <div class="mb-4">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">History Problem</h3>
                    <p class="text-xs text-gray-500 mt-1">List of problems previously found on this Product / Part Number in the past.</p>
                </div>

                <div class="mb-6 p-4 border border-red-200 bg-red-50 dark:bg-red-900/10 dark:border-red-800/50">
                    <!-- Past History (Read-only) -->
                    <ul class="list-disc pl-5 space-y-1 mb-4 text-sm text-gray-700 dark:text-gray-300">
                        @forelse(optional($part->product)->historyProblems ?? [] as $history)
                            <li class="font-medium text-red-700 dark:text-red-400">
                                {{ $history->problem_description }}
                                <span class="text-xs text-gray-500 dark:text-gray-500 ml-2 font-normal italic">
                                    (Found on {{ $history->created_at->format('d M Y') }})
                                </span>
                            </li>
                        @empty
                            <li class="text-gray-500 italic text-sm">No defect history yet (History Problem) for this part.</li>
                        @endforelse
                    </ul>

                    <!-- New History Input hidden in approval -->
                </div>

                <div class="mb-4 mt-8">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Form Validation Management (24 Point)</h3>
                    <p class="text-xs text-gray-500 mt-1">Only shows points mapped to this part during PO registration.</p>
                </div>

                @php
                    $checkCount = max(1, min($part->qty, 12));
                @endphp
                <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-100 dark:bg-gray-700/80 text-gray-700 dark:text-gray-300 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-4 py-3 border-r dark:border-gray-600 w-12 text-center">No</th>
                                <th class="px-4 py-3 border-r dark:border-gray-600">Check Point</th>
                                <th class="px-4 py-3 border-r dark:border-gray-600 w-48">Standard Parameter</th>
                                @for($i = 1; $i <= $checkCount; $i++)
                                <th class="px-2 py-3 border-r dark:border-gray-600 text-center w-12">{{ $i }}</th>
                                @endfor
                                <th class="px-4 py-3 text-center w-32">Result</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($checksheet->details as $index => $detail)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 border-r dark:border-gray-700 text-center text-gray-500">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 border-r dark:border-gray-700 font-medium text-gray-800 dark:text-gray-200 whitespace-normal min-w-[200px]">
                                    {{ $detail->point_check }}
                                </td>
                                <td class="px-4 py-3 border-r dark:border-gray-700 text-gray-600 dark:text-gray-400 whitespace-normal">
                                    {{ $detail->standard ?? '-' }}
                                </td>
                                @for($i = 1; $i <= $checkCount; $i++)
                                @php
                                    $sampleValue = $detail->samples[$i] ?? '';
                                @endphp
                                <td class="px-2 py-2 border-r dark:border-gray-700 text-center cursor-pointer sample-cell select-none" data-detail-id="{{ $detail->id }}" data-sample-index="{{ $i }}">
                                    <input type="hidden" data-detail-id="{{ $detail->id }}" data-sample-index="{{ $i }}" value="{{ $sampleValue }}" class="sample-input-{{ $detail->id }}">
                                    <div class="flex items-center justify-center h-8 w-8 mx-auto transition hover:bg-gray-200 dark:hover:bg-gray-600 icon-container">
                                        @if($sampleValue === 'OK')
                                            <i class="fa-solid fa-circle text-green-500 text-lg"></i>
                                        @elseif($sampleValue === 'NG')
                                            <i class="fa-solid fa-xmark text-red-500 text-xl"></i>
                                        @else
                                            <i class="fa-solid fa-minus text-gray-300 dark:text-gray-600"></i>
                                        @endif
                                    </div>
                                </td>
                                @endfor
                                <td class="px-4 py-2 text-center">
                                    <select data-detail-id="{{ $detail->id }}" id="row-result-{{ $detail->id }}" {{ $readonly ? 'disabled' : '' }}
                                            class="w-full text-xs py-1.5 px-2 font-bold border-gray-300 dark:border-gray-600 shadow-sm focus:ring-1 focus:ring-blue-500 bg-gray-100 dark:bg-gray-800 dark:text-white @if($detail->row_result == 'OK') text-green-600 bg-green-50 dark:bg-green-900/20 @elseif($detail->row_result == 'NG') text-red-600 bg-red-50 dark:bg-red-900/20 @endif">
                                        <option value="" class="text-gray-400">- Select -</option>
                                        <option value="OK" class="text-green-600 font-bold" {{ $detail->row_result === 'OK' ? 'selected' : '' }}>OK</option>
                                        <option value="NG" class="text-red-600 font-bold" {{ $detail->row_result === 'NG' ? 'selected' : '' }}>NG</option>
                                    </select>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ 4 + $checkCount ?? 5 }}" class="px-4 py-6 text-center text-gray-500 italic">
                                    No check points mapped to this part.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-purple-50 dark:bg-purple-900/10 p-4 border border-purple-100 dark:border-purple-800/30">
                    <div class="flex items-start gap-4 w-full">
                        <label class="font-bold text-gray-800 dark:text-white text-base whitespace-nowrap mt-2">Remark:</label>
                        <textarea name="final_result" rows="2" {{ $readonly ? 'disabled' : '' }}
                                class="border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-base py-2 px-3 w-full text-gray-800 bg-gray-100 dark:bg-gray-800 dark:text-white dark:border-gray-600">{{ $checksheet->final_result }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
            <a href="{{ route('checksheet-approvals.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm text-[13px] font-medium">
                Cancel
            </a>
            @if($canApprove)
                <button type="submit" name="action" value="save" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-sm font-semibold flex items-center gap-2 text-sm" onclick="confirmAction(event, 'Are you sure you want to save changes? The checksheet will stay at the current approval level.');">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
                <button type="button" name="action" value="reject" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white transition shadow-sm font-semibold flex items-center gap-2 text-sm" onclick="confirmRejectAction(event);">
                    <i class="fa-solid fa-rotate-left"></i> Reject
                </button>
                <button type="submit" name="action" value="approve" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm font-semibold flex items-center gap-2 text-sm" onclick="confirmAction(event, 'Are you sure you want to approve this checksheet as {{ $levelName }}?');">
                    <i class="fa-solid fa-check-double"></i> Approve as {{ $levelName }}
                </button>
            @endif
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileUpload = document.getElementById('file-upload');
        const fileNameDisplay = document.getElementById('file-name-display');

        if(fileUpload) {
            fileUpload.addEventListener('change', function(e) {
                if(e.target.files.length > 0) {
                    fileNameDisplay.textContent = e.target.files[0].name;
                    fileNameDisplay.classList.add('text-blue-600', 'font-medium');
                } else {
                    fileNameDisplay.textContent = 'PDF, PNG, JPG up to 10MB';
                    fileNameDisplay.classList.remove('text-blue-600', 'font-medium');
                }
            });
        }

        // Dynamic History Problem Inputs
        const historyWrapper = document.getElementById('dynamic-history-wrapper');
        if (historyWrapper) {
            historyWrapper.addEventListener('click', function(e) {
                const addBtn = e.target.closest('.add-history-btn');
                const removeBtn = e.target.closest('.remove-history-btn');
                
                if (addBtn) {
                    const newRow = document.createElement('div');
                    newRow.className = 'flex items-center gap-2 history-row mt-2';
                    newRow.innerHTML = `
                        <input type="text" name="new_history_problems[]" placeholder="Description of new problem..." class="flex-1 text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-gray-800 dark:text-white">
                        <button type="button" class="px-3 py-2 bg-red-100 hover:bg-red-200 dark:bg-red-900/40 dark:hover:bg-red-800/60 text-red-700 dark:text-red-400 transition remove-history-btn">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                    `;
                    historyWrapper.appendChild(newRow);
                }
                
                if (removeBtn) {
                    removeBtn.closest('.history-row').remove();
                }
            });
        }

        // Sample Check Toggle Logic
        document.querySelectorAll('.sample-cell').forEach(cell => {
            cell.addEventListener('click', function() {
                const resultSelect = this.closest('tr').querySelector('select[id^="row-result-"]');
                if (resultSelect && resultSelect.disabled) return; // Prevent if readonly
                
                const detailId = this.dataset.detailId;
                const input = this.querySelector('input[type="hidden"]');
                const iconContainer = this.querySelector('.icon-container');
                const pointName = this.closest('tr').querySelector('td:nth-child(2)').textContent.trim();
                
                let currentValue = input.value;
                let newValue, iconHtml;

                if (currentValue === '') {
                    newValue = 'OK';
                    iconHtml = '<i class="fa-solid fa-circle text-green-500 text-lg"></i>';
                } else if (currentValue === 'OK') {
                    newValue = 'NG';
                    iconHtml = '<i class="fa-solid fa-xmark text-red-500 text-xl"></i>';
                } else {
                    newValue = '';
                    iconHtml = '<i class="fa-solid fa-minus text-gray-300 dark:text-gray-600"></i>';
                }

                if (newValue === 'NG') {
                    Swal.fire({
                        title: 'NG Found',
                        text: 'Please provide a reason/remark for this NG:',
                        input: 'textarea',
                        inputPlaceholder: 'Enter reason here...',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fa-solid fa-check"></i> Confirm NG',
                        cancelButtonText: 'Cancel',
                        background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
                        color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#111827',
                        inputValidator: (value) => {
                            if (!value || value.trim() === '') {
                                return 'Reason is required for NG!';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            input.value = 'NG';
                            iconContainer.innerHTML = iconHtml;
                            calculateRowResult(detailId);
                            
                            // Append remark
                            const remarkTextarea = document.querySelector('textarea[name="final_result"]');
                            if (remarkTextarea) {
                                let currentRemark = remarkTextarea.value.trim();
                                let addition = `[${pointName}] NG: ${result.value.trim()}`;
                                remarkTextarea.value = currentRemark ? currentRemark + '\n' + addition : addition;
                                // trigger input event in case needed
                                remarkTextarea.dispatchEvent(new Event('input'));
                            }
                        }
                    });
                } else {
                    input.value = newValue;
                    iconContainer.innerHTML = iconHtml;
                    calculateRowResult(detailId);
                    
                    if (newValue === 'OK') {
                        const remarkTextarea = document.querySelector('textarea[name="final_result"]');
                        if (remarkTextarea) {
                            let lines = remarkTextarea.value.split('\n');
                            let newLines = lines.filter(line => !line.startsWith(`[${pointName}] NG:`));
                            remarkTextarea.value = newLines.join('\n').trim();
                        }
                    }
                }
            });
        });

        function calculateRowResult(detailId) {
            const inputs = document.querySelectorAll(`.sample-input-${detailId}`);
            let hasNg = false;
            let allOk = true;
            let hasEmpty = false;

            inputs.forEach(input => {
                if (input.value === 'NG') hasNg = true;
                if (input.value !== 'OK') allOk = false;
                if (input.value === '') hasEmpty = true;
            });

            const resultSelect = document.getElementById(`row-result-${detailId}`);
            if (!resultSelect) return;

            if (hasNg) {
                resultSelect.value = 'NG';
                updateSelectStyle(resultSelect, 'NG');
                resultSelect.dataset.prevValue = 'NG';
            } else if (allOk && !hasEmpty && inputs.length > 0) {
                resultSelect.value = 'OK';
                updateSelectStyle(resultSelect, 'OK');
                resultSelect.dataset.prevValue = 'OK';
            } else {
                resultSelect.value = '';
                updateSelectStyle(resultSelect, '');
                resultSelect.dataset.prevValue = '';
            }
            
            checkIfCanApprove();
        }

        // Handle Row Result Select Dropdown Manual Changes
        document.querySelectorAll('select[id^="row-result-"]').forEach(select => {
            select.dataset.prevValue = select.value;
            
            select.addEventListener('change', function() {
                const newValue = this.value;
                const pointName = this.closest('tr').querySelector('td:nth-child(2)').textContent.trim();
                
                if (newValue === 'NG') {
                    Swal.fire({
                        title: 'NG Found',
                        text: 'Please provide a reason/remark for this NG:',
                        input: 'textarea',
                        inputPlaceholder: 'Enter reason here...',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fa-solid fa-check"></i> Confirm NG',
                        cancelButtonText: 'Cancel',
                        background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
                        color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#111827',
                        inputValidator: (value) => {
                            if (!value || value.trim() === '') {
                                return 'Reason is required for NG!';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.dataset.prevValue = 'NG';
                            updateSelectStyle(this, 'NG');
                            checkIfCanApprove();
                            
                            const remarkTextarea = document.querySelector('textarea[name="final_result"]');
                            if (remarkTextarea) {
                                let currentRemark = remarkTextarea.value.trim();
                                let addition = `[${pointName}] NG: ${result.value.trim()}`;
                                remarkTextarea.value = currentRemark ? currentRemark + '\n' + addition : addition;
                                remarkTextarea.dispatchEvent(new Event('input'));
                            }
                        } else {
                            // Revert value
                            this.value = this.dataset.prevValue;
                            updateSelectStyle(this, this.value);
                            checkIfCanApprove();
                        }
                    });
                } else {
                    this.dataset.prevValue = newValue;
                    updateSelectStyle(this, newValue);
                    checkIfCanApprove();
                    
                    if (newValue === 'OK') {
                        const remarkTextarea = document.querySelector('textarea[name="final_result"]');
                        if (remarkTextarea) {
                            let lines = remarkTextarea.value.split('\n');
                            let newLines = lines.filter(line => !line.startsWith(`[${pointName}] NG:`));
                            remarkTextarea.value = newLines.join('\n').trim();
                        }
                    }
                }
            });
        });

        function updateSelectStyle(select, value) {
            select.classList.remove('text-green-600', 'bg-green-50', 'dark:bg-green-900/20', 'text-red-600', 'bg-red-50', 'dark:bg-red-900/20');
            if (value === 'OK') {
                select.classList.add('text-green-600', 'bg-green-50', 'dark:bg-green-900/20');
            } else if (value === 'NG') {
                select.classList.add('text-red-600', 'bg-red-50', 'dark:bg-red-900/20');
            }
        }
        
        function checkIfCanApprove() {
            let hasNg = false;
            document.querySelectorAll('select[id^="row-result-"]').forEach(select => {
                if (select.value === 'NG') hasNg = true;
            });
            
            const approveBtn = document.querySelector('button[value="approve"]');
            if (approveBtn) {
                if (hasNg) {
                    approveBtn.disabled = true;
                    approveBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    approveBtn.title = 'Cannot approve when there is an NG result. Please use Save Changes.';
                    approveBtn.onclick = function(e) { e.preventDefault(); return false; };
                } else {
                    approveBtn.disabled = false;
                    approveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    approveBtn.title = '';
                    approveBtn.onclick = function(e) { confirmAction(e, 'Are you sure you want to approve this checksheet as {{ $levelName ?? "" }}?'); };
                }
            }
            
            const remarkTextarea = document.querySelector('textarea[name="final_result"]');
            if (remarkTextarea) {
                if (hasNg) {
                    remarkTextarea.required = true;
                    remarkTextarea.classList.add('border-red-500', 'bg-red-50');
                } else {
                    remarkTextarea.required = false;
                    remarkTextarea.classList.remove('border-red-500', 'bg-red-50');
                }
            }
        }
        
        // Form Submission - Fetch API
        function submitViaFetch(details) {
            const actionUrl = '{{ route("checksheet-approvals.store", $checksheet->hashed_id) }}';
            const previousUrl = '{{ base64_encode($previousUrl ?? route("checksheet-approvals.index")) }}';
            
            // Get action value from the clicked button or hidden input
            const actionInput = document.querySelector('input[name="action"]');
            const action = window.lastTriggeredAction || (actionInput ? actionInput.value : 'approve');
            const rejectReasonInput = document.querySelector('input[name="reject_reason"]');
            const rejectReason = window.lastRejectReason || (rejectReasonInput ? rejectReasonInput.value : '');
            
            const payload = {
                _token: '{{ csrf_token() }}',
                action: action,
                reject_reason: rejectReason,
                reject_photo_base64: window.lastRejectPhotoBase64 || '',
                role: '{{ $role ?? "" }}',
                previous_url: previousUrl,
                details_json: JSON.stringify(details)
            };
            
            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(async response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else if (response.ok) {
                    window.location.href = atob(previousUrl);
                } else {
                    const text = await response.text();
                    alert('Submission failed. Server responded with status: ' + response.status);
                    console.error("Server Error:", text);
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                alert('Connection error occurred while submitting.');
            });
        }

        const approvalForm = document.getElementById('approval-form');
        if (approvalForm) {
            approvalForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const details = {};
                
                document.querySelectorAll('.sample-cell').forEach(cell => {
                    const detailId = cell.dataset.detailId;
                    const sampleIndex = cell.dataset.sampleIndex;
                    const input = cell.querySelector('input[type="hidden"]');
                    
                    if (!details[detailId]) {
                        details[detailId] = { samples: {}, row_result: null };
                    }
                    if (input && input.value) {
                        details[detailId].samples[sampleIndex] = input.value;
                    }
                });

                document.querySelectorAll('input[id^="row-result-"]').forEach(resultInput => {
                    const detailId = resultInput.dataset.detailId;
                    if (detailId) {
                        if (!details[detailId]) {
                            details[detailId] = { samples: {}, row_result: null };
                        }
                        details[detailId].row_result = resultInput.value || null;
                    }
                });
                
                submitViaFetch(details);
            });
        }

        let currentRejectPhotoBase64 = null;
        let currentRejectStream = null;

        window.confirmRejectAction = function(event) {
            event.preventDefault();
            const form = document.getElementById('approval-form');
            currentRejectPhotoBase64 = null;

            Swal.fire({
                title: 'Reject Checksheet & Return',
                html: `
                    <div class="text-left text-sm space-y-3">
                        <div>
                            <label class="block font-bold text-gray-700 dark:text-gray-200 mb-1">Reason for Rejection <span class="text-red-500">*</span></label>
                            <textarea id="swal-reject-reason" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-800 dark:text-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Type reason for rejection here..."></textarea>
                        </div>
                        
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <label class="block font-bold text-gray-700 dark:text-gray-200 mb-1">Foto Bukti Reject (Opsional / Kamera)</label>
                            
                            <div class="flex flex-wrap gap-2 mb-2">
                                <button type="button" id="btn-open-camera" onclick="startRejectCamera()" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded shadow-sm transition flex items-center gap-1.5">
                                    <i class="fa-solid fa-camera"></i> Ambil dari Kamera
                                </button>
                                <label class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded shadow-sm transition cursor-pointer flex items-center gap-1.5">
                                    <i class="fa-solid fa-folder-open"></i> Upload File Foto
                                    <input type="file" id="swal-reject-file" accept="image/*" capture="environment" class="hidden" onchange="handleRejectFileSelect(event)">
                                </label>
                            </div>

                            <!-- Live Camera Feed Container -->
                            <div id="reject-camera-container" class="hidden relative mb-2 bg-black rounded-md overflow-hidden border border-gray-700">
                                <video id="reject-camera-video" autoplay playsinline class="w-full h-48 object-cover"></video>
                                <div class="p-2 bg-gray-900 flex justify-between items-center">
                                    <button type="button" onclick="snapRejectPhoto()" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-bold rounded flex items-center gap-1">
                                        <i class="fa-solid fa-circle-dot text-red-400"></i> Tangkap Foto
                                    </button>
                                    <button type="button" onclick="stopRejectCamera()" class="px-2.5 py-1 bg-gray-700 hover:bg-gray-600 text-white text-xs rounded">
                                        Batal
                                    </button>
                                </div>
                            </div>

                            <!-- Photo Preview Container -->
                            <div id="reject-preview-container" class="hidden relative border border-gray-300 dark:border-gray-600 rounded-md p-1 bg-gray-50 dark:bg-gray-800">
                                <img id="reject-preview-img" class="w-full h-40 object-contain rounded">
                                <button type="button" onclick="clearRejectPhoto()" class="absolute top-2 right-2 bg-red-600 hover:bg-red-700 text-white p-1 rounded-full text-xs shadow" title="Hapus Foto">
                                    <i class="fa-solid fa-times w-4 h-4 flex items-center justify-center"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fa-solid fa-rotate-left mr-1"></i> Confirm Reject',
                cancelButtonText: 'Cancel',
                background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff',
                color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#111827',
                didClose: () => {
                    stopRejectCamera();
                },
                preConfirm: () => {
                    const reason = document.getElementById('swal-reject-reason').value.trim();
                    if (!reason) {
                        Swal.showValidationMessage('Alasan reject wajib diisi!');
                        return false;
                    }
                    return {
                        reason: reason,
                        photoBase64: currentRejectPhotoBase64
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.lastTriggeredAction = 'reject';
                    window.lastRejectReason = result.value.reason;
                    window.lastRejectPhotoBase64 = result.value.photoBase64;

                    let hiddenAction = form.querySelector('input[name="action"]');
                    if (!hiddenAction) {
                        hiddenAction = document.createElement('input');
                        hiddenAction.type = 'hidden';
                        hiddenAction.name = 'action';
                        form.appendChild(hiddenAction);
                    }
                    hiddenAction.value = 'reject';

                    let hiddenReason = form.querySelector('input[name="reject_reason"]');
                    if (!hiddenReason) {
                        hiddenReason = document.createElement('input');
                        hiddenReason.type = 'hidden';
                        hiddenReason.name = 'reject_reason';
                        form.appendChild(hiddenReason);
                    }
                    hiddenReason.value = result.value.reason;

                    if (result.value.photoBase64) {
                        let hiddenPhoto = form.querySelector('input[name="reject_photo_base64"]');
                        if (!hiddenPhoto) {
                            hiddenPhoto = document.createElement('input');
                            hiddenPhoto.type = 'hidden';
                            hiddenPhoto.name = 'reject_photo_base64';
                            form.appendChild(hiddenPhoto);
                        }
                        hiddenPhoto.value = result.value.photoBase64;
                    }

                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                }
            });
        };

        window.startRejectCamera = function() {
            const container = document.getElementById('reject-camera-container');
            const video = document.getElementById('reject-camera-video');
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert("Browser Anda tidak mendukung akses kamera langsung. Silakan gunakan tombol Upload File Foto.");
                return;
            }
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                .then(stream => {
                    currentRejectStream = stream;
                    video.srcObject = stream;
                    container.classList.remove('hidden');
                })
                .catch(err => {
                    console.error("Camera access error:", err);
                    alert("Gagal mengakses kamera. Pastikan izin kamera telah diberikan.");
                });
        };

        window.stopRejectCamera = function() {
            if (currentRejectStream) {
                currentRejectStream.getTracks().forEach(track => track.stop());
                currentRejectStream = null;
            }
            const container = document.getElementById('reject-camera-container');
            if (container) container.classList.add('hidden');
        };

        window.snapRejectPhoto = function() {
            const video = document.getElementById('reject-camera-video');
            if (!video || !currentRejectStream) return;

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            currentRejectPhotoBase64 = canvas.toDataURL('image/jpeg', 0.85);
            stopRejectCamera();

            const previewContainer = document.getElementById('reject-preview-container');
            const previewImg = document.getElementById('reject-preview-img');
            if (previewContainer && previewImg) {
                previewImg.src = currentRejectPhotoBase64;
                previewContainer.classList.remove('hidden');
            }
        };

        window.handleRejectFileSelect = function(e) {
            if (e.target.files && e.target.files[0]) {
                const file = e.target.files[0];
                const reader = new FileReader();
                reader.onload = function(evt) {
                    currentRejectPhotoBase64 = evt.target.result;
                    const previewContainer = document.getElementById('reject-preview-container');
                    const previewImg = document.getElementById('reject-preview-img');
                    if (previewContainer && previewImg) {
                        previewImg.src = currentRejectPhotoBase64;
                        previewContainer.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(file);
            }
        };

        window.clearRejectPhoto = function() {
            currentRejectPhotoBase64 = null;
            const previewContainer = document.getElementById('reject-preview-container');
            if (previewContainer) previewContainer.classList.add('hidden');
            const fileInput = document.getElementById('swal-reject-file');
            if (fileInput) fileInput.value = '';
        };

        checkIfCanApprove();
    });
</script>
@endpush
