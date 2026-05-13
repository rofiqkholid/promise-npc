<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcEvent;
use App\Models\NpcPart;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Exception;
use App\Models\NpcDeliveryGroup;
use App\Models\NpcCustomerCategory;

class NpcEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\NpcEvent::with(['customerCategory.customer', 'deliveryGroup', 'parts.product.vehicleModel'])->latest();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('po_no', 'like', "%{$search}%")
                  ->orWhere('delivery_to', 'like', "%{$search}%")
                  ->orWhereHas('customerCategory', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('customer', function($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                              ->orWhere('code', 'like', "%{$search}%");
                        });
                  })
                  ->orWhereHas('deliveryGroup', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $events = $query->paginate(20);
        return view('npc_events.index', compact('events'));
    }

    public function create()
    {
        $customers = \App\Models\Customer::orderBy('name')->get();
        // The models will be fetched dynamically via JS internally in the view
        
        // Ambil data Master Delivery Target
        $delivery_targets = \App\Models\NpcDeliveryTarget::where('is_active', true)->orderBy('target_name')->get();

        // Ambil data Delivery Group
        $delivery_groups = NpcDeliveryGroup::orderBy('name')->get();

        return view('npc_events.create', compact('customers', 'delivery_targets', 'delivery_groups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required', // Needed for flow Validation
            'model_id' => 'required', // Needed to validate parts
            'customer_category_id' => 'required|exists:npc_customer_categories,id',
            'delivery_group_id' => 'required|exists:npc_delivery_groups,id',
            'delivery_to' => 'nullable|string|max:255',
            'po_no' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('npc_events')->where(function ($query) use ($request) {
                    return $query->where('delivery_group_id', $request->delivery_group_id);
                })
            ],
            'parts' => 'required|array|min:1',
            'parts.*.part_no' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::exists('products', 'part_no')->where('model_id', $request->model_id)
            ],
            'parts.*.part_name' => 'nullable|string',
            'parts.*.qty' => 'required|integer|min:1',
            'parts.*.delivery_date' => 'required|date'
        ], [
            'parts.*.part_no.exists' => 'One of the Part Numbers entered is invalid or not part of the Model.',
            'po_no.unique' => 'The combination of PO No and Delivery Group already exists.'
        ]);

        $event = \App\Models\NpcEvent::create([
            'po_no' => $request->po_no,
            'customer_category_id' => $request->customer_category_id,
            'delivery_group_id' => $request->delivery_group_id,
            'delivery_to' => $request->delivery_to,
        ]);

        foreach ($request->parts as $partData) {
            // Coba cari produk berdasarkan part_no
            $product = \App\Models\Product::with('docPackage')->where('part_no', $partData['part_no'])->first();
            $processName = null;
            $departmentName = 'PUD';

            if ($product) {
                // Ambil master routing urutan pertama
                $routing = \App\Models\NpcMasterRouting::with('process')
                            ->where('part_id', $product->id)
                            ->orderBy('sequence_order', 'asc')
                            ->first();

                if ($routing && $routing->process) {
                    $processName = $routing->process->process_name;
                    $departmentName = $routing->process->department ?? 'PUD';
                }
            }

            // Tentukan drawing_revision_id saat ini
            $currentRevisionId = null;
            if ($product && $product->docPackage) {
                $currentRevisionId = $product->docPackage->current_revision_id;
            }

            // 2. Build Part Details
            $part = \App\Models\NpcPart::create([
                'npc_event_id' => $event->id,
                'product_id' => $product ? $product->id : null,
                'part_revision_id' => $currentRevisionId,
                'qty' => $partData['qty'],
                'delivery_date' => $partData['delivery_date'],
                'status' => 'PO_REGISTERED',
            ]);

            // Processes will be configured during the Setup Routing phase natively


        }

        return redirect()->route('events.index')->with('success', 'Event, PO, and Parts successfully added.');
    }

    public function edit(\App\Models\NpcEvent $event)
    {
        $event->load('customerCategory', 'parts.product');
        $masterCustomerId = optional($event->customerCategory)->customer_id;
        $masterModelId = optional(optional($event->parts->first())->product)->model_id;
        
        $customers = \App\Models\Customer::orderBy('name')->get();
        $models = \App\Models\VehicleModel::where('customer_id', $masterCustomerId)->orderBy('name')->get();
        
        $customer_categories = NpcCustomerCategory::where('customer_id', $masterCustomerId)->orderBy('name')->get();
        $delivery_groups = NpcDeliveryGroup::orderBy('name')->get();

        $delivery_targets = \App\Models\NpcDeliveryTarget::where('is_active', true)->orderBy('target_name')->get();

        return view('npc_events.edit', compact('event', 'customers', 'models', 'customer_categories', 'delivery_groups', 'delivery_targets', 'masterCustomerId', 'masterModelId'));
    }

    public function update(Request $request, \App\Models\NpcEvent $event)
    {
        $request->validate([
            'po_no' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('npc_events')->where(function ($query) use ($request) {
                    return $query->where('delivery_group_id', $request->delivery_group_id);
                })->ignore($event->id)
            ],
            'customer_category_id' => 'required|exists:npc_customer_categories,id',
            'delivery_group_id' => 'required|exists:npc_delivery_groups,id',
            'delivery_to' => 'nullable|string|max:255',
        ], [
            'po_no.unique' => 'The combination of PO No and Delivery Group already exists.'
        ]);

        $event->update([
            'po_no' => $request->po_no,
            'customer_category_id' => $request->customer_category_id,
            'delivery_group_id' => $request->delivery_group_id,
            'delivery_to' => $request->delivery_to,
        ]);

        return redirect()->route('events.index')->with('success', 'Event updated successfully.');
    }

    public function destroy(\App\Models\NpcEvent $event)
    {
        $event->delete();
        return redirect()->route('events.index')->with('success', 'Event deleted successfully.');
    }

    public function importForm()
    {
        $customers = \App\Models\Customer::orderBy('name')->get();
        $delivery_targets = \App\Models\NpcDeliveryTarget::where('is_active', true)->orderBy('target_name')->get();
        $delivery_groups = NpcDeliveryGroup::orderBy('name')->get();
        return view('npc_events.import', compact('customers', 'delivery_targets', 'delivery_groups'));
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
            $eventsCreated = []; // Untuk melacak event yang sudah dibuat agar tidak duplikat dlm satu proses

            foreach ($rows as $row) {
                // Mapping Kolom (sesuai template baru):
                // 0: PO NO, 1: PART NO, 2: PART NAME, 3: QTY, 4: DELV DATE, 
                // 5: CUSTOMER CODE, 6: MODEL NAME, 7: EVENT CATEGORY, 8: DELIVERY GROUP, 9: DELIVERY TO
                
                if (empty($row[1])) continue; // Skip empty part no

                $custCode = trim($row[5] ?? '');
                $modelName = trim($row[6] ?? '');
                $catName = trim($row[7] ?? '');
                $groupName = trim($row[8] ?? '');
                $deliveryTo = trim($row[9] ?? '');

                if (empty($custCode) || empty($catName) || empty($groupName)) continue;

                // 1. Resolve IDs
                $customer = \App\Models\Customer::where('code', $custCode)->first();
                if (!$customer) continue;

                $category = \App\Models\NpcCustomerCategory::where('customer_id', $customer->id)
                            ->where('name', $catName)->first();
                if (!$category) {
                    // Opsional: Buat kategori jika tidak ada? Untuk sekarang kita skip jika master datanya tidak ada.
                    continue;
                }

                $delivGroup = \App\Models\NpcDeliveryGroup::where('name', $groupName)->first();
                if (!$delivGroup) continue;

                // 2. Find/Create Event (Batching by PO + Category + Group + DeliveryTo)
                $poNo = $row[0] ?? 'PO-'.time();
                $eventKey = $poNo . '_' . $category->id . '_' . $delivGroup->id . '_' . $deliveryTo;
                if (!isset($eventsCreated[$eventKey])) {
                    $eventsCreated[$eventKey] = NpcEvent::create([
                        'po_no' => $poNo,
                        'customer_category_id' => $category->id,
                        'delivery_group_id' => $delivGroup->id,
                        'delivery_to' => $deliveryTo ?: null,
                    ]);
                }
                $event = $eventsCreated[$eventKey];

                // 3. Process Part
                $deliveryDate = null;
                if (!empty($row[4])) {
                    try {
                        if (is_numeric($row[4])) {
                            $deliveryDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[4])->format('Y-m-d');
                        } else {
                            $deliveryDate = Carbon::parse($row[4])->format('Y-m-d');
                        }
                    } catch (Exception $e) {
                        $deliveryDate = now()->format('Y-m-d');
                    }
                }

                // Cari product, prioritaskan model name jika diisi
                $productQuery = \App\Models\Product::where('part_no', $row[1]);
                if (!empty($modelName)) {
                    $productQuery->whereHas('vehicleModel', function($q) use ($modelName) {
                        $q->where('name', $modelName);
                    });
                }
                $product = $productQuery->first();

                $part = NpcPart::create([
                    'npc_event_id' => $event->id,
                    'product_id' => $product ? $product->id : null,
                    'qty' => (int) ($row[3] ?? 1),
                    'delivery_date' => $deliveryDate,
                    'status' => 'WAITING_DEPT_CONFIRM',
                ]);
                
                $importedCount++;
            }

            $eventCount = count($eventsCreated);
            return redirect()->route('events.index')->with('success', "Success! $eventCount Events created and $importedCount Part(s) imported from Excel.");

        } catch (Exception $e) {
            return back()->with('error', 'Failed processing Excel: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set Headers
            $headers = [
                'PO NO', 'PART NO', 'PART NAME', 'QTY', 'DELV DATE (YYYY-MM-DD)', 
                'CUSTOMER CODE', 'MODEL NAME', 'EVENT CATEGORY', 'DELIVERY GROUP', 'DELIVERY TO'
            ];
            foreach ($headers as $index => $header) {
                $column = chr(65 + $index);
                if ($index >= 26) {
                    // Handle columns after Z if needed, but here only 10 columns
                }
                $sheet->setCellValue($column . '1', $header);
                $sheet->getStyle($column . '1')->getFont()->setBold(true);
            }
            
            // Add Sample Data
            $sampleData = [
                ['PO/2026/001', 'PART-001', 'Sample Part Name', 100, '2026-05-20', 'TOYOTA', 'AVANZA', 'NEW MODEL', 'GR.1', 'CKD-PLANT'],
                ['PO/2026/002', 'PART-002', 'Another Part', 50, '2026-05-25', 'HONDA', 'CIVIC', 'FACELIFT', 'GR.2', ''],
            ];
            
            foreach ($sampleData as $rowIndex => $rowData) {
                foreach ($rowData as $columnIndex => $value) {
                    $column = chr(65 + $columnIndex);
                    $sheet->setCellValue($column . ($rowIndex + 2), $value);
                }
            }
            
            // Auto size columns
            foreach (range('A', 'J') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            $fileName = 'NPC_Bulk_Import_Template_' . date('Ymd_His') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($tempFile);
            
            return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
            
        } catch (Exception $e) {
            return back()->with('error', 'Failed generating template: ' . $e->getMessage());
        }
    }
}
