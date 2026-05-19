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

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%")
                  ->orWhereHas('causer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('activity_logs.index', compact('logs'));
    }
}
