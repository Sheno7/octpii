<?php

namespace Tests\Feature;

use App\Models\PricingModels;
use Tests\TestCase;

class UpPricingModelsTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_listing(): void
    {
        $response = $this->get('api/v1/pricing-models/list');
        $response->assertStatus(200);
    }

    public function test_add(): void
    {
        $response = $this->post('api/v1/pricing-models/add', [
            'name' => 'test',
            'capacity' => 1,
            'variable_name' => 'test',
            'pricing_type' => 1,
            'capacity_threshold' => 1,
            'additional_cost' => 1,
            'markup' => 1,
            'created_at' => '2021-09-09 00:00:00',
            'updated_at' => '2021-09-09 00:00:00',
        ]);
        $response->assertStatus(200);
    }

    public function test_edit(): void
    {
        $id = PricingModels::where('deleted_at', null)->first()->id;
        $response = $this->post('api/v1/pricing-models/edit?id='.$id, [
            'name' => 'test',
            'capacity' => 1,
            'variable_name' => 'test',
            'pricing_type' => 1,
            'capacity_threshold' => 1,
            'additional_cost' => 1,
            'markup' => 1,
            'created_at' => '2021-09-09 00:00:00',
            'updated_at' => '2021-09-09 00:00:00',
        ]);
        $response->assertStatus(200);
    }

    public function test_delete(): void
    {
        $id = PricingModels::where('deleted_at', null)->first()->id;
        $response = $this->delete('api/v1/pricing-models/destroy?id='.$id);
        $response->assertStatus(200);
    }

    // test drop down
    public function test_drop_down(): void
    {
        $response = $this->get('api/v1/pricing-models/dropdown');
        $response->assertStatus(200);
    }
}
