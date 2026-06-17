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
        Schema::create('npc_product_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreignId('npc_master_checkpoint_id')->constrained('npc_master_checkpoints')->onDelete('cascade');
            $table->string('custom_standard')->nullable();
            $table->timestamps();
            
            // Ensure unique mapping
            $table->unique(['product_id', 'npc_master_checkpoint_id'], 'prod_chkpt_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_product_checkpoints');
    }
};
