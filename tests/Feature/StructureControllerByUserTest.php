<?php

namespace Tests\Feature;

use App\Models\Role;
use Tests\PassportTestCase;

class StructureControllerByUserTest extends PassportTestCase
{
    protected $role = Role::USER;

    public function testGetList()
    {
        $response = $this->getJson('/api/structure');
        $this->assertStatus($response, 200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'sections' => [
                    '*' => [
                        'id',
                        'headline',
                        'has_headline',
                        'elements' => [
                            '*' => [
                                'id',
                                'type',
                                'content',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $chapters = $response->json();
        foreach ($chapters as $chapter) {
            $sections = $chapter['sections'];
            $this->assertNotEmpty($chapters);
            foreach ($sections as $section) {
                if ($section['has_headline']) {
                    $this->assertTextWithKeyIsGiven($section, 'headline');
                }
                $this->assertTextWithKeyIsGiven($section, 'description', true);
                $elements = $section['elements'];
                foreach ($elements as $element) {
                    $this->assertTextWithKeyIsGiven($element, 'content');
                    if ('choice' === $element['type']) {
                        $this->assertNotEmpty($element['choice_type_id']);
                    }
                }
            }
        }
    }
}
