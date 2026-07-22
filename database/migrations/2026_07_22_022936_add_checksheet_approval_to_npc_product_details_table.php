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
            $table->string('master_checksheet_status')->default('DRAFT');
            $table->unsignedBigInteger('checksheet_approved_by')->nullable();
            $table->timestamp('checksheet_approved_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_product_details', function (Blueprint $table) {
            $table->dropColumn(['master_checksheet_status', 'checksheet_approved_by', 'checksheet_approved_at']);
        });
    }
};
