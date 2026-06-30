<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role = 'worker', bool $active = true): User
    {
        return User::create([
            'name' => 'Test '.$role,
            'email' => $role.'@example.com',
            'password' => 'password', // يُجزّأ تلقائيًا عبر cast الموديل
            'role' => $role,
            'is_active' => $active,
        ]);
    }

    public function test_guest_is_redirected_to_login_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_admin_can_login_and_reach_dashboard(): void
    {
        $this->makeUser('admin');

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_worker_can_login(): void
    {
        $this->makeUser('worker');

        $this->post('/login', [
            'email' => 'worker@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->makeUser('admin');

        $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $this->makeUser('worker', active: false);

        $this->post('/login', [
            'email' => 'worker@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $user = $this->makeUser('admin');

        $this->actingAs($user)->get('/login')->assertRedirect('/dashboard');
    }

    public function test_user_can_logout(): void
    {
        $user = $this->makeUser('admin');

        $this->actingAs($user)->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }
}
