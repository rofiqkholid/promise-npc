@if(in_array($part->status, ['PO_REGISTERED', 'WAITING_DEPT_CONFIRM']))
    <div class="text-[10px] text-gray-400 italic font-medium">Waiting for Parts Registration</div>
@elseif($part->status === 'WAITING_QE_CHECK')
    @php
        $hasChecksheet = $part->checksheet()->exists();
    @endphp
    @if($hasChecksheet)
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-100 border border-blue-200 text-blue-800 text-[10px] font-bold shadow-sm"><i class="fa-solid fa-pen-to-square"></i> FILLED & BEING CHECKED</span>
    @else
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-orange-100 border border-orange-200 text-orange-800 text-[10px] font-bold shadow-sm"><i class="fa-solid fa-triangle-exclamation animate-pulse"></i> NOT YET INPUT BY QC</span>
    @endif
    <div class="mt-2 text-[10px] text-gray-500"><i class="fa-solid fa-calendar-check text-gray-400 mr-1"></i> Target QC: <span class="font-bold text-gray-700">{{ \Carbon\Carbon::parse($part->qc_target_date)->format('d M') }}</span></div>
@else
    <div class="text-[10px] text-emerald-600 font-bold bg-emerald-50 border border-emerald-100 px-2 py-1 inline-flex items-center gap-1"><i class="fa-solid fa-shield-halved"></i> PASSED QC</div>
@endif
