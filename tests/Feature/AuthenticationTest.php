<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_admin_can_log_in_and_log_out(): void
    {
        $user = User::factory()->superAdmin()->create(['password' => 'KataSandi!123']);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'KataSandi!123'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_inactive_account_cannot_log_in(): void
    {
        $user = User::factory()->superAdmin()->inactive()->create(['password' => 'KataSandi!123']);

        $this->post(route('login'), ['email' => $user->email, 'password' => 'KataSandi!123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
