<?php

namespace Tests\Unit;

use App\Models\Areas;
use App\Models\Cities;
use Tests\TestCase;

class UpAreasControllerTest extends TestCase
{

    public function testIndexReturnsSuccessResponse()
    {
        $response = $this->json('GET', '/api/v1/areas/list');
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'response' => Areas::select('id','title_ar', 'title_en', 'city_id', 'created_at')
                        ->join('cities', 'areas.city_id', '=', 'cities.id')
                        ->select('areas.id','areas.title_ar', 'areas.title_en',
                            'cities.title_en AS city','areas.created_at')
                        ->orderBy('id', 'desc')
                        ->paginate(10)
                        ->toArray(),
                ],
            ]);
    }

    public function testAddReturnsSuccessResponse()
    {
        $response = $this->json('POST', '/api/v1/areas/add', [
            'title_ar' => 'test',
            'title_en' => 'test',
            'lat' => 166262.266,
            'long' => 162262.266,
            'city_id' => Cities::where('deleted_at', null)->first()->id,
            'status' => 0,
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
            ]);
    }

    public function testDestroyReturnsSuccessResponse()
    {
        $id = Areas::where('deleted_at', null)->first()->id;
        $response = $this->json('DELETE', '/api/v1/areas/destroy?id='.$id);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
            ]);
    }

    public function testindexEditReturnsSuccessResponse()
    {
        $id = Areas::where('deleted_at', null)->first()->id;
        $response = $this->json('POST', '/api/v1/areas/edit?id='.$id, [
            'title_ar' => 'test',
            'title_en' => 'test',
            'lat' => 166262.266,
            'long' => 162262.266,
            'city_id' => Cities::where('deleted_at', null)->first()->id,
            'status' => 1,
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
            ]);
    }



}
