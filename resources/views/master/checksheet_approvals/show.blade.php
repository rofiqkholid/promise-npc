@extends('layouts.app')

@section('title', 'Review Master Checksheet')
@section('page_title', 'Master Data / Checksheet Approvals / ' . $product->part_no)

@section('content')
<div class="w-[850px] max-w-full mx-auto flex flex-col" style="height: calc(100vh - 120px);">
    <!-- Header (No Buttons) -->
    <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 px-4 py-3 rounded-t-lg flex justify-between items-center">
        <div>
            <h2 class="text-lg font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-list-check text-blue-500"></i> Review Master Checksheet
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                <strong>Part No:</strong> <span class="text-blue-600 dark:text-blue-400 font-bold">{{ $product->part_no }}</span> | <strong>Name:</strong> {{ $product->part_name }}
            </p>
        </div>
    </div>
    
    <!-- Iframe Container -->
    <div class="flex-1 bg-gray-200 dark:bg-gray-900 overflow-hidden relative border-l border-r border-gray-200 dark:border-gray-700">
        <iframe src="{{ route('checksheets.setup.preview', $product->hashed_id) }}?hide_print=1" frameborder="0" class="w-full h-full bg-gray-100"></iframe>
    </div>
    
    <!-- Bottom Action Bar -->
    <div class="bg-white dark:bg-gray-800 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] border-t border-l border-r border-gray-200 dark:border-gray-700 px-6 py-4 flex justify-between items-center rounded-b-lg relative z-10 gap-4">
        <div class="text-sm text-gray-500 italic truncate hidden sm:block">
            Review before decision.
        </div>
        <div class="flex items-center gap-3 flex-shrink-0 ml-auto">
            @if($canApprove && (optional($product->productDetail)->master_checksheet_status === 'WAITING_APPROVAL' || optional($product->productDetail)->master_checksheet_status === 'DRAFT' || optional($product->productDetail)->master_checksheet_status === null))
                <form id="form-reject" action="{{ route('master.checksheet_approvals.reject', $product->hashed_id) }}" method="POST" class="m-0">
                    @csrf
                    <button type="button" class="inline-flex items-center gap-2 px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded shadow-sm transition" onclick="confirmReject(event, 'form-reject')">
                        <i class="fa-solid fa-times text-lg"></i> Reject
                    </button>
                </form>
                <form action="{{ route('master.checksheet_approvals.approve', $product->hashed_id) }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-8 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white text-sm font-bold rounded shadow-sm transition shadow-blue-500/20" onclick="confirmAction(event, 'Approve this Master Checksheet?')">
                        <i class="fa-solid fa-check text-lg"></i> Approve
                    </button>
                </form>
            @endif
            <a href="{{ route('master.checksheet_approvals.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-bold rounded shadow-sm transition ml-4">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmReject(event, formId) {
        event.preventDefault();
        Swal.fire({
            title: 'Reject Master Checksheet?',
            text: "Please provide a reason for rejection (this will be sent back to the engineering team):",
            input: 'textarea',
            inputPlaceholder: 'Type your reason here...',
            inputAttributes: {
                'aria-label': 'Type your reason here'
            },
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fa-solid fa-times"></i> Yes, Reject it!',
            inputValidator: (value) => {
                if (!value || value.trim() === '') {
                    return 'You need to write a reason for rejection!'
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.getElementById(formId);
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'reject_reason';
                input.value = result.value;
                form.appendChild(input);
                form.submit();
            }
        });
    }
</script>
@endpush
@endsection
