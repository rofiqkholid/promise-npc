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
        Schema::table('npc_checksheet_details', function (Blueprint $table) {
            $table->text('ng_reason')->nullable()->after('ng_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_checksheet_details', function (Blueprint $table) {
            $table->dropColumn('ng_reason');
        });
    }
};
