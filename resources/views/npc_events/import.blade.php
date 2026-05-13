@extends('layouts.app')

@section('title', 'Import Data Event & Parts')
@section('page_title', 'Master Data / Import Event')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700 max-w-3xl mx-auto">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            <i class="fa-solid fa-file-excel text-green-600 mr-2"></i> Import PO / Data Excel
        </h2>
    </div>

    <form action="{{ route('events.import.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="p-6 space-y-6">

            <div class="bg-blue-50 dark:bg-blue-900/30 p-4 border border-blue-100 dark:border-blue-800 text-sm text-blue-800 dark:text-blue-300">
                <div class="flex justify-between items-center mb-3">
                    <div>
                        <p class="font-bold text-base flex items-center gap-2">
                            <i class="fa-solid fa-file-circle-check text-blue-600 dark:text-blue-400"></i>
                            Import Instructions
                        </p>
                    </div>
                    <a href="{{ route('events.import.template') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all hover:scale-105 active:scale-95">
                        <i class="fa-solid fa-download"></i> Download Template
                    </a>
                </div>
                
                <div class="grid grid-cols-1 gap-4 mt-2">
                    <div class="flex gap-3">
                        <div class="w-6 h-6 bg-blue-100 dark:bg-blue-800 flex items-center justify-center shrink-0 font-bold text-xs text-blue-600 dark:text-blue-300">1</div>
                        <p class="text-xs leading-relaxed"><strong>Bulk Support</strong>: You can now import multiple Customers, Models, and POs in a single file. The system will automatically create separate Events for each combination of Customer Category and Delivery Group found in the Excel.</p>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 h-6 bg-blue-100 dark:bg-blue-800 flex items-center justify-center shrink-0 font-bold text-xs text-blue-600 dark:text-blue-300">2</div>
                        <p class="text-xs leading-relaxed"><strong>Data Matching</strong>: Ensure <code>CUSTOMER CODE</code>, <code>MODEL NAME</code>, <code>EVENT CATEGORY</code>, and <code>DELIVERY GROUP</code> match exactly with the names in Master Data.</p>
                    </div>
                    <div class="flex gap-3">
                        <div class="w-6 h-6 bg-blue-100 dark:bg-blue-800 flex items-center justify-center shrink-0 font-bold text-xs text-blue-600 dark:text-blue-300">3</div>
                        <p class="text-xs leading-relaxed"><strong>Mandatory Fields</strong>: <code>PO NO</code>, <code>PART NO</code>, <code>QTY</code>, <code>DELV DATE</code>, <code>CUSTOMER CODE</code>, <code>EVENT CATEGORY</code>, and <code>DELIVERY GROUP</code> are required for each row.</p>
                    </div>
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <!-- File Upload -->
            <div class="space-y-1">
                <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 text-center mb-4">
                    Upload Bulk Excel File <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 flex justify-center px-6 pt-10 pb-10 border-2 border-gray-300 dark:border-gray-600 border-dashed bg-gray-50/50 dark:bg-gray-700/30 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors cursor-pointer" onclick="document.getElementById('file').click()">
                    <div class="space-y-1 text-center">
                        <i class="fa-solid fa-file-excel text-5xl text-green-500 mb-4 animate-bounce-slow"></i>
                        <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                            <label for="file" class="relative cursor-pointer font-bold text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                <span>Click to select bulk Excel file</span>
                                <input id="file" name="file" type="file" class="sr-only" required accept=".xlsx, .xls, .csv">
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Support .xlsx, .xls, .csv (Max 10MB)</p>
                        <p id="file-name-display" class="text-base font-black text-emerald-600 dark:text-emerald-400 mt-4 hidden p-2 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800"></p>
                    </div>
                </div>
            </div>

        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
            <a href="{{ route('events.index') }}" class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-md shadow-blue-500/20 text-sm font-medium hover:from-blue-700 hover:to-cyan-700 transition flex items-center gap-2" onclick="confirmAction(event, 'Start import process to database? This will create an Event and insert Parts in it.')">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload & Eksekusi
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Logika menampilkan nama file Excel saat diselect
    document.getElementById('file').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        const display = document.getElementById('file-name-display');
        if (fileName) {
            display.textContent = "Selected File: " + fileName;
            display.classList.remove('hidden');
        } else {
            display.classList.add('hidden');
        }
    });
</script>
@endpush

