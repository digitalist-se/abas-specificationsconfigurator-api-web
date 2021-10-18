<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Text;
use App\Models\Locale;
use Tests\PassportTestCase;

class TextControllerByUserTest extends PassportTestCase
{
    protected $role = Role::USER;

    protected function locale() {
        return Locale::current()->getValue();
    }

    public function test_get_list()
    {
        $locale = $this->locale();
        $response = $this->getJson('/api/texts?locale='.$locale);
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
        $locale = $this->locale();
        $text          = Text::factory()->create(['locale' => $locale]);
        $newValue      = 'new Value';
        $response      = $this->putJson('/api/texts/'.$text->id, ['value' => $newValue]);
        static::assertStatus($response, 403);
    }

    public function test_create()
    {
        $locale = $this->locale();
        $data      = [
            'key'   => 'random key',
            'value' => 'random value',
            'locale' => $locale,
        ];
        $response = $this->postJson('/api/texts', $data);
        static::assertStatus($response, 403);
    }
}
