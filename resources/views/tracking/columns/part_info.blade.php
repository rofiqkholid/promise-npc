<div class="text-gray-800 dark:text-gray-200 font-bold text-sm">{{ optional($part->product)->part_no }}</div>
<div class="text-xs text-gray-500 dark:text-gray-400 font-medium mb-1.5">{{ optional($part->product)->part_name }}</div>
<div class="text-[10px] text-gray-400 uppercase tracking-widest bg-gray-50 dark:bg-gray-700 px-2 py-0.5 inline-block border border-gray-200 dark:border-gray-600">{{ optional($part->product->vehicleModel)->name ?? 'Unknown Model' }}</div>
<div class="text-gray-800 dark:text-gray-300 font-black flex items-center gap-1.5 mt-2"><i class="fa-solid fa-boxes-stacked text-gray-400"></i> Initial Target: {{ number_format($part->qty) }} <span class="text-xs font-semibold text-gray-500">PCS</span></div>
