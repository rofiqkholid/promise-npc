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
            $table->unsignedBigInteger('qe_staff_id')->nullable()->after('qe_check_date');
            $table->dateTime('qe_staff_date')->nullable()->after('qe_staff_id');
            
            $table->unsignedBigInteger('qe_spv_id')->nullable()->after('qe_staff_date');
            $table->dateTime('qe_spv_date')->nullable()->after('qe_spv_id');
            
            $table->unsignedBigInteger('qe_mgr_id')->nullable()->after('qe_spv_date');
            $table->dateTime('qe_mgr_date')->nullable()->after('qe_mgr_id');
            
            $table->unsignedBigInteger('mgm_staff_id')->nullable()->after('mgm_check_date');
            $table->dateTime('mgm_staff_date')->nullable()->after('mgm_staff_id');
            
            $table->unsignedBigInteger('mgm_spv_id')->nullable()->after('mgm_staff_date');
            $table->dateTime('mgm_spv_date')->nullable()->after('mgm_spv_id');
            
            $table->unsignedBigInteger('mgm_mgr_id')->nullable()->after('mgm_spv_date');
            $table->dateTime('mgm_mgr_date')->nullable()->after('mgm_mgr_id');
            
            $table->string('approval_status')->default('WAITING_QE_STAFF')->after('final_result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_checksheets', function (Blueprint $table) {
            $table->dropColumn([
                'qe_staff_id', 'qe_staff_date',
                'qe_spv_id', 'qe_spv_date',
                'qe_mgr_id', 'qe_mgr_date',
                'mgm_staff_id', 'mgm_staff_date',
                'mgm_spv_id', 'mgm_spv_date',
                'mgm_mgr_id', 'mgm_mgr_date',
                'approval_status'
            ]);
        });
    }
};
