<?php

namespace Tests\Feature;

use App\Models\Locale;
use App\Models\Role;
use App\Models\Text;
use Tests\PassportTestCase;

class TextControllerByAdminTest extends PassportTestCase
{
    protected $role = Role::ADMIN;

    protected function locale()
    {
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
            static::assertIsString($key);
            static::assertIsString($textObject['key']);
            static::assertIsString($textObject['value']);
            static::assertIsString($textObject['locale']);
            static::assertIsString($textObject['id']);
            static::assertNotEmpty($key);
            static::assertNotEmpty($textObject['key']);
            static::assertNotEmpty($textObject['value']);
            static::assertNotEmpty($textObject['locale']);
            static::assertNotEmpty($textObject['id']);
        }
    }

    public function test_update()
    {
        $locale = $this->locale();
        $text = Text::factory()->create(['locale' => $locale]);
        $newValue = 'new Value';
        $response = $this->putJson('/api/texts/'.$text->id, ['value' => $newValue]);
        static::assertStatus($response, 204);

        $response = $this->getJson('/api/texts?locale='.$locale);
        static::assertStatus($response, 200);
        $response->assertJson([
            $text->key => ['value' => $newValue],
        ]);
    }

    public function test_create()
    {
        $locale = $this->locale();
        $data = [
            'key'   => 'random key',
            'value' => 'random value',
            'locale' => $locale,
        ];
        $response = $this->postJson('/api/texts', $data);
        static::assertStatus($response, 204);

        $response = $this->getJson('/api/texts?locale='.$locale);
        static::assertStatus($response, 200);
        $response->assertJson([
            $data['key'] => ['value' => $data['value']],
        ]);
    }
}
