<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    protected $model = Setting::class;
    public function run(): void
    {
        DB::table('setting')->insert([
            [
                'key' => 'wizard',
                'value' => json_encode(true),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'sector',
                'value' => json_encode(2),
                 'created_at' => now(),
                'updated_at' => now(),

            ],
            [
                'key' => 'wizard_status',
                'value' => json_encode(true),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);


    }
}
