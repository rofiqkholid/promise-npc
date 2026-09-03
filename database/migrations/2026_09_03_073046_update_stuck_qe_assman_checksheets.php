<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Push any checksheets currently stuck at WAITING_QE_ASSMAN to WAITING_MGM_ASSMAN
        DB::table('npc_checksheets')
            ->where('approval_status', 'WAITING_QE_ASSMAN')
            ->update(['approval_status' => 'WAITING_MGM_ASSMAN']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way data patch, cannot be reliably reversed.
    }
};
