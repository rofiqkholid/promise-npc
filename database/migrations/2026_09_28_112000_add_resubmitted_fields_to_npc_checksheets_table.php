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
            $table->unsignedBigInteger('resubmitted_by_id')->nullable()->after('reject_photo_path');
            $table->timestamp('resubmitted_at')->nullable()->after('resubmitted_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_checksheets', function (Blueprint $table) {
            $table->dropColumn(['resubmitted_by_id', 'resubmitted_at']);
        });
    }
};
