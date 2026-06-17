<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\Kost;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KostSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = User::whereHas('role', fn ($q) => $q->where('name', 'tenant'))->get();
        $facilities = Facility::pluck('id')->toArray();

        $kostData = [
            [
                'kost' => [
                    'name' => 'Kost Melati Indah',
                    'description' => 'Kost eksklusif dengan suasana nyaman dan bersih. Cocok untuk mahasiswa dan karyawan. Dekat kampus dan pusat perbelanjaan.',
                    'type' => 'putri',
                ],
                'address' => [
                    'street' => 'Jl. Melati No. 12',
                    'district' => 'Sleman',
                    'city' => 'Yogyakarta',
                    'province' => 'DI Yogyakarta',
                ],
                'rooms' => [
                    ['name' => 'Kamar A1', 'floor' => 1, 'size_sqm' => 12, 'price_monthly' => 800000],
                    ['name' => 'Kamar A2', 'floor' => 1, 'size_sqm' => 12, 'price_monthly' => 800000],
                    ['name' => 'Kamar B1', 'floor' => 2, 'size_sqm' => 16, 'price_monthly' => 1200000],
                    ['name' => 'Kamar B2', 'floor' => 2, 'size_sqm' => 16, 'price_monthly' => 1200000],
                    ['name' => 'Kamar Premium', 'floor' => 2, 'size_sqm' => 20, 'price_monthly' => 1800000],
                ],
            ],
            [
                'kost' => [
                    'name' => 'Kost Putra Maju',
                    'description' => 'Kost putra strategis dekat universitas terkemuka. Fasilitas lengkap dengan keamanan 24 jam.',
                    'type' => 'putra',
                ],
                'address' => [
                    'street' => 'Jl. Dipatiukur No. 45',
                    'district' => 'Coblong',
                    'city' => 'Bandung',
                    'province' => 'Jawa Barat',
                ],
                'rooms' => [
                    ['name' => 'Standard 1', 'floor' => 1, 'size_sqm' => 10, 'price_monthly' => 600000],
                    ['name' => 'Standard 2', 'floor' => 1, 'size_sqm' => 10, 'price_monthly' => 600000],
                    ['name' => 'Deluxe 1', 'floor' => 2, 'size_sqm' => 14, 'price_monthly' => 950000],
                ],
            ],
        ];

        $mainTenant = User::where('email', 'tenant@simako.com')->first();

        foreach ($kostData as $i => $data) {
            $tenant = $i === 0 ? $mainTenant : $tenants->get($i % $tenants->count());

            $slug = Str::slug($data['kost']['name']);
            $uniqueSlug = $slug;
            $count = 1;
            while (Kost::where('slug', $uniqueSlug)->exists()) {
                $uniqueSlug = $slug.'-'.$count++;
            }

            $kost = Kost::firstOrCreate(
                ['slug' => $uniqueSlug],
                array_merge($data['kost'], [
                    'user_id' => $tenant->id,
                    'slug' => $uniqueSlug,
                    'status' => 'active',
                    'total_rooms' => count($data['rooms']),
                    'available_rooms' => count($data['rooms']),
                ]),
            );

            $kost->address()->firstOrCreate(
                ['addressable_type' => Kost::class, 'addressable_id' => $kost->id],
                $data['address'],
            );

            $facilitySubset = collect($facilities)->random(min(8, count($facilities)))->toArray();
            $kost->facilities()->syncWithoutDetaching($facilitySubset);

            foreach ($data['rooms'] as $roomData) {
                $priceMonthly = $roomData['price_monthly'];

                $room = Room::firstOrCreate(
                    ['kost_id' => $kost->id, 'name' => $roomData['name']],
                    [
                        'kost_id' => $kost->id,
                        'name' => $roomData['name'],
                        'floor' => $roomData['floor'],
                        'size_sqm' => $roomData['size_sqm'],
                        'is_available' => true,
                    ],
                );

                foreach ($this->pricesFor($priceMonthly) as $price) {
                    RoomPrice::firstOrCreate(
                        ['room_id' => $room->id, 'period' => $price['period']],
                        array_merge($price, ['room_id' => $room->id]),
                    );
                }
            }
        }

        // Random kosts untuk tenant lain
        $otherTenants = $tenants->filter(fn ($t) => $t->email !== 'tenant@simako.com');

        $randomNames = [
            'Kost Harmoni', 'Kost Sejahtera', 'Kost Damai', 'Kost Barokah',
            'Kost Flamboyan', 'Kost Dahlia', 'Kost Anggrek', 'Kost Mawar',
        ];

        $cities = [
            ['city' => 'Jakarta Selatan', 'province' => 'DKI Jakarta'],
            ['city' => 'Surabaya', 'province' => 'Jawa Timur'],
            ['city' => 'Medan', 'province' => 'Sumatera Utara'],
            ['city' => 'Semarang', 'province' => 'Jawa Tengah'],
        ];

        $otherTenants->each(function (User $tenant) use ($randomNames, $cities, $facilities) {
            $name = fake()->randomElement($randomNames).' '.fake('id_ID')->lastName();
            $city = fake()->randomElement($cities);
            $type = fake()->randomElement(['putra', 'putri', 'campur']);

            $slug = Str::slug($name);
            $uniqueSlug = $slug;
            $count = 1;
            while (Kost::where('slug', $uniqueSlug)->exists()) {
                $uniqueSlug = $slug.'-'.$count++;
            }

            $kost = Kost::create([
                'user_id' => $tenant->id,
                'name' => $name,
                'slug' => $uniqueSlug,
                'description' => 'Kost '.$type.' dengan lokasi strategis, nyaman dan bersih.',
                'type' => $type,
                'status' => 'active',
                'total_rooms' => 4,
                'available_rooms' => 4,
            ]);

            $kost->address()->create(array_merge($city, [
                'street' => 'Jl. '.fake()->streetName(),
                'district' => fake()->city(),
            ]));

            $facilitySubset = collect($facilities)->random(min(6, count($facilities)))->toArray();
            $kost->facilities()->attach($facilitySubset);

            for ($i = 1; $i <= 4; $i++) {
                $priceMonthly = fake()->randomElement([500000, 700000, 900000, 1200000, 1500000]);

                $room = Room::create([
                    'kost_id' => $kost->id,
                    'name' => 'Kamar '.chr(64 + $i),
                    'floor' => fake()->randomElement([1, 2]),
                    'size_sqm' => fake()->randomElement([9, 12, 14, 16]),
                    'is_available' => true,
                ]);

                $roomFacilitySubset = collect($facilities)->random(min(4, count($facilities)))->toArray();
                $room->facilities()->attach($roomFacilitySubset);

                foreach ($this->pricesFor($priceMonthly) as $price) {
                    RoomPrice::create(array_merge($price, ['room_id' => $room->id]));
                }
            }
        });
    }

    /** @return array<int, array{period: string, price: int, deposit: int}> */
    private function pricesFor(int $monthly): array
    {
        return [
            ['period' => 'monthly', 'price' => $monthly, 'deposit' => $monthly],
            ['period' => 'quarterly', 'price' => (int) ($monthly * 3 * 0.95), 'deposit' => $monthly],
            ['period' => 'semi_annual', 'price' => (int) ($monthly * 6 * 0.9), 'deposit' => $monthly],
            ['period' => 'annual', 'price' => (int) ($monthly * 12 * 0.85), 'deposit' => $monthly],
        ];
    }
}
