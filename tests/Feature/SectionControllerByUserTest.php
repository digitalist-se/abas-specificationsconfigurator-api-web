<?php

namespace Tests\Feature;

use App\Models\Role;
use Tests\PassportTestCase;

class SectionControllerByUserTest extends PassportTestCase
{
    protected $role = Role::USER;

    public function test_get_list()
    {
        $response = $this->getJson('/api/chapters');
        $chapters = $response->json();
        foreach ($chapters as $chapter) {
            $response = $this->getJson('/api/sections/'.$chapter['id']);
            static::assertStatus($response, 200);
            $response->assertJsonStructure([
                '*' => [
                    'id',
                    'headline',
                    'has_headline',
                ],
            ]);

            $sections = $response->json();
            static::assertNotEmpty($chapters);
            foreach ($sections as $section) {
                if ($section['has_headline']) {
                    $this->assertTextWithKeyIsGiven($section, 'headline');
                }
                $this->assertTextWithKeyIsGiven($section, 'description', true);
            }
        }
    }
}
