<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Comedy'
            ],
            [
                'name' => 'Thriller'
            ],
            [
                'name' => 'Sci-Fi'
            ],
            [
                'name' => 'Drama'
            ],
            [
                'name' => 'Romantic'
            ]
        ];

        foreach ($items as $item) {
            Category::create($item);
        }
    }
}
