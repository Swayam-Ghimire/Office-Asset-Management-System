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
        Category::create(['name' => 'Laptop']);
        Category::create(['name' => 'Monitor']);
        Category::create(['name' => 'Screen']);
        Category::create(['name' => 'Mouse']);
        Category::create(['name' => 'Keyboard']);
        Category::create(['name' => 'Chair']);
        Category::create(['name' => 'Phone']);
        Category::create(['name' => 'Tablet']);
        Category::create(['name' => 'Printer']);
        Category::create(['name' => 'Headset']);
        Category::create(['name' => 'Camera']);
        Category::create(['name' => 'USB']);
        Category::create(['name' => 'Cable']);
        Category::create(['name' => 'Bag']);
        Category::create(['name' => 'Desk']);
        Category::create(['name' => 'Software']);
        Category::create(['name' => 'Others']);

        


    }
}
