<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use App\Models\NpcPart;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix part_revision_id for child parts that were missing it
        $parts = NpcPart::with('product.siblings.docPackage')->whereNull('part_revision_id')->get();
        foreach ($parts as $part) {
            if ($part->product) {
                $effectiveDoc = $part->product->getEffectiveDocPackage();
                if ($effectiveDoc) {
                    $part->part_revision_id = $effectiveDoc->current_revision_id;
                    $part->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed for data backfill
    }
};
