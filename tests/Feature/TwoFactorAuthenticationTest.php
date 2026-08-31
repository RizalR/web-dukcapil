<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(TwoFactorAuthenticationProvider::class, new FakeTwoFactorAuthenticationProvider);
    }

    public function test_super_admin_without_mfa_must_enroll_before_accessing_panel(): void
    {
        $user = User::factory()->superAdminWithoutTwoFactorAuthentication()->create([
            'password' => 'KataSandi!123',
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'KataSandi!123',
        ])->assertRedirect(route('admin.two-factor.edit'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.two-factor.edit'));
    }

    public function test_editor_cannot_access_super_admin_mfa_settings(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)
            ->get(route('admin.two-factor.edit'))
            ->assertForbidden();
    }

    public function test_super_admin_can_enable_and_confirm_mfa(): void
    {
        $user = User::factory()->superAdminWithoutTwoFactorAuthentication()->create([
            'password' => 'KataSandi!123',
        ]);

        $this->actingAs($user)
            ->post(route('admin.two-factor.store'), ['password' => 'KataSandi!123'])
            ->assertRedirect(route('admin.two-factor.edit'));

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
        $this->actingAs($user)
            ->get(route('admin.two-factor.edit'))
            ->assertSee('Pindai QR code');

        $this->actingAs($user)
            ->put(route('admin.two-factor.update'), ['code' => '123456'])
            ->assertRedirect(route('admin.two-factor.edit'));

        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->actingAs($user)
            ->get(route('admin.two-factor.edit'))
            ->assertSee('MFA melindungi akun ini');
        $this->assertDatabaseHas('audit_logs', ['user_id' => $user->id, 'action' => 'auth.mfa.enabled']);
    }

    public function test_enrollment_requires_the_current_password(): void
    {
        $user = User::factory()->superAdminWithoutTwoFactorAuthentication()->create([
            'password' => 'KataSandi!123',
        ]);

        $this->actingAs($user)
            ->post(route('admin.two-factor.store'), ['password' => 'salah'])
            ->assertSessionHasErrors(['password' => 'Kata sandi saat ini tidak sesuai.']);

        $this->assertNull($user->fresh()->two_factor_secret);
    }

    public function test_configured_super_admin_must_complete_mfa_challenge(): void
    {
        $user = User::factory()->superAdmin()->create(['password' => 'KataSandi!123']);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'KataSandi!123',
        ])->assertRedirect(route('two-factor.challenge'));

        $this->assertGuest();
        $this->get(route('two-factor.challenge'))
            ->assertOk()
            ->assertSee('Verifikasi dua faktor');

        $this->post(route('two-factor.challenge.store'), ['code' => '123456'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.login',
        ]);
    }

    public function test_invalid_mfa_code_does_not_authenticate_super_admin(): void
    {
        $user = User::factory()->superAdmin()->create(['password' => 'KataSandi!123']);
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'KataSandi!123',
        ]);

        $this->post(route('two-factor.challenge.store'), ['code' => '000000'])
            ->assertSessionHasErrors(['code' => 'Kode authenticator atau recovery code tidak sesuai.']);

        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.mfa.failed',
        ]);
    }

    public function test_recovery_code_authenticates_once_and_is_replaced(): void
    {
        $user = User::factory()->superAdmin()->create(['password' => 'KataSandi!123']);
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'KataSandi!123',
        ]);

        $this->post(route('two-factor.challenge.store'), ['recovery_code' => 'TEST-RECOVERY-CODE'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotContains('test-recovery-code', $user->fresh()->recoveryCodes());
    }

    public function test_challenge_rejects_totp_and_recovery_code_submitted_together(): void
    {
        $user = User::factory()->superAdmin()->create(['password' => 'KataSandi!123']);
        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'KataSandi!123',
        ]);

        $this->post(route('two-factor.challenge.store'), [
            'code' => '123456',
            'recovery_code' => 'test-recovery-code',
        ])->assertSessionHasErrors(['code', 'recovery_code']);

        $this->assertGuest();
    }

    public function test_super_admin_can_regenerate_recovery_codes_with_password_and_totp(): void
    {
        $user = User::factory()->superAdmin()->create(['password' => 'KataSandi!123']);
        $oldRecoveryCodes = $user->recoveryCodes();

        $this->actingAs($user)
            ->post(route('admin.two-factor.recovery-codes.store'), [
                'password' => 'KataSandi!123',
                'code' => '123456',
            ])->assertRedirect(route('admin.two-factor.edit'));

        $this->assertNotSame($oldRecoveryCodes, $user->fresh()->recoveryCodes());
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'auth.mfa.recovery_codes_regenerated',
        ]);
    }

    public function test_guest_without_pending_login_cannot_open_mfa_challenge(): void
    {
        $this->get(route('two-factor.challenge'))
            ->assertRedirect(route('login'));
    }
}

final class FakeTwoFactorAuthenticationProvider implements TwoFactorAuthenticationProvider
{
    public function generateSecretKey()
    {
        return 'TESTTWOFACTORSECRET123456789012';
    }

    public function qrCodeUrl($companyName, $companyEmail, $secret)
    {
        return 'otpauth://totp/'.rawurlencode($companyName.':'.$companyEmail).'?secret='.$secret;
    }

    public function verify($secret, $code)
    {
        return $secret === 'TESTTWOFACTORSECRET123456789012' && $code === '123456';
    }
}
