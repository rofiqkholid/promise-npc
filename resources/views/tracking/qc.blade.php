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

    <!-- Bulk Print Bar -->
    <div x-data="{
        activePhotoModal: null,
        selectedParts: [],
        selectAll: false,
        toggleAll(event) {
            const isChecked = event.target.checked;
            const checkboxes = document.querySelectorAll('.part-checkbox');
            if (isChecked) {
                let ids = [];
                checkboxes.forEach(cb => ids.push(cb.value));
                this.selectedParts = [...new Set([...this.selectedParts, ...ids])];
            } else {
                let idsToRemove = [];
                checkboxes.forEach(cb => idsToRemove.push(cb.value));
                this.selectedParts = this.selectedParts.filter(id => !idsToRemove.includes(id));
            }
        },
        togglePart(id) {
            if (this.selectedParts.includes(id)) {
                this.selectedParts = this.selectedParts.filter(i => i !== id);
                this.selectAll = false;
                document.getElementById('selectAllParts').checked = false;
            } else {
                this.selectedParts.push(id);
            }
        },
        init() {
            // Re-bind Alpine togglePart to window so datatables can call it
            window.togglePart = (id) => this.togglePart(id);
            
            // Uncheck Select All when table redraws
            $('#qcTable').on('draw.dt', () => {
                this.selectAll = false;
                let selectAllCb = document.getElementById('selectAllParts');
                if(selectAllCb) selectAllCb.checked = false;
                // Re-evaluate current page checkboxes to see if all are checked
                this.$nextTick(() => {
                    const checkboxes = document.querySelectorAll('.part-checkbox');
                    if (checkboxes.length > 0) {
                        let allChecked = true;
                        checkboxes.forEach(cb => {
                            if (!this.selectedParts.includes(cb.value)) allChecked = false;
                        });
                        if(allChecked) {
                            this.selectAll = true;
                            if(selectAllCb) selectAllCb.checked = true;
                        }
                    }
                });
            });
        }
    }">
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

        <!-- Table -->
        <div class="p-6">
            <div class="mb-4 flex flex-col sm:flex-row gap-2" x-data="{
                searchQuery: '{{ request('search') }}',
                customerFilter: '{{ request('customer_filter') }}',
                modelFilter: '{{ request('model_filter') }}',
                statusFilter: '{{ request('status_filter') }}',
                performSearch() {
                    let table = $('#qcTable').DataTable();
                    table.ajax.url('{{ route('tracking.qc') }}?search=' + encodeURIComponent(this.searchQuery) + 
                              '&customer_filter=' + encodeURIComponent(this.customerFilter) + 
                              '&model_filter=' + encodeURIComponent(this.modelFilter) + 
                              '&status_filter=' + encodeURIComponent(this.statusFilter)).load();
                }
            }">
                <div class="w-full sm:w-48">
                    <select x-model="customerFilter" @change="performSearch()" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-none">
                        <option value="">All Customers</option>
                        @foreach($customers ?? [] as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full sm:w-48">
                    <select x-model="modelFilter" @change="performSearch()" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-none">
                        <option value="">All Models</option>
                        @foreach($models ?? [] as $mod)
                            <option value="{{ $mod->id }}" x-show="!customerFilter || '{{ $mod->customer_id }}' == customerFilter">{{ $mod->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if(isset($status_options) && count($status_options) > 1)
                <div class="w-full sm:w-48">
                    <select x-model="statusFilter" @change="performSearch()" class="py-2 px-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-full transition shadow-sm rounded-none">
                        <option value="">All Statuses</option>
                        @foreach($status_options as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
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
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        // Alpine data removed as we merged activePhotoModal into the main x-data wrapper
    });

    $(document).ready(function() {
        $('#qcTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('tracking.qc') }}",
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
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'px-4 py-2 text-center align-middle' },
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-2 text-center text-slate-800 dark:text-slate-200 text-[13px] font-medium' },
                { data: 'part_info', name: 'product.part_no', className: 'px-4 py-2 align-top', orderable: false, searchable: false },
                { data: 'status_po', name: 'status', className: 'px-4 py-2 text-center align-middle', orderable: false, searchable: false },
                { data: 'qc_progress', name: 'qc_progress', className: 'px-4 py-2 text-center align-middle', orderable: false, searchable: false },
                { data: 'action_qc', name: 'action', className: 'px-4 py-2 text-right align-middle pointer-events-auto', orderable: false, searchable: false }
            ],
            drawCallback: function() {
                // Style pagination buttons exactly like Activity Logs
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
                    
                // Fix classes applied by DataTables dynamically
                $('#qcTable_paginate a').each(function() {
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