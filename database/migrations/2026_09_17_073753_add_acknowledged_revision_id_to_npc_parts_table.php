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
        Schema::table('npc_parts', function (Blueprint $table) {
            $table->unsignedBigInteger('acknowledged_revision_id')->nullable()->after('part_revision_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_parts', function (Blueprint $table) {
            $table->dropColumn('acknowledged_revision_id');
        });
    }
};
