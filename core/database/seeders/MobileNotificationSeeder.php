<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MobileNotificationSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $users = User::all();

        foreach ($users as $user) {
            for ($i = 1; $i <= 20; $i++) {
                $user->mobileNotifications()->updateOrCreate([
                    'title' => 'test-' . $i,
                ], [
                    'body' => 'Lorem magna amet aliquyam et et, dolores labore diam lorem diam. Sit sit sanctus amet at. Ea amet dolor diam.',
                    'is_read' => false,
                    'read_at' => null,
                ]);
            }
        }
    }
}
