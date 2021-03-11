<?php

namespace Tests\Feature;

use Tests\PassportTestCase;

class ChoiceTypeControllerByUserTest extends PassportTestCase
{
    public function test_get_list_of_choice_types()
    {
        $response = $this->getJson('/api/choice-types');
        static::assertStatus($response, 200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'type',
                'multiple',
                'tiles',
                'options' => [
                    '*' => [
                        'type',
                        'text',
                        'value',
                    ],
                ],
            ],
        ]);
    }
}
