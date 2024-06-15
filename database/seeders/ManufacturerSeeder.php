<?php

namespace Database\Seeders;

use App\Models\Manufacturer;
use Illuminate\Database\Seeder;

class ManufacturerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Penguin Random House'
            ],
            [
                'name' => 'HarperCollins'
            ],
            [
                'name' => 'Hachette Livre'
            ],
            [
                'name' => 'Simon & Schuster'
            ],
            [
                'name' => 'Macmillan Publishers'
            ]
        ];

        foreach ($items as $item) {
            Manufacturer::create($item);
        }
    }
}
