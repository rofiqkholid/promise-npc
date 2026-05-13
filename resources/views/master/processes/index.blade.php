@extends('layouts.app')

@section('title', 'Master Process / Routing')
@section('page_title', 'Master Data / Process Production')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow border border-gray-200 dark:border-gray-700">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-route text-blue-500"></i> Register Process / Routing
        </h2>
        <a href="{{ route('master.processes.create') }}" class="px-4 py-2 bg-gradient-to-r from-blue-600 to-cyan-600 text-white hover:from-blue-700 hover:to-cyan-700 transition shadow-md shadow-blue-500/20 font-medium text-sm flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Add Process
        </a>
    </div>

    <div class="p-6">
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-400">
                <thead class="bg-gray-100 dark:bg-gray-700/50 text-slate-800 dark:text-slate-200 border-b border-gray-200 dark:border-gray-600 uppercase text-xs tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-semibold">No</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Name Process</th>
                        <th scope="col" class="px-6 py-4 font-semibold">Department (PIC)</th>
                        <th scope="col" class="px-6 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($processes as $i => $process)
                    <tr class="bg-white dark:bg-gray-800 border-b dark:border-gray-700 hover:bg-blue-50/50 dark:hover:bg-gray-700/30 transition group">
                        <td class="px-6 py-4 font-medium text-slate-500 dark:text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-6 py-4 font-bold text-slate-900 dark:text-white">{{ $process->process_name }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @forelse($process->departments as $dept)
                                    <span class="px-2.5 py-1 border text-xs font-semibold {{ $dept->name == 'ME' ? 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/40 dark:text-orange-300 dark:border-orange-800' : 'bg-purple-100 text-purple-800 border-purple-200 dark:bg-purple-900/40 dark:text-purple-300 dark:border-purple-800' }}">
                                        {{ $dept->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400 italic">Not set</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-1 opacity-50 group-hover:opacity-100 transition">
                                <a href="{{ route('master.processes.edit', $process->hashed_id) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 transition" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('master.processes.destroy', $process->hashed_id) }}" method="POST" class="inline" onsubmit="confirmAction(event, 'Delete proses ini secara permanen?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 hover:bg-red-50 p-2 transition" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i class="fa-regular fa-folder-open text-4xl text-gray-300 dark:text-gray-600"></i>
                                <p>No production process data registered yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

