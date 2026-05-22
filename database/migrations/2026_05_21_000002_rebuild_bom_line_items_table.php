<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('drop index if exists bom_line_items_item_code_index');
        DB::statement('drop index if exists bom_line_items_part_no_index');

        Schema::rename('bom_line_items', 'bom_line_items_old');

        Schema::create('bom_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_header_id')->constrained('bom_headers')->onDelete('cascade');
            $table->string('part_no', 100)->nullable();
            $table->string('part_code', 100)->nullable();
            $table->string('item_code', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('specification', 255)->nullable();
            $table->text('size_of_material')->nullable();
            $table->decimal('required_qty', 12, 4)->nullable();
            $table->string('uom', 30)->nullable();
            $table->string('purchase_tech_spec_no', 100)->nullable();
            $table->string('stock_verification', 20)->nullable();
            $table->text('remarks')->nullable();
            $table->string('allocated_to_role', 100)->nullable();
            $table->boolean('is_critical')->default(false);
            $table->boolean('is_assembly_header')->default(false);
            $table->enum('status', ['pending', 'assembly_header', 'in_stock', 'partial_stock', 'out_of_stock'])
                ->default('pending');
            $table->timestamps();

            $table->index('item_code');
            $table->index('part_no');
        });

        DB::table('bom_line_items')
            ->insertUsing(
                [
                    'id',
                    'bom_header_id',
                    'part_no',
                    'part_code',
                    'item_code',
                    'description',
                    'specification',
                    'size_of_material',
                    'required_qty',
                    'uom',
                    'purchase_tech_spec_no',
                    'stock_verification',
                    'remarks',
                    'allocated_to_role',
                    'is_critical',
                    'is_assembly_header',
                    'status',
                    'created_at',
                    'updated_at',
                ],
                DB::table('bom_line_items_old')->select([
                    'id',
                    'bom_header_id',
                    'part_no',
                    'part_code',
                    'item_code',
                    'description',
                    'specification',
                    'size_of_material',
                    'required_qty',
                    'uom',
                    'purchase_tech_spec_no',
                    'stock_verification',
                    'remarks',
                    'allocated_to_role',
                    'is_critical',
                    'is_assembly_header',
                    'status',
                    'created_at',
                    'updated_at',
                ])
            );

        Schema::drop('bom_line_items_old');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('bom_line_items', 'bom_line_items_new');

        Schema::create('bom_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_header_id')->constrained('bom_headers')->onDelete('cascade');
            $table->string('item_code', 100);
            $table->text('description');
            $table->string('uom', 30);
            $table->decimal('required_qty', 12, 4);
            $table->string('specification', 255)->nullable();
            $table->string('allocated_to_role', 100);
            $table->enum('status', ['pending', 'in_stock', 'partial_stock', 'out_of_stock'])
                ->default('pending');
            $table->timestamps();
            $table->string('part_no', 100)->nullable();
            $table->string('part_code', 100)->nullable();
            $table->text('size_of_material')->nullable();
            $table->string('purchase_tech_spec_no', 100)->nullable();
            $table->string('stock_verification', 20)->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->boolean('is_assembly_header')->default(false);

            $table->index('item_code');
            $table->index('part_no');
        });

        DB::table('bom_line_items')
            ->insertUsing(
                [
                    'id',
                    'bom_header_id',
                    'item_code',
                    'description',
                    'uom',
                    'required_qty',
                    'specification',
                    'allocated_to_role',
                    'status',
                    'created_at',
                    'updated_at',
                    'part_no',
                    'part_code',
                    'size_of_material',
                    'purchase_tech_spec_no',
                    'stock_verification',
                    'remarks',
                    'is_critical',
                    'is_assembly_header',
                ],
                DB::table('bom_line_items_new')->select([
                    'id',
                    'bom_header_id',
                    'item_code',
                    'description',
                    'uom',
                    'required_qty',
                    'specification',
                    'allocated_to_role',
                    'status',
                    'created_at',
                    'updated_at',
                    'part_no',
                    'part_code',
                    'size_of_material',
                    'purchase_tech_spec_no',
                    'stock_verification',
                    'remarks',
                    'is_critical',
                    'is_assembly_header',
                ])
            );

        Schema::drop('bom_line_items_new');
    }
};
