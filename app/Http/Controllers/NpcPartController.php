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
            $query = clone $event->parts()->with('product.vehicleModel', 'event');

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->order(function ($q) {
                    $q->orderBy('created_at', 'desc');
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
                        'deleteUrl' => route('events.parts.destroy', [$event->hashed_id, $part->hashed_id])
                    ])->render();
                })
                ->rawColumns(['po_no', 'part_no', 'model', 'part_name', 'qty', 'delv_date', 'status_label', 'action'])
                ->make(true);
        }

        return view('npc_parts.index', compact('event'));
    }

    public function create(NpcEvent $event)
    {
        return view('npc_parts.create', compact('event'));
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

        $product = \App\Models\Product::with('docPackage')
            ->where('part_no', $request->part_no)
            ->where('customer_id', $customerId)
            ->first();

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

        // Process schedules will be configured natively using the Setup Routing feature.

        return redirect()->route('events.parts.index', $event->id)->with('success', 'Part / Item added to event successfully.');
    }

    public function edit(NpcEvent $event, NpcPart $part)
    {
        return view('npc_parts.edit', compact('event', 'part'));
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

        $product = \App\Models\Product::where('part_no', $request->part_no)
            ->where('customer_id', $customerId)
            ->first();

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
                'part_revision_id' => $product->docPackage->current_revision_id
            ]);
            return back()->with('success', 'Latest ECN revision successfully applied for part ' . $product->part_no);
        }

        return back()->with('error', 'Failed to apply ECN revision. Master Data Drawing not found.');
    }
}
