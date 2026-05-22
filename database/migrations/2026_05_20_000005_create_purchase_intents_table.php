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
        Schema::create('purchase_intents', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id');
            $table->foreignId('bom_header_id')->constrained('bom_headers')->onDelete('cascade');
            $table->string('item_code', 100);
            $table->text('description');
            $table->string('specification', 255)->nullable();
            $table->decimal('required_qty', 12, 4);
            $table->decimal('available_qty', 12, 4);
            $table->decimal('shortfall_qty', 12, 4);
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'acknowledged', 'po_raised'])->default('pending');
            $table->timestamps();

            $table->index(['batch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_intents');
    }
};
