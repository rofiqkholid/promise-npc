<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Quality Label - {{ optional($part->product)->part_no }}</title>
    <!-- Use Tailwind via CDN for quick styling in print view if not built into this specific route, but assume app.css is available -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page {
            /* Adjust page size to standard label size or A4 depending on printer. Assuming a standard label or 4 labels per page. */
            /* Using standard size, but letting the printer dialog handle it is usually safer. */
            margin: 0;
        }
        body {
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px;
        }
        .label-container {
            width: 100%;
            max-width: 400px;
            height: 250px; /* fixed height for consistent label size */
            background-color: white;
            border: 2px solid #000;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .label-container {
                margin: 10px auto; /* Center on page */
                border: 1px solid #000;
                page-break-after: always; /* Force 1 label per page if they use a label printer */
                /* If using A4 paper, they might want multiple per page, so page-break-after: auto might be better, but "sesuai qty" often implies a roll of labels. */
            }
            /* Remove the page break on the last item to avoid a blank page */
            .label-container:last-child {
                page-break-after: auto;
            }
            /* Hide UI elements not meant for printing */
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="antialiased text-gray-900 flex flex-wrap justify-center gap-4">

    <!-- Control bar (hidden when printing) -->
    <div class="w-full text-center mb-6 no-print">
        <div class="bg-white p-4 shadow-sm border border-gray-200 inline-block">
            <h2 class="text-lg font-bold mb-2">Quality Label Print Preview</h2>
            <p class="text-sm text-gray-600 mb-4">Total Qty: {{ $part->qty }} PCS. <br>This will generate {{ $part->qty }} labels.</p>
            <button onclick="window.print()" class="px-6 py-2 bg-blue-600 text-white font-bold shadow hover:bg-blue-700 transition">
                <i class="fa-solid fa-print mr-2"></i> Print Labels Now
            </button>
            <button onclick="window.close()" class="px-6 py-2 bg-gray-200 text-gray-700 font-bold shadow ml-2 hover:bg-gray-300 transition">
                Close
            </button>
        </div>
    </div>

    @for($i = 1; $i <= $part->qty; $i++)
        <div class="label-container">
            <!-- Header -->
            <div class="flex justify-between items-start border-b-2 border-black pb-2 mb-2">
                <div>
                    <h1 class="text-xl font-black uppercase tracking-wider leading-none">QUALITY PASSED</h1>
                    <p class="text-xs font-bold text-gray-600 mt-1">PT. Summit Adyawinsa Indonesia</p>
                </div>
                <div class="bg-black text-white px-3 py-1 font-bold text-sm">
                    QC
                </div>
            </div>

            <!-- Body -->
            <div class="flex-1 flex flex-col justify-center">
                <div class="flex justify-between items-end mb-2">
                    <span class="text-[10px] font-bold text-gray-500 uppercase">Part No:</span>
                </div>
                <div class="text-2xl font-black leading-none mb-1">{{ optional($part->product)->part_no ?? '-' }}</div>
                <div class="text-sm font-semibold text-gray-700 mb-3 truncate">{{ optional($part->product)->part_name ?? '-' }}</div>
                
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <div>
                        <span class="text-[9px] font-bold text-gray-500 uppercase block">Model/Event:</span>
                        <span class="font-bold block truncate" title="{{ optional(optional($part->product)->vehicleModel)->name ?? (optional(optional($part->event)->customerCategory)->name ?? '-') }}">{{ optional(optional($part->product)->vehicleModel)->name ?? (optional(optional($part->event)->customerCategory)->name ?? '-') }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-gray-500 uppercase block">PO Number:</span>
                        <span class="font-bold block truncate" title="{{ optional($part->event)->po_no ?? '-' }}">{{ optional($part->event)->po_no ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-[9px] font-bold text-gray-500 uppercase block">ECN No:</span>
                        <span class="font-bold block truncate" title="{{ optional(optional(optional($part->product)->docPackage)->currentRevision)->ecn_no ?? '-' }}">{{ optional(optional(optional($part->product)->docPackage)->currentRevision)->ecn_no ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t-2 border-black pt-2 mt-2 flex justify-between items-end">
                <div>
                    <span class="text-[9px] font-bold text-gray-500 uppercase block">Inspected By & Date:</span>
                    <div class="text-xs font-bold">
                        {{ optional(optional($part->checksheet)->qeChecker)->name ?? 'QC Inspector' }} 
                        <span class="font-normal">|</span> 
                        {{ optional($part->checksheet)->qe_check_date ? \Carbon\Carbon::parse($part->checksheet->qe_check_date)->format('d M Y') : \Carbon\Carbon::now()->format('d M Y') }}
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-[9px] font-bold text-gray-500 uppercase block">Piece No:</span>
                    <div class="text-lg font-black tracking-widest">{{ $i }}<span class="text-xs text-gray-500 font-bold">/{{ $part->qty }}</span></div>
                </div>
            </div>
        </div>
    @endfor

    <!-- FontAwesome for the print icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        // Automatically trigger print dialog when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
