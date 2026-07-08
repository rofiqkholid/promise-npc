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
        if ($request->ajax()) {
            $query = \App\Models\NpcEvent::with(['customerCategory.customer', 'deliveryGroup', 'parts.product.vehicleModel']);

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('event_name', function ($event) {
                    $html = '<div class="text-blue-900 dark:text-blue-400 font-semibold text-sm">' . $event->po_no . '</div>';
                    $html .= '<div class="text-xs text-slate-500 font-normal mt-0.5">' . (optional($event->customerCategory)->name ?? '-') . '</div>';
                    return $html;
                })
                ->addColumn('customer', function ($event) {
                    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-medium">' . (optional(optional($event->customerCategory)->customer)->code ?? '-') . '</span>';
                })
                ->addColumn('model', function ($event) {
                    return '<span class="text-gray-600 dark:text-gray-400 text-sm font-medium">' . (optional(optional(optional($event->parts->first())->product)->vehicleModel)->name ?? '-') . '</span>';
                })
                ->addColumn('category', function ($event) {
                    return '<span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">' . (optional($event->customerCategory)->name ?? '-') . '</span>';
                })
                ->addColumn('gr', function ($event) {
                    return '<span class="text-gray-600 dark:text-gray-400 text-sm font-medium">' . (optional($event->deliveryGroup)->name ?? '-') . '</span>';
                })
                ->addColumn('delivery_to', function ($event) {
                    return '<span class="text-gray-600 dark:text-gray-400 text-sm font-medium">' . ($event->delivery_to ?? '-') . '</span>';
                })
                ->addColumn('action', function ($event) {
                    return view('components.datatable-actions', [
                        'editUrl' => route('events.edit', $event->hashed_id),
                        'deleteUrl' => route('events.destroy', $event->hashed_id),
                        'deleteMessage' => 'Are you sure you want to delete this data?',
                        'extraButtons' => '<a href="' . route('events.parts.index', $event->hashed_id) . '" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 transition" title="Parts List"><i class="fa-solid fa-list-check"></i></a>'
                    ])->render();
                })
                ->filterColumn('event_name', function($query, $keyword) {
                    $query->where('po_no', 'like', "%{$keyword}%")
                          ->orWhereHas('customerCategory', function($q) use ($keyword) {
                              $q->where('name', 'like', "%{$keyword}%");
                          });
                })
                ->filterColumn('customer', function($query, $keyword) {
                    $query->whereHas('customerCategory.customer', function($q) use ($keyword) {
                        $q->where('code', 'like', "%{$keyword}%")
                          ->orWhere('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('model', function($query, $keyword) {
                    $query->whereHas('parts.product.vehicleModel', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('category', function($query, $keyword) {
                    $query->whereHas('customerCategory', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('gr', function($query, $keyword) {
                    $query->whereHas('deliveryGroup', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['event_name', 'customer', 'model', 'category', 'gr', 'delivery_to', 'action'])
                ->make(true);
        }

        return view('npc_events.index');
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
                \Illuminate\Validation\Rule::exists('products', 'part_no')->where('customer_id', $request->customer_id)
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
            // Coba cari produk berdasarkan part_no dan customer_id
            $product = \App\Models\Product::with('docPackage')
                ->where('part_no', $partData['part_no'])
                ->where('customer_id', $request->customer_id)
                ->first();
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
            'customer_id' => 'required',
            'model_id' => 'required',
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
            'parts' => 'required|array|min:1',
            'parts.*.part_no' => [
                'required',
                'string',
                \Illuminate\Validation\Rule::exists('products', 'part_no')->where('customer_id', $request->customer_id)
            ],
            'parts.*.part_name' => 'nullable|string',
            'parts.*.qty' => 'required|integer|min:1',
            'parts.*.delivery_date' => 'required|date'
        ], [
            'parts.*.part_no.exists' => 'One of the Part Numbers entered is invalid or not part of the Model.',
            'po_no.unique' => 'The combination of PO No and Delivery Group already exists.'
        ]);

        $event->update([
            'po_no' => $request->po_no,
            'customer_category_id' => $request->customer_category_id,
            'delivery_group_id' => $request->delivery_group_id,
            'delivery_to' => $request->delivery_to,
        ]);

        $existingPartIds = [];
        foreach ($request->parts as $partData) {
            $product = \App\Models\Product::with('docPackage')
                ->where('part_no', $partData['part_no'])
                ->where('customer_id', $request->customer_id)
                ->first();
            if (!$product) continue;

            $currentRevisionId = null;
            if ($product && $product->docPackage) {
                $currentRevisionId = $product->docPackage->current_revision_id;
            }

            if (isset($partData['id']) && !empty($partData['id'])) {
                // Update existing part
                $part = \App\Models\NpcPart::find($partData['id']);
                if ($part && $part->npc_event_id == $event->id) {
                    $part->update([
                        'product_id' => $product->id,
                        'part_revision_id' => $currentRevisionId,
                        'qty' => $partData['qty'],
                        'delivery_date' => $partData['delivery_date'],
                    ]);
                    $existingPartIds[] = $part->id;
                }
            } else {
                // Create new part
                $part = \App\Models\NpcPart::create([
                    'npc_event_id' => $event->id,
                    'product_id' => $product->id,
                    'part_revision_id' => $currentRevisionId,
                    'qty' => $partData['qty'],
                    'delivery_date' => $partData['delivery_date'],
                    'status' => 'PO_REGISTERED',
                ]);
                $existingPartIds[] = $part->id;
            }
        }

        // Delete parts that were removed from the form, but only if they haven't started processing
        \App\Models\NpcPart::where('npc_event_id', $event->id)
            ->whereNotIn('id', $existingPartIds)
            ->where('status', 'PO_REGISTERED')
            ->delete();

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

            \Illuminate\Support\Facades\DB::beginTransaction();

            $importedCount = 0;
            $eventsCreated = []; // Untuk melacak event yang sudah dibuat agar tidak duplikat dlm satu proses
            $rowErrors = [];

            foreach ($rows as $index => $row) {
                // Mapping Kolom (sesuai template baru):
                // 0: PO NO, 1: CUSTOMER CODE, 2: MODEL NAME, 3: EVENT CATEGORY, 4: DELIVERY GROUP, 5: DELIVERY TO, 6: DELV DATE, 7: PART NO, 8: PART NAME, 9: QTY
                
                $actualRowNumber = $index + 2;

                $partNo = trim($row[7] ?? '');
                if (empty($partNo)) continue; // Skip empty part no

                $poNo = trim($row[0] ?? '');
                $custCode = trim($row[1] ?? '');
                $modelName = trim($row[2] ?? '');
                $catName = trim($row[3] ?? '');
                $groupName = trim($row[4] ?? '');
                $deliveryTo = trim($row[5] ?? '');
                
                $deliveryDateRaw = $row[6] ?? null;
                $partName = trim($row[8] ?? '');
                $qty = (int) ($row[9] ?? 1);

                if (empty($custCode) || empty($catName) || empty($groupName)) {
                    $rowErrors[] = "Row {$actualRowNumber}: CUSTOMER CODE, EVENT CATEGORY, and DELIVERY GROUP are required.";
                    continue;
                }

                // 1. Resolve IDs
                $customer = \App\Models\Customer::where('code', $custCode)->first();
                if (!$customer) {
                    $rowErrors[] = "Row {$actualRowNumber}: Customer Code '{$custCode}' not found in system.";
                    continue;
                }

                $category = \App\Models\NpcCustomerCategory::where('customer_id', $customer->id)
                            ->where('name', $catName)->first();
                if (!$category) {
                    $rowErrors[] = "Row {$actualRowNumber}: Event Category '{$catName}' not found for Customer '{$custCode}'.";
                    continue;
                }

                $delivGroup = \App\Models\NpcDeliveryGroup::where('name', $groupName)->first();
                if (!$delivGroup) {
                    $rowErrors[] = "Row {$actualRowNumber}: Delivery Group '{$groupName}' not found.";
                    continue;
                }

                // Cari product, prioritaskan model name jika diisi
                $productQuery = \App\Models\Product::with('docPackage')->where('part_no', $partNo);
                if (!empty($modelName)) {
                    $productQuery->whereHas('vehicleModel', function($q) use ($modelName) {
                        $q->where('name', $modelName);
                    });
                }
                $product = $productQuery->first();

                if (!$product) {
                    $rowErrors[] = "Row {$actualRowNumber}: Product Part No '{$partNo}' (Model: '{$modelName}') not found.";
                    continue;
                }

                // Parse Delivery Date first
                $deliveryDate = null;
                if (!empty($deliveryDateRaw)) {
                    try {
                        if (is_numeric($deliveryDateRaw)) {
                            $deliveryDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($deliveryDateRaw)->format('Y-m-d');
                        } else {
                            $deliveryDate = Carbon::parse($deliveryDateRaw)->format('Y-m-d');
                        }
                    } catch (Exception $e) {
                        $deliveryDate = now()->format('Y-m-d');
                    }
                }

                // 2. Find/Create Event (Batching by PO)
                $poNo = $poNo ?: 'PO-'.time();
                $eventKey = $poNo;
                
                if (!isset($eventsCreated[$eventKey])) {
                    $existingEvent = \App\Models\NpcEvent::where('po_no', $poNo)->first();
                    if ($existingEvent) {
                        $eventsCreated[$eventKey] = $existingEvent;
                    } else {
                        $eventsCreated[$eventKey] = NpcEvent::create([
                            'po_no' => $poNo,
                            'customer_category_id' => $category->id,
                            'delivery_group_id' => $delivGroup->id,
                            'delivery_to' => $deliveryTo ?: null,
                        ]);
                    }
                }
                
                $event = $eventsCreated[$eventKey];

                // Validasi kesamaan kunci (karena append ke PO yang sudah ada / di cache)
                if ($event->delivery_group_id != $delivGroup->id || 
                    $event->customer_category_id != $category->id || 
                    $event->delivery_to != $deliveryTo) {
                    $rowErrors[] = "Row {$actualRowNumber}: PO '{$poNo}' exists but with different Group, Category, or Delivery To. Rejected.";
                    continue;
                }

                // Validasi Delivery Date (harus sama dengan item yang sudah ada di PO tersebut)
                $firstPart = \App\Models\NpcPart::where('npc_event_id', $event->id)->first();
                if ($firstPart && $firstPart->delivery_date != $deliveryDate) {
                    $rowErrors[] = "Row {$actualRowNumber}: PO '{$poNo}' exists but has a different Delivery Date ({$firstPart->delivery_date}). Rejected.";
                    continue;
                }

                // Validasi Item Berbeda (tidak boleh ada part yang sama di dalam satu PO)
                $partExists = \App\Models\NpcPart::where('npc_event_id', $event->id)
                                    ->where('product_id', $product->id)
                                    ->exists();
                if ($partExists) {
                    $rowErrors[] = "Row {$actualRowNumber}: Part '{$partNo}' already exists in PO '{$poNo}'. Rejected.";
                    continue;
                }

                // 3. Process Part
                // Tentukan drawing_revision_id saat ini
                $currentRevisionId = null;
                if ($product && $product->docPackage) {
                    $currentRevisionId = $product->docPackage->current_revision_id;
                }

                $part = NpcPart::create([
                    'npc_event_id' => $event->id,
                    'product_id' => $product->id,
                    'part_revision_id' => $currentRevisionId,
                    'qty' => $qty,
                    'delivery_date' => $deliveryDate,
                    'status' => 'PO_REGISTERED',
                ]);
                
                $importedCount++;
            }

            if (!empty($rowErrors)) {
                \Illuminate\Support\Facades\DB::rollBack();
                $errorList = "<ul class='list-disc pl-5 mt-2 space-y-1'>";
                foreach (array_slice($rowErrors, 0, 10) as $err) {
                    $errorList .= "<li>{$err}</li>";
                }
                if (count($rowErrors) > 10) {
                    $errorList .= "<li>...and " . (count($rowErrors) - 10) . " more errors.</li>";
                }
                $errorList .= "</ul>";
                
                return back()->with('error_details', "<strong>Import failed due to data mismatch:</strong>" . $errorList)
                             ->with('error', 'Import failed. Please check the details on the page.');
            }

            \Illuminate\Support\Facades\DB::commit();
            $eventCount = count($eventsCreated);
            return redirect()->route('events.index')->with('success', "Success! $eventCount PO(s) processed and $importedCount Part(s) imported from Excel.");

        } catch (Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
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
                'PO NO', 'CUSTOMER CODE', 'MODEL NAME', 'EVENT CATEGORY', 'DELIVERY GROUP', 'DELIVERY TO',
                'DELV DATE (YYYY-MM-DD)', 'PART NO', 'PART NAME', 'QTY'
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
