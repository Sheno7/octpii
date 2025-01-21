<?php

namespace Tests\Unit;

use App\Models\Cities;
use App\Models\Countries;
use Tests\TestCase;

class UpCitiesControllerTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function testIndexReturnsSuccessResponse()
    {
        $response = $this->json('GET', '/api/v1/cities/list');
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'response' => Cities::select('cities.id','cities.title_ar', 'cities.title_en',
                        'countries.title_en AS country','cities.created_at')
                        ->join('countries', 'cities.country_id', '=', 'countries.id')
                        ->orderBy('id', 'desc')
                        ->paginate(10)
                        ->toArray(),
                ],
            ]);
    }

//    public function testaddCityReturnsSuccessResponse()
//    {
//        $country = Countries::where('deleted_at', null)->first();
//        $response = $this->json('POST', '/api/v1/cities/add', [
//            'title_ar' => 'test',
//            'title_en' => 'test',
//            'country_id' => $country,
//            'status' => 1,
//        ]);
//         $response->assertStatus(200)
//             ->assertJson([
//                 'status' => true,
//                 'message' => 'success',
//                 'data' => null,
//             ]);
//    }

//    public function testeditCityReturnsSuccessResponse()
//    {
//        $country = Countries::where('deleted_at', null)->first()->id;
//        $citites  = Cities::where('deleted_at', null)->first()->id;
//        $response = $this->json('POST', '/api/v1/cities/edit?id='.$citites, [
//            'title_ar' => 'test',
//            'title_en' => 'test',
//            'country_id' => $country,
//            'status' => 1,
//        ]);
//        $response->assertStatus(200)
//            ->assertJson([
//                'status' => true,
//                'message' => 'success',
//                'data' => null,
//            ]);
//    }

    public function testdeleteCityReturnsSuccessResponse()
    {
        $id = Cities::where('deleted_at', null)->first()->id;
        $response = $this->json('DELETE', '/api/v1/cities/destroy?id='.$id);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => null,
            ]);
    }



}
