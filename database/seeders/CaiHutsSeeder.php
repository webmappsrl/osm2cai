<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CaiHutsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\CaiHuts::factory(10)->create();
    }
}
