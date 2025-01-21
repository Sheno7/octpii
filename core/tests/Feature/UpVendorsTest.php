<?php

namespace Tests\Feature;

use App\Models\Vendors;
use Tests\TestCase;

class UpVendorsTest extends TestCase
{
    public function test_list_vendors(): void
    {
        $response = $this->get('api/v1/vendors/list');
        $response->assertStatus(200);
    }

    public function test_show_vendor(): void
    {
        $vendor = Vendors::where('deleted_at', null)->pluck('id')->first();
        $response = $this->post('api/v1/vendors/show', ['id' => $vendor]);
        $response->assertStatus(200);
    }

//    public function test_check_domain(): void
//    {
//        $response = $this->post('api/v1/vendors/checkdomain', ['domain' => random_str(10)]);
//        $response->assertStatus(200);
//    }
}
