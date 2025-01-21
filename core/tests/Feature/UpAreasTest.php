<?php

namespace Feature;

use App\Models\Areas;
use App\Models\Cities;
use Tests\TestCase;

class UpAreasTest extends TestCase
{

    public function test_areas(): void
    {
        $response = $this->get('api/v1/areas/list');
        $response->assertStatus(200);
    }

    public function test_add(): void
    {
        $response = $this->post('api/v1/areas/add', [
            'title_ar' => 'test',
            'title_en' => 'test',
            'city_id' => Cities::where('deleted_at', null)->first()->id,
            'lat' => '123',
            'long' => '123'
        ]);
        $response->assertStatus(200);
    }

    public function test_destroy(): void
    {
        $id = Areas::where('deleted_at', null)->first()->id;
        $response = $this->delete('api/v1/areas/destroy?id='.$id);
        $response->assertStatus(200);
    }

    public function test_edit(): void
    {
        $id = Areas::where('deleted_at', null)->first()->id;
        $response = $this->post('api/v1/areas/edit?id='.$id, [
            'title_ar' => 'test',
            'title_en' => 'test',
            'lat' => '123',
            'long' => '123',
            'city_id' => Cities::where('deleted_at', null)->first()->id,
            'status' => 1,
        ]);
        $response->assertStatus(200);
    }

}
