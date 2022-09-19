<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Statement::factory(3)->create();
        \App\Models\Charge::factory(50)->create();
        \App\Models\Payment::factory(50)->create();
        \App\Models\Scan::factory(50)->create();
    }
}
