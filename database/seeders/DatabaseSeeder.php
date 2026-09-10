<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            WarehouseSeeder::class,
            CustomerGroupSeeder::class,
            SpecificationAttributeSeeder::class,
            MakeSeeder::class,
            ComputerPartSeeder::class,
            BackendMenuSeeder::class,
        ]);
    }
}
