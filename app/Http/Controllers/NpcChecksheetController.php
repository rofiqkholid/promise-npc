<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcPart;
use App\Models\NpcChecksheet;
use App\Models\NpcChecksheetDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class NpcChecksheetController extends Controller
{
    /**
     * Show the form for creating/editing the QC or MGM checksheet.
     */
    public function create(NpcPart $part)
    {
        $part->load('product.mappedCheckpoints.masterCheckpoint');
        $checksheet = $part->checksheet;
        if ($checksheet) {
            $checksheet->load('details');
        }
        
        if (!$checksheet) {
            $checksheet = NpcChecksheet::create([
                'npc_part_id' => $part->id,
                'final_result' => 'Pending'
            ]);

            $this->generateChecksheetDetails($checksheet, $part);
        } elseif ($checksheet->details->isEmpty()) {
            $this->generateChecksheetDetails($checksheet, $part);
        }

        return redirect()->route('checksheets.edit', $checksheet->id);
    }

    private function generateChecksheetDetails(NpcChecksheet $checksheet, NpcPart $part)
    {
        if ($part->product && $part->product->mappedCheckpoints->isNotEmpty()) {
            $checkpoints = $part->product->mappedCheckpoints;
            foreach ($checkpoints as $mapped) {
                if ($mapped->masterCheckpoint) {
                    NpcChecksheetDetail::create([
                        'npc_checksheet_id' => $checksheet->id,
                        'point_check'       => $mapped->masterCheckpoint->check_item,
                        'standard'          => $mapped->custom_standard ?? $mapped->masterCheckpoint->standard,
                    ]);
                }
            }
        } else {
            // Fallback to ALL active master checkpoints
            $checkpoints = \App\Models\NpcMasterCheckpoint::where('is_active', true)->orderBy('point_number')->get();
            foreach ($checkpoints as $mappedPoint) {
                NpcChecksheetDetail::create([
                    'npc_checksheet_id' => $checksheet->id,
                    'point_check'       => $mappedPoint->check_item,
                    'standard'          => null,
                ]);
            }
        }
    }

    /**
     * Display the checksheet form.
     */
    public function edit(NpcChecksheet $checksheet)
    {
        $checksheet->load('details', 'npcPart.checkpoints', 'qeChecker', 'mgmChecker');
        $part = $checksheet->npcPart;

        return view('npc_checksheets.edit', compact('checksheet', 'part'));
    }

    /**
     * Store checksheet inputs (handles both QE/QC and MGM roles).
     */
    public function update(Request $request, NpcChecksheet $checksheet)
    {
        $request->validate([
            'role' => 'required|in:QC,MGM',
        ]);

        $part = $checksheet->npcPart;

        if ($request->role === 'QC') {
            $request->validate([
                'accuracy_percentage' => 'required|numeric|min:0|max:100',
                'attachment_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240'
            ]);

            $updateData = [
                'accuracy_percentage' => $request->accuracy_percentage,
                'qe_checked_by' => auth()->check() ? auth()->user()->getAttribute('id') : 1,
                'qe_check_date' => Carbon::now()
            ];

            if ($request->hasFile('attachment_file')) {
                if ($checksheet->attachment_path) {
                    Storage::disk('public')->delete($checksheet->attachment_path);
                }
                $path = $request->file('attachment_file')->store('npc_checksheets', 'public');
                $updateData['attachment_path'] = $path;
            }

            $checksheet->update($updateData);

            if ($part->status === 'WAITING_QE_CHECK') {
                $part->update(['status' => 'WAITING_MGM_CHECK']);
            }

            return redirect()->route('tracking.index')->with('success', "QC Data (Accuracy: {$request->accuracy_percentage}%) successfully saved.");

        } elseif ($request->role === 'MGM') {
            $request->validate([
                'final_result' => 'nullable|string|max:1000',
                'details' => 'array'
            ]);

            foreach ($request->input('details', []) as $id => $data) {
                $detail = NpcChecksheetDetail::find($id);
                if ($detail && $detail->npc_checksheet_id == $checksheet->id) {
                    $detail->update([
                        'row_result' => $data['row_result'] ?? null,
                        'samples' => $data['samples'] ?? null,
                    ]);
                }
            }

            $checksheet->update([
                'final_result' => $request->final_result,
                'mgm_checked_by' => auth()->check() ? auth()->user()->getAttribute('id') : 1,
                'mgm_check_date' => Carbon::now(),
                'approval_status' => 'WAITING_QE_STAFF' // Enter Approval Phase
            ]);

            if ($request->has('new_history_problems') && is_array($request->new_history_problems)) {
                $problems = array_filter($request->new_history_problems, function($val) {
                    return !empty(trim($val));
                });

                if (!empty($problems) && $part->product) {
                    $insertData = [];
                    foreach ($problems as $probDesc) {
                        $insertData[] = [
                            'product_id' => $part->product->id,
                            'problem_description' => trim($probDesc),
                            'npc_part_id_finder' => $part->id,
                            'created_by' => auth()->check() ? auth()->user()->getAttribute('id') : 1,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                    \App\Models\ProductHistoryProblem::insert($insertData);
                }
            }

            // Instead of FINISHED, move part to WAITING_APPROVAL
            if ($part->status === 'WAITING_MGM_CHECK') {
                $part->update(['status' => 'WAITING_APPROVAL']);
            }

            return redirect()->route('tracking.index')->with('success', 'MGM Checksheet successfully submitted to Approval Phase.');
        }

        return redirect()->route('tracking.index');
    }

    /**
     * Export checksheet to Excel
     */
    public function export(NpcChecksheet $checksheet)
    {
        $checksheet->load('details', 'npcPart.product.specChildParts', 'npcPart.event.customerCategory', 'npcPart.product.docPackage.currentRevision', 'npcPart.product.vehicleModel', 'npcPart.product.productDetail', 'qeStaff', 'qeSpv', 'qeMgr', 'mgmStaff', 'mgmSpv', 'mgmMgr');
        $part = $checksheet->npcPart;
        $product = $part->product;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setShowGridlines(false);
        
        // 1. SET COLUMN WIDTHS
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(25);
        
        for ($i = 0; $i < 12; $i++) {
            $col = chr(69 + $i); // E to P
            $sheet->getColumnDimension($col)->setWidth(5);
        }
        $sheet->getColumnDimension('Q')->setWidth(12);
        
        // 2. HEADER: PART EVENT DELIVERY CHECKSHEET
        $sheet->mergeCells('C1:I3');
        $sheet->setCellValue('C1', 'PART EVENT DELIVERY CHECKSHEET');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                            ->setVertical(Alignment::VERTICAL_CENTER)
                                            ->setWrapText(true);
                                            
        // Logo Space
        $sheet->mergeCells('A1:B3');
        $sheet->getStyle('A1:B3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        // (Optional) Add Logo image here if path is known
        $logoPath = public_path('images/ada_logo.png'); // Example
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Logo');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('A1');
            $drawing->setHeight(40);
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        }
        
        // 3. DOCUMENT INFORMATION (Top Right)
        $sheet->mergeCells('J1:Q1');
        $sheet->setCellValue('J1', 'Document Information');
        $sheet->getStyle('J1')->getFont()->setBold(true);
        $sheet->getStyle('J1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->mergeCells('J2:K2');
        $sheet->setCellValue('J2', 'No. Document');
        $sheet->mergeCells('L2:M2');
        $sheet->setCellValue('L2', ': FO-17-35');
        $sheet->mergeCells('N2:O2');
        $sheet->setCellValue('N2', 'Revision');
        $sheet->setCellValue('P2', 'Date');
        $sheet->setCellValue('Q2', 'Item Change');
        
        $sheet->mergeCells('J3:K3');
        $sheet->setCellValue('J3', 'Revision');
        $sheet->mergeCells('L3:M3');
        $sheet->setCellValue('L3', ': 00'); // Default
        
        $sheet->mergeCells('J4:K4');
        $sheet->setCellValue('J4', 'Date Release');
        $sheet->mergeCells('L4:M4');
        $sheet->setCellValue('L4', ': ' . Carbon::now()->format('d M Y')); // Or from DB
        
        $sheet->mergeCells('J5:M5');
        $sheet->setCellValue('J5', 'Event');
        $sheet->mergeCells('N5:Q5');
        $sheet->setCellValue('N5', optional(optional($part->event)->customerCategory)->name ?? '-');
        
        $sheet->mergeCells('J6:M6');
        $sheet->setCellValue('J6', 'PO Number');
        $sheet->mergeCells('N6:Q6');
        $sheet->setCellValue('N6', optional($part->event)->po_no ?? '-');
        
        $sheet->mergeCells('J7:M7');
        $sheet->setCellValue('J7', 'Quantity Order (pcs)');
        $sheet->mergeCells('N7:Q7');
        $sheet->setCellValue('N7', $part->qty);
        
        $sheet->getStyle('J1:Q7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('J2:Q2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('J2:Q7')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle('N5:Q7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('N5:Q7')->getFont()->getColor()->setARGB('FF0055AA'); // Blue text
        
        // 4. PART INFO (Top Left)
        $sheet->mergeCells('A5:B5');
        $sheet->setCellValue('A5', 'Model');
        $sheet->mergeCells('C5:I5');
        $sheet->setCellValue('C5', optional(optional($product)->vehicleModel)->name ?? '-');
        
        $sheet->mergeCells('A6:B6');
        $sheet->setCellValue('A6', 'Part Name');
        $sheet->mergeCells('C6:I6');
        $sheet->setCellValue('C6', optional($product)->part_name ?? '-');
        
        $sheet->mergeCells('A7:B7');
        $sheet->setCellValue('A7', 'Part No.');
        $sheet->setCellValue('C7', optional($product)->part_no ?? '-');
        $sheet->setCellValue('D7', 'EO No.');
        $sheet->mergeCells('E7:I7');
        $sheet->setCellValue('E7', optional(optional(optional($product)->docPackage)->currentRevision)->ecn_no ?? '-');
        
        $sheet->mergeCells('A8:B8');
        $sheet->setCellValue('A8', 'Process');
        $sheet->setCellValue('C8', 'SSW'); // Default or dynamic
        $sheet->setCellValue('D8', 'Manual');
        $sheet->mergeCells('E8:I8');
        $sheet->setCellValue('E8', 'Auto/Robot');
        
        $sheet->getStyle('A5:I8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('C5:I6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E7:I7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E8:I8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getStyle('C5:I6')->getFont()->getColor()->setARGB('FF0055AA'); // Blue text
        $sheet->getStyle('C7')->getFont()->getColor()->setARGB('FF0055AA'); // Blue text
        $sheet->getStyle('E7:I7')->getFont()->getColor()->setARGB('FF0055AA'); // Blue text
        $sheet->getStyle('C8')->getFont()->getColor()->setARGB('FF0055AA'); // Blue text
        $sheet->getStyle('D8')->getFont()->getColor()->setARGB('FF0055AA'); // Blue text
        $sheet->getStyle('E8:I8')->getFont()->getColor()->setARGB('FF0055AA'); // Blue text
        $sheet->getStyle('A5:B8')->getFont()->setBold(true);
        $sheet->getStyle('D7')->getFont()->setBold(true);
        
        // 5. SPEC CHILD PART
        $sheet->mergeCells('A10:K10');
        $sheet->setCellValue('A10', 'Spec Child Part');
        $sheet->getStyle('A10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A10')->getFont()->setBold(true);
        $sheet->getStyle('A10')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        $sheet->setCellValue('A11', 'No');
        $sheet->mergeCells('B11:C11');
        $sheet->setCellValue('B11', 'Material Part');
        $sheet->setCellValue('D11', 'Thickness');
        $sheet->setCellValue('E11', 'No');
        $sheet->mergeCells('F11:H11');
        $sheet->setCellValue('F11', 'STD Part');
        $sheet->mergeCells('I11:K11');
        $sheet->setCellValue('I11', 'Spec');
        
        $sheet->getStyle('A11:K11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A11:K11')->getFont()->setBold(true);
        
        $materials = [];
        $stds = [];
        if ($product) {
            $materials = $product->specChildParts->where('part_type', 'MATERIAL')->values();
            $stds = $product->specChildParts->where('part_type', 'STD_PART')->values();
        }
        
        $currentRow = 12;
        $alphabet = range('a', 'z');
        for ($i = 0; $i < 15; $i++) {
            $mat = $materials[$i] ?? null;
            $std = $stds[$i] ?? null;
            
            $sheet->setCellValue('A' . $currentRow, $i + 1);
            $sheet->mergeCells('B' . $currentRow . ':C' . $currentRow);
            
            $matName = '';
            if ($mat && $mat->inventory_material_id) {
                $invMat = \Illuminate\Support\Facades\DB::table('inv_m_material_spec')->where('id', $mat->inventory_material_id)->first();
                $matName = $invMat ? $invMat->spec_name : '';
            }
            $sheet->setCellValue('B' . $currentRow, $matName);
            $sheet->setCellValue('D' . $currentRow, $mat ? $mat->thickness : '');
            
            $sheet->setCellValue('E' . $currentRow, $alphabet[$i] . '.');
            $sheet->mergeCells('F' . $currentRow . ':H' . $currentRow);
            
            $stdName = '';
            if ($std && $std->stdPart) {
                $stdName = $std->stdPart->name;
            }
            $sheet->setCellValue('F' . $currentRow, $stdName);
            $sheet->mergeCells('I' . $currentRow . ':K' . $currentRow);
            $sheet->setCellValue('I' . $currentRow, $std ? $std->spec : '');
            
            $currentRow++;
        }
        $sheet->getStyle('A10:K' . ($currentRow - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A11:A' . ($currentRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D11:E' . ($currentRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // 6. SKETCH
        $sheet->mergeCells('L10:Q' . ($currentRow - 1));
        $sheet->getStyle('L10:Q' . ($currentRow - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->setCellValue('L10', 'SKETCH');
        $sheet->getStyle('L10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('L10')->getFont()->setBold(true);
        
        if ($product && $product->productDetail && $product->productDetail->sketch_image_path) {
            $imgPath = \Illuminate\Support\Facades\Storage::path($product->productDetail->sketch_image_path);
            if (file_exists($imgPath)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Sketch');
                $drawing->setDescription('Sketch Image');
                $drawing->setPath($imgPath);
                $drawing->setCoordinates('L11');
                
                // Calculate proportional dimensions to fit nicely
                $imageSize = getimagesize($imgPath);
                if ($imageSize) {
                    $origWidth = $imageSize[0];
                    $origHeight = $imageSize[1];
                    
                    $maxWidth = 240;  // Approx width of columns L to Q
                    $maxHeight = 250; // Approx height of 15 rows
                    
                    $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
                    
                    if ($ratio < 1) {
                        $drawing->setWidth($origWidth * $ratio);
                        $drawing->setHeight($origHeight * $ratio);
                    } else {
                        $drawing->setWidth($origWidth);
                        $drawing->setHeight($origHeight);
                    }
                } else {
                    $drawing->setHeight(200); // Fallback
                }
                
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(10);
                $drawing->setWorksheet($sheet);
            }
        }
        
        // 7. POINT CHECK TABLE
        $headerRow = $currentRow + 1;
        $sheet->setCellValue('A' . $headerRow, 'No.');
        $sheet->mergeCells('B' . $headerRow . ':C' . $headerRow);
        $sheet->setCellValue('B' . $headerRow, 'Point Check');
        $sheet->setCellValue('D' . $headerRow, 'Standard');
        
        for ($i = 0; $i < 12; $i++) {
            $col = chr(69 + $i);
            $sheet->setCellValue($col . $headerRow, $i + 1);
        }
        $sheet->setCellValue('Q' . $headerRow, 'Result');
        
        $sheet->getStyle('A' . $headerRow . ':Q' . $headerRow)->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFD966']], // Yellowish
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
        ]);
        
        $itemRow = $headerRow + 1;
        $detailCount = 1;
        foreach ($checksheet->details as $detail) {
            $sheet->setCellValue('A' . $itemRow, $detailCount++);
            
            // Category mapping
            $category = 'Quality';
            $pcLow = strtolower($detail->point_check);
            if (str_contains($pcLow, 'history') || str_contains($pcLow, 'problem')) {
                $category = 'History Problem';
            } elseif (str_contains($pcLow, 'pallet') || str_contains($pcLow, 'label') || str_contains($pcLow, 'packaging') || str_contains($pcLow, 'harigami')) {
                $category = 'Packaging';
            }
            
            $sheet->setCellValue('B' . $itemRow, $category);
            $sheet->setCellValue('C' . $itemRow, $detail->point_check);
            $sheet->setCellValue('D' . $itemRow, $detail->standard);
            
            // Render samples
            $samples = $detail->samples ?? [];
            for ($i = 0; $i < 12; $i++) {
                $col = chr(69 + $i);
                $val = '';
                if (isset($samples[$i])) {
                    if ($samples[$i] === 'OK') $val = 'O';
                    elseif ($samples[$i] === 'NG') $val = 'X';
                }
                $sheet->setCellValue($col . $itemRow, $val);
                // Color formatting
                if ($val === 'O') {
                    $sheet->getStyle($col . $itemRow)->getFont()->getColor()->setARGB('FF00B050');
                    $sheet->getStyle($col . $itemRow)->getFont()->setBold(true);
                } elseif ($val === 'X') {
                    $sheet->getStyle($col . $itemRow)->getFont()->getColor()->setARGB('FFFF0000');
                    $sheet->getStyle($col . $itemRow)->getFont()->setBold(true);
                }
            }
            
            // Result
            $sheet->setCellValue('Q' . $itemRow, $detail->row_result);
            $itemRow++;
        }
        
        // Merge identical categories in B
        $startMerge = $headerRow + 1;
        if ($itemRow > $headerRow + 1) {
            $currentCat = $sheet->getCell('B' . $startMerge)->getValue();
            for ($r = $startMerge + 1; $r <= $itemRow; $r++) {
                $cat = $sheet->getCell('B' . $r)->getValue();
                if ($cat !== $currentCat || $r == $itemRow) {
                    if ($r - 1 > $startMerge) {
                        $sheet->mergeCells('B' . $startMerge . ':B' . ($r - 1));
                        $sheet->getStyle('B' . $startMerge)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                        $sheet->getStyle('B' . $startMerge)->getAlignment()->setWrapText(true);
                    }
                    $currentCat = $cat;
                    $startMerge = $r;
                }
            }
        }
        
        $sheet->getStyle('A' . $headerRow . ':Q' . ($itemRow - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A' . ($headerRow + 1) . ':A' . ($itemRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . ($headerRow + 1) . ':Q' . ($itemRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // 8. FOOTER
        $footerRow = $itemRow + 1;
        $sheet->mergeCells('A' . $footerRow . ':B' . $footerRow);
        $sheet->setCellValue('A' . $footerRow, 'Checking Date');
        $sheet->mergeCells('C' . $footerRow . ':D' . $footerRow);
        $sheet->setCellValue('C' . $footerRow, Carbon::now()->format('d-M-Y'));
        
        $sheet->mergeCells('E' . $footerRow . ':Q' . $footerRow);
        $sheet->setCellValue('E' . $footerRow, 'If had 1 point X, delivery will be postponed until improvement has been complised');
        $sheet->getStyle('E' . $footerRow)->getFont()->getColor()->setARGB('FF0000FF'); // Blue
        $sheet->getStyle('E' . $footerRow)->getFont()->setItalic(true)->setBold(true);
        $sheet->getStyle('E' . $footerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->getStyle('A' . $footerRow . ':Q' . $footerRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A' . $footerRow)->getFont()->setBold(true);
        $sheet->getStyle('C' . $footerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sigRow = $footerRow + 1;
        $sheet->mergeCells('A' . $sigRow . ':B' . ($sigRow + 3));
        $sheet->setCellValue('A' . $sigRow, 'Checked By');
        $sheet->getStyle('A' . $sigRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $sigRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        
        $sheet->mergeCells('C' . $sigRow . ':H' . $sigRow);
        $sheet->setCellValue('C' . $sigRow, 'QE');
        $sheet->mergeCells('I' . $sigRow . ':Q' . $sigRow);
        $sheet->setCellValue('I' . $sigRow, 'NPC / MANAGEMENT');
        $sheet->getStyle('C' . $sigRow . ':Q' . $sigRow)->getFont()->setBold(true);
        
        $sheet->setCellValue('C' . ($sigRow + 1), 'Mgr/Asst Mgr');
        $sheet->setCellValue('D' . ($sigRow + 1), 'Spv/Leader');
        $sheet->mergeCells('E' . ($sigRow + 1) . ':H' . ($sigRow + 1));
        $sheet->setCellValue('E' . ($sigRow + 1), 'Staff/Opr');
        
        $sheet->mergeCells('I' . ($sigRow + 1) . ':K' . ($sigRow + 1));
        $sheet->setCellValue('I' . ($sigRow + 1), "Management\nMgr/Asst Mgr");
        $sheet->mergeCells('L' . ($sigRow + 1) . ':N' . ($sigRow + 1));
        $sheet->setCellValue('L' . ($sigRow + 1), "Management\nSpv/Leader");
        $sheet->mergeCells('O' . ($sigRow + 1) . ':Q' . ($sigRow + 1));
        $sheet->setCellValue('O' . ($sigRow + 1), "Management\nStaff/Opr");
        $sheet->getStyle('I' . ($sigRow + 1) . ':Q' . ($sigRow + 1))->getAlignment()->setWrapText(true);
        
        // Empty boxes for signature -> now filled with names
        $sheet->mergeCells('C' . ($sigRow + 2) . ':C' . ($sigRow + 3));
        $sheet->mergeCells('D' . ($sigRow + 2) . ':D' . ($sigRow + 3));
        $sheet->mergeCells('E' . ($sigRow + 2) . ':H' . ($sigRow + 3));
        $sheet->mergeCells('I' . ($sigRow + 2) . ':K' . ($sigRow + 3));
        $sheet->mergeCells('L' . ($sigRow + 2) . ':N' . ($sigRow + 3));
        $sheet->mergeCells('O' . ($sigRow + 2) . ':Q' . ($sigRow + 3));
        
        $sheet->setCellValue('C' . ($sigRow + 2), optional($checksheet->qeMgr)->name ?? '');
        $sheet->setCellValue('D' . ($sigRow + 2), optional($checksheet->qeSpv)->name ?? '');
        $sheet->setCellValue('E' . ($sigRow + 2), optional($checksheet->qeStaff)->name ?? '');
        
        $sheet->setCellValue('I' . ($sigRow + 2), optional($checksheet->mgmMgr)->name ?? '');
        $sheet->setCellValue('L' . ($sigRow + 2), optional($checksheet->mgmSpv)->name ?? '');
        $sheet->setCellValue('O' . ($sigRow + 2), optional($checksheet->mgmStaff)->name ?? '');

        $sheet->getRowDimension($sigRow + 2)->setRowHeight(30);
        $sheet->getRowDimension($sigRow + 3)->setRowHeight(30);
        
        $sheet->getStyle('A' . $sigRow . ':Q' . ($sigRow + 3))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('C' . $sigRow . ':Q' . ($sigRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('C' . ($sigRow + 2) . ':Q' . ($sigRow + 3))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->getStyle('C' . ($sigRow + 2) . ':Q' . ($sigRow + 3))->getFont()->setItalic(true);
        
        // Generate Response
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Checksheet_' . ($product->part_no ?? 'UNKNOWN') . '_' . time() . '.xlsx';
        
        if (ob_get_length()) {
            ob_end_clean();
        }
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Print QC Quality Label for a specific part
     */
    public function printLabel(NpcPart $part)
    {
        // Pastikan part sudah memiliki checksheet dan product
        $part->load(['product.vehicleModel.customer', 'product.docPackage.currentRevision', 'checksheet.qeChecker', 'event.customerCategory']);

        // Jika belum ada checksheet, abort atau redirect
        if (!$part->checksheet) {
            return redirect()->back()->with('error', 'QC data not found. Please complete QC first.');
        }

        return view('npc_checksheets.label_print', compact('part'));
    }
}
