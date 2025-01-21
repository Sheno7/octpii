<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VeProvidersTest extends TestCase
{
    public function test_list_providers(): void
    {
        $response = $this->getJson('/api/ve/v1/providers/list');
        $response->assertStatus(200);

    }
}
