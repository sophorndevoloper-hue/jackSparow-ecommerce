<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(BackendMenuSeeder::class);
    }
}
