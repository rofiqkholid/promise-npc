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
        <div class="flex-1 min-w-0 pr-4">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex flex-wrap items-center gap-2">
                <i class="fa-solid fa-clipboard-check text-blue-500"></i> 
                <span class="leading-tight">PART EVENT DELIVERY CHECKSHEET</span>
                @if(optional(optional(optional($part->product)->productDetail))->master_checksheet_status === 'APPROVED')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-bold bg-green-100 text-green-800 border border-green-200 shadow-sm whitespace-nowrap flex-shrink-0">
                        <i class="fa-solid fa-circle-check"></i> QC Approved
                    </span>
                @endif
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
            @if(!$readonly && !$checksheet->mgm_checked_by)
            <form action="{{ route('checksheets.sync', $checksheet->hashed_id) }}" method="POST" class="inline" onsubmit="return confirm('WARNING: This will overwrite all your current Check Point data with the latest Master Data. Are you sure you want to proceed?');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold shadow-sm transition">
                    <i class="fa-solid fa-rotate"></i> Sync Checkpoints
                </button>
            </form>
            @endif
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $isMGM ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800' }} text-sm font-semibold shadow-sm border {{ $isMGM ? 'border-purple-200' : 'border-orange-200' }}">
                <i class="fa-solid fa-user-shield"></i> {{ $role }} Review Mode
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
    <!-- Checksheet Returned / Rejected Banner -->
    <div class="m-4 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded-r shadow-sm">
        <div class="flex items-start gap-3">
            <div class="text-red-500 text-xl font-bold mt-0.5"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="flex-1">
                <h4 class="text-sm font-bold text-red-800 dark:text-red-300 uppercase tracking-wider flex items-center gap-2">
                    Checksheet Returned / Rejected (Perlu Perbaikan)
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

    <form id="checksheet-form" action="{{ route('checksheets.update', $checksheet->hashed_id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="role" value="{{ $role }}">
        <input type="hidden" name="previous_url" value="{{ base64_encode($previousUrl ?? route('tracking.index')) }}">
        <input type="hidden" name="details_json" id="details_json">

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
                        Upload IR Evidence (Max 50MB) <span class="text-gray-400 font-normal">(Optional)</span>
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
                            <p class="text-xs text-gray-500" id="file-name-display">PDF, PNG, JPG up to 50MB</p>
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
                        <a href="{{ url('file/storage/' . ltrim(str_replace('public/', '', $checksheet->attachment_path), '/')) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 shadow-sm text-[13px] font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <i class="fa-solid fa-file-pdf text-red-500"></i> View Attachment QC
                        </a>
                    </div>
                    @else
                    <div class="flex items-center text-sm text-gray-500 italic">
                        No attachment file QC.
                    </div>
                    @endif
                </div>

                @php
                    $checkCount = max(1, min($part->qty, 12));
                    $historyDetails = $checksheet->details->filter(function($d) {
                        $pcLow = trim(strtolower($d->point_check));
                        return str_contains($pcLow, 'history') || str_contains($pcLow, 'problem') || str_starts_with($d->point_check, '[');
                    });
                @endphp

                <div class="mb-4">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">History Problem</h3>
                    <p class="text-xs text-gray-500 mt-1">List of problems previously found on this Product / Part Number in the past.</p>
                </div>

                <div class="mb-6 p-4 border border-red-200 bg-red-50 dark:bg-red-900/10 dark:border-red-800/50 rounded-lg">
                    <!-- Past History (With Checklists) -->
                    <div class="space-y-3 mb-4">
                        @forelse(optional($part->product)->historyProblems ?? [] as $history)
                            @php
                                $pointText = '[' . $history->created_at->format('d/m/y') . '] ' . $history->problem_description;
                                $historyDetail = $historyDetails->first(function($d) use ($pointText, $history) {
                                    return $d->point_check === $pointText || str_contains(strtolower($d->point_check), strtolower($history->problem_description));
                                });
                                $detailId = optional($historyDetail)->id;
                            @endphp
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800/60 rounded-md shadow-2xs">
                                <div class="flex-1">
                                    <span class="font-semibold text-red-700 dark:text-red-400 text-sm">
                                        {{ $history->problem_description }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-2 italic">
                                        (Found on {{ $history->created_at->format('d M Y') }})
                                    </span>
                                </div>
                                @if($detailId)
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Samples:</span>
                                    <div class="flex items-center gap-1.5 overflow-x-auto">
                                        @for($i = 1; $i <= $checkCount; $i++)
                                        @php
                                            $sampleValue = $historyDetail->samples[$i] ?? '';
                                            if (empty($sampleValue) && in_array($part->status, ['APPROVED', 'FINISHED', 'COMPLETED'])) {
                                                $sampleValue = 'OK';
                                            }
                                        @endphp
                                        <div class="flex flex-col items-center">
                                            <span class="text-[10px] text-gray-400 mb-0.5">{{ $i }}</span>
                                            <div class="px-2 py-1 border dark:border-gray-700 rounded text-center cursor-pointer sample-cell select-none bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 transition" data-detail-id="{{ $detailId }}" data-sample-index="{{ $i }}">
                                                <input type="hidden" data-detail-id="{{ $detailId }}" data-sample-index="{{ $i }}" value="{{ $sampleValue }}" class="sample-input-{{ $detailId }}">
                                                <div class="flex items-center justify-center h-6 w-6 mx-auto icon-container">
                                                    @if($sampleValue === 'OK')
                                                        <i class="fa-solid fa-circle text-green-500 text-base"></i>
                                                    @elseif($sampleValue === 'NG')
                                                        <i class="fa-solid fa-xmark text-red-500 text-lg"></i>
                                                    @else
                                                        <i class="fa-solid fa-minus text-gray-300 dark:text-gray-600"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endfor
                                        <input type="hidden" data-detail-id="{{ $detailId }}" id="row-result-{{ $detailId }}" value="{{ $historyDetail->row_result ?? 'OK' }}" {{ $readonly ? 'disabled' : '' }}>
                                    </div>
                                </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-gray-500 italic text-sm">No defect history yet (History Problem) for this part.</div>
                        @endforelse
                    </div>

                    <!-- New History Input -->
                    @if(!$readonly)
                    <div class="border-t border-red-200 dark:border-red-800/50 pt-4 mt-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Add New History Problem <span class="text-gray-500 text-xs font-normal">(Fill in if there are defect findings outside the checklist)</span>
                        </label>
                        <div id="dynamic-history-wrapper" class="space-y-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800/60 rounded-md shadow-2xs history-row">
                                <div class="flex-1">
                                    <input type="text" name="new_history_problems[]" placeholder="Description of new problem..." class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-gray-800 dark:text-white rounded-md">
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Samples:</span>
                                    <div class="flex items-center gap-1.5 overflow-x-auto">
                                        @for($i = 1; $i <= $checkCount; $i++)
                                        <div class="flex flex-col items-center">
                                            <span class="text-[10px] text-gray-400 mb-0.5">{{ $i }}</span>
                                            <div class="px-2 py-1 border dark:border-gray-700 rounded text-center cursor-pointer sample-cell new-history-sample-cell select-none bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 transition" data-sample-index="{{ $i }}">
                                                <input type="hidden" data-sample-index="{{ $i }}" value="" class="new-history-sample-input">
                                                <div class="flex items-center justify-center h-6 w-6 mx-auto icon-container">
                                                    <i class="fa-solid fa-minus text-gray-300 dark:text-gray-600"></i>
                                                </div>
                                            </div>
                                        </div>
                                        @endfor
                                    </div>
                                    <button type="button" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition add-history-btn rounded-md" title="Add another problem row">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
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
                            @php
                                $normalDetails = $checksheet->details->reject(function($d) {
                                    $pcLow = trim(strtolower($d->point_check));
                                    return str_contains($pcLow, 'history') || str_contains($pcLow, 'problem') || str_starts_with($d->point_check, '[');
                                });
                            @endphp
                            @forelse($normalDetails as $detail)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="px-4 py-3 border-r dark:border-gray-700 text-center text-gray-500">{{ $loop->iteration }}</td>
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
                                    <input type="hidden" data-detail-id="{{ $detail->id }}" id="row-result-{{ $detail->id }}" value="{{ $detail->row_result }}" {{ $readonly ? 'disabled' : '' }}>
                                    <div id="row-result-display-{{ $detail->id }}" class="w-full text-xs py-1.5 px-2 font-bold border border-gray-300 dark:border-gray-600 shadow-sm dark:bg-gray-800 dark:text-white @if($detail->row_result == 'OK') text-green-600 bg-green-50 dark:bg-green-900/20 @elseif($detail->row_result == 'NG') text-red-600 bg-red-50 dark:bg-red-900/20 @else text-gray-400 @endif">
                                        {{ $detail->row_result ?: '- Auto -' }}
                                    </div>
                                     <div id="ng-photo-container-{{ $detail->id }}" class="mt-1.5 {{ $detail->row_result === 'NG' ? '' : 'hidden' }}">
                                        @php
                                            $ngPhotoUrl = $detail->ng_photo_path ? url('file/storage/' . ltrim(str_replace('public/', '', $detail->ng_photo_path), '/')) : null;
                                        @endphp
                                        <input type="hidden" id="ng-reason-val-{{ $detail->id }}" value="{{ $detail->ng_reason }}">
                                        <div id="ng-reason-display-{{ $detail->id }}" class="text-[10px] text-red-600 dark:text-red-300 font-semibold italic mb-1 max-w-[150px] mx-auto break-words leading-tight {{ $detail->ng_reason ? '' : 'hidden' }}">
                                            "{{ $detail->ng_reason }}"
                                        </div>
                                        <div id="ng-photo-preview-box-{{ $detail->id }}" class="{{ $ngPhotoUrl ? '' : 'hidden' }} flex items-center justify-center gap-1">
                                            <a href="{{ $ngPhotoUrl ?: '#' }}" target="_blank" id="ng-photo-link-{{ $detail->id }}" class="inline-block relative group">
                                                <img src="{{ $ngPhotoUrl }}" id="ng-photo-img-{{ $detail->id }}" class="h-8 w-8 object-cover rounded border border-red-300 dark:border-red-700 shadow-2xs hover:scale-110 transition">
                                            </a>
                                            @if(!$readonly)
                                            <button type="button" onclick="openNgPhotoModal({{ $detail->id }}, '{{ e($detail->point_check) }}')" class="text-[10px] text-blue-600 dark:text-blue-400 hover:underline p-0.5" title="Edit Evidence & Reason"><i class="fa-solid fa-pen"></i></button>
                                            <button type="button" onclick="removeNgPhoto({{ $detail->id }})" class="text-[10px] text-red-600 dark:text-red-400 hover:underline p-0.5" title="Remove Photo"><i class="fa-solid fa-trash"></i></button>
                                            @endif
                                        </div>
                                        <div id="ng-photo-btn-box-{{ $detail->id }}" class="{{ $ngPhotoUrl ? 'hidden' : '' }}">
                                            @if(!$readonly)
                                            <button type="button" onclick="openNgPhotoModal({{ $detail->id }}, '{{ e($detail->point_check) }}')" class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 bg-red-50 dark:bg-red-900/40 text-red-600 dark:text-red-300 border border-red-200 dark:border-red-800 rounded hover:bg-red-100 transition whitespace-nowrap">
                                                <i class="fa-solid fa-camera text-red-500"></i> Evidence & Reason
                                            </button>
                                            @else
                                            <span class="text-[10px] text-gray-400 italic">No Photo</span>
                                            @endif
                                        </div>
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
            <a href="{{ $previousUrl ?? route('tracking.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm text-[13px] font-medium">
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
                    fileNameDisplay.textContent = 'PDF, PNG, JPG up to 50MB';
                    fileNameDisplay.classList.remove('text-blue-600', 'font-medium');
                }
            });
        }

        // Dynamic History Problem Inputs
        const checkCount = {{ $checkCount }};
        const historyWrapper = document.getElementById('dynamic-history-wrapper');
        if (historyWrapper) {
            historyWrapper.addEventListener('click', function(e) {
                const addBtn = e.target.closest('.add-history-btn');
                const removeBtn = e.target.closest('.remove-history-btn');
                
                if (addBtn) {
                    let sampleColsHtml = '';
                    for (let i = 1; i <= checkCount; i++) {
                        sampleColsHtml += `
                            <div class="flex flex-col items-center">
                                <span class="text-[10px] text-gray-400 mb-0.5">${i}</span>
                                <div class="px-2 py-1 border dark:border-gray-700 rounded text-center cursor-pointer sample-cell new-history-sample-cell select-none bg-gray-50 dark:bg-gray-700/50 hover:bg-gray-100 transition" data-sample-index="${i}">
                                    <input type="hidden" data-sample-index="${i}" value="" class="new-history-sample-input">
                                    <div class="flex items-center justify-center h-6 w-6 mx-auto icon-container">
                                        <i class="fa-solid fa-minus text-gray-300 dark:text-gray-600"></i>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    const newRow = document.createElement('div');
                    newRow.className = 'flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800/60 rounded-md shadow-2xs history-row mt-2';
                    newRow.innerHTML = `
                        <div class="flex-1">
                            <input type="text" name="new_history_problems[]" placeholder="Description of new problem..." class="w-full text-sm border-gray-300 dark:border-gray-600 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-gray-800 dark:text-white rounded-md">
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Samples:</span>
                            <div class="flex items-center gap-1.5 overflow-x-auto">
                                ${sampleColsHtml}
                            </div>
                            <button type="button" class="px-3 py-2 bg-red-100 hover:bg-red-200 dark:bg-red-900/40 dark:hover:bg-red-800/60 text-red-700 dark:text-red-400 transition remove-history-btn rounded-md" title="Remove problem row">
                                <i class="fa-solid fa-minus"></i>
                            </button>
                        </div>
                    `;
                    historyWrapper.appendChild(newRow);
                }
                
                if (removeBtn) {
                    removeBtn.closest('.history-row').remove();
                    updateOverallFormStatus();
                }
            });
        }

        // Sample Check Toggle Logic (Using event delegation on document)
        document.addEventListener('click', function(e) {
            const cell = e.target.closest('.sample-cell');
            if (!cell) return;

            // Require history problem description to be filled first for new history rows
            const historyRow = cell.closest('.history-row');
            if (historyRow) {
                const textInput = historyRow.querySelector('input[name="new_history_problems[]"]');
                if (textInput && textInput.value.trim() === '') {
                    textInput.focus();
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Problem Description Required',
                            text: 'Please enter the problem description first before filling in sample results!',
                            confirmButtonColor: '#ea580c'
                        });
                    } else {
                        alert('Please enter the problem description first before filling in sample results!');
                    }
                    return;
                }
            }

            const detailId = cell.dataset.detailId;
            if (detailId) {
                const resultElement = document.getElementById(`row-result-${detailId}`);
                if (resultElement && resultElement.disabled) return; // Prevent if readonly
            }
            
            const input = cell.querySelector('input[type="hidden"]');
            const iconContainer = cell.querySelector('.icon-container');
            if (!input || !iconContainer) return;
            
            let currentValue = input.value;
            let newValue, iconHtml;

            if (currentValue === '') {
                newValue = 'OK';
                iconHtml = '<i class="fa-solid fa-circle text-green-500 text-base"></i>';
            } else if (currentValue === 'OK') {
                newValue = 'NG';
                iconHtml = '<i class="fa-solid fa-xmark text-red-500 text-lg"></i>';
            } else {
                newValue = '';
                iconHtml = '<i class="fa-solid fa-minus text-gray-300 dark:text-gray-600"></i>';
            }

            if (newValue === 'NG') {
                let pointName = 'History Problem';
                if (detailId) {
                    const tr = cell.closest('tr');
                    if (tr && tr.querySelector('td:nth-child(2)')) {
                        pointName = tr.querySelector('td:nth-child(2)').textContent.trim();
                    } else {
                        const historyBox = cell.closest('.flex-col, .flex');
                        if (historyBox && historyBox.querySelector('.font-semibold')) {
                            pointName = historyBox.querySelector('.font-semibold').textContent.trim();
                        }
                    }

                    openNgPhotoModal(detailId, pointName).then((result) => {
                        if (result && result.isConfirmed) {
                            input.value = 'NG';
                            iconContainer.innerHTML = iconHtml;
                            calculateRowResult(detailId);
                        } else {
                            // Revert to previous value if NG modal cancelled
                            input.value = currentValue;
                            iconContainer.innerHTML = (currentValue === 'OK') 
                                ? '<i class="fa-solid fa-circle text-green-500 text-base"></i>' 
                                : '<i class="fa-solid fa-minus text-gray-300 dark:text-gray-600"></i>';
                            calculateRowResult(detailId);
                        }
                    });
                } else {
                    // For new history problem without detailId yet
                    const historyRow = cell.closest('.history-row');
                    const textInput = historyRow ? historyRow.querySelector('input[name="new_history_problems[]"]') : null;
                    if (textInput && textInput.value.trim() !== '') {
                        pointName = textInput.value.trim();
                    }
                    input.value = 'NG';
                    iconContainer.innerHTML = iconHtml;
                    updateOverallFormStatus();
                }
            } else {
                input.value = newValue;
                iconContainer.innerHTML = iconHtml;
                if (detailId) {
                    calculateRowResult(detailId);
                } else {
                    updateOverallFormStatus();
                }
            }
        });

        function calculateRowResult(detailId) {
            const inputs = document.querySelectorAll(`.sample-input-${detailId}`);
            const resultInput = document.getElementById(`row-result-${detailId}`);
            const resultDisplay = document.getElementById(`row-result-display-${detailId}`);
            const photoContainer = document.getElementById(`ng-photo-container-${detailId}`);
            if (!resultInput) return;

            if (inputs.length > 0) {
                let hasNg = false;
                let allOk = true;
                let hasEmpty = false;

                inputs.forEach(input => {
                    if (input.value === 'NG') hasNg = true;
                    if (input.value !== 'OK') allOk = false;
                    if (input.value === '') hasEmpty = true;
                });

                if (hasNg) {
                    resultInput.value = 'NG';
                    if (resultDisplay) { resultDisplay.textContent = 'NG'; updateSelectStyle(resultDisplay, 'NG'); }
                    if (photoContainer) photoContainer.classList.remove('hidden');
                } else if (allOk && !hasEmpty) {
                    resultInput.value = 'OK';
                    if (resultDisplay) { resultDisplay.textContent = 'OK'; updateSelectStyle(resultDisplay, 'OK'); }
                    if (photoContainer) photoContainer.classList.add('hidden');
                } else {
                    resultInput.value = '';
                    if (resultDisplay) { resultDisplay.textContent = '- Auto -'; updateSelectStyle(resultDisplay, ''); }
                    if (photoContainer) photoContainer.classList.add('hidden');
                }
            } else {
                if (resultDisplay) {
                    if (resultInput.value === 'NG') {
                        updateSelectStyle(resultDisplay, 'NG');
                        if (photoContainer) photoContainer.classList.remove('hidden');
                    } else if (resultInput.value === 'OK') {
                        updateSelectStyle(resultDisplay, 'OK');
                        if (photoContainer) photoContainer.classList.add('hidden');
                    } else {
                        if (photoContainer) photoContainer.classList.add('hidden');
                    }
                }
            }
            
            updateOverallFormStatus();
        }

        function updateOverallFormStatus() {
            const submitBtn = document.getElementById('submit-btn');
            if (!submitBtn || submitBtn.dataset.role !== 'MGM') return;

            const allSelects = document.querySelectorAll('input[id^="row-result-"]');
            let hasNg = false;
            let hasEmpty = false;
            allSelects.forEach(select => {
                if (select.value === 'NG') hasNg = true;
                if (!select.value || select.value === '') hasEmpty = true;
            });

            document.querySelectorAll('.new-history-sample-input').forEach(sInput => {
                if (sInput.value === 'NG') hasNg = true;
            });

            const btnText = document.getElementById('submit-btn-text');
            const icon = submitBtn.querySelector('i');

            if (hasNg) {
                btnText.textContent = 'Save Draft (NG Found)';
                icon.className = 'fa-solid fa-save';
                submitBtn.classList.remove('bg-purple-600', 'hover:bg-purple-700');
                submitBtn.classList.add('bg-yellow-600', 'hover:bg-yellow-700');
            } else if (hasEmpty) {
                btnText.textContent = 'Save Draft (Incomplete)';
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

        // Form Submission - Fetch API
        async function submitViaFetch(details) {
            const actionUrl = '{{ route("checksheets.update", $checksheet->hashed_id) }}';
            const previousUrl = '{{ base64_encode($previousUrl ?? route("tracking.index")) }}';
            const form = document.getElementById('checksheet-form');
            const role = '{{ $role }}';
            
            const btn = document.getElementById('submit-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...';
            }

            // Extract new history problems and samples from the DOM
            const historyRows = form.querySelectorAll('.history-row');
            let newHistoryProblems = [];
            let newHistorySamples = [];
            if (historyRows.length > 0) {
                historyRows.forEach(row => {
                    const textInput = row.querySelector('input[name="new_history_problems[]"]');
                    if (textInput && textInput.value.trim() !== '') {
                        newHistoryProblems.push(textInput.value.trim());
                        let samplesObj = {};
                        row.querySelectorAll('.new-history-sample-input').forEach(sInput => {
                            const sIdx = sInput.dataset.sampleIndex;
                            if (sIdx && sInput.value) {
                                samplesObj[sIdx] = sInput.value;
                            }
                        });
                        newHistorySamples.push(samplesObj);
                    }
                });
            }

            let fetchOptions = {};
            
            if (role === 'QC') {
                // QC has file uploads, using application/json + base64 to bypass WAF limit
                let payload = {
                    _token: '{{ csrf_token() }}',
                    role: role,
                    previous_url: previousUrl,
                    details_json: JSON.stringify(details),
                    accuracy_percentage: form.querySelector('[name="accuracy_percentage"]') ? form.querySelector('[name="accuracy_percentage"]').value : '',
                    new_history_problems: newHistoryProblems,
                    new_history_samples: newHistorySamples
                };

                const fileInput = form.querySelector('input[name="attachment_file"]');
                if (fileInput && fileInput.files.length > 0) {
                    const file = fileInput.files[0];
                    payload.attachment_file_name = file.name;
                    const base64Str = await new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = () => resolve(reader.result.split(',')[1]);
                        reader.onerror = error => reject(error);
                    });
                    
                    const chunkSize = 512 * 1024; // 512KB chunks to safely bypass strict 1MB proxy limits
                    const uniqueName = Date.now() + '_' + file.name.replace(/[^a-zA-Z0-9.\-_]/g, '_');
                    
                    try {
                        for (let i = 0; i < base64Str.length; i += chunkSize) {
                            const chunk = base64Str.substring(i, i + chunkSize);
                            const response = await fetch('{{ route("upload.chunk") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ chunk: chunk, file_name: uniqueName })
                            });
                            
                            if (!response.ok) {
                                throw new Error('Chunk upload failed with status ' + response.status);
                            }
                            
                            if (btn) {
                                const progress = Math.min(100, Math.round(((i + chunkSize) / base64Str.length) * 100));
                                btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-1"></i> Uploading ${progress}%...`;
                            }
                        }
                        
                        payload.attachment_temp_file = uniqueName;
                        
                        if (btn) {
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Finalizing...';
                        }
                    } catch (e) {
                        alert('Upload failed: ' + e.message);
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = originalBtnHtml;
                        }
                        return;
                    }
                }
                
                fetchOptions = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                };
            } else {
                // MGM has no files and large text, MUST use application/json to bypass WAF text limits
                const payload = {
                    _token: '{{ csrf_token() }}',
                    role: role,
                    previous_url: previousUrl,
                    details_json: JSON.stringify(details),
                    final_result: form.querySelector('[name="final_result"]') ? form.querySelector('[name="final_result"]').value : '',
                    new_history_problems: newHistoryProblems,
                    new_history_samples: newHistorySamples
                };
                
                fetchOptions = {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                };
            }

            fetch(actionUrl, fetchOptions)
            .then(async response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else if (response.ok) {
                    const data = await response.json();
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = atob(previousUrl);
                    }
                } else {
                    const text = await response.text();
                    let errMsg = 'Submission failed. Server responded with status: ' + response.status;
                    if (response.status === 422) {
                        try {
                            const errors = JSON.parse(text).errors;
                            errMsg = Object.values(errors).flat().join('\n');
                        } catch(e) {}
                    }
                    alert(errMsg);
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save mr-1"></i> Save QC Data'; }
                    console.error("Server Error:", text);
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                alert('Connection error occurred while submitting.');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save mr-1"></i> Save QC Data'; }
            });
        }

        const checksheetForm = document.getElementById('checksheet-form');
        if (checksheetForm) {
            checksheetForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const details = {};
                
                document.querySelectorAll('.sample-cell').forEach(cell => {
                    const detailId = cell.dataset.detailId;
                    if (!detailId || detailId === 'undefined') return;
                    
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

                if (window.ngPhotoStaging) {
                    Object.keys(window.ngPhotoStaging).forEach(detailId => {
                        if (!details[detailId]) {
                            details[detailId] = { samples: {}, row_result: null };
                        }
                        details[detailId].ng_photo_base64 = window.ngPhotoStaging[detailId];
                    });
                }

                if (window.ngReasonStaging) {
                    Object.keys(window.ngReasonStaging).forEach(detailId => {
                        if (!details[detailId]) {
                            details[detailId] = { samples: {}, row_result: null };
                        }
                        details[detailId].ng_reason = window.ngReasonStaging[detailId];
                    });
                }
                
                submitViaFetch(details);
            });
        }

        const submitBtn = document.getElementById('submit-btn');
        if (submitBtn) {
            submitBtn.addEventListener('click', function(e) {
                serializeChecksheetDetails();
            });
        }

        // Initialize button state and calculate row results on page load
        document.querySelectorAll('input[id^="row-result-"]').forEach(resultInput => {
            const detailId = resultInput.dataset.detailId;
            if (detailId) {
                calculateRowResult(detailId);
            }
        });
        updateOverallFormStatus();
    });

    window.ngPhotoStaging = window.ngPhotoStaging || {};
    window.ngReasonStaging = window.ngReasonStaging || {};
    let currentNgCameraStream = null;

    window.openNgPhotoModal = function(detailId, pointCheckName) {
        if (currentNgCameraStream) {
            currentNgCameraStream.getTracks().forEach(track => track.stop());
            currentNgCameraStream = null;
        }

        const existingReason = window.ngReasonStaging[detailId] !== undefined 
            ? window.ngReasonStaging[detailId] 
            : (document.getElementById(`ng-reason-val-${detailId}`)?.value || '');

        return Swal.fire({
            title: 'NG Evidence & Reason',
            html: `
                <div class="text-left text-sm space-y-3">
                    <p class="text-xs text-gray-500 font-semibold">Point: <span class="text-gray-800 dark:text-gray-200 font-bold">${pointCheckName}</span></p>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1"><i class="fa-solid fa-comment-dots text-red-500 mr-1"></i> NG Reason / Description <span class="text-red-500 font-bold">*Required</span>:</label>
                        <textarea id="swal-ng-reason" rows="2" class="w-full text-xs p-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:ring-1 focus:ring-blue-500" placeholder="Explain reason / description for NG finding (Required)...">${existingReason}</textarea>
                    </div>

                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5"><i class="fa-solid fa-camera text-red-500 mr-1"></i> Photo Evidence <span class="text-gray-400 font-normal">(Optional)</span>:</label>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="startNgCamera()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded shadow transition inline-flex items-center gap-1">
                                <i class="fa-solid fa-video"></i> Use Camera
                            </button>
                            <label class="px-3 py-1.5 bg-gray-600 hover:bg-gray-700 text-white text-xs font-bold rounded shadow transition cursor-pointer inline-flex items-center gap-1">
                                <i class="fa-solid fa-upload"></i> Upload File
                                <input type="file" id="swal-ng-file" accept="image/*" class="hidden" onchange="handleNgFileSelect(event)">
                            </label>
                        </div>
                    </div>

                    <div id="ng-camera-container" class="hidden relative bg-black rounded overflow-hidden">
                        <video id="ng-camera-video" autoplay playsinline class="w-full h-48 object-cover"></video>
                        <button type="button" onclick="snapNgPhoto()" class="absolute bottom-2 left-1/2 -translate-x-1/2 px-4 py-1.5 bg-red-600 text-white text-xs font-bold rounded-full shadow-md hover:bg-red-700 transition">
                            <i class="fa-solid fa-camera"></i> Snap Photo
                        </button>
                    </div>

                    <div id="ng-preview-container" class="hidden">
                        <span class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Photo Preview:</span>
                        <div class="relative inline-block border rounded overflow-hidden">
                            <img id="ng-preview-img" src="" class="max-h-48 w-auto rounded">
                            <button type="button" onclick="clearNgModalPhoto()" class="absolute top-1 right-1 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center shadow">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-check"></i> Save Evidence & Reason',
            confirmButtonColor: '#10B981',
            cancelButtonText: 'Cancel',
            willClose: () => {
                if (currentNgCameraStream) {
                    currentNgCameraStream.getTracks().forEach(track => track.stop());
                    currentNgCameraStream = null;
                }
            },
            preConfirm: () => {
                const previewImg = document.getElementById('ng-preview-img');
                const reasonInput = document.getElementById('swal-ng-reason');
                const reasonText = reasonInput ? reasonInput.value.trim() : '';

                if (!reasonText) {
                    Swal.showValidationMessage('NG Reason is required! Please explain the defect finding.');
                    return false;
                }

                return {
                    photo: (previewImg && previewImg.src && previewImg.src !== window.location.href) ? previewImg.src : null,
                    reason: reasonText
                };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const base64Photo = result.value.photo;
                const reasonText = result.value.reason;

                if (base64Photo && base64Photo !== window.location.href) {
                    window.ngPhotoStaging[detailId] = base64Photo;
                }
                window.ngReasonStaging[detailId] = reasonText;

                const reasonDisp = document.getElementById(`ng-reason-display-${detailId}`);
                const reasonVal = document.getElementById(`ng-reason-val-${detailId}`);
                if (reasonVal) reasonVal.value = reasonText;
                if (reasonDisp) {
                    reasonDisp.textContent = reasonText ? `"${reasonText}"` : '';
                    if (reasonText) reasonDisp.classList.remove('hidden'); else reasonDisp.classList.add('hidden');
                }

                if (base64Photo && base64Photo !== window.location.href) {
                    const previewBox = document.getElementById(`ng-photo-preview-box-${detailId}`);
                    const btnBox = document.getElementById(`ng-photo-btn-box-${detailId}`);
                    const img = document.getElementById(`ng-photo-img-${detailId}`);
                    const link = document.getElementById(`ng-photo-link-${detailId}`);

                    if (img) img.src = base64Photo;
                    if (link) link.href = base64Photo;
                    if (previewBox) previewBox.classList.remove('hidden');
                    if (btnBox) btnBox.classList.add('hidden');
                }
            }
        });
    };

    window.startNgCamera = async function() {
        const cameraContainer = document.getElementById('ng-camera-container');
        const video = document.getElementById('ng-camera-video');
        try {
            if (currentNgCameraStream) {
                currentNgCameraStream.getTracks().forEach(track => track.stop());
            }
            currentNgCameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
            });
            video.srcObject = currentNgCameraStream;
            cameraContainer.classList.remove('hidden');
        } catch (err) {
            alert('Could not access camera: ' + err.message);
        }
    };

    window.snapNgPhoto = function() {
        const video = document.getElementById('ng-camera-video');
        if (!video || !currentNgCameraStream) return;

        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const base64 = canvas.toDataURL('image/jpeg', 0.85);
        
        currentNgCameraStream.getTracks().forEach(track => track.stop());
        currentNgCameraStream = null;
        document.getElementById('ng-camera-container').classList.add('hidden');

        const previewContainer = document.getElementById('ng-preview-container');
        const previewImg = document.getElementById('ng-preview-img');
        if (previewContainer && previewImg) {
            previewImg.src = base64;
            previewContainer.classList.remove('hidden');
        }
    };

    window.handleNgFileSelect = function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            const reader = new FileReader();
            reader.onload = function(evt) {
                const previewContainer = document.getElementById('ng-preview-container');
                const previewImg = document.getElementById('ng-preview-img');
                if (previewContainer && previewImg) {
                    previewImg.src = evt.target.result;
                    previewContainer.classList.remove('hidden');
                }
            };
            reader.readAsDataURL(file);
        }
    };

    window.clearNgModalPhoto = function() {
        const previewContainer = document.getElementById('ng-preview-container');
        if (previewContainer) previewContainer.classList.add('hidden');
        const fileInput = document.getElementById('swal-ng-file');
        if (fileInput) fileInput.value = '';
    };

    window.removeNgPhoto = function(detailId) {
        if (confirm('Are you sure you want to remove this NG evidence photo?')) {
            window.ngPhotoStaging[detailId] = 'REMOVE';
            const previewBox = document.getElementById(`ng-photo-preview-box-${detailId}`);
            const btnBox = document.getElementById(`ng-photo-btn-box-${detailId}`);
            if (previewBox) previewBox.classList.add('hidden');
            if (btnBox) btnBox.classList.remove('hidden');
        }
    };
</script>
@endpush
