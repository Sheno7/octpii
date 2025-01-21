<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Providers;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        if (Branch::count() === 0) {
            $branch = Branch::create([
                'name_en' => 'HQ',
                'name_ar' => 'الفرع الرئيسي',
            ]);
            Providers::query()->update(['branch_id' => $branch->id]);
            Booking::query()->update(['branch_id' => $branch->id]);
        }
    }
}
