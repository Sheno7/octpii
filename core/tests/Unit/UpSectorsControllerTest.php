<?php

namespace Tests\Unit;

use App\Models\PricingModels;
use App\Models\Sectors;
use App\Models\Services;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpSectorsControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
//    public function test_list_sector()
//    {
//        $response = $this->json('GET', '/api/v1/sectors/list');
//        $response->assertStatus(200)
//            ->assertJson([
//                'status' => true,
//                'message' => 'success',
//                'data' => [
//                    'response' => Sectors::select('id', 'title_ar', 'title_en', 'status', 'icon', 'created_at')
//                        ->withCount('services')
//                        ->orderBy('id', 'desc')
//                        ->paginate(10)
//                        ->toArray(),
//                ],
//            ]);
//    }
    public function testIndex()
    {
        // Arrange

        // Act
        $response = $this->get('/api/v1/sectors/list'); // Replace '/sectors' with the actual route for the index function

        // Assert
        $response->assertStatus(200); // Check if the response status is 200 (OK)
        $response->assertJsonStructure([
            'status',
            'data' => [
                '*' => [
                    'id',
                    'title_ar',
                    'title_en',
                    'status',
                    'icon',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                    'pricing_models',
                    'services_count',
                ]
            ]
        ]); // Check if the response JSON structure is as expected
    }

//    public function test_add_sector()
//    {
//        Storage::fake('uploads');
//        $response = $this->json('POST', '/api/v1/sectors/add', [
//            'title_ar' => 'Test Arabic Title',
//            'title_en' => 'Test English Title',
//            'pricing_model_id' => PricingModels::where('deleted_at', null)->first()->id,
//            'status' => 1
//        ]);
//
//        $response->assertStatus(200)
//            ->assertJson([
//                'status' => true,
//                'message' => 'success',
//                'data' => [
//                    'response' => 'success',
//                ],
//            ]);
//         $this->assertDatabaseHas('sectors', [
//            'title_ar' => 'Test Arabic Title',
//            'title_en' => 'Test English Title',
//            'status' => 1
//        ]);
////        $this->assertDatabaseHas('pricing_model_sector', [
////            'sector_id' => Sectors::where('deleted_at', null)->first()->id,
////            'pricing_model_id' => PricingModels::where('deleted_at', null)->first()->id,
////        ]);
//    }
//
//    public function test_delete_sector()
//    {
//        $id = Sectors::where('deleted_at', null)->first()->id;
//        $response = $this->json('DELETE', '/api/v1/sectors/destroy?id=' . $id);
//        $response->assertStatus(200)
//            ->assertJson([
//                'status' => true,
//                'message' => 'success',
//            ]);
//    }
//
//    public function test_edit_sector()
//    {
//        $id = Sectors::where('deleted_at', null)->first()->id;
//        $response = $this->json('POST', '/api/v1/sectors/edit?id=' . $id, [
//            'title_ar' => 'Test Arabic Title',
//            'title_en' => 'Test English Title',
//            'status' => 1
//        ]);
//        $response->assertStatus(200)
//            ->assertJson([
//                'status' => true,
//                'message' => 'success',
//            ]);
//    }
//
//    public function test_change_status()
//    {
//        $id = Sectors::where('deleted_at', null)->first()->id;
//        $response = $this->json('POST', '/api/v1/sectors/changeStatus?id=' . $id, [
//            'status' => 0
//        ]);
//        $response->assertStatus(200)
//            ->assertJson([
//                'status' => true,
//                'message' => 'success',
//            ]);
//    }
//
//    public function test_change_service_in_sector()
//    {
//        $id = Sectors::where('deleted_at', null)->first()->id;
//        $response = $this->json('POST', '/api/v1/sectors/addandremove?id=' . $id, [
//            'serviceid' => Services::where('deleted_at', null)->first()->id,
//            'sectorid' => Sectors::where('deleted_at', null)->first()->id,
//        ]);
//        $response->assertStatus(200)
//            ->assertJson([
//                'status' => true,
//                'message' => 'success',
//                'data' => [
//                    'response' => 'success'
//                ],
//            ]);
//    }
//
//    public function test_change_price_in_sector()
//    {
//        $id = Sectors::where('deleted_at', null)->first()->id;
//        $response = $this->json('POST', '/api/v1/sectors/editPricingmodelForSector?sector_id=' . $id
//            . '&pricing_model_id=' . PricingModels::where('deleted_at', null)->first()->id, [
//            'price' => 1000
//        ]);
//        $response->assertStatus(200)
//            ->assertJson([
//                'status' => true,
//                'message' => 'success',
//                'data' => [
//                    'response' => 'success'
//                ],
//            ]);
//    }
}
