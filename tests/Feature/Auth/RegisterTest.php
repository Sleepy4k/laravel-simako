<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::factory()->create(['name' => 'admin']);
        Role::factory()->create(['name' => 'tenant']);
        Role::factory()->create(['name' => 'pengguna']);
    }

    public function test_register_choice_page_renders(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Auth/Register'));
    }

    public function test_user_register_page_renders(): void
    {
        $response = $this->get(route('register.user'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Auth/RegisterUser'));
    }

    public function test_tenant_register_page_renders(): void
    {
        $response = $this->get(route('register.tenant'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Auth/RegisterTenant'));
    }

    public function test_pengguna_can_register_with_email(): void
    {
        Storage::fake('public');

        $response = $this->post(route('register.user.store'), [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'phone' => null,
            'password' => 'password',
            'password_confirmation' => 'password',
            'gender' => 'male',
            'birth_date' => '2000-01-01',
            'id_card_number' => '3201234567890001',
            'id_card_image' => UploadedFile::fake()->image('ktp.jpg'),
        ]);

        $response->assertRedirect(route('dashboard.index'));
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
        $this->assertDatabaseHas('user_profiles', ['name' => 'Test User']);
    }

    public function test_pengguna_can_register_with_phone(): void
    {
        Storage::fake('public');

        $response = $this->post(route('register.user.store'), [
            'name' => 'Test Phone User',
            'email' => null,
            'phone' => '08119999999',
            'password' => 'password',
            'password_confirmation' => 'password',
            'gender' => 'female',
            'id_card_number' => '3201234567890002',
            'id_card_image' => UploadedFile::fake()->image('ktp.jpg'),
        ]);

        $response->assertRedirect(route('dashboard.index'));
        $this->assertDatabaseHas('users', ['phone' => '08119999999']);
    }

    public function test_pengguna_register_fails_without_email_or_phone(): void
    {
        Storage::fake('public');

        $response = $this->post(route('register.user.store'), [
            'name' => 'No Contact',
            'password' => 'password',
            'password_confirmation' => 'password',
            'gender' => 'male',
            'id_card_number' => '3201234567890003',
            'id_card_image' => UploadedFile::fake()->image('ktp.jpg'),
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('users', ['email' => null, 'phone' => null]);
    }

    public function test_tenant_can_register(): void
    {
        Storage::fake('public');

        $response = $this->post(route('register.tenant.store'), [
            'name' => 'Tenant Owner',
            'email' => 'tenant@example.com',
            'phone' => '08122222222',
            'password' => 'password',
            'password_confirmation' => 'password',
            'business_name' => 'Kost Test Jaya',
            'identity_image' => UploadedFile::fake()->image('ktp.jpg'),
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Tenant Owner',
        ]);

        $response->assertRedirect(route('dashboard.index'));
        $this->assertDatabaseHas('users', ['email' => 'tenant@example.com']);
        $this->assertDatabaseHas('tenant_profiles', ['business_name' => 'Kost Test Jaya']);
        $this->assertDatabaseHas('bank_accounts', ['account_number' => '1234567890']);
    }

    public function test_duplicate_email_fails_registration(): void
    {
        Storage::fake('public');

        User::factory()->pengguna()->create(['email' => 'existing@example.com']);

        $response = $this->post(route('register.user.store'), [
            'name' => 'Duplicate',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'gender' => 'male',
            'id_card_number' => '3201234567890004',
            'id_card_image' => UploadedFile::fake()->image('ktp.jpg'),
        ]);

        $response->assertSessionHasErrors('email');
    }
}
