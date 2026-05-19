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
        $query = NpcMasterRouting::with(['part', 'process'])
            ->select('part_id')
            ->groupBy('part_id');

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
            
            // Set Headers
            $headers = ['PART NO', 'PROCESS NAME', 'DEPARTMENT NAME', 'SEQUENCE ORDER'];
            foreach ($headers as $index => $header) {
                $column = chr(65 + $index);
                $sheet->setCellValue($column . '1', $header);
                $sheet->getStyle($column . '1')->getFont()->setBold(true);
            }
            
            // Add Sample Data
            $sampleData = [
                ['PART-001', 'Laser Cutting', 'Produksi', 1],
                ['PART-001', 'Bending', 'Produksi', 2],
                ['PART-001', 'Welding', 'Produksi', 3],
                ['PART-002', 'Bending', 'Produksi', 1],
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
                $processName = trim($row[1] ?? '');
                $deptName = trim($row[2] ?? '');
                $seqOrder = (int) ($row[3] ?? 1);

                if (empty($processName) || empty($deptName)) continue;

                // 1. Resolve IDs
                $product = Product::where('part_no', $partNo)->first();
                if (!$product) continue;

                $process = \App\Models\NpcProcess::where('process_name', $processName)->first();
                if (!$process) continue;

                $department = \App\Models\NpcDepartment::where('name', $deptName)->first();
                if (!$department) continue;

                // 2. Delete existing routing for this part if not processed yet in this loop
                if (!in_array($product->id, $partsProcessed)) {
                    NpcMasterRouting::where('part_id', $product->id)->delete();
                    $partsProcessed[] = $product->id;
                }

                // 3. Insert routing
                NpcMasterRouting::create([
                    'part_id' => $product->id,
                    'process_id' => $process->id,
                    'department_id' => $department->id,
                    'sequence_order' => $seqOrder,
                ]);

                $importedCount++;
            }

            DB::commit();
            
            activity()
                ->causedBy(auth()->user())
                ->event('imported')
                ->log("Routing per Part ID - $importedCount rows for $partCount parts");

            $partCount = count($partsProcessed);
            return redirect()->route('master.routings.index')->with('success', "Success! $importedCount routing steps imported for $partCount Part(s).");

        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed processing Excel: ' . $e->getMessage());
        }
    }
}
