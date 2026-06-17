<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::factory()->create(['name' => 'admin']);
        Role::factory()->create(['name' => 'tenant']);
        Role::factory()->create(['name' => 'pengguna']);
    }

    public function test_login_page_renders(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Auth/Login'));
    }

    public function test_user_can_login_with_email(): void
    {
        $user = User::factory()->pengguna()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_phone(): void
    {
        $user = User::factory()->pengguna()->create([
            'phone' => '08123456789',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login.store'), [
            'login' => '08123456789',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->pengguna()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->pengguna()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password'),
            'is_active' => false,
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->pengguna()->create();
        $this->actingAs($user);

        $response = $this->post(route('logout'));
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_requires_login_field(): void
    {
        $response = $this->post(route('login.store'), [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('login');
    }
}
