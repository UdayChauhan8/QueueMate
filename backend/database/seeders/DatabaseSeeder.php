<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;
use App\Models\Service;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $clinic = Clinic::firstOrCreate(
            ['slug' => 'greenlab'],
            [
                'name' => 'GreenLab Diagnostics',
                'timezone' => 'Asia/Kolkata',
                'settings' => ['avg_duration_default' => 10, 'no_show_timeout' => 10],
            ]
        );

        Service::firstOrCreate(
            ['clinic_id' => $clinic->id, 'name' => 'General Test'],
            ['avg_duration_minutes' => 10]
        );
    }
}
