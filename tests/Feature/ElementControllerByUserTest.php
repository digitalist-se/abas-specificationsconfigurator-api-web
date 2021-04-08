<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\PassportTestCase;

class ElementControllerByUserTest extends PassportTestCase
{
    use WithoutMiddleware; // without middleware so we can create more than 60 request per minute
    protected $role = Role::USER;

    public function test_get_list()
    {
        $response = $this->getJson('/api/chapters');
        $chapters = $response->json();
        foreach ($chapters as $chapter) {
            $response = $this->getJson('/api/sections/'.$chapter['id']);
            static::assertStatus($response, 200);

            $sections = $response->json();
            static::assertNotEmpty($chapters);
            foreach ($sections as $section) {
                $response = $this->getJson('/api/elements/'.$section['id']);
                static::assertStatus($response, 200);
                $response->assertJsonStructure([
                    '*' => [
                        'id',
                        'type',
                        'content',
                    ],
                ]);
                $elements = $response->json();
                foreach ($elements as $element) {
                    $this->assertTextWithKeyIsGiven($element, 'content');
                }
            }
        }
    }
}
