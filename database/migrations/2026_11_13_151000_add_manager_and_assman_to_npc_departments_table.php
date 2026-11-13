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
        Schema::table('npc_departments', function (Blueprint $table) {
            $table->unsignedBigInteger('manager_id')->nullable()->after('sso_department_id')->comment('User ID of the department manager');
            $table->unsignedBigInteger('assman_id')->nullable()->after('manager_id')->comment('User ID of the department assistant manager');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_departments', function (Blueprint $table) {
            $table->dropColumn(['manager_id', 'assman_id']);
        });
    }
};

