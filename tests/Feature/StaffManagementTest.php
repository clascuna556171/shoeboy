<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_create_a_staff_account(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->actingAs($owner)->post('/staff', [
            'name' => 'New Hire',
            'email' => 'newhire@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff',
            'contact_number' => '09171234567',
        ])->assertSessionHas('success');

        $user = User::where('email', 'newhire@test.com')->firstOrFail();
        $this->assertSame('staff', $user->role);
        $this->assertTrue($user->is_active);
    }

    public function test_owner_can_toggle_staff_activation(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($owner)->patch("/staff/{$staff->id}/toggle")->assertSessionHas('info');
        $this->assertFalse($staff->fresh()->is_active);

        $this->actingAs($owner)->patch("/staff/{$staff->id}/toggle")->assertSessionHas('info');
        $this->assertTrue($staff->fresh()->is_active);
    }

    public function test_owner_cannot_deactivate_their_own_account(): void
    {
        $owner = User::factory()->create(['role' => 'owner', 'is_active' => true]);

        $this->actingAs($owner)->patch("/staff/{$owner->id}/toggle")->assertSessionHas('error');
        $this->assertTrue($owner->fresh()->is_active);
    }

    public function test_store_requires_a_unique_email(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);
        User::factory()->create(['email' => 'taken@test.com']);

        $this->actingAs($owner)->post('/staff', [
            'name' => 'Dup',
            'email' => 'taken@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'staff',
        ])->assertSessionHasErrors('email');
    }
}
