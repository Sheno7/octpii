<?php

namespace Database\Seeders;

use App\Models\Domains;
use App\Models\Tenant;
use App\Models\Vendors;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PricingModelsTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $userId = env('SEEDER_USER_ID');
        // get last user insert into users table
        // swicth database connection to tenant
        $lastUser = DB::table('users')->where('id', $userId)->first();
        $vendor_id = Vendors::where('user_id', $userId)->first()->id;
        //$dynamicDatabaseName = Domains::where('vendor_id', $vendor_id)->pluck('domain')->first();
        $dynamicDatabaseName = Tenant::latest()->first();
        config(['database.connections.tenant.database' => $dynamicDatabaseName['tenancy_db_name']]);
        DB::connection('tenant')->table('users')->insert([
            'first_name' => $lastUser->first_name,
            'last_name' => $lastUser->last_name,
            'name' => $lastUser->first_name . ' ' . $lastUser->last_name,
            'email' => $lastUser->email,
            'phone' => $lastUser->phone,
            'country_id' => $lastUser->country_id,
            'dob' => $lastUser->dob ?? null,
            'gender' => $lastUser->gender?? null,
            'image' => $lastUser->image?? null,
            'password' => $lastUser->password ?? Hash::make(rand(10000000, 99999999)),
            'status' => 1,
            'created_at' => $lastUser->created_at,
            'updated_at' => $lastUser->updated_at,
        ]);
        //$lastUser = DB::table('users')->where('id', 4)->first();
        //dd($lastUser);
//        if ($lastUser !== null) {
//            DB::table('users')->insert([
//                'first_name' => $lastUser->first_name,
//                'last_name' => $lastUser->last_name,
//                'name' => $lastUser->first_name . ' ' . $lastUser->last_name,
//                'email' => $lastUser->email,
//                'phone' => $lastUser->phone,
//                'country_id' => $lastUser->country_id,
//                'password' => $lastUser->password ?? Hash::make('12345678'),
//                'status' => 1,
//                'created_at' => $lastUser->created_at,
//                'updated_at' => $lastUser->updated_at,
//            ]);
//        }
    }
}
        // Get last record from setting table
//        $lastRecord = DB::table('setting')->latest()->pluck('value')->first();
//        // get record ID
//        $lastRecordId = DB::table('setting')->latest()->pluck('id')->first();
//
//        if ($lastRecord !== null) {
//            $data = json_decode($lastRecord);
//
//            if ($data !== null && isset($data->user)) { // Check if $data is valid and has the 'user' property
//                DB::table('users')->insert([
//                    'first_name' => $data->user->first_name,
//                    'last_name' => $data->user->last_name,
//                    'name' => $data->user->first_name . ' ' . $data->user->last_name,
//                    'email' => $data->user->email,
//                    'phone' => $data->user->phone,
//                    'country_id' => $data->user->country_id,
//                    'password' => $data->user->password ?? Hash::make('12345678'),
//                    'status' => 1,
//                    'created_at' => $data->user->created_at,
//                    'updated_at' => $data->user->updated_at,
//                ]);
//
//                // remove row from setting after insert into users table
//                DB::table('setting')->where('id', $lastRecordId)->delete();
//            }
//        }
//    }

