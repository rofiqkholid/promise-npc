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
        Schema::table('npc_checksheets', function (Blueprint $table) {
            $table->unsignedBigInteger('qe_assman_id')->nullable()->after('qe_spv_date');
            $table->dateTime('qe_assman_date')->nullable()->after('qe_assman_id');
            
            $table->unsignedBigInteger('mgm_assman_id')->nullable()->after('mgm_spv_date');
            $table->dateTime('mgm_assman_date')->nullable()->after('mgm_assman_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_checksheets', function (Blueprint $table) {
            $table->dropColumn([
                'qe_assman_id', 'qe_assman_date',
                'mgm_assman_id', 'mgm_assman_date'
            ]);
        });
    }
};
