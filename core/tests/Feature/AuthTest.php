<?php

namespace Tests\Feature;

use App\Models\Countries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_register(): void
    {
        $response = $this->post('api/register', [
            'name' => 'test',
            'phone' => '123456789',
            'email' => 'amr@amr.com',
            'password' => '123456',
        ]);
        $response->assertStatus(302);
    }

//    public function test_login(): void
//    {
//        $response = $this->post('api/login', [
//            'email' => 'amr@amr.com',
//            'password' => '123456',
//        ]);
//        $response->assertStatus(200);
//    }

    public function test_sendOtp(): void
    {
        $response = $this->post('api/sendOtp', [
            'phone' => '01001933551',
            'country_id' => Countries::where('deleted_at', null)->first()->id,
        ]);
        $response->assertStatus(200);
    }

    public function test_verifyOtp(): void
    {
        $response = $this->post('api/verifyOtp', [
            'phone' => '01001933551',
            'country_id' => Countries::where('deleted_at', null)->first()->id,
            'otp' => '1234',
        ]);
        $response->assertStatus(200);
    }
}
