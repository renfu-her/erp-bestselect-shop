<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    private const API_URL = '/api/web/collection/collection';

    /**
     * test API json response
     *
     * @return void
     */
    public function test_json_structure()
    {
        $response = $this->postJson(self::API_URL, [
            'id' => '1',
//            'amount' => '10'
        ]);
        $response->assertJsonStructure([
            'status',
            'msg',
            'data' => [
                'name',
                'list' => [
                ]
            ]
        ]);
    }

    public function test_json_response_type()
    {
        $response = $this->postJson(self::API_URL, [
            'id' => '2',
//            'amount' => '10'
        ]);

        $status = $response->decodeResponseJson()['status'];
        $this->assertIsInt($status);

        $jsonData = $response->decodeResponseJson()['data'];

        $this->assertIsArray($jsonData['list']);
        $this->assertIsInt($jsonData['list'][0]['amount']);
        $this->assertIsArray($jsonData['list'][0]['price']);
        $this->assertIsInt($jsonData['list'][0]['price']['origin']);
        $this->assertIsInt($jsonData['list'][0]['price']['sale']);
    }
}
