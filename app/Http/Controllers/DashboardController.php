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
                $q->whereHas('parts.product', function($q2) use ($filterCustomer) {
                    $q2->where('customer_id', $filterCustomer);
                });
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

        $noFilters = function($q) {};

        // 1. Top Level Metrics (KPI Cards)
        $totalPOQuery = NpcEvent::where($noFilters);
        $totalPOList = $totalPOQuery->select('id', 'po_no')->get()->unique(function($item) {
            return $item->po_no ?: 'EV-'.$item->id;
        })->values();
        $totalPO = $totalPOList->count();

        // A PO is complete if it has parts and NONE of its parts are in an active status or just finished
        $poCompleteQuery = NpcEvent::where($noFilters)->whereHas('parts')->whereDoesntHave('parts', function($q) {
            $q->whereNotIn('status', ['CLOSED', 'OUTSTANDING']);
        });
        $poCompleteList = $poCompleteQuery->select('id', 'po_no')->get()->unique(function($item) {
            return $item->po_no ?: 'EV-'.$item->id;
        })->values();
        $poComplete = $poCompleteList->count();

        $poOnHandList = clone $totalPOQuery;
        $poOnHandList = NpcEvent::with(['parts.product.vehicleModel'])->where($noFilters)->where(function($q) {
            $q->whereDoesntHave('parts')
              ->orWhereHas('parts', function($q2) {
                  $q2->whereNotIn('status', ['CLOSED', 'OUTSTANDING']);
              });
        })->select('id', 'po_no')->get()->unique(function($item) {
            return $item->po_no ?: 'EV-'.$item->id;
        })->values();
        $poOnHand = $poOnHandList->count();

        $stockQuery = NpcPart::where('status', 'FINISHED')->whereHas('event', $noFilters);
        $stockList = $stockQuery->with(['event' => function($q) {
            $q->select('id', 'po_no');
        }, 'product' => function($q) {
            $q->select('id', 'part_no');
        }])->select('id', 'npc_event_id', 'product_id')->get()->unique(function($item) {
            return ($item->event && $item->event->po_no) ? $item->event->po_no : 'EV-'.($item->npc_event_id ?? $item->id);
        })->values();
        $stock = $stockList->count();

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
            ->whereHas('event', $noFilters)
            ->whereNotIn('status', ['CLOSED'])
            ->whereNotNull('delivery_date')
            ->orderBy('delivery_date', 'asc')
            ->take(5)
            ->get();

        // --- DASHBOARD V2 CHARTS DATA ---
        
        // Chart 1: Plan vs Actual
        $isFiltered = !empty($filterCustomer) || !empty($filterPo) || !empty($filterModel) || $filterYear != date('Y') || !empty($filterMonth);
        $poChunks = $this->buildCustomerChartChunks($applyEventFilters, $isFiltered);

        // Chart 1b: PO Progress
        $progressYear = $request->input('progress_year', date('Y'));
        $progressMonth = $request->input('progress_month');
        $progressCustomer = $request->input('progress_customer');
        $progressPo = $request->input('progress_po');
        $progressModel = $request->input('progress_model');

        $applyProgressFilters = function($q) use ($progressYear, $progressMonth, $progressCustomer, $progressPo, $progressModel) {
            if ($progressYear) {
                $q->whereYear('created_at', $progressYear);
            }
            if ($progressMonth) {
                $q->whereMonth('created_at', $progressMonth);
            }
            if ($progressCustomer) {
                $q->whereHas('parts.product', function($q2) use ($progressCustomer) {
                    $q2->where('customer_id', $progressCustomer);
                });
            }
            if ($progressPo) {
                $q->where('po_no', 'like', "%{$progressPo}%");
            }
            if ($progressModel) {
                $q->whereHas('parts.product', function($q2) use ($progressModel) {
                    $q2->where('model_id', $progressModel);
                });
            }
        };

        $progressChunks = $this->buildChartChunks($applyProgressFilters);

        // We still need to provide $trendChart in a format that works, or pass chunks directly to view.
        // Let's pass $poChunks directly.

        // Chart 2: Department Workload (Waiting Processes)
        $waitingProcesses = \App\Models\NpcPartProcess::with('department')
            ->whereHas('part.event', $noFilters)
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
            ->whereHas('event', $noFilters)
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
            ->whereHas('event', $noFilters)
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
            ->whereHas('event', $noFilters)
            ->whereNotIn('status', ['FINISHED', 'CLOSED'])
            ->where('updated_at', '<', Carbon::now()->subDays(3))
            ->orderBy('updated_at', 'asc')
            ->take(5)
            ->get();

        // c. Rolled Back Parts (Action Required)
        $rolledBackParts = NpcPart::with(['product', 'event.customerCategory'])
            ->whereHas('event', $noFilters)
            ->whereNotNull('rollback_reason')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        // 4. Remain Deliveries (Grouped by PO)
        $remainDeliveries = NpcEvent::with(['customerCategory', 'vehicleModel', 'parts.product.vehicleModel'])
            ->where($noFilters)
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
        $customers = \App\Models\Customer::orderBy('code')->get();
        $vehicleModels = \App\Models\VehicleModel::whereIn('id', function($q) { $q->selectRaw('MIN(id)')->from('models')->groupBy('name', 'customer_id'); })->orderBy('name')->get();
        $availableYears = NpcEvent::selectRaw('YEAR(created_at) as year')->distinct()->orderBy('year', 'desc')->pluck('year');
        if ($availableYears->isEmpty()) {
            $availableYears = collect([date('Y')]);
        }

        return view('dashboard', compact(
            'metrics', 'nearestEvents', 'ecnUpdates', 'stagnantParts', 'rolledBackParts', 'remainDeliveries',
            'poChunks', 'progressChunks', 'departmentChart', 'customerChart',
            'filterYear', 'filterMonth', 'filterCustomer', 'filterPo', 'filterModel', 
            'progressYear', 'progressMonth', 'progressCustomer', 'progressPo', 'progressModel',
            'customers', 'vehicleModels', 'availableYears'
        ));
    }

    private function buildChartChunks($applyFilterCallback)
    {
        $queryEvents = NpcEvent::with(['customerCategory', 'deliveryGroup', 'vehicleModel', 'parts' => function($q) {
            $q->select('id', 'npc_event_id', 'status', 'product_id')->with(['product.customer', 'product.vehicleModel', 'processes', 'processes.department']);
        }]);

        $applyFilterCallback($queryEvents);

        $recentEvents = $queryEvents->orderBy('created_at', 'desc')
        ->whereHas('parts', function($q) {
            $q->whereNotIn('status', ['FINISHED', 'CLOSED']); // Only active POs
        })
        ->take(20)
        ->get()
        ->reverse(); // reverse to show oldest of the recent on the left

        $chunks = [];
        $currentChunk = [];
        
        // Group the events first by PO No and Event Type
        $groupedEvents = [];
        foreach ($recentEvents as $ev) {
            $poLabel = $ev->po_no ? $ev->po_no : 'EV-'.$ev->id;
            $groupKey = $poLabel;
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
                    $finishedItems++; // Keep this for overall logic if needed
                }
                
                $unfinishedItems++; // Just increment for all or let it be
                
                $currentDept = 'NPC'; // default
                
                if ($part->status === 'PO_REGISTERED') {
                    $currentDept = 'NPC';
                } elseif ($part->status === 'WAITING_DEPT_CONFIRM' || $part->status === 'WAITING_ME_CHECK') {
                    $currentDept = 'Production';
                } elseif ($part->status === 'WAITING_QE_CHECK' || $part->status === 'WAITING_APPROVAL') {
                    $currentDept = 'Quality';
                } elseif ($part->status === 'WAITING_MGM_CHECK') {
                    $currentDept = 'MGM';
                } elseif ($part->status === 'FINISHED') {
                    $currentDept = 'Finished';
                } elseif ($part->status === 'CLOSED' || $part->status === 'OUTSTANDING') {
                    $currentDept = 'Closed';
                }
                
                if(!isset($deptCounts[$currentDept])) $deptCounts[$currentDept] = 0;
                $deptCounts[$currentDept]++;
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
                $chunks[] = $currentChunk;
                $currentChunk = [];
            }
        }
        
        if (count($currentChunk) > 0) {
            $chunks[] = $currentChunk;
        }
        
        return $chunks;
    }

    private function buildCustomerChartChunks($applyFilterCallback, $isFiltered = false)
    {
        $queryEvents = NpcEvent::with(['customerCategory', 'deliveryGroup', 'vehicleModel', 'parts' => function($q) {
            $q->select('id', 'npc_event_id', 'status', 'product_id')->with(['product.customer', 'product.vehicleModel', 'processes', 'processes.department']);
        }]);

        $applyFilterCallback($queryEvents);

        $recentEvents = $queryEvents->orderBy('created_at', 'desc')
        ->whereHas('parts', function($q) {
            $q->whereNotIn('status', ['FINISHED', 'CLOSED']); // Only active POs
        })
        ->get();

        $chunks = [];
        $currentChunk = [];
        
        // Group the events
        $groupedData = [];
        foreach ($recentEvents as $ev) {
            foreach ($ev->parts as $part) {
                if ($part->product && $part->product->customer) {
                    $custCode = $part->product->customer->code;
                    $custName = $part->product->customer->name;
                    $poNo = $ev->po_no ?? 'EV-'.$ev->id;
                    $catName = $ev->customerCategory ? $ev->customerCategory->name : 'Unknown';
                    $internalCatId = $ev->customerCategory ? $ev->customerCategory->internal_category_id : 999;
                    
                    if ($isFiltered) {
                        $groupKey = $custCode . '-' . $poNo . '-' . $catName; // Group by PO AND Category
                        $chartLabel = $catName; // The label on X-axis is Event (Category)
                        $tooltipTitle = $catName . ' (' . $custCode . ') - ' . $poNo;
                        $sortOrder = $internalCatId; // Sort by internal category ID
                        $stackKey = $poNo; // The sub-item inside the bar/tooltip is PO No
                    } else {
                        $groupKey = $custCode; // Group by customer
                        $chartLabel = $custCode; // The label on X-axis is Customer Code
                        $tooltipTitle = $custCode;
                        $sortOrder = 0; // Don't sort customers by category
                        $stackKey = $catName; // The sub-item inside the bar/tooltip is Category
                    }
                    
                    if (!isset($groupedData[$groupKey])) {
                        $groupedData[$groupKey] = [
                            'key' => $groupKey,
                            'chartLabel' => $chartLabel,
                            'tooltipTitle' => $tooltipTitle,
                            'sortOrder' => $sortOrder,
                            'totalItems' => 0,
                            'categories' => [],
                            'deliveryGroups' => []
                        ];
                    }
                    
                    if ($ev->deliveryGroup && !in_array($ev->deliveryGroup->name, $groupedData[$groupKey]['deliveryGroups'])) {
                        $groupedData[$groupKey]['deliveryGroups'][] = $ev->deliveryGroup->name;
                    }
                    
                    if (!isset($groupedData[$groupKey]['categories'][$stackKey])) {
                        $groupedData[$groupKey]['categories'][$stackKey] = [
                            'planItems' => 0,
                            'actualItems' => 0,
                            'groups' => []
                        ];
                    }
                    
                    $groupName = $ev->deliveryGroup ? $ev->deliveryGroup->name : 'No Group';
                    if (!isset($groupedData[$groupKey]['categories'][$stackKey]['groups'][$groupName])) {
                        $groupedData[$groupKey]['categories'][$stackKey]['groups'][$groupName] = [
                            'planItems' => 0,
                            'actualItems' => 0
                        ];
                    }
                    
                    $groupedData[$groupKey]['totalItems']++;
                    $groupedData[$groupKey]['categories'][$stackKey]['planItems']++;
                    $groupedData[$groupKey]['categories'][$stackKey]['groups'][$groupName]['planItems']++;
                    
                    if (in_array($part->status, ['FINISHED', 'CLOSED', 'OUTSTANDING'])) {
                        $groupedData[$groupKey]['categories'][$stackKey]['actualItems']++;
                        $groupedData[$groupKey]['categories'][$stackKey]['groups'][$groupName]['actualItems']++;
                    }
                }
            }
        }
        
        $groupedDataValues = array_values($groupedData);
        if ($isFiltered) {
            usort($groupedDataValues, function($a, $b) {
                return $a['sortOrder'] <=> $b['sortOrder'];
            });
        }
        
        foreach ($groupedDataValues as $group) {
            $totalItems = $group['totalItems'];
            
            $catData = [];
            foreach ($group['categories'] as $catName => $cat) {
                $planPct = $totalItems > 0 ? round(($cat['planItems'] / $totalItems) * 100, 1) : 0;
                $actPct = $totalItems > 0 ? round(($cat['actualItems'] / $totalItems) * 100, 1) : 0;
                
                $groupsDetail = [];
                foreach ($cat['groups'] as $gName => $gData) {
                    $gPlanPct = $totalItems > 0 ? round(($gData['planItems'] / $totalItems) * 100, 1) : 0;
                    $gActPct = $totalItems > 0 ? round(($gData['actualItems'] / $totalItems) * 100, 1) : 0;
                    $groupsDetail[$gName] = [
                        'planPct' => $gPlanPct,
                        'actualPct' => $gActPct,
                        'planItems' => $gData['planItems'],
                        'actualItems' => $gData['actualItems']
                    ];
                }

                $catData[$catName] = [
                    'planPct' => $planPct,
                    'actualPct' => $actPct,
                    'planItems' => $cat['planItems'],
                    'actualItems' => $cat['actualItems'],
                    'groups' => $groupsDetail
                ];
            }
            
            $tooltipTitle = $group['tooltipTitle'];
            
            $chartTooltip = [
                $tooltipTitle,
                'Total: ' . $totalItems . ' items'
            ];
            
            $custData = [
                'id' => $group['key'],
                'po_no' => $group['chartLabel'],
                'chartLabel' => $group['chartLabel'],
                'chartTooltip' => $chartTooltip,
                'totalItems' => $totalItems,
                'categories' => $catData
            ];
            
            $currentChunk[] = $custData;
            
            if (count($currentChunk) == 5) {
                $chunks[] = $currentChunk;
                $currentChunk = [];
            }
        }
        
        if (count($currentChunk) > 0) {
            $chunks[] = $currentChunk;
        }
        
        return $chunks;
    }
}
