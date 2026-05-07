<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcPart;
use App\Models\NpcEvent;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Top Level Metrics (KPI Cards)
        $totalActiveEvents = NpcEvent::count();

        $partsInProduction = NpcPart::where('status', 'WAITING_DEPT_CONFIRM')->count();
        $pendingQc = NpcPart::where('status', 'WAITING_QE_CHECK')->count();
        $readyToDeliver = NpcPart::where('status', 'FINISHED')->count();

        $metrics = [
            'active_events' => $totalActiveEvents,
            'in_production' => $partsInProduction,
            'pending_qc' => $pendingQc,
            'ready_deliver' => $readyToDeliver,
        ];

        // 2. Nearest Events
        $nearestEvents = NpcPart::with(['product.vehicleModel', 'product.customer', 'event.customerCategory'])
            ->whereNotIn('status', ['CLOSED'])
            ->whereNotNull('delivery_date')
            ->orderBy('delivery_date', 'asc')
            ->get()
            ->unique('npc_event_id')
            ->take(5);

        // --- DASHBOARD V2 CHARTS DATA ---

        // Chart 1: Event Progress (Total Items vs Finished Items)
        // Get 6 most recent active events
        $recentEvents = NpcEvent::with(['parts' => function($q) {
            $q->select('id', 'npc_event_id', 'status');
        }])
        ->orderBy('created_at', 'desc')
        ->take(6)
        ->get()
        ->reverse(); // reverse to show oldest of the recent on the left

        $eventLabels = [];
        $totalItemsData = [];
        $finishedItemsData = [];

        foreach ($recentEvents as $ev) {
            // Use PO No as label, truncate if too long
            $poLabel = $ev->po_no ? (strlen($ev->po_no) > 12 ? substr($ev->po_no, 0, 12).'...' : $ev->po_no) : 'EV-'.$ev->id;
            $eventLabels[] = $poLabel;
            
            $totalItems = $ev->parts->count();
            $finishedItems = $ev->parts->whereIn('status', ['FINISHED', 'CLOSED', 'OUTSTANDING'])->count();
            
            $totalItemsData[] = $totalItems;
            $finishedItemsData[] = $finishedItems;
        }

        $trendChart = [
            'labels' => array_values($eventLabels),
            'new' => array_values($totalItemsData),
            'finished' => array_values($finishedItemsData),
        ];

        // Chart 2: Department Workload (Waiting Processes)
        $waitingProcesses = \App\Models\NpcPartProcess::with('department')
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
            ->whereNotIn('status', ['FINISHED', 'CLOSED'])
            ->whereNotNull('part_revision_id')
            ->whereHas('product.docPackage', function ($q) {
                $q->whereColumn('doc_packages.current_revision_id', '!=', 'npc_parts.part_revision_id');
            })
            ->take(5)
            ->get();

        // b. Stagnant Parts (No update for > 7 days, excluding finished/closed)
        $stagnantParts = NpcPart::with(['product', 'event.customerCategory'])
            ->whereNotIn('status', ['FINISHED', 'CLOSED'])
            ->where('updated_at', '<', Carbon::now()->subDays(7))
            ->orderBy('updated_at', 'asc')
            ->take(5)
            ->get();

        // 4. Recent Deliveries / History
        $recentDeliveries = NpcPart::with(['product', 'event.customerCategory'])
            ->whereIn('status', ['CLOSED', 'OUTSTANDING'])
            ->orderBy('actual_delivery', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'metrics', 'nearestEvents', 'ecnUpdates', 'stagnantParts', 'recentDeliveries',
            'trendChart', 'departmentChart', 'customerChart'
        ));
    }
}
