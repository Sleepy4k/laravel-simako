<?php

namespace Tests\Feature\Tenant;

use App\Models\Kost;
use App\Models\TenantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KostManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    private function createVerifiedTenant(): User
    {
        $admin = User::factory()->admin()->create();
        $tenant = User::factory()->tenant()->create();
        TenantProfile::factory()->create([
            'user_id' => $tenant->id,
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);

        return $tenant;
    }

    public function test_tenant_can_view_kosts(): void
    {
        $tenant = $this->createVerifiedTenant();

        $response = $this->actingAs($tenant)->get(route('dashboard.tenant.kosts.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Tenant/Kosts/Index'));
    }

    public function test_tenant_can_create_kost(): void
    {
        $tenant = $this->createVerifiedTenant();

        $response = $this->actingAs($tenant)->post(route('dashboard.tenant.kosts.store'), [
            'name' => 'Kost Test Baru',
            'description' => 'Deskripsi kost',
            'type' => 'putra',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kosts', ['name' => 'Kost Test Baru', 'user_id' => $tenant->id]);
    }

    public function test_tenant_cannot_edit_another_tenants_kost(): void
    {
        $tenant = $this->createVerifiedTenant();
        $otherTenant = User::factory()->tenant()->create();
        $kost = Kost::factory()->create(['user_id' => $otherTenant->id]);

        $response = $this->actingAs($tenant)->get(route('dashboard.tenant.kosts.edit', $kost));
        $response->assertStatus(403);
    }

    public function test_tenant_can_delete_own_kost(): void
    {
        $tenant = $this->createVerifiedTenant();
        $kost = Kost::factory()->create(['user_id' => $tenant->id]);

        $response = $this->actingAs($tenant)->delete(route('dashboard.tenant.kosts.destroy', $kost));
        $response->assertRedirect();
        $this->assertModelMissing($kost);
    }
}
