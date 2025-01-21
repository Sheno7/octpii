<?php

namespace Tests\Feature;

use App\Models\PricingModels;
use App\Models\Sectors;
use App\Models\Services;
use Tests\TestCase;

class UpSectorsTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_sectors(): void
    {
        $response = $this->get('api/v1/sectors/list');
        $response->assertStatus(200);
    }

//    public function test_add_sector(): void
//    {
//        $response = $this->post('api/v1/sectors/add', [
//            'title_ar' => 'test',
//            'title_en' => 'test',
//            'pricing_model_id' => PricingModels::where('deleted_at', null)->first()->id,
//        ]);
//        $response->assertStatus(200);
//    }
//
//    public function test_edit_sector(): void
//    {
//        $id = Sectors::where('deleted_at', null)->first()->id;
//        $response = $this->post('api/v1/sectors/edit?id='.$id, [
//            'title_ar' => 'test 5',
//            'title_en' => 'test 5',
//        ]);
//        $response->assertStatus(200);
//    }
//
//    public function test_destroy_sector(): void
//    {
//        $id = Sectors::where('deleted_at', null)->first()->id;
//        $response = $this->delete('api/v1/sectors/destroy?id='.$id);
//        $response->assertStatus(200);
//    }
//
//    public function test_change_status(): void
//    {
//        $id = Sectors::where('deleted_at', null)->first()->id;
//        $response = $this->post('api/v1/sectors/changeStatus?id='.$id , [
//            'status' => '0|1'
//        ]);
//        $response->assertStatus(200);
//    }
//    // change service in sector exist in service row
//    public function test_change_service_in_sector(): void
//    {
//        $id = Sectors::where('deleted_at', null)->first()->id;
//        $response = $this->post('api/v1/sectors/addandremove?id='.$id , [
//            'serviceid' => Services::where('deleted_at', null)->first()->id,
//            'sectorid' => $id,
//        ]);
//        $response->assertStatus(200);
//    }
//
//    public function test_change_pricing_model_in_sector(): void
//    {
//        $id = Sectors::where('deleted_at', null)->first()->id;
//        $response = $this->post('api/v1/sectors/editPricingmodelForSector?sector_id='.$id , [
//            'pricing_model_id' => PricingModels::where('deleted_at', null)->first()->id,
//        ]);
//        $response->assertStatus(200);
//    }
}
