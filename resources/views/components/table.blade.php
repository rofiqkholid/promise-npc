<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 overflow-hidden relative">
    <div class="p-4 bg-white dark:bg-gray-800">
        <table {{ $attributes->merge(['class' => 'custom-table w-full text-left border-collapse']) }}>
            {{ $slot }}
        </table>
    </div>
</div>

@once
<style>
    /* Global DataTables Sorting Arrows styling */
    table.dataTable thead th { position: relative; cursor: pointer; }
    table.dataTable thead th.sorting:after, table.dataTable thead th.sorting_asc:after, table.dataTable thead th.sorting_desc:after {
        font-family: "Font Awesome 6 Free"; font-weight: 900; position: absolute; right: 8px; top: 50%; transform: translateY(-50%); font-size: 0.75rem; color: #9ca3af; opacity: 0.5;
    }
    table.dataTable thead th.sorting:after { content: "\f0dc"; } 
    table.dataTable thead th.sorting_asc:after { content: "\f0de"; opacity: 1; color: #4b5563; }
    table.dataTable thead th.sorting_desc:after { content: "\f0dd"; opacity: 1; color: #4b5563; }
    table.dataTable thead th.no-sort { cursor: default; }
    table.dataTable thead th.no-sort:after { display: none; }
</style>
<script>
    /**
     * Global DataTable Helper (Elegant Minimalist Template)
     */
    window.defaultDataTable = function (selector, userConfig = {}) {
        if (typeof $ === 'undefined') return console.error('jQuery required');

        const defaults = {
            processing: true,
            serverSide: false,
            responsive: true,
            lengthChange: false, // Hide "Show 10 entries" globally
            info: false,         // Hide "Showing 1 to X" globally
            searching: true,     // Keep search globally unless overridden
            pageLength: 10,
            stripeClasses: ['bg-white dark:bg-gray-800', 'bg-gray-50 dark:bg-gray-750'], // Native zebra striping
            dom: "<'flex justify-end mb-4'f>rt<'flex justify-center mt-6 mb-2'p>", // Clean DOM
            language: {
                search: "",
                searchPlaceholder: "Search records...",
                paginate: { previous: "Previous", next: "Next" },
                emptyTable: `<div class="py-12 text-center text-gray-500"><i class="fa-solid fa-ghost text-4xl text-gray-300 mb-3 block"></i><p class="font-medium text-lg">No records found</p></div>`,
                zeroRecords: `<div class="py-12 text-center text-gray-500"><i class="fa-solid fa-magnifying-glass text-4xl text-gray-300 mb-3 block"></i><p class="font-medium text-lg">No matching results</p></div>`
            },
            drawCallback: function() {
                // Apply Tailwind styles to Pagination (Joined blocks, Gray active state)
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
                    
                // Clean up generic search box
                $('.dataTables_filter input').addClass('block w-64 shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:text-white py-1.5 px-3 ml-2');
                $('.dataTables_filter label').addClass('flex items-center text-sm font-medium text-gray-700');
                
                // Fix classes applied dynamically
                $(selector + '_wrapper .dataTables_paginate a').each(function() {
                    $(this).removeClass('paginate_button');
                });
            }
        };

        const options = $.extend(true, {}, defaults, userConfig);
        
        // CSRF Token Injection
        if (options.ajax && typeof options.ajax === 'object') {
            options.ajax.headers = { ...options.ajax.headers, 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') };
        }

        const dt = $(selector).DataTable(options);

        return dt;
    };
</script>
@endonce

