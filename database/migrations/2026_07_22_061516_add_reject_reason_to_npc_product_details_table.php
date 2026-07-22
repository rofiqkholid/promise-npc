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
        Schema::table('npc_product_details', function (Blueprint $table) {
            $table->text('reject_reason')->nullable()->after('checksheet_approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_product_details', function (Blueprint $table) {
            $table->dropColumn('reject_reason');
        });
    }
};
