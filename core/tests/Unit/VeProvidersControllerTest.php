<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class VeProvidersControllerTest extends TestCase
{
    public function testIndexReturnsSuccessResponse()
    {
     // handle using token
     $response = $this->get('/api/ve/v1/providers/list');
        $response->assertStatus(200);
    }



}
