<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['name'=>'Chair']);
        Category::create(['name'=>'Car']);
        Category::create(['name'=>'Monitor']);
        Category::create(['name'=>'Bike']);
        Category::create(['name'=>'Pens']);
        Category::create(['name'=>'Printers']);
        Category::create(['name'=>'Scanner']);
        Category::create(['name'=>'Other']);
        Category::create(['name'=>'Software']);

    }
}
