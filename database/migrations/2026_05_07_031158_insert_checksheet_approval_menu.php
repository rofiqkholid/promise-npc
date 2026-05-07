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
        \Illuminate\Support\Facades\DB::table('npc_menus')->insert([
            'title' => 'Checksheet Approvals',
            'route_name' => 'checksheet-approvals.index',
            'icon' => 'fa-solid fa-list-check',
            'order' => 50,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('npc_menus')->where('route_name', 'checksheet-approvals.index')->delete();
    }
};
