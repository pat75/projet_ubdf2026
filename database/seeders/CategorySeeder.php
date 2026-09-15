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
                $category + ['position' => $position, 'is_active' => true],
            );
        }
    }
}
