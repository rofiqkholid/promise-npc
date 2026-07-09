<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('npc_events', function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->nullable()->after('delivery_to');
            // Adding index to speed up filtering
            $table->index('model_id');
        });

        // Data Backfill: Populate model_id for existing events based on their first part
        \Illuminate\Support\Facades\DB::transaction(function () {
            $events = \App\Models\NpcEvent::with(['parts' => function($q) {
                // Get the first part order by id ASC (to mimic first())
                $q->orderBy('id', 'asc');
            }, 'parts.product'])->get();

            foreach ($events as $event) {
                $firstPart = $event->parts->first();
                if ($firstPart && $firstPart->product) {
                    $modelId = $firstPart->product->model_id;
                    if ($modelId) {
                        \Illuminate\Support\Facades\DB::table('npc_events')
                            ->where('id', $event->id)
                            ->update(['model_id' => $modelId]);
                    }
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_events', function (Blueprint $table) {
            $table->dropIndex(['model_id']);
            $table->dropColumn('model_id');
        });
    }
};
