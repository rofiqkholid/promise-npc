@extends('layouts.app')

@section('title', 'Input Quality Checksheet')
@section('page_title', 'Checksheet Production / ' . (optional($part->event)->po_no ?? 'Part Has Been Deleted'))

@section('content')
@php
    $readonly = request()->has('readonly') || in_array(optional($part)->status, ['WAITING_APPROVAL', 'FINISHED', 'CLOSED', 'OUTSTANDING']);
    $isMGM = $part ? ($part->status === 'WAITING_MGM_CHECK' || $readonly) : false;
    $role = $readonly ? 'READONLY' : ($isMGM ? 'MGM' : 'QC');
@endphp

<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 max-w-5xl mx-auto">
    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gray-50/50 dark:bg-gray-800/50">
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
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $isMGM ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800' }} text-sm font-semibold shadow-sm border {{ $isMGM ? 'border-purple-200' : 'border-orange-200' }}">
                <i class="fa-solid fa-user-shield"></i> {{ $role }} Review Mode
            </span>
        </div>
    </div>

    <!-- Part Context Info -->
    <div class="px-4 py-2 grid grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div>
            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Event/Project</span>
            <span class="text-sm font-medium text-gray-700 dark:text-white">{{ optional(optional($part->event)->customerCategory)->name ?? 'N/A' }}</span>
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

    <!-- Product Sketch Image -->
    @if(optional(optional($part->product)->productDetail)->sketch_image_path)
    <div class="px-4 py-6 bg-gray-50 dark:bg-gray-800/80 border-b border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center">
        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Part Sketch Reference</h3>
        <img src="{{ url('file/storage/' . ltrim(str_replace('public/', '', $part->product->productDetail->sketch_image_path), '/')) }}" alt="Sketch Image" class="max-h-[400px] max-w-full object-contain border border-gray-300 dark:border-gray-600 shadow-md p-2 rounded bg-white dark:bg-gray-900">
    </div>
    @endif

    <form action="{{ route('checksheets.update', $checksheet->hashed_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="role" value="{{ $role }}">

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
            @if(!$isMGM)
            <!--=============================
                   QA / QC FORM 
            ==============================-->
            <div class="space-y-6 max-w-2xl mx-auto">
                <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 text-sm text-blue-700 dark:text-blue-300">
                    <p class="font-semibold mb-1">Instruction Quality Control:</p>
                    <p>Please fill in the part dimension accuracy percentage and attach the physical inspection report file (PDF/Image).</p>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Calculation Accuracy (%) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative w-48">
                        <input type="number" step="0.01" min="0" max="100" name="accuracy_percentage" required value="{{ old('accuracy_percentage', $checksheet->accuracy_percentage) }}"
                            class="w-full text-right pr-8 border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-lg font-bold text-gray-800 dark:bg-gray-700 dark:text-white pb-2 pt-2">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 font-bold">%</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Upload IR Evidence (Max 10MB) <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <div class="space-y-1 text-center">
                            <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-400 mb-2"></i>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-700 font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 px-2 py-1">
                                    <span>Upload a file</span>
                                    <input id="file-upload" name="attachment_file" type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png">
                                </label>
                            </div>
                            <p class="text-xs text-gray-500" id="file-name-display">PDF, PNG, JPG up to 10MB</p>
                        </div>
                    </div>
                    @if($checksheet->attachment_path)
                        <div class="mt-2 text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
                            <i class="fa-solid fa-paperclip"></i> Existing file attached. Upload again to replace.
                        </div>
                    @endif
                </div>
            </div>
            @endif

            @if($isMGM)
            <!--=============================
                   MGM CHECKLIST FORM 
            ==============================-->
            <div class="mb-6">
                <!-- Data QC Previous (Read Only) -->
                <div class="flex flex-col md:flex-row gap-6 mb-6 p-4 bg-slate-50 border border-slate-200 dark:bg-gray-900 dark:border-gray-700">
                    <div>
                        <span class="block text-xs text-gray-500 uppercase font-semibold">Result Accuracy QC</span>
                        <span class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ $checksheet->accuracy_percentage ?? 'N/A' }}%</span>
                    </div>
                    @if($checksheet->attachment_path)
                    <div class="flex items-center">
                        <a href="{{ Storage::url($checksheet->attachment_path) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 shadow-sm text-[13px] font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <i class="fa-solid fa-file-pdf text-red-500"></i> View Attachment QC
                        </a>
                    </div>
                    @else
                    <div class="flex items-center text-sm text-gray-500 italic">
                        No attachment file QC.
                    </div>
                    @endif
                </div>

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

                    <!-- New History Input -->
                    @if(!$readonly)
                    <div class="border-t border-red-200 dark:border-red-800/50 pt-4 mt-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Add New History Problem <span class="text-gray-500 text-xs font-normal">(Fill in if there are defect findings outside the checklist)</span>
                        </label>
                        <div id="dynamic-history-wrapper" class="space-y-2">
                            <div class="flex items-center gap-2 history-row">
                                <input type="text" name="new_history_problems[]" placeholder="Description of new problem..." class="flex-1 text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-gray-800 dark:text-white">
                                <button type="button" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition add-history-btn">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
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
                                    <input type="hidden" name="details[{{ $detail->id }}][samples][{{ $i }}]" value="{{ $sampleValue }}" class="sample-input-{{ $detail->id }}">
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
                                    <input type="hidden" name="details[{{ $detail->id }}][row_result]" id="row-result-{{ $detail->id }}" value="{{ $detail->row_result }}" {{ $readonly ? 'disabled' : '' }}>
                                    <div id="row-result-display-{{ $detail->id }}" class="w-full text-xs py-1.5 px-2 font-bold border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-800 dark:text-white @if($detail->row_result == 'OK') text-green-600 bg-green-50 dark:bg-green-900/20 @elseif($detail->row_result == 'NG') text-red-600 bg-red-50 dark:bg-red-900/20 @else text-gray-400 @endif">
                                        {{ $detail->row_result ?: '- Auto -' }}
                                    </div>
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
                                class="border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-base py-2 px-3 w-full text-gray-800 dark:bg-gray-800 dark:text-white dark:border-gray-600" placeholder="Add remark if necessary...">{{ $checksheet->final_result }}</textarea>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
            <a href="{{ route('tracking.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm text-[13px] font-medium">
                {{ $readonly ? 'Back to Tracking' : 'Cancel' }}
            </a>
            @if(!$readonly)
            <button type="submit" id="submit-btn" data-role="{{ $role }}" class="px-5 py-2 {{ $isMGM ? 'bg-purple-600 hover:bg-purple-700' : 'bg-orange-600 hover:bg-orange-700' }} text-white transition shadow-sm font-semibold flex items-center gap-2 text-sm">
                <i class="fa-solid fa-floppy-disk"></i> <span id="submit-btn-text">{{ $isMGM ? 'Submit to Approval' : 'Submit Accuracy (QC)' }}</span>
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
                const resultElement = this.closest('tr').querySelector('input[name$="[row_result]"]');
                if (!resultElement || resultElement.disabled) return; // Prevent if readonly
                
                const detailId = this.dataset.detailId;
                const input = this.querySelector('input[type="hidden"]');
                const iconContainer = this.querySelector('.icon-container');
                
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

                input.value = newValue;
                iconContainer.innerHTML = iconHtml;

                calculateRowResult(detailId);
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

            const resultInput = document.getElementById(`row-result-${detailId}`);
            const resultDisplay = document.getElementById(`row-result-display-${detailId}`);
            if (!resultInput || !resultDisplay) return;

            if (hasNg) {
                resultInput.value = 'NG';
                resultDisplay.textContent = 'NG';
                updateSelectStyle(resultDisplay, 'NG');
            } else if (allOk && !hasEmpty && inputs.length > 0) {
                resultInput.value = 'OK';
                resultDisplay.textContent = 'OK';
                updateSelectStyle(resultDisplay, 'OK');
            } else {
                resultInput.value = '';
                resultDisplay.textContent = '- Auto -';
                updateSelectStyle(resultDisplay, '');
            }
            
            updateOverallFormStatus();
        }

        function updateOverallFormStatus() {
            const submitBtn = document.getElementById('submit-btn');
            if (!submitBtn || submitBtn.dataset.role !== 'MGM') return;

            const allSelects = document.querySelectorAll('input[name$="[row_result]"]');
            let hasNg = false;
            allSelects.forEach(select => {
                if (select.value === 'NG') hasNg = true;
            });

            const btnText = document.getElementById('submit-btn-text');
            const icon = submitBtn.querySelector('i');

            if (hasNg) {
                btnText.textContent = 'Save Draft (NG Found)';
                icon.className = 'fa-solid fa-save';
                submitBtn.classList.remove('bg-purple-600', 'hover:bg-purple-700');
                submitBtn.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
            } else {
                btnText.textContent = 'Submit to Approval';
                icon.className = 'fa-solid fa-floppy-disk';
                submitBtn.classList.remove('bg-yellow-600', 'hover:bg-yellow-700');
                submitBtn.classList.add('bg-purple-600', 'hover:bg-purple-700');
            }
        }

        function updateSelectStyle(element, value) {
            element.classList.remove('text-green-600', 'bg-green-50', 'dark:bg-green-900/20', 'text-red-600', 'bg-red-50', 'dark:bg-red-900/20', 'text-gray-400');
            if (value === 'OK') {
                element.classList.add('text-green-600', 'bg-green-50', 'dark:bg-green-900/20');
            } else if (value === 'NG') {
                element.classList.add('text-red-600', 'bg-red-50', 'dark:bg-red-900/20');
            } else {
                element.classList.add('text-gray-400');
            }
        }

        // Initialize button state on page load
        updateOverallFormStatus();
    });
</script>
@endpush
