<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcPart;
use App\Models\NpcEvent;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $filterYear = $request->input('chart_year', date('Y'));
        $filterMonth = $request->input('chart_month');
        $filterCustomer = $request->input('chart_customer');
        $filterPo = $request->input('chart_po');
        $filterModel = $request->input('chart_model');

        $applyEventFilters = function($q) use ($filterYear, $filterMonth, $filterCustomer, $filterPo, $filterModel) {
            if ($filterYear) {
                $q->whereYear('created_at', $filterYear);
            }
            if ($filterMonth) {
                $q->whereMonth('created_at', $filterMonth);
            }
            if ($filterCustomer) {
                $q->where('customer_category_id', $filterCustomer);
            }
            if ($filterPo) {
                $q->where('po_no', 'like', "%{$filterPo}%");
            }
            if ($filterModel) {
                $q->whereHas('parts.product', function($q2) use ($filterModel) {
                    $q2->where('model_id', $filterModel);
                });
            }
        };

        // 1. Top Level Metrics (KPI Cards)
        $totalPOQuery = NpcEvent::where($applyEventFilters);
        $totalPO = $totalPOQuery->count();
        $totalPOList = $totalPOQuery->select('id', 'po_no')->get();

        // A PO is complete if it has parts and NONE of its parts are in an active status or just finished
        $poCompleteQuery = NpcEvent::where($applyEventFilters)->whereHas('parts')->whereDoesntHave('parts', function($q) {
            $q->whereNotIn('status', ['CLOSED', 'OUTSTANDING']);
        });
        $poComplete = $poCompleteQuery->count();
        $poCompleteList = $poCompleteQuery->select('id', 'po_no')->get();

        $poOnHandList = clone $totalPOQuery;
        $poOnHandList = NpcEvent::with(['parts.product.vehicleModel'])->where($applyEventFilters)->where(function($q) {
            $q->whereDoesntHave('parts')
              ->orWhereHas('parts', function($q2) {
                  $q2->whereNotIn('status', ['CLOSED', 'OUTSTANDING']);
              });
        })->select('id', 'po_no')->get();
        $poOnHand = $poOnHandList->count();

        $stockQuery = NpcPart::where('status', 'FINISHED')->whereHas('event', $applyEventFilters);
        $stock = $stockQuery->count();
        $stockList = $stockQuery->with(['event' => function($q) {
            $q->select('id', 'po_no');
        }, 'product' => function($q) {
            $q->select('id', 'part_no');
        }])->select('id', 'npc_event_id', 'product_id')->get();

        $metrics = [
            'total_po' => $totalPO,
            'po_on_hand' => $poOnHand,
            'po_complete' => $poComplete,
            'stock' => $stock,
            'total_po_list' => $totalPOList,
            'po_on_hand_list' => $poOnHandList,
            'po_complete_list' => $poCompleteList,
            'stock_list' => $stockList,
        ];

        // 2. Nearest Events
        $nearestEvents = NpcPart::with(['product.vehicleModel', 'product.customer', 'event.customerCategory', 'event.deliveryGroup'])
            ->whereHas('event', $applyEventFilters)
            ->whereNotIn('status', ['CLOSED'])
            ->whereNotNull('delivery_date')
            ->orderBy('delivery_date', 'asc')
            ->take(5)
            ->get();

        // --- DASHBOARD V2 CHARTS DATA ---
        
        // Chart 1 & PO Progress: Plan vs Actual
        $queryEvents = NpcEvent::with(['customerCategory', 'deliveryGroup', 'vehicleModel', 'parts' => function($q) {
            $q->select('id', 'npc_event_id', 'status', 'product_id')->with(['product.customer', 'product.vehicleModel', 'processes', 'processes.department']);
        }]);

        $applyEventFilters($queryEvents);

        $recentEvents = $queryEvents->orderBy('created_at', 'desc')
        ->whereHas('parts', function($q) {
            $q->whereNotIn('status', ['FINISHED', 'CLOSED']); // Only active POs
        })
        ->take(20)
        ->get()
        ->reverse(); // reverse to show oldest of the recent on the left

        $poChunks = [];
        $currentChunk = [];
        
        // Group the events first by PO No and Event Type
        $groupedEvents = [];
        foreach ($recentEvents as $ev) {
            $poLabel = $ev->po_no ? $ev->po_no : 'EV-'.$ev->id;
            $groupKey = $poLabel . '_' . ($ev->customer_category_id ?? '0');
            $grName = $ev->deliveryGroup ? $ev->deliveryGroup->name : 'Unknown Batch';
            
            if (!isset($groupedEvents[$groupKey])) {
                $groupedEvents[$groupKey] = [
                    'id' => $ev->id,
                    'po_no' => $poLabel,
                    'custName' => $ev->customerCategory ? $ev->customerCategory->name : 'Unknown',
                    'modelStr' => $ev->vehicleModel ? $ev->vehicleModel->name : '-',
                    'parts' => [],
                    'batches' => []
                ];
            }
            
            if (!isset($groupedEvents[$groupKey]['batches'][$grName])) {
                $groupedEvents[$groupKey]['batches'][$grName] = [
                    'plan' => 0,
                    'actual' => 0
                ];
            }
            
            $groupedEvents[$groupKey]['batches'][$grName]['plan'] += $ev->parts->count();
            
            foreach ($ev->parts as $part) {
                $groupedEvents[$groupKey]['parts'][] = $part;
                if (in_array($part->status, ['FINISHED', 'CLOSED', 'OUTSTANDING'])) {
                    $groupedEvents[$groupKey]['batches'][$grName]['actual']++;
                }
            }
        }
        
        foreach ($groupedEvents as $group) {
            $poLabel = $group['po_no'];
            $custName = $group['custName'];
            
            // Delivery Group is omitted since multiple batches are aggregated
            $firstLineLabel = $custName . ' (' . $poLabel . ')';

            // Get unique customers and models from parts
            $customers = [];
            foreach($group['parts'] as $part) {
                if ($part->product && $part->product->customer) {
                    $customers[] = $part->product->customer->code;
                }
            }
            $customers = array_unique($customers);
            $customerStr = count($customers) > 0 ? implode(', ', $customers) : '-';
            
            $modelStr = $group['modelStr'];

            // Multi-line label for Chart.js Tooltip
            $chartTooltip = [
                $firstLineLabel,
                $customerStr . ' | ' . $modelStr
            ];
            
            if (count($group['batches']) > 1) {
                $chartTooltip[] = '----------------';
                foreach ($group['batches'] as $grName => $counts) {
                    $chartTooltip[] = $grName . ' - Plan: ' . $counts['plan'] . ', Act: ' . $counts['actual'];
                }
            } elseif (count($group['batches']) == 1) {
                $singleBatch = array_key_first($group['batches']);
                $chartTooltip[1] .= ' | ' . $singleBatch;
            }
            
            // X-Axis label (Full string, let Chart.js handle it)
            $chartLabel = $poLabel;
            
            $totalItems = count($group['parts']);
            $finishedItems = 0;
            $unfinishedItems = 0;
            $deptCounts = [];
            
            foreach($group['parts'] as $part) {
                if (in_array($part->status, ['FINISHED', 'CLOSED', 'OUTSTANDING'])) {
                    $finishedItems++;
                } else {
                    $unfinishedItems++;
                    $currentDept = 'Unknown';
                    if ($part->status === 'PO_REGISTERED') {
                        $currentDept = 'Draft';
                    } elseif ($part->status === 'WAITING_DEPT_CONFIRM') {
                        $activeProc = $part->processes->firstWhere('actual_completion_date', null);
                        if ($activeProc && $activeProc->department) {
                            $currentDept = $activeProc->department->name;
                        } else {
                            $currentDept = 'Produksi';
                        }
                    } elseif ($part->status === 'WAITING_QE_CHECK' || $part->status === 'WAITING_APPROVAL') {
                        $currentDept = 'QC';
                    } elseif ($part->status === 'WAITING_MGM_CHECK') {
                        $currentDept = 'MGM';
                    } elseif ($part->status === 'WAITING_ME_CHECK') {
                        $currentDept = 'ME';
                    }
                    
                    if(!isset($deptCounts[$currentDept])) $deptCounts[$currentDept] = 0;
                    $deptCounts[$currentDept]++;
                }
            }
            
            $inProgressItems = $totalItems - $finishedItems;
            $rate = $totalItems > 0 ? round(($finishedItems / $totalItems) * 100) : 0;
            
            $poData = [
                'id' => $group['id'],
                'po_no' => $poLabel,
                'chartLabel' => $chartLabel,
                'chartTooltip' => $chartTooltip,
                'totalItems' => $totalItems,
                'finishedItems' => $finishedItems,
                'inProgressItems' => $inProgressItems,
                'unfinishedItems' => $unfinishedItems,
                'rate' => $rate,
                'deptCounts' => $deptCounts
            ];
            
            $currentChunk[] = $poData;
            
            if (count($currentChunk) == 5) {
                $poChunks[] = $currentChunk;
                $currentChunk = [];
            }
        }
        
        if (count($currentChunk) > 0) {
            $poChunks[] = $currentChunk;
        }

        // We still need to provide $trendChart in a format that works, or pass chunks directly to view.
        // Let's pass $poChunks directly.

        // Chart 2: Department Workload (Waiting Processes)
        $waitingProcesses = \App\Models\NpcPartProcess::with('department')
            ->whereHas('part.event', $applyEventFilters)
            ->where('status', 'WAITING')
            ->get();
            
        $deptCounts = [];
        foreach ($waitingProcesses as $proc) {
            $deptName = $proc->department ? $proc->department->name : 'Unknown';
            if (!isset($deptCounts[$deptName])) {
                $deptCounts[$deptName] = 0;
            }
            $deptCounts[$deptName]++;
        }
        
        arsort($deptCounts); // Sort high to low
        
        $departmentChart = [
            'labels' => array_keys($deptCounts),
            'data' => array_values($deptCounts)
        ];

        // Chart 3: Customer Proportion (Active Parts)
        $activeParts = NpcPart::with('event.customerCategory')
            ->whereHas('event', $applyEventFilters)
            ->whereNotIn('status', ['CLOSED', 'OUTSTANDING'])
            ->get();
            
        $custCounts = [];
        foreach ($activeParts as $pt) {
            $custName = $pt->event->customerCategory->name ?? 'No Category';
            if (!isset($custCounts[$custName])) {
                $custCounts[$custName] = 0;
            }
            $custCounts[$custName]++;
        }

        arsort($custCounts); // Sort high to low
        
        $customerChart = [
            'labels' => array_keys($custCounts),
            'data' => array_values($custCounts)
        ];

        // --- END CHARTS DATA ---

        // 3. Action Required (To-Do List)
        // a. ECN Updates
        $allEcnUpdates = NpcPart::with(['product.docPackage.currentRevision', 'event.customerCategory', 'drawingRevision'])
            ->whereHas('event', $applyEventFilters)
            ->whereNotIn('status', ['FINISHED', 'CLOSED'])
            ->whereNotNull('part_revision_id')
            ->whereHas('product.docPackage', function ($q) {
                $q->whereColumn('doc_packages.current_revision_id', '!=', 'npc_parts.part_revision_id');
            })
            ->latest()
            ->get();
            
        $ecnUpdates = $allEcnUpdates->groupBy('product_id')->map(function($group) {
            $first = $group->first();
            $first->po_count = $group->count();
            return $first;
        })->values()->take(5);

        // b. Stagnant Parts (No update for > 3 days, excluding finished/closed)
        $stagnantParts = NpcPart::with(['product', 'event.customerCategory'])
            ->whereHas('event', $applyEventFilters)
            ->whereNotIn('status', ['FINISHED', 'CLOSED'])
            ->where('updated_at', '<', Carbon::now()->subDays(3))
            ->orderBy('updated_at', 'asc')
            ->take(5)
            ->get();

        // c. Rolled Back Parts (Action Required)
        $rolledBackParts = NpcPart::with(['product', 'event.customerCategory'])
            ->whereHas('event', $applyEventFilters)
            ->whereNotNull('rollback_reason')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        // 4. Remain Deliveries (Grouped by PO)
        $remainDeliveries = NpcEvent::with(['customerCategory', 'vehicleModel', 'parts.product.vehicleModel'])
            ->where($applyEventFilters)
            ->whereHas('parts', function($q) {
                $q->whereNotIn('status', ['CLOSED', 'OUTSTANDING']);
            })
            ->withCount(['parts as total_items', 'parts as remaining_items' => function($q) {
                $q->whereNotIn('status', ['CLOSED', 'OUTSTANDING']);
            }])
            ->addSelect(['nearest_delivery_date' => NpcPart::select('delivery_date')
                ->whereColumn('npc_event_id', 'npc_events.id')
                ->whereNotIn('status', ['CLOSED', 'OUTSTANDING'])
                ->whereNotNull('delivery_date')
                ->orderBy('delivery_date', 'asc')
                ->limit(1)
            ])
            ->orderBy('nearest_delivery_date', 'asc')
            ->take(5)
            ->get();

        // Filter options for view
        $customerCategories = \App\Models\NpcCustomerCategory::orderBy('name')->get();
        $vehicleModels = \App\Models\VehicleModel::whereIn('id', function($q) { $q->selectRaw('MIN(id)')->from('models')->groupBy('name', 'customer_id'); })->orderBy('name')->get();
        $availableYears = NpcEvent::selectRaw('YEAR(created_at) as year')->distinct()->orderBy('year', 'desc')->pluck('year');
        if ($availableYears->isEmpty()) {
            $availableYears = collect([date('Y')]);
        }

        return view('dashboard', compact(
            'metrics', 'nearestEvents', 'ecnUpdates', 'stagnantParts', 'rolledBackParts', 'remainDeliveries',
            'poChunks', 'departmentChart', 'customerChart',
            'filterYear', 'filterMonth', 'filterCustomer', 'filterPo', 'filterModel', 'customerCategories', 'vehicleModels', 'availableYears'
        ));
    }
}
