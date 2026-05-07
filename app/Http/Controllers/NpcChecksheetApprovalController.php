<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NpcChecksheet;
use App\Models\NpcPart;
use Carbon\Carbon;

class NpcChecksheetApprovalController extends Controller
{
    public function index()
    {
        // Get checksheets that are in approval phase
        $checksheets = NpcChecksheet::with([
            'npcPart.product.vehicleModel.customer', 
            'npcPart.event.customerCategory.customer',
            'npcPart.event.deliveryGroup'
        ])
            ->whereHas('npcPart', function($q) {
                $q->whereIn('status', ['WAITING_APPROVAL', 'FINISHED']);
            })
            ->get();

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
        $status = $checksheet->approval_status;
        $userId = auth()->check() ? auth()->user()->getAttribute('id') : 1;
        $now = Carbon::now();
        $part = $checksheet->npcPart;

        $updateData = [];

        if ($status === 'WAITING_QE_STAFF') {
            $updateData['qe_staff_id'] = $userId;
            $updateData['qe_staff_date'] = $now;
            $updateData['approval_status'] = 'WAITING_QE_SPV';
        } elseif ($status === 'WAITING_QE_SPV') {
            $updateData['qe_spv_id'] = $userId;
            $updateData['qe_spv_date'] = $now;
            $updateData['approval_status'] = 'WAITING_QE_MGR';
        } elseif ($status === 'WAITING_QE_MGR') {
            $updateData['qe_mgr_id'] = $userId;
            $updateData['qe_mgr_date'] = $now;
            $updateData['approval_status'] = 'WAITING_MGM_STAFF';
        } elseif ($status === 'WAITING_MGM_STAFF') {
            $updateData['mgm_staff_id'] = $userId;
            $updateData['mgm_staff_date'] = $now;
            $updateData['approval_status'] = 'WAITING_MGM_SPV';
        } elseif ($status === 'WAITING_MGM_SPV') {
            $updateData['mgm_spv_id'] = $userId;
            $updateData['mgm_spv_date'] = $now;
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
