<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsoleBatchPickerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Selecting a batch in the console must keep the owner inside the console,
     * not bounce them to the owner workspace (regression: form posted to /).
     */
    public function test_owner_batch_picker_stays_in_console(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $batch = Batch::factory()->create();

        $response = $this->actingAs($owner)
            ->get(route('staff.workspace', ['batch' => $batch->id]));

        $response->assertOk();
        $response->assertViewIs('dashboard.staff');
        $response->assertSee('action="'.route('staff.workspace').'"', false);
    }

    public function test_staff_batch_picker_stays_in_console(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $batch = Batch::factory()->create();

        $response = $this->actingAs($staff)
            ->get(route('staff.workspace', ['batch' => $batch->id]));

        $response->assertOk();
        $response->assertViewIs('dashboard.staff');
    }
}
