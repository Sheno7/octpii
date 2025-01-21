<?php

namespace Tests\Unit;

use App\Models\Sectors;
use App\Models\Services;
use Tests\TestCase;

class UpServicesControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function testIndexReturnsSuccessResponse()
    {
        $response = $this->json('GET', '/api/v1/services/list');
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'response' => Services::select('services.id', 'services.title_ar', 'services.title_en', 'services.icon',
                        'services.status', 'services.description_ar', 'services.description_en',
                        'sectors.title_en as sector', 'sectors.id as sectorid', 'services.created_at')
                        ->join('sectors', 'services.sector_id', '=', 'sectors.id')
                        ->orderBy('id', 'desc')
                        ->paginate(10)
                        ->toArray(),
                ],
            ]);
    }

    // test for change status
    public function testChangeStatusWithValidId()
    {
        $id = Services::where('deleted_at', null)->first()->id;
        $response = $this->json('POST', '/api/v1/services/changeStatus', ['id' => $id , 'status' => 1]);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'response' => 'success'
                ],
            ]);
    }

    public function testEditStatusWithValidId()
    {
        $id = Services::where('deleted_at', null)->first()->id;
        $response = $this->json('POST', '/api/v1/services/edit',
            ['id' => $id , 'title_ar' => 'test' , 'title_en' => 'test' , 'description_ar' => 'test' , 'description_en' => 'test' ,
                'sector_id' => Sectors::where('deleted_at', null)->first()->id , 'status' => 1]);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'response' => 'success'
                ],
            ]);
    }

    public function testIndexReturnsAddResponse()
    {
        $response = $this->json('POST', '/api/v1/services/add',
            ['title_ar' => 'test' , 'title_en' => 'test' , 'description_ar' => 'test' , 'description_en' => 'test' ,
                'sector_id' => Sectors::where('deleted_at', null)->first()->id , 'status' => 1]);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'response' => 'success'
                ],
            ]);
    }


}
