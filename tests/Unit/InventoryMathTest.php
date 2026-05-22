<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\BomHeader;
use App\Models\BomLineItem;
use App\Models\Inventory;
use App\Models\MaterialAllocation;
use App\Models\PurchaseIntent;
use App\Services\InventoryManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class InventoryMathTest extends TestCase
{
    use RefreshDatabase;

    private InventoryManagementService $mathEngine;
    private BomHeader $bomHeader;
    private string $batchId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mathEngine = new InventoryManagementService();

        // Setup parent dependencies
        $user = User::factory()->create();
        $project = Project::create([
            'name' => 'Lunar Orion Rocket',
            'description' => 'Test launcher'
        ]);

        $this->bomHeader = BomHeader::create([
            'project_id' => $project->id,
            'version' => 'v1.0.0-test',
            'status' => 'processing',
            'uploaded_by' => $user->id,
        ]);

        $this->batchId = (string) Str::uuid();
    }

    /**
     * Test Condition A: Full Available Stock.
     */
    public function test_condition_a_full_available_stock(): void
    {
        // 1. Arrange: Stock = 100, Required = 40
        $inventory = Inventory::create([
            'item_code' => 'IC-TEST-001',
            'description' => 'Fuselage Braces',
            'available_qty' => 100.0000,
        ]);

        $lineItem = BomLineItem::create([
            'bom_header_id' => $this->bomHeader->id,
            'item_code' => 'IC-TEST-001',
            'description' => 'Fuselage Braces',
            'uom' => 'pcs',
            'required_qty' => 40.0000,
            'specification' => 'Alloy 6061',
            'allocated_to_role' => 'Engineering',
            'status' => 'pending',
        ]);

        // 2. Act
        $this->mathEngine->processLineItem($this->bomHeader, $lineItem, $this->batchId);

        // 3. Assert
        $inventory->refresh();
        $lineItem->refresh();

        $this->assertEquals(60.0000, (float) $inventory->available_qty);
        $this->assertEquals('in_stock', $lineItem->status);

        // Check allocation
        $allocation = MaterialAllocation::where('bom_header_id', $this->bomHeader->id)->first();
        $this->assertNotNull($allocation);
        $this->assertEquals('IC-TEST-001', $allocation->item_code);
        $this->assertEquals(40.0000, (float) $allocation->allocated_qty);

        // Assert NO purchase intents created
        $this->assertEquals(0, PurchaseIntent::count());
    }

    /**
     * Test Condition B: Partial Available Stock.
     */
    public function test_condition_b_partial_available_stock(): void
    {
        // 1. Arrange: Stock = 15, Required = 50
        $inventory = Inventory::create([
            'item_code' => 'IC-TEST-002',
            'description' => 'Titanium Struts',
            'available_qty' => 15.0000,
        ]);

        $lineItem = BomLineItem::create([
            'bom_header_id' => $this->bomHeader->id,
            'item_code' => 'IC-TEST-002',
            'description' => 'Titanium Struts',
            'uom' => 'pcs',
            'required_qty' => 50.0000,
            'specification' => 'Grade 5 Titanium',
            'allocated_to_role' => 'Defense',
            'status' => 'pending',
        ]);

        // 2. Act
        $this->mathEngine->processLineItem($this->bomHeader, $lineItem, $this->batchId);

        // 3. Assert
        $inventory->refresh();
        $lineItem->refresh();

        $this->assertEquals(0.0000, (float) $inventory->available_qty);
        $this->assertEquals('partial_stock', $lineItem->status);

        // Check partial allocation
        $allocation = MaterialAllocation::where('bom_header_id', $this->bomHeader->id)->first();
        $this->assertNotNull($allocation);
        $this->assertEquals(15.0000, (float) $allocation->allocated_qty);

        // Check purchase intent shortfall
        $intent = PurchaseIntent::where('bom_header_id', $this->bomHeader->id)->first();
        $this->assertNotNull($intent);
        $this->assertEquals('IC-TEST-002', $intent->item_code);
        $this->assertEquals(50.0000, (float) $intent->required_qty);
        $this->assertEquals(15.0000, (float) $intent->available_qty);
        $this->assertEquals(35.0000, (float) $intent->shortfall_qty);
        $this->assertEquals('high', $intent->priority); // Critical role 'Defense' should elevate priority
    }

    /**
     * Test Condition C: Out of Stock.
     */
    public function test_condition_c_out_of_stock(): void
    {
        // 1. Arrange: Stock = 0, Required = 25
        $inventory = Inventory::create([
            'item_code' => 'IC-TEST-003',
            'description' => 'Hydrogen Sensors',
            'available_qty' => 0.0000,
        ]);

        $lineItem = BomLineItem::create([
            'bom_header_id' => $this->bomHeader->id,
            'item_code' => 'IC-TEST-003',
            'description' => 'Hydrogen Sensors',
            'uom' => 'pcs',
            'required_qty' => 25.0000,
            'specification' => 'Cryo Grade',
            'allocated_to_role' => 'Logistics',
            'status' => 'pending',
        ]);

        // 2. Act
        $this->mathEngine->processLineItem($this->bomHeader, $lineItem, $this->batchId);

        // 3. Assert
        $inventory->refresh();
        $lineItem->refresh();

        $this->assertEquals(0.0000, (float) $inventory->available_qty);
        $this->assertEquals('out_of_stock', $lineItem->status);

        // Check NO allocations created
        $this->assertEquals(0, MaterialAllocation::count());

        // Check purchase intent created for full target volume
        $intent = PurchaseIntent::where('bom_header_id', $this->bomHeader->id)->first();
        $this->assertNotNull($intent);
        $this->assertEquals('IC-TEST-003', $intent->item_code);
        $this->assertEquals(25.0000, (float) $intent->required_qty);
        $this->assertEquals(0.0000, (float) $intent->available_qty);
        $this->assertEquals(25.0000, (float) $intent->shortfall_qty);
        $this->assertEquals('medium', $intent->priority); // Standard role 'Logistics', medium shortfall
    }

    /**
     * Test that zero required quantity does not allocate material.
     */
    public function test_required_qty_zero_creates_no_material_allocation(): void
    {
        // 1. Arrange: Stock = 10, Required = 0
        $inventory = Inventory::create([
            'item_code' => 'IC-TEST-004',
            'description' => 'Null Spacer',
            'available_qty' => 10.0000,
        ]);

        $lineItem = BomLineItem::create([
            'bom_header_id' => $this->bomHeader->id,
            'item_code' => 'IC-TEST-004',
            'description' => 'Null Spacer',
            'uom' => 'pcs',
            'required_qty' => 0.0000,
            'specification' => 'Not required',
            'allocated_to_role' => 'Engineering',
            'status' => 'pending',
        ]);

        // 2. Act
        $this->mathEngine->processLineItem($this->bomHeader, $lineItem, $this->batchId);

        // 3. Assert
        $inventory->refresh();
        $lineItem->refresh();

        $this->assertEquals(10.0000, (float) $inventory->available_qty);
        $this->assertEquals('in_stock', $lineItem->status);
        $this->assertEquals(0, MaterialAllocation::count());
        $this->assertEquals(0, PurchaseIntent::count());
    }
}
