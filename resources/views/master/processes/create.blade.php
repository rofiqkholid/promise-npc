@extends('layouts.app')

@section('title', 'Add Master Process')
@section('page_title', 'Master Data / Process Production / Add New')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700 max-w-2xl">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
            <i class="fa-solid fa-plus-circle text-blue-600 mr-2"></i> Form Add Process
        </h2>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 text-red-700 p-4 m-6 mb-0 text-sm border-l-4 border-red-500">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('master.processes.store') }}" method="POST">
        @csrf
        <div class="p-6 space-y-6">
            
            <div class="space-y-1">
                <label for="process_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Name Process <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-solid fa-route text-xs"></i>
                    </div>
                    <input type="text" id="process_name" name="process_name" required value="{{ old('process_name') }}"
                        class="w-full border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                        style="padding-left: 2.5rem;" placeholder="Example: Stamping, Painting, Assy, Weld...">
                </div>
            </div>

            <div class="space-y-1">
                <label for="department_ids" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Department PIC <span class="text-red-500">*</span>
                </label>
                <div x-data="{
                    search: '',
                    open: false,
                    departments: {{ json_encode($departments->map(fn($d) => ['id' => $d->id, 'name' => $d->full_name ?? $d->name])) }},
                    selectedIds: [{{ implode(',', old('department_ids', [])) }}],
                    get filteredDepartments() {
                        if (this.search === '') {
                            return this.departments.filter(d => !this.selectedIds.includes(d.id));
                        }
                        return this.departments.filter(d => !this.selectedIds.includes(d.id) && d.name.toLowerCase().includes(this.search.toLowerCase()));
                    },
                    get selectedDepartments() {
                        return this.departments.filter(d => this.selectedIds.includes(d.id));
                    },
                    selectDepartment(id) {
                        if (!this.selectedIds.includes(id)) {
                            this.selectedIds.push(id);
                        }
                        this.search = '';
                        this.open = false;
                        this.$refs.searchInput.focus();
                    },
                    removeDepartment(id) {
                        this.selectedIds = this.selectedIds.filter(i => i !== id);
                    }
                }" class="space-y-3">
                    
                    <!-- Search / Select Box -->
                    <div class="relative" @click.away="open = false">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-search text-xs"></i>
                            </div>
                            <input 
                                x-ref="searchInput"
                                type="text" 
                                x-model="search" 
                                @focus="open = true"
                                class="w-full border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:text-white"
                                style="padding-left: 2.5rem;" 
                                placeholder="Type to search and add department...">
                        </div>
                        
                        <!-- Dropdown Box -->
                        <div x-show="open && filteredDepartments.length > 0" 
                             style="display: none;"
                             class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 max-h-60 overflow-y-auto">
                            <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
                                <template x-for="dept in filteredDepartments" :key="dept.id">
                                    <li>
                                        <button type="button" @click="selectDepartment(dept.id)" class="w-full text-left px-4 py-2 hover:bg-blue-50 dark:hover:bg-gray-700 hover:text-blue-600 dark:hover:text-white transition-colors">
                                            <span x-text="dept.name"></span>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        
                        <div x-show="open && search !== '' && filteredDepartments.length === 0" 
                             style="display: none;"
                             class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg border border-gray-200 dark:border-gray-700 p-4 text-center">
                            <span class="text-sm text-gray-500"><i class="fa-solid fa-magnifying-glass mr-1"></i> No department found with that keyword.</span>
                        </div>
                    </div>

                    <!-- Selected tags container (Result) -->
                    <div class="p-3 bg-slate-50 dark:bg-gray-800/50 border border-dashed border-gray-300 dark:border-gray-700 min-h-[60px]">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Selected PIC:</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-if="selectedDepartments.length === 0">
                                <span class="text-sm text-gray-400 italic flex items-center"><i class="fa-solid fa-inbox mr-2"></i> None selected yet.</span>
                            </template>
                            
                            <template x-for="dept in selectedDepartments" :key="dept.id">
                                <div class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 text-sm font-medium border border-blue-200 dark:border-blue-800 shadow-sm transition-all hover:shadow">
                                    <span x-text="dept.name"></span>
                                    <button type="button" @click="removeDepartment(dept.id)" class="ml-1.5 text-blue-400 hover:text-red-500 dark:text-blue-400 dark:hover:text-red-400 focus:outline-none transition-colors">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Hidden native inputs for form submission -->
                    <div class="hidden">
                        <template x-for="id in selectedIds">
                            <input type="hidden" name="department_ids[]" :value="id">
                        </template>
                        <!-- Fallback required validator simulation -->
                        <select name="department_validator" required class="hidden" x-bind:required="selectedIds.length === 0">
                            <option value=""></option>
                            <option value="filled" selected x-show="selectedIds.length > 0">Filled</option>
                        </select>
                    </div>

                </div>
            </div>

        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
            <a href="{{ route('master.processes.index') }}" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white shadow-md shadow-blue-500/20 text-sm font-medium hover:from-blue-700 hover:to-cyan-700 transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> Save Process
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>

</script>
@endpush
