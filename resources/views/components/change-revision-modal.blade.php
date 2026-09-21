<!-- Change ECN Revision Modal -->
<div x-data="{
        isOpen: false,
        loading: false,
        partId: null,
        poNo: '',
        partNo: '',
        partName: '',
        selectedRevId: null,
        currentRevId: null,
        latestRevId: null,
        revisions: [],
        formAction: '',

        async open(detail) {
            this.partId = detail.partId;
            this.isOpen = true;
            this.loading = true;
            this.revisions = [];
            this.formAction = `/parts/${this.partId}/change-revision`;
            document.body.classList.add('overflow-hidden');

            try {
                const res = await fetch(`/parts/${this.partId}/revisions`);
                const data = await res.json();
                if (data.success) {
                    this.poNo = data.po_no;
                    this.partNo = data.part_no;
                    this.partName = data.part_name;
                    this.currentRevId = data.current_revision_id;
                    this.latestRevId = data.latest_revision_id;
                    this.selectedRevId = data.current_revision_id || (data.revisions.length ? data.revisions[0].id : null);
                    this.revisions = data.revisions;
                }
            } catch (err) {
                console.error('Failed to load ECN revisions', err);
            } finally {
                this.loading = false;
            }
        },

        close() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        }
    }"
    @open-change-revision-modal.window="open($event.detail)"
    x-show="isOpen"
    style="display: none;"
    class="fixed inset-0 z-[110] flex items-center justify-center"
    x-cloak>

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
         class="relative w-full max-w-lg mx-4 bg-white dark:bg-gray-800 shadow-2xl border border-slate-200 dark:border-gray-700 overflow-hidden flex flex-col max-h-[85vh]">

        <!-- Header -->
        <div class="px-5 py-3 border-b border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-700/50 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5 text-amber-600 dark:text-amber-400">
                <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                <h3 class="text-base font-bold text-slate-800 dark:text-gray-100">Change / Revert ECN Revision</h3>
            </div>
            <button @click="close()" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-gray-200 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 overflow-y-auto flex-1">
            <!-- Loading indicator -->
            <div x-show="loading" class="py-10 text-center text-slate-500">
                <i class="fa-solid fa-spinner fa-spin text-2xl mb-2 text-amber-500"></i>
                <p class="text-sm">Loading ECN revisions...</p>
            </div>

            <!-- Content when loaded -->
            <div x-show="!loading">
                <!-- Info Part & PO -->
                <div class="bg-slate-50 dark:bg-gray-700/40 p-3.5 rounded border border-slate-200 dark:border-gray-600 mb-5 text-xs text-slate-700 dark:text-gray-300 space-y-1">
                    <div class="flex justify-between">
                        <span class="font-semibold text-slate-500 dark:text-gray-400">PO Number:</span>
                        <span class="font-bold text-slate-800 dark:text-gray-100" x-text="poNo"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold text-slate-500 dark:text-gray-400">Part No:</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400" x-text="partNo"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-semibold text-slate-500 dark:text-gray-400">Part Name:</span>
                        <span class="font-medium" x-text="partName"></span>
                    </div>
                </div>

                <form :action="formAction" method="POST" id="changeRevisionForm">
                    @csrf
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-gray-300 mb-3">
                        SELECT DRAWING / ECN REVISION:
                    </label>

                    <div class="space-y-2 mb-4 max-h-56 overflow-y-auto pr-1">
                        <template x-for="rev in revisions" :key="rev.id">
                            <label :class="{
                                    'border-amber-500 bg-amber-50/50 dark:bg-amber-900/20': selectedRevId == rev.id,
                                    'border-slate-200 dark:border-gray-600 hover:bg-slate-50 dark:hover:bg-gray-700': selectedRevId != rev.id
                                }"
                                class="flex items-start gap-3 p-3 border cursor-pointer transition-colors text-xs">
                                
                                <input type="radio" name="part_revision_id" :value="rev.id" x-model="selectedRevId" class="mt-0.5 text-amber-500 focus:ring-amber-500">
                                
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 font-bold text-slate-800 dark:text-gray-200">
                                        <span x-text="'Rev ' + rev.revision_no + ' (' + rev.ecn_no + ')'"></span>
                                        <template x-if="rev.is_current">
                                            <span class="bg-green-100 text-green-700 text-[10px] px-1.5 py-0.5 rounded font-bold">Latest</span>
                                        </template>
                                        <template x-if="rev.id == currentRevId">
                                            <span class="bg-blue-100 text-blue-700 text-[10px] px-1.5 py-0.5 rounded font-bold">Used by this PO</span>
                                        </template>
                                    </div>
                                    <div class="text-[11px] text-slate-400 dark:text-gray-400 mt-0.5" x-text="'Created Date: ' + rev.created_at"></div>
                                </div>
                            </label>
                        </template>

                        <div x-show="revisions.length === 0" class="text-center py-6 text-slate-400 text-xs">
                            No ECN revision data found.
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 dark:border-gray-700 flex justify-end gap-2">
                        <button type="button" @click="close()" class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-gray-300 border border-slate-300 dark:border-gray-600 hover:bg-slate-100 dark:hover:bg-gray-700 transition">
                            Cancel
                        </button>
                        <button type="submit" :disabled="!selectedRevId" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white text-xs font-bold shadow transition flex items-center gap-1.5">
                            <i class="fa-solid fa-check"></i> Save Revision Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
