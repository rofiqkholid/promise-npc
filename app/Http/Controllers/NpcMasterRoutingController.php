<?php

namespace App\Http\Controllers;

use App\Models\NpcMasterRouting;
use App\Models\Product;
use App\Models\NpcProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Exception;

class NpcMasterRoutingController extends Controller
{
    public function index(Request $request)
    {
        $query = NpcMasterRouting::with(['part'])
            ->select('part_id')
            ->distinct();

        if ($request->has('search') && $request->search != '') {
            $query->whereHas('part', function($q) use ($request) {
                $q->where('part_no', 'like', '%' . $request->search . '%')
                  ->orWhere('part_name', 'like', '%' . $request->search . '%');
            });
        }

        $routings = $query->paginate(10);
            
        // We grouped by part_id, so now we fetch the processes for each part
        foreach ($routings as $routing) {
            $routing->processes = NpcMasterRouting::with('process')
                ->where('part_id', $routing->part_id)
                ->orderBy('sequence_order')
                ->get();
        }

        return view('master.routings.index', compact('routings'));
    }

    public function create()
    {
        $processes = NpcProcess::with('departments')->orderBy('process_name')->get();
        return view('master.routings.create', compact('processes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'part_id' => 'required|exists:products,id',
            'process_ids' => 'required|array|min:1',
            'process_ids.*' => 'required|exists:npc_processes,id',
            'department_ids' => 'required|array|min:1',
            'department_ids.*' => 'required|exists:npc_departments,id',
        ]);

        DB::transaction(function () use ($request) {
            // Delete yang lama jika sudah ada (opsional jika ini create)
            NpcMasterRouting::where('part_id', $request->part_id)->delete();

            foreach ($request->process_ids as $index => $processId) {
                NpcMasterRouting::create([
                    'part_id' => $request->part_id,
                    'process_id' => $processId,
                    'department_id' => $request->department_ids[$index],
                    'sequence_order' => $index + 1,
                ]);
            }
        });
        
        $part = Product::findOrFail($request->part_id);
        activity()
            ->causedBy(auth()->user())
            ->performedOn($part)
            ->event('created')
            ->log('Routing per Part ID');

        return redirect()->route('master.routings.index')->with('success', 'Routing Master successfully saved.');
    }

    public function edit($part_id)
    {
        if (!is_numeric($part_id)) {
            $hashids = new \Hashids\Hashids(env('APP_KEY'), 10);
            $decoded = $hashids->decode($part_id);
            $part_id = !empty($decoded) ? $decoded[0] : abort(404);
        }

        $part = Product::findOrFail($part_id);
        $routings = NpcMasterRouting::where('part_id', $part_id)->orderBy('sequence_order')->get();
        $processes = NpcProcess::with('departments')->orderBy('process_name')->get();

        return view('master.routings.edit', compact('part', 'routings', 'processes'));
    }

    public function update(Request $request, $part_id)
    {
        if (!is_numeric($part_id)) {
            $hashids = new \Hashids\Hashids(env('APP_KEY'), 10);
            $decoded = $hashids->decode($part_id);
            $part_id = !empty($decoded) ? $decoded[0] : abort(404);
        }

        $request->validate([
            'part_id' => 'required|exists:products,id',
            'process_ids' => 'required|array|min:1',
            'process_ids.*' => 'required|exists:npc_processes,id',
            'department_ids' => 'required|array|min:1',
            'department_ids.*' => 'required|exists:npc_departments,id',
        ]);

        DB::transaction(function () use ($request, $part_id) {
            // Delete old routing for the original part_id
            NpcMasterRouting::where('part_id', $part_id)->delete();
            
            // If the part was changed, delete any existing routings for the new part_id to prevent duplicates
            if ($part_id != $request->part_id) {
                NpcMasterRouting::where('part_id', $request->part_id)->delete();
            }

            foreach ($request->process_ids as $index => $processId) {
                NpcMasterRouting::create([
                    'part_id' => $request->part_id,
                    'process_id' => $processId,
                    'department_id' => $request->department_ids[$index],
                    'sequence_order' => $index + 1,
                ]);
            }
        });
        
        $part = Product::findOrFail($request->part_id);
        activity()
            ->causedBy(auth()->user())
            ->performedOn($part)
            ->event('updated')
            ->log('Routing per Part ID');

        return redirect()->route('master.routings.index')->with('success', 'Routing Master successfully updated.');
    }

    public function destroy($part_id)
    {
        if (!is_numeric($part_id)) {
            $hashids = new \Hashids\Hashids(env('APP_KEY'), 10);
            $decoded = $hashids->decode($part_id);
            $part_id = !empty($decoded) ? $decoded[0] : abort(404);
        }

        $part = Product::findOrFail($part_id);
        activity()
            ->causedBy(auth()->user())
            ->performedOn($part)
            ->event('deleted')
            ->log('Routing per Part ID');

        NpcMasterRouting::where('part_id', $part_id)->delete();
        return redirect()->route('master.routings.index')->with('success', 'Routing Master successfully deleted.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:npc_master_routings,id'
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->order as $index => $id) {
                NpcMasterRouting::where('id', $id)->update(['sequence_order' => $index + 1]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Routing sequence successfully updated.']);
    }

    public function importForm()
    {
        return view('master.routings.import');
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template Import');
            
            // --- Create MasterData Sheet for Dropdown Reference ---
            $masterSheet = $spreadsheet->createSheet();
            $masterSheet->setTitle('MasterData');
            $masterSheet->setCellValue('A1', 'PROCESS NAME');
            $masterSheet->setCellValue('B1', 'DEPARTMENT NAME');
            $masterSheet->getStyle('A1:B1')->getFont()->setBold(true);
            
            $processes = \App\Models\NpcProcess::orderBy('process_name')->get();
            $rowProc = 2;
            foreach ($processes as $p) {
                $masterSheet->setCellValue('A' . $rowProc++, $p->process_name);
            }
            
            $departments = \App\Models\NpcDepartment::orderBy('name')->get();
            $rowDept = 2;
            foreach ($departments as $d) {
                $masterSheet->setCellValue('B' . $rowDept++, $d->name);
            }
            
            $masterSheet->getColumnDimension('A')->setAutoSize(true);
            $masterSheet->getColumnDimension('B')->setAutoSize(true);
            // Hide the MasterData sheet so it doesn't confuse users
            $masterSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

            // --- Setup Template Import Sheet ---
            $spreadsheet->setActiveSheetIndex(0);
            
            // Set Headers
            $headers = ['PART NO'];
            $maxSteps = 15; // Provide columns for up to 15 routing steps
            
            for ($i = 1; $i <= $maxSteps; $i++) {
                $headers[] = "PROCESS $i";
                $headers[] = "DEPARTMENT $i";
            }

            foreach ($headers as $index => $header) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index + 1);
                $sheet->setCellValue($column . '1', $header);
                $sheet->getStyle($column . '1')->getFont()->setBold(true);
                
                // Color coding for easier reading
                if ($index > 0) {
                    $color = ($index % 2 == 1) ? 'FFD9EAD3' : 'FFFCE5CD'; // Light green for Process, Light orange for Dept
                    $sheet->getStyle($column . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($color);
                }
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // --- Data Validation (Dropdowns) ---
            $procValidation = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
            $procValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $procValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $procValidation->setAllowBlank(true);
            $procValidation->setShowDropDown(true);
            $procValidation->setErrorTitle('Invalid Process');
            $procValidation->setError('Please select a valid Process from the dropdown list.');
            $procValidation->setFormula1('\'MasterData\'!$A$2:$A$' . ($processes->count() + 1));

            $deptValidation = new \PhpOffice\PhpSpreadsheet\Cell\DataValidation();
            $deptValidation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $deptValidation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $deptValidation->setAllowBlank(true);
            $deptValidation->setShowDropDown(true);
            $deptValidation->setErrorTitle('Invalid Department');
            $deptValidation->setError('Please select a valid Department from the dropdown list.');
            $deptValidation->setFormula1('\'MasterData\'!$B$2:$B$' . ($departments->count() + 1));

            // Apply validation to first 500 rows
            for ($row = 2; $row <= 500; $row++) {
                for ($step = 1; $step <= $maxSteps; $step++) {
                    $procCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 * $step);
                    $deptCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(2 * $step + 1);
                    
                    $sheet->getCell($procCol . $row)->setDataValidation(clone $procValidation);
                    $sheet->getCell($deptCol . $row)->setDataValidation(clone $deptValidation);
                }
            }
            
            $writer = new Xlsx($spreadsheet);
            $fileName = 'Routing_Proses_Import_Template_' . date('Ymd_His') . '.xlsx';
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
            $worksheet = $spreadsheet->getSheetByName('Template Import');
            if (!$worksheet) {
                $worksheet = $spreadsheet->getActiveSheet();
            }
            $rows = $worksheet->toArray();
            
            // Skip Header
            $headers = array_shift($rows);

            $importedCount = 0;
            $partsProcessed = [];
            $rowErrors = [];
            $validRows = [];

            // Pre-load all processes and departments to minimize DB calls
            $masterProcesses = \App\Models\NpcProcess::all()->keyBy('process_name');
            $masterDepartments = \App\Models\NpcDepartment::all();
            
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

                $sequence = 1;
                $hasRouting = false;
                $partValidRows = [];
                $errorCountBefore = count($rowErrors);

                // Iterate through maximum expected steps (e.g. 15 steps -> columns up to index 30)
                $maxCols = count($row);
                for ($step = 1; $step <= 15; $step++) {
                    $procIndex = 2 * $step - 1; // 1, 3, 5...
                    $deptIndex = 2 * $step;     // 2, 4, 6...
                    
                    if ($procIndex >= $maxCols && $deptIndex >= $maxCols) {
                        break;
                    }

                    $processName = trim($row[$procIndex] ?? '');
                    $deptName = trim($row[$deptIndex] ?? '');

                    if (empty($processName) && empty($deptName)) {
                        continue; // Skip empty step
                    }

                    if (empty($processName) || empty($deptName)) {
                        $rowErrors[] = "Row {$actualRowNumber}: Step {$step} is incomplete. Both Process and Department must be selected.";
                        break;
                    }

                    $process = $masterProcesses->get($processName);
                    if (!$process) {
                        $rowErrors[] = "Row {$actualRowNumber}: Process '{$processName}' (Step {$step}) is not registered.";
                        break;
                    }

                    $department = $masterDepartments->firstWhere('name', $deptName);
                    if (!$department) {
                        $department = $masterDepartments->firstWhere('full_name', $deptName);
                    }
                    
                    if (!$department) {
                        $rowErrors[] = "Row {$actualRowNumber}: Department '{$deptName}' (Step {$step}) is not found.";
                        break;
                    }

                    if (!$process->departments()->where('npc_departments.id', $department->id)->exists()) {
                        $rowErrors[] = "Row {$actualRowNumber}: Process '{$processName}' is not linked to Department '{$deptName}'. Please check Master Process.";
                        break;
                    }

                    $partValidRows[] = [
                        'product_id' => $product->id,
                        'process_id' => $process->id,
                        'department_id' => $department->id,
                        'sequence_order' => $sequence++
                    ];
                    $hasRouting = true;
                }

                if (count($rowErrors) == $errorCountBefore && $hasRouting) {
                    if (!in_array($product->id, $partsProcessed)) {
                        $partsProcessed[] = $product->id;
                    }
                    $validRows = array_merge($validRows, $partValidRows);
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

            foreach ($partsProcessed as $productId) {
                NpcMasterRouting::where('part_id', $productId)->delete();
            }

            foreach ($validRows as $valid) {
                NpcMasterRouting::create([
                    'part_id' => $valid['product_id'],
                    'process_id' => $valid['process_id'],
                    'department_id' => $valid['department_id'],
                    'sequence_order' => $valid['sequence_order'],
                ]);
                $importedCount++;
            }

            $partCount = count($partsProcessed);
            
            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->event('imported')
                ->log("Routing per Part ID - $importedCount rows for $partCount parts");

            return redirect()->route('master.routings.index')->with('success', "Success! $importedCount routing steps imported for $partCount Part(s).");

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed processing Excel: ' . $e->getMessage());
        }
    }
}
