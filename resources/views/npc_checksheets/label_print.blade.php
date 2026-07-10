<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $printParts = isset($parts) ? $parts : (isset($part) ? collect([$part]) : collect([]));
        $titlePartNo = isset($part) ? optional($part->product)->part_no : 'Bulk';
    @endphp
    <title>Print Quality Label - {{ $titlePartNo }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ===== SCREEN STYLES ===== */
        body {
            background-color: #e5e7eb;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        .no-print {
            background: #1e293b;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .no-print h2 {
            color: #f1f5f9;
            font-size: 16px;
            font-weight: 700;
            margin: 0;
        }

        .no-print .info-text {
            color: #94a3b8;
            font-size: 13px;
            margin: 0;
        }

        .no-print .controls {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .no-print label {
            color: #cbd5e1;
            font-size: 13px;
            font-weight: 600;
        }

        .no-print select {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #475569;
            background: #334155;
            color: #f1f5f9;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-print {
            padding: 8px 20px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-print:hover { background: #1d4ed8; }

        .btn-close {
            padding: 8px 16px;
            background: #475569;
            color: #e2e8f0;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-close:hover { background: #64748b; }

        /* ===== LABEL GRID WRAPPER ===== */
        #labels-wrapper {
            padding: 24px;
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* default: 2 per row */
            gap: 16px;
            max-width: 900px;
            margin: 0 auto;
        }

        /* ===== SINGLE LABEL ===== */
        .label-card {
            background: white;
            border: 2px solid #111;
            border-radius: 6px;
            padding: 14px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 380px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #111;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .label-header h1 {
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 2px 0;
            line-height: 1;
        }

        .label-header .company {
            font-size: 9px;
            font-weight: 700;
            color: #6b7280;
        }

        .label-badge {
            background: #111;
            color: white;
            padding: 4px 10px;
            font-weight: 700;
            font-size: 12px;
            border-radius: 3px;
            flex-shrink: 0;
        }

        .label-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .part-no {
            font-size: 20px;
            font-weight: 900;
            line-height: 1;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }

        .part-name {
            font-size: 11px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .label-fields {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
        }

        .field-label {
            font-size: 8px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            display: block;
            margin-bottom: 1px;
        }

        .field-value {
            font-size: 10px;
            font-weight: 700;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .label-footer {
            border-top: 2px solid #111;
            padding-top: 8px;
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .label-footer .field-label { margin-bottom: 2px; }

        .piece-no {
            font-size: 18px;
            font-weight: 900;
            letter-spacing: 2px;
            line-height: 1;
        }

        .piece-total {
            font-size: 10px;
            font-weight: 700;
            color: #6b7280;
        }

        .img-wrapper {
            width: 100%;
            text-align: center;
            margin: 4px 0;
            min-height: 75px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .part-img {
            max-height: 70px;
            max-width: 100%;
            object-fit: contain;
            display: inline-block;
        }

        /* ===== SCALING FOR 3 & 4 COLUMNS ===== */
        .cols-3 .label-card { padding: 10px; height: 290px; }
        .cols-3 .label-header h1 { font-size: 12px; }
        .cols-3 .label-header .company { font-size: 7px; }
        .cols-3 .label-badge { font-size: 9px; padding: 3px 6px; }
        .cols-3 .part-no { font-size: 16px; }
        .cols-3 .part-name { font-size: 9px; }
        .cols-3 .field-label { font-size: 6px; }
        .cols-3 .field-value { font-size: 8px; }
        .cols-3 .piece-no { font-size: 14px; }
        .cols-3 .piece-total { font-size: 8px; }
        .cols-3 .signature-box { width: 90px !important; height: 50px !important; }
        .cols-3 .img-wrapper { min-height: 45px !important; margin: 2px 0 !important; }
        .cols-3 .part-img { max-height: 40px !important; }

        .cols-4 .label-card { padding: 8px; height: 250px; }
        .cols-4 .label-header h1 { font-size: 10px; }
        .cols-4 .label-header .company { font-size: 6px; }
        .cols-4 .label-badge { font-size: 8px; padding: 2px 4px; }
        .cols-4 .part-no { font-size: 13px; }
        .cols-4 .part-name { font-size: 8px; margin-bottom: 4px; }
        .cols-4 .field-label { font-size: 5px; }
        .cols-4 .field-value { font-size: 7px; }
        .cols-4 .piece-no { font-size: 12px; }
        .cols-4 .piece-total { font-size: 7px; }
        .cols-4 .signature-box { width: 70px !important; height: 40px !important; }
        .cols-4 .signature-box span { font-size: 5px !important; }
        .cols-4 .img-wrapper { min-height: 35px !important; margin: 2px 0 !important; }
        .cols-4 .part-img { max-height: 30px !important; }

        /* ===== PRINT STYLES ===== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 10mm 10mm 10mm;
            }

            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            #labels-wrapper {
                padding: 0;
                margin: 0;
                max-width: 100%;
                width: 100%;
                gap: 6mm;
            }

            /* Grid kolom saat print dikontrol oleh JS via inline style */

            .label-card {
                border: 1.5px solid #111;
                border-radius: 4px;
                padding: 8px;
                break-inside: avoid;
                page-break-inside: avoid;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body class="bg-gray-200 text-gray-800">

    <!-- Top Bar (No Print) -->
    <div class="no-print bg-slate-800 text-slate-200 py-3 px-6 shadow-md flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <i class="fa-solid fa-tags text-blue-400 text-xl"></i>
            <div>
                <h1 class="font-bold text-lg leading-tight">Quality Label Print Preview</h1>
                <p class="text-xs text-slate-400">
                    @if(isset($part))
                        Part: <strong class="text-white">{{ optional($part->product)->part_no ?? 'UNKNOWN' }}</strong> | 
                        Total Qty: <strong class="text-white">{{ $part->qty }} PCS</strong> | 
                        {{ $part->qty }} labels will be printed
                    @else
                        Multiple Parts Selected | Total Labels: <strong class="text-white">{{ $printParts->sum('qty') }}</strong>
                    @endif
                </p>
            </div>
        </div>
        <div class="controls">
            <label for="cols-select"><i class="fa-solid fa-grip mr-1"></i> Labels per row:</label>
            <select id="cols-select" onchange="setColumns(this.value)">
                <option value="1">1 label / row</option>
                <option value="2" selected>2 labels / row</option>
                <option value="3">3 labels / row</option>
                <option value="4">4 labels / row</option>
            </select>
            <button class="btn-print" onclick="window.print()">
                <i class="fa-solid fa-print mr-2"></i> Print Now
            </button>
            <button class="btn-close" onclick="window.history.back()">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </button>
        </div>
    </div>

    <!-- ===== LABELS GRID ===== -->
    <div id="labels-wrapper" class="cols-2">
        @foreach($printParts as $part)
        @for($i = 1; $i <= $part->qty; $i++)
        <div class="label-card">
            <!-- Header -->
            <div class="label-header">
                <div>
                    <h1>QUALITY PASSED</h1>
                    <span class="company">PT. Summit Adyawinsa Indonesia</span>
                </div>
                <span class="label-badge">QC</span>
            </div>

            <!-- Body -->
            <div class="label-body">
                <div class="part-no">{{ optional($part->product)->part_no ?? '-' }}</div>
                <div class="part-name" title="{{ optional($part->product)->part_name ?? '-' }}">{{ optional($part->product)->part_name ?? '-' }}</div>

                {{-- Label Image --}}
                @php
                    $labelImgPath = optional(optional($part->product)->productDetail)->label_image_path;
                    $labelImgUrl  = $labelImgPath
                        ? url('file/storage/' . ltrim(str_replace('public/', '', $labelImgPath), '/'))
                        : null;
                @endphp
                <div class="img-wrapper">
                    @if($labelImgUrl)
                    <img src="{{ $labelImgUrl }}" class="part-img" alt="Part Image">
                    @endif
                </div>

                <div class="label-fields">
                    <div>
                        <span class="field-label">Model/Event</span>
                        <span class="field-value" title="{{ optional(optional($part->product)->vehicleModel)->name ?? (optional(optional($part->event)->customerCategory)->name ?? '-') }}">
                            {{ optional(optional($part->product)->vehicleModel)->name ?? (optional(optional($part->event)->customerCategory)->name ?? '-') }}
                        </span>
                    </div>
                    <div>
                        <span class="field-label">PO Number</span>
                        <span class="field-value" title="{{ optional($part->event)->po_no ?? '-' }}">{{ optional($part->event)->po_no ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="field-label">ECN No</span>
                        @php
                            $effectiveDoc = optional($part->product)->getEffectiveDocPackage();
                        @endphp
                        <span class="field-value" title="{{ optional(optional($effectiveDoc)->currentRevision)->ecn_no ?? '-' }}">
                            {{ optional(optional($effectiveDoc)->currentRevision)->ecn_no ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="label-footer">
                <div style="flex: 1;">
                    <span class="field-label">Inspected By &amp; Date</span>
                    <span class="field-value" style="margin-bottom: 6px; display:block;">
                        {{ optional(optional($part->checksheet)->qeChecker)->name ?? 'QC Inspector' }}
                        &nbsp;|&nbsp;
                        {{ optional($part->checksheet)->qe_check_date ? \Carbon\Carbon::parse($part->checksheet->qe_check_date)->format('d M Y') : \Carbon\Carbon::now()->format('d M Y') }}
                    </span>
                    <div style="display: flex; gap: 8px;">
                        <div class="signature-box" style="border: 1px solid #94a3b8; width: 120px; height: 75px; border-radius: 4px; display: flex; flex-direction: column; justify-content: flex-end; padding: 3px 4px; background: #f8fafc;">
                            <span style="font-size: 7px; font-weight: bold; color: #64748b; text-transform: uppercase;">QC</span>
                        </div>
                        <div class="signature-box" style="border: 1px solid #94a3b8; width: 120px; height: 75px; border-radius: 4px; display: flex; flex-direction: column; justify-content: flex-end; padding: 3px 4px; background: #f8fafc;">
                            <span style="font-size: 7px; font-weight: bold; color: #64748b; text-transform: uppercase;">NPC</span>
                        </div>
                    </div>
                </div>
                <div class="text-right" style="display: flex; flex-direction: column; justify-content: flex-end; align-items: flex-end;">
                    <span class="field-label" style="text-align:right">Piece No</span>
                    <div class="piece-no">{{ $i }}<span class="piece-total">/{{ $part->qty }}</span></div>
                </div>
            </div>
        </div>
        @endfor
        @endforeach
    </div>

    <script>
        // Ubah jumlah kolom grid
        function setColumns(cols) {
            const wrapper = document.getElementById('labels-wrapper');
            wrapper.style.gridTemplateColumns = 'repeat(' + cols + ', 1fr)';
            wrapper.className = 'cols-' + cols; // Apply class for scaling
            
            // Simpan pilihan ke CSS variable untuk media print
            document.documentElement.style.setProperty('--print-cols', cols);
            // Inject print style override
            updatePrintStyle(cols);
        }

        function updatePrintStyle(cols) {
            let el = document.getElementById('dynamic-print-style');
            if (!el) {
                el = document.createElement('style');
                el.id = 'dynamic-print-style';
                document.head.appendChild(el);
            }
            el.textContent = `
                @media print {
                    #labels-wrapper {
                        grid-template-columns: repeat(${cols}, 1fr) !important;
                    }
                }
            `;
        }

        // Set default
        updatePrintStyle(2);

        // Auto-print setelah halaman siap
        window.onload = function () {
            setTimeout(function () {
                window.print();
            }, 700);
        };
    </script>
</body>
</html>
