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
        Schema::create('material_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_header_id')->constrained('bom_headers')->onDelete('cascade');
            $table->string('item_code', 100);
            $table->text('description');
            $table->decimal('allocated_qty', 12, 4);
            $table->string('allocated_to', 100);
            $table->string('allocated_by', 50)->default('System - Auto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_allocations');
    }
};
