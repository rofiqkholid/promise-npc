<?php

namespace App\Http\Controllers;

use App\Models\NpcMasterRouting;
use App\Models\Product;
use App\Models\NpcProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NpcMasterRoutingController extends Controller
{
    public function index()
    {
        $routings = NpcMasterRouting::with(['part', 'process'])
            ->select('part_id')
            ->groupBy('part_id')
            ->paginate(10);
            
        // We grouped by part_id, so now we fetch the processes for each part
        foreach ($routings as $routing) {
            $routing->processes = NpcMasterRouting::with('process')
                ->where('part_id', $routing->part_id)
                ->orderBy('sequence_order')
                ->get();
        }

        return view('master.routings.index', compact('routings'));
    }

    public function create()
    {
        $processes = NpcProcess::with('departments')->orderBy('process_name')->get();
        return view('master.routings.create', compact('processes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'part_id' => 'required|exists:products,id',
            'process_ids' => 'required|array|min:1',
            'process_ids.*' => 'required|exists:npc_processes,id',
            'department_ids' => 'required|array|min:1',
            'department_ids.*' => 'required|exists:npc_departments,id',
        ]);

        DB::transaction(function () use ($request) {
            // Delete yang lama jika sudah ada (opsional jika ini create)
            NpcMasterRouting::where('part_id', $request->part_id)->delete();

            foreach ($request->process_ids as $index => $processId) {
                NpcMasterRouting::create([
                    'part_id' => $request->part_id,
                    'process_id' => $processId,
                    'department_id' => $request->department_ids[$index],
                    'sequence_order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('master.routings.index')->with('success', 'Routing Master successfully saved.');
    }

    public function edit($part_id)
    {
        if (!is_numeric($part_id)) {
            $hashids = new \Hashids\Hashids(env('APP_KEY'), 10);
            $decoded = $hashids->decode($part_id);
            $part_id = !empty($decoded) ? $decoded[0] : abort(404);
        }

        $part = Product::findOrFail($part_id);
        $routings = NpcMasterRouting::where('part_id', $part_id)->orderBy('sequence_order')->get();
        $processes = NpcProcess::with('departments')->orderBy('process_name')->get();

        return view('master.routings.edit', compact('part', 'routings', 'processes'));
    }

    public function update(Request $request, $part_id)
    {
        if (!is_numeric($part_id)) {
            $hashids = new \Hashids\Hashids(env('APP_KEY'), 10);
            $decoded = $hashids->decode($part_id);
            $part_id = !empty($decoded) ? $decoded[0] : abort(404);
        }

        $request->validate([
            'part_id' => 'required|exists:products,id',
            'process_ids' => 'required|array|min:1',
            'process_ids.*' => 'required|exists:npc_processes,id',
            'department_ids' => 'required|array|min:1',
            'department_ids.*' => 'required|exists:npc_departments,id',
        ]);

        DB::transaction(function () use ($request, $part_id) {
            // Delete old routing for the original part_id
            NpcMasterRouting::where('part_id', $part_id)->delete();
            
            // If the part was changed, delete any existing routings for the new part_id to prevent duplicates
            if ($part_id != $request->part_id) {
                NpcMasterRouting::where('part_id', $request->part_id)->delete();
            }

            foreach ($request->process_ids as $index => $processId) {
                NpcMasterRouting::create([
                    'part_id' => $request->part_id,
                    'process_id' => $processId,
                    'department_id' => $request->department_ids[$index],
                    'sequence_order' => $index + 1,
                ]);
            }
        });

        return redirect()->route('master.routings.index')->with('success', 'Routing Master successfully updated.');
    }

    public function destroy($part_id)
    {
        if (!is_numeric($part_id)) {
            $hashids = new \Hashids\Hashids(env('APP_KEY'), 10);
            $decoded = $hashids->decode($part_id);
            $part_id = !empty($decoded) ? $decoded[0] : abort(404);
        }

        NpcMasterRouting::where('part_id', $part_id)->delete();
        return redirect()->route('master.routings.index')->with('success', 'Routing Master successfully deleted.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:npc_master_routings,id'
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->order as $index => $id) {
                NpcMasterRouting::where('id', $id)->update(['sequence_order' => $index + 1]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Routing sequence successfully updated.']);
    }
}
