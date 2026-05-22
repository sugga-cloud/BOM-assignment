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
        Schema::create('bom_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_header_id')->constrained('bom_headers')->onDelete('cascade');

            // Hierarchical part number from the XLS (e.g. 1, 1.1, *1.2, 6.21.1)
            $table->string('part_no', 100)->nullable();
            // Formal part code (col 2 of XLS - often empty for sub-components)
            $table->string('part_code', 100)->nullable();
            // item_code = part_code if set, else part_no — used for inventory matching
            $table->string('item_code', 100)->nullable();
            $table->text('description')->nullable();
            // Material specification (MATERIAL SPECIFICATION column)
            $table->string('specification', 255)->nullable();
            // Size of material (SIZE OF MATERIAL column)
            $table->text('size_of_material')->nullable();
            // Quantity and unit — nullable for assembly parent header rows
            $table->decimal('required_qty', 12, 4)->nullable();
            $table->string('uom', 30)->nullable();
            // Purchase technical specification reference number
            $table->string('purchase_tech_spec_no', 100)->nullable();
            // Stock verification flag from XLS column 8
            $table->string('stock_verification', 20)->nullable();
            // Remarks column
            $table->text('remarks')->nullable();
            // Allocated role — not present in XLS, kept nullable for manual/future use
            $table->string('allocated_to_role', 100)->nullable();
            // Flags
            $table->boolean('is_critical')->default(false);        // part_no starts with *
            $table->boolean('is_assembly_header')->default(false); // row has no qty (parent row)

            $table->enum('status', ['pending', 'assembly_header', 'in_stock', 'partial_stock', 'out_of_stock'])->default('pending');
            $table->timestamps();

            $table->index('item_code');
            $table->index('part_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_line_items');
    }
};
