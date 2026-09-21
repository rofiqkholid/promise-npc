<?php

namespace App\Http\Controllers;

use App\Models\NpcEvent;
use App\Models\NpcPart;
use Illuminate\Http\Request;

class NpcPartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, NpcEvent $event)
    {
        if ($request->ajax()) {
            $query = clone $event->parts()->with(['product.vehicleModel', 'product.docPackage.currentRevision', 'drawingRevision', 'event']);

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->order(function ($q) {
                    $q->orderBy('created_at', 'desc');
                })
                ->filterColumn('po_no', function($query, $keyword) {
                    $query->whereHas('event', function($q) use ($keyword) {
                        $q->where('po_no', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('part_no', function($query, $keyword) {
                    $query->whereHas('product', function($q) use ($keyword) {
                        $q->where('part_no', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('model', function($query, $keyword) {
                    $query->whereHas('product.vehicleModel', function($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('part_name', function($query, $keyword) {
                    $query->whereHas('product', function($q) use ($keyword) {
                        $q->where('part_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('delv_date', function($query, $keyword) {
                    $query->where('delivery_date', 'like', "%{$keyword}%");
                })
                ->filterColumn('status_label', function($query, $keyword) {
                    $query->where('status', 'like', "%{$keyword}%");
                })
                ->addIndexColumn()
                ->addColumn('po_no', function ($part) {
                    return '<span class="text-slate-800 dark:text-slate-200 font-medium text-sm">' . optional($part->event)->po_no . '</span>';
                })
                ->addColumn('part_no', function ($part) {
                    return '<span class="text-blue-600 dark:text-blue-400 text-sm font-semibold">' . optional($part->product)->part_no . '</span>';
                })
                ->addColumn('model', function ($part) {
                    return '<span class="text-slate-600 dark:text-slate-400 text-sm">' . optional(optional($part->product)->vehicleModel)->name . '</span>';
                })
                ->addColumn('part_name', function ($part) {
                    return '<span class="text-slate-600 dark:text-slate-400 text-sm">' . optional($part->product)->part_name . '</span>';
                })
                ->addColumn('qty', function ($part) {
                    return '<span class="text-slate-600 dark:text-slate-400 text-sm font-medium">' . $part->qty . '</span>';
                })
                ->addColumn('delv_date', function ($part) {
                    return '<span class="text-slate-600 dark:text-slate-400 text-sm font-medium">' . \Carbon\Carbon::parse($part->delivery_date)->format('d M Y') . '</span>';
                })
                ->addColumn('ecn_info', function ($part) {
                    $revNo = optional($part->drawingRevision)->revision_no !== null && optional($part->drawingRevision)->revision_no !== '' ? optional($part->drawingRevision)->revision_no : '-';
                    $ecnNo = optional($part->drawingRevision)->ecn_no ?: 'No ECN';
                    
                    $html = '<div class="text-xs font-semibold text-slate-700 dark:text-gray-200">Rev ' . e($revNo) . '</div>';
                    $html .= '<div class="text-[10px] text-slate-400">(' . e($ecnNo) . ')</div>';
                    if ($part->has_ecn_update) {
                        $html .= '<span class="inline-block mt-0.5 text-[9px] bg-red-100 text-red-600 font-bold px-1 rounded">⚠️ UPDATE</span>';
                    }
                    return $html;
                })
                ->addColumn('status_label', function ($part) {
                    if ($part->status === 'WAITING_DEPT_CONFIRM') {
                        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium">WAITING DEPT</span>';
                    } elseif ($part->status === 'WAITING_QE_CHECK') {
                        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-orange-100 text-orange-800 text-xs font-medium">WAITING QE</span>';
                    } elseif ($part->status === 'WAITING_MGM_CHECK') {
                        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-purple-100 text-purple-800 text-xs font-medium">WAITING MGM</span>';
                    } elseif ($part->status === 'FINISHED') {
                        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-100 text-green-800 text-xs font-medium">FINISHED</span>';
                    } else {
                        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 text-gray-800 text-xs font-medium">' . $part->status . '</span>';
                    }
                })
                ->addColumn('action', function ($part) use ($event) {
                    return view('components.datatable-actions', [
                        'editUrl' => route('events.parts.edit', [$event->hashed_id, $part->hashed_id]),
                        'deleteUrl' => route('events.parts.destroy', [$event->hashed_id, $part->hashed_id]),
                        'extraButtons' => '<button type="button" onclick="window.dispatchEvent(new CustomEvent(\'open-change-revision-modal\', { detail: { partId: \'' . $part->hashed_id . '\' } }))" class="text-amber-600 hover:text-amber-800 hover:bg-amber-50 p-2 transition" title="Change / Revert ECN Revision"><i class="fa-solid fa-clock-rotate-left"></i></button>'
                    ])->render();
                })
                ->rawColumns(['po_no', 'part_no', 'model', 'part_name', 'qty', 'delv_date', 'ecn_info', 'status_label', 'action'])
                ->make(true);
        }

        return view('master.parts.index', compact('event'));
    }

    public function create(NpcEvent $event)
    {
        return view('master.parts.create', compact('event'));
    }

    public function store(Request $request, \App\Models\NpcEvent $event)
    {
        $customerId = optional($event->customerCategory)->customer_id;

        $request->validate([
            'part_no' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::exists('products', 'part_no')->where('customer_id', $customerId)
            ],
            'part_name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'delivery_date' => 'required|date',
        ], [
            'part_no.exists' => "The Part Number you entered is invalid or not part of this event's Customer."
        ]);

        $product = null;
        if ($request->filled('product_id')) {
            $product = \App\Models\Product::with('docPackage')->find($request->product_id);
        } else {
            $product = \App\Models\Product::with('docPackage')
                ->where('part_no', $request->part_no)
                ->where('customer_id', $customerId)
                ->first();
        }

        if ($product) {
            $missing = $product->getMissingMasterData();
            if (!empty($missing)) {
                return back()->withInput()->with('error', 'Cannot register this Part Number. The Master Data is incomplete. Please complete the following in Master Data first: ' . implode(', ', $missing));
            }
        }

        $currentRevisionId = null;
        if ($product && $product->docPackage) {
            $currentRevisionId = $product->docPackage->current_revision_id;
        }

        $part = \App\Models\NpcPart::create([
            'npc_event_id' => $event->id,
            'product_id' => $product ? $product->id : null,
            'part_revision_id' => $currentRevisionId,
            'qty' => $request->qty,
            'delivery_date' => $request->delivery_date,
            'status' => 'PO_REGISTERED'
        ]);

        // Generate checksheet immediately to lock in current checkpoints (Master Data snapshot)
        $checksheet = \App\Models\NpcChecksheet::create([
            'npc_part_id' => $part->id,
            'final_result' => null
        ]);

        $part->load('product.mappedCheckpoints.masterCheckpoint');
        if ($part->product && $part->product->mappedCheckpoints->isNotEmpty()) {
            $checkpoints = $part->product->mappedCheckpoints;
            foreach ($checkpoints as $mapped) {
                if ($mapped->masterCheckpoint) {
                    \Spatie\Activitylog\Facades\Activity::withoutLogs(function() use ($checksheet, $mapped) {
                        \App\Models\NpcChecksheetDetail::create([
                            'npc_checksheet_id' => $checksheet->id,
                            'point_check'       => $mapped->masterCheckpoint->check_item,
                            'standard'          => $mapped->custom_standard ?? $mapped->masterCheckpoint->standard,
                        ]);
                    });
                }
            }
        } else {
            // Fallback to ALL active master checkpoints
            $checkpoints = \App\Models\NpcMasterCheckpoint::where('is_active', true)->orderBy('point_number')->get();
            foreach ($checkpoints as $mappedPoint) {
                \Spatie\Activitylog\Facades\Activity::withoutLogs(function() use ($checksheet, $mappedPoint) {
                    \App\Models\NpcChecksheetDetail::create([
                        'npc_checksheet_id' => $checksheet->id,
                        'point_check'       => $mappedPoint->check_item,
                        'standard'          => null,
                    ]);
                });
            }
        }

        // Process schedules will be configured natively using the Setup Routing feature.

        return redirect()->route('events.parts.index', $event->id)->with('success', 'Part / Item added to event successfully.');
    }

    public function edit(NpcEvent $event, NpcPart $part)
    {
        return view('master.parts.edit', compact('event', 'part'));
    }

    public function update(Request $request, \App\Models\NpcEvent $event, \App\Models\NpcPart $part)
    {
        $customerId = optional($event->customerCategory)->customer_id;

        $request->validate([
            'part_no' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::exists('products', 'part_no')->where('customer_id', $customerId)
            ],
            'part_name' => 'required|string|max:255',
            'qty' => 'required|integer|min:1',
            'delivery_date' => 'required|date'
        ], [
            'part_no.exists' => "The Part Number you entered is invalid or not part of this event's Customer."
        ]);

        $product = null;
        if ($request->filled('product_id')) {
            $product = \App\Models\Product::find($request->product_id);
        } else {
            $product = \App\Models\Product::where('part_no', $request->part_no)
                ->where('customer_id', $customerId)
                ->first();
        }

        if ($product) {
            $missing = $product->getMissingMasterData();
            if (!empty($missing)) {
                return back()->withInput()->with('error', 'Cannot update to this Part Number. The Master Data is incomplete. Please complete the following in Master Data first: ' . implode(', ', $missing));
            }
        }

        $part->update([
            'npc_event_id' => $event->id,
            'product_id' => $product ? $product->id : null,
            'qty' => $request->qty,
            'delivery_date' => $request->delivery_date
        ]);

        return redirect()->route('events.parts.index', $event->id)->with('success', 'Part updated successfully.');
    }

    public function destroy(\App\Models\NpcEvent $event, \App\Models\NpcPart $part)
    {
        $part->delete();
        return redirect()->route('events.parts.index', $event->id)->with('success', 'Part / Item deleted successfully.');
    }

    public function applyEcn(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        $product = \App\Models\Product::with('docPackage')->find($part->product_id);
        
        if ($product && $product->docPackage) {
            $part->update([
                'part_revision_id' => $product->docPackage->current_revision_id,
                'acknowledged_revision_id' => null
            ]);
            return back()->with('success', 'Latest ECN revision successfully applied for PO ' . optional($part->event)->po_no);
        }

        return back()->with('error', 'Failed to apply ECN revision. Master Data Drawing not found.');
    }

    public function applyEcnAll(\Illuminate\Http\Request $request, \App\Models\Product $product)
    {
        $product->load('docPackage');
        
        if ($product && $product->docPackage) {
            \App\Models\NpcPart::where('product_id', $product->id)
                ->whereNotIn('status', ['FINISHED', 'CLOSED'])
                ->update([
                    'part_revision_id' => $product->docPackage->current_revision_id,
                    'acknowledged_revision_id' => null
                ]);
            return back()->with('success', 'Latest ECN revision successfully applied for all active POs of part ' . $product->part_no);
        }

        return back()->with('error', 'Failed to apply ECN revision. Master Data Drawing not found.');
    }

    public function acknowledgeEcn(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        $product = \App\Models\Product::with('docPackage')->find($part->product_id);
        
        if ($product && $product->docPackage) {
            $part->update([
                'acknowledged_revision_id' => $product->docPackage->current_revision_id
            ]);
            return back()->with('success', 'ECN revision successfully acknowledged for PO ' . optional($part->event)->po_no);
        }

        return back()->with('error', 'Failed to acknowledge ECN revision. Master Data Drawing not found.');
    }

    public function acknowledgeEcnAll(\Illuminate\Http\Request $request, \App\Models\Product $product)
    {
        $product->load('docPackage');
        
        if ($product && $product->docPackage) {
            \App\Models\NpcPart::where('product_id', $product->id)
                ->whereNotIn('status', ['FINISHED', 'CLOSED'])
                ->update([
                    'acknowledged_revision_id' => $product->docPackage->current_revision_id
                ]);
            return back()->with('success', 'ECN revision successfully acknowledged for all active POs of part ' . $product->part_no);
        }

        return back()->with('error', 'Failed to acknowledge ECN revision. Master Data Drawing not found.');
    }

    public function getRevisions(\App\Models\NpcPart $part)
    {
        $part->load(['product.docPackage.revisions', 'drawingRevision', 'event']);
        $product = $part->product;
        $docPackage = $product ? $product->getEffectiveDocPackage() : null;

        $revisions = collect();
        if ($docPackage) {
            $revisions = \App\Models\DocPackageRevision::where('package_id', $docPackage->id)
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($rev) use ($docPackage) {
                    return [
                        'id' => $rev->id,
                        'revision_no' => $rev->revision_no !== null && $rev->revision_no !== '' ? $rev->revision_no : '-',
                        'ecn_no' => $rev->ecn_no ?: 'No ECN',
                        'is_current' => $rev->id == $docPackage->current_revision_id,
                        'created_at' => $rev->created_at ? $rev->created_at->format('d M Y H:i') : '-'
                    ];
                });
        }

        return response()->json([
            'success' => true,
            'po_no' => optional($part->event)->po_no ?? '-',
            'part_no' => optional($part->product)->part_no ?? '-',
            'part_name' => optional($part->product)->part_name ?? '-',
            'current_revision_id' => $part->part_revision_id,
            'acknowledged_revision_id' => $part->acknowledged_revision_id,
            'latest_revision_id' => optional($docPackage)->current_revision_id,
            'revisions' => $revisions
        ]);
    }

    public function changeRevision(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        $request->validate([
            'part_revision_id' => 'required|integer|exists:doc_package_revisions,id'
        ]);

        $part->update([
            'part_revision_id' => $request->part_revision_id,
            'acknowledged_revision_id' => null
        ]);

        $rev = \App\Models\DocPackageRevision::find($request->part_revision_id);
        $revText = $rev ? "Rev " . ($rev->revision_no ?? '-') . " (" . ($rev->ecn_no ?: 'No ECN') . ")" : '';

        return back()->with('success', 'Drawing revision for PO ' . optional($part->event)->po_no . ' successfully changed to ' . $revText);
    }
}
