<?php

namespace Database\Seeders;

use App\Models\Facility;
use App\Models\FacilityCategory;
use Illuminate\Database\Seeder;

class FacilityCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Umum',
                'icon' => 'home',
                'facilities' => [
                    ['name' => 'WiFi / Internet', 'icon' => 'wifi'],
                    ['name' => 'Parkir Motor', 'icon' => 'motorcycle'],
                    ['name' => 'Parkir Mobil', 'icon' => 'car'],
                    ['name' => 'CCTV', 'icon' => 'cctv'],
                    ['name' => 'Keamanan 24 Jam', 'icon' => 'security'],
                    ['name' => 'Akses Kartu', 'icon' => 'card'],
                    ['name' => 'Dapur Bersama', 'icon' => 'kitchen'],
                    ['name' => 'Ruang Santai', 'icon' => 'sofa'],
                    ['name' => 'Jemuran', 'icon' => 'laundry'],
                ],
            ],
            [
                'name' => 'Kamar Mandi',
                'icon' => 'shower',
                'facilities' => [
                    ['name' => 'Kamar Mandi Dalam', 'icon' => 'shower'],
                    ['name' => 'Kamar Mandi Bersama', 'icon' => 'shower-group'],
                    ['name' => 'Water Heater', 'icon' => 'water-heater'],
                    ['name' => 'Kloset Duduk', 'icon' => 'toilet'],
                ],
            ],
            [
                'name' => 'Kamar',
                'icon' => 'bed',
                'facilities' => [
                    ['name' => 'AC', 'icon' => 'ac'],
                    ['name' => 'Kipas Angin', 'icon' => 'fan'],
                    ['name' => 'Kasur', 'icon' => 'bed'],
                    ['name' => 'Lemari', 'icon' => 'wardrobe'],
                    ['name' => 'Meja Belajar', 'icon' => 'desk'],
                    ['name' => 'Kursi', 'icon' => 'chair'],
                    ['name' => 'TV', 'icon' => 'tv'],
                    ['name' => 'Cermin', 'icon' => 'mirror'],
                ],
            ],
            [
                'name' => 'Layanan',
                'icon' => 'service',
                'facilities' => [
                    ['name' => 'Laundry', 'icon' => 'washing-machine'],
                    ['name' => 'Cleaning Service', 'icon' => 'broom'],
                    ['name' => 'Listrik Termasuk', 'icon' => 'electricity'],
                    ['name' => 'Air Termasuk', 'icon' => 'water'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $facilities = $categoryData['facilities'];
            unset($categoryData['facilities']);

            $category = FacilityCategory::firstOrCreate(
                ['name' => $categoryData['name']],
                ['icon' => $categoryData['icon']],
            );

            foreach ($facilities as $facilityData) {
                Facility::firstOrCreate(
                    ['facility_category_id' => $category->id, 'name' => $facilityData['name']],
                    ['icon' => $facilityData['icon']],
                );
            }
        }
    }
}
