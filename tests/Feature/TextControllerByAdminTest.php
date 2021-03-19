<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Text;
use Tests\PassportTestCase;

class TextControllerByAdminTest extends PassportTestCase
{
    protected $role = Role::ADMIN;

    public function test_get_list()
    {
        $response = $this->getJson('/api/texts');
        static::assertStatus($response, 200);
        $response->assertJson([]);
        $texts = $response->json();
        static::assertNotEmpty($texts);
        foreach ($texts as $key => $textObject) {
            static::assertTrue(is_string($key));
            static::assertTrue(is_string($textObject['key']));
            static::assertTrue(is_string($textObject['value']));
            static::assertNotEmpty($key);
            static::assertNotEmpty($textObject['key']);
            static::assertNotEmpty($textObject['value']);
        }
    }

    public function test_update()
    {
        $text          = Text::factory()->create();
        $newValue      = 'new Value';
        $response      = $this->putJson('/api/texts/'.$text->key, ['value' => $newValue]);
        static::assertStatus($response, 204);

        $response = $this->getJson('/api/texts');
        static::assertStatus($response, 200);
        $response->assertJson([
            $text->key => ['value' => $newValue],
        ]);
    }

    public function test_create()
    {
        $data      = [
            'key'   => 'random key',
            'value' => 'random value',
        ];
        $response = $this->postJson('/api/texts', $data);
        static::assertStatus($response, 204);

        $response = $this->getJson('/api/texts');
        static::assertStatus($response, 200);
        $response->assertJson([
            $data['key'] => ['value' => $data['value']],
        ]);
    }
}
