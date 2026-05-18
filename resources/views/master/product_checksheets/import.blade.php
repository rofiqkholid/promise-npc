@extends('layouts.app')

@section('title', 'Import Routing Checksheet')
@section('page_title', 'Import Routing Checksheet')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 max-w-2xl mx-auto">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-file-import text-blue-500"></i> Import Routing Checksheet (Excel)
        </h2>
    </div>

    <form action="{{ route('master.checksheets.import.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="p-6">
            @if(session('error'))
                <div class="mb-4 p-4 text-red-700 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800" role="alert">
                    <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ session('error') }}
                </div>
            @endif

            <div class="mb-6 p-4 border border-blue-200 bg-blue-50 dark:bg-blue-900/10 dark:border-blue-800/50">
                <h3 class="text-sm font-bold text-blue-800 dark:text-blue-300 mb-2">Instructions:</h3>
                <ul class="list-disc pl-5 text-sm text-blue-700 dark:text-blue-400 space-y-1">
                    <li>Download the template file to see the required format.</li>
                    <li>Fill in <strong>PART NO</strong>, <strong>CHECKPOINT NUMBER</strong> (or <strong>POINT CHECK NAME</strong>), and <strong>CUSTOM STANDARD</strong>.</li>
                    <li>Make sure the Part No and Checkpoint already exist in the system.</li>
                    <li><strong>Important:</strong> Uploading will <strong class="text-red-600">REPLACE</strong> all existing checkpoints for any Part No included in the file.</li>
                </ul>
                <div class="mt-4">
                    <a href="{{ route('master.checksheets.import.template') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all hover:scale-105 active:scale-95">
                        <i class="fa-solid fa-download"></i> Download Template
                    </a>
                </div>
            </div>

            <div class="mb-4">
                <label for="file" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Select Excel File (.xlsx, .xls, .csv) <span class="text-red-500">*</span></label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md hover:border-blue-400 dark:hover:border-blue-500 transition-colors bg-gray-50 dark:bg-gray-800/50">
                    <div class="space-y-1 text-center">
                        <i class="fa-solid fa-file-excel text-4xl text-gray-400 dark:text-gray-500 mb-3"></i>
                        <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                            <label for="file" class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 focus-within:outline-none px-2 py-1">
                                <span>Upload a file</span>
                                <input id="file" name="file" type="file" class="sr-only" accept=".xlsx,.xls,.csv" required>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400" id="file-name-display">
                            Excel/CSV up to 10MB
                        </p>
                    </div>
                </div>
                @error('file')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
            <a href="{{ route('master.checksheets.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm text-sm font-medium">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm font-semibold flex items-center gap-2 text-sm">
                <i class="fa-solid fa-upload"></i> Process Import
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('file').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var display = document.getElementById('file-name-display');
        display.textContent = 'Selected: ' + fileName;
        display.classList.add('text-blue-600', 'font-semibold');
    });
</script>
@endpush
