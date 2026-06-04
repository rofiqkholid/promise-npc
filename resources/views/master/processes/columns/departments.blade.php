<div class="flex flex-wrap gap-1">
    @forelse($process->departments as $dept)
        <span class="px-2.5 py-1 border text-xs font-semibold {{ $dept->name == 'ME' ? 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/40 dark:text-orange-300 dark:border-orange-800' : 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-900/40 dark:text-purple-300 dark:border-purple-800' }}">
            {{ $dept->name }}
        </span>
    @empty
        <span class="text-xs text-gray-400 italic">Not set</span>
    @endforelse
</div>
