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
                'npcPart.event.deliveryGroup',
                'rejectedBy',
                'resubmittedBy'
            ])
                ->whereHas('npcPart', function($q) {
                    $q->whereIn('status', ['WAITING_APPROVAL', 'FINISHED', 'OUTSTANDING', 'CLOSED']);
                });

            if ($request->filled('stage')) {
                $query->where('approval_status', $request->stage);
            }

            if ($request->filled('customer')) {
                $query->whereHas('npcPart.event.customerCategory.customer', function($q) use ($request) {
                    $q->where('id', $request->customer);
                });
            }

            if ($request->filled('model')) {
                $query->whereHas('npcPart.product.vehicleModel', function($q) use ($request) {
                    $q->where('id', $request->model);
                });
            }

            if ($request->filled('po')) {
                $query->whereHas('npcPart.event', function($q) use ($request) {
                    $q->where('po_no', $request->po);
                });
            }

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
                    }

                    if ($checksheet->reject_reason) {
                        $fromStage = $levelMap[$checksheet->rejected_from_stage] ?? str_replace('WAITING_', '', $checksheet->rejected_from_stage ?? '');
                        $byUser = optional($checksheet->rejectedBy)->name;
                        $byStr = $byUser ? 'by ' . e($byUser) : '';
                        $fromStr = $fromStage ? ' (' . e($fromStage) . ')' : '';

                        $html = '<div class="flex flex-col items-center gap-1 text-center min-w-[150px]">';
                        $html .= '<span class="inline-flex items-center gap-1 px-2.5 py-1 bg-yellow-100 border border-yellow-200 text-yellow-800 text-[10px] font-bold tracking-wide"><i class="fa-solid fa-hourglass-half animate-pulse"></i> Waiting: ' . e($levelName) . '</span>';
                        
                        if ($checksheet->resubmitted_at) {
                            $resubUser = optional($checksheet->resubmittedBy)->name;
                            $resubStr = $resubUser ? ' by ' . e($resubUser) : '';
                            $html .= '<div class="w-full text-left text-xs bg-emerald-50 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-200 border border-emerald-300 dark:border-emerald-700/60 p-1.5 rounded shadow-2xs">';
                            $html .= '<div class="font-bold text-[10px] text-emerald-800 dark:text-emerald-300 flex items-center gap-1">';
                            $html .= '<i class="fa-solid fa-circle-check text-emerald-500"></i> Revised / Resubmitted' . e($resubStr);
                            $html .= '</div>';
                            $html .= '</div>';
                        } else {
                            $html .= '<div class="w-full text-left text-xs bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800/60 p-1.5 rounded shadow-2xs">';
                            $html .= '<div class="font-bold flex items-center justify-between gap-1 text-[10px] text-red-800 dark:text-red-300"><span class="flex items-center gap-1"><i class="fa-solid fa-circle-xmark text-red-500"></i> Rejected ' . e($byStr) . e($fromStr) . ':</span>';
                            
                            if ($checksheet->reject_photo_path) {
                                $photoUrl = url('file/storage/' . ltrim(str_replace('public/', '', $checksheet->reject_photo_path), '/'));
                                $html .= '<a href="' . $photoUrl . '" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/40 border border-blue-200 dark:border-blue-700 px-1.5 py-0.5 rounded hover:bg-blue-100 transition whitespace-nowrap" title="View Reject Photo"><i class="fa-solid fa-camera text-blue-500"></i> Photo</a>';
                            }
                            
                            $html .= '</div>';
                            $html .= '<div class="text-[11px] text-red-700 dark:text-red-200 whitespace-normal break-words leading-tight mt-0.5">' . nl2br(e($checksheet->reject_reason)) . '</div>';
                            $html .= '</div>';
                        }
                        
                        $html .= '</div>';
                        return $html;
                    }

                    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-yellow-100 border border-yellow-200 text-yellow-800 text-[10px] font-bold tracking-wide"><i class="fa-solid fa-hourglass-half animate-pulse"></i> ' . e($levelName) . '</span>';
                })
                ->addColumn('action', function($checksheet) {
                    $url = route('checksheet-approvals.show', $checksheet->hashed_id);
                    $btn = '<div class="flex flex-col items-end gap-2">';
                    $btn .= '<a href="' . $url . '" data-part-id="' . $checksheet->npcPart->hashed_id . '" class="inline-flex px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white shadow-sm font-bold transition items-center gap-2 text-[11px] w-full max-w-[150px] justify-center" style="background-color: #3b82f6;">';
                    $btn .= '<i class="fa-solid fa-file-signature"></i> View / Approve';
                    $btn .= '</a>';
                    
                    $previewBtn = '<a href="' . route('checksheets.preview', $checksheet->hashed_id) . '" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-purple-500 hover:bg-purple-600 text-white text-xs font-bold transition shadow-sm" title="Preview Report"><i class="fa-solid fa-file-pdf"></i> Preview</a>';
                    
                    if ($checksheet->approval_status === 'APPROVED') {
                        $part = $checksheet->npcPart;
                        $rollbackBtn = '';
                        $user = auth()->user();
                        $isAdmin = $user && $user->roles->filter(function($role) {
                            $code = strtolower($role->code);
                            $name = strtolower($role->role_name);
                            return $code === 'administrator' || $code === 'npc_admin' || $name === 'administrator';
                        })->isNotEmpty();
                        
                        if ($part && ($part->delivered_qty == 0 || $isAdmin)) {
                            $rollbackUrl = route('tracking.mgm.rollback', $part->hashed_id);
                            $token = csrf_token();
                            $rollbackBtn = '<form action="'.$rollbackUrl.'" method="POST" class="inline m-0 p-0 rollback-form-approval"><input type="hidden" name="_token" value="'.$token.'"><input type="hidden" name="rollback_reason" class="rollback-reason-input"><button type="button" class="inline-flex items-center gap-2 px-3 py-2 bg-red-500 hover:bg-red-600 text-white text-xs font-bold transition shadow-sm" title="Rollback Approval" onclick="confirmRollbackWithReason(event)"><i class="fa-solid fa-rotate-left"></i> Rollback</button></form>';
                        }
                        
                        return '<div class="flex items-center justify-end gap-2"><span class="text-xs text-emerald-600 font-semibold flex items-center justify-end gap-1 whitespace-nowrap mr-2"><i class="fa-solid fa-circle-check"></i> Completed</span>' . $rollbackBtn . $previewBtn . '</div>';
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
                            ->orWhereHas('npcPart.product.vehicleModel', function($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('npcPart.product.vehicleModel.customer', function($q) use ($search) {
                                $q->where('code', 'like', "%{$search}%")
                                  ->orWhere('name', 'like', "%{$search}%");
                            });
                        });
                    }
                })
                ->rawColumns(['part_info', 'event_info', 'po_info', 'model_customer', 'approval_stage', 'action'])
                ->make(true);
        }

        $customers = \App\Models\Customer::orderBy('name')->get();
        $models = \App\Models\VehicleModel::orderBy('name')->get();
        $poList = \App\Models\NpcEvent::select('po_no')->whereNotNull('po_no')->distinct()->orderBy('po_no')->get();

        return view('tracking.checksheets.approval_index', compact('customers', 'models', 'poList'));
    }

    public function show(NpcChecksheet $checksheet)
    {
        $checksheet->load('details', 'npcPart.product.specChildParts', 'npcPart.event.customerCategory', 'npcPart.event.deliveryGroup', 'npcPart.product.docPackage.currentRevision', 'npcPart.product.vehicleModel', 'npcPart.product.productDetail', 'rejectedBy');
        $part = $checksheet->npcPart;
        
        return view('tracking.checksheets.approval_show', compact('checksheet', 'part'));
    }

    public function store(Request $request, NpcChecksheet $checksheet)
    {
        // Setup redirect
        $redirectUrl = $request->input('previous_url') ? base64_decode($request->input('previous_url')) : route('checksheet-approvals.index');
        if ($redirectUrl === false || !filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
            $redirectUrl = route('checksheet-approvals.index');
        }

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

        // Save details and remark if provided
        $detailsInput = [];
        $base64String = '';
        
        if ($request->has('details_json_chunks')) {
            $base64String = implode('', $request->input('details_json_chunks'));
        } elseif ($request->filled('details_json')) {
            $base64String = $request->details_json;
        }

        if (!empty($base64String)) {
            // Check if it's base64 encoded
            $decodedString = base64_decode($base64String, true);
            if ($decodedString !== false && json_decode($decodedString, true) !== null) {
                $detailsInput = json_decode($decodedString, true);
            } else {
                $decoded = json_decode($base64String, true);
                if (is_array($decoded)) {
                    $detailsInput = $decoded;
                }
            }
        } elseif ($request->has('details') && is_array($request->input('details'))) {
            $detailsInput = $request->input('details');
        }

        if (!empty($detailsInput)) {
            $hasNg = false;
            foreach ($detailsInput as $id => $data) {
                $detail = \App\Models\NpcChecksheetDetail::find($id);
                if ($detail && $detail->npc_checksheet_id == $checksheet->id) {
                    $rowResult = $data['row_result'] ?? null;
                    if ($rowResult === 'NG') {
                        $hasNg = true;
                    }
                    $detailUpdate = [
                        'row_result' => $rowResult,
                        'samples' => $data['samples'] ?? null,
                    ];

                    if (!empty($data['ng_photo_base64'])) {
                        $base64Data = $data['ng_photo_base64'];
                        if ($base64Data === 'REMOVE') {
                            $detailUpdate['ng_photo_path'] = null;
                        } elseif (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                            $imageData = substr($base64Data, strpos($base64Data, ',') + 1);
                            $ext = strtolower($type[1]);
                            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                $ext = 'jpg';
                            }
                            $decodedImg = base64_decode($imageData);
                            if ($decodedImg !== false) {
                                $filename = 'ng_detail_' . $detail->id . '_' . time() . '.' . $ext;
                                $path = 'checksheets/ng_evidence/' . $filename;
                                \Illuminate\Support\Facades\Storage::disk('public')->put($path, $decodedImg);
                                $detailUpdate['ng_photo_path'] = $path;
                            }
                        }
                    }

                    if (array_key_exists('ng_reason', $data)) {
                        $detailUpdate['ng_reason'] = $data['ng_reason'];
                    }

                    $detail->update($detailUpdate);
                }
            }
            
            if ($request->has('final_result')) {
                $checksheet->update(['final_result' => $request->input('final_result')]);
            }

            if ($hasNg && $action === 'approve') {
                $msg = 'Cannot approve because there is an NG result. Please fix the part and change to OK, or use Save Changes to hold.';
                if ($request->expectsJson()) {
                    $request->session()->flash('error', $msg);
                    return response()->json(['redirect' => route('checksheet-approvals.show', $checksheet->hashed_id)]);
                }
                return redirect()->back()->with('error', $msg);
            }
            
            if ($hasNg && $action === 'save' && empty(trim($request->input('final_result')))) {
                $msg = 'Remark is required when there is an NG result.';
                if ($request->expectsJson()) {
                    $request->session()->flash('error', $msg);
                    return response()->json(['redirect' => route('checksheet-approvals.show', $checksheet->hashed_id)]);
                }
                return redirect()->back()->with('error', $msg);
            }
        }
        
        if ($action === 'save') {
            $msg = 'Changes have been saved successfully.';
            if ($request->expectsJson()) {
                $request->session()->flash('success', $msg);
                return response()->json(['redirect' => route('checksheet-approvals.show', $checksheet->hashed_id)]);
            }
            return redirect()->route('checksheet-approvals.show', $checksheet->hashed_id)->with('success', $msg);
        }
        
        if (!auth()->user()->canApproveChecksheetStage($status)) {
            abort(403, 'You do not have the required Role or Permission to approve/reject at this stage.');
        }

        if ($action === 'reject') {
            $request->validate([
                'reject_reason' => 'required|string|max:1000'
            ]);

            $updateData['reject_reason'] = $request->reject_reason;
            $updateData['rejected_by_id'] = $userId;
            $updateData['rejected_from_stage'] = $status;
            $updateData['rejected_at'] = $now;
            $updateData['resubmitted_by_id'] = null;
            $updateData['resubmitted_at'] = null;

            if ($request->hasFile('reject_photo')) {
                $file = $request->file('reject_photo');
                $filename = 'reject_' . $checksheet->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('checksheets/rejects', $filename, 'public');
                $updateData['reject_photo_path'] = $path;
            } elseif ($request->filled('reject_photo_base64')) {
                $base64Data = $request->input('reject_photo_base64');
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                    $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                    $type = strtolower($type[1]);
                    if (!in_array($type, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $type = 'jpg';
                    }
                    $base64Data = base64_decode($base64Data);
                    if ($base64Data !== false) {
                        $filename = 'reject_' . $checksheet->id . '_' . time() . '.' . $type;
                        $path = 'checksheets/rejects/' . $filename;
                        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $base64Data);
                        $updateData['reject_photo_path'] = $path;
                    }
                }
            }

            if ($status === 'WAITING_MGM_MGR') {
                $updateData['approval_status'] = 'WAITING_QE_MGR';
                $updateData['qe_mgr_id'] = null;
                $updateData['qe_mgr_date'] = null;
            } elseif ($status === 'WAITING_QE_MGR') {
                $updateData['approval_status'] = 'WAITING_MGM_ASSMAN';
                $updateData['mgm_assman_id'] = null;
                $updateData['mgm_assman_date'] = null;
            } elseif ($status === 'WAITING_MGM_ASSMAN') {
                $updateData['approval_status'] = 'WAITING_MGM_SPV';
                $updateData['mgm_spv_id'] = null;
                $updateData['mgm_spv_date'] = null;
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
                if ($request->expectsJson()) {
                    $request->session()->flash('error', 'Cannot reject at this state.');
                    return response()->json(['redirect' => route('checksheet-approvals.show', $checksheet->hashed_id)]);
                }
                return redirect()->back()->with('error', 'Cannot reject at this state.');
            }

            $checksheet->update($updateData);

            if ($request->expectsJson()) {
                $request->session()->flash('success', 'Checksheet successfully rejected and returned to the previous step.');
                return response()->json(['redirect' => $redirectUrl]);
            }

            return redirect($redirectUrl)->with('success', 'Checksheet successfully rejected and returned to the previous step.');
        }

        // If currently approving at or past the stage that originally rejected it, clear active rejection data
        if ($checksheet->rejected_from_stage && $status === $checksheet->rejected_from_stage) {
            $updateData['reject_reason'] = null;
            $updateData['rejected_by_id'] = null;
            $updateData['rejected_from_stage'] = null;
            $updateData['rejected_at'] = null;
            $updateData['reject_photo_path'] = null;
            $updateData['resubmitted_by_id'] = null;
            $updateData['resubmitted_at'] = null;
        } elseif ($checksheet->reject_reason) {
            $updateData['resubmitted_by_id'] = $userId;
            $updateData['resubmitted_at'] = $now;
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
            $updateData['approval_status'] = 'WAITING_MGM_ASSMAN';
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
            $updateData['reject_reason'] = null;
            $updateData['rejected_by_id'] = null;
            $updateData['rejected_from_stage'] = null;
            $updateData['rejected_at'] = null;

            if ($part && $part->status === 'WAITING_APPROVAL') {
                $part->update(['status' => 'FINISHED']);
            }
        } else {
            if ($request->expectsJson()) {
                $request->session()->flash('error', 'Invalid approval status.');
                return response()->json(['redirect' => route('checksheet-approvals.show', $checksheet->hashed_id)]);
            }
            return redirect()->back()->with('error', 'Invalid approval status.');
        }

        $checksheet->update($updateData);

        // Kirim Notifikasi Email Thread PO untuk Approval
        try {
            $nextStage = $updateData['approval_status'] ?? 'APPROVED';
            $recipients = \App\Services\NotificationRecipientService::getRecipientsForApproval($checksheet, $nextStage);
            if (!empty($recipients)) {
                \Illuminate\Support\Facades\Mail::to($recipients)
                    ->queue(new \App\Mail\PoApprovalNotificationMail($checksheet, 'APPROVED', null));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed sending PO approval email notification: ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            $request->session()->flash('success', 'Checksheet successfully approved.');
            return response()->json(['redirect' => $redirectUrl]);
        }
        return redirect($redirectUrl)->with('success', 'Checksheet successfully approved.');
    }
}
