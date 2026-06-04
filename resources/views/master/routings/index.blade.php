@extends('layouts.app')

@section('title', 'Master Data Routing')
@section('page_title', 'Master Data / Routing (Flow Process)')

@push('styles')
    <style>
        .sortable-item:last-child .process-arrow {
            display: none;
        }
        .sortable-ghost {
            opacity: 0.4;
        }
    </style>
@endpush

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-route text-blue-500"></i> Register Master Routing
        </h2>
        <div class="flex items-center gap-2">
            <a href="{{ route('master.routings.import') }}" class="px-4 py-2 bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-md shadow-emerald-500/20 font-medium text-sm flex items-center gap-2">
                <i class="fa-solid fa-file-import"></i> Import Excel
            </a>
            <a href="{{ route('master.routings.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add Routing
            </a>
        </div>
    </div>

    <div class="p-6">

        <div class="overflow-x-auto">
            <table id="routingsTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16">#</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Part Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Flow Process (Routing)</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-32">Action</th>
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
    $(document).ready(function() {
        let table = initPromiseDataTable('#routingsTable', {
            ajax: "{{ route('master.routings.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-6 py-4 text-slate-800 dark:text-slate-200 text-sm' },
                { data: 'part_no', name: 'part_no', className: 'px-6 py-4 text-sm' },
                { data: 'part_name', name: 'part_name', className: 'px-6 py-4' },
                { data: 'flow_process', name: 'flow_process', className: 'px-6 py-4 text-slate-700 dark:text-slate-300 text-sm', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-6 py-4 text-right' }
            ],
            drawCallback: function(settings) {
                // Re-initialize Sortable after each draw
                if (typeof Sortable !== 'undefined') {
                    document.querySelectorAll('.sortable-container').forEach(container => {
                        new Sortable(container, {
                            handle: '.cursor-move',
                            animation: 150,
                            ghostClass: 'sortable-ghost',
                            onEnd: window.sortableOnEndFunction
                        });
                    });
                }
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        window.sortableOnEndFunction = function (evt) {
            const itemEl = evt.item;
            const parentEl = evt.to;
            const partId = parentEl.getAttribute('data-part-id');
            
            // Collect new order
            const order = [];
            parentEl.querySelectorAll('.sortable-item').forEach(item => {
                order.push(item.getAttribute('data-id'));
            });

            // Send AJAX
            fetch("{{ route('master.routings.reorder') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ order: order })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Show tiny success toast using the layout's Toast mechanism if available, or just ignore since it's saved.
                } else {
                    alert('Failed to save new sequence.');
                }
            })
            .catch(err => {
                console.error('Error reordering:', err);
                alert('Terjadi kesalahan saat menghubungi server.');
            });
        };
    });
</script>
@endpush
