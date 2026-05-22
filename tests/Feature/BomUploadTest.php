<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\BomHeader;
use App\Models\Inventory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessBomInventoryJob;

class BomUploadTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default user and project
        $this->user = User::factory()->create();
        $this->project = Project::create([
            'name' => 'Falcon Core Heavy Rocket',
            'description' => 'Heavy lifter launcher thrust frame'
        ]);

        // Seed some inventory
        Inventory::create([
            'item_code' => 'IC-001',
            'description' => 'Fuselage Panels',
            'available_qty' => 500,
        ]);
    }

    /**
     * Test successful BOM Upload initializes queue dispatches.
     */
    public function test_bom_upload_initializes_queue_dispatched(): void
    {
        Queue::fake();

        // 1. Arrange: Create a valid CSV file string
        $csvContent = "item_code,description,uom,required_qty,specification,allocated_to_role\n"
                    . "IC-001,Fuselage Panels,pcs,50,Grade A,Engineering\n";
                    
        $file = UploadedFile::fake()->createWithContent('bom_payload.csv', $csvContent);

        // 2. Act: Send POST upload request
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bom/upload', [
                'project_id' => $this->project->id,
                'version' => 'v1.0.0',
                'file' => $file,
            ]);

        // 3. Assert: 202 Accepted status and job in queue
        $response->assertStatus(202);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'bom_header_id',
                'project_id',
                'version',
                'status',
                'status_url',
            ]
        ]);

        $bomHeaderId = $response->json('data.bom_header_id');
        $this->assertDatabaseHas('bom_headers', [
            'id' => $bomHeaderId,
            'version' => 'v1.0.0',
            'status' => 'processing',
        ]);

        // Verify bulk lines inserted
        $this->assertDatabaseHas('bom_line_items', [
            'bom_header_id' => $bomHeaderId,
            'item_code' => 'IC-001',
            'required_qty' => 50.0000,
        ]);

        Queue::assertPushed(ProcessBomInventoryJob::class);
    }

    /**
     * Test layout validation failure.
     */
    public function test_bom_upload_fails_on_structural_validation(): void
    {
        // Arrange: Missing mandatory 'allocated_to_role' header
        $badCsvContent = "item_code,description,uom,required_qty,specification\n"
                       . "IC-001,Fuselage Panels,pcs,50,Grade A\n";
                       
        $file = UploadedFile::fake()->createWithContent('bad_layout.csv', $badCsvContent);

        // Act
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bom/upload', [
                'project_id' => $this->project->id,
                'version' => 'v1.0.0',
                'file' => $file,
            ]);

        // Assert: 422 Unprocessable and structural message
        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonMissingPath('data');
        $this->assertStringContainsString('Structural Validation Failure', $response->json('message'));
        
        // Assert no records written
        $this->assertDatabaseCount('bom_headers', 0);
    }

    /**
     * Test version duplication block.
     */
    public function test_bom_upload_prevents_duplicate_versions(): void
    {
        // 1. Arrange: Create pre-existing version header in database
        BomHeader::create([
            'project_id' => $this->project->id,
            'version' => 'v1.1.0',
            'status' => 'completed',
            'uploaded_by' => $this->user->id,
        ]);

        $csvContent = "item_code,description,uom,required_qty,specification,allocated_to_role\n"
                    . "IC-001,Fuselage Panels,pcs,50,Grade A,Engineering\n";
                    
        $file = UploadedFile::fake()->createWithContent('bom_payload.csv', $csvContent);

        // 2. Act: Try to upload same version v1.1.0
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/bom/upload', [
                'project_id' => $this->project->id,
                'version' => 'v1.1.0',
                'file' => $file,
            ]);

        // 3. Assert: 422 validation fail on duplicate version
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['version']);
    }

    /**
     * Test status poll endpoints.
     */
    public function test_status_endpoint_returns_correct_payload(): void
    {
        // Arrange: Create completed BOM header
        $header = BomHeader::create([
            'project_id' => $this->project->id,
            'version' => 'v2.0.0',
            'status' => 'completed',
            'uploaded_by' => $this->user->id,
        ]);

        // Act
        $response = $this->getJson("/api/v1/bom/status/{$header->id}");

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.status', 'completed');
        $response->assertJsonPath('data.version', 'v2.0.0');
        $response->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'project_name',
                'version',
                'status',
                'line_items_count',
                'allocated_count',
                'intent_count',
                'allocations',
                'shortfalls',
            ]
        ]);
    }

    /**
     * Test dynamic metrics endpoint.
     */
    public function test_metrics_endpoint_returns_aggregates(): void
    {
        // Arrange: Create a project and check
        $response = $this->getJson('/api/v1/bom/metrics');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'projects_count',
                'inventory_count',
                'total_allocations',
                'total_shortfalls',
                'recent_uploads',
                'recent_audits',
                'allocations_by_role',
                'shortfalls_by_priority',
            ]
        ]);
    }
}
