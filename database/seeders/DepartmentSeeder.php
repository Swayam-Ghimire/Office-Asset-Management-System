<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Department::create(['name'=>'Human Resource']);
        Department::create(['name'=>'Development']);
        Department::create(['name'=>'Production']);
        Department::create(['name'=>'Operational']);
    }
}
