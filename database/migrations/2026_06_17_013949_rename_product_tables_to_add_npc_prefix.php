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
        if (Schema::hasTable('product_checkpoints')) {
            Schema::rename('product_checkpoints', 'npc_product_checkpoints');
        }
        if (Schema::hasTable('product_history_problems')) {
            Schema::rename('product_history_problems', 'npc_product_history_problems');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('npc_product_checkpoints')) {
            Schema::rename('npc_product_checkpoints', 'product_checkpoints');
        }
        if (Schema::hasTable('npc_product_history_problems')) {
            Schema::rename('npc_product_history_problems', 'product_history_problems');
        }
    }
};
