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
        $totalPO = NpcEvent::where($applyEventFilters)->count();

        // A PO is complete if it has parts and NONE of its parts are in an active status or just finished
        $poComplete = NpcEvent::where($applyEventFilters)->whereHas('parts')->whereDoesntHave('parts', function($q) {
            $q->whereNotIn('status', ['CLOSED', 'OUTSTANDING']);
        })->count();

        $poOnHand = $totalPO - $poComplete;

        $stock = NpcPart::where('status', 'FINISHED')->whereHas('event', $applyEventFilters)->count();

        $metrics = [
            'total_po' => $totalPO,
            'po_on_hand' => $poOnHand,
            'po_complete' => $poComplete,
            'stock' => $stock,
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
        
        // Chart 1: Plan vs Actual
        $queryEvents = NpcEvent::with(['customerCategory', 'deliveryGroup', 'parts' => function($q) {
            $q->select('id', 'npc_event_id', 'status', 'product_id')->with(['product.customer', 'product.vehicleModel']);
        }]);

        $applyEventFilters($queryEvents);

        $recentEvents = $queryEvents->orderBy('created_at', 'desc')
        ->take(15)
        ->get()
        ->reverse(); // reverse to show oldest of the recent on the left

        $eventLabels = [];
        $totalItemsData = [];
        $finishedItemsData = [];
        $inProgressItemsData = [];
        $completionRates = [];

        foreach ($recentEvents as $ev) {
            // Use Customer + PO No as label
            $poLabel = $ev->po_no ? $ev->po_no : 'EV-'.$ev->id;
            $custName = $ev->customerCategory ? $ev->customerCategory->name : 'Unknown';
            $grName = $ev->deliveryGroup ? $ev->deliveryGroup->name : '';
            
            $firstLineLabel = $custName . ' (' . $poLabel . ')';
            if ($grName) {
                $firstLineLabel .= ' - ' . $grName;
            }

            // Get unique customers and models from parts
            $customers = [];
            $models = [];
            foreach($ev->parts as $part) {
                if ($part->product) {
                    if ($part->product->customer) {
                        $customers[] = $part->product->customer->code;
                    }
                    if ($part->product->vehicleModel) {
                        $models[] = $part->product->vehicleModel->name;
                    }
                }
            }
            $customers = array_unique($customers);
            $models = array_unique($models);
            
            $customerStr = count($customers) > 0 ? implode(', ', $customers) : '-';
            $modelStr = count($models) > 0 ? implode(', ', $models) : '-';

            // Multi-line label for Chart.js
            $eventLabels[] = [
                $firstLineLabel,
                $customerStr . ' | ' . $modelStr
            ];
            
            $totalItems = $ev->parts->count();
            $finishedItems = $ev->parts->whereIn('status', ['FINISHED', 'CLOSED', 'OUTSTANDING'])->count();
            $inProgressItems = $totalItems - $finishedItems;
            $rate = $totalItems > 0 ? round(($finishedItems / $totalItems) * 100) : 0;
            
            $totalItemsData[] = $totalItems;
            $finishedItemsData[] = $finishedItems;
            $inProgressItemsData[] = $inProgressItems;
            $completionRates[] = $rate;
        }

        $trendChart = [
            'labels' => array_values($eventLabels),
            'new' => array_values($totalItemsData),
            'finished' => array_values($finishedItemsData),
            'in_progress' => array_values($inProgressItemsData),
            'rates' => array_values($completionRates),
        ];

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
        $ecnUpdates = NpcPart::with(['product', 'event.customerCategory'])
            ->whereHas('event', $applyEventFilters)
            ->whereNotIn('status', ['FINISHED', 'CLOSED'])
            ->whereNotNull('part_revision_id')
            ->whereHas('product.docPackage', function ($q) {
                $q->whereColumn('doc_packages.current_revision_id', '!=', 'npc_parts.part_revision_id');
            })
            ->take(5)
            ->get();

        // b. Stagnant Parts (No update for > 3 days, excluding finished/closed)
        $stagnantParts = NpcPart::with(['product', 'event.customerCategory'])
            ->whereHas('event', $applyEventFilters)
            ->whereNotIn('status', ['FINISHED', 'CLOSED'])
            ->where('updated_at', '<', Carbon::now()->subDays(3))
            ->orderBy('updated_at', 'asc')
            ->take(5)
            ->get();

        // 4. Remain Deliveries
        $remainDeliveries = NpcPart::with(['product.vehicleModel', 'event.customerCategory'])
            ->whereHas('event', $applyEventFilters)
            ->where('status', 'FINISHED')
            ->whereNotNull('delivery_date')
            ->orderBy('delivery_date', 'asc')
            ->take(5)
            ->get();

        // Filter options for view
        $customerCategories = \App\Models\NpcCustomerCategory::orderBy('name')->get();
        $vehicleModels = \App\Models\VehicleModel::orderBy('name')->get();
        $availableYears = NpcEvent::selectRaw('YEAR(created_at) as year')->distinct()->orderBy('year', 'desc')->pluck('year');
        if ($availableYears->isEmpty()) {
            $availableYears = collect([date('Y')]);
        }

        return view('dashboard', compact(
            'metrics', 'nearestEvents', 'ecnUpdates', 'stagnantParts', 'remainDeliveries',
            'trendChart', 'departmentChart', 'customerChart',
            'filterYear', 'filterMonth', 'filterCustomer', 'filterPo', 'filterModel', 'customerCategories', 'vehicleModels', 'availableYears'
        ));
    }
}
