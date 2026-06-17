<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Role;
use App\Models\TenantProfile;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $tenantRole = Role::where('name', 'tenant')->first();
        $penggunaRole = Role::where('name', 'pengguna')->first();

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@simako.com'],
            [
                'role_id' => $adminRole->id,
                'phone' => '081200000001',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
        UserProfile::firstOrCreate(
            ['user_id' => $admin->id],
            ['name' => 'Admin Simako', 'gender' => 'male'],
        );

        // Tenant tetap
        $tenant = User::firstOrCreate(
            ['email' => 'tenant@simako.com'],
            [
                'role_id' => $tenantRole->id,
                'phone' => '081200000002',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
        TenantProfile::firstOrCreate(
            ['user_id' => $tenant->id],
            [
                'business_name' => 'Kost Melati Indah',
                'verified_at' => now(),
                'verified_by' => $admin->id,
            ],
        );
        BankAccount::firstOrCreate(
            ['user_id' => $tenant->id, 'account_number' => '1234567890'],
            [
                'bank_name' => 'BCA',
                'account_holder' => 'Budi Santoso',
                'is_primary' => true,
            ],
        );

        // Pengguna tetap
        $user = User::firstOrCreate(
            ['email' => 'user@simako.com'],
            [
                'role_id' => $penggunaRole->id,
                'phone' => '081200000003',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        );
        UserProfile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Andi Mahasiswa',
                'gender' => 'male',
                'birth_date' => '2000-05-15',
            ],
        );

        // 5 tenant random
        User::factory(5)->tenant()->create()->each(function (User $u) use ($admin) {
            $name = fake('id_ID')->name();
            TenantProfile::create([
                'user_id' => $u->id,
                'business_name' => 'Kost '.fake('id_ID')->lastName(),
                'verified_at' => now(),
                'verified_by' => $admin->id,
            ]);
            BankAccount::create([
                'user_id' => $u->id,
                'bank_name' => fake()->randomElement(['BCA', 'BRI', 'Mandiri', 'BNI', 'CIMB']),
                'account_number' => fake()->numerify('##########'),
                'account_holder' => $name,
                'is_primary' => true,
            ]);
        });

        // 15 pengguna random
        User::factory(15)->pengguna()->create()->each(function (User $u) {
            UserProfile::create([
                'user_id' => $u->id,
                'name' => fake('id_ID')->name(),
                'gender' => fake()->randomElement(['male', 'female']),
            ]);
        });
    }
}
