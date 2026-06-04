@extends('layouts.app')

@section('title', 'Master STD Parts')
@section('page_title', 'Master Data / STD Parts')

@section('content')
<div x-data="{ showImportModal: false }">
    <div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-screwdriver-wrench text-blue-500"></i> Master STD Part Register
            </h2>
            <div class="flex gap-2">
                <button type="button" @click="showImportModal = true" class="px-4 py-2 bg-white text-gray-700 dark:bg-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm font-medium text-sm flex items-center gap-2 rounded-none">
                    <i class="fa-solid fa-file-excel text-green-600"></i> Import Excel
                </button>
                <a href="{{ route('master.std-parts.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2 rounded-none">
                    <i class="fa-solid fa-plus"></i> Add New STD Part
                </a>
            </div>
        </div>

        <div class="overflow-x-auto border-t border-gray-200 dark:border-gray-700">
            <table id="stdPartsTable" class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold w-16 text-center">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Name</th>
                        <th scope="col" class="px-6 py-4 font-semibold w-24">Status</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right w-24">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables will fill this -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Import Modal -->
    <div x-show="showImportModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" x-cloak style="display: none;">
        <div x-show="showImportModal" @click.away="showImportModal = false" x-transition class="bg-white dark:bg-gray-800 w-full max-w-md shadow-xl border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Import STD Parts</h3>
                <button @click="showImportModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Upload an Excel file (.xlsx, .xls) or CSV to bulk import STD Parts. Please use the provided template to ensure correct formatting.</p>
                    <a href="{{ route('master.std-parts.import.template') }}" class="inline-flex items-center gap-2 px-3 py-1.5 border border-green-600 text-green-600 hover:bg-green-50 dark:border-green-500 dark:text-green-400 dark:hover:bg-green-900/30 text-sm font-medium transition rounded-none">
                        <i class="fa-solid fa-download"></i> Download Template
                    </a>
                </div>
                
                <form action="{{ route('master.std-parts.import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select File <span class="text-red-500">*</span></label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                            class="block w-full text-sm text-gray-500 dark:text-gray-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-none file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-200 cursor-pointer">
                    </div>
                    
                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="button" @click="showImportModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition text-sm rounded-none">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 transition text-sm flex items-center gap-2 rounded-none">
                            <i class="fa-solid fa-upload"></i> Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        initPromiseDataTable('#stdPartsTable', {
            ajax: "{{ route('master.std-parts.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-6 py-4 font-bold text-center text-indigo-600 dark:text-indigo-400' },
                { data: 'name', name: 'name', className: 'px-6 py-4 font-semibold text-slate-900 dark:text-white' },
                { data: 'is_active', name: 'is_active', className: 'px-6 py-4', searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-6 py-4 text-right' }
            ]
        });
    });
</script>
@endpush
