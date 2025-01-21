<?php

namespace Tests\Unit;


use App\Models\Vendors;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UpVendorsControllerTest extends TestCase
{
    public function testIndexReturnsSuccessResponse()
    {
        $response = $this->json('GET', '/api/v1/vendors/list');
        $response->assertStatus(200)
                      ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'response' => Vendors::select('vendors.id', 'vendors.org_name_en', 'sectors.id as sector_id',
                        'sectors.title_en as sector_name', 'vendors.services_count',
                        DB::raw("CASE WHEN vendors.status = 0 THEN 'inactive' ELSE 'active' END AS status"),
                        'vendors.created_at as created_at')
                        ->join('sectors', 'vendors.sector_id', '=', 'sectors.id')
                        ->join('domains', 'vendors.id', '=', 'domains.vendor_id')
                        ->join('users', 'vendors.user_id', '=', 'users.id')
                        ->orderBy('id', 'desc')
                        ->paginate(10)
                        ->toArray(),
                ],
            ]);
    }


    public function testIndexShowOnSuccess()
    {
        $vendor = DB::table('vendors')->select('id')->first();
        $response = $this->json('POST', '/api/v1/vendors/show', ['id' => $vendor->id]);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'response' => Vendors::select('vendors.id', 'vendors.org_name_en', 'sectors.id as sectorsid',
                        'sectors.title_en as sector_name', 'vendors.services_count',
                        DB::raw("CASE WHEN vendors.status = 0 THEN 'inactive' ELSE 'active' END AS status"),
                        'vendors.created_at as created_at')
                        ->join('sectors', 'vendors.sector_id', '=', 'sectors.id')
                        ->join('domains', 'vendors.id', '=', 'domains.vendor_id')
                        ->join('users', 'vendors.user_id', '=', 'users.id')
                        ->where('vendors.id', $vendor->id)
                        ->first()
                ]
            ]);
    }

//    public function testCheckdomain()
//    {
//        $response = $this->json('POST', '/api/v1/vendors/checkdomain', ['domain' => 'test.com']);
//        $response->assertStatus(200)
//            ->assertJson([
//                'status' => true,
//                'message' => 'success',
//                'data' => [
//                    'response' => DB::table('domains')->where('domain', 'test.com')->first()
//                ]
//            ]);
//    }
}

