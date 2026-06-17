<?php

namespace Tests\Feature\Public;

use App\Models\Kost;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KostBrowseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRoles();
    }

    private function createKost(array $attrs = []): Kost
    {
        $tenant = User::factory()->tenant()->create();
        $kost = Kost::factory()->create(array_merge([
            'user_id' => $tenant->id,
            'status' => 'active',
        ], $attrs));

        $room = Room::factory()->create(['kost_id' => $kost->id, 'is_available' => true]);
        RoomPrice::factory()->create(['room_id' => $room->id, 'period' => 'monthly', 'price' => 800000]);

        return $kost;
    }

    public function test_home_page_renders(): void
    {
        $this->createKost();

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Home/Index'));
    }

    public function test_kost_index_renders(): void
    {
        $this->createKost();

        $response = $this->get(route('kosts.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Kosts/Index'));
    }

    public function test_kost_index_filters_by_type(): void
    {
        $this->createKost(['type' => 'putra']);
        $this->createKost(['type' => 'putri']);

        $response = $this->get(route('kosts.index', ['type' => 'putra']));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Kosts/Index')
            ->where('kosts.total', 1)
        );
    }

    public function test_kost_show_renders(): void
    {
        $kost = $this->createKost();

        $response = $this->get(route('kosts.show', $kost->slug));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Kosts/Show'));
    }

    public function test_inactive_kost_not_shown_in_index(): void
    {
        $this->createKost(['status' => 'draft']);

        $response = $this->get(route('kosts.index'));
        $response->assertInertia(fn ($page) => $page
            ->where('kosts.total', 0)
        );
    }
}
