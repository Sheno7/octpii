<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VeCustomersControllerTest extends TestCase
{
    public function testIndexReturnsSuccessResponse()
    {
        // Fetch the data from the database
        $data = DB::table('customers')
            ->select('customers.id', 'users.id as user_id', 'users.first_name', 'users.last_name', 'customers.created_at')
            ->join('users', 'customers.user_id', '=', 'users.id')
            ->orderBy('customers.id', 'desc')
            ->paginate(10);

        // Add 'total_spent' and 'total_booking' keys with empty values for each customer
        foreach ($data as $customer) {
            $customer->total_spent = '';
            $customer->total_booking = '';
        }

        // Assert the response with the expected JSON structure and data
        $response = $this->json('GET', '/api/ve/v1/customers/list');
        $response->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'success',
                'data' => [
                    'response' => $data->toArray(),
                ],
            ]);
    }
}
