@extends('layouts.app')

@section('title', $pageTitle ?? 'Quality Control (QC)')
@section('page_title', 'Transaction / ' . ($pageTitle ?? 'Quality Control (QC)'))

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid {{ $pageIcon ?? 'fa-microscope' }} text-blue-500"></i> {{ $pageTitle ?? 'Quality Control (QC)' }}
        </h2>
        @if(isset($pageDesc))
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-7">{{ $pageDesc }}</p>
        @endif
    </div>

    <!-- Table -->
    <div class="p-6" x-data="qcPage">
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table id="qcTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-64">Part Info / PO</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center w-32">Status PO</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-center">QC Progress</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-48">Action QC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- DataTables will fill this -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('qcPage', () => ({
            activePhotoModal: null
        }));
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
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-6 py-4 text-center text-slate-800 dark:text-slate-200 text-sm font-medium' },
                { data: 'part_info', name: 'product.part_no', className: 'px-6 py-4 align-top', orderable: false, searchable: false },
                { data: 'status_po', name: 'status', className: 'px-6 py-4 text-center align-middle', orderable: false, searchable: false },
                { data: 'qc_progress', name: 'qc_progress', className: 'px-6 py-4 text-center align-middle', orderable: false, searchable: false },
                { data: 'action_qc', name: 'action', className: 'px-6 py-4 text-right align-middle pointer-events-auto', orderable: false, searchable: false }
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