<?php

namespace Tests\Feature\Admin;

use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    private function createUnverifiedTenant(): User
    {
        $tenant = User::factory()->tenant()->create();
        TenantProfile::factory()->create([
            'user_id' => $tenant->id,
            'verified_at' => null,
        ]);

        return $tenant;
    }

    public function test_admin_can_view_tenants(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard.admin.tenants.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/Tenants/Index'));
    }

    public function test_admin_can_verify_tenant(): void
    {
        $admin = User::factory()->admin()->create();
        $tenant = $this->createUnverifiedTenant();

        $response = $this->actingAs($admin)->patch(route('dashboard.admin.tenants.verify', $tenant));
        $response->assertRedirect();
        $this->assertDatabaseHas('tenant_profiles', [
            'user_id' => $tenant->id,
        ]);
        $this->assertNotNull(TenantProfile::where('user_id', $tenant->id)->first()->verified_at);
    }

    public function test_admin_can_suspend_tenant(): void
    {
        $admin = User::factory()->admin()->create();
        $tenant = $this->createUnverifiedTenant();

        $response = $this->actingAs($admin)->patch(route('dashboard.admin.tenants.suspend', $tenant), [
            'reason' => 'Melanggar ketentuan layanan',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenant_profiles', [
            'user_id' => $tenant->id,
            'suspension_reason' => 'Melanggar ketentuan layanan',
        ]);
        $this->assertDatabaseHas('users', ['id' => $tenant->id, 'is_active' => false]);
    }

    public function test_non_admin_cannot_access_admin_routes(): void
    {
        $tenant = User::factory()->tenant()->create();

        $response = $this->actingAs($tenant)->get(route('dashboard.admin.tenants.index'));
        $response->assertStatus(403);
    }
}
