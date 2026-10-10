<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_headline_is_concise_and_excludes_actor(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'name' => 'Adrian Dael']);

        $log = AuditLog::create([
            'user_id' => $owner->id,
            'action' => 'batch_intake_created',
            'details' => ['batch_code' => 'B01', 'total_pairs' => 200, 'total_cost' => 150000, 'avg_cost' => 750],
        ]);

        $this->assertEquals('Recorded a batch intake — B01', $log->headline);
        $this->assertStringNotContainsString('Adrian Dael', $log->headline);
        $this->assertStringNotContainsString('console', $log->headline);
    }

    public function test_chips_surface_key_facts(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $log = AuditLog::create([
            'user_id' => $owner->id,
            'action' => 'payment_verified',
            'details' => ['method' => 'gcash', 'amount' => 4200, 'reference_no' => '8891', 'order_number' => 'ORD-1'],
        ]);

        $this->assertEquals(['GCASH', '₱4,200.00', '8891'], $log->chips);
    }

    public function test_sentence_omits_actor_and_console_context(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'name' => 'Adrian Dael']);

        $log = AuditLog::create([
            'user_id' => $owner->id,
            'action' => 'expense_recorded',
            'details' => ['amount' => 1212, 'category' => 'Sack Purchase'],
        ]);

        $this->assertStringNotContainsString('Adrian Dael', $log->sentence);
        $this->assertStringNotContainsString('console', $log->sentence);
        $this->assertStringContainsString('Sack Purchase', $log->sentence);
    }
}
