<?php

namespace Tests\Unit;

use App\Models\PricingModels;
use Tests\TestCase;

class UpPricingModelsControllerTest extends TestCase
{
    /**
     * A basic unit test example.
//     */
    public function test_listing(): void
    {
        $response = $this->json('GET', '/api/v1/pricing-models/list');
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'response' => PricingModels::select('id','name','capacity','variable_name','pricing_type','capacity_threshold','additional_cost','markup', 'created_at')
                        ->orderBy('id', 'desc')
                        ->paginate(10)
                        ->toArray(),
                ],
            ]);
    }
//
    public function test_add_pricing(): void
    {
        $response = $this->json('POST', '/api/v1/pricing-models/add', [
            'name' => 'test',
            'capacity' => 1,
            'variable_name' => 'test',
            'pricing_type' => 'fixed' ,
            'capacity_threshold' => 1,
            'additional_cost' => 0,
            'markup' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'name' => 'test',
                    'capacity' => 1,
                    'variable_name' => 'test',
                    'pricing_type' => 'fixed',
                    'capacity_threshold' => 1,
                    'markup' => 1,
                ],
            ]);
    }

//    public function test_edit_pricing(): void
//    {
//        $id = PricingModels::where('deleted_at', null)->first()->id;
//        $response = $this->json('POST', '/api/v1/pricing-models/edit?id='.$id, [
//            'name' => 'test',
//            'capacity' => 1,
//            'variable_name' => 'test',
//            'pricing_type' => 'fixed' ,
//            'capacity_threshold' => 1,
//            'additional_cost' => 0,
//            'markup' => 1,
//            'created_at' => now(),
//            'updated_at' => now()
//        ]);
//        $response->assertStatus(200)
//            ->assertJson([
//                'status' => true,
//                'message' => 'success',
//                'data' => [
//                    'name' => 'test',
//                    'capacity' => 1,
//                    'variable_name' => 'test',
//                    'pricing_type' => 'fixed' ,
//                    'capacity_threshold' => 1,
//                    'additional_cost' => 0,
//                    'markup' => 1
//                ],
//            ]);
//    }


    public function test_destroy_pricing(): void
    {
        $id = PricingModels::where('deleted_at', null)->first()->id;
        $response = $this->json('DELETE', '/api/v1/pricing-models/destroy?id='.$id);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success'
            ]);
    }
}
