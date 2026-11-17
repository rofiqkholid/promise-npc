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
        Schema::table('npc_part_processes', function (Blueprint $table) {
            $table->dateTime('overdue_notified_at')->nullable()->after('hold_reason')->comment('Timestamp when overdue notification email was sent to manager');
        });

        // Tandai seluruh proses lama yang sudah lewat batas waktu sebelum aturan ini aktif,
        // agar tidak memicu pengiriman email mendadak untuk data-data masa lalu.
        DB::table('npc_part_processes')
            ->where('status', '!=', 'FINISHED')
            ->whereNotNull('target_completion_date')
            ->whereDate('target_completion_date', '<', now()->toDateString())
            ->update(['overdue_notified_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_part_processes', function (Blueprint $table) {
            $table->dropColumn('overdue_notified_at');
        });
    }
};

