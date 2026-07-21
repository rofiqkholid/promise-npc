<!-- ECN Alert Modal -->
<div x-data="{ 
        isOpen: false,
        open() { this.isOpen = true; document.body.classList.add('overflow-hidden'); },
        close() { this.isOpen = false; document.body.classList.remove('overflow-hidden'); }
    }"
    @open-ecn-alert.window="open()"
    x-show="isOpen"
    style="display: none;"
    class="fixed inset-0 z-[100] flex items-center justify-center">

    <!-- Backdrop -->
    <div x-show="isOpen" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()" 
         class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <!-- Modal Content -->
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         class="relative w-full max-w-4xl mx-4 bg-white dark:bg-gray-800 shadow-2xl border border-slate-200 dark:border-gray-700 overflow-hidden flex flex-col max-h-[85vh]">
        
        <!-- Header -->
        <div class="px-4 py-2 border-b border-slate-200 dark:border-gray-700 bg-red-50 dark:bg-red-900/20 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3 text-red-600 dark:text-red-400">
                <div class="w-10 h-10 bg-red-100 dark:bg-red-800/50 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Revision Update Warning (ECN)</h3>
                    <p class="text-sm text-red-500/80 dark:text-red-400/80">Some parts have drawing changes. Please review and apply the new revisions.</p>
                </div>
            </div>
            <button @click="close()" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-gray-700 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto bg-slate-50 dark:bg-gray-800/50 flex-1">
            @if(isset($ecnUpdatedParts) && count($ecnUpdatedParts) > 0)
            <div class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-100 dark:bg-gray-700/50 text-slate-600 dark:text-gray-300 font-semibold uppercase text-[11px] tracking-wider border-b border-slate-200 dark:border-gray-700">
                            <tr>
                                <th class="px-4 py-3">Part Info</th>
                                <th class="px-4 py-3">PO Number</th>
                                <th class="px-4 py-3">Time Created</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-gray-700 text-slate-700 dark:text-gray-300">
                            @foreach($ecnUpdatedParts as $ep)
                            <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-blue-600 dark:text-blue-400">{{ optional($ep->product)->part_no }}</div>
                                    <div class="text-xs text-slate-500">{{ optional($ep->product)->part_name }}</div>
                                    <div class="mt-1 flex items-center gap-2 text-[11px] font-medium bg-slate-100 dark:bg-gray-700 w-fit px-2 py-1">
                                        @php
                                            $oldRev = optional($ep->drawingRevision)->revision_no !== null && optional($ep->drawingRevision)->revision_no !== '' ? optional($ep->drawingRevision)->revision_no : '-';
                                            $oldEcn = optional($ep->drawingRevision)->ecn_no ?: 'No ECN';
                                            
                                            $newRevObj = optional(optional(optional($ep->product)->docPackage)->currentRevision);
                                            $newRev = $newRevObj->revision_no !== null && $newRevObj->revision_no !== '' ? $newRevObj->revision_no : '-';
                                            $newEcn = $newRevObj->ecn_no ?: 'No ECN';
                                            
                                            $oldText = "Rev $oldRev ($oldEcn)";
                                            $newText = "Rev $newRev ($newEcn)";
                                        @endphp
                                        <span class="text-red-500 line-through" title="Old ECN">
                                            {{ $oldText }}
                                        </span>
                                        <i class="fa-solid fa-arrow-right text-gray-400"></i>
                                        <span class="text-green-600" title="New ECN">
                                            @if($oldText === $newText)
                                                {{ $newText }} <span class="text-amber-600 text-[10px] ml-1 bg-amber-100 px-1 rounded">(New File Uploaded)</span>
                                            @else
                                                {{ $newText }}
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 bg-slate-100 dark:bg-gray-700 text-xs font-semibold border border-slate-200 dark:border-gray-600" title="{{ $ep->po_list ?? optional($ep->event)->po_no }}">
                                        {{ isset($ep->po_count) && $ep->po_count > 1 ? $ep->po_count . ' Active PO(s)' : (optional($ep->event)->po_no ?? '-') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    {{ $ep->created_at->format('d M Y H:i') }}
                                    <div class="text-slate-400">{{ $ep->created_at->diffForHumans() }}</div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form action="{{ route('parts.apply-ecn', $ep->hashed_id) }}" method="POST" onsubmit="confirmAction(event, 'Apply latest revision to this part?')" class="inline-block">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-sm transition-colors flex items-center gap-2">
                                            <i class="fa-solid fa-check"></i> Apply
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="py-12 text-center flex flex-col items-center justify-center">
                <div class="w-16 h-16 bg-slate-100 dark:bg-gray-700 flex items-center justify-center mb-4">
                    <i class="fa-regular fa-face-smile text-3xl text-slate-400"></i>
                </div>
                <h4 class="text-lg font-semibold text-slate-800 dark:text-gray-200 mb-1">All clear!</h4>
                <p class="text-sm text-slate-500 dark:text-gray-400">No parts require ECN adjustment at this time.</p>
            </div>
            @endif
        </div>
        
        <!-- Footer -->
        <div class="px-4 py-2 border-t border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 shrink-0 text-right">
            <button @click="close()" class="px-5 py-2 text-sm font-medium text-slate-600 dark:text-gray-300 border border-slate-300 dark:border-gray-600 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">Close</button>
        </div>
    </div>
</div>
