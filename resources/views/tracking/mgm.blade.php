@extends('layouts.app')

@section('title', $pageTitle ?? 'Management Check')
@section('page_title', 'Transaction / ' . ($pageTitle ?? 'Management Check'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-user-tie' }} text-blue-500"></i> {{ $pageTitle ?? 'Management Check' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    <!-- Table -->
    <div class="p-6">

        <!-- Filters -->
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
                <div class="flex items-end">
                    <button type="button" id="clearFiltersBtn" class="py-2 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium transition shadow-sm flex items-center gap-2 w-full justify-center">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </button>
                </div>
            </div>

            <div class="flex items-end w-full md:w-auto">
                <div class="relative w-full md:w-80">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </div>
                    <input type="text" id="searchInput"
                        value="{{ request('search') }}"
                        placeholder="Search Part No, Part Name, PO No..."
                        class="!pl-10 !pr-10 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-md">
                    <button type="button" id="clearSearchBtn" style="display:none;"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-red-500 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-4 py-2 font-semibold w-16">No</th>
                        <th scope="col" class="px-4 py-2 font-semibold w-72">Product Identity</th>
                        <th scope="col" class="px-4 py-2 font-semibold text-center">Quality Validation Status (QC)</th>
                        <th scope="col" class="px-4 py-2 font-semibold text-right w-48">Final Validation (MGM)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($parts as $part)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition text-sm">
                        <td class="px-4 py-2 text-slate-800 dark:text-slate-200 text-[13px]">
                            {{ ($parts->currentPage() - 1) * $parts->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-2">
                            <div class="text-gray-800 dark:text-gray-200 font-bold text-sm">{{ optional($part->product)->part_no }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1.5">{{ optional($part->product)->part_name }}</div>
                            <div class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50 dark:bg-gray-700 px-2 py-0.5 inline-block border border-gray-200 dark:border-gray-600 mb-2">PO: {{ optional($part->event)->po_no }} | MODEL: {{ optional($part->product->vehicleModel)->name ?? 'Unknown Model' }}</div>
                            <div class="text-gray-800 dark:text-gray-300 font-black flex items-center gap-1.5"><i class="fa-solid fa-boxes-stacked text-gray-400"></i> {{ number_format($part->qty) }} <span class="text-xs font-semibold text-gray-500">PCS</span></div>
                        </td>
                        <td class="px-4 py-2 text-center align-middle">
                            @if(in_array($part->status, ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK']))
                                <div class="inline-flex flex-col items-center gap-1.5 px-3 py-2 bg-slate-50 border border-slate-200 text-[10px] text-slate-500 italic">
                                    <i class="fa-solid fa-microscope text-sm"></i> Currently in QC Inspection
                                </div>
                            @else
                                <div class="flex flex-col items-center gap-1">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 border border-green-200 text-green-700 text-[10px] font-bold shadow-sm"><i class="fa-solid fa-check-double"></i> PASSED QC CERTIFICATION</span>
                                    <span class="text-[11px] text-gray-500 font-medium mt-1">Date Input: {{ $part->qc_target_date ? \Carbon\Carbon::parse($part->qc_target_date)->format('d M Y') : '-' }}</span>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right align-middle pointer-events-auto">
                            <div class="flex flex-col items-end gap-2">
                                @if(in_array($part->status, ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK']))
                                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed w-full max-w-[150px]">
                                        <i class="fa-solid fa-lock text-[8px]"></i> Not Yet Registered in MGM
                                    </div>
                                @elseif($part->status === 'WAITING_MGM_CHECK')
                                    <a href="{{ route('checksheets.create', $part->hashed_id) }}" class="inline-flex px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white shadow-sm font-bold transition items-center justify-center gap-2 text-[11px] w-full max-w-[150px]" style="background-color: #a855f7;">
                                        <i class="fa-solid fa-user-check"></i> MGM Checksheet Form
                                    </a>
                                    @if($part->checksheet)
                                    <a href="{{ route('checksheets.export', $part->checksheet->hashed_id) }}" class="inline-flex px-4 py-2 bg-green-500 hover:bg-green-600 text-white shadow-sm font-bold transition items-center justify-center gap-2 text-[11px] w-full max-w-[150px]">
                                        <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a>

                                    @endif
                                    <p class="text-[9px] text-gray-400 mt-1 italic text-right max-w-[150px] text-balance">Review checksheet and sign the FG parts check</p>
                                @else
                                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 text-[10px] text-gray-400 italic flex items-center justify-center gap-1.5 cursor-not-allowed w-full max-w-[150px]">
                                        <i class="fa-solid fa-lock text-[8px]"></i> Completed
                                    </div>
                                    @if(in_array($part->status, ['WAITING_APPROVAL', 'FINISHED']) && $part->delivered_qty == 0)
                                    @php
                                        $checksheet = $part->checksheet;
                                        $canRollback = !$checksheet || $checksheet->approval_status === null || $checksheet->approval_status === 'WAITING_MGM_STAFF';
                                    @endphp
                                    @if($canRollback)
                                    <form action="{{ route('tracking.mgm.rollback', $part->hashed_id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold transition mt-1" onclick="confirmAction(event, 'Are you sure you want to rollback this part to MGM Check stage?')">
                                            <i class="fa-solid fa-rotate-left"></i> Rollback MGM
                                        </button>
                                    </form>
                                    @endif
                                    @endif
                                    @if($part->checksheet)
                                    <a href="{{ route('checksheets.export', $part->checksheet->hashed_id) }}" class="inline-flex px-4 py-2 bg-green-500 hover:bg-green-600 text-white shadow-sm font-bold transition items-center justify-center gap-2 text-[11px] w-full max-w-[150px]">
                                        <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a>

                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-solid fa-user-tie text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No management check (MGM) submissions currently.</p>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let debounceTimer;
    
        function performSearch() {
            let searchQuery = $('#searchInput').val();
            let customerFilter = $('#customerFilter').val();
            let modelFilter = $('#modelFilter').val();
            
            let url = '{{ route('tracking.mgm') }}?search=' + encodeURIComponent(searchQuery || '') + 
                      '&customer_filter=' + encodeURIComponent(customerFilter || '') + 
                      '&model_filter=' + encodeURIComponent(modelFilter || '');
                      
            fetch(url)
            .then(res => res.text())
            .then(html => {
                let doc = new DOMParser().parseFromString(html, 'text/html');
                document.querySelector('tbody').innerHTML = doc.querySelector('tbody').innerHTML;
                let pagination = document.querySelector('.p-4.border-t nav');
                let newPagination = doc.querySelector('.p-4.border-t nav');
                if(pagination && newPagination) pagination.parentElement.innerHTML = newPagination.parentElement.innerHTML;
                window.history.pushState(null, '', url);
            })
            .catch(err => console.error(err));
        }

        $('#searchInput').on('input', function() {
            if ($(this).val().length > 0) {
                $('#clearSearchBtn').show();
            } else {
                $('#clearSearchBtn').hide();
            }
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(performSearch, 500);
        });
        
        if ($('#searchInput').val() && $('#searchInput').val().length > 0) {
            $('#clearSearchBtn').show();
        }
        
        $('#clearSearchBtn').on('click', function(e) {
            e.preventDefault();
            $('#searchInput').val('');
            $(this).hide();
            performSearch();
            $('#searchInput').focus();
        });

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
            
            // If the currently selected model is now disabled, reset it
            if ($('#modelFilter option:selected').prop('disabled')) {
                $('#modelFilter').val('').trigger('change.select2');
            }
            performSearch();
        });

        $('#modelFilter').on('change', function(e) {
            performSearch();
        });

        $('#clearFiltersBtn').on('click', function(e) {
            e.preventDefault();
            $('#searchInput').val('');
            $('#clearSearchBtn').hide();
            
            $('#modelFilter').val('');
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
