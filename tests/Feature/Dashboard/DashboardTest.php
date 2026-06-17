<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_pengguna_sees_dashboard(): void
    {
        $user = User::factory()->pengguna()->create();

        $response = $this->actingAs($user)->get(route('dashboard.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/Index')
            ->where('role', 'pengguna')
        );
    }

    public function test_tenant_sees_dashboard(): void
    {
        $tenant = User::factory()->tenant()->create();

        $response = $this->actingAs($tenant)->get(route('dashboard.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/Index')
            ->where('role', 'tenant')
        );
    }

    public function test_admin_sees_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard/Index')
            ->where('role', 'admin')
        );
    }

    public function test_pengguna_cannot_access_tenant_routes(): void
    {
        $user = User::factory()->pengguna()->create();

        $response = $this->actingAs($user)->get(route('dashboard.tenant.kosts.index'));
        $response->assertStatus(403);
    }

    public function test_tenant_cannot_access_admin_routes(): void
    {
        $tenant = User::factory()->tenant()->create();

        $response = $this->actingAs($tenant)->get(route('dashboard.admin.users.index'));
        $response->assertStatus(403);
    }
}
