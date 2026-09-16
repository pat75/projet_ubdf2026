<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('categories.list') as $position => $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'name_plural' => $category['name_plural'],
                    'position' => $category['accueil'] ?? 90 + $position,
                    'is_active' => true,
                ],
            );
        }
    }
}
