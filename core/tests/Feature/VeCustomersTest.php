<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VeCustomersTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_list(): void
    {
        $response = $this->get('api/ve/v1/customers/list');
        // using barer token

    }
}
