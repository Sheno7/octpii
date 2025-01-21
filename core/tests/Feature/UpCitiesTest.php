<?php

namespace Feature;

use App\Models\Cities;
use App\Models\Countries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Predis\Command\Traits\Count;
use Tests\TestCase;

class UpCitiesTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_cities(): void
    {
        $response = $this->get('api/v1/cities/list');
        $response->assertStatus(200);
    }

    public function test_add(): void
    {
        $response = $this->post('api/v1/cities/add', [
            'title_ar' => 'test',
            'title_en' => 'test',
            'country_id' => Countries::where('deleted_at', null)->first()->id,
            'status' => 1,
        ]);
        $response->assertStatus(200);
    }

    public function test_edit(): void
    {
        $response = $this->post('api/v1/cities/edit', [
            'id' => 1,
            'title_ar' => 'test',
            'title_en' => 'test',
            'country_id' => Countries::where('deleted_at', null)->first()->id,
            'status' => 1,
        ]);
        $response->assertStatus(200);
    }

    public function test_destroy(): void
    {
        $id = Cities::where('deleted_at', null)->first()->id;
        $response = $this->delete('api/v1/cities/destroy?id='.$id);
        $response->assertStatus(200);
    }
}
