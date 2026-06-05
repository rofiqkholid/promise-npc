<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductionTrackingController extends Controller
{
    private function buildQuery($statusParam, $search = null)
    {
        $query = \App\Models\NpcPart::with(['event.customerCategory', 'event.deliveryGroup', 'processes.process', 'processes.department', 'checkpoints', 'checksheet', 'product.vehicleModel.customer']);

        if ($statusParam !== 'all') {
            if ($statusParam === 'CLOSED') {
                $query->whereIn('status', ['CLOSED', 'OUTSTANDING']);
            }
        }
        
        if (request()->has('filter') && request('filter') === 'ecn_updated') {
            $query->whereNotIn('status', ['FINISHED', 'CLOSED'])
                  ->whereNotNull('part_revision_id')
                  ->whereHas('product.docPackage', function ($q) {
                      $q->whereColumn('doc_packages.current_revision_id', '!=', 'npc_parts.part_revision_id');
                  });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($q) use ($search) {
                    $q->where('part_no', 'like', "%{$search}%")
                      ->orWhere('part_name', 'like', "%{$search}%");
                })
                ->orWhereHas('event', function ($q) use ($search) {
                    $q->where('po_no', 'like', "%{$search}%");
                });
            });
        }
        
        return $query;
    }

    private function renderTrackingPage($statusParam, $pageTitle, $pageIcon, $pageDesc, $viewFile = 'tracking.index')
    {
        if (request()->ajax()) {
            $search = request('search')['value'] ?? null; // Datatables sends search in search[value]
            $query = $this->buildQuery($statusParam, $search);

            $dt = \Yajra\DataTables\Facades\DataTables::of($query)
                ->order(function ($query) {
                    $query->orderBy('created_at', 'desc');
                })
                ->addIndexColumn()
                ->addColumn('part_info', function ($part) {
                    return view('tracking.columns.part_info', compact('part'))->render();
                });
                
            // Specific columns based on statusParam / viewFile
            if ($viewFile === 'tracking.qc') {
                $dt->addColumn('status_po', function ($part) {
                    return view('tracking.columns.status_po', compact('part'))->render();
                })
                ->addColumn('qc_progress', function ($part) {
                    return view('tracking.columns.qc_progress', compact('part'))->render();
                })
                ->addColumn('action_qc', function ($part) {
                    return view('tracking.columns.action_qc', compact('part'))->render();
                });
                $dt->rawColumns(['part_info', 'status_po', 'qc_progress', 'action_qc']);
            } elseif ($viewFile === 'tracking.production') {
                $dt->addColumn('status_po', function ($part) {
                    return view('tracking.columns.status_production', compact('part'))->render();
                })
                ->addColumn('routing_execution', function ($part) {
                    return view('tracking.columns.production_routing', compact('part'))->render();
                })
                ->addColumn('action_production', function ($part) {
                    return view('tracking.columns.action_production', compact('part'))->render();
                });
                $dt->rawColumns(['part_info', 'status_po', 'routing_execution', 'action_production']);
            } elseif ($viewFile === 'tracking.stock') {
                $dt->addColumn('delivery_target', function ($part) {
                    return view('tracking.columns.stock_delivery_target', compact('part'))->render();
                })
                ->addColumn('qty_target', function ($part) {
                    return view('tracking.columns.stock_qty_target', compact('part'))->render();
                })
                ->addColumn('approval_info', function ($part) {
                    return view('tracking.columns.stock_approval_info', compact('part'))->render();
                })
                ->addColumn('action_stock', function ($part) {
                    return view('tracking.columns.stock_action', compact('part'))->render();
                });
                $dt->rawColumns(['part_info', 'delivery_target', 'qty_target', 'approval_info', 'action_stock']);
            }
            
            // Add other pages when ready
            
            return $dt->make(true);
        }

        // For DataTables views, we don't need the actual parts data on initial load.
        if (in_array($viewFile, ['tracking.qc', 'tracking.production'])) {
            $parts = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        } else {
            // For other views that still use normal Laravel pagination
            $search = request('search');
            $parts = $this->buildQuery($statusParam, $search)->latest()->paginate(15);
        }
        
        return view($viewFile, compact('parts', 'statusParam', 'pageTitle', 'pageIcon', 'pageDesc'));
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $metrics = [
            'total_events' => \App\Models\NpcEvent::count(),
            'total_pos' => \App\Models\NpcEvent::whereNotNull('po_no')->count(),
            'total_parts' => \App\Models\NpcPart::count(),
            'total_po_close' => \App\Models\NpcPart::where('status', 'CLOSED')->count(),
        ];

        if ($request->ajax()) {
            $query = \App\Models\NpcEvent::with(['customerCategory', 'parts.product', 'parts.processes.process', 'parts.processes.department', 'parts.checksheet'])->whereHas('parts');
            
            // Note: DataTables search is usually handled by $request->search['value'] but we will let Yajra handle it.
            // Custom search logic if we want to search in related tables:
            
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->order(function ($q) {
                    $q->orderBy('created_at', 'desc');
                })
                ->addIndexColumn()
                ->addColumn('po_info', function ($po) {
                    return view('tracking.columns.global_po_info', compact('po'))->render();
                })
                ->addColumn('part_count', function ($po) {
                    return view('tracking.columns.global_part_count', compact('po'))->render();
                })
                ->addColumn('nearest', function ($po) {
                    return view('tracking.columns.global_nearest', compact('po'))->render();
                })
                ->addColumn('overall_progress', function ($po) {
                    return view('tracking.columns.global_progress', compact('po'))->render();
                })
                ->addColumn('system_duration', function ($po) {
                    return view('tracking.columns.global_system_duration', compact('po'))->render();
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('po_no', 'like', "%{$search}%")
                              ->orWhere('delivery_to', 'like', "%{$search}%")
                              ->orWhereHas('customerCategory', function ($q) use ($search) {
                                  $q->where('name', 'like', "%{$search}%")
                                    ->orWhereHas('customer', function ($q) use ($search) {
                                        $q->where('code', 'like', "%{$search}%")
                                          ->orWhere('name', 'like', "%{$search}%");
                                    });
                              });
                        });
                    }
                })
                ->rawColumns(['po_info', 'part_count', 'nearest', 'overall_progress', 'system_duration'])
                ->make(true);
        }

        // Just pass an empty paginator to avoid breaking any blade view that might still expect it (if we didn't remove it)
        // Or simply empty collection
        $pos = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);

        return view('tracking.global', [
            'pos' => $pos,
            'statusParam' => 'all',
            'pageTitle' => 'Global Tracking',
            'pageIcon' => 'fa-globe',
            'pageDesc' => 'Track progress based on Purchase Order (PO)',
            'metrics' => $metrics
        ]);
    }

    public function setup(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('PO_REGISTERED', 'Setup Routing Production', 'fa-route', 'Preparation of routing and production schedule for new PO', 'tracking.setup');
    }

    public function production(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('WAITING_DEPT_CONFIRM', 'Process Production', 'fa-industry', 'Monitor the progress of components currently in the production stage', 'tracking.production');
    }

    public function qc(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('WAITING_QE_CHECK', 'Quality Inspection (QC)', 'fa-microscope', 'Input and validation of quality inspection', 'tracking.qc');
    }

    public function mgm(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('WAITING_MGM_CHECK', 'Management Check', 'fa-user-tie', 'Validation and final confirmation by management', 'tracking.mgm');
    }

    public function stock(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('FINISHED', 'Finished Stock', 'fa-boxes-stacked', 'Finished components ready to be shipped', 'tracking.stock');
    }

    public function history(\Illuminate\Http\Request $request)
    {
        return $this->renderTrackingPage('CLOSED', 'Delivery History', 'fa-truck-fast', 'Components that have been delivered to the customer');
    }

    public function updateStatus(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        $request->validate([
            'status' => 'required|in:PO_REGISTERED,WAITING_DEPT_CONFIRM,IN_PRODUCTION,WAITING_QE_CHECK,WAITING_MGM_CHECK,FINISHED,OUTSTANDING,CLOSED',
            'actual_completion_date' => 'nullable|date',
            'production_notes' => 'nullable|string|max:500',
        ]);

        $updateData = ['status' => $request->status];

        if ($request->status === 'WAITING_QE_CHECK') {
            if ($request->filled('actual_completion_date')) {
                $updateData['actual_completion_date'] = $request->actual_completion_date;
            }
            if ($request->filled('production_notes')) {
                $updateData['production_notes'] = $request->production_notes;
            }
        }

        $part->update($updateData);

        return back()->with('success', 'Part Status successfully updated.');
    }

    public function completeProcess(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        $request->validate([
            'process_id'              => 'required',
            'actual_completion_date'  => 'required|date',
            'actual_qty'              => 'required|integer|min:' . $part->qty,
            'photo'                   => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'production_notes'        => 'nullable|string|max:500',
        ], [
            'actual_qty.min' => 'Total Qty Completed cannot be less than Planning PO (' . $part->qty . ' PCS).'
        ]);

        // Decode hashed process_id
        $hashids    = new \Hashids\Hashids(env('APP_KEY'), 10);
        $decodedIds = $hashids->decode($request->process_id);
        $processId  = $decodedIds[0] ?? null;

        $process = \App\Models\NpcPartProcess::where('id', $processId)
            ->where('npc_part_id', $part->id)
            ->firstOrFail();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('production_proofs', 'public');
        }

        // Tandai proses ini selesai
        $process->update([
            'status' => 'FINISHED',
            'actual_completion_date' => $request->actual_completion_date,
            'actual_qty' => $request->actual_qty,
            'photo_proof' => $photoPath
        ]);

        // Cek apakah part ini masih punya proses yang belum selesai berdasar urutan
        $remainingProcesses = \App\Models\NpcPartProcess::where('npc_part_id', $part->id)
            ->where('status', 'WAITING')
            ->count();

        // Jika tidak ada sisa proses, barulah lempar part ke divisi QC
        if ($remainingProcesses === 0) {
            $part->update([
                'status' => 'WAITING_QE_CHECK',
                'actual_completion_date' => $request->actual_completion_date,
                'production_notes' => $request->production_notes,
            ]);
            return back()->with('success', 'Production sequence complete. Goods successfully submitted to QC!');
        }

        return back()->with('success', 'Process finished! Continue to the next department.');
    }

    public function rollbackSetup(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        // Cek apakah part ada di WAITING_DEPT_CONFIRM
        if ($part->status !== 'WAITING_DEPT_CONFIRM') {
            return back()->with('error', 'Only can rollback parts that are waiting for production.');
        }

        // Cek apakah ada proses yang sudah selesai
        $hasFinishedProcess = \App\Models\NpcPartProcess::where('npc_part_id', $part->id)
            ->where('status', 'FINISHED')
            ->exists();
            
        if ($hasFinishedProcess) {
            return back()->with('error', 'Cannot rollback to Setup because there is already a department that has completed the production process.');
        }

        // Delete semua proses yang masih WAITING karena akan di-setup ulang
        $part->processes()->delete();

        // Kembalikan ke PO_REGISTERED
        $part->update([
            'status' => 'PO_REGISTERED',
            'qc_target_date' => null,
            'mgm_target_date' => null
        ]);

        $latestActivity = $part->activities()->latest()->first();
        if ($latestActivity && $latestActivity->event === 'updated') {
            $latestActivity->update([
                'event' => 'rollbacked',
                'description' => 'Rollback Setup Routing'
            ]);
        }

        return back()->with('success', 'Successfully canceled (rollback) setup routing. Part returns to the setup queue.');
    }

    public function rollbackProcess(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        // Temukan proses terakhir yang sudah FINISHED
        $lastFinishedProcess = \App\Models\NpcPartProcess::where('npc_part_id', $part->id)
            ->where('status', 'FINISHED')
            ->orderBy('sequence_order', 'desc')
            ->first();

        if (!$lastFinishedProcess) {
            return back()->with('error', 'No processes can be rolled back.');
        }

        // Cek apakah sudah diproses oleh departemen selanjutnya (misal QC sudah isi checksheet)
        if (!in_array($part->status, ['WAITING_DEPT_CONFIRM', 'WAITING_QE_CHECK'])) {
             return back()->with('error', 'No rollback because it has been processed by the next stage (QC/MGM/Stock).');
        }
        
        if ($part->status === 'WAITING_QE_CHECK') {
            // Cek apakah QC sudah mulai mengisi checksheet
            $checksheet = $part->checksheet;
            if ($checksheet && $checksheet->qe_checked_by) {
                return back()->with('error', 'No rollback because QC has started checking (Checksheet filled).');
            }
        }

        // Delete bukti foto jika ada
        if ($lastFinishedProcess->photo_proof) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($lastFinishedProcess->photo_proof);
        }

        // Kembalikan status proses
        $lastFinishedProcess->update([
            'status' => 'WAITING',
            'actual_completion_date' => null,
            'actual_qty' => null,
            'photo_proof' => null
        ]);

        $latestProcessActivity = $lastFinishedProcess->activities()->latest()->first();
        if ($latestProcessActivity && $latestProcessActivity->event === 'updated') {
            $latestProcessActivity->update([
                'event' => 'rollbacked',
                'description' => 'Rollback Process Production'
            ]);
        }

        // Kembalikan status part jika sebelumnya sudah dilempar ke QC
        if ($part->status === 'WAITING_QE_CHECK') {
            $part->update([
                'status' => 'WAITING_DEPT_CONFIRM',
                'actual_completion_date' => null,
                'production_notes' => null,
            ]);

            $latestPartActivity = $part->activities()->latest()->first();
            if ($latestPartActivity && $latestPartActivity->event === 'updated') {
                $latestPartActivity->update([
                    'event' => 'rollbacked',
                    'description' => 'Rollback Process Production'
                ]);
            }
            
            // Delete checksheet pending jika ada
            if ($part->checksheet) {
                $part->checksheet->details()->delete();
                $part->checksheet->delete();
            }
        }

        return back()->with('success', 'Success rollback process ' . optional($lastFinishedProcess->process)->process_name . '.');
    }

    public function rollbackQc(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        // Only allow rollback if the part is in WAITING_MGM_CHECK
        if ($part->status !== 'WAITING_MGM_CHECK') {
            return back()->with('error', 'Only parts waiting for MGM check can be rolled back to QC.');
        }

        // Check if MGM has started checking (signed the checksheet)
        if ($part->checksheet && $part->checksheet->mgm_checked_by) {
            return back()->with('error', 'No rollback because MGM has already signed the checksheet.');
        }
        
        $part->update([
            'status' => 'WAITING_QE_CHECK',
            'mgm_target_date' => null, // Reset MGM target date if any
        ]);

        $latestActivity = $part->activities()->latest()->first();
        if ($latestActivity && $latestActivity->event === 'updated') {
            $latestActivity->update([
                'event' => 'rollbacked',
                'description' => 'Rollback QC Check'
            ]);
        }

        return back()->with('success', 'Successfully rolled back part from MGM to QC Check stage.');
    }

    public function rollbackMgm(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        // Only allow rollback if the part is in WAITING_APPROVAL or FINISHED
        if (!in_array($part->status, ['WAITING_APPROVAL', 'FINISHED'])) {
            return back()->with('error', 'Only parts in Approval or Finished stock can be rolled back to MGM.');
        }

        // Check if Approval has already progressed
        // For checksheet approval, we check if it has advanced beyond WAITING_MGM_STAFF
        if ($part->checksheet && $part->checksheet->approval_status !== null && $part->checksheet->approval_status !== 'WAITING_MGM_STAFF') {
            return back()->with('error', 'Cannot rollback because Checksheet Approval has already started/progressed.');
        }

        // If it's FINISHED, check if it has already started being delivered
        if ($part->status === 'FINISHED' && $part->delivered_qty > 0) {
            return back()->with('error', 'Cannot rollback because part has already started delivery.');
        }

        $part->update([
            'status' => 'WAITING_MGM_CHECK',
        ]);

        $latestActivity = $part->activities()->latest()->first();
        if ($latestActivity && $latestActivity->event === 'updated') {
            $latestActivity->update([
                'event' => 'rollbacked',
                'description' => 'Rollback Management Check'
            ]);
        }

        return back()->with('success', 'Successfully rolled back part to Management Check stage.');
    }

    public function deliver(\Illuminate\Http\Request $request, \App\Models\NpcPart $part)
    {
        $request->validate([
            'delivered_qty' => 'required|integer|min:1'
        ]);

        $maxQty = $part->qty - $part->delivered_qty;
        if ($request->delivered_qty > $maxQty) {
            return back()->with('error', 'The quantity to be sent exceeds the remaining quantity (' . $maxQty . ' PCS).');
        }

        $newDeliveredQty = $part->delivered_qty + $request->delivered_qty;
        $status = ($newDeliveredQty >= $part->qty) ? 'CLOSED' : 'OUTSTANDING';

        $part->update([
            'status' => $status,
            'delivered_qty' => $newDeliveredQty,
            'actual_delivery' => \Carbon\Carbon::now()
        ]);

        $msg = ($status === 'CLOSED') 
            ? 'Part successfully delivered in full to customer and closed.'
            : 'Partial delivery successfully recorded (' . $request->delivered_qty . ' PCS). The remaining quantity is still outstanding.';

        return back()->with('success', $msg);
    }
}
