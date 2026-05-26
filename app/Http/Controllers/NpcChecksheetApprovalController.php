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
        $query = NpcChecksheet::with([
            'npcPart.product.vehicleModel.customer', 
            'npcPart.event.customerCategory.customer',
            'npcPart.event.deliveryGroup'
        ])
            ->whereHas('npcPart', function($q) {
                $q->whereIn('status', ['WAITING_APPROVAL', 'FINISHED', 'OUTSTANDING', 'CLOSED']);
            });

        if ($request->filled('search')) {
            $search = $request->search;
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

        $checksheets = $query->latest()->paginate(20);

        return view('npc_checksheets.approval_index', compact('checksheets'));
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

        if ($action === 'reject') {
            if ($status === 'WAITING_MGM_MGR') {
                $updateData['approval_status'] = 'WAITING_QE_MGR';
                $updateData['qe_mgr_id'] = null;
                $updateData['qe_mgr_date'] = null;
            } elseif ($status === 'WAITING_QE_MGR') {
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
