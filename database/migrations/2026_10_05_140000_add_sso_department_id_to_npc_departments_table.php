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
            $table->unsignedBigInteger('sso_department_id')->nullable()->after('is_active')->comment('Mapped ID from departments table in SSO/Core');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_departments', function (Blueprint $table) {
            $table->dropColumn('sso_department_id');
        });
    }
};
