<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\NpcMasterStdPart;

class NpcMasterStdPartController extends Controller
{
    public function index(Request $request)
    {
        $query = NpcMasterStdPart::orderBy('name', 'asc');
        
        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $stdParts = $query->paginate(20);
        return view('master.std_parts.index', compact('stdParts'));
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
