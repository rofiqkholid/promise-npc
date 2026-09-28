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
            $table->text('reject_reason')->nullable()->after('approval_status');
            $table->unsignedBigInteger('rejected_by_id')->nullable()->after('reject_reason');
            $table->string('rejected_from_stage')->nullable()->after('rejected_by_id');
            $table->timestamp('rejected_at')->nullable()->after('rejected_from_stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_checksheets', function (Blueprint $table) {
            $table->dropColumn(['reject_reason', 'rejected_by_id', 'rejected_from_stage', 'rejected_at']);
        });
    }
};
