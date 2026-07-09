<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\NpcMasterCheckpoint;
use App\Models\ProductCheckpoint;
use App\Models\NpcProductDetail;
use App\Models\NpcSpecChildPart;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Exception;

class ProductChecksheetSetupController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Product::with('mappedCheckpoints', 'customer', 'vehicleModel', 'docPackage.currentRevision', 'siblings.docPackage.currentRevision')
                ->withCount('mappedCheckpoints');

            if ($request->has('customer_id') && $request->customer_id != '') {
                $query->where('customer_id', $request->customer_id);
            }

            if ($request->has('model_id') && $request->model_id != '') {
                $query->where('model_id', $request->model_id);
            }

            if ($request->has('status') && $request->status != '') {
                if ($request->status == 'mapped') {
                    $query->whereHas('mappedCheckpoints');
                } elseif ($request->status == 'unmapped') {
                    $query->whereDoesntHave('mappedCheckpoints');
                }
            }

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->order(function ($q) {
                    $q->orderByRaw('(CASE WHEN (SELECT COUNT(*) FROM npc_product_checkpoints WHERE npc_product_checkpoints.product_id = products.id) > 0 THEN 1 ELSE 0 END) DESC')
                      ->orderBy('products.updated_at', 'desc')
                      ->orderBy('products.part_no', 'asc');
                })
                ->addIndexColumn()
                ->addColumn('customer', function ($product) {
                    return '<div class="text-sm font-bold text-gray-900 dark:text-gray-100">' . (optional($product->customer)->code ?? '-') . '</div>';
                })
                ->addColumn('model', function ($product) {
                    return '<div class="text-sm font-medium text-gray-800 dark:text-gray-200">' . (optional($product->vehicleModel)->name ?? '-') . '</div>';
                })
                ->addColumn('part_no', function ($product) {
                    return '<div class="text-blue-600 dark:text-blue-400 font-bold text-sm">' . $product->part_no . '</div>';
                })
                ->addColumn('part_name', function ($product) {
                    return '<div class="text-gray-800 dark:text-gray-200 font-bold">' . $product->part_name . '</div>';
                })
                ->addColumn('ecn_info', function ($product) {
                    $docPackage = $product->getEffectiveDocPackage();
                    if ($docPackage && $docPackage->currentRevision) {
                        return '<div class="text-sm font-bold text-gray-800 dark:text-gray-200">' . ($docPackage->currentRevision->ecn_no ?? 'No ECN') . '</div>' .
                               '<div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Rev ' . $docPackage->currentRevision->revision_no . '</div>';
                    }
                    return '<span class="text-xs text-gray-400 italic">No Data</span>';
                })
                ->addColumn('mapping_status', function ($product) {
                    if ($product->mappedCheckpoints->isNotEmpty()) {
                        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 text-green-700 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800/50 text-[10px] font-bold uppercase tracking-wider"><i class="fa-solid fa-check-circle"></i> Mapped (' . $product->mappedCheckpoints->count() . ' Points)</span>';
                    } else {
                        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-600 border border-gray-200 dark:bg-gray-700 dark:text-gray-400 dark:border-gray-600 text-[10px] font-bold uppercase tracking-wider"><i class="fa-solid fa-minus"></i> Unmapped</span>';
                    }
                })
                ->addColumn('action', function ($product) {
                    $buttons = '';
                    if ($product->mappedCheckpoints->isNotEmpty()) {
                        $buttons .= '<a href="' . route('checksheets.setup.preview', $product->hashed_id) . '" target="_blank" class="inline-flex px-3 py-1.5 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white dark:hover:bg-emerald-500 font-medium transition items-center gap-1.5 text-xs shadow-sm border border-emerald-200 dark:border-emerald-800/50 hover:border-transparent mr-2" title="Preview Checksheet"><i class="fa-solid fa-eye"></i> Preview</a>';
                    }
                    $buttons .= '<a href="' . route('checksheets.setup.edit', $product->hashed_id) . '" class="inline-flex px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-500 font-medium transition items-center gap-1.5 text-xs shadow-sm border border-blue-200 dark:border-blue-800/50 hover:border-transparent"><i class="fa-solid fa-pencil"></i> Mapping Checksheet</a>';
                    return '<div class="flex items-center justify-end gap-2">' . $buttons . '</div>';
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('part_no', 'like', "%{$search}%")
                              ->orWhere('part_name', 'like', "%{$search}%");
                        });
                    }
                })
                ->rawColumns(['customer', 'model', 'part_no', 'part_name', 'ecn_info', 'mapping_status', 'action'])
                ->make(true);
        }

        $customers = \App\Models\Customer::orderBy('code')->get();
        $models = \App\Models\VehicleModel::whereIn('id', function($q) { $q->selectRaw('MIN(id)')->from('models')->groupBy('name', 'customer_id'); })->orderBy('name')->get();

        return view('master.product_checksheets.index', compact('customers', 'models'));
    }

    public function edit(Product $product)
    {
        $masterPoints = NpcMasterCheckpoint::where('is_active', true)->orderBy('sequence_order')->orderBy('point_number')->get();
        // Load existing mapping if any
        $product->load('mappedCheckpoints', 'productDetail', 'specChildParts.stdPart');
        
        $mappedData = [];
        foreach ($product->mappedCheckpoints as $mc) {
            $mappedData[$mc->npc_master_checkpoint_id] = $mc->custom_standard;
        }

        // Separate material parts and std parts
        $materialParts = $product->specChildParts->where('part_type', 'MATERIAL')->values();
        $stdParts = $product->specChildParts->where('part_type', 'STD_PART')->values();
        
        // For inventory material names, we'll fetch them manually if they exist
        $materialIds = $materialParts->pluck('inventory_material_id')->filter()->toArray();
        $inventoryMaterials = [];
        if (!empty($materialIds)) {
            $invMats = \Illuminate\Support\Facades\DB::table('inv_m_material_spec')
                        ->whereIn('id', $materialIds)
                        ->get(['id', 'spec_name'])->keyBy('id');
            foreach ($invMats as $id => $mat) {
                $inventoryMaterials[$id] = $mat->spec_name;
            }
        }

        // The user says "default check all" if empty!
        $isFirstTime = $product->mappedCheckpoints->isEmpty();

        return view('tracking.checksheet_setup', compact('product', 'masterPoints', 'mappedData', 'isFirstTime', 'materialParts', 'stdParts', 'inventoryMaterials'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'points' => 'array',
            'sketch_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'material_parts' => 'nullable|array',
            'std_parts' => 'nullable|array',
        ]);

        // 1. Handle Master Config (Sketch Image & Process Type)
        $detailData = [];
        
        if ($request->has('process_type')) {
            $detailData['process_type'] = $request->process_type;
        }

        if ($request->hasFile('sketch_image')) {
            $file = $request->file('sketch_image');
            $filename = time() . '_sketch_' . $file->getClientOriginalName();
            $path = $file->storeAs('public/checksheets/sketches', $filename, 'public');

            // Delete old if exists
            if ($product->productDetail && $product->productDetail->sketch_image_path) {
                Storage::delete($product->productDetail->sketch_image_path);
            }
            
            $detailData['sketch_image_path'] = $path;
        }

        if (!empty($detailData)) {
            NpcProductDetail::updateOrCreate(
                ['product_id' => $product->id],
                $detailData
            );
        }

        // 2. Handle Spec Child Parts
        NpcSpecChildPart::where('product_id', $product->id)->delete();
        $childPartsData = [];

        // Materials
        if ($request->has('material_parts') && is_array($request->material_parts)) {
            foreach ($request->material_parts as $mat) {
                if (!empty($mat['inventory_material_id']) || !empty($mat['sequence_label'])) {
                    $childPartsData[] = [
                        'product_id' => $product->id,
                        'part_type' => 'MATERIAL',
                        'sequence_label' => $mat['sequence_label'] ?? '',
                        'inventory_material_id' => $mat['inventory_material_id'] ?? null,
                        'std_part_id' => null,
                        'thickness' => $mat['thickness'] ?? null,
                        'spec' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // STD Parts
        if ($request->has('std_parts') && is_array($request->std_parts)) {
            foreach ($request->std_parts as $std) {
                if (!empty($std['std_part_id']) || !empty($std['sequence_label'])) {
                    $childPartsData[] = [
                        'product_id' => $product->id,
                        'part_type' => 'STD_PART',
                        'sequence_label' => $std['sequence_label'] ?? '',
                        'inventory_material_id' => null,
                        'std_part_id' => $std['std_part_id'] ?? null,
                        'thickness' => null,
                        'spec' => $std['spec'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        if (!empty($childPartsData)) {
            NpcSpecChildPart::insert($childPartsData);
        }

        // 3. Handle Checkpoints
        // Delete old mapping
        ProductCheckpoint::where('product_id', $product->id)->delete();

        // Insert new ones
        if ($request->has('points') && is_array($request->points)) {
            $insertData = [];
            foreach ($request->points as $masterId => $data) {
                if (isset($data['is_checked']) && $data['is_checked'] == '1') {
                    $insertData[] = [
                        'product_id' => $product->id,
                        'npc_master_checkpoint_id' => $masterId,
                        'custom_standard' => $data['custom_standard'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if (!empty($insertData)) {
                ProductCheckpoint::insert($insertData);
            }
        }
        
        activity()
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->event('updated')
            ->log('Part Checksheet Master');

        $product->touch(); // Ensure the parent product's updated_at is bumped for sorting

        return redirect()->route('master.checksheets.index')->with('success', 'Master Checksheet for Part ' . $product->part_no . ' successfully saved!');
    }

    public function preview(Product $product)
    {
        $product->load('mappedCheckpoints.masterCheckpoint', 'productDetail', 'specChildParts.stdPart', 'docPackage.currentRevision', 'siblings.docPackage.currentRevision', 'vehicleModel.customer');

        if ($product->mappedCheckpoints->isEmpty()) {
            return back()->with('error', 'Part checksheet has not been mapped yet. Cannot preview.');
        }

        $fakeChecksheet = new \App\Models\NpcChecksheet();
        $fakeChecksheet->final_result = 'Preview Mode';
        
        $details = collect();
        foreach ($product->mappedCheckpoints as $mapped) {
            if ($mapped->masterCheckpoint) {
                $detail = new \App\Models\NpcChecksheetDetail([
                    'point_check' => $mapped->masterCheckpoint->check_item,
                    'standard' => $mapped->custom_standard ?? $mapped->masterCheckpoint->standard,
                    'row_result' => null,
                    'samples' => [],
                ]);
                $details->push($detail);
            }
        }
        $fakeChecksheet->setRelation('details', $details);

        $part = null;
        $checksheet = $fakeChecksheet;

        return view('npc_checksheets.preview', compact('checksheet', 'part', 'product'));
    }

    public function importForm()
    {
        return view('master.product_checksheets.import');
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template Import');
            
            // Fetch actual Master Checkpoint Data
            $masterPoints = \App\Models\NpcMasterCheckpoint::where('is_active', true)
                                ->orderBy('sequence_order')
                                ->orderBy('point_number')
                                ->get();
                                
            // Set Headers
            $sheet->setCellValue('A1', 'PART NO');
            $sheet->getStyle('A1')->getFont()->setBold(true);
            
            $colIndex = 1;
            foreach ($masterPoints as $mp) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(++$colIndex);
                $sheet->setCellValue($column . '1', $mp->check_item);
                $sheet->getStyle($column . '1')->getFont()->setBold(true);
            }
            

            
            // Auto size columns
            for ($i = 1; $i <= $colIndex; $i++) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            $writer = new Xlsx($spreadsheet);
            $fileName = 'Routing_Checksheet_Import_Template_' . date('Ymd_His') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($tempFile);
            
            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
            
        } catch (Exception $e) {
            return back()->with('error', 'Failed generating template: ' . $e->getMessage());
        }
    }

    public function importData(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Skip Header
            $headers = array_shift($rows);

            $importedCount = 0;
            $partsProcessed = [];
            $rowErrors = [];
            $validRows = [];

            // Mapping Headers to Master Checkpoints
            $masterPoints = \App\Models\NpcMasterCheckpoint::where('is_active', true)->get();
            $headerMap = [];
            foreach ($headers as $colIndex => $headerText) {
                if ($colIndex == 0) continue; // Skip PART NO
                
                $headerText = trim($headerText ?? '');
                if (empty($headerText)) continue;

                // Match by exact check_item string (case-insensitive if needed)
                $matchedPoint = $masterPoints->first(function($point) use ($headerText) {
                    return strtolower(trim($point->check_item)) === strtolower($headerText);
                });

                if ($matchedPoint) {
                    $headerMap[$colIndex] = $matchedPoint->id;
                }
            }

            foreach ($rows as $index => $row) {
                if (empty($row[0])) continue; // Skip empty PART NO

                $actualRowNumber = $index + 2; // +1 for 0-index, +1 for header
                $partNo = trim($row[0]);

                // 1. Resolve Product
                $product = Product::where('part_no', $partNo)->first();
                if (!$product) {
                    $rowErrors[] = "Row {$actualRowNumber}: Part No '{$partNo}' is not found in the system.";
                    continue;
                }

                if (!in_array($product->id, $partsProcessed)) {
                    $partsProcessed[] = $product->id;
                }

                // 2. Iterate through columns and prepare valid checkpoints
                foreach ($headerMap as $colIndex => $masterPointId) {
                    $cellValue = trim($row[$colIndex] ?? '');

                    // Skip empty or dash (-) values
                    if ($cellValue === '' || $cellValue === '-') {
                        continue;
                    }

                    $validRows[] = [
                        'product_id' => $product->id,
                        'npc_master_checkpoint_id' => $masterPointId,
                        'custom_standard' => $cellValue
                    ];
                }
            }

            if (!empty($rowErrors)) {
                $displayErrors = array_slice($rowErrors, 0, 15);
                if (count($rowErrors) > 15) {
                    $displayErrors[] = "<i>...and " . (count($rowErrors) - 15) . " other errors.</i>";
                }
                $errorMsg = "<strong>Import failed due to data mismatch:</strong><ul class='list-disc pl-5 mt-2'><li>" . implode("</li><li>", $displayErrors) . "</li></ul>";
                
                return back()
                    ->with('error', 'Import failed due to data mismatches. Please check the details on the page.')
                    ->with('error_details', $errorMsg);
            }

            DB::beginTransaction();

            // Delete existing checkpoints for processed parts
            foreach ($partsProcessed as $productId) {
                ProductCheckpoint::where('product_id', $productId)->delete();
            }

            foreach ($validRows as $valid) {
                ProductCheckpoint::create([
                    'product_id' => $valid['product_id'],
                    'npc_master_checkpoint_id' => $valid['npc_master_checkpoint_id'],
                    'custom_standard' => $valid['custom_standard'],
                ]);

                $importedCount++;
            }

            $partCount = count($partsProcessed);

            // Touch all updated products to bump their sorting order
            if (!empty($partsProcessed)) {
                Product::whereIn('id', $partsProcessed)->update(['updated_at' => now()]);
            }

            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->event('imported')
                ->log("Part Checksheet Master - $importedCount checkpoints mapped for $partCount parts");

            return redirect()->route('master.checksheets.index')->with('success', "Success! $importedCount checkpoints mapped for $partCount Part(s).");

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed processing Excel: ' . $e->getMessage());
        }
    }
}
