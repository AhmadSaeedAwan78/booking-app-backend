<?php

namespace Database\Seeders;

use App\Models\BussinessAdministrator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BusinessAdministratorrSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BussinessAdministrator::firstOrCreate([
            'id' => 1,
            'name' => 'John Dow',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
