<?php

namespace Tests\Unit;

use App\Models\Countries;
use Tests\TestCase;

class UpCountriesContrlllerTest extends TestCase
{
    public function testIndexReturnsSuccessResponse()
    {
        $response = $this->json('GET', '/api/v1/countries/list');
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'response' => Countries::select('id','title_ar', 'title_en', 'isocode', 'flag', 'code', 'created_at')
                        ->orderBy('updated_at', 'desc')
                        ->paginate(10)
                        ->toArray(),
                ],
            ]);
    }

    public function testindexedit()
    {
        $id = Countries::where('deleted_at', null)->first()->id;
        $response = $this->json('POST', '/api/v1/countries/edit?id='.$id, [
            'title_ar' => 'test',
            'title_en' => 'test',
            'isocode' => 'test',
            'flag' => 'test',
            'code' => 'test',
            'status' => 1,
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
            ]);
    }
    public function testindexdelete()
    {
        $id = Countries::where('deleted_at', null)->first()->id;
        $response = $this->json('DELETE', '/api/v1/countries/destroy?id='.$id);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
            ]);
    }

    public function testindexadd()
    {
        $response = $this->json('POST', '/api/v1/countries/add', [
            'title_ar' => 'test',
            'title_en' => 'test',
            'isocode' => 'EG',
            'flag' => 'test.png',
            'code' => '20',
            'currency' => 'EGP',
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success'
            ]);
    }
}
