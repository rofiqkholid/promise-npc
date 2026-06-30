<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Checksheet - {{ optional($product)->part_no }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue-text: #0055AA;
        }
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
            font-size: 11px;
            color: #000;
        }
        .a4-page {
            width: 210mm;
            padding: 10mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            box-sizing: border-box;
            position: relative;
        }
        .header-actions {
            width: 210mm;
            margin: 0 auto 10px auto;
            display: flex;
            justify-content: space-between;
        }
        .btn {
            padding: 8px 16px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-family: inherit;
        }
        .btn-secondary {
            background: #4b5563;
        }
        .excel-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* To ensure exact fit */
            box-sizing: border-box;
        }
        .excel-table th, .excel-table td {
            border: 1px solid #000;
            padding: 3px;
            vertical-align: middle;
            word-wrap: break-word;
            line-height: 1.2;
            height: 18px; /* Force minimum height for empty cells */
            box-sizing: border-box;
        }
        
        .no-border-cell {
            border: none !important;
        }
        
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-blue { color: var(--blue-text); }
        .bg-yellow-exact { background-color: #fff2cc; } /* FFFFD966 */
        .text-green { color: #00B050; }
        .text-red { color: #FF0000; }
        .text-blue-sig { color: #0000FF; }
        .italic { font-style: italic; }
        
        .logo-img { max-height: 40px; max-width: 100px; display: block; margin: 0 auto; }
        .title-cell { font-size: 18px; }
        
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .a4-page { box-shadow: none; width: 100%; margin: 0; padding: 0; }
            .header-actions { display: none; }
            @page { size: A4 portrait; margin: 5mm; }
        }
    </style>
</head>
<body>

<div class="header-actions">
    <a href="javascript:history.back()" class="btn btn-secondary">← Back</a>
    <button onclick="window.print()" class="btn">Print / Save as PDF</button>
</div>

<div class="a4-page">
    <table class="excel-table">
        <!-- Exact 17 Columns setup mimicking A to Q -->
        <colgroup>
            <col style="width: 3.65%;">  <!-- A: 5 -->
            <col style="width: 10.95%;"> <!-- B: 15 -->
            <col style="width: 14.60%;"> <!-- C: 20 -->
            <col style="width: 18.25%;"> <!-- D: 25 -->
            <col style="width: 3.65%;">  <!-- E: 5 -->
            <col style="width: 3.65%;">  <!-- F: 5 -->
            <col style="width: 3.65%;">  <!-- G: 5 -->
            <col style="width: 3.65%;">  <!-- H: 5 -->
            <col style="width: 3.65%;">  <!-- I: 5 -->
            <col style="width: 3.65%;">  <!-- J: 5 -->
            <col style="width: 3.65%;">  <!-- K: 5 -->
            <col style="width: 3.65%;">  <!-- L: 5 -->
            <col style="width: 3.65%;">  <!-- M: 5 -->
            <col style="width: 3.65%;">  <!-- N: 5 -->
            <col style="width: 3.65%;">  <!-- O: 5 -->
            <col style="width: 3.65%;">  <!-- P: 5 -->
            <col style="width: 8.75%;">  <!-- Q: 12 -->
        </colgroup>
        <tbody>
            <!-- Row 1 -->
            <tr>
                <td colspan="2" rowspan="3" class="text-center" style="border-bottom: none;">
                    @if(file_exists(public_path('images/sai_logo_bg.png')))
                        <img src="{{ asset('images/sai_logo_bg.png') }}" alt="Logo" class="logo-img">
                    @endif
                </td>
                <td colspan="7" rowspan="4" class="text-center font-bold title-cell">PART EVENT DELIVERY CHECKSHEET</td>
                <td colspan="8" class="text-center font-bold">Document Information</td>
            </tr>
            <!-- Row 2 -->
            <tr class="text-center">
                <td colspan="2" class="text-left">No. Document</td>
                <td colspan="2" class="text-left">: FO-17-35</td>
                <td colspan="2" class="text-left">Revision</td>
                <td colspan="1">Date</td>
                <td colspan="1">Item Change</td>
            </tr>
            <!-- Row 3 -->
            <tr>
                <td colspan="2" class="text-left">Revision</td>
                <td colspan="2" class="text-left">: 00</td>
                <td colspan="2"></td>
                <td colspan="1"></td>
                <td colspan="1"></td>
            </tr>
            
            <!-- Row 4 -->
            <tr>
                <td colspan="2" class="text-center" style="font-size: 9px; font-weight: bold; padding: 2px; border-top: none;">New Project Control<br>Dept.</td>
                <td colspan="2">Date Release</td>
                <td colspan="2">: {{ \Carbon\Carbon::now()->format('d M Y') }}</td>
                <td colspan="2"></td>
                <td colspan="1"></td>
                <td colspan="1"></td>
            </tr>

            <!-- Row 5 -->
            <tr>
                <td colspan="2" class="font-bold">Model</td>
                <td colspan="7" class="text-center font-bold text-blue">{{ optional(optional($product)->vehicleModel)->name ?? '-' }}</td>
                <td colspan="4">Event</td>
                <td colspan="4" class="text-center text-blue">{{ optional(optional(optional($part)->event)->customerCategory)->name ?? '-' }}</td>
            </tr>
            
            <!-- Row 6 -->
            <tr>
                <td colspan="2" class="font-bold">Part Name</td>
                <td colspan="7" class="text-center font-bold text-blue">{{ optional($product)->part_name ?? '-' }}</td>
                <td colspan="4">PO Number</td>
                <td colspan="4" class="text-center text-blue">{{ optional(optional($part)->event)->po_no ?? '-' }}</td>
            </tr>
            
            <!-- Row 7 -->
            <tr>
                <td colspan="2" class="font-bold">Part No.</td>
                <td colspan="1" class="text-center text-blue">{{ optional($product)->part_no ?? '-' }}</td>
                <td colspan="1" class="font-bold text-center">EO No.</td>
                <td colspan="5" class="text-center text-blue">{{ optional($part->drawingRevision)->ecn_no ?? optional(optional(optional($product)->docPackage)->currentRevision)->ecn_no ?? optional(optional(optional($product)->getEffectiveDocPackage())->currentRevision)->ecn_no ?? '-' }}</td>
                <td colspan="4">Quantity Order (pcs)</td>
                <td colspan="4" class="text-center text-blue">{{ optional($part)->qty ?? '-' }}</td>
            </tr>
            
            <!-- Row 8 -->
            <tr>
                <td colspan="2" class="font-bold">Process</td>
                @php
                    $processNames = optional($part)->processes ? optional($part)->processes->map(function($p) { return optional($p->process)->process_name; })->filter()->implode(', ') : '-';
                    if (empty($processNames)) $processNames = '-';
                @endphp
                <td colspan="1" class="text-center text-blue">{{ $processNames }}</td>
                <td colspan="1" class="text-center text-blue">Manual</td>
                <td colspan="5" class="text-center text-blue">Auto/Robot</td>
                <td colspan="8" class="no-border-cell"></td>
            </tr>
            
            <!-- Row 9 Spacer -->
            <tr><td colspan="17" class="no-border-cell" style="height: 10px;"></td></tr>
            
            <!-- Extract Sketch Variables -->
            @php
                $sketchPath = $product && $product->productDetail ? $product->productDetail->sketch_image_path : null;
                $sketchSrc = '';
                $isLandscape = false;
                if ($sketchPath && Storage::exists($sketchPath)) {
                    $mime = Storage::mimeType($sketchPath);
                    $sketchData = base64_encode(Storage::get($sketchPath));
                    $sketchSrc = 'data:' . $mime . ';base64,' . $sketchData;
                    
                    $absPath = Storage::path($sketchPath);
                    $imgSize = @getimagesize($absPath);
                    if ($imgSize && $imgSize[0] > $imgSize[1]) {
                        $isLandscape = true;
                    }
                }
            @endphp

            @if($isLandscape && $sketchSrc)
            <!-- Landscape SKETCH Row -->
            <tr>
                <td colspan="17" class="text-center font-bold" style="vertical-align: top; padding: 10px;">
                    <div style="margin-bottom: 10px;">SKETCH</div>
                    <img src="{{ $sketchSrc }}" alt="Sketch" style="max-width: 100%; max-height: 400px; object-fit: contain; margin: 0 auto; display: block;">
                </td>
            </tr>
            @endif

            <!-- Row 10 -->
            <tr>
                @if($isLandscape)
                    <td colspan="17" class="text-center font-bold">Spec Child Part</td>
                @else
                    <td colspan="11" class="text-center font-bold">Spec Child Part</td>
                    <td colspan="6" rowspan="17" class="text-center font-bold" style="vertical-align: top; padding: 10px;">
                        <div style="margin-bottom: 10px;">SKETCH</div>
                        @if($sketchSrc)
                            <img src="{{ $sketchSrc }}" alt="Sketch" style="max-width: 100%; max-height: 250px; object-fit: contain;">
                        @endif
                    </td>
                @endif
            </tr>
            
            <!-- Row 11 -->
            <tr class="text-center font-bold">
                <td>No</td>
                @if($isLandscape)
                    <td colspan="4">Material Part</td>
                    <td colspan="2">Thickness</td>
                    <td>No</td>
                    <td colspan="4">STD Part</td>
                    <td colspan="5">Spec</td>
                @else
                    <td colspan="2">Material Part</td>
                    <td>Thickness</td>
                    <td>No</td>
                    <td colspan="3">STD Part</td>
                    <td colspan="3">Spec</td>
                @endif
            </tr>
            
            <!-- Rows 12 to 26 -->
            @php
                $materials = [];
                $stds = [];
                if ($product) {
                    $materials = $product->specChildParts->where('part_type', 'MATERIAL')->values();
                    $stds = $product->specChildParts->where('part_type', 'STD_PART')->values();
                }
                $alphabet = range('a', 'z');
            @endphp
            
            @for ($i = 0; $i < 15; $i++)
                @php
                    $mat = $materials[$i] ?? null;
                    $std = $stds[$i] ?? null;
                    $matName = '';
                    if ($mat && $mat->inventory_material_id) {
                        $invMat = \Illuminate\Support\Facades\DB::table('inv_m_material_spec')->where('id', $mat->inventory_material_id)->first();
                        $matName = $invMat ? $invMat->spec_name : '';
                    }
                    $stdName = ($std && $std->stdPart) ? $std->stdPart->name : '';
                @endphp
                <tr class="text-center">
                    <td>{{ $i + 1 }}</td>
                    @if($isLandscape)
                        <td colspan="4">{{ $matName }}</td>
                        <td colspan="2">{{ $mat ? $mat->thickness : '' }}</td>
                        <td>{{ $alphabet[$i] }}.</td>
                        <td colspan="4">{{ $stdName }}</td>
                        <td colspan="5">{{ $std ? $std->spec : '' }}</td>
                    @else
                        <td colspan="2">{{ $matName }}</td>
                        <td>{{ $mat ? $mat->thickness : '' }}</td>
                        <td>{{ $alphabet[$i] }}.</td>
                        <td colspan="3">{{ $stdName }}</td>
                        <td colspan="3">{{ $std ? $std->spec : '' }}</td>
                    @endif
                </tr>
            @endfor
            
            <!-- Spacer Row before point check? Excel export doesn't seem to have one but let's add one if we want -->
            
            <!-- Row 27 Point Check Header -->
            <tr class="bg-yellow-exact font-bold text-center">
                <td>No.</td>
                <td colspan="2">Point Check</td>
                <td>Standard</td>
                @for($i=1; $i<=12; $i++)
                <td>{{ $i }}</td>
                @endfor
                <td>Result</td>
            </tr>
            
            <!-- Rows 28+ Point Check Data -->
            @php
                $detailCount = 1;
                $detailsArray = [];
                
                $historyItems = [];
                if ($product && $product->historyProblems && $product->historyProblems->count() > 0) {
                    foreach ($product->historyProblems as $hp) {
                        $historyItems[] = [
                            'cat' => 'History Problem',
                            'point' => '[' . $hp->created_at->format('d/m/y') . '] ' . $hp->problem_description,
                            'std' => '',
                            'samples' => [],
                            'result' => ''
                        ];
                    }
                }
                while (count($historyItems) < 4) {
                    $historyItems[] = [
                        'cat' => 'History Problem',
                        'point' => ' ',
                        'std' => '',
                        'samples' => [],
                        'result' => ''
                    ];
                }
                foreach ($historyItems as $hi) {
                    $detailsArray[] = $hi;
                }

                foreach($checksheet->details as $detail) {
                    $category = 'Quality';
                    $pcLow = trim(strtolower($detail->point_check));
                    if (str_contains($pcLow, 'history') || str_contains($pcLow, 'problem')) {
                        $category = 'History Problem';
                    } elseif (
                        $pcLow === 'pallet usage' || 
                        $pcLow === 'part quantity order' || 
                        $pcLow === 'part tag label' || 
                        $pcLow === 'qc marking check' || 
                        $pcLow === 'harigami'
                    ) {
                        $category = 'Packaging';
                    }
                    $detailsArray[] = [
                        'cat' => $category,
                        'point' => $detail->point_check,
                        'std' => $detail->standard,
                        'samples' => $detail->samples ?? [],
                        'result' => $detail->row_result
                    ];
                }
                
                usort($detailsArray, function($a, $b) {
                    $order = ['History Problem' => 1, 'Quality' => 2, 'Packaging' => 3];
                    $oa = $order[$a['cat']] ?? 99;
                    $ob = $order[$b['cat']] ?? 99;
                    return $oa <=> $ob;
                });
                $grouped = [];
                foreach($detailsArray as $d) {
                    $grouped[$d['cat']][] = $d;
                }
            @endphp
            
            @foreach($grouped as $cat => $items)
                @foreach($items as $index => $item)
                <tr>
                    <td class="text-center">{{ $detailCount++ }}</td>
                    @if($index === 0)
                        <td rowspan="{{ count($items) }}" style="vertical-align: middle; text-align: center;">{{ $cat }}</td>
                    @endif
                    @if($cat === 'History Problem')
                        <td colspan="2" style="overflow: hidden; text-overflow: clip; white-space: nowrap;">{{ $item['point'] }}</td>
                    @else
                        <td>{{ $item['point'] }}</td>
                        <td>{{ $item['std'] }}</td>
                    @endif
                    @for($i=1; $i<=12; $i++)
                        @php
                            $val = '';
                            $sampleVal = $item['samples'][$i] ?? '';
                            $colorClass = '';
                            if ($sampleVal === 'OK') { $val = 'O'; $colorClass = 'text-green font-bold'; }
                            elseif ($sampleVal === 'NG') { $val = 'X'; $colorClass = 'text-red font-bold'; }
                        @endphp
                        <td class="text-center {{ $colorClass }}">{{ $val }}</td>
                    @endfor
                    <td class="text-center font-bold @if($item['result'] === 'OK') text-green @elseif($item['result'] === 'NG') text-red @endif">
                        {{ $item['result'] }}
                    </td>
                </tr>
                @endforeach
            @endforeach
            
            <!-- Footer Signatures Spacer -->
            <tr><td colspan="17" class="no-border-cell" style="height: 10px;"></td></tr>
            
            <!-- Footer Row 1 -->
            <tr>
                <td colspan="2" class="font-bold">Checking Date</td>
                <td colspan="2" class="text-center">{{ \Carbon\Carbon::now()->format('d-M-Y') }}</td>
                <td colspan="13" class="text-center font-bold italic text-blue-sig">If had 1 point X, delivery will be postponed until improvement has been completed</td>
            </tr>
            
            <!-- Footer Row 2 -->
            <tr class="text-center font-bold">
                <td colspan="2" rowspan="3" style="vertical-align: middle;">Checked By</td>
                <td colspan="6">QE</td>
                <td colspan="9">NPC / MANAGEMENT</td>
            </tr>
            
            <!-- Footer Row 3 -->
            <tr class="text-center">
                <td colspan="1">Mgr</td>
                <td colspan="1">Asst Mgr</td>
                <td colspan="4">Staff/SPV</td>
                <td colspan="3">Mgr</td>
                <td colspan="3">Asst Mgr</td>
                <td colspan="3">Staff/SPV</td>
            </tr>
            
            <!-- Footer Row 4 (Signature empty space) -->
            <tr class="text-center" style="height: 50px;">
                <td colspan="1" style="vertical-align: bottom; font-weight:bold;">
                    @if(optional($checksheet)->qe_mgr_id) <div style="color: green; font-size: 10px; margin-bottom: 2px;">✔ Approved</div> @endif
                    {{ optional($checksheet->qeMgr)->name ?? '' }}
                </td>
                <td colspan="1" style="vertical-align: bottom; font-weight:bold;">
                    @if(optional($checksheet)->qe_spv_id) <div style="color: green; font-size: 10px; margin-bottom: 2px;">✔ Approved</div> @endif
                    {{ optional($checksheet->qeSpv)->name ?? '' }}
                </td>
                <td colspan="4" style="vertical-align: bottom; font-weight:bold;">
                    @if(optional($checksheet)->qe_staff_id || optional($checksheet)->qe_check_date) <div style="color: green; font-size: 10px; margin-bottom: 2px;">✔ Approved</div> @endif
                    {{ optional($checksheet->qeStaff)->name ?? (optional($checksheet->qeChecker)->name ?? '') }}
                </td>
                <td colspan="3" style="vertical-align: bottom; font-weight:bold;">
                    @if(optional($checksheet)->mgm_mgr_id) <div style="color: green; font-size: 10px; margin-bottom: 2px;">✔ Approved</div> @endif
                    {{ optional($checksheet->mgmMgr)->name ?? '' }}
                </td>
                <td colspan="3" style="vertical-align: bottom; font-weight:bold;">
                    @if(optional($checksheet)->mgm_spv_id) <div style="color: green; font-size: 10px; margin-bottom: 2px;">✔ Approved</div> @endif
                    {{ optional($checksheet->mgmSpv)->name ?? '' }}
                </td>
                <td colspan="3" style="vertical-align: bottom; font-weight:bold;">
                    @if(optional($checksheet)->mgm_staff_id) <div style="color: green; font-size: 10px; margin-bottom: 2px;">✔ Approved</div> @endif
                    {{ optional($checksheet->mgmStaff)->name ?? '' }}
                </td>
            </tr>
            
        </tbody>
    </table>
</div>

</body>
</html>
