<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Halaman Depan Kantor',
                'sort_order' => 1,
                'active' => true,
            ],
            [
                'name' => 'Ruang Pelayanan',
                'sort_order' => 2,
                'active' => true,
            ],
            [
                'name' => 'Ruang Kerja',
                'sort_order' => 3,
                'active' => true,
            ],
            [
                'name' => 'Toilet',
                'sort_order' => 4,
                'active' => true,
            ],
            [
                'name' => 'Area Parkir',
                'sort_order' => 5,
                'active' => true,
            ],
        ];

        foreach ($locations as $loc) {
            Location::updateOrCreate(
                ['name' => $loc['name']],
                [
                    'sort_order' => $loc['sort_order'],
                    'active' => $loc['active'],
                ]
            );
        }
    }
}

