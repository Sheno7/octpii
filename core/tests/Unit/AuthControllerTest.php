<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
class AuthControllerTest extends TestCase
{
    public function test_login_with_valid_credentials()
    {
        // Create a user for testing
        $user = User::create([
            'name' => 'amr',
            'phone' => '01001933559',
            'email' => 'email@email.com',
            'password' => bcrypt('your_plain_text_password')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'your_plain_text_password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'phone',
                    'email',
                    'status',
                    'created_at',
                    'updated_at',
                    'deleted_at'
                ],
                'authorization' => [
                    'token',
                    'type',
                ],
            ]);

        // You can also assert specific user attributes and token values if needed
        $response->assertJson([
            'user' => [
                'name' => 'amr',
                'phone' => '01001933559',
                // Add other expected user attributes' values
            ],
            'authorization' => [
                'type' => 'Bearer',
            ],
        ]);
    }
    public function test_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'non_existent_email@email.com',
            'password' => 'invalid_password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized',
            ]);
    }

    public function testIndexReturnsSendOtp()
    {
        $response = $this->json('POST', '/api/sendOtp', ['phone' => '01001933556','country_id'=>'1']);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
            ]);
    }
    // test with wrong country id
//    public function testIndexReturnsSendOtpWithWrongCountryId()
//    {
//        $response = $this->json('POST', '/api/sendOtp', ['phone' => '01001933556','country_id'=>'5000000']);
//        $response->assertStatus(422)
//            ->assertJson([
//                'status' => false,
//                'message' => 'The selected country id is invalid.',
//            ]);
//    }

    public function testIndexReturnsVerifyOtp()
    {
        $response = $this->json('POST', '/api/verifyOtp', ['phone' => '01001933556','country_id'=>'1','otp'=>'1234']);
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
            ]);
    }


}
