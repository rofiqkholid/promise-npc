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
        $query = Product::with('mappedCheckpoints', 'customer', 'vehicleModel')->orderBy('part_no');
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('part_no', 'like', "%{$search}%")
                  ->orWhere('part_name', 'like', "%{$search}%");
            });
        }

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
        
        $products = $query->paginate(20);

        $customers = \App\Models\Customer::orderBy('code')->get();
        $models = \App\Models\VehicleModel::orderBy('name')->get();

        return view('master.product_checksheets.index', compact('products', 'customers', 'models'));
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

        // 1. Handle Sketch Image (untuk checksheet)
        if ($request->hasFile('sketch_image')) {
            $file = $request->file('sketch_image');
            $filename = time() . '_sketch_' . $file->getClientOriginalName();
            $path = $file->storeAs('public/checksheets/sketches', $filename);

            // Delete old if exists
            if ($product->productDetail && $product->productDetail->sketch_image_path) {
                Storage::delete($product->productDetail->sketch_image_path);
            }

            NpcProductDetail::updateOrCreate(
                ['product_id' => $product->id],
                ['sketch_image_path' => $path]
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

        return redirect()->route('master.checksheets.index')->with('success', 'Master Checksheet for Part ' . $product->part_no . ' successfully saved!');
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
            
            // Set Headers
            $headers = ['PART NO', 'CHECKPOINT NUMBER', 'POINT CHECK NAME', 'CUSTOM STANDARD'];
            foreach ($headers as $index => $header) {
                $column = chr(65 + $index);
                $sheet->setCellValue($column . '1', $header);
                $sheet->getStyle($column . '1')->getFont()->setBold(true);
            }
            
            // Add Sample Data
            $sampleData = [
                ['PART-001', '1', 'Visual Check', 'No Scratch'],
                ['PART-001', '2', 'Dimension', 'Length 100mm +- 0.5'],
                ['PART-002', '1', 'Visual Check', 'Surface Smooth'],
            ];
            
            foreach ($sampleData as $rowIndex => $rowData) {
                foreach ($rowData as $columnIndex => $value) {
                    $column = chr(65 + $columnIndex);
                    $sheet->setCellValue($column . ($rowIndex + 2), $value);
                }
            }
            
            // Auto size columns
            foreach (range('A', 'D') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
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

            DB::beginTransaction();

            foreach ($rows as $row) {
                if (empty($row[0])) continue; // Skip empty PART NO

                $partNo = trim($row[0]);
                $checkpointNum = trim($row[1] ?? '');
                $pointCheckName = trim($row[2] ?? '');
                $customStandard = trim($row[3] ?? '');

                if (empty($checkpointNum) && empty($pointCheckName)) continue;

                // 1. Resolve Product
                $product = Product::where('part_no', $partNo)->first();
                if (!$product) continue;

                // 2. Resolve Master Checkpoint
                $masterPointQuery = NpcMasterCheckpoint::query();
                if (!empty($checkpointNum)) {
                    $masterPointQuery->where('point_number', $checkpointNum);
                } elseif (!empty($pointCheckName)) {
                    $masterPointQuery->where('point_check', $pointCheckName);
                }
                
                $masterPoint = $masterPointQuery->first();
                if (!$masterPoint) continue;

                // 3. Delete existing checksheet for this part if not processed yet in this loop
                if (!in_array($product->id, $partsProcessed)) {
                    ProductCheckpoint::where('product_id', $product->id)->delete();
                    $partsProcessed[] = $product->id;
                }

                // 4. Insert checksheet routing
                ProductCheckpoint::create([
                    'product_id' => $product->id,
                    'npc_master_checkpoint_id' => $masterPoint->id,
                    'custom_standard' => $customStandard ?: null,
                ]);

                $importedCount++;
            }

            DB::commit();

            $partCount = count($partsProcessed);
            return redirect()->route('master.checksheets.index')->with('success', "Success! $importedCount checkpoints mapped for $partCount Part(s).");

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed processing Excel: ' . $e->getMessage());
        }
    }
}
