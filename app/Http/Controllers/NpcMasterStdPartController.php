<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\NpcMasterStdPart;

class NpcMasterStdPartController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NpcMasterStdPart::query();
            
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', function ($part) {
                    return '<span class="font-semibold text-slate-900 dark:text-white">' . $part->name . '</span>';
                })
                ->editColumn('is_active', function ($part) {
                    if ($part->is_active) {
                        return '<span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border border-green-200 dark:border-green-800">Active</span>';
                    }
                    return '<span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600">Inactive</span>';
                })
                ->addColumn('action', function ($part) {
                    return view('components.datatable-actions', [
                        'editUrl' => route('master.std-parts.edit', $part->hashed_id),
                        'deleteUrl' => route('master.std-parts.destroy', $part->hashed_id),
                        'deleteMessage' => 'Permanently delete this STD part?'
                    ])->render();
                })
                ->rawColumns(['name', 'is_active', 'action'])
                ->make(true);
        }

        return view('master.std_parts.index');
    }

    public function create()
    {
        return view('master.std_parts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:npc_master_std_parts,name',
            'is_active' => 'boolean'
        ]);

        NpcMasterStdPart::create([
            'name' => $request->name,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('master.std-parts.index')->with('success', 'STD Part created successfully.');
    }

    public function edit(NpcMasterStdPart $std_part)
    {
        return view('master.std_parts.edit', compact('std_part'));
    }

    public function update(Request $request, NpcMasterStdPart $std_part)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:npc_master_std_parts,name,' . $std_part->id,
            'is_active' => 'boolean'
        ]);

        $std_part->update([
            'name' => $request->name,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('master.std-parts.index')->with('success', 'STD Part updated successfully.');
    }

    public function destroy(NpcMasterStdPart $std_part)
    {
        $std_part->delete();
        return redirect()->route('master.std-parts.index')->with('success', 'STD Part deleted successfully.');
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set Headers
            $headers = ['STD PART NAME', 'IS ACTIVE (Y/N)'];
            foreach ($headers as $index => $header) {
                $column = chr(65 + $index);
                $sheet->setCellValue($column . '1', $header);
                $sheet->getStyle($column . '1')->getFont()->setBold(true);
            }
            
            // Add Sample Data
            $sampleData = [
                ['BOLT HEX M8X1.25X20', 'Y'],
                ['NUT FLANGE M10', 'Y'],
                ['WASHER FLAT M6', 'N'],
            ];
            
            foreach ($sampleData as $rowIndex => $rowData) {
                foreach ($rowData as $columnIndex => $value) {
                    $column = chr(65 + $columnIndex);
                    $sheet->setCellValue($column . ($rowIndex + 2), $value);
                }
            }
            
            // Auto size columns
            foreach (range('A', 'B') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            $fileName = 'NPC_Master_STD_Part_Template_' . date('Ymd_His') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($tempFile);
            
            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed generating template: ' . $e->getMessage());
        }
    }

    public function importData(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('file')->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Skip Header
            array_shift($rows);

            $importedCount = 0;
            $updatedCount = 0;

            foreach ($rows as $row) {
                $partName = trim($row[0] ?? '');
                $isActiveStr = trim(strtoupper($row[1] ?? 'Y'));

                if (empty($partName)) continue;

                $isActive = ($isActiveStr === 'Y');

                $part = NpcMasterStdPart::where('name', $partName)->first();

                if ($part) {
                    $part->update(['is_active' => $isActive]);
                    $updatedCount++;
                } else {
                    NpcMasterStdPart::create([
                        'name' => $partName,
                        'is_active' => $isActive,
                    ]);
                    $importedCount++;
                }
            }

            return redirect()->route('master.std-parts.index')->with('success', "Success! $importedCount STD Part(s) created and $updatedCount updated from Excel.");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed processing Excel: ' . $e->getMessage());
        }
    }
}
