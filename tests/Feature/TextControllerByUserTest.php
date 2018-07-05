<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Text;
use Tests\PassportTestCase;

class TextControllerByUserTest extends PassportTestCase
{
    protected $role = Role::USER;

    public function testGetList()
    {
        $response = $this->getJson('/api/texts');
        $this->assertStatus($response, 200);
        $response->assertJson([]);
        $texts = $response->json();
        $this->assertNotEmpty($texts);
        foreach ($texts as $key => $textObject) {
            $this->assertTrue(is_string($key));
            $this->assertTrue(is_string($textObject['key']));
            $this->assertTrue(is_string($textObject['value']));
            $this->assertNotEmpty($key);
            $this->assertNotEmpty($textObject['key']);
            $this->assertNotEmpty($textObject['value']);
        }
    }

    public function testUpdate()
    {
        $text          = factory(Text::class)->create();
        $newValue      = 'new Value';
        $response      = $this->putJson('/api/texts/'.$text->key, ['value' => $newValue]);
        $this->assertStatus($response, 403);
    }

    public function testCreate()
    {
        $data      = [
            'key'   => 'random key',
            'value' => 'random value',
        ];
        $response = $this->postJson('/api/texts', $data);
        $this->assertStatus($response, 403);
    }
}
