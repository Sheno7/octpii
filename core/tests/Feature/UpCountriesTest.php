<?php

namespace Feature;

use App\Models\Countries;
use Exception;
use Tests\TestCase;

class UpCountriesTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_list_countries(): void
    {
        $response = $this->get('api/v1/countries/list');
        $response->assertStatus(200);
    }

    public function test_add_countries(): void
    {
        try {
                $response = $this->json('POST', 'api/v1/countries/add', [
                'title_ar' => 'test',
                'title_en' => 'test',
                'isocode' => 'test',
                'flag' => 'test',
                'code' => 'test',
                'status' => 0,
            ]);
            $response->assertStatus(200);
        } catch (Exception $e) {
            $this->fail('An unexpected exception was thrown: ' . $e->getMessage());
        }
}

    public function test_edit_countries(): void
    {
        try {
            $id = Countries::where('deleted_at', null)->first()->id;
            $response = $this->json('POST', 'api/v1/countries/edit?id='.$id, [
                'title_ar' => 'test',
                'title_en' => 'test',
                'isocode' => 'test',
                'flag' => 'test',
                'code' => 'test',
                'status' => 0,
            ]);
            $response->assertStatus(200);
        } catch (Exception $e) {
            $this->fail('An unexpected exception was thrown: ' . $e->getMessage());
        }
    }

    public function test_edit_countries_with_valid_data(): void
    {
        try {
            $id = Countries::where('deleted_at', null)->first()->id;
            $response = $this->json('POST', 'api/v1/countries/edit?id='.$id, [
                'title_ar' => 'test',
                'title_en' => 'test',
                'isocode' => 'test',
                'flag' => 'test',
                'code' => 'test',
                'status' => 0,
            ]);
            $response->assertStatus(200);
        } catch (Exception $e) {
            $this->fail('An unexpected exception was thrown: ' . $e->getMessage());
        }
    }

    public function delete_countries(): void
    {
        try {
            $id = Countries::where('deleted_at', null)->first()->id;
            $response = $this->json('POST', 'api/v1/countries/delete?id='.$id);
            $response->assertStatus(200);
        } catch (Exception $e) {
            $this->fail('An unexpected exception was thrown: ' . $e->getMessage());
        }
    }
}
