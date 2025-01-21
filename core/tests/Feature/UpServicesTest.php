<?php

namespace Tests\Feature;
use App\Models\Services;
use Tests\TestCase;

class UpServicesTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_services(): void
    {
        $response = $this->get('api/v1/services/list');
        $response->assertStatus(200);
    }

    public function test_add_service(): void
    {
        $response = $this->json('POST', 'api/v1/services/add', [
            'title_ar' => 'test',
            'title_en' => 'test',
            'description_ar' => 'test',
            'description_en' => 'test',
            'sector_id' => 1,
           // 'pricing_model_id' => 1,
            'icon' => 'test',
            'status' => 1,
        ]);
        $response->assertStatus(200);
    }
    // test delete service
    public function test_delete_service(): void
    {
        $id = Services::where('deleted_at', null)->first()->id;
        $response = $this->json('DELETE', 'api/v1/services/destroy?id='.$id);
        $response->assertStatus(200);
    }

    public function test_edit_service(): void
    {
        $id = Services::where('deleted_at', null)->first()->id;
        $response = $this->json('POST', 'api/v1/services/edit?id='.$id, [
            'title_ar' => 'test',
            'title_en' => 'test',
            'description_ar' => 'test',
            'description_en' => 'test',
            'sector_id' => 1,
            'icon' => 'test',
            'status' => 1,
        ]);
        $response->assertStatus(200);
    }

    public function test_change_status(): void
    {
        $id = Services::where('deleted_at', null)->first()->id;
        $response = $this->json('POST', 'api/v1/services/changeStatus?id='.$id);
        $response->assertStatus(200);
    }
}
