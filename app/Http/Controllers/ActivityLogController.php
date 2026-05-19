<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs.
     */
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        // Filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('menu')) {
            $menu = $request->menu;
            // Check if it's a manual log description or a Model subject_type
            if (str_contains($menu, 'App\\Models')) {
                $query->where('subject_type', $menu);
            } else {
                $query->where('description', $menu);
            }
        }

        $logs = $query->limit(2000)->get();

        // Data for filter dropdowns
        $users = \App\Models\User::whereHas('roles')
            ->orWhereHas('specificMenus')
            ->orderBy('name')
            ->get();
        $menus = [
            'App\Models\Customer' => 'Customer Mapping',
            'App\Models\NpcProcess' => 'Process Master',
            'App\Models\NpcMasterCheckpoint' => 'QE Point Master',
            'App\Models\NpcEvent' => 'Event Data (PO)',
            'App\Models\NpcRole' => 'NPC Role',
            'App\Models\User' => 'NPC User Access',
            'App\Models\NpcPart' => 'Part Transaction',
            'App\Models\NpcChecksheet' => 'Checksheet Transaction',
            'App\Models\NpcChecksheetDetail' => 'Checksheet Details',
            'App\Models\NpcPartProcess' => 'Part Process Progress',
            // Manual logs use description instead of subject_type
            'Routing per Part ID' => 'Routing per Part ID',
            'Part Checksheet Master' => 'Part Checksheet Master',
        ];

        return view('activity_logs.index', compact('logs', 'users', 'menus'));
    }
}
