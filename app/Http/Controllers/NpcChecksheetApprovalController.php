<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcChecksheet;
use App\Models\NpcPart;
use Carbon\Carbon;

class NpcChecksheetApprovalController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = NpcChecksheet::with([
                'npcPart.product.vehicleModel.customer', 
                'npcPart.event.customerCategory.customer',
                'npcPart.event.deliveryGroup'
            ])
                ->whereHas('npcPart', function($q) {
                    $q->whereIn('status', ['WAITING_APPROVAL', 'FINISHED', 'OUTSTANDING', 'CLOSED']);
                });

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->order(function ($q) {
                    $q->orderBy('created_at', 'desc');
                })
                ->addIndexColumn()
                ->addColumn('part_info', function ($checksheet) {
                    $partNo = optional($checksheet->npcPart->product)->part_no ?? '-';
                    $partName = optional($checksheet->npcPart->product)->part_name ?? '-';
                    return '<div class="text-gray-800 dark:text-gray-200 font-bold text-sm">' . $partNo . '</div><div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">' . $partName . '</div>';
                })
                ->addColumn('event_info', function ($checksheet) {
                    $category = optional(optional($checksheet->npcPart->event)->customerCategory)->name ?? 'N/A';
                    return '<div class="text-blue-600 dark:text-blue-400 font-bold text-[11px] uppercase tracking-wide bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 px-2 py-0.5 inline-block mb-1">' . $category . '</div>';
                })
                ->addColumn('po_info', function ($checksheet) {
                    $po = optional($checksheet->npcPart->event)->po_no ?? 'N/A';
                    $dg = optional(optional($checksheet->npcPart->event)->deliveryGroup)->name ?? 'N/A';
                    return '<div class="text-gray-700 dark:text-gray-300 font-semibold text-sm">' . $po . '</div><div class="text-xs text-gray-400 mt-0.5">' . $dg . '</div>';
                })
                ->addColumn('model_customer', function ($checksheet) {
                    $model = optional(optional($checksheet->npcPart->product)->vehicleModel)->name ?? 'N/A';
                    $customer = optional(optional(optional($checksheet->npcPart->event)->customerCategory)->customer)->code ?? 'N/A';
                    return '<div class="text-gray-700 dark:text-gray-300 text-sm font-medium">' . $model . '</div><div class="text-xs text-gray-400 mt-0.5">' . $customer . '</div>';
                })
                ->addColumn('approval_stage', function ($checksheet) {
                    $levelMap = [
                        'WAITING_QE_STAFF'   => 'QE Staff',
                        'WAITING_MGM_STAFF'  => 'NPC Staff',
                        'WAITING_QE_SPV'     => 'QE SPV',
                        'WAITING_MGM_SPV'    => 'NPC SPV',
                        'WAITING_QE_ASSMAN'  => 'QE Asst Mgr',
                        'WAITING_MGM_ASSMAN' => 'NPC Asst Mgr',
                        'WAITING_QE_MGR'     => 'QE Mgr',
                        'WAITING_MGM_MGR'    => 'NPC Mgr',
                        'APPROVED'           => 'Fully Approved'
                    ];
                    $levelName = $levelMap[$checksheet->approval_status] ?? str_replace('WAITING_', '', $checksheet->approval_status);
                    
                    if ($checksheet->approval_status === 'APPROVED') {
                        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 border border-emerald-200 text-emerald-800 text-[10px] font-bold"><i class="fa-solid fa-check-double"></i> FULLY APPROVED</span>';
                    } else {
                        return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-yellow-100 border border-yellow-200 text-yellow-800 text-[10px] font-bold tracking-wide"><i class="fa-solid fa-hourglass-half animate-pulse"></i> ' . $levelName . '</span>';
                    }
                })
                ->addColumn('action', function ($checksheet) {
                    $previewBtn = '<a href="' . route('checksheets.preview', $checksheet->hashed_id) . '" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-purple-500 hover:bg-purple-600 text-white text-xs font-bold transition shadow-sm" title="Preview Report"><i class="fa-solid fa-file-pdf"></i> Preview</a>';
                    
                    if ($checksheet->approval_status === 'APPROVED') {
                        return '<div class="flex items-center justify-end gap-2"><span class="text-xs text-emerald-600 font-semibold flex items-center justify-end gap-1 whitespace-nowrap"><i class="fa-solid fa-circle-check"></i> Completed</span>' . $previewBtn . '</div>';
                    } else {
                        $user = auth()->user();
                        $actionBtn = '';
                        if ($user && $user->canApproveChecksheetStage($checksheet->approval_status)) {
                            $actionBtn = '<a href="' . route('checksheet-approvals.show', $checksheet->hashed_id) . '" class="inline-flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white text-xs font-bold transition shadow-sm shadow-blue-500/20 whitespace-nowrap"><i class="fa-solid fa-check"></i> Approve</a>';
                        } else {
                            $actionBtn = '<a href="' . route('checksheet-approvals.show', $checksheet->hashed_id) . '" class="inline-flex items-center gap-2 px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-bold transition shadow-sm whitespace-nowrap"><i class="fa-solid fa-eye"></i> View Details</a>';
                        }
                        return '<div class="flex items-center justify-end gap-2">' . $actionBtn . $previewBtn . '</div>';
                    }
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function($q) use ($search) {
                            $q->whereHas('npcPart.product', function($q) use ($search) {
                                $q->where('part_no', 'like', "%{$search}%")
                                  ->orWhere('part_name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('npcPart.event', function($q) use ($search) {
                                $q->where('po_no', 'like', "%{$search}%");
                            })
                            ->orWhereHas('npcPart.event.customerCategory', function($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            });
                        });
                    }
                })
                ->rawColumns(['part_info', 'event_info', 'po_info', 'model_customer', 'approval_stage', 'action'])
                ->make(true);
        }

        return view('npc_checksheets.approval_index');
    }

    public function show(NpcChecksheet $checksheet)
    {
        $checksheet->load('details', 'npcPart.product.specChildParts', 'npcPart.event.customerCategory', 'npcPart.event.deliveryGroup', 'npcPart.product.docPackage.currentRevision', 'npcPart.product.vehicleModel', 'npcPart.product.productDetail');
        $part = $checksheet->npcPart;
        
        return view('npc_checksheets.approval_show', compact('checksheet', 'part'));
    }

    public function store(Request $request, NpcChecksheet $checksheet)
    {
        $action = $request->input('action', 'approve');
        $status = $checksheet->approval_status;
        $userId = auth()->check() ? auth()->user()->getAttribute('id') : 1;
        $now = Carbon::now();
        $part = $checksheet->npcPart;

        $updateData = [];

        // Check RBAC Authorization using our new double-layer check
        if (!auth()->check()) {
            abort(403, 'Unauthorized');
        }
        
        if ($action === 'rollback') {
            if (!auth()->user()->roles->contains('code', 'admin')) {
                abort(403, 'Only Admins can rollback approvals.');
            }
            
            $checksheet->update([
                'approval_status' => 'WAITING_QE_STAFF',
                'qe_staff_id' => null, 'qe_staff_date' => null,
                'mgm_staff_id' => null, 'mgm_staff_date' => null,
                'qe_spv_id' => null, 'qe_spv_date' => null,
                'mgm_spv_id' => null, 'mgm_spv_date' => null,
                'qe_assman_id' => null, 'qe_assman_date' => null,
                'mgm_assman_id' => null, 'mgm_assman_date' => null,
                'qe_mgr_id' => null, 'qe_mgr_date' => null,
                'mgm_mgr_id' => null, 'mgm_mgr_date' => null,
            ]);
            
            if ($part) {
                $part->update(['status' => 'WAITING_APPROVAL']);
            }
            
            return redirect()->route('checksheet-approvals.index')->with('success', 'Checksheet has been successfully rolled back to the first step.');
        }

        if (!auth()->user()->canApproveChecksheetStage($status)) {
            abort(403, 'You do not have the required Role or Permission to approve/reject at this stage.');
        }

        if ($action === 'reject') {
            if ($status === 'WAITING_MGM_MGR') {
                $updateData['approval_status'] = 'WAITING_QE_MGR';
                $updateData['qe_mgr_id'] = null;
                $updateData['qe_mgr_date'] = null;
            } elseif ($status === 'WAITING_QE_MGR') {
                $updateData['approval_status'] = 'WAITING_MGM_ASSMAN';
                $updateData['mgm_assman_id'] = null;
                $updateData['mgm_assman_date'] = null;
            } elseif ($status === 'WAITING_MGM_ASSMAN') {
                $updateData['approval_status'] = 'WAITING_QE_ASSMAN';
                $updateData['qe_assman_id'] = null;
                $updateData['qe_assman_date'] = null;
            } elseif ($status === 'WAITING_QE_ASSMAN') {
                $updateData['approval_status'] = 'WAITING_MGM_SPV';
                $updateData['mgm_spv_id'] = null;
                $updateData['mgm_spv_date'] = null;
            } elseif ($status === 'WAITING_MGM_SPV') {
                $updateData['approval_status'] = 'WAITING_QE_SPV';
                $updateData['qe_spv_id'] = null;
                $updateData['qe_spv_date'] = null;
            } elseif ($status === 'WAITING_QE_SPV') {
                $updateData['approval_status'] = 'WAITING_MGM_STAFF';
                $updateData['mgm_staff_id'] = null;
                $updateData['mgm_staff_date'] = null;
            } elseif ($status === 'WAITING_MGM_STAFF') {
                $updateData['approval_status'] = 'WAITING_QE_STAFF';
                $updateData['mgm_checked_by'] = null;
                $updateData['mgm_check_date'] = null;
                if ($part) {
                    $part->update(['status' => 'WAITING_MGM_CHECK']);
                }
            } else {
                return redirect()->back()->with('error', 'Cannot reject at this state.');
            }

            $checksheet->update($updateData);

            return redirect()->route('checksheet-approvals.index')->with('success', 'Checksheet successfully rejected and returned to the previous step.');
        }

        if ($status === 'WAITING_QE_STAFF') {
            $updateData['qe_staff_id'] = $userId;
            $updateData['qe_staff_date'] = $now;
            $updateData['approval_status'] = 'WAITING_MGM_STAFF';
        } elseif ($status === 'WAITING_MGM_STAFF') {
            $updateData['mgm_staff_id'] = $userId;
            $updateData['mgm_staff_date'] = $now;
            $updateData['approval_status'] = 'WAITING_QE_SPV';
        } elseif ($status === 'WAITING_QE_SPV') {
            $updateData['qe_spv_id'] = $userId;
            $updateData['qe_spv_date'] = $now;
            $updateData['approval_status'] = 'WAITING_MGM_SPV';
        } elseif ($status === 'WAITING_MGM_SPV') {
            $updateData['mgm_spv_id'] = $userId;
            $updateData['mgm_spv_date'] = $now;
            $updateData['approval_status'] = 'WAITING_QE_ASSMAN';
        } elseif ($status === 'WAITING_QE_ASSMAN') {
            $updateData['qe_assman_id'] = $userId;
            $updateData['qe_assman_date'] = $now;
            $updateData['approval_status'] = 'WAITING_MGM_ASSMAN';
        } elseif ($status === 'WAITING_MGM_ASSMAN') {
            $updateData['mgm_assman_id'] = $userId;
            $updateData['mgm_assman_date'] = $now;
            $updateData['approval_status'] = 'WAITING_QE_MGR';
        } elseif ($status === 'WAITING_QE_MGR') {
            $updateData['qe_mgr_id'] = $userId;
            $updateData['qe_mgr_date'] = $now;
            $updateData['approval_status'] = 'WAITING_MGM_MGR';
        } elseif ($status === 'WAITING_MGM_MGR') {
            $updateData['mgm_mgr_id'] = $userId;
            $updateData['mgm_mgr_date'] = $now;
            $updateData['approval_status'] = 'APPROVED';

            if ($part && $part->status === 'WAITING_APPROVAL') {
                $part->update(['status' => 'FINISHED']);
            }
        } else {
            return redirect()->back()->with('error', 'Invalid approval status.');
        }

        $checksheet->update($updateData);

        return redirect()->route('checksheet-approvals.index')->with('success', 'Checksheet successfully approved.');
    }
}
